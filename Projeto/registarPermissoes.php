<?php
include 'db_connection.php';

// Handle form submission: assign role to user
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userId = $_POST['user_id'] ?? null;
    $roleId = $_POST['role_id'] ?? null;

    if ($userId && $roleId) {
        $stmt = $conn->prepare("UPDATE Utilizador SET role_id = ? WHERE id = ?");
        $stmt->bind_param("ii", $roleId, $userId);
        $stmt->execute();
        $stmt->close();

        $message = "Role assigned successfully!";
    }
}

// Fetch users without role
$resultUsers = $conn->query("SELECT id, nome, email FROM Utilizador WHERE role_id IS NULL ORDER BY nome");

// Fetch roles
$resultRoles = $conn->query("SELECT id, nome FROM Role ORDER BY nome");
$roles = [];
while ($row = $resultRoles->fetch_assoc()) {
    $roles[$row['id']] = $row['nome'];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Assign Roles to Users</title>
    <link rel="stylesheet" href="./css/registarPermissoes.css" />
</head>
<body>
    <div class="container">
        <h2>Assign Role to Users</h2>

        <?php if (isset($message)): ?>
            <p class="message"><?php echo htmlspecialchars($message); ?></p>
        <?php endif; ?>

        <?php if ($resultUsers->num_rows > 0): ?>
            <table class="Perm-tabela">
                <thead>
                    <tr>
                        <th>Nome</th>
                        <th>Email</th>
                        <th>Assign Role</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($user = $resultUsers->fetch_assoc()): ?>
                        <tr>
                            <form method="POST" action="">
                                <td><?php echo htmlspecialchars($user['nome']); ?></td>
                                <td><?php echo htmlspecialchars($user['email']); ?></td>
                                <td>
                                    <select name="role_id" required>
                                        <option value="">-- Select Role --</option>
                                        <?php foreach ($roles as $id => $name): ?>
                                            <option value="<?php echo $id; ?>"><?php echo htmlspecialchars($name); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </td>
                                <td>
                                    <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                    <button type="submit">Assign Role</button>
                                </td>
                            </form>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>No users without roles found.</p>
        <?php endif; ?>
    </div>

    <a href="adminbase.php" class="voltar-fixo">⟵ Voltar</a>
</body>
</html>