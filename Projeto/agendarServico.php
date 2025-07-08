<?php
session_start();
include './db_connection.php';

// Ensure the user is a logged-in client
if (!isset($_SESSION['user_id']) || $_SESSION['tipo'] !== 'Cliente') {
    header("Location: login.php");
    exit;
}

$idCliente = $_SESSION['user_id'];

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $servico = $_POST['servico'] ?? '';
    $data = $_POST['data'] ?? '';
    $hora = $_POST['hora'] ?? '';

    if (!empty($servico) && !empty($data) && !empty($hora)) {
        $stmt = $conn->prepare("SELECT id FROM Servico WHERE nome = ?");
        $stmt->bind_param("s", $servico);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $idServico = $row['id'];

            $insert = $conn->prepare("INSERT INTO Servico_Agendado (data, hora, estado, idServico, idCliente) VALUES (?, ?, 'Pendente', ?, ?)");
            $insert->bind_param("ssii", $data, $hora, $idServico, $idCliente);
            if ($insert->execute()) {
                echo "<script>alert('Serviço agendado com sucesso!'); window.location.href='clientebase.php';</script>";
                exit;
            } else {
                $mensagem = "Erro ao agendar serviço.";
            }
        } else {
            $mensagem = "Serviço não encontrado.";
        }
    } else {
        $mensagem = "Preencha todos os campos.";
    }
}
?>

<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Agendamento de Serviços</title>
    <link rel="icon" href="./favicons/a.png" />
    <link rel="stylesheet" href="./css/agendarServiço.css">
</head>
<body>
    <div class="header">
        <img src="./favicons/icon1.png" alt="Icon" class="icon1" />
        <h1>Gazóline</h1>
    </div>

    <div class="container">
        <h2>Agendamento de Serviços</h2>

        <form method="POST" action="">
            <label for="servico">Tipo de Serviço</label>
            <select id="servico" name="servico" required>
                <option value="">-- Selecione um serviço --</option>
                <?php
                $sql = "SELECT nome FROM Servico";
                $result = $conn->query($sql);
                if ($result && $result->num_rows > 0) {
                    while($row = $result->fetch_assoc()) {
                        echo "<option value='" . htmlspecialchars($row['nome']) . "'>" . htmlspecialchars($row['nome']) . "</option>";
                    }
                }
                ?>
            </select>

            <label for="data">Data</label>
            <input type="date" id="data" name="data" required>

            <label for="hora">Hora</label>
            <input type="time" id="hora" name="hora" required>

            <button type="submit">Marcar</button>
        </form>

        <?php if (isset($mensagem)) {
            echo "<div class='mensagem'>$mensagem</div>";
        } ?>
    </div>

    <a href="clientebase.php" class="voltar-fixo">Voltar</a>
</body>
</html>