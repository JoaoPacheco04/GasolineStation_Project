<?php
session_start();
include('db_connection.php'); // Adjust path if needed

// Only allow Gerente Posto
if (!isset($_SESSION['user_id']) || $_SESSION['tipo'] !== 'Gerente_Posto') {
    header("Location: login.php");
    exit;
}

// Fetch bombas and their fuel level
$bomba_query = "
    SELECT b.idBomba AS id, s.idSensor, re.nivelCombustivel AS percent, MAX(re.data) as data, b.estado
    FROM bomba b
    JOIN sensor s ON b.idSensor = s.idSensor
    LEFT JOIN registro_estado re ON s.idSensor = re.idSensor
    GROUP BY b.idBomba
";

$bomba_result = $conn->query($bomba_query);
$bombas = [];
if ($bomba_result) {
    while ($row = $bomba_result->fetch_assoc()) {
        $bombas[] = $row;
    }
}

// Fetch alerts
$alert_query = "SELECT * FROM notificacao_bomba ORDER BY data DESC LIMIT 10";
$alerts = $conn->query($alert_query);
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8" />
    <title>Gerente Posto - Dashboard</title>
    <link rel="stylesheet" href="./css/GerentePostoBase.css" />
</head>
<body>
  <div class="dashboard">

  <!-- Estado dos Tanques -->
  <div class="container">
    <h2 class="section-title">Estado dos Tanques</h2>
    <div class="bomba-grid" id="bomba-grid">
  <?php foreach ($bombas as $bomba): ?>
    <a href="detalhes_bomba.php?id=<?= $bomba['id'] ?>" 
       class="bomba-card" 
       style="background: linear-gradient(to top, #dc3545 <?= (100 - $bomba['percent']) ?>%, #28a745 <?= $bomba['percent'] ?>%)">
      <div class="icon">⛽</div>
      <div class="percent"><?= round($bomba['percent']) ?>%</div>
      <div class="estado-label"><?= htmlspecialchars($bomba['estado'] ?? 'ativo') ?></div>
    </a>
  <?php endforeach; ?>
</div>

  </div>

  <!-- Notificações -->
  <div class="container">
    <h2 class="section-title">Notificações</h2>
    <div class="alert-box">
      <?php while ($alert = $alerts->fetch_assoc()): ?>
        <div class="alert">
          <strong>[<?= date('H:i', strtotime($alert['data'])) ?>]</strong>
          <?= htmlspecialchars($alert['mensagem']) ?>
        </div>
      <?php endwhile; ?>
    </div>
    <form method="post" action="clear_alerts.php">
      <button type="submit" class="clear-alerts">Limpar Alertas</button>
    </form>
  </div>

</div>



<script>
  async function refreshData() {
    try {
      const response = await fetch('refresh_bombas_alerts.php');
      if (!response.ok) throw new Error('Network response was not ok');

      const data = await response.json();

      document.getElementById('bomba-grid').innerHTML = data.bombas;
      document.getElementById('alert-list').innerHTML = data.alerts;

    } catch (error) {
      console.error('Failed to refresh data:', error);
    }
  }

  setInterval(refreshData, 10000);
</script>

</body>
</html>
