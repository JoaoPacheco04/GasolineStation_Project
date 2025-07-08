<?php
session_start();
include('db_connection.php');
if (!isset($_SESSION['user_id']) || $_SESSION['tipo'] !== 'Cliente') {
    header("Location: login.php");
    exit;
}
$idCliente = $_SESSION['user_id'];
$conn->query("UPDATE notificacao_servico SET lida = 1 WHERE idCliente = $idCliente");
header("Location: clientebase.php");
exit;
?>
