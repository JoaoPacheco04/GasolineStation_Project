<?php
session_start();
include('../db_connection.php');

if (!isset($_SESSION['user_id']) || $_SESSION['tipo'] !== 'Operador') {
    header("Location: ../index.html");
    exit;
}

// Obter e validar dados
$cliente_input = $_POST['cliente_input']; // formato "ID - NIF"
$metodo_pagamento = $_POST['metodo_pagamento'];
$total = floatval($_POST['total_hidden']);
$produtos_json = $_POST['produtos_json'];
$data = date("Y-m-d");

// Extrair ID do cliente
$parts = explode(" - ", $cliente_input);
$id_client = intval(trim($parts[0]));

// Verificar limite de 5 transações digitais
if ($metodo_pagamento !== 'Dinheiro') {
    $stmt = $conn->prepare("
        SELECT COUNT(*) 
        FROM Venda 
        WHERE id_client = ? AND metodo_pagamento != 'Dinheiro' AND data = ?
    ");
    $stmt->bind_param("is", $id_client, $data);
    $stmt->execute();
    $stmt->bind_result($digital_count);
    $stmt->fetch();
    $stmt->close();

    if ($digital_count >= 5) {
        echo "⚠️ Limite de 5 transações digitais por dia atingido!";
        exit;
    }
}

// Inserir venda
$stmt = $conn->prepare("
    INSERT INTO Venda (id_cliente, id_operador, data_venda, total, metodo_pagamento) 
    VALUES (?, ?, ?, ?, ?)
");
$stmt->bind_param("iisds", $id_client, $_SESSION['user_id'], $data, $total, $metodo_pagamento);
$stmt->execute();
$id_venda = $stmt->insert_id;
$stmt->close();

// Inserir produtos vendidos
$produtos = json_decode($produtos_json, true);
// Insert sold products with quantity
$stmt = $conn->prepare("
    INSERT INTO venda_produto (id_venda, id_produto , quantidade, preco_unitario) 
    VALUES (?, ?, ?, ?)
");

foreach ($produtos as $produto) {
    $id = intval($produto['id']);
    $preco = floatval($produto['preco']);
    $quantidade = intval($produto['quantidade']);

    // Check stock
    $stmtCheck = $conn->prepare("SELECT stock FROM produto_loja WHERE id = ?");
    $stmtCheck->bind_param("i", $id);
    $stmtCheck->execute();
    $stmtCheck->bind_result($stock_atual);
    $stmtCheck->fetch();
    $stmtCheck->close();

    if ($stock_atual < $quantidade) {
        echo "❌ Estoque insuficiente para o produto ID $id. Disponível: $stock_atual, solicitado: $quantidade.";
        exit;
    }

    // Insert sale
    $stmt->bind_param("iiid", $id_venda, $id, $quantidade, $preco);
    $stmt->execute();

    // Update stock
    $stmtStock = $conn->prepare("UPDATE produto_loja SET stock = stock - ? WHERE id = ?");
    $stmtStock->bind_param("ii", $quantidade, $id);
    $stmtStock->execute();
    $stmtStock->close();
}

$stmt->close();

// Calcular pontos (1 ponto por cada 10€)
$pontos = floor($total / 10);

// Check if client has loyalty card
$stmt = $conn->prepare("SELECT id_cartao FROM cartao_fidelidade WHERE id_client = ?");
$stmt->bind_param("i", $id_client);
$stmt->execute();
$stmt->store_result();



if ($stmt->num_rows > 0) {
    $stmt->bind_result($id_cartao);
    $stmt->fetch();
    $stmt->close();

  // Insert movement
$stmt_mov = $conn->prepare("INSERT INTO movimento_cartao (id_cartao, tipo, pontos, data_movimento) VALUES (?, ?, ?, NOW())");
$tipo_mov = 'acumular'; // ENUM must match exactly
$stmt_mov->bind_param("isi", $id_cartao, $tipo_mov, $pontos);
$stmt_mov->execute();
$stmt_mov->close();

// Update card points
$stmt = $conn->prepare("UPDATE cartao_fidelidade SET pontos = pontos + ? WHERE id_cartao = ?");
$stmt->bind_param("ii", $pontos, $id_cartao);
$stmt->execute();
$stmt->close();



    echo "✅ Venda registada com sucesso. Pontos acumulados: $pontos";
} else {
    echo "✅ Venda registada com sucesso. (Cliente sem cartão fidelidade)";
}

