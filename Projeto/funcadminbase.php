<?php
session_start();
// Verifica se o utilizador está autenticado e é Funcionário Administrativo
if (!isset($_SESSION['user_id']) || $_SESSION['tipo'] !== 'Funcionario_Administrativo') {
    header("Location: login.php");
    exit;
}

$primeiroNome = $_SESSION['primeiro_nome'] ?? 'Funcionário Administrativo';
?>

<!DOCTYPE html>
<html lang="pt">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Funcionário Administrativo</title>
  <link rel="icon" href="./favicons/a.png" />
  <link rel="stylesheet" href="./css/funadmbase.css" />
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
    <h1>Funcionário Administrativo</h1>
  </div>

  <div class="main-container">
    <div class="botoes-container">
      <button onclick="window.location.href='registarFidelidade.php'">Registar Programa de Fidelidade</button>
      <button onclick="window.location.href='registarServico.php'">Registar Serviço</button>
      <button onclick="window.location.href='atribuirServico.php'">Atribuir Serviço</button>
      <button onclick="window.location.href='registarMaterialServico.php'">Registar Material de Serviço</button>
    </div>
  </div>
</body>
</html>
