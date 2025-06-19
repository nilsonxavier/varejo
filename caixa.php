<?php
require_once 'verifica_login.php';
require_once 'conexx/config.php';

$usuario_id = $_SESSION['usuario_id'];

// Verifica se existe caixa aberto
$caixa_aberto = $conn->query("SELECT * FROM caixas WHERE status = 'aberto' ORDER BY id DESC LIMIT 1")->fetch_assoc();

// Abertura de caixa
if (isset($_POST['abrir_caixa'])) {
    // Revalida para evitar duplicação
    $existe_caixa_aberto = $conn->query("SELECT id FROM caixas WHERE status = 'aberto'")->num_rows;
    if ($existe_caixa_aberto > 0) {
        header("Location: caixa.php");
        exit;
    }

    $valor_inicial = floatval($_POST['valor_inicial']);
    $data_abertura = date('Y-m-d H:i:s');

    $stmt = $conn->prepare("INSERT INTO caixas (usuario_id, data_abertura, valor_inicial, status) VALUES (?, ?, ?, 'aberto')");
    $stmt->bind_param("isd", $usuario_id, $data_abertura, $valor_inicial);
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
    $data_movimentacao = date('Y-m-d H:i:s');

    $stmt = $conn->prepare("INSERT INTO movimentacoes (caixa_id, tipo, valor, descricao, data_movimentacao) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("isdss", $caixa_id, $tipo, $valor, $descricao, $data_movimentacao);
    $stmt->execute();

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

include __DIR__.'/includes/header.php';
include __DIR__.'/includes/navbar.php';
include __DIR__.'/includes/footer.php';
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Controle de Caixa</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container py-5">
    <div class="bg-white p-4 rounded shadow-sm">
        <h2 class="mb-4 border-bottom pb-2">Controle de Caixa</h2>

        <?php if (!$caixa_aberto): ?>
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
            <div class="mb-4">
                <p class="mb-1"><strong>Caixa aberto em:</strong> <?= $caixa_aberto['data_abertura'] ?></p>
                <p><strong>Valor inicial:</strong> R$ <?= number_format($caixa_aberto['valor_inicial'], 2, ',', '.') ?></p>
            </div>

            <form method="post" class="row g-3 mb-4 align-items-end">
                <div class="col-md-2">
                    <label class="form-label">Tipo</label>
                    <select name="tipo" class="form-select" required>
                        <option value="entrada">Entrada</option>
                        <option value="saida">Saída</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Valor</label>
                    <input type="number" name="valor" step="0.01" min="0" required class="form-control" placeholder="Valor">
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
                                <td><?= ucfirst($mov['tipo']) ?></td>
                                <td>R$ <?= number_format($mov['valor'], 2, ',', '.') ?></td>
                                <td><?= htmlspecialchars($mov['descricao']) ?></td>
                                <td><?= $mov['data_movimentacao'] ?></td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>

            <?php
            $saldo_atual = $caixa_aberto['valor_inicial'] + $total_entradas - $total_saidas;
            ?>
            <div class="card mt-4 border-0 shadow-sm">
                <div class="card-body">
                    <h5 class="card-title">Resumo do Caixa</h5>
                    <p class="card-text text-success">
                        <strong>Total de Entradas:</strong> R$ <?= number_format($total_entradas, 2, ',', '.') ?>
                    </p>
                    <p class="card-text text-danger">
                        <strong>Total de Saídas:</strong> R$ <?= number_format($total_saidas, 2, ',', '.') ?>
                    </p>
                    <p class="card-text text-primary">
                        <strong>Saldo Atual:</strong> R$ <?= number_format($saldo_atual, 2, ',', '.') ?>
                    </p>
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
                    $result = $conn->query("SELECT * FROM caixas WHERE status = 'fechado' ORDER BY id DESC LIMIT 10");
                    while ($cx = $result->fetch_assoc()):
                    ?>
                        <tr>
                            <td><?= $cx['id'] ?></td>
                            <td><?= $cx['data_abertura'] ?></td>
                            <td><?= $cx['data_fechamento'] ?></td>
                            <td>R$ <?= number_format($cx['valor_inicial'], 2, ',', '.') ?></td>
                            <td>R$ <?= number_format($cx['valor_final'], 2, ',', '.') ?></td>
                            <td>
                                <a href="detalhes_caixa.php?id=<?= $cx['id'] ?>" class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-eye"></i> Ver
                                </a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Bootstrap Icons -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
</body>