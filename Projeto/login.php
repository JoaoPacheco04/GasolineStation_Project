<?php
session_start();
include 'db_connection.php';

$message = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $loginOrEmail = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    $sql = "
        SELECT u.*, r.nome AS role
        FROM Utilizador u
        LEFT JOIN Role r ON u.role_id = r.id
        WHERE u.login = ? OR u.email = ?
    ";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $loginOrEmail, $loginOrEmail);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();

        if ($user['ativo'] !== 'Ativo') {
            $message = "❌ A sua conta está desativada. Contacte o administrador.";
        } elseif (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['login'] = $user['login'];
            $_SESSION['tipo'] = $user['role'];

            header("Location: dashboard.php");
            exit;
        } else {
            $message = "❌ Password incorreta.";
        }
    } else {
        $message = "❌ Utilizador não encontrado.";
    }

    $stmt->close();
    $conn->close();
}

?>

<!DOCTYPE html>
<html lang="pt">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Gazóline Online - Login</title>
  <link rel="icon" href="./favicons/a.png" />
  <link rel="stylesheet" href="./css/login.css" />
</head>
<body>

  <div class="header">
    <img src="./favicons/icon1.png" alt="Icon" class="icon1" />
    <h1>Gazóline</h1>
  </div>

  <div class="login-container">
    <h2>Login</h2>

    <?php if ($message): ?>
      <div class="message"><?php echo htmlspecialchars($message); ?></div>
    <?php endif; ?>

    <form id="login-form" action="" method="POST" novalidate>
      <input type="text" name="username" placeholder="Login ou Email" required />
      <input type="password" name="password" placeholder="Password" required />
      <button type="submit">Log In</button>
    </form>

    <div class="register-link">
      Novo por aqui?
      <a href="registarUtilizador.html">Regista-te aqui</a>
    </div>
  </div>

</body>
</html>
