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
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Detalhes do Caixa</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="p-4 bg-light">
<div class="container bg-white p-4 rounded shadow">
    <h2>Detalhes do Caixa #<?= $caixa['id'] ?></h2>
    <p><strong>Aberto em:</strong> <?= $caixa['data_abertura'] ?></p>
    <p><strong>Fechado em:</strong> <?= $caixa['data_fechamento'] ?></p>
    <p><strong>Valor Inicial:</strong> R$ <?= number_format($caixa['valor_inicial'], 2, ',', '.') ?></p>
    <p><strong>Total de Entradas:</strong> R$ <?= number_format($total_entradas, 2, ',', '.') ?></p>
    <p><strong>Total de Saídas:</strong> R$ <?= number_format($total_saidas, 2, ',', '.') ?></p>
    <p><strong>Valor Final:</strong> R$ <?= number_format($saldo, 2, ',', '.') ?></p>

    <hr>

    <h4>Movimentações</h4>
    <table class="table table-bordered">
        <thead>
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
                    <td><?= ucfirst($mov['tipo']) ?></td>
                    <td>R$ <?= number_format($mov['valor'], 2, ',', '.') ?></td>
                    <td><?= htmlspecialchars($mov['descricao']) ?></td>
                    <td><?= $mov['data_movimentacao'] ?></td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>

    <a href="caixa.php" class="btn btn-secondary mt-3">Voltar</a>
</div>
</body>
</html>
