<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['tipo'] !== 'Funcionario_Administrativo') {
    header("Location: login.php");
    exit;
}

include './db_connection.php';

$mensagem = "";
$mensagem_erro = false;

$editar_id = null;
$nome = "";
$descricao = "";
$preco = "";

// Tratar apagar serviço
if (isset($_GET['acao'], $_GET['id']) && $_GET['acao'] === 'apagar') {
    $id = intval($_GET['id']);
    $sql_del = "DELETE FROM Servico WHERE id = ?";
    if ($stmt = $conn->prepare($sql_del)) {
        $stmt->bind_param("i", $id);
        if ($stmt->execute()) {
            header("Location: registarServico.php?msg=apagado");

            exit;
        } else {
            $mensagem = "❌ Erro ao apagar serviço: " . $stmt->error;
            $mensagem_erro = true;
        }
        $stmt->close();
    }
}

// Mostrar mensagem de sucesso ao apagar
if (isset($_GET['msg']) && $_GET['msg'] === 'apagado') {
    $mensagem = "Serviço apagado com sucesso.";
    $mensagem_erro = false;
}

// Se estiver editando (mostrar formulário preenchido)
if (isset($_GET['acao'], $_GET['id']) && $_GET['acao'] === 'editar') {
    $editar_id = intval($_GET['id']);
    $sql_sel = "SELECT nome, descricao, preco FROM Servico WHERE id = ?";
    if ($stmt = $conn->prepare($sql_sel)) {
        $stmt->bind_param("i", $editar_id);
        if ($stmt->execute()) {
            $stmt->bind_result($nome, $descricao, $preco);
            if (!$stmt->fetch()) {
                $mensagem = "❌ Serviço não encontrado para edição.";
                $mensagem_erro = true;
                $editar_id = null;
            }
        } else {
            $mensagem = "❌ Erro ao buscar serviço para edição: " . $stmt->error;
            $mensagem_erro = true;
            $editar_id = null;
        }
        $stmt->close();
    }
}

// Tratar submissão do formulário (novo ou edição)
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['nome'], $_POST['descricao'], $_POST['preco'])) {
    $nome = trim($_POST['nome']);
    $descricao = trim($_POST['descricao']);
    $preco = str_replace(',', '.', $_POST['preco']);
    $editar_id = isset($_POST['id']) ? intval($_POST['id']) : null;

    if (!is_numeric($preco) || floatval($preco) < 0) {
        $mensagem = "❌ Preço inválido.";
        $mensagem_erro = true;
    } else {
        if ($editar_id) {
            // Atualizar serviço existente
            $sql_update = "UPDATE Servico SET nome = ?, descricao = ?, preco = ? WHERE id = ?";
            if ($stmt = $conn->prepare($sql_update)) {
                $stmt->bind_param("ssdi", $nome, $descricao, $preco, $editar_id);
                if ($stmt->execute()) {
                    $mensagem = "✅ Serviço atualizado com sucesso!";
                    $mensagem_erro = false;
                    $editar_id = null; // Para esconder o formulário de edição
                    // Limpar variáveis
                    $nome = $descricao = $preco = "";
                } else {
                    $mensagem = "❌ Erro ao atualizar serviço: " . $stmt->error;
                    $mensagem_erro = true;
                }
                $stmt->close();
            } else {
                $mensagem = "❌ Erro na preparação da query: " . $conn->error;
                $mensagem_erro = true;
            }
        } else {
            // Inserir novo serviço
            $sql_insert = "INSERT INTO Servico (nome, descricao, preco) VALUES (?, ?, ?)";
            if ($stmt = $conn->prepare($sql_insert)) {
                $stmt->bind_param("ssd", $nome, $descricao, $preco);
                if ($stmt->execute()) {
                    $mensagem = "✅ Serviço registado com sucesso!";
                    $mensagem_erro = false;
                    // Limpar variáveis para não mostrar o formulário preenchido
                    $nome = $descricao = $preco = "";
                } else {
                    $mensagem = "❌ Erro ao registar serviço: " . $stmt->error;
                    $mensagem_erro = true;
                }
                $stmt->close();
            } else {
                $mensagem = "❌ Erro na preparação da query: " . $conn->error;
                $mensagem_erro = true;
            }
        }
    }
}

// Buscar serviços para listar
$sql = "SELECT id, nome, descricao, preco FROM Servico ORDER BY id";
$result = $conn->query($sql);
if (!$result) {
    die("Erro ao buscar serviços: " . $conn->error);
}
?>

<!DOCTYPE html>
<html lang="pt">
<head>
<meta charset="UTF-8" />
<title>Gestão de Serviços</title>
<link rel="stylesheet" href="./css/formstyle.css" />
<style>
  /* Seu CSS existente para formatação e tabela */
  body {
    background: linear-gradient(to right, #483D8B, #E6E6FA);
    font-family: Arial, sans-serif;
    margin: 0; padding: 40px 20px;
    min-height: 100vh;
    display: flex;
    justify-content: center;
  }
  .container {
    width: 100%;
    max-width: 900px;
    background: white;
    padding: 20px 30px;
    border-radius: 12px;
    box-shadow: 0 8px 16px rgba(0,0,0,0.25);
  }
  h1 {
    text-align: center;
    color: #483D8B;
    margin-bottom: 20px;
  }
  table {
    width: 100%;
    border-collapse: collapse;
  }
  th, td {
    padding: 12px 10px;
    border-bottom: 1px solid #ddd;
    text-align: left;
    vertical-align: middle;
  }
  th {
    background-color: #6A5ACD;
    color: white;
  }
  tr:hover {
    background-color: #f1f1f1;
  }
  .acoes a {
    margin-right: 10px;
    text-decoration: none;
    color: #6A5ACD;
    font-weight: bold;
  }
  .acoes a.apagar {
    color: red;
  }
  .mensagem {
    text-align: center;
    font-weight: bold;
    margin-bottom: 20px;
    color: green;
  }
  .mensagem.erro {
    color: red;
  }
  .btn-novo, .btn-voltar {
    background-color: #6A5ACD;
    color: white;
    padding: 10px 15px;
    border: none;
    border-radius: 8px;
    cursor: pointer;
    font-weight: bold;
    margin-bottom: 15px;
    display: inline-block;
    text-decoration: none;
  }
  .btn-novo:hover, .btn-voltar:hover {
    background-color: #5548a1;
  }
  #formServico {
    margin-top: 20px;
    border-top: 1px solid #ccc;
    padding-top: 20px;
  }
  #formServico h2 {
    color: #483D8B;
  }
  #formServico label {
    display: block;
    margin-top: 10px;
    font-weight: bold;
  }
  #formServico input[type="text"],
  #formServico textarea {
    width: 100%;
    padding: 8px;
    margin-top: 6px;
    border-radius: 6px;
    border: 1px solid #ccc;
    box-sizing: border-box;
    font-size: 14px;
  }
  #formServico button[type="submit"], #formServico .btn-cancelar {
    background-color: #6A5ACD;
    color: white;
    padding: 10px 20px;
    border: none;
    border-radius: 8px;
    cursor: pointer;
    font-weight: bold;
    margin-top: 15px;
    margin-right: 10px;
  }
  #formServico button[type="submit"]:hover, #formServico .btn-cancelar:hover {
    background-color: #5548a1;
  }
  #formServico .btn-cancelar {
    background-color: #444;
  }
</style>
<script>
  function toggleForm() {
    const form = document.getElementById('formServico');
    if (form.style.display === 'none' || form.style.display === '') {
      form.style.display = 'block';
      window.scrollTo(0, document.body.scrollHeight);
    } else {
      form.style.display = 'none';
    }
  }
  function voltarMenu() {
    window.location.href = 'funcadminbase.php';
  }
  // Abrir o formulário se estiver editando ou se mensagem de erro (para mostrar erros ao tentar salvar)
  document.addEventListener('DOMContentLoaded', function () {
    <?php if ($editar_id || $mensagem_erro): ?>
      document.getElementById('formServico').style.display = 'block';
      window.scrollTo(0, document.body.scrollHeight);
    <?php endif; ?>
  });
</script>
</head>
<body>
<div class="container">
  <h1>Gestão de Serviços</h1>

  <?php if ($mensagem): ?>
    <div class="mensagem <?= $mensagem_erro ? 'erro' : '' ?>"><?= htmlspecialchars($mensagem) ?></div>
  <?php endif; ?>

  <button class="btn-novo" onclick="toggleForm()">+ Novo Serviço</button>
  <button class="btn-voltar" onclick="voltarMenu()">← Voltar</button>

  <table>
    <thead>
      <tr>
        <th>ID</th>
        <th>Nome</th>
        <th>Descrição</th>
        <th>Preço (€)</th>
        <th>Ações</th>
      </tr>
    </thead>
    <tbody>
    <?php while ($row = $result->fetch_assoc()): ?>
      <tr>
        <td><?= (int)$row['id'] ?></td>
        <td><?= htmlspecialchars($row['nome']) ?></td>
        <td><?= htmlspecialchars($row['descricao']) ?></td>
        <td><?= number_format($row['preco'], 2, ',', '.') ?></td>
        <td class="acoes">
          <a href="?acao=editar&id=<?= (int)$row['id'] ?>">Editar</a>
          <a href="?acao=apagar&id=<?= (int)$row['id'] ?>" class="apagar" onclick="return confirm('Tem certeza que quer apagar este serviço?');">Apagar</a>
        </td>
      </tr>
    <?php endwhile; ?>
    </tbody>
  </table>

  <form id="formServico" method="post" action="">
    <h2><?= $editar_id ? "Editar Serviço" : "Registar Novo Serviço" ?></h2>

    <input type="hidden" name="id" value="<?= $editar_id ? (int)$editar_id : '' ?>" />

    <label for="nome">Nome:</label>
    <input type="text" id="nome" name="nome" value="<?= htmlspecialchars($nome) ?>" required />

    <label for="descricao">Descrição:</label>
    <textarea id="descricao" name="descricao" rows="3" required><?= htmlspecialchars($descricao) ?></textarea>

    <label for="preco">Preço (€):</label>
    <input type="text" id="preco" name="preco" pattern="^\d+([,\.]\d{1,2})?$" placeholder="Ex: 25,00" value="<?= htmlspecialchars(number_format((float)$preco, 2, ',', '.')) ?>" required />

    <button type="submit"><?= $editar_id ? "Atualizar Serviço" : "Salvar Serviço" ?></button>

  </form>
</div>
</body>
</html>
