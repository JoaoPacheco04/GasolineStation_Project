<?php
include 'db_connection.php';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $id = intval($_POST['id']);
    $nome = $_POST['nome'];
    $morada = $_POST['morada'];
    $nif = $_POST['nif'];
    $email = $_POST['email'];
    $password = $_POST['password'];

    if (!empty($password)) {
        $password_hashed = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("
            UPDATE Utilizador 
            SET nome = ?, morada = ?, NIF = ?, email = ?, password = ?
            WHERE id = ?
        ");
        $stmt->bind_param("sssssi", $nome, $morada, $nif, $email, $password_hashed, $id);
    } else {
        $stmt = $conn->prepare("
            UPDATE Utilizador 
            SET nome = ?, morada = ?, NIF = ?, email = ?
            WHERE id = ?
        ");
        $stmt->bind_param("ssssi", $nome, $morada, $nif, $email, $id);
    }

    if ($stmt->execute()) {
        echo "✅ Utilizador atualizado com sucesso.";
    } else {
        echo "❌ Erro: " . $stmt->error;
    }

    $stmt->close();
}

$conn->close();
?>
