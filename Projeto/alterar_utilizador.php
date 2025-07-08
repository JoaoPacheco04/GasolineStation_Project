<?php
include 'db_connection.php';

// Atualizar dados se o formulário foi submetido
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = intval($_POST['id']);
    $nome = $_POST['nome'];
    $morada = $_POST['morada'];
    $nif = $_POST['nif'];
    $email = $_POST['email'];
    $ativo = ($_POST['ativo'] === 'Ativo') ? 'Ativo' : 'Inativo';

    if (!empty($_POST['password'])) {
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
        $stmt = $conn->prepare("UPDATE Utilizador SET nome = ?, morada = ?, NIF = ?, email = ?, password = ?, ativo = ? WHERE id = ?");
        $stmt->bind_param("ssssssi", $nome, $morada, $nif, $email, $password, $ativo, $id);
    } else {
        $stmt = $conn->prepare("UPDATE Utilizador SET nome = ?, morada = ?, NIF = ?, email = ?, ativo = ? WHERE id = ?");
        $stmt->bind_param("sssssi", $nome, $morada, $nif, $email, $ativo, $id);
    }

  if ($stmt->execute()) {
    echo "<p class='message success'>Utilizador atualizado com sucesso.</p>";
} else {
    echo "<p class='message error'>Erro: " . htmlspecialchars($stmt->error) . "</p>";
}


    $stmt->close();
}

// Se um utilizador foi selecionado por GET
if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $stmt = $conn->prepare("SELECT id, nome, morada, NIF, email, ativo FROM Utilizador WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->bind_result($uid, $nome, $morada, $nif, $email, $ativo);
    $stmt->fetch();
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <title>Alterar Utilizador</title>
    <link rel="stylesheet" href="./css/alterar_utilizador.css" />
</head>
<body>
    <div class="container">
        <h2>Selecionar Utilizador</h2>
        <form method="get">
            <label for="id">ID do Utilizador:</label>
            <select name="id" id="id">
                <?php
                $result = $conn->query("SELECT id, nome FROM Utilizador");
                while ($row = $result->fetch_assoc()) {
                    $selected = (isset($id) && $id == $row['id']) ? 'selected' : '';
                    echo "<option value='{$row['id']}' $selected>{$row['id']} - {$row['nome']}</option>";
                }
                ?>
            </select>
            <input type="submit" value="Editar">
        </form>

        <?php if (isset($uid)): ?>
            <h2>Editar Dados de <?= htmlspecialchars($nome) ?></h2>
            <form method="post">
                <input type="hidden" name="id" value="<?= $uid ?>">

                <label>Nome:</label>
                <input type="text" name="nome" value="<?= htmlspecialchars($nome) ?>">

                <label>Morada:</label>
                <input type="text" name="morada" value="<?= htmlspecialchars($morada) ?>">

                <label>NIF:</label>
                <input type="text" name="nif" value="<?= htmlspecialchars($nif) ?>">

                <label>Email:</label>
                <input type="email" name="email" value="<?= htmlspecialchars($email) ?>">

                <label>Nova Password (deixe em branco para manter):</label>
                <input type="password" name="password">

                <label>Estado do Utilizador:</label>
                <select name="ativo">
                    <option value="Ativo" <?= ($ativo === 'Ativo') ? 'selected' : '' ?>>Ativo</option>
                    <option value="Inativo" <?= ($ativo === 'Inativo') ? 'selected' : '' ?>>Inativo</option>
                </select><br><br>

                <input type="submit" value="Guardar Alterações">
            </form>
        <?php endif; ?>
    </div>
    
      <a href="adminbase.php" class="voltar-fixo">⟵ Voltar</a>
</body>
</html>

<?php $conn->close(); ?>
