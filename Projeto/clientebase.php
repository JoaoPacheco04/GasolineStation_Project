<?php
session_start();
include('db_connection.php'); // Make sure this exists
if (!isset($_SESSION['user_id']) || $_SESSION['tipo'] !== 'Cliente') {
    header("Location: login.php");
    exit;
}

$idCliente = $_SESSION['user_id'];
$primeiroNome = $_SESSION['primeiro_nome'] ?? 'Cliente'; // adjust this if needed

$notisql = "SELECT mensagem, data FROM notificacao_servico WHERE idCliente = $idCliente AND lida = 0 ORDER BY data DESC";
$notiresult = $conn->query($notisql);
?>
<!DOCTYPE html>
<html lang="pt">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Cliente</title>
  <link rel="icon" href="./favicons/a.png" />
  <link rel="stylesheet" href="./css/clientebase.css" />
</head>
<body>
  <nav class="admin-nav">
    <div class="navbar">
      <div class="nav-left">Olá, <strong><?= htmlspecialchars($primeiroNome) ?></strong></div>
      <div class="nav-right"><a href="logout.php">Logout</a></div>
    </div>
  </nav>

  <div class="header">
    <img src="./favicons/icon1.png" alt="Icon" class="icon1" />
    <h1>Gazóline</h1>
  </div>

  <div class="main-container">
    <div class="botoes-container">
      <?php
        $buttons = [
          'agendar_servico' => 'agendarServico.php',
          'consultar_servico' => 'consultarServicos.php',
          'cartao_fidelidade' => 'fidelidade.php'
        ];
        foreach ($buttons as $perm => $link) {
          
            $label = ucwords(str_replace('_', ' ', $perm));
            echo "<button onclick=\"window.location.href='$link'\">$label</button>";
          
        }
      ?>
    </div>

    <div class="notificacoes-box">
      <h2>Notificações</h2>
      <?php if ($notiresult && $notiresult->num_rows > 0): ?>
        <ul>
          <?php while ($row = $notiresult->fetch_assoc()): ?>
            <li><strong><?php echo $row['data']; ?>:</strong> <?php echo $row['mensagem']; ?></li>
          <?php endwhile; ?>
        </ul>
        <form action="marcar_notificacoes_lidas.php" method="post">
          <button type="submit" class="mark-read">Marcar como lidas</button>
        </form>
      <?php else: ?>
        <p>Sem novas notificações.</p>
      <?php endif; ?>
    </div>
  </div>
</body>
</html>
