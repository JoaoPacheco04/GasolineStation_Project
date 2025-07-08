<?php
session_start();
include('../db_connection.php');

$mensagem = "";
$form_submitted = false;

// Load client data for datalist
$clientes = $conn->query("SELECT id, nif FROM Utilizador WHERE role_id = 2");
$clienteData = [];
while ($row = $clientes->fetch_assoc()) {
    $clienteData[] = $row['id'] . ' - ' . $row['nif'];
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $form_submitted = true;
    $input = $_POST['cliente_input'] ?? '';
    preg_match('/^(\d+)\s*-\s*(\d+)$/', $input, $matches);

    if ($matches && count($matches) === 3) {
        $id = (int)$matches[1];
        $nif = (int)$matches[2];

        // Verify user exists
        $stmt = $conn->prepare("SELECT id FROM utilizador WHERE id = ? AND nif = ?");
        $stmt->bind_param("ii", $id, $nif);
        $stmt->execute();
        $stmt->bind_result($id_client);
        if ($stmt->fetch()) {
            $stmt->close();

            // Check if they already have a card
            $check = $conn->prepare("SELECT id_cartao FROM cartao_fidelidade WHERE id_client = ?");
            $check->bind_param("i", $id_client);
            $check->execute();
            $check->store_result();

            if ($check->num_rows === 0) {
                $check->close();
                // Register new card
                $insert = $conn->prepare("INSERT INTO cartao_fidelidade (id_client, pontos) VALUES (?, 0)");
                $insert->bind_param("i", $id_client);
                if ($insert->execute()) {
                    $mensagem = "🎉 Bem-vindo à família Gazoline! Cartão de fidelidade criado com sucesso!";
                } else {
                    $mensagem = "❌ Erro ao registar o cartão.";
                }
                $insert->close();
            } else {
                $mensagem = "⚠️ Este cliente já possui um cartão de fidelidade.";
                $check->close();
            }
        } else {
            $mensagem = "❌ Cliente não encontrado. Verifique o ID e o NIF.";
            $stmt->close(); 
        }

    } else {
        $mensagem = "❌ Formato inválido. Use a opção sugerida (ex: 123 - 987654321).";
    }
}
?>

<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <title>Registar Cartão de Fidelidade</title>
    <link rel="stylesheet" href="entradaStock.css">
    <link rel="stylesheet" href="registarCartao.css">
</head>
<body>
    <div class="container">
        <h1>Registar Cartão de Fidelidade</h1>
        <form method="post">
            <label for="cliente_input">Selecione Cliente (ID - NIF):</label>
            <input list="clientes" name="cliente_input" id="cliente_input" required placeholder="Ex: 123 - 987654321">
            <datalist id="clientes">
                <?php foreach ($clienteData as $val): ?>
                    <option value="<?= htmlspecialchars($val) ?>">
                <?php endforeach; ?>
            </datalist>
            <button type="submit">Criar Cartão</button>
        </form>

        <?php if ($form_submitted): ?>
            <div class="mensagem">
                <?= htmlspecialchars($mensagem) ?>
            </div>
        <?php endif; ?>
    </div>
    
      <a href="operadorbase.php" class="voltar-fixo">⟵ Voltar</a>
</body>
</html>
