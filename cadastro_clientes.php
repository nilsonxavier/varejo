<?php
require_once 'conexx/config.php';
require_once 'verifica_login.php';
// ... resto da página protegida ...
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT); // Habilita exceções

$erro_cadastro = '';

// Adicionar Cliente
if (isset($_POST['adicionar_cliente'])) {
    $nome = trim($_POST['nome']);
    $telefone = trim($_POST['telefone']);
    $email = trim($_POST['email']);
    $cpf = trim($_POST['cpf']);
    $endereco = trim($_POST['endereco']);
    $cep = trim($_POST['cep']);
    $lista_preco_id = intval($_POST['lista_preco_id']);

    if ($nome != '') {
        $stmt = $conn->prepare("INSERT INTO clientes (nome, telefone, email, cpf, endereco, cep, lista_preco_id) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssssi", $nome, $telefone, $email, $cpf, $endereco, $cep, $lista_preco_id);
        try {
            $stmt->execute();
            header("Location: cadastro_clientes.php");
            exit;
        } catch (mysqli_sql_exception $e) {
            if ($e->getCode() == 1062) {
                $erro_cadastro = "CPF já cadastrado.";
            } else {
                $erro_cadastro = "Erro ao cadastrar cliente: " . $e->getMessage();
            }
        }
    }
}

// Excluir Cliente
if (isset($_GET['excluir_cliente'])) {
    $excluir_id = intval($_GET['excluir_cliente']);
    if ($excluir_id > 0) {
        $conn->query("DELETE FROM clientes WHERE id = $excluir_id");
    }
    header("Location: cadastro_clientes.php");
    exit;
}

include __DIR__ . '/includes/header.php';
include __DIR__ . '/includes/navbar.php';
include __DIR__ . '/includes/footer.php';
?>


<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <title>Cadastro de Clientes</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
        }

        .section-card {
            background-color: #ffffff;
            border-radius: 12px;
            padding: 25px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.05);
            margin-bottom: 30px;
        }

        h2 {
            margin-bottom: 20px;
        }

        .form-control,
        .form-select {
            border-radius: 8px;
        }

        .btn-success {
            border-radius: 8px;
            padding: 10px 20px;
        }

        .btn-outline-danger,
        .btn-outline-primary {
            border-radius: 6px;
            transition: all 0.2s ease;
            margin-left: 5px;
        }

        .btn-outline-danger:hover {
            background-color: #dc3545;
            color: white;
        }

        .btn-outline-primary:hover {
            background-color: #0d6efd;
            color: white;
        }

        .list-group-item {
            border-radius: 10px;
            margin-bottom: 10px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.03);
        }

        .cliente-info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 5px 15px;
        }

        .cliente-info-grid small {
            color: #555;
        }
    </style>
</head>

<body>

    <div class="container py-4">



        <!-- Botão para abrir o modal -->
        <button type="button" class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#cadastroClienteModal">
            <i class="bi bi-person-plus"></i> Novo Cliente
        </button>

        <!-- Modal de Cadastro de Cliente -->
        <div class="modal fade" id="cadastroClienteModal" tabindex="-1" aria-labelledby="cadastroClienteModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h2 class="modal-title fs-5" id="cadastroClienteModalLabel"><i class="bi bi-person-plus"></i> Cadastro de Clientes</h2>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
                    </div>
                    <div class="modal-body">
                        <?php if ($erro_cadastro): ?>
                            <div class="alert alert-danger">
                                <?= htmlspecialchars($erro_cadastro) ?>
                            </div>
                        <?php endif; ?>

                        <form method="post">
                            <div class="mb-3">
                                <label>Nome do Cliente:</label>
                                <input type="text" name="nome" class="form-control" placeholder="Ex: João Silva"
                                    value="<?= htmlspecialchars($nome ?? '') ?>" required>
                            </div>
                            <div class="mb-3">
                                <label>Telefone:</label>
                                <input type="text" name="telefone" class="form-control" placeholder="Ex: (11) 99999-9999"
                                    value="<?= htmlspecialchars($telefone ?? '') ?>">
                            </div>
                            <div class="mb-3">
                                <label>Email:</label>
                                <input type="email" name="email" class="form-control" placeholder="Ex: joao@email.com"
                                    value="<?= htmlspecialchars($email ?? '') ?>">
                            </div>
                            <div class="mb-3">
                                <label>CPF:</label>
                                <input type="text" name="cpf" class="form-control" placeholder="Ex: 000.000.000-00"
                                    value="<?= htmlspecialchars($cpf ?? '') ?>">
                            </div>
                            <div class="mb-3">
                                <label>Endereço:</label>
                                <input type="text" name="endereco" class="form-control" placeholder="Ex: Rua Exemplo, 123"
                                    value="<?= htmlspecialchars($endereco ?? '') ?>">
                            </div>
                            <div class="mb-3">
                                <label>CEP:</label>
                                <input type="text" name="cep" class="form-control" placeholder="Ex: 00000-000"
                                    value="<?= htmlspecialchars($cep ?? '') ?>">
                            </div>
                            <div class="mb-3">
                                <label>Lista de Preço:</label>
                                <select name="lista_preco_id" class="form-select">
                                    <option value="">Selecione uma Lista</option>
                                    <?php
                                    $listas = $conn->query("SELECT * FROM listas_precos ORDER BY nome");
                                    while ($l = $listas->fetch_assoc()) {
                                        $selected = ($lista_preco_id ?? '') == $l['id'] ? 'selected' : '';
                                        echo "<option value='{$l['id']}' $selected>{$l['nome']}</option>";
                                    }
                                    ?>
                                </select>
                            </div>
                            <button type="submit" name="adicionar_cliente" class="btn btn-success">
                                <i class="bi bi-check-circle"></i> Cadastrar Cliente
                            </button>
                        </form>

                    </div>
                </div>
            </div>
        </div>

        <!-- Lista de Clientes -->
        <div class="section-card">
            <h2><i class="bi bi-people"></i> Clientes Cadastrados</h2>
            <?php
            $empresa_id = $_SESSION['usuario_empresa'];
            //var_dump($_SESSION);
            $clientes = $conn->query("
    SELECT c.*, lp.nome AS lista_nome 
    FROM clientes c 
    LEFT JOIN listas_precos lp ON c.lista_preco_id = lp.id
    WHERE c.empresa_id = $empresa_id
    ORDER BY c.created_at DESC
");
            if ($clientes->num_rows > 0) {
                echo "<ul class='list-group'>";
                while ($c = $clientes->fetch_assoc()) {
                    echo "<li class='list-group-item'>
                        <div class='d-flex justify-content-between align-items-start'>
                            <div>
                                <strong>{$c['nome']}</strong> | CPF: {$c['cpf']}
                                <div class='cliente-info-grid mt-2'>
                                    <small>Tel: {$c['telefone']}</small>
                                    <small>Email: {$c['email']}</small>
                                    <small>Endereço: {$c['endereco']}</small>
                                    <small>CEP: {$c['cep']}</small>
                                    <small>Lista: " . ($c['lista_nome'] ?: 'Nenhuma') . "</small>
                                    <small>Saldo: R$ " . number_format($c['saldo'], 2, ',', '.') . "</small>
                                </div>
                            </div>
                            <div class='mt-2'>
                                <a href='editar_cliente.php?id={$c['id']}' class='btn btn-sm btn-outline-primary'>
                                    <i class='bi bi-pencil'></i> Editar
                                </a>
                                <a href='cadastro_clientes.php?excluir_cliente={$c['id']}' class='btn btn-sm btn-outline-danger' onclick=\"return confirm('Tem certeza que deseja excluir este cliente?')\">
                                    <i class='bi bi-trash'></i> Excluir
                                </a>
                            </div>
                        </div>
                    </li>";
                }
                echo "</ul>";
            } else {
                echo "<p class='text-muted'>Nenhum cliente cadastrado ainda.</p>";
            }
            ?>
        </div>

    </div>
    <?php if ($erro_cadastro): ?>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                var cadastroModal = new bootstrap.Modal(document.getElementById('cadastroClienteModal'));
                cadastroModal.show();
            });
        </script>
    <?php endif; ?>

    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
</body>

</html>