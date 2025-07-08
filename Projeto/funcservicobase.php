<?php
session_start();
// Verifica se o utilizador está autenticado e é funcionário de serviços
if (!isset($_SESSION['user_id']) || $_SESSION['tipo'] !== 'Funcionario_Servicos') {
    header("Location: login.php");
    exit;
}
$primeiroNome = $_SESSION['primeiro_nome'] ?? 'Funcionário Servicos';
?>

<!DOCTYPE html>
<html lang="pt">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Funcionário de Serviços</title>
  <link rel="icon" href="./favicons/a.png" />
  <link rel="stylesheet" href="./css/funservico.css" />
</head>
<body>
   <nav class="admin-nav">
    <div class="navbar">
      <div class="nav-left">Olá, <strong><?= htmlspecialchars($primeiroNome) ?></strong></div>
      <div class="nav-right">
        <form action="logout.php" method="post" style="margin:0;">
          <button type="submit" class="logout-button">Terminar Sessão</button>
        </form>
      </div>
    </div>
  </nav>

  <div class="header">
    <img src="./favicons/icon1.png" alt="Icon" class="icon1" />
    <h1>Funcionário de Serviços</h1>
  </div>
  <div class="main-container">
  <div class="botoes-container">
    <button onclick="window.location.href='consultarServicoFuncionario.php'">Consultar Serviços Agendados</button>
    <button onclick="window.location.href='registarIndisponibilidade.php'">Registar Indisponibilidade</button>
    <button onclick="window.location.href='consultasRealizado.php'"> Serviço Realizado</button>
    <button onclick="window.location.href='marcarFerias.php'">Registar Pedido de Férias</button>
  </div>
 </div>
</body>
</html>