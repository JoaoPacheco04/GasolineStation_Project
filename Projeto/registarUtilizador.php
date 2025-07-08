<?php
include 'db_connection.php';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Only run this block if the form was submitted via POST

    // Get form data safely
    $nome = $_POST['nome'];
    $morada = $_POST['morada'];
    $NIF = $_POST['nif'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    // Generate login
    $login = strtolower(explode(' ', $nome)[0]) . rand(100, 999);

    // Insert into DB
    $sql = "INSERT INTO Utilizador (nome, morada, NIF, email, login, password)
            VALUES ('$nome', '$morada', '$NIF', '$email', '$login', '$password')";

    if ($conn->query($sql) === TRUE) {
        echo "Utilizador registado com sucesso! Login: $login";
    } else {
        echo "Erro: " . $conn->error;
    }
}

$conn->close();
?>