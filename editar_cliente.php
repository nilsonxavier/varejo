<?php
require_once 'conexx/config.php';

// Verificar se o ID do cliente foi passado
if (!isset($_GET['id']) || intval($_GET['id']) <= 0) {
    header("Location: cadastro_clientes.php");
    exit;
}

$cliente_id = intval($_GET['id']);

// Buscar os dados atuais do cliente
$stmt = $conn->prepare("SELECT * FROM clientes WHERE id = ?");
$stmt->bind_param("i", $cliente_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    echo "<p>Cliente não encontrado!</p>";
    exit;
}

$cliente = $result->fetch_assoc();

// Atualizar os dados do cliente
if (isset($_POST['atualizar_cliente'])) {
    $nome = trim($_POST['nome']);
    $telefone = trim($_POST['telefone']);
    $email = trim($_POST['email']);
    $cpf = trim($_POST['cpf']);
    $endereco = trim($_POST['endereco']);
    $cep = trim($_POST['cep']);
    $lista_preco_id = intval($_POST['lista_preco_id']);
    $saldo = floatval(str_replace(',', '.', str_replace('.', '', $_POST['saldo']))); // Converte "1.234,56" para 1234.56

    $stmt = $conn->prepare("UPDATE clientes SET nome=?, telefone=?, email=?, cpf=?, endereco=?, cep=?, lista_preco_id=?, saldo=? WHERE id=?");
    $stmt->bind_param("ssssssidi", $nome, $telefone, $email, $cpf, $endereco, $cep, $lista_preco_id, $saldo, $cliente_id);
    $stmt->execute();

    header("Location: cadastro_clientes.php");
    exit;
}

// Listas de preços
$listas = $conn->query("SELECT * FROM listas_precos ORDER BY nome");

include __DIR__.'/includes/header.php';
include __DIR__.'/includes/navbar.php';
include __DIR__.'/includes/footer.php';

?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Editar Cliente</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .section-card {
            background-color: #ffffff;
            border-radius: 12px;
            padding: 25px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.05);
            margin-top: 30px;
        }
        .btn-primary {
            border-radius: 8px;
        }
    </style>
</head>
<body>

<div class="container py-4">
    <div class="section-card">
        <h2><i class="bi bi-pencil-square"></i> Editar Cliente</h2>
        <form method="post">
            <div class="mb-3">
                <label>Nome:</label>
                <input type="text" name="nome" class="form-control" value="<?= htmlspecialchars($cliente['nome']) ?>" required>
            </div>
            <div class="mb-3">
                <label>Telefone:</label>
                <input type="text" name="telefone" class="form-control" value="<?= htmlspecialchars($cliente['telefone']) ?>">
            </div>
            <div class="mb-3">
                <label>Email:</label>
                <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($cliente['email']) ?>">
            </div>
            <div class="mb-3">
                <label>CPF:</label>
                <input type="text" name="cpf" class="form-control" value="<?= htmlspecialchars($cliente['cpf']) ?>">
            </div>
            <div class="mb-3">
                <label>Endereço:</label>
                <input type="text" name="endereco" class="form-control" value="<?= htmlspecialchars($cliente['endereco']) ?>">
            </div>
            <div class="mb-3">
                <label>CEP:</label>
                <input type="text" name="cep" class="form-control" value="<?= htmlspecialchars($cliente['cep']) ?>">
            </div>
            <div class="mb-3">
                <label>Lista de Preço:</label>
                <select name="lista_preco_id" class="form-select">
                    <option value="">Nenhuma</option>
                    <?php while ($l = $listas->fetch_assoc()): ?>
                        <option value="<?= $l['id'] ?>" <?= ($cliente['lista_preco_id'] == $l['id'] ? 'selected' : '') ?>>
                            <?= htmlspecialchars($l['nome']) ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div class="mb-3">
                <label>Saldo (ex: 100.00 ou -50.00):</label>
                <input type="text" name="saldo" class="form-control" value="<?= number_format($cliente['saldo'], 2, ',', '.') ?>">
            </div>
            <button type="submit" name="atualizar_cliente" class="btn btn-primary">
                <i class="bi bi-save"></i> Salvar Alterações
            </button>
            <a href="cadastro_clientes.php" class="btn btn-secondary ms-2">Cancelar</a>
        </form>
    </div>
</div>

</body>
</html>
