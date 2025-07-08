<?php
session_start();
include('db_connection.php'); // Your DB connection

// Check permission: only Gerente_Posto can update
if (!isset($_SESSION['user_id']) || $_SESSION['tipo'] !== 'Gerente_Posto') {
    http_response_code(403);
    echo json_encode(['error' => 'Access denied']);
    exit;
}

if (!isset($_POST['idBomba']) || !isset($_POST['estado'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing parameters']);
    exit;
}

$idBomba = intval($_POST['idBomba']);
$novoEstado = $_POST['estado']; // Should be 'ativo' or 'inativo'

// Validate estado
if (!in_array($novoEstado, ['ativo', 'inativo'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Estado inválido']);
    exit;
}

// Get current estado
$sql = "SELECT estado, idSensor FROM bomba WHERE idBomba = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $idBomba);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows === 0) {
    http_response_code(404);
    echo json_encode(['error' => 'Bomba não encontrada']);
    exit;
}

$bomba = $result->fetch_assoc();
$estadoAtual = $bomba['estado'];
$idSensor = $bomba['idSensor'];

if ($estadoAtual !== $novoEstado) {
    // Update estado
    $updateSql = "UPDATE bomba SET estado = ? WHERE idBomba = ?";
    $stmt2 = $conn->prepare($updateSql);
    $stmt2->bind_param('si', $novoEstado, $idBomba);
    $stmt2->execute();

    // Insert notification
    $mensagem = "Estado da bomba $idBomba mudou de $estadoAtual para $novoEstado";
    $insertSql = "INSERT INTO notificacao_bomba (idSensor, mensagem) VALUES (?, ?)";
    $stmt3 = $conn->prepare($insertSql);
    $stmt3->bind_param('is', $idSensor, $mensagem);
    $stmt3->execute();

    echo json_encode(['success' => true, 'message' => $mensagem]);
} else {
    echo json_encode(['success' => false, 'message' => 'Estado não mudou']);
}
?>
