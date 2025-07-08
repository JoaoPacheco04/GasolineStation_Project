<?php
session_start();
include './db_connection.php';

if (!isset($_SESSION['user_id']) || $_SESSION['tipo'] !== 'Funcionario_Administrativo') {
    http_response_code(403);
    echo json_encode([]);
    exit;
}

$date = $_GET['date'] ?? '';
if (!$date) {
    echo json_encode([]);
    exit;
}

// Query to get employees available on $date (not in Indisponibilidade on that date)
$sql = "
SELECT u.id, u.nome
FROM Utilizador u
JOIN role r ON u.role_id = r.id
WHERE r.nome = 'Funcionario_Servicos'
AND u.id NOT IN (
    SELECT idFuncionario FROM Indisponibilidade WHERE data = ?
)
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $date);
$stmt->execute();
$result = $stmt->get_result();

$funcionarios = [];
while ($row = $result->fetch_assoc()) {
    $funcionarios[] = $row;
}

header('Content-Type: application/json');
echo json_encode($funcionarios);
