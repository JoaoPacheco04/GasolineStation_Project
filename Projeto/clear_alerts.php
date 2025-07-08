<?php
session_start();
include('db_connection.php');

if (!isset($_SESSION['user_id']) || $_SESSION['tipo'] !== 'Gerente_Posto') {
    header("Location: login.php");
    exit;
}

$sql = "DELETE FROM notificacao_bomba";  // delete all alerts
$conn->query($sql);

header("Location: GerentePostoBase.php");
exit;
?>
