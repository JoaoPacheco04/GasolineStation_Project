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
$pontos_necessarios = "";
$imagem_url = "";

// Apagar prêmio
if (isset($_GET['acao'], $_GET['id'])) {
    $id = intval($_GET['id']);
    if ($_GET['acao'] === 'apagar') {
        $sql_del = "DELETE FROM Premio WHERE id = ?";
        if ($stmt = $conn->prepare($sql_del)) {
            $stmt->bind_param("i", $id);
            if ($stmt->execute()) {
                $mensagem = "✅ Prêmio apagado com sucesso.";
                $mensagem_erro = false;
            } else {
                $mensagem = "❌ Erro ao apagar prêmio: " . $stmt->error;
                $mensagem_erro = true;
            }
            $stmt->close();
        }
    } elseif ($_GET['acao'] === 'editar') {
        // Carregar dados para editar
        $editar_id = $id;
        $sql_sel = "SELECT nome, descricao, pontos_necessarios, imagem_url FROM Premio WHERE id = ?";
        if ($stmt = $conn->prepare($sql_sel)) {
            $stmt->bind_param("i", $editar_id);
            if ($stmt->execute()) {
                $stmt->bind_result($nome, $descricao, $pontos_necessarios, $imagem_url);
                if (!$stmt->fetch()) {
                    $mensagem = "❌ Prêmio não encontrado para edição.";
                    $mensagem_erro = true;
                    $editar_id = null;
                }
            } else {
                $mensagem = "❌ Erro ao buscar prêmio para edição: " . $stmt->error;
                $mensagem_erro = true;
                $editar_id = null;
            }
            $stmt->close();
        }
    }
}

// Processar formulário (inserir ou editar)
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['nome'], $_POST['descricao'], $_POST['pontos_necessarios'])) {
    $nome = trim($_POST['nome']);
    $descricao = trim($_POST['descricao']);
    $pontos_necessarios = intval($_POST['pontos_necessarios']);
    $imagem_url = trim($_POST['imagem_url']);
    $editar_id_post = isset($_POST['id']) && $_POST['id'] !== '' ? intval($_POST['id']) : null;

    if ($pontos_necessarios < 0) {
        $mensagem = "❌ Pontos necessários devem ser zero ou mais.";
        $mensagem_erro = true;
    } else {
        if ($editar_id_post) {
            // Atualizar
            $sql_update = "UPDATE Premio SET nome = ?, descricao = ?, pontos_necessarios = ?, imagem_url = ? WHERE id = ?";
            if ($stmt = $conn->prepare($sql_update)) {
                $stmt->bind_param("ssisi", $nome, $descricao, $pontos_necessarios, $imagem_url, $editar_id_post);
                if ($stmt->execute()) {
                    $mensagem = "✅ Prêmio atualizado com sucesso!";
                    $mensagem_erro = false;
                    $editar_id = null;
                    $nome = $descricao = $imagem_url = "";
                    $pontos_necessarios = "";
                } else {
                    $mensagem = "❌ Erro ao atualizar prêmio: " . $stmt->error;
                    $mensagem_erro = true;
                }
                $stmt->close();
            }
        } else {
            // Inserir novo
            $sql_insert = "INSERT INTO Premio (nome, descricao, pontos_necessarios, imagem_url) VALUES (?, ?, ?, ?)";
            if ($stmt = $conn->prepare($sql_insert)) {
                $stmt->bind_param("ssis", $nome, $descricao, $pontos_necessarios, $imagem_url);
                if ($stmt->execute()) {
                    $mensagem = "✅ Prêmio registado com sucesso!";
                    $mensagem_erro = false;
                    $nome = $descricao = $imagem_url = "";
                    $pontos_necessarios = "";
                    $editar_id = null;
                } else {
                    $mensagem = "❌ Erro ao registar prêmio: " . $stmt->error;
                    $mensagem_erro = true;
                }
                $stmt->close();
            }
        }
    }
}

// Buscar todos os prêmios
$sql = "SELECT id, nome, descricao, pontos_necessarios, imagem_url FROM Premio ORDER BY id";
$result = $conn->query($sql);
if (!$result) {
    die("Erro ao buscar prêmios: " . $conn->error);
}
?>

<!DOCTYPE html>
<html lang="pt">
<head>
<meta charset="UTF-8" />
<title>Gestão de Prêmios</title>
<link rel="stylesheet" href="./css/formstyle.css" />
<style>
  /* Seu CSS existente */
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
    max-width: 1000px;
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
    font-size: 14px;
  }
  th, td {
    padding: 10px 8px;
    border-bottom: 1px solid #ddd;
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
    margin-right: 12px;
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
  .btn-novo {
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
  .btn-novo:hover {
    background-color: #5548a1;
  }
  #formPremio {
    margin-top: 20px;
    border-top: 1px solid #ccc;
    padding-top: 20px;
    display: none;
  }
  #formPremio h2 {
    color: #483D8B;
  }
  #formPremio label {
    display: block;
    margin-top: 10px;
    font-weight: bold;
  }
  #formPremio input[type="text"],
  #formPremio input[type="number"],
  #formPremio textarea {
    width: 100%;
    padding: 8px;
    margin-top: 6px;
    border-radius: 6px;
    border: 1px solid #ccc;
    box-sizing: border-box;
    font-size: 14px;
  }
  #formPremio button[type="submit"], #formPremio .btn-cancelar {
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
  #formPremio button[type="submit"]:hover, #formPremio .btn-cancelar:hover {
    background-color: #5548a1;
  }
  #formPremio .btn-cancelar {
    background-color: #444;
  }
  img.preview {
    max-width: 80px;
    max-height: 50px;
    border-radius: 6px;
    margin-top: 6px;
  }
</style>
<script>
  function toggleForm() {
    const form = document.getElementById('formPremio');
    if (form.style.display === 'none' || form.style.display === '') {
      form.style.display = 'block';
      window.scrollTo(0, document.body.scrollHeight);
    } else {
      form.style.display = 'none';
      // Limpar form ao esconder (se não estiver editando)
      if (!document.getElementById('id').value) {
        limparForm();
      }
    }
  }
  function limparForm() {
    document.getElementById('formPremio').reset();
    document.getElementById('id').value = "";
    document.getElementById('previewImagem').src = "";
  }
  function mostrarPreviewImagem() {
    const url = document.getElementById('imagem_url').value.trim();
    const preview = document.getElementById('previewImagem');
    if (url) {
      preview.src = url;
      preview.style.display = "inline";
    } else {
      preview.src = "";
      preview.style.display = "none";
    }
  }
  window.onload = function() {
    // Se estiver editando, mostrar o form aberto
    <?php if ($editar_id !== null): ?>
      toggleForm();
      mostrarPreviewImagem();
    <?php endif; ?>
  };
</script>
</head>
<body>
<div class="container">
  <h1>Gestão de Prêmios</h1>

  <?php if ($mensagem): ?>
    <p class="mensagem <?php echo $mensagem_erro ? 'erro' : ''; ?>"><?php echo htmlspecialchars($mensagem); ?></p>
  <?php endif; ?>

  <button class="btn-novo" onclick="toggleForm();">➕ Novo Prêmio</button>

  <table>
    <thead>
      <tr>
        <th>ID</th>
        <th>Nome</th>
        <th>Descrição</th>
        <th>Pontos Necessários</th>
        <th>Imagem</th>
        <th>Ações</th>
      </tr>
    </thead>
    <tbody>
      <?php while ($row = $result->fetch_assoc()): ?>
        <tr>
          <td><?= $row['id'] ?></td>
          <td><?= htmlspecialchars($row['nome']) ?></td>
          <td><?= htmlspecialchars($row['descricao']) ?></td>
          <td><?= $row['pontos_necessarios'] ?></td>
          <td>
            <?php if ($row['imagem_url']): ?>
              <img src="<?= htmlspecialchars($row['imagem_url']) ?>" alt="Imagem" class="preview" />
            <?php else: ?>
              -
            <?php endif; ?>
          </td>
          <td class="acoes">
            <a href="?acao=editar&id=<?= $row['id'] ?>">Editar</a>
            <a href="?acao=apagar&id=<?= $row['id'] ?>" class="apagar" onclick="return confirm('Tem certeza que quer apagar este prêmio?');">Apagar</a>
          </td>
        </tr>
      <?php endwhile; ?>
    </tbody>
  </table>

  <form id="formPremio" method="post" action="">
    <h2><?php echo $editar_id !== null ? 'Editar Prêmio' : 'Novo Prêmio'; ?></h2>
    <input type="hidden" id="id" name="id" value="<?php echo $editar_id !== null ? $editar_id : ''; ?>" />

    <label for="nome">Nome:</label>
    <input type="text" id="nome" name="nome" required value="<?php echo htmlspecialchars($nome); ?>" />

    <label for="descricao">Descrição:</label>
    <textarea id="descricao" name="descricao" rows="3" required><?php echo htmlspecialchars($descricao); ?></textarea>

    <label for="pontos_necessarios">Pontos Necessários:</label>
    <input type="number" id="pontos_necessarios" name="pontos_necessarios" min="0" required value="<?php echo htmlspecialchars($pontos_necessarios); ?>" />

    <label for="imagem_url">URL da Imagem:</label>
    <input type="text" id="imagem_url" name="imagem_url" value="<?php echo htmlspecialchars($imagem_url); ?>" oninput="mostrarPreviewImagem();" />
    <br />
    <img id="previewImagem" class="preview" src="" alt="Preview Imagem" style="display:none;" />

    <br />
    <button type="submit"><?php echo $editar_id !== null ? 'Atualizar' : 'Registar'; ?></button>
    <button type="button" class="btn-cancelar" onclick="toggleForm(); limparForm();">Cancelar</button>
  </form>
  
  <button onclick="window.location.href='funcadminbase.php';" 
          style="background-color: #5548a1; color:white; padding:10px 15px; border:none; border-radius:8px; cursor:pointer; font-weight:bold; margin-bottom:15px;">
    ← Voltar ao Menu
  </button
</div>
</body>
</html>
