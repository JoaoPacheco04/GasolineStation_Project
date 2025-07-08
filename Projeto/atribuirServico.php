<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['tipo'] !== 'Funcionario_Administrativo') {
    header("Location: login.php");
    exit;
}

include './db_connection.php';

$mensagem = "";
$mensagem_erro = false;

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['servico_id'], $_POST['funcionario_id'])) {
    $servico_id = intval($_POST['servico_id']);
    $funcionario_id = intval($_POST['funcionario_id']);

    $sql = "INSERT INTO Servico_Agendado_Funcionario (idAgendamento, idFuncionario) VALUES (?, ?)";

    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("ii", $servico_id, $funcionario_id);
        if ($stmt->execute()) {
            $mensagem = "✅ Serviço atribuído com sucesso!";
            $mensagem_erro = false;
        } else {
            if ($conn->errno == 1062) {
                $mensagem = "❌ Este serviço já está atribuído a este funcionário.";
            } else {
                $mensagem = "❌ Erro ao atribuir serviço: " . $stmt->error;
            }
            $mensagem_erro = true;
        }
        $stmt->close();
    } else {
        $mensagem = "❌ Erro na preparação da query: " . $conn->error;
        $mensagem_erro = true;
    }
}

$sql_servicos = "
    SELECT sa.idAgendamento, s.nome AS descricao, sa.data
    FROM Servico_Agendado sa
    JOIN Servico s ON sa.idServico = s.id
    LEFT JOIN Servico_Agendado_Funcionario saf ON sa.idAgendamento = saf.idAgendamento
    WHERE saf.idFuncionario IS NULL
";

$result_servicos = $conn->query($sql_servicos);
if (!$result_servicos) {
    die("Erro ao buscar serviços: " . $conn->error);
}

$sql_funcionarios = "
    SELECT u.id, u.nome 
    FROM Utilizador u
    JOIN role r ON u.role_id = r.id
    WHERE r.nome = 'Funcionario_Servicos'
";
$result_funcionarios = $conn->query($sql_funcionarios);
if (!$result_funcionarios) {
    die("Erro ao buscar funcionários: " . $conn->error);
}
?>

<!DOCTYPE html>
<html lang="pt">
<head>
  <meta charset="UTF-8" />
  <title>Atribuir Serviço</title>
  <link rel="stylesheet" href="./css/formstyle.css" />
  <style>
    body {
      background: linear-gradient(to right, #483D8B, #E6E6FA);
      font-family: Arial, sans-serif;
      margin: 0;
      padding: 40px 20px;
      min-height: 100vh;
      display: flex;
      justify-content: center;
      align-items: flex-start;
    }

    .main-container {
      width: 100%;
      max-width: 600px;
      background: white;
      padding: 30px;
      border-radius: 12px;
      box-shadow: 0 8px 16px rgba(0, 0, 0, 0.25);
    }

    h1 {
      text-align: center;
      color: #483D8B;
      margin-bottom: 20px;
    }

    form {
      margin-bottom: 20px;
    }

    label {
      display: block;
      margin-top: 15px;
      color: #333;
      font-weight: bold;
    }

    select, button {
      width: 100%;
      padding: 10px;
      margin-top: 8px;
      border-radius: 8px;
      border: 1px solid #ccc;
      font-size: 16px;
      box-sizing: border-box;
    }

    button[type="submit"] {
      background-color: #6A5ACD;
      color: white;
      border: none;
      font-weight: bold;
      cursor: pointer;
      transition: background-color 0.3s ease;
      margin-top: 20px;
    }

    button[type="submit"]:hover {
      background-color: #5548a1;
    }

    .logout-button {
      background-color: #ccc;
      margin-top: 10px;
      padding: 10px;
      border-radius: 8px;
      border: none;
      width: 100%;
      font-weight: bold;
      cursor: pointer;
      transition: background-color 0.3s ease;
    }

    .logout-button:hover {
      background-color: #999;
    }

    .mensagem {
      margin-top: 20px;
      text-align: center;
      font-weight: bold;
      color: green;
    }

    .mensagem.erro {
      color: red;
    }
  </style>
</head>
<body>
  <div class="main-container">
    <h1>Atribuir Serviço</h1>

    <?php if ($mensagem): ?>
      <div class="mensagem <?= $mensagem_erro ? 'erro' : '' ?>"><?= htmlspecialchars($mensagem) ?></div>
    <?php endif; ?>

    <form method="post" action="atribuirServico.php">
      <label for="servico_id">Serviço Agendado:</label>
      <select name="servico_id" required>
        <option value="">-- Escolha um serviço --</option>
        <?php while ($row = $result_servicos->fetch_assoc()): ?>
  <option value="<?= $row['idAgendamento'] ?>" data-date="<?= $row['data'] ?>">
    <?= htmlspecialchars($row['descricao']) . " ({$row['data']})" ?>
  </option>
<?php endwhile; ?>

      </select>

      <label for="funcionario_id">Funcionário de Serviços:</label>
      <select name="funcionario_id" required>
        <option value="">-- Escolha um funcionário --</option>
        <?php while ($row = $result_funcionarios->fetch_assoc()): ?>
          <option value="<?= $row['id'] ?>"><?= htmlspecialchars($row['nome']) ?></option>
        <?php endwhile; ?>
      </select>

      <button type="submit">Atribuir Serviço</button>
    </form>

    <form action="funcadminbase.php" method="get">
      <button class="logout-button" type="submit">Voltar</button>
    </form>
  </div>

  <script>
document.addEventListener("DOMContentLoaded", function() {
    const servicoSelect = document.querySelector('select[name="servico_id"]');
    const funcionarioSelect = document.querySelector('select[name="funcionario_id"]');

    // Disable employee select until a service is selected
    funcionarioSelect.disabled = true;

    servicoSelect.addEventListener('change', function() {
        const servicoId = this.value;

        if (!servicoId) {
            funcionarioSelect.innerHTML = '<option value="">-- Escolha um funcionário --</option>';
            funcionarioSelect.disabled = true;
            return;
        }

        // Get the date from the selected option's data attribute (we need to add this)
        const selectedOption = this.options[this.selectedIndex];
        const serviceDate = selectedOption.getAttribute('data-date');

        if (!serviceDate) {
            funcionarioSelect.innerHTML = '<option value="">-- Escolha um funcionário --</option>';
            funcionarioSelect.disabled = true;
            return;
        }

        fetch(`getAvailableFuncionarios.php?date=${serviceDate}`)
            .then(response => response.json())
            .then(data => {
                funcionarioSelect.innerHTML = '<option value="">-- Escolha um funcionário --</option>';
                if (data.length === 0) {
                    funcionarioSelect.innerHTML = '<option value="">Nenhum funcionário disponível nesta data</option>';
                    funcionarioSelect.disabled = true;
                    return;
                }
                data.forEach(func => {
                    const option = document.createElement('option');
                    option.value = func.id;
                    option.textContent = func.nome;
                    funcionarioSelect.appendChild(option);
                });
                funcionarioSelect.disabled = false;
            })
            .catch(err => {
                funcionarioSelect.innerHTML = '<option value="">Erro ao carregar funcionários</option>';
                funcionarioSelect.disabled = true;
                console.error(err);
            });
    });
});
</script>

</body>
</html>
