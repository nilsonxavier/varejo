<?php
require_once 'verifica_login.php';
require_once 'conexx/config.php';
include __DIR__.'/includes/header.php';
include __DIR__.'/includes/navbar.php';

$cliente_id = intval($_GET['id'] ?? 0);
$empresa_id = $_SESSION['usuario_empresa'];

// Busca dados do cliente
$stmt = $conn->prepare("SELECT nome FROM clientes WHERE id = ? AND empresa_id = ?");
$stmt->bind_param('ii', $cliente_id, $empresa_id);
$stmt->execute();
$res = $stmt->get_result();
$cliente = $res->fetch_assoc();

if (!$cliente) {
    echo '<div class="alert alert-danger">Cliente não encontrado.</div>';
    exit;
}

// Filtro por período
$inicio = $_GET['inicio'] ?? '';
$fim = $_GET['fim'] ?? '';
$where = "cliente_id = ?";
$params = [$cliente_id];
$types = "i";
if ($inicio && $fim) {
    $where .= " AND data_movimentacao BETWEEN ? AND ?";
    $params[] = $inicio . " 00:00:00";
    $params[] = $fim . " 23:59:59";
    $types .= "ss";
} elseif ($inicio) {
    $where .= " AND data_movimentacao >= ?";
    $params[] = $inicio . " 00:00:00";
    $types .= "s";
} elseif ($fim) {
    $where .= " AND data_movimentacao <= ?";
    $params[] = $fim . " 23:59:59";
    $types .= "s";
}
$sql = "SELECT tipo, valor, descricao, saldo_apos, data_movimentacao FROM movimentacoes_clientes WHERE $where ORDER BY data_movimentacao DESC";
$stmt = $conn->prepare($sql);
if ($params) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$res = $stmt->get_result();
$movs = [];
while ($row = $res->fetch_assoc()) $movs[] = $row;

// Busca saldo atual
$stmt = $conn->prepare("SELECT saldo FROM clientes WHERE id = ?");
$stmt->bind_param('i', $cliente_id);
$stmt->execute();
$res = $stmt->get_result();
$saldo = $res->fetch_assoc()['saldo'] ?? 0;
?>

<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
<div class="container py-3 d-flex justify-content-center align-items-start" style="min-height:70vh;">
    <div class="section-card">
        <div class="extrato-header d-flex align-items-center gap-2 mb-2">
            <i class="bi bi-receipt fs-3 text-primary"></i>
            <h4 class="mb-0">Extrato do Cliente</h4>
        </div>
        <div class="mb-2"><strong><i class="bi bi-person"></i> Cliente:</strong> <?= htmlspecialchars($cliente['nome']) ?></div>
        <div class="extrato-saldo mb-2"><i class="bi bi-cash-coin"></i> Saldo atual: R$ <?= number_format($saldo,2,',','.') ?></div>
        <form method="get" class="mb-2">
            <input type="hidden" name="id" value="<?= $cliente_id ?>">
            <div class="row g-2 align-items-center">
                <div class="col-12 col-md-auto">
                    <label class="form-label mb-0"><i class="bi bi-calendar-range"></i> Período:</label>
                </div>
                <div class="col-6 col-md-auto">
                    <input type="date" name="inicio" value="<?= htmlspecialchars($inicio) ?>" class="form-control form-control-sm" placeholder="Início">
                </div>
                <div class="col-6 col-md-auto">
                    <input type="date" name="fim" value="<?= htmlspecialchars($fim) ?>" class="form-control form-control-sm" placeholder="Fim">
                </div>
                <div class="col-12 col-md-auto mt-2 mt-md-0 d-flex gap-2">
                    <button class="btn btn-sm btn-primary w-100" type="submit"><i class="bi bi-search"></i> Filtrar</button>
                    <a href="extrato_cliente.php?id=<?= $cliente_id ?>" class="btn btn-sm btn-outline-secondary w-100"><i class="bi bi-x-circle"></i> Limpar</a>
                </div>
            </div>
        </form>
        <div class="extrato-table mt-2">
            <table class="table table-sm mb-0">
                <thead>
                    <tr>
                        <th><i class="bi bi-calendar-date"></i> Data</th>
                        <th><i class="bi bi-arrow-left-right"></i> Tipo</th>
                        <th><i class="bi bi-currency-dollar"></i> Valor</th>
                        <th><i class="bi bi-pencil"></i> Origem</th>
                        <th><i class="bi bi-wallet2"></i> Saldo</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach($movs as $m): ?>
                    <tr>
                        <td><?= date('d/m/Y H:i', strtotime($m['data_movimentacao'])) ?></td>
                        <td><?= htmlspecialchars($m['tipo']) ?></td>
                        <td class="<?= $m['tipo'] === 'debito' ? 'text-danger' : ($m['tipo'] === 'credito' ? 'text-success' : '') ?>">R$ <?= number_format($m['valor'],2,',','.') ?></td>
                        <td><?= htmlspecialchars($m['descricao']) ?></td>
                        <td>R$ <?= number_format($m['saldo_apos'],2,',','.') ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <button class="btn btn-success w-100 mt-3" onclick="window.print()"><i class="bi bi-printer"></i> Imprimir Extrato</button>
    </div>
</div>

<style>
body {
    background: linear-gradient(135deg, #f8f9fa 60%, #e9ecef 100%);
}
.section-card {
    background: #fff;
    border-radius: 18px;
    padding: 22px 18px;
    box-shadow: 0 4px 18px rgba(0,0,0,0.08);
    margin-bottom: 30px;
    max-width: 420px;
    width: 100%;
    margin-left: auto;
    margin-right: auto;
}
.extrato-header {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-bottom: 10px;
}
.extrato-header i {
    font-size: 1.7em;
    color: #0d6efd;
}
.extrato-saldo {
    font-size: 1.2em;
    font-weight: bold;
    color: #198754;
    background: #e6fff2;
    border-radius: 8px;
    padding: 8px 12px;
    margin-bottom: 10px;
    display: inline-block;
}
.extrato-table {
    background: #f8f9fa;
    border-radius: 10px;
    overflow: hidden;
    box-shadow: 0 2px 8px rgba(0,0,0,0.04);
}
.extrato-table th {
    background: #0d6efd;
    color: #fff;
    font-size: 0.93em;
    font-weight: 500;
    border: none;
}
.extrato-table td {
    font-size: 0.92em;
    border: none;
    padding: 6px 5px;
}
.extrato-table th {
    background: #0d6efd;
    color: #fff;
    font-size: 0.98em;
    font-weight: 500;
    border: none;
}
.extrato-table td {
    font-size: 0.97em;
    border: none;
    padding: 7px 6px;
}
.extrato-table tr:nth-child(even) td {
    background: #f3f6fa;
}
.extrato-table tr:nth-child(odd) td {
    background: #fff;
}
.extrato-table td:nth-child(2) {
    font-weight: 500;
}
.extrato-table td:nth-child(3) {
    color: #0d6efd;
    font-weight: bold;
}
.extrato-table td:nth-child(5) {
    color: #198754;
    font-size: 0.98em;
}
.extrato-table td.text-danger {
    color: #dc3545 !important;
}
.extrato-table td.text-success {
    color: #198754 !important;
}
@media (max-width: 600px) {
    .container {
        padding-left: 0 !important;
        padding-right: 0 !important;
        min-width: 100vw;
        justify-content: center !important;
    }
    .section-card {
        padding: 10px 2px;
        max-width: 98vw;
        width: 98vw;
        margin-left: auto;
        margin-right: auto;
    }
    .extrato-header {
        flex-direction: column;
        align-items: flex-start;
    }
    .extrato-saldo {
        font-size: 1.1em;
        padding: 7px 8px;
    }
        .extrato-table th, .extrato-table td {
            font-size: 0.90em;
            padding: 5px 1px;
    }
    .row.g-2 {
        gap: 0.5rem !important;
    }
    .row .col-12, .row .col-6 {
        padding-right: 0.3rem;
        padding-left: 0.3rem;
    }
    .row .col-md-auto {
        width: 100%;
    }
}
@media print {
    body * { visibility: hidden; }
    .container, .container * { visibility: visible; }
    .container { max-width: 80mm !important; }
    .section-card { box-shadow:none; border:none; }
    table { font-size:11px; }
    button { display:none; }
}
</style>

<?php include __DIR__.'/includes/footer.php'; ?>
