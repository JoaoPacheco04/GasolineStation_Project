<?php
session_start();
include './db_connection.php';

if (!isset($_SESSION['user_id']) || $_SESSION['tipo'] !== 'Cliente') {
    header("Location: login.php");
    exit;
}

$idCliente = $_SESSION['user_id'];

$sql = "SELECT SA.data, SA.hora, SA.estado, S.nome AS servico_nome
        FROM Servico_Agendado SA
        JOIN Servico S ON SA.idServico = S.id
        WHERE SA.idCliente = ?
        ORDER BY SA.data, SA.hora";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $idCliente);
$stmt->execute();
$result = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="pt">
<head>
  <meta charset="UTF-8">
  <title>Consultar Serviços</title>
  <link rel="stylesheet" href="./css/consultarServicos.css">
</head>
<body>

  <div class="container">
    <h1>Serviços Agendados</h1>

    <table class="servicos-tabela">
      <thead>
        <tr>
          <th>Tipo de Serviço</th>
          <th>Data</th>
          <th>Hora</th>
          <th>Status</th>
        </tr>
      </thead>
      <tbody>
        <?php while ($row = $result->fetch_assoc()): ?>
          <tr>
            <td><?php echo htmlspecialchars($row['servico_nome']); ?></td>
            <td><?php echo htmlspecialchars($row['data']); ?></td>
            <td><?php echo htmlspecialchars($row['hora']); ?></td>
            <td><span class="status"><?php echo htmlspecialchars($row['estado']); ?></span></td>
          </tr>
        <?php endwhile; ?>
      </tbody>
    </table>
  </div>

  
  <a href="clientebase.php" class="voltar-fixo">⟵ Voltar</a>

</body>
</html>