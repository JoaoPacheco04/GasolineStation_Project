<?php
session_start();
include('db_connection.php');

if (!isset($_SESSION['user_id']) || $_SESSION['tipo'] !== 'Gerente_Posto') {
    header("Location: login.php");
    exit;
}

if (!isset($_GET['id'])) {
    echo "Bomba ID não fornecido";
    exit;
}

$idBomba = intval($_GET['id']);

// Get bomba info with latest fuel level
$query = "
    SELECT b.idBomba, b.estado, s.idSensor, re.nivelCombustivel, re.data
    FROM bomba b
    JOIN sensor s ON b.idSensor = s.idSensor
    LEFT JOIN registro_estado re ON s.idSensor = re.idSensor
    WHERE b.idBomba = ?
    ORDER BY re.data DESC
    LIMIT 1
";
$stmt = $conn->prepare($query);
$stmt->bind_param('i', $idBomba);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo "Bomba não encontrada";
    exit;
}

$bomba = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8" />
    <title>Detalhes da Bomba <?= $bomba['idBomba'] ?></title>
    <link rel="stylesheet" href="./css/GerentePostoBase.css" />
</head>
<body>
    <div class="container">
        <h1>Detalhes da Bomba <?= $bomba['idBomba'] ?></h1>
        <p><strong>Estado:</strong> <?= htmlspecialchars($bomba['estado']) ?></p>
        <p><strong>Nível de Combustível:</strong> <?= $bomba['nivelCombustivel'] !== null ? round($bomba['nivelCombustivel'], 2) . '%' : 'Sem dados' ?></p>
        <p><strong>Última Atualização:</strong> <?= $bomba['data'] ?? 'Sem dados' ?></p>

        <!-- Form to change estado -->
        <form id="updateEstadoForm" method="POST" action="update_bomba_estado.php">
            <input type="hidden" name="idBomba" value="<?= $bomba['idBomba'] ?>" />
            <label for="estado">Alterar Estado:</label>
            <select name="estado" id="estado">
                <option value="ativo" <?= $bomba['estado'] === 'ativo' ? 'selected' : '' ?>>Ativo</option>
                <option value="inativo" <?= $bomba['estado'] === 'inativo' ? 'selected' : '' ?>>Inativo</option>
            </select>
            <button type="submit">Salvar</button>
        </form>

        <p><a href="GerentePostoBase.php">Voltar ao Dashboard</a></p>
    </div>

    <script>
    // Ajax submit to update estado without page reload
    document.getElementById('updateEstadoForm').addEventListener('submit', async function(e) {
        e.preventDefault();

        const formData = new FormData(this);

        const response = await fetch('update_bomba_estado.php', {
            method: 'POST',
            body: formData
        });

        const result = await response.json();

        if (result.success) {
            alert(result.message);
            location.reload(); // Refresh to get updated info
        } else {
            alert('Erro: ' + (result.message || 'Não foi possível atualizar'));
        }
    });
    </script>
</body>
</html>
