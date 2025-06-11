<?php
require_once 'verifica_login.php';
require_once 'conexx/config.php';

$usuario_id = $_SESSION['usuario_id'];

$caixa_aberto = $conn->query("SELECT * FROM caixas WHERE status = 'aberto' ORDER BY id DESC LIMIT 1")->fetch_assoc();

// Abertura de caixa
if (isset($_POST['abrir_caixa'])) {
    $existe_caixa_aberto = $conn->query("SELECT id FROM caixas WHERE status = 'aberto'")->num_rows;
    if ($existe_caixa_aberto > 0) {
        header("Location: caixa.php");
        exit;
    }

    $valor_inicial = floatval($_POST['valor_inicial']);
    $stmt = $conn->prepare("INSERT INTO caixas (usuario_id, data_abertura, valor_inicial, status) VALUES (?, NOW(), ?, 'aberto')");
    $stmt->bind_param("id", $usuario_id, $valor_inicial);
    $stmt->execute();
    header("Location: caixa.php");
    exit;
}

// Registrar movimentação
if (isset($_POST['registrar_movimentacao']) && $caixa_aberto) {
    $tipo = $_POST['tipo'];
    $valor = floatval($_POST['valor']);
    $descricao = trim($_POST['descricao']);
    $caixa_id = $caixa_aberto['id'];

    $stmt = $conn->prepare("INSERT INTO movimentacoes (caixa_id, tipo, valor, descricao, data_movimentacao) VALUES (?, ?, ?, ?, NOW())");
    $stmt->bind_param("isds", $caixa_id, $tipo, $valor, $descricao);
    $stmt->execute();
    header("Location: caixa.php");
    exit;
}

// Fechar caixa
if (isset($_POST['fechar_caixa']) && $caixa_aberto) {
    $conn->begin_transaction();

    try {
        $caixa_id = $caixa_aberto['id'];

        $total_entradas = $conn->query("SELECT SUM(valor) as total FROM movimentacoes WHERE caixa_id = $caixa_id AND tipo = 'entrada'")->fetch_assoc()['total'] ?? 0;
        $total_saidas = $conn->query("SELECT SUM(valor) as total FROM movimentacoes WHERE caixa_id = $caixa_id AND tipo = 'saida'")->fetch_assoc()['total'] ?? 0;

        $valor_final = $caixa_aberto['valor_inicial'] + $total_entradas - $total_saidas;

        $stmt = $conn->prepare("UPDATE caixas SET data_fechamento = NOW(), valor_final = ?, status = 'fechado' WHERE id = ?");
        $stmt->bind_param("di", $valor_final, $caixa_id);
        $stmt->execute();

        $conn->commit();
    } catch (Exception $e) {
        $conn->rollback();
        die("Erro ao fechar caixa: " . $e->getMessage());
    }

    header("Location: caixa.php");
    exit;
}

include __DIR__.'/includes/header.php';
include __DIR__.'/includes/navbar.php';
?>

<div class="container bg-white p-4 rounded shadow-sm mt-4">
    <h2 class="mb-4">💰 Controle de Caixa</h2>

    <?php if (!$caixa_aberto): ?>
        <form method="post" class="mb-4 card p-3 bg-light border-0 shadow-sm" onsubmit="this.querySelector('button[type=submit]').disabled = true;">
            <label class="form-label">Valor Inicial:</label>
            <input type="number" name="valor_inicial" step="0.01" min="0" required class="form-control mb-2">
            <button type="submit" name="abrir_caixa" class="btn btn-success">Abrir Caixa</button>
        </form>
    <?php else: ?>
        <div class="alert alert-info">
            <strong>Caixa Aberto:</strong><br>
            Abertura: <?= $caixa_aberto['data_abertura'] ?><br>
            Valor Inicial: <strong>R$ <?= number_format($caixa_aberto['valor_inicial'], 2, ',', '.') ?></strong>
        </div>

        <form method="post" class="row g-2 align-items-end mb-4 p-3 bg-light border rounded shadow-sm" onsubmit="this.querySelector('button[type=submit]').disabled = true;">
            <div class="col-md-2">
                <label class="form-label">Tipo</label>
                <select name="tipo" class="form-select" required>
                    <option value="entrada">Entrada</option>
                    <option value="saida">Saída</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Valor</label>
                <input type="number" name="valor" step="0.01" min="0" required class="form-control">
            </div>
            <div class="col-md-6">
                <label class="form-label">Descrição</label>
                <input type="text" name="descricao" required class="form-control">
            </div>
            <div class="col-md-2">
                <button type="submit" name="registrar_movimentacao" class="btn btn-primary w-100">Registrar</button>
            </div>
        </form>

        <form method="post">
            <button type="submit" name="fechar_caixa" class="btn btn-danger">Fechar Caixa</button>
        </form>

        <hr>

        <h4 class="mt-4">📋 Movimentações</h4>
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead class="table-dark">
                    <tr>
                        <th>Tipo</th>
                        <th>Valor</th>
                        <th>Descrição</th>
                        <th>Data</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $caixa_id = $caixa_aberto['id'];
                    $stmt = $conn->prepare("SELECT tipo, valor, descricao, data_movimentacao FROM movimentacoes WHERE caixa_id = ? ORDER BY data_movimentacao DESC");
                    $stmt->bind_param("i", $caixa_id);
                    $stmt->execute();
                    $movs = $stmt->get_result();

                    $total_entradas = 0;
                    $total_saidas = 0;
                    while ($mov = $movs->fetch_assoc()):
                        if ($mov['tipo'] === 'entrada') {
                            $total_entradas += $mov['valor'];
                        } else {
                            $total_saidas += $mov['valor'];
                        }
                    ?>
                        <tr>
                            <td><span class="badge bg-<?= $mov['tipo'] === 'entrada' ? 'success' : 'danger' ?>"><?= ucfirst($mov['tipo']) ?></span></td>
                            <td>R$ <?= number_format($mov['valor'], 2, ',', '.') ?></td>
                            <td><?= htmlspecialchars($mov['descricao']) ?></td>
                            <td><?= $mov['data_movimentacao'] ?></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>

        <?php $saldo_atual = $caixa_aberto['valor_inicial'] + $total_entradas - $total_saidas; ?>
        <div class="row text-center my-4">
            <div class="col-md-4">
                <div class="card text-bg-success">
                    <div class="card-body">
                        <h5 class="card-title">Entradas</h5>
                        <p class="card-text fs-5">R$ <?= number_format($total_entradas, 2, ',', '.') ?></p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card text-bg-danger">
                    <div class="card-body">
                        <h5 class="card-title">Saídas</h5>
                        <p class="card-text fs-5">R$ <?= number_format($total_saidas, 2, ',', '.') ?></p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card text-bg-primary">
                    <div class="card-body">
                        <h5 class="card-title">Saldo Atual</h5>
                        <p class="card-text fs-5">R$ <?= number_format($saldo_atual, 2, ',', '.') ?></p>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <hr class="my-5">

    <h3>📚 Caixas Anteriores</h3>
    <div class="table-responsive">
        <table class="table table-bordered table-striped">
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
                $result = $conn->query("SELECT * FROM caixas WHERE status = 'fechado' ORDER BY id DESC LIMIT 10");
                while ($cx = $result->fetch_assoc()):
                ?>
                    <tr>
                        <td><?= $cx['id'] ?></td>
                        <td><?= $cx['data_abertura'] ?></td>
                        <td><?= $cx['data_fechamento'] ?></td>
                        <td>R$ <?= number_format($cx['valor_inicial'], 2, ',', '.') ?></td>
                        <td>R$ <?= number_format($cx['valor_final'], 2, ',', '.') ?></td>
                        <td><a href="detalhes_caixa.php?id=<?= $cx['id'] ?>" class="btn btn-sm btn-outline-primary">Ver</a></td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include __DIR__.'/includes/footer.php'; ?>
