<?php
session_start();
include('../db_connection.php');

if (!isset($_SESSION['user_id']) || $_SESSION['tipo'] !== 'Operador') {
    header("Location: ../index.html");
    exit;
}

// Fetch clients (role_id = 2 = Cliente)
$clientes = $conn->query("SELECT id, nif FROM Utilizador WHERE role_id = 2");
$premios = $conn->query("SELECT id, nome, pontos_necessarios FROM Premio");
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <title>Registar Prémio</title>
    <link rel="stylesheet" href="../css/operadorbase.css">
    <link rel="stylesheet" href="./registarPremio.css">
</head>
<body>
<div class="container">
    <h1>Registar Prémio</h1>

    <form action="processarPremio.php" method="post" id="premioForm">
        <label for="cliente_input">Selecionar Cliente (NIF ou ID):</label>
        <input list="clientes" name="cliente_input" id="cliente_input" autocomplete="off" required>
        <input type="hidden" name="id_client" id="id_client" required>

        <datalist id="clientes">
            <?php while ($c = $clientes->fetch_assoc()): ?>
                <option value="<?= htmlspecialchars($c['id'] . ' - ' . $c['nif']) ?>">
            <?php endwhile; ?>
        </datalist>

        <label for="premiosGrid">Selecionar Prémio(s):</label>
        <div class="premios-grid" id="premiosGrid">
            <?php if ($premios->num_rows > 0): ?>
                <?php while ($p = $premios->fetch_assoc()): ?>
                    <div class="premio-button" 
                         data-id="<?= htmlspecialchars($p['id']) ?>" 
                         tabindex="0"   
                         role="button">
                        <?= htmlspecialchars($p['nome']) ?><br>
                        <small>(<?= htmlspecialchars($p['pontos_necessarios']) ?> pontos)</small>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p>Nenhum prémio disponível.</p>
            <?php endif; ?>
        </div>

        <input type="hidden" name="premio_id" id="premio_id" required>

        <button type="submit">Oferecer Prémio</button>
    </form>
</div>

<script src="./registarPremio.js"></script>
<script>
// Extract client ID from input before submit
document.getElementById('premioForm').addEventListener('submit', function(e) {
    const input = document.getElementById('cliente_input').value.trim();
    const idMatch = input.match(/^(\d+)\s*-/); // expects "ID - NIF"
    if (idMatch) {
        document.getElementById('id_client').value = idMatch[1];
    } else {
        alert("Por favor, selecione um cliente válido.");
        e.preventDefault();
        return;
    }

    // Also validate at least one prize selected
    if (!document.getElementById('premio_id').value) {
        alert("Por favor, selecione pelo menos um prémio.");
        e.preventDefault();
    }
});
</script>
~

      <a href="operadorbase.php" class="voltar-fixo">⟵ Voltar</a>
</body>
</html>
