<?php
session_start();
include('../db_connection.php');

// Role check: only allow logged-in 
if (!isset($_SESSION['user_id']) || $_SESSION['tipo'] !== 'Operador') {
    header("Location: ../login.php");
    exit;
}

$primeiroNome = $_SESSION['primeiro_nome'] ?? 'OPERADOR';
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <title>Operador - Base</title>
    <link rel="stylesheet" href="../css/operadorbase.css" />
</head>
<body>
 <nav class="admin-nav">
    <div class="navbar">
      <div class="nav-left">Olá, <strong><?= htmlspecialchars($primeiroNome) ?></strong></div>
      <div class="nav-right">
        <form action="../logout.php" method="post" style="margin:0;">
          <button type="submit" class="logout-button">Logout</button>
        </form>
      </div>
    </div>
  </nav>
    <div class="main-container">
        <h1>Bem-vindo Operador</h1>
        <p class="subtitle">Selecione uma das ações abaixo:</p>

        <div class="botoes-container">
            <form action="registarVenda.php" method="post">
                <button type="submit">Registar Venda</button>
            </form>

            <form action="registarPremio.php" method="post">
                <button type="submit">Registar Prémio</button>
            </form>

            <form action="entradaStock.php" method="post">
                <button type="submit">Entrada de Produto em Stock</button>
            </form>

            <form action="registarCartao.php" method="post">
                <button type="submit">Registar Cartão de Fidelidade</button>
            </form>
        </div>

    </div>

</body>
</html>
