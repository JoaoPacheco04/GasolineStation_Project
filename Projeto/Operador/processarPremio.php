<?php
session_start();
include('../db_connection.php');

if (!isset($_SESSION['user_id']) || $_SESSION['tipo'] !== 'Operador') {
    header("Location: ../index.html");
    exit;
}

if (!isset($_POST['id_client'], $_POST['premio_id'])) {
    echo "Dados inválidos.";
    exit;
}

$id_client = (int)$_POST['id_client'];
$premio_ids = explode(',', $_POST['premio_id']);

// Get cartao_fidelidade id and current points
$stmt = $conn->prepare("SELECT id_cartao, pontos FROM cartao_fidelidade WHERE id_client = ?");
$stmt->bind_param("i", $id_client);
$stmt->execute();
$stmt->bind_result($id_cartao, $pontos_atual);
$stmt->fetch();
$stmt->close();

if ($pontos_atual === null) {
    echo "Cliente não encontrado ou sem cartão fidelidade.";
    exit;
}

$mensagens = [];
$sucesso = false;

foreach ($premio_ids as $pid) {
    $pid = (int)$pid;
    if ($pid <= 0) continue;

    $stmt = $conn->prepare("SELECT nome, pontos_necessarios FROM Premio WHERE id = ?");
    $stmt->bind_param("i", $pid);
    $stmt->execute();
    $stmt->bind_result($nome, $pontos_necessarios);

    if ($stmt->fetch()) {
        $stmt->close();
    
        if ($pontos_atual >= $pontos_necessarios) {
            // Deduct points from cartao_fidelidade (update)
            $stmt_update = $conn->prepare("UPDATE cartao_fidelidade SET pontos = pontos - ? WHERE id_cartao = ?");
            $stmt_update->bind_param("ii", $pontos_necessarios, $id_cartao);
            $stmt_update->execute();
            $stmt_update->close();
    
            // Log movement in movimento_cartao with data_movimento and tipo bound param
            $stmt_mov = $conn->prepare("INSERT INTO movimento_cartao (id_cartao, tipo, pontos, data_movimento) VALUES (?, ?, ?, NOW())");
            $tipo_mov = 'descontar'; // must exactly match ENUM in DB
            $stmt_mov->bind_param("isi", $id_cartao, $tipo_mov, $pontos_necessarios);
            $stmt_mov->execute();
            $stmt_mov->close();
    
            $pontos_atual -= $pontos_necessarios;
            $mensagens[] = "✅ '$nome' resgatado com sucesso!";
            $sucesso = true;
        } else {
            $mensagens[] = "❌ '$nome' precisa de $pontos_necessarios pontos. Você tem apenas $pontos_atual.";
        }
    } else {
        $stmt->close();
        $mensagens[] = "❌ Prémio com ID $pid não encontrado.";
    }
    
}

foreach ($mensagens as $m) {
    echo "<p>$m</p>";
}

echo "<p><strong>Pontos restantes:</strong> $pontos_atual</p>";

if (!$sucesso) {
    echo "<p><strong>Nenhum prémio foi resgatado.</strong></p>";
}
?>
