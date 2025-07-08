<?php
session_start();
include('../db_connection.php');

if (!isset($_SESSION['user_id']) || $_SESSION['tipo'] !== 'Operador') {
    header("Location: ../index.html");
    exit;
}

$mensagem = "";
$editMode = false;
$nome = $descricao = $categoria = "";
$preco = 0.0;
$stock = 0;

// Handle reset to add new item
if (isset($_GET['reset'])) {
    $editMode = false;
} elseif ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nome = $_POST['nome'] ?? '';
    $descricao = $_POST['descricao'] ?? '';
    $preco = isset($_POST['preco']) ? floatval($_POST['preco']) : 0.0;
    $stock = isset($_POST['stock']) ? intval($_POST['stock']) : 0;
    $categoria = $_POST['categoria'] ?? '';
    $modo = $_POST['modo'] ?? '';

    if ($nome && $descricao && $preco && $stock && $categoria) {
        if ($modo === "update") {
            // Add to existing stock
            $stmt = $conn->prepare("SELECT stock FROM produto_loja WHERE nome = ?");
            $stmt->bind_param("s", $nome);
            $stmt->execute();
            $stmt->bind_result($existingStock);
            $stmt->fetch();
            $stmt->close();

            $newStock = $existingStock + $stock;

            $update = $conn->prepare("UPDATE produto_loja SET descricao=?, preco=?, stock=?, categoria=? WHERE nome=?");
            $update->bind_param("sdiss", $descricao, $preco, $newStock, $categoria, $nome);

            if ($update->execute()) {
                $mensagem = "✅ Produto atualizado com sucesso (stock adicionado).";
            } else {
                $mensagem = "❌ Erro ao atualizar o produto.";
            }
            $update->close();
            $editMode = true;
        } else {
            $insert = $conn->prepare("INSERT INTO produto_loja (nome, descricao, preco, stock, categoria) VALUES (?, ?, ?, ?, ?)");
            $insert->bind_param("ssdis", $nome, $descricao, $preco, $stock, $categoria);
            if ($insert->execute()) {
                $mensagem = "✅ Produto adicionado com sucesso.";
            } else {
                $mensagem = "❌ Erro ao adicionar o produto.";
            }
            $insert->close();
        }
    } else {
        $mensagem = "❌ Por favor, preencha todos os campos corretamente.";
    }
} elseif ($_SERVER["REQUEST_METHOD"] == "GET" && isset($_GET['produto'])) {
    $nomeSelecionado = $_GET['produto'];
    $stmt = $conn->prepare("SELECT nome, descricao, preco, stock, categoria FROM produto_loja WHERE nome = ?");
    $stmt->bind_param("s", $nomeSelecionado);
    $stmt->execute();
    $stmt->bind_result($nome, $descricao, $preco, $stock, $categoria);
    if ($stmt->fetch()) {
        $editMode = true;
    }
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <title>Entrada de Produto em Stock</title>
    <link rel="stylesheet" href="entradaStock.css">
</head>
<body>
    <h1>Entrada de Produto em Stock</h1>

    <div class="form-container">
        <!-- Produto Selection -->
        <form method="get" action="">
            <label for="produto">Selecionar Produto:</label>
            <select id="produto" name="produto" required>
                <option value="">-- Selecione --</option>
                <?php
                $result = $conn->query("SELECT nome FROM produto_loja ORDER BY nome ASC");
                while ($row = $result->fetch_assoc()) {
                    $selected = ($row['nome'] == ($nome ?? '')) ? 'selected' : '';
                    echo "<option value=\"{$row['nome']}\" $selected>{$row['nome']}</option>";
                }
                ?>
            </select>
            <button type="submit">Editar</button>
        </form>

        <!-- Formulário Principal -->
        <form method="post" action="">
            <input type="hidden" name="modo" value="<?php echo $editMode ? 'update' : 'insert'; ?>">

            <label for="nome">Nome do Produto</label>
            <input type="text" id="nome" name="nome" value="<?php echo htmlspecialchars($nome); ?>" required <?php if ($editMode) echo "readonly"; ?>>

            <label for="descricao">Descrição</label>
            <input type="text" id="descricao" name="descricao" value="<?php echo htmlspecialchars($descricao); ?>" required>

            <label for="preco">Preço (€)</label>
            <input type="number" step="0.01" id="preco" name="preco" value="<?php echo htmlspecialchars($preco); ?>" required>

            <label for="stock"><?php echo $editMode ? "Adicionar ao Stock Atual" : "Quantidade em Stock"; ?></label>
            <input type="number" id="stock" name="stock" value="0" required>

            <label for="categoria">Categoria</label>
            <select id="categoria" name="categoria" required>
                <option value="">-- Selecione --</option>
                <option value="Bebida" <?php if ($categoria == "Bebida") echo "selected"; ?>>Bebida</option>
                <option value="Snack" <?php if ($categoria == "Snack") echo "selected"; ?>>Snack</option>
                <option value="Outro" <?php if ($categoria == "Outro") echo "selected"; ?>>Outro</option>
            </select>

            <button type="submit"><?php echo $editMode ? "Atualizar Produto" : "Adicionar Produto"; ?></button>
        </form>

        <!-- Botão para Adicionar Novo Produto -->
        <form method="get" action="" style="margin-top: 15px;">
            <button type="submit" name="reset" value="1">➕ Adicionar Novo Produto</button>
        </form>

        <!-- Mensagem -->
        <?php if (!empty($mensagem)): ?>
            <div class="mensagem"><?php echo $mensagem; ?></div>
        <?php endif; ?>
    </div>

    
      <a href="operadorbase.php" class="voltar-fixo">⟵ Voltar</a>
</body>
</html>
