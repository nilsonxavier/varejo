<?php
require_once 'conexx/config.php';
require_once 'verifica_login.php';
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

// Processa movimentação ANTES de qualquer saída HTML
if (isset($_POST['registrar_movimentacao'])) {
    // Proteção contra duplo envio
    if (empty($_POST['mov_token']) || $_POST['mov_token'] !== $_SESSION['mov_token']) {
        exit;
    }
    // Invalida token após uso
    unset($_SESSION['mov_token']);
    $cliente_id = intval($_GET['id']);
    $tipo = $_POST['tipo_mov'];
    $valor = floatval($_POST['valor_mov']);
    $descricao = trim($_POST['descricao_mov']);
    $empresa_id = $_SESSION['usuario_empresa'];
    // Buscar saldo atual
    $stmt = $conn->prepare("SELECT saldo FROM clientes WHERE id = ?");
    $stmt->bind_param("i", $cliente_id);
    $stmt->execute();
    $res = $stmt->get_result();
    $saldo_atual = $res->num_rows ? floatval($res->fetch_assoc()['saldo']) : 0;
    if ($tipo === 'emprestimo') {
        $novo_saldo = $saldo_atual - $valor;
        $tipo_mov = 'devedor';
    } elseif ($tipo === 'recebimento') {
        $novo_saldo = $saldo_atual + $valor;
        $tipo_mov = 'credor';
    } else {
        $novo_saldo = $saldo_atual;
        $tipo_mov = '';
    }
    if ($tipo_mov) {
        $stmt = $conn->prepare("INSERT INTO movimentacoes_clientes (cliente_id, tipo, valor, descricao, empresa_id, saldo_apos) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("isdssi", $cliente_id, $tipo_mov, $valor, $descricao, $empresa_id, $novo_saldo);
        $stmt->execute();
        // Atualiza saldo do cliente
        $stmt = $conn->prepare("UPDATE clientes SET saldo=? WHERE id=?");
        $stmt->bind_param("di", $novo_saldo, $cliente_id);
        $stmt->execute();
        $msg = urlencode('Movimentação registrada com sucesso!');
        header("Location: cadastro_clientes.php?msg=$msg");
        exit;
    }
}

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
    // Listas de preços da empresa do cliente
    $empresa_id_cliente = intval($cliente['empresa_id']);
$listas = $conn->query("SELECT * FROM listas_precos ORDER BY nome");
    $listas = $conn->query("SELECT * FROM listas_precos WHERE empresa_id = $empresa_id_cliente ORDER BY nome");

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
                <?php
                // Gera token único para o formulário
                if (empty($_SESSION['mov_token'])) {
                    $_SESSION['mov_token'] = bin2hex(random_bytes(16));
                }
                ?>
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
            <div class="d-flex flex-wrap gap-2 mt-3">
                <button type="button" class="btn btn-warning btn-sm flex-fill" data-bs-toggle="modal" data-bs-target="#modalEmprestar">
                    <i class="bi bi-arrow-up-circle"></i> Emprestar
                </button>
                <button type="button" class="btn btn-success btn-sm flex-fill" data-bs-toggle="modal" data-bs-target="#modalReceber">
                    <i class="bi bi-arrow-down-circle"></i> Receber
                </button>
            </div>
    </div>
</div>


    <!-- Modal Emprestar -->
    <div class="modal fade" id="modalEmprestar" tabindex="-1" aria-labelledby="modalEmprestarLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="post">
                    <div class="modal-header">
                        <h5 class="modal-title" id="modalEmprestarLabel">Emprestar dinheiro para <?= htmlspecialchars($cliente['nome']) ?></h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="tipo_mov" value="emprestimo">
                            <input type="hidden" name="mov_token" value="<?= $_SESSION['mov_token'] ?>">
                        <div class="mb-3">
                            <label>Valor (R$):</label>
                            <input type="number" step="0.01" min="0" name="valor_mov" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label>Descrição (opcional):</label>
                            <input type="text" name="descricao_mov" class="form-control">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" name="registrar_movimentacao" class="btn btn-warning">Confirmar Emprestimo</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Receber -->
    <div class="modal fade" id="modalReceber" tabindex="-1" aria-labelledby="modalReceberLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="post">
                    <div class="modal-header">
                        <h5 class="modal-title" id="modalReceberLabel">Receber pagamento de <?= htmlspecialchars($cliente['nome']) ?></h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="tipo_mov" value="recebimento">
                            <input type="hidden" name="mov_token" value="<?= $_SESSION['mov_token'] ?>">
                        <div class="mb-3">
                            <label>Valor (R$):</label>
                            <input type="number" step="0.01" min="0" name="valor_mov" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label>Descrição (opcional):</label>
                            <input type="text" name="descricao_mov" class="form-control">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" name="registrar_movimentacao" class="btn btn-success">Confirmar Recebimento</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <?php
    if (isset($_POST['registrar_movimentacao'])) {
            // Proteção contra duplo envio
            if (empty($_POST['mov_token']) || $_POST['mov_token'] !== $_SESSION['mov_token']) {
                exit;
            }
            // Invalida token após uso
            unset($_SESSION['mov_token']);
        $tipo = $_POST['tipo_mov'];
        $valor = floatval($_POST['valor_mov']);
        $descricao = trim($_POST['descricao_mov']);
        $empresa_id = intval($cliente['empresa_id']);
        $saldo_atual = floatval($cliente['saldo']);
        if ($tipo === 'emprestimo') {
            $novo_saldo = $saldo_atual + $valor;
            $tipo_mov = 'debito';
        } elseif ($tipo === 'recebimento') {
            $novo_saldo = $saldo_atual - $valor;
            $tipo_mov = 'credito';
        } else {
            $novo_saldo = $saldo_atual;
            $tipo_mov = '';
        }
        if ($tipo_mov) {
            $stmt = $conn->prepare("INSERT INTO movimentacoes_clientes (cliente_id, tipo, valor, descricao, empresa_id, saldo_apos) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("isdssi", $cliente_id, $tipo_mov, $valor, $descricao, $empresa_id, $novo_saldo);
            $stmt->execute();
            // Atualiza saldo do cliente
            $stmt = $conn->prepare("UPDATE clientes SET saldo=? WHERE id=?");
            $stmt->bind_param("di", $novo_saldo, $cliente_id);
            $stmt->execute();
            $msg = urlencode('Movimentação registrada com sucesso!');
            header("Location: cadastro_clientes.php?msg=$msg");
            exit;
        }
    }
    ?>

</body>
</html>
