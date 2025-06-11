<?php
require_once 'verifica_login.php';
require_once 'conexx/config.php';

$caixa_id = intval($_GET['id']);
$caixa = $conn->query("SELECT * FROM caixas WHERE id = $caixa_id")->fetch_assoc();

if (!$caixa) {
    echo "Caixa não encontrado.";
    exit;
}

$movs = $conn->query("SELECT * FROM movimentacoes WHERE caixa_id = $caixa_id ORDER BY data_movimentacao DESC");

$total_entradas = $conn->query("SELECT SUM(valor) as total FROM movimentacoes WHERE caixa_id = $caixa_id AND tipo = 'entrada'")->fetch_assoc()['total'] ?? 0;
$total_saidas = $conn->query("SELECT SUM(valor) as total FROM movimentacoes WHERE caixa_id = $caixa_id AND tipo = 'saida'")->fetch_assoc()['total'] ?? 0;
$saldo = $caixa['valor_inicial'] + $total_entradas - $total_saidas;

include __DIR__.'/includes/header.php';
include __DIR__.'/includes/navbar.php';

?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Detalhes do Caixa</title>
    
</head>
<body class="bg-light p-4">

<div class="container bg-white p-4 rounded shadow">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2><i class="bi bi-cash-coin me-2"></i>Caixa #<?= $caixa['id'] ?></h2>
        <a href="caixa.php" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Voltar
        </a>
    </div>

    <div class="row g-3">
        <div class="col-md-4">
            <div class="card border-primary">
                <div class="card-body">
                    <h5 class="card-title">Abertura</h5>
                    <p class="card-text mb-1"><strong>Aberto em:</strong><br> <?= $caixa['data_abertura'] ?></p>
                    <p class="card-text"><strong>Fechado em:</strong><br> <?= $caixa['data_fechamento'] ?></p>
                </div>
            </div>
        </div>

        <div class="col-md-8">
            <div class="row g-3">
                <div class="col-md-4">
                    <div class="card text-bg-secondary">
                        <div class="card-body">
                            <h6 class="card-title"><i class="bi bi-currency-dollar me-1"></i>Valor Inicial</h6>
                            <p class="card-text fs-5">R$ <?= number_format($caixa['valor_inicial'], 2, ',', '.') ?></p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card text-bg-success">
                        <div class="card-body">
                            <h6 class="card-title"><i class="bi bi-arrow-down-circle me-1"></i>Entradas</h6>
                            <p class="card-text fs-5">R$ <?= number_format($total_entradas, 2, ',', '.') ?></p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card text-bg-danger">
                        <div class="card-body">
                            <h6 class="card-title"><i class="bi bi-arrow-up-circle me-1"></i>Saídas</h6>
                            <p class="card-text fs-5">R$ <?= number_format($total_saidas, 2, ',', '.') ?></p>
                        </div>
                    </div>
                </div>
                <div class="col-12">
                    <div class="card text-bg-primary">
                        <div class="card-body">
                            <h6 class="card-title"><i class="bi bi-wallet2 me-1"></i>Saldo Final</h6>
                            <p class="card-text fs-4 fw-bold">R$ <?= number_format($saldo, 2, ',', '.') ?></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <hr class="my-4">

    <h4 class="mb-3"><i class="bi bi-journal-text me-2"></i>Movimentações</h4>
    <div class="table-responsive">
        <table class="table table-striped table-hover align-middle">
            <thead class="table-light">
                <tr>
                    <th>Tipo</th>
                    <th>Valor</th>
                    <th>Descrição</th>
                    <th>Data</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($mov = $movs->fetch_assoc()): ?>
                    <tr>
                        <td>
                            <span class="badge <?= $mov['tipo'] === 'entrada' ? 'bg-success' : 'bg-danger' ?>">
                                <?= ucfirst($mov['tipo']) ?>
                            </span>
                        </td>
                        <td>R$ <?= number_format($mov['valor'], 2, ',', '.') ?></td>
                        <td><?= htmlspecialchars($mov['descricao']) ?></td>
                        <td><?= $mov['data_movimentacao'] ?></td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

</body>
</html>
