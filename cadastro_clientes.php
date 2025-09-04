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
    $empresa_id = $_SESSION['usuario_empresa'];

    if ($nome != '') {
        $stmt = $conn->prepare("INSERT INTO clientes (nome, telefone, email, cpf, endereco, cep, lista_preco_id, empresa_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssssii", $nome, $telefone, $email, $cpf, $endereco, $cep, $lista_preco_id, $empresa_id);
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

// Movimentação Financeira (Emprestar/Receber)
if (isset($_POST['registrar_movimentacao'])) {
    $cliente_id = intval($_POST['cliente_id']);
    $tipo = $_POST['tipo_mov'];
    $valor = floatval($_POST['valor_mov']);
    $descricao = trim($_POST['descricao_mov']);
    $empresa_id = $_SESSION['usuario_empresa'];
    // Buscar saldo atual
    $res = $conn->query("SELECT saldo FROM clientes WHERE id = $cliente_id");
    $saldo_atual = $res->num_rows ? floatval($res->fetch_assoc()['saldo']) : 0;
    if ($tipo === 'emprestimo') {
        $novo_saldo = $saldo_atual - $valor; // saldo devedor
        $tipo_mov = 'debito';
    } elseif ($tipo === 'recebimento') {
        $novo_saldo = $saldo_atual + $valor; // saldo credor
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
        header("Location: cadastro_clientes.php");
        exit;
    }
}

include __DIR__ . '/includes/header.php';
include __DIR__ . '/includes/navbar.php';
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
            overflow: hidden;
        }

        .cliente-info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 5px 15px;
        }

        .cliente-info-grid small {
            color: #555;
        }

        .btn-action {
            min-width: 120px;
            border-radius: 8px;
            font-weight: 500;
            padding: 8px 0;
            font-size: 1rem;
            transition: all 0.2s;
            background: #f8f9fa;
            color: #222;
            border: 1px solid #dee2e6;
            max-width: 100%;
            margin-left: 0 !important;
            margin-right: 0 !important;
        }

        .btn-action:hover,
        .btn-action:focus {
            background: #e9ecef;
            color: #111;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.07);
        }

        .btn-action.btn-outline-primary {
            color: #0d6efd;
            border-color: #0d6efd;
            background: #f8f9fa;
        }

        .btn-action.btn-outline-primary:hover,
        .btn-action.btn-outline-primary:focus {
            background: #0d6efd;
            color: #fff;
        }

        .btn-action.btn-outline-danger {
            color: #dc3545;
            border-color: #dc3545;
            background: #f8f9fa;
        }

        .btn-action.btn-outline-danger:hover,
        .btn-action.btn-outline-danger:focus {
            background: #dc3545;
            color: #fff;
        }

        .btn-action.btn-outline-secondary {
            color: #6c757d;
            border-color: #6c757d;
            background: #f8f9fa;
        }

        .btn-action.btn-outline-secondary:hover,
        .btn-action.btn-outline-secondary:focus {
            background: #6c757d;
            color: #fff;
        }

        .btn-action.btn-warning {
            background: #fffbe6;
            color: #ffc107;
            border-color: #ffc107;
        }

        .btn-action.btn-warning:hover,
        .btn-action.btn-warning:focus {
            background: #ffc107;
            color: #fff;
        }

        .btn-action.btn-success {
            background: #e6fff2;
            color: #198754;
            border-color: #198754;
        }

        .btn-action.btn-success:hover,
        .btn-action.btn-success:focus {
            background: #198754;
            color: #fff;
        }

        @media (max-width: 576px) {
            .cliente-card-flex {
                flex-direction: column !important;
            }
            .botao-acoes-clientes {
                order: 2;
                display: block !important;
                width: 100%;
                padding-bottom: 4px;
                margin-top: 12px;
            }
            .cliente-info-block {
                order: 1;
            }
            .btn-action {
                display: block !important;
                width: 100%;
                min-width: 0;
                margin: 0 0 10px 0;
                height: 48px;
                font-size: 0.98rem;
                box-sizing: border-box;
                text-align: center;
            }
            .btn-action i {
                font-size: 1.1em;
                margin-right: 6px;
            }
        }
        .botao-acoes-clientes {
            width: 100%;
            max-width: 100%;
            box-sizing: border-box;
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
                                <input type="text" name="nome" class="form-control" placeholder="Ex: João Silva" value="<?= htmlspecialchars($nome ?? '') ?>" required>
                            </div>
                            <div class="mb-3">
                                <label>Telefone:</label>
                                <input type="text" name="telefone" class="form-control" placeholder="Ex: (11) 99999-9999" value="<?= htmlspecialchars($telefone ?? '') ?>">
                            </div>
                            <div class="mb-3">
                                <label>Email:</label>
                                <input type="email" name="email" class="form-control" placeholder="Ex: joao@email.com" value="<?= htmlspecialchars($email ?? '') ?>">
                            </div>
                            <div class="mb-3">
                                <label>CPF:</label>
                                <input type="text" name="cpf" class="form-control" placeholder="Ex: 000.000.000-00" value="<?= htmlspecialchars($cpf ?? '') ?>">
                            </div>
                            <div class="mb-3">
                                <label>Endereço:</label>
                                <input type="text" name="endereco" class="form-control" placeholder="Ex: Rua Exemplo, 123" value="<?= htmlspecialchars($endereco ?? '') ?>">
                            </div>
                            <div class="mb-3">
                                <label>CEP:</label>
                                <input type="text" name="cep" class="form-control" placeholder="Ex: 00000-000" value="<?= htmlspecialchars($cep ?? '') ?>">
                            </div>
                            <div class="mb-3">
                                <label>Lista de Preço:</label>
                                <select name="lista_preco_id" class="form-select">
                                    <option value="">Selecione uma Lista</option>
                                    <?php
                                    $empresa_id = $_SESSION['usuario_empresa'];
                                    $listas = $conn->query("SELECT * FROM listas_precos WHERE empresa_id = $empresa_id ORDER BY nome");
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
            <form method="get" class="mb-3 d-flex flex-wrap gap-2">
                <input type="text" name="busca" class="form-control" style="max-width:300px" placeholder="Pesquisar por nome, CPF ou telefone" value="<?= htmlspecialchars($_GET['busca'] ?? '') ?>">
                <button type="submit" class="btn btn-outline-primary"><i class="bi bi-search"></i> Buscar</button>
            </form>
            <?php
            $empresa_id = $_SESSION['usuario_empresa'];
            $busca = trim($_GET['busca'] ?? '');
            $pagina = max(1, intval($_GET['pagina'] ?? 1));
            $limite = 10;
            $offset = ($pagina - 1) * $limite;
            $where = "c.empresa_id = $empresa_id";
            if ($busca) {
                $busca_sql = $conn->real_escape_string($busca);
                $where .= " AND (c.nome LIKE '%$busca_sql%' OR c.cpf LIKE '%$busca_sql%' OR c.telefone LIKE '%$busca_sql%')";
            }
            $total = $conn->query("SELECT COUNT(*) as total FROM clientes c WHERE $where")->fetch_assoc()['total'];
            $clientes = $conn->query("
                SELECT c.*, lp.nome AS lista_nome 
                FROM clientes c 
                LEFT JOIN listas_precos lp ON c.lista_preco_id = lp.id
                WHERE $where
                ORDER BY c.created_at DESC
                LIMIT $limite OFFSET $offset
            ");
            if ($clientes->num_rows > 0) {
                echo "<ul class='list-group'>";
                while ($c = $clientes->fetch_assoc()) {
                    echo '<li class="list-group-item">
                        <div class="d-flex cliente-card-flex justify-content-between align-items-start flex-wrap">
                            <div class="cliente-info-block">
                                <strong>' . $c['nome'] . '</strong> | CPF: ' . $c['cpf'] . '
                                <div class="cliente-info-grid mt-2">
                                    <small>Tel: ' . $c['telefone'] . '</small>
                                    <small>Email: ' . $c['email'] . '</small>
                                    <small>Endereço: ' . $c['endereco'] . '</small>
                                    <small>CEP: ' . $c['cep'] . '</small>
                                    <small>Lista: ' . ($c['lista_nome'] ?: 'Nenhuma') . '</small>
                                    <small>Saldo: R$ ' . number_format($c['saldo'], 2, ',', '.') . '</small>
                                </div>
                            </div>
                            <div class="mt-2 botao-acoes-clientes">
                                <a href="editar_cliente.php?id=' . $c['id'] . '" class="btn btn-action btn-outline-primary">
                                    <i class="bi bi-pencil"></i> Editar
                                </a>
                                <a href="cadastro_clientes.php?excluir_cliente=' . $c['id'] . '" class="btn btn-action btn-outline-danger" onclick="return confirm(\'Tem certeza que deseja excluir este cliente?\')">
                                    <i class="bi bi-trash"></i> Excluir
                                </a>
                                <a href="extrato_cliente.php?id=' . $c['id'] . '" class="btn btn-action btn-outline-secondary" target="_blank" title="Ver Extrato">
                                    <i class="bi bi-receipt"></i> Extrato
                                </a>
                                <button type="button" class="btn btn-action btn-warning" data-bs-toggle="modal" data-bs-target="#modalEmprestar' . $c['id'] . '">
                                    <i class="bi bi-arrow-up-circle"></i> Emprestar
                                </button>
                                <button type="button" class="btn btn-action btn-success" data-bs-toggle="modal" data-bs-target="#modalReceber' . $c['id'] . '">
                                    <i class="bi bi-arrow-down-circle"></i> Receber
                                </button>
                            </div>
                        </div>
                    </li>';
                    // Modal Emprestar
                    echo "<div class='modal fade' id='modalEmprestar{$c['id']}' tabindex='-1' aria-labelledby='modalEmprestarLabel{$c['id']}' aria-hidden='true'>
                            <div class='modal-dialog'>
                                <div class='modal-content'>
                                    <form method='post'>
                                        <div class='modal-header'>
                                            <h5 class='modal-title' id='modalEmprestarLabel{$c['id']}'>Emprestar dinheiro para {$c['nome']}</h5>
                                            <button type='button' class='btn-close' data-bs-dismiss='modal' aria-label='Fechar'></button>
                                        </div>
                                        <div class='modal-body'>
                                            <input type='hidden' name='cliente_id' value='{$c['id']}'>
                                            <input type='hidden' name='tipo_mov' value='emprestimo'>
                                            <div class='mb-3'>
                                                <label>Valor (R$):</label>
                                                <input type='number' step='0.01' min='0' name='valor_mov' class='form-control' required>
                                            </div>
                                            <div class='mb-3'>
                                                <label>Descrição (opcional):</label>
                                                <input type='text' name='descricao_mov' class='form-control'>
                                            </div>
                                        </div>
                                        <div class='modal-footer'>
                                            <button type='button' class='btn btn-secondary' data-bs-dismiss='modal'>Cancelar</button>
                                            <button type='submit' name='registrar_movimentacao' class='btn btn-warning'>Confirmar Emprestimo</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>";
                    // Modal Receber
                    echo "<div class='modal fade' id='modalReceber{$c['id']}' tabindex='-1' aria-labelledby='modalReceberLabel{$c['id']}' aria-hidden='true'>
                            <div class='modal-dialog'>
                                <div class='modal-content'>
                                    <form method='post'>
                                        <div class='modal-header'>
                                            <h5 class='modal-title' id='modalReceberLabel{$c['id']}'>Receber pagamento de {$c['nome']}</h5>
                                            <button type='button' class='btn-close' data-bs-dismiss='modal' aria-label='Fechar'></button>
                                        </div>
                                        <div class='modal-body'>
                                            <input type='hidden' name='cliente_id' value='{$c['id']}'>
                                            <input type='hidden' name='tipo_mov' value='recebimento'>
                                            <div class='mb-3'>
                                                <label>Valor (R$):</label>
                                                <input type='number' step='0.01' min='0' name='valor_mov' class='form-control' required>
                                            </div>
                                            <div class='mb-3'>
                                                <label>Descrição (opcional):</label>
                                                <input type='text' name='descricao_mov' class='form-control'>
                                            </div>
                                        </div>
                                        <div class='modal-footer'>
                                            <button type='button' class='btn btn-secondary' data-bs-dismiss='modal'>Cancelar</button>
                                            <button type='submit' name='registrar_movimentacao' class='btn btn-success'>Confirmar Recebimento</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>";
                }
                echo "</ul>";
                // Paginação
                $total_paginas = ceil($total / $limite);
                if ($total_paginas > 1) {
                    echo '<nav><ul class="pagination justify-content-center mt-3">';
                    for ($i = 1; $i <= $total_paginas; $i++) {
                        $active = $i == $pagina ? 'active' : '';
                        $queryString = http_build_query(array_merge($_GET, ['pagina' => $i]));
                        echo "<li class='page-item $active'><a class='page-link' href='?{$queryString}'>$i</a></li>";
                    }
                    echo '</ul></nav>';
                }
            } else {
                echo "<p class='text-muted'>Nenhum cliente cadastrado ainda.</p>";
            }
            ?>
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