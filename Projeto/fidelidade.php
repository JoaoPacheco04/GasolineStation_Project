<?php
session_start();
include './db_connection.php'; // <-- Update with your DB connection file path

if (!isset($_SESSION['user_id']) || $_SESSION['tipo'] !== 'Cliente') {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Step 1: Get the cartao_fidelidade for this client
$stmt = $conn->prepare("SELECT id_cartao, pontos FROM cartao_fidelidade WHERE id_client = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

$cartao = $result->fetch_assoc();

?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Cartão Fidelidade</title>
  <link rel="stylesheet" href="./css/fidelidade.css" />
</head>
<body>
  <?php if ($cartao): ?>
    <div class="points-container">
      <h2>Pontos: <?= $cartao['pontos'] ?></h2>
    </div>

    <div class="main-container">
      <h1>Movimentos do Cartão</h1>

      <?php
      $stmt = $conn->prepare("SELECT tipo, pontos, data_movimento FROM movimento_cartao WHERE id_cartao = ? ORDER BY data_movimento DESC");
      $stmt->bind_param("i", $cartao['id_cartao']);
      $stmt->execute();
      $movs = $stmt->get_result();

      if ($movs->num_rows > 0):
          while ($row = $movs->fetch_assoc()):
              $tipo = $row['tipo'] === 'acumular' ? 'Acumulado' : 'Descontado';
              $classe = $row['tipo'] === 'acumular' ? 'acumular' : 'descontar';
      ?>
        <div class="movimento <?= $classe ?>">
          <strong><?= $tipo ?>:</strong> <?= $row['pontos'] ?> ponto(s)<br>
          <small><?= $row['data_movimento'] ?></small>
        </div>
      <?php endwhile; else: ?>
        <p>Sem movimentos registrados.</p>
      <?php endif; ?>
    </div>
  <?php else: ?>
    <div class="main-container">
      <h1>Sem Cartão Fidelidade</h1>
      <p>Você ainda não possui um cartão fidelidade.</p>
    </div>
  <?php endif; ?>

  <a href="clientebase.php" class="voltar-fixo">⟵ Voltar</a>
</body>
</html>
