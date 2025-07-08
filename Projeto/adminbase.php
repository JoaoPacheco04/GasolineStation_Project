<?php
session_start();
// Role check: only allow logged-in admins
if (!isset($_SESSION['user_id']) || $_SESSION['tipo'] !== 'Administrador') {
    header("Location: login.php");
    exit;
}

$primeiroNome = $_SESSION['primeiro_nome'] ?? 'Administrador';
?>
<!DOCTYPE html>
<html lang="pt">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Administrador</title>
  <link rel="icon" href="./favicons/a.png" />
  <link rel="stylesheet" href="./css/adminbase.css" />
</head>
<body>
  <nav class="admin-nav">
    <div class="navbar">
      <div class="nav-left">Olá, <strong>Administrador</strong></div>
      <div class="nav-right"><a href="logout.php">Logout</a></div>
    </div>
  </nav>

  <div class="header">
    <img src="./favicons/icon1.png" alt="Icon" class="icon1" />
    <h1>Gazóline</h1>
  </div>

  <div class="white-container">
    <div class="botoes-container">
      <button onclick="window.location.href='registarUtilizador.html'">Registar Utilizador</button>
      <button onclick="window.location.href='registarPermissoes.php'">Registar Permissões</button>
      <button onclick="window.location.href='alterar_utilizador.php'">Alterar Utilizador</button>
      <button onclick="window.location.href='alterarPermissoes.php'">Alterar Permissões</button>
    </div>

  
  </div>
</body>
</html>
