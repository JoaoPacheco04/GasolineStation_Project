<?php
session_start();
include('../db_connection.php');

if (!isset($_SESSION['user_id']) || $_SESSION['tipo'] !== 'Operador') {
    header("Location: ../index.html");
    exit;
}

$produtos = $conn->query("SELECT id, nome, preco FROM produto_loja");

$clientes = $conn->query("SELECT id, nif FROM Utilizador WHERE role_id = 2");
$clienteData = [];
while ($row = $clientes->fetch_assoc()) {
    $clienteData[] = $row['id'] . ' - ' . $row['nif'];
}
?>

<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <title>Registar Venda</title>
    <link rel="stylesheet" href="venda.css">
</head>
<body>
    <h1>Registar Venda</h1>
    <form action="processarVenda.php" method="post">
        

    <div class="container">
        <!-- LEFT: Client info + Products -->
        <div class="left-section">
            <!-- Client and Payment -->
            <div class="form-section">
                <label for="cliente_input">NIF ou ID do Cliente:</label>
                <input list="clientes" name="cliente_input" id="cliente_input" required>
                <datalist id="clientes">
                    <?php foreach ($clienteData as $val): ?>
                        <option value="<?= $val ?>">
                    <?php endforeach; ?>
                </datalist>

                <label for="metodo_pagamento">Método de Pagamento:</label>
                <select name="metodo_pagamento" id="metodo_pagamento" required>
                    <option value="Dinheiro">Dinheiro</option>
                    <option value="Cartao">Cartão</option>
                </select>
            </div>

            <!-- Product Buttons -->
            <div class="produtos-section">
                <h3>Produtos:</h3>
                <div class="produtos-grid">
                    <?php while ($p = $produtos->fetch_assoc()): ?>
                        <button type="button" onclick="adicionarProduto(<?= $p['id'] ?>, '<?= $p['nome'] ?>', <?= $p['preco'] ?>)">
                            <?= $p['nome'] ?><br>€<?= $p['preco'] ?>
                        </button>
                    <?php endwhile; ?>
                </div>
            </div>
        </div>

        <!-- RIGHT: Cart -->
        <div class="right-section">
            <h3>Selecionados:</h3>
            <select id="listaProdutos" size="10" style="width: 100%;"></select>
            <button type="button" onclick="removerProduto()">Remover Selecionado</button>
            <h3>Total: €<span id="total">0.00</span></h3>
            <input type="hidden" name="total_hidden" id="total_hidden">
            <input type="hidden" name="produtos_json" id="produtos_json">
            <button type="submit">Finalizar Venda</button>
        </div>
    </div>
</form>


    <script>
        const clientesExistentes = <?= json_encode($clienteData) ?>;
    </script>
    <script src="venda.js"></script>


</body>
</html>

