<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['tipo'] !== 'Funcionario_Servicos') {
    header("Location: login.php");
    exit;
}

include './db_connection.php';

$mensagem = "";
$mensagem_erro = false;

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['funcionario_id'], $_POST['data'], $_POST['motivo'])) {
    $funcionario_id = intval($_POST['funcionario_id']);
    $data = $_POST['data'];
    $motivo = trim($_POST['motivo']);

    // Check if already registered for that day
    $check_sql = "SELECT * FROM Indisponibilidade WHERE idFuncionario = ? AND data = ?";
    $stmt_check = $conn->prepare($check_sql);
    $stmt_check->bind_param("is", $funcionario_id, $data);
    $stmt_check->execute();
    $result_check = $stmt_check->get_result();

    if ($result_check->num_rows > 0) {
        $mensagem = "❌ Funcionário já está marcado como indisponível nesta data.";
        $mensagem_erro = true;
    } else {
        // Insert new unavailability
        $insert_sql = "INSERT INTO Indisponibilidade (idFuncionario, data, motivo) VALUES (?, ?, ?)";
        $stmt_insert = $conn->prepare($insert_sql);
        $stmt_insert->bind_param("iss", $funcionario_id, $data, $motivo);

        if ($stmt_insert->execute()) {
            $mensagem = "✅ Indisponibilidade registada com sucesso!";
            $mensagem_erro = false;
        } else {
            $mensagem = "❌ Erro ao registar indisponibilidade: " . $conn->error;
            $mensagem_erro = true;
        }

        $stmt_insert->close();
    }

    $stmt_check->close();
}

// Fetch Funcionarios for the dropdown
$sql_funcionarios = "SELECT u.id, u.nome 
                     FROM Utilizador u 
                     JOIN role r ON u.role_id = r.id 
                     WHERE r.nome = 'Funcionario_Servicos'";
$result_funcionarios = $conn->query($sql_funcionarios);
if (!$result_funcionarios) {
    die("Erro ao buscar funcionários: " . $conn->error);
}
?>

<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8" />
    <title>Registrar Indisponibilidade</title>>
<link rel="stylesheet" href="./css/registarIndisponibilidade.css" />
    
</head>
<body>
<div class="container">
    <h2>Registrar Indisponibilidade</h2>

    <?php if ($mensagem): ?>
        <div class="mensagem <?= $mensagem_erro ? 'erro' : 'sucesso' ?>"><?= htmlspecialchars($mensagem) ?></div>
    <?php endif; ?>

    <form method="POST" action="">
        <label for="funcionario_id">Funcionário:</label>
        <select id="funcionario_id" name="funcionario_id" required>
            <option value="">-- Selecione um funcionário --</option>
            <?php while ($row = $result_funcionarios->fetch_assoc()): ?>
                <option value="<?= $row['id'] ?>"><?= htmlspecialchars($row['nome']) ?></option>
            <?php endwhile; ?>
        </select>

        <label for="data">Data de Indisponibilidade:</label>
        <input type="date" id="data" name="data" required>

        <label for="motivo">Motivo (opcional):</label>
        <input type="text" id="motivo" name="motivo" maxlength="255">

        <button type="submit">Registrar Indisponibilidade</button>
    </form>
</div>

      <a href="funcservicobase.php" class="voltar-fixo">⟵ Voltar</a>
</body>
</html>
