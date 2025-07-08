<?php
session_start();
include 'db_connection.php';
if (!isset($_SESSION['user_id']) || !isset($_SESSION['tipo'])) {
    header("Location: login.php");
    exit;
}

switch ($_SESSION['tipo']) {
    case 'Administrador':
        header("Location: adminbase.php");
        break;
    case 'Cliente':
        header("Location: clientebase.php");
        break;
    case 'Funcionario_Administrativo':
        header("Location: funcadminbase.php");
        break;
    case 'Funcionario_Servicos':
        header("Location: funcservicobase.php");
        break;
    case 'Gerente_Posto':
        header("Location: GerentePostoBase.php");
        break;
    case 'Operador':
        header("Location: ./Operador/operadorbase.php");
        break;
    default:
        echo "Tipo de utilizador desconhecido.";
}

exit;
?>
