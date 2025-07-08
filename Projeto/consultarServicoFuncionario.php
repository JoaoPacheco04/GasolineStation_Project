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

// Atualização do estado do serviço (POST)
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['idServicoAgendado'], $_POST['novoEstado'])) {
    $idServicoAgendado = intval($_POST['idServicoAgendado']);
    $novoEstado = $_POST['novoEstado'];

    $estados_validos = ['nao iniciado', 'em execucao', 'finalizado'];

    if (in_array($novoEstado, $estados_validos)) {
        if ($novoEstado === 'finalizado') {
            $idFuncionario = $_SESSION['user_id'];
            // Atualiza com o id do funcionário que finalizou
            $stmt = $conn->prepare("UPDATE Servico_Agendado SET estado = ?, idFuncionarioFinalizou = ? WHERE idAgendamento = ?");
            $stmt->bind_param("sii", $novoEstado, $idFuncionario, $idServicoAgendado);
        } else {
            // Atualiza apenas o estado
            $stmt = $conn->prepare("UPDATE Servico_Agendado SET estado = ? WHERE idAgendamento = ?");
            $stmt->bind_param("si", $novoEstado, $idServicoAgendado);
        }
        $stmt->execute();
        $stmt->close();

        // Obtem o id do cliente
        $stmt = $conn->prepare("SELECT idCliente FROM Servico_Agendado WHERE idAgendamento = ?");
        $stmt->bind_param("i", $idServicoAgendado);
        $stmt->execute();
        $stmt->bind_result($idCliente);
        $stmt->fetch();
        $stmt->close();

        // Cria notificação
        $mensagem = "O estado do seu serviço agendado mudou para '$novoEstado'.";
        $stmt = $conn->prepare("INSERT INTO notificacao_servico (idAgendamento, idCliente, mensagem) VALUES (?, ?, ?)");
        $stmt->bind_param("iis", $idServicoAgendado, $idCliente, $mensagem);
        $stmt->execute();
        $stmt->close();
    }
}

// SOMENTE SERVIÇOS QUE AINDA NÃO FORAM FINALIZADOS
$sql = "SELECT SA.idAgendamento, SA.data, SA.hora, SA.estado, 
               S.nome AS nome_servico, 
               U.nome AS nome_cliente
        FROM Servico_Agendado SA
        JOIN Servico S ON SA.idServico = S.id
        JOIN Utilizador U ON SA.idCliente = U.id
        WHERE SA.estado != 'finalizado'
        ORDER BY SA.data, SA.hora";

$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="pt">
<head>
  <meta charset="UTF-8" />
  <title>Serviços Agendados</title>
  <link rel="stylesheet" href="./css/consultarServicos.css" />
</head>
<body>
  <div class="container">
    <h1>Serviços Agendados</h1>

    <table class="servicos-tabela" border="1" cellpadding="5" cellspacing="0">
      <thead>
        <tr>
          <th>Cliente</th>
          <th>Serviço</th>
          <th>Data</th>
          <th>Hora</th>
          <th>Estado Atual</th>
          <th>Alterar Estado</th>
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
              <td>
                <form method="POST" style="display: flex; gap: 5px;">
                  <input type="hidden" name="idServicoAgendado" value="<?php echo htmlspecialchars($row['idAgendamento']); ?>">
                  <select name="novoEstado" required>
                    <option value="">Escolher...</option>
                    <option value="nao iniciado" <?php if($row['estado'] === 'nao iniciado') echo 'selected'; ?>>Não iniciado</option>
                    <option value="em execucao" <?php if($row['estado'] === 'em execucao') echo 'selected'; ?>>Em execução</option>
                    <option value="finalizado" <?php if($row['estado'] === 'finalizado') echo 'selected'; ?>>Finalizado</option>
                  </select>
                  <button type="submit">Atualizar</button>
                </form>
              </td>
            </tr>
          <?php endwhile; ?>
        <?php else: ?>
          <tr><td colspan="6">Nenhum serviço agendado encontrado.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>

  <a href="funcservicobase.php" class="voltar-fixo">⟵ Voltar</a>
</body>
</html>
