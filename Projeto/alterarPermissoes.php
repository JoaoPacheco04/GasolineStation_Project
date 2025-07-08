<?php
include 'db_connection.php';

// Handle role update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_role'])) {
    $userId = $_POST['user_id'] ?? null;
    $newRoleId = $_POST['role_id'] ?? null;

    if ($userId && $newRoleId) {
        $stmt = $conn->prepare("UPDATE Utilizador SET role_id = ? WHERE id = ?");
        $stmt->bind_param("ii", $newRoleId, $userId);
        $stmt->execute();
        $stmt->close();
        $message = "Role updated successfully!";
    }
}

// Handle search
$searchTerm = $_GET['search'] ?? '';
$users = [];

if ($searchTerm !== '') {
    // Perform filtered search
    $likeTerm = '%' . $searchTerm . '%';
    $stmt = $conn->prepare("SELECT u.id, u.nome, u.email, u.NIF, u.login, u.role_id, r.nome as role_name 
                            FROM Utilizador u 
                            LEFT JOIN Role r ON u.role_id = r.id 
                            WHERE u.nome LIKE ? OR u.email LIKE ? OR u.NIF LIKE ? OR u.login LIKE ?
                            ORDER BY u.nome");
    $stmt->bind_param("ssss", $likeTerm, $likeTerm, $likeTerm, $likeTerm);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $users[] = $row;
    }
    $stmt->close();
} else {
    // Show full list
    $stmt = $conn->prepare("SELECT u.id, u.nome, u.email, u.NIF, u.login, u.role_id, r.nome as role_name 
                            FROM Utilizador u 
                            LEFT JOIN Role r ON u.role_id = r.id 
                            ORDER BY u.nome");
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $users[] = $row;
    }
    $stmt->close();
}

// Get roles for dropdown
$roleQuery = $conn->query("SELECT id, nome FROM Role ORDER BY nome");
$roles = [];
while ($row = $roleQuery->fetch_assoc()) {
    $roles[$row['id']] = $row['nome'];
}
?>

<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <title>Alterar Role do Utilizador</title>
    <link rel="stylesheet" href="./css/alterarPermissoes.css">
</head>
<body>
    <div class="container">
        <h2>Alterar Role do Utilizador</h2>

        <?php if (isset($message)): ?>
            <p style="color: green;"><?php echo htmlspecialchars($message); ?></p>
        <?php endif; ?>

        <!-- Search bar -->
        <form method="GET" action="">
            <input type="text" name="search" placeholder="Procurar por nome, email, NIF ou login" value="<?php echo htmlspecialchars($searchTerm); ?>" required>
            <button type="submit">Procurar</button>
        </form>

        <?php if (!empty($users)): ?>
            <table>
                <thead>
                    <tr>
                        <th>Nome</th>
                        <th>Email</th>
                        <th>NIF</th>
                        <th>Login</th>
                        <th>Role Atual</th>
                        <th>Nova Role</th>
                        <th>Ação</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $user): ?>
                        <tr>
                            <form method="POST" action="">
                                <td><?php echo htmlspecialchars($user['nome']); ?></td>
                                <td><?php echo htmlspecialchars($user['email']); ?></td>
                                <td><?php echo htmlspecialchars($user['NIF']); ?></td>
                                <td><?php echo htmlspecialchars($user['login']); ?></td>
                                <td><?php echo htmlspecialchars($user['role_name'] ?? 'Nenhuma'); ?></td>
                                <td>
                                    <select name="role_id" required>
                                        <option value="">-- Selecionar Role --</option>
                                        <?php foreach ($roles as $id => $roleName): ?>
                                            <option value="<?php echo $id; ?>" <?php if ($user['role_id'] == $id) echo 'selected'; ?>>
                                                <?php echo htmlspecialchars($roleName); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </td>
                                <td>
                                    <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                    <button type="submit" name="update_role">Atualizar</button>
                                </td>
                            </form>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php elseif ($searchTerm): ?>
            <p>Nenhum utilizador encontrado para "<?php echo htmlspecialchars($searchTerm); ?>"</p>
        <?php endif; ?>
    </div>
    
      <a href="adminbase.php" class="voltar-fixo">⟵ Voltar</a>
</body>
</html>
