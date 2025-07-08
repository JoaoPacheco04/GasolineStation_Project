<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['tipo'] !== 'Funcionario_Administrativo') {
    header("Location: login.php");
    exit;
}

include './db_connection.php';

$mensagem = "";
$mensagem_erro = false;

// Fetch all materials from the Material table for the dropdown
$sql_materiais = "SELECT idMaterial, nome FROM Material ORDER BY nome";
$result_materiais = $conn->query($sql_materiais);
if (!$result_materiais) {
    die("Erro ao buscar materiais: " . $conn->error);
}

// Process form submission
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['servico_id'], $_POST['material_id'], $_POST['quantidade'])) {
    $servico_id = intval($_POST['servico_id']);
    $material_ids = $_POST['material_id'];
    $quantidades = $_POST['quantidade'];

    $sql_inserir = "INSERT INTO Material_Servico_Agendado (idAgendamento, idMaterial, quantidade) VALUES (?, ?, ?)";

    $conn->begin_transaction();

    try {
        $stmt_insere = $conn->prepare($sql_inserir);

        foreach ($material_ids as $i => $idMaterial) {
            $idMaterial = intval($idMaterial);
            $quantidade = max(1, intval($quantidades[$i]));

            // Skip invalid or zero values
            if ($idMaterial <= 0) continue;

            $stmt_insere->bind_param("iii", $servico_id, $idMaterial, $quantidade);
            $stmt_insere->execute();
        }

        $conn->commit();
        $mensagem = "✅ Materiais registrados com sucesso!";
    } catch (Exception $e) {
        $conn->rollback();
        $mensagem = "❌ Erro: " . $e->getMessage();
        $mensagem_erro = true;
    }

    if (isset($stmt_insere)) $stmt_insere->close();
}

// Fetch scheduled services for select dropdown
$sql_servicos = "
    SELECT sa.idAgendamento, s.nome AS descricao, DATE_FORMAT(sa.data, '%d/%m/%Y') AS data_formatada
    FROM Servico_Agendado sa
    JOIN Servico s ON sa.idServico = s.id
    ORDER BY sa.data DESC
";
$result_servicos = $conn->query($sql_servicos);
if (!$result_servicos) {
    die("Erro ao buscar serviços: " . $conn->error);
}
?>

<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8" />
    <title>Registrar Materiais no Serviço Agendado</title>
    <link rel="stylesheet" href="./css/formstyle.css" />
    <style>
        body {
            background: linear-gradient(to right, #483D8B, #E6E6FA);
            font-family: Arial, sans-serif;
            display: flex;
            justify-content: center;
            padding: 40px 20px;
            min-height: 100vh;
        }
        .main-container {
            background: white;
            padding: 25px 30px;
            border-radius: 12px;
            box-shadow: 0 8px 16px rgba(0,0,0,0.25);
            width: 100%;
            max-width: 500px;
        }
        h1 {
            color: #483D8B;
            text-align: center;
        }
        label {
            display: block;
            margin-top: 15px;
            font-weight: bold;
        }
        select, input[type=number] {
            width: 100%;
            padding: 8px;
            margin-top: 6px;
            border-radius: 6px;
            border: 1px solid #ccc;
        }
        .material-group {
            margin-bottom: 10px;
            border-bottom: 1px dashed #ccc;
            padding-bottom: 10px;
        }
        button {
            margin-top: 20px;
            background-color: #6A5ACD;
            color: white;
            border: none;
            padding: 12px;
            width: 100%;
            font-weight: bold;
            border-radius: 8px;
            cursor: pointer;
        }
        button:hover {
            background-color: #5548a1;
        }
        .mensagem {
            margin: 10px 0;
            text-align: center;
            font-weight: bold;
        }
        .mensagem.erro {
            color: red;
        }
        .mensagem:not(.erro) {
            color: green;
        }
    </style>
</head>
<body>
<div class="main-container">
    <h1>Registrar Materiais no Serviço</h1>

    <?php if ($mensagem): ?>
        <div class="mensagem <?= $mensagem_erro ? 'erro' : '' ?>">
            <?= htmlspecialchars($mensagem) ?>
        </div>
    <?php endif; ?>

    <form method="post">
        <label for="servico_id">Serviço Agendado:</label>
        <select name="servico_id" required>
            <option value="">-- Escolha um serviço --</option>
            <?php while ($row = $result_servicos->fetch_assoc()): ?>
                <option value="<?= $row['idAgendamento'] ?>">
                    <?= htmlspecialchars($row['descricao']) . " ({$row['data_formatada']})" ?>
                </option>
            <?php endwhile; ?>
        </select>

        <div id="materiais-container">
            <div class="material-group">
                <label>Material:</label>
                <select name="material_id[]" required>
                    <option value="">-- Escolha um material --</option>
                    <?php
                    $result_materiais->data_seek(0); // reset pointer
                    while ($mat = $result_materiais->fetch_assoc()): ?>
                        <option value="<?= $mat['idMaterial'] ?>">
                            <?= htmlspecialchars($mat['nome']) ?>
                        </option>
                    <?php endwhile; ?>
                </select>

                <label>Quantidade:</label>
                <input type="number" name="quantidade[]" min="1" value="1" required />
            </div>
        </div>

        <button type="button" onclick="adicionarMaterial()">➕ Adicionar outro material</button>
        <button type="submit">Registrar Materiais</button>
    </form>

    <form action="funcadminbase.php" method="get">
        <button type="submit">Voltar</button>
    </form>
</div>

<script>
function adicionarMaterial() {
    const container = document.getElementById('materiais-container');
    const grupo = document.createElement('div');
    grupo.className = 'material-group';

    // Fetch materials options as a string from first dropdown to reuse
    const firstSelect = container.querySelector('select[name="material_id[]"]');
    const optionsHTML = firstSelect ? firstSelect.innerHTML : '<option value="">-- Escolha um material --</option>';

    grupo.innerHTML = `
        <label>Material:</label>
        <select name="material_id[]" required>
            ${optionsHTML}
        </select>

        <label>Quantidade:</label>
        <input type="number" name="quantidade[]" min="1" value="1" required />
    `;
    container.appendChild(grupo);
}
</script>
</body>
</html>
