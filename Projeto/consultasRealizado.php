<?php
session_start();
include './db_connection.php';
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Verifica se é funcionário de serviços
if (!isset($_SESSION['user_id']) || $_SESSION['tipo'] !== 'Funcionario_Servicos') {
    header("Location: login.php");
    exit;
}

$idFuncionario = $_SESSION['user_id'];

$sql = "SELECT SA.idAgendamento, SA.data, SA.hora, SA.estado, 
               S.nome AS nome_servico, 
               U.nome AS nome_cliente
        FROM Servico_Agendado SA
        JOIN Servico S ON SA.idServico = S.id
        JOIN Utilizador U ON SA.idCliente = U.id
        WHERE SA.estado = 'finalizado' AND SA.idFuncionarioFinalizou = ?
        ORDER BY SA.data DESC, SA.hora DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $idFuncionario);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="pt">
<head>
  <meta charset="UTF-8" />
  <title>Serviços Finalizados</title>
  <link rel="stylesheet" href="./css/consultarServicos.css" />
</head>
<body>
  <div class="container">
    <h1>Serviços Finalizados por Si</h1>

    <table class="servicos-tabela" border="1" cellpadding="5" cellspacing="0">
      <thead>
        <tr>
          <th>Cliente</th>
          <th>Serviço</th>
          <th>Data</th>
          <th>Hora</th>
          <th>Estado</th>
        </tr>
      </thead>
      <tbody>
        <?php if ($result && $result->num_rows > 0): ?>
          <?php while ($row = $result->fetch_assoc()): ?>
            <tr>
              <td><?php echo htmlspecialchars($row['nome_cliente']); ?></td>
              <td><?php echo htmlspecialchars($row['nome_servico']); ?></td>
              <td><?php echo htmlspecialchars($row['data']); ?></td>
              <td><?php echo htmlspecialchars($row['hora']); ?></td>
              <td><?php echo htmlspecialchars($row['estado']); ?></td>
            </tr>
          <?php endwhile; ?>
        <?php else: ?>
          <tr><td colspan="5">Nenhum serviço finalizado encontrado.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>

  <a href="funcservicobase.php" class="voltar-fixo">⟵ Voltar</a>
</body>
</html>
