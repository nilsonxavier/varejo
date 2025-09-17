<?php
require_once 'verifica_login.php';
require_once 'conexx/config.php';

$usuario_id = $_SESSION['usuario_id'];
$empresa_id = $_SESSION['usuario_empresa'];

// Debug inicial
error_log("CAIXA DEBUG - Usuario: $usuario_id, Empresa: $empresa_id");

// Verifica se existe caixa aberto
$caixa_aberto = $conn->query("SELECT * FROM caixas WHERE status = 'aberto' AND empresa_id = " . intval($empresa_id) . " ORDER BY id DESC LIMIT 1")->fetch_assoc();

// Abertura de caixa
if (isset($_POST['abrir_caixa'])) {
    error_log("CAIXA DEBUG - Tentativa de abrir caixa");
    // Revalida para evitar duplicação
    $existe_caixa_aberto = $conn->query("SELECT id FROM caixas WHERE status = 'aberto' AND empresa_id = " . intval($empresa_id))->num_rows;
    if ($existe_caixa_aberto > 0) {
        error_log("CAIXA DEBUG - Caixa já existe, redirecionando");
        header("Location: caixa.php");
        exit;
    }

    $valor_inicial = floatval($_POST['valor_inicial']);
    $data_abertura = date('Y-m-d H:i:s');

    error_log("CAIXA DEBUG - Inserindo caixa com valor: $valor_inicial");
    $stmt = $conn->prepare("INSERT INTO caixas (usuario_id, empresa_id, data_abertura, valor_inicial, status) VALUES (?, ?, ?, ?, 'aberto')");
    $stmt->bind_param("iisd", $usuario_id, $empresa_id, $data_abertura, $valor_inicial);
    
    if ($stmt->execute()) {
        error_log("CAIXA DEBUG - Caixa inserido com sucesso");
    } else {
        error_log("CAIXA DEBUG - Erro ao inserir caixa: " . $stmt->error);
    }
    
    header("Location: caixa.php");
    exit;
}

// Registrar movimentação
if (isset($_POST['registrar_movimentacao'])) {
    error_log("CAIXA DEBUG - POST recebido para registrar_movimentacao");
    error_log("CAIXA DEBUG - POST data: " . print_r($_POST, true));
    
    if ($caixa_aberto) {
        error_log("CAIXA DEBUG - Caixa aberto ID: " . $caixa_aberto['id']);
        
        $tipo = $_POST['tipo'];
        $valor = floatval($_POST['valor']);
        $descricao = trim($_POST['descricao']);
        $caixa_id = $caixa_aberto['id'];
        $data_movimentacao = date('Y-m-d H:i:s');

        error_log("CAIXA DEBUG - Dados para inserção: Tipo=$tipo, Valor=$valor, Desc=$descricao, CaixaID=$caixa_id");

        $stmt = $conn->prepare("INSERT INTO movimentacoes (caixa_id, tipo, valor, descricao, data_movimentacao, empresa_id) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("isdssi", $caixa_id, $tipo, $valor, $descricao, $data_movimentacao, $empresa_id);
        
        if ($stmt->execute()) {
            $mov_id = $conn->insert_id;
            error_log("CAIXA DEBUG - Movimentação inserida com sucesso! ID: $mov_id");
            
            // Mostrar mensagem de sucesso antes do redirect
            $_SESSION['success_message'] = "Movimentação registrada com sucesso!";
        } else {
            error_log("CAIXA DEBUG - ERRO ao inserir movimentação: " . $stmt->error);
            $_SESSION['error_message'] = "Erro ao registrar movimentação: " . $stmt->error;
        }
    } else {
        error_log("CAIXA DEBUG - ERRO: Nenhum caixa aberto");
        $_SESSION['error_message'] = "Nenhum caixa está aberto!";
    }

    header("Location: caixa.php");
    exit;
}

// Fechar caixa com transação
if (isset($_POST['fechar_caixa']) && $caixa_aberto) {
    $conn->begin_transaction();
    try {
        $caixa_id = $caixa_aberto['id'];
        $total_entradas = $conn->query("SELECT SUM(valor) as total FROM movimentacoes WHERE caixa_id = $caixa_id AND tipo = 'entrada'")->fetch_assoc()['total'] ?? 0;
        $total_saidas = $conn->query("SELECT SUM(valor) as total FROM movimentacoes WHERE caixa_id = $caixa_id AND tipo = 'saida'")->fetch_assoc()['total'] ?? 0;
        $valor_final = $caixa_aberto['valor_inicial'] + $total_entradas - $total_saidas;
        $data_fechamento = date('Y-m-d H:i:s');

        $stmt = $conn->prepare("UPDATE caixas SET data_fechamento = ?, valor_final = ?, status = 'fechado' WHERE id = ?");
        $stmt->bind_param("sdi", $data_fechamento, $valor_final, $caixa_id);
        $stmt->execute();

        $conn->commit();
    } catch (Exception $e) {
        $conn->rollback();
        die("Erro ao fechar caixa: " . $e->getMessage());
    }

    header("Location: caixa.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Controle de Caixa</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
</head>
<body class="bg-light">

<!-- Mensagens de feedback -->
<?php if (isset($_SESSION['success_message'])): ?>
    <div class="alert alert-success alert-dismissible fade show m-3" role="alert">
        <?= $_SESSION['success_message'] ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php unset($_SESSION['success_message']); ?>
<?php endif; ?>

<?php if (isset($_SESSION['error_message'])): ?>
    <div class="alert alert-danger alert-dismissible fade show m-3" role="alert">
        <?= $_SESSION['error_message'] ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php unset($_SESSION['error_message']); ?>
<?php endif; ?>

<div class="container py-5">
    <div class="bg-white p-4 rounded shadow-sm">
        <h2 class="mb-4 border-bottom pb-2">Controle de Caixa</h2>

        <?php if (!$caixa_aberto): ?>
            <div class="alert alert-info">
                <strong>Debug:</strong> Nenhum caixa aberto para empresa ID: <?= $empresa_id ?>
            </div>
            
            <form method="post" class="mb-4">
                <div class="mb-3">
                    <label class="form-label fw-semibold">Valor Inicial:</label>
                    <input type="number" name="valor_inicial" step="0.01" min="0" required class="form-control">
                </div>
                <button type="submit" name="abrir_caixa" class="btn btn-success">
                    <i class="bi bi-box-arrow-in-right me-1"></i> Abrir Caixa
                </button>
            </form>
        <?php else: ?>
            <div class="alert alert-success">
                <strong>Debug:</strong> Caixa ID <?= $caixa_aberto['id'] ?> aberto para empresa <?= $empresa_id ?>
            </div>
            
            <div class="mb-4">
                <p class="mb-1"><strong>Caixa aberto em:</strong> <?= $caixa_aberto['data_abertura'] ?></p>
                <p><strong>Valor inicial:</strong> R$ <?= number_format($caixa_aberto['valor_inicial'], 2, ',', '.') ?></p>
            </div>

            <form method="post" class="row g-3 mb-4 align-items-end" id="formMovimentacao">
                <div class="col-md-2">
                    <label class="form-label">Tipo</label>
                    <select name="tipo" class="form-select" required>
                        <option value="entrada">Entrada</option>
                        <option value="saida">Saída</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Valor</label>
                    <input type="number" name="valor" step="0.01" min="0.01" required class="form-control" placeholder="Valor">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Descrição</label>
                    <input type="text" name="descricao" required class="form-control" placeholder="Descrição">
                </div>
                <div class="col-md-2">
                    <button type="submit" name="registrar_movimentacao" class="btn btn-primary w-100">
                        <i class="bi bi-plus-circle me-1"></i> Registrar
                    </button>
                </div>
            </form>

            <form method="post">
                <button type="submit" name="fechar_caixa" class="btn btn-danger">
                    <i class="bi bi-lock me-1"></i> Fechar Caixa
                </button>
            </form>

            <hr>

            <h4 class="mb-3">Movimentações</h4>
            <div class="table-responsive">
                <table class="table table-bordered align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>ID</th>
                            <th>Tipo</th>
                            <th>Valor</th>
                            <th>Descrição</th>
                            <th>Data</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $caixa_id = $caixa_aberto['id'];
                        $stmt = $conn->prepare("SELECT id, tipo, valor, descricao, data_movimentacao FROM movimentacoes WHERE caixa_id = ? ORDER BY data_movimentacao DESC");
                        $stmt->bind_param("i", $caixa_id);
                        $stmt->execute();
                        $movs = $stmt->get_result();

                        $total_entradas = 0;
                        $total_saidas = 0;
                        $count = 0;
                        while ($mov = $movs->fetch_assoc()):
                            $count++;
                            if ($mov['tipo'] === 'entrada') {
                                $total_entradas += $mov['valor'];
                            } else {
                                $total_saidas += $mov['valor'];
                            }
                        ?>
                            <tr>
                                <td><?= $mov['id'] ?></td>
                                <td><span class="badge bg-<?= $mov['tipo'] === 'entrada' ? 'success' : 'danger' ?>"><?= ucfirst($mov['tipo']) ?></span></td>
                                <td>R$ <?= number_format($mov['valor'], 2, ',', '.') ?></td>
                                <td><?= htmlspecialchars($mov['descricao']) ?></td>
                                <td><?= date('d/m/Y H:i:s', strtotime($mov['data_movimentacao'])) ?></td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
                <?php if ($count == 0): ?>
                    <div class="alert alert-warning">Nenhuma movimentação encontrada para este caixa.</div>
                <?php endif; ?>
            </div>

            <?php
            $saldo_atual = $caixa_aberto['valor_inicial'] + $total_entradas - $total_saidas;
            ?>
            <div class="card mt-4 border-0 shadow-sm">
                <div class="card-body">
                    <h5 class="card-title">Resumo do Caixa</h5>
                    <div class="row">
                        <div class="col-md-3">
                            <p class="card-text text-info">
                                <strong>Valor Inicial:</strong><br>
                                R$ <?= number_format($caixa_aberto['valor_inicial'], 2, ',', '.') ?>
                            </p>
                        </div>
                        <div class="col-md-3">
                            <p class="card-text text-success">
                                <strong>Total Entradas:</strong><br>
                                R$ <?= number_format($total_entradas, 2, ',', '.') ?>
                            </p>
                        </div>
                        <div class="col-md-3">
                            <p class="card-text text-danger">
                                <strong>Total Saídas:</strong><br>
                                R$ <?= number_format($total_saidas, 2, ',', '.') ?>
                            </p>
                        </div>
                        <div class="col-md-3">
                            <p class="card-text text-primary">
                                <strong>Saldo Atual:</strong><br>
                                <h4>R$ <?= number_format($saldo_atual, 2, ',', '.') ?></h4>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <hr class="my-5">

        <h3 class="mb-4">Caixas Anteriores</h3>
        <div class="table-responsive">
            <table class="table table-bordered table-hover table-striped align-middle">
                <thead class="table-secondary">
                    <tr>
                        <th>ID</th>
                        <th>Aberto em</th>
                        <th>Fechado em</th>
                        <th>Valor Inicial</th>
                        <th>Valor Final</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $result = $conn->query("SELECT * FROM caixas WHERE status = 'fechado' AND empresa_id = " . intval($empresa_id) . " ORDER BY id DESC LIMIT 10");
                    while ($cx = $result->fetch_assoc()):
                    ?>
                        <tr>
                            <td><?= $cx['id'] ?></td>
                            <td><?= date('d/m/Y H:i:s', strtotime($cx['data_abertura'])) ?></td>
                            <td><?= $cx['data_fechamento'] ? date('d/m/Y H:i:s', strtotime($cx['data_fechamento'])) : '-' ?></td>
                            <td>R$ <?= number_format($cx['valor_inicial'], 2, ',', '.') ?></td>
                            <td>R$ <?= number_format($cx['valor_final'] ?? 0, 2, ',', '.') ?></td>
                            <td>
                                <a href="detalhes_caixa.php?id=<?= $cx['id'] ?>" class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-eye"></i> Ver Detalhes
                                </a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.getElementById('formMovimentacao')?.addEventListener('submit', function(e) {
    const valor = parseFloat(document.querySelector('input[name="valor"]').value);
    const descricao = document.querySelector('input[name="descricao"]').value.trim();
    
    if (valor <= 0) {
        alert('O valor deve ser maior que zero!');
        e.preventDefault();
        return;
    }
    
    if (descricao.length < 3) {
        alert('A descrição deve ter pelo menos 3 caracteres!');
        e.preventDefault();
        return;
    }
    
    console.log('Enviando movimentação:', {
        tipo: document.querySelector('select[name="tipo"]').value,
        valor: valor,
        descricao: descricao
    });
});
</script>

</body>
</html>
