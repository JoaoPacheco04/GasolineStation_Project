<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['tipo'] !== 'Funcionario_Servicos') {
    header("Location: login.php");
    exit;
}

include './db_connection.php';

$mensagem = "";
$mensagem_erro = false;

function dateRangeArray($start, $end) {
    $dates = [];
    $current = strtotime($start);
    $end = strtotime($end);
    while ($current <= $end) {
        $dates[] = date('Y-m-d', $current);
        $current = strtotime('+1 day', $current);
    }
    return $dates;
}

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['funcionario_id'], $_POST['data_inicio'], $_POST['data_fim'], $_POST['motivo'])) {
    $funcionario_id = intval($_POST['funcionario_id']);
    $data_inicio = $_POST['data_inicio'];
    $data_fim = $_POST['data_fim'];
    $motivo = trim($_POST['motivo']);

    if ($data_inicio > $data_fim) {
        $mensagem = "❌ Data inicial não pode ser maior que a data final.";
        $mensagem_erro = true;
    } else {
        $dates = dateRangeArray($data_inicio, $data_fim);

        // Check if any date in the range is already taken
        $placeholders = implode(',', array_fill(0, count($dates), '?'));
        $types = str_repeat('s', count($dates)); // all strings for dates

        $check_sql = "SELECT data FROM Indisponibilidade WHERE idFuncionario = ? AND data IN ($placeholders)";
        $stmt_check = $conn->prepare($check_sql);

        // Bind params dynamically: first funcionario_id (int), then dates (strings)
        $bind_params = array_merge([$funcionario_id], $dates);
        $stmt_check->bind_param('i' . $types, ...$bind_params);

        $stmt_check->execute();
        $result_check = $stmt_check->get_result();

        if ($result_check->num_rows > 0) {
            $existing_dates = [];
            while ($row = $result_check->fetch_assoc()) {
                $existing_dates[] = $row['data'];
            }
            $mensagem = "❌ Já existe indisponibilidade registada nas datas: " . implode(', ', $existing_dates);
            $mensagem_erro = true;
        } else {
            // Insert all dates in a transaction
            $conn->begin_transaction();

            $insert_sql = "INSERT INTO Indisponibilidade (idFuncionario, data, motivo) VALUES (?, ?, ?)";
            $stmt_insert = $conn->prepare($insert_sql);

            $all_success = true;
            foreach ($dates as $date) {
                $stmt_insert->bind_param("iss", $funcionario_id, $date, $motivo);
                if (!$stmt_insert->execute()) {
                    $all_success = false;
                    $mensagem = "❌ Erro ao registar indisponibilidade: " . $stmt_insert->error;
                    $mensagem_erro = true;
                    break;
                }
            }

            if ($all_success) {
                $conn->commit();
                $mensagem = "✅ Férias registadas com sucesso de $data_inicio até $data_fim!";
                $mensagem_erro = false;
            } else {
                $conn->rollback();
            }

            $stmt_insert->close();
        }
        $stmt_check->close();
    }
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
    <title>Marcar Férias</title>
 
    <link rel="stylesheet" href="./css/marcarFerias.css" />
</head>
<body>
<div class="container">
    <h2>Marcar Férias</h2>

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

        <label for="data_inicio">Data Início das Férias:</label>
        <input type="date" id="data_inicio" name="data_inicio" required>

        <label for="data_fim">Data Fim das Férias:</label>
        <input type="date" id="data_fim" name="data_fim" required>

        <label for="motivo">Motivo:</label>
        <input type="text" id="motivo" name="motivo" maxlength="255" value="Férias" required>

        <button type="submit">Registrar Férias</button>
    </form>
</div>


      <a href="funcservicobase.php" class="voltar-fixo">⟵ Voltar</a>
</body>
</html>
