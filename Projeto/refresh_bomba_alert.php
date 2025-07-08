<?php
session_start();
include('db_connection.php');

if (!isset($_SESSION['user_id']) || $_SESSION['tipo'] !== 'Gerente_Posto') {
    http_response_code(403);
    exit("Access denied");
}

// Fetch bombas
$bomba_query = "
    SELECT b.idBomba as id, 
           MAX(re.data) as latest_data, 
           (SELECT re2.nivelCombustivel 
            FROM registro_estado re2 
            WHERE re2.idSensor = s.idSensor 
            ORDER BY re2.data DESC LIMIT 1) as nivelCombustivel
    FROM bomba b
    JOIN sensor s ON b.idSensor = s.idSensor
    LEFT JOIN registro_estado re ON s.idSensor = re.idSensor
    GROUP BY b.idBomba, s.idSensor
    ORDER BY b.idBomba
";
$bombas_result = $conn->query($bomba_query);
$bombas = [];
if ($bombas_result) {
    while ($row = $bombas_result->fetch_assoc()) {
        $nivel = is_null($row['nivelCombustivel']) ? 0 : floatval($row['nivelCombustivel']);
        $bombas[] = [
            'id' => $row['id'],
            'percent' => $nivel
        ];
    }
}

// Fetch alerts
$alert_query = "SELECT * FROM notificacao_bomba ORDER BY data DESC LIMIT 10";
$alerts = $conn->query($alert_query);

// Return JSON with two html parts
ob_start();
?>
<!-- bomba grid HTML -->
<?php foreach ($bombas as $bomba):
    $percent = $bomba['percent'];
    $gradient = "linear-gradient(to top, red " . (100 - $percent) . "%, green " . $percent . "%)";
?>
<a href="detalhes_bomba.php?id=<?= htmlspecialchars($bomba['id']) ?>" 
   class="bomba-card" 
   style="background: <?= $gradient ?>;">
  <div class="icon">⛽</div>
  <div class="percent"><?= round($percent) ?>%</div>
  <div class="id">Bomba <?= htmlspecialchars($bomba['id']) ?></div>
</a>
<?php endforeach; ?>
<?php
$bomba_html = ob_get_clean();

ob_start();
?>
<!-- alerts list HTML -->
<?php while ($alert = $alerts->fetch_assoc()): ?>
  <div class="alert">
    <strong>[<?= date('H:i', strtotime($alert['data'])) ?>]</strong>
    <?= htmlspecialchars($alert['mensagem']) ?>
  </div>
<?php endwhile; ?>
<?php
$alerts_html = ob_get_clean();

header('Content-Type: application/json');
echo json_encode([
    'bombas' => $bomba_html,
    'alerts' => $alerts_html
]);
exit;
?>
