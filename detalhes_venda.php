<?php
require_once 'conexx/config.php';

$venda_id = isset($_GET['venda_id']) ? intval($_GET['venda_id']) : 0;

// Buscar dados da venda
$venda = $conn->query("SELECT * FROM vendas WHERE id = $venda_id")->fetch_assoc();

if (!$venda) {
    echo "<div class='alert alert-danger container mt-4'>Venda não encontrada.</div>";
    exit;
}

$itens = $conn->query("SELECT vi.*, m.nome AS material_nome FROM vendas_itens vi
                       JOIN materiais m ON vi.material_id = m.id
                       WHERE vi.venda_id = $venda_id");

include __DIR__.'/includes/header.php';
include __DIR__.'/includes/navbar.php';
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Detalhes da Venda</title>
    <style>
        .section-card {
            background-color: #ffffff;
            border-radius: 12px;
            padding: 20px;
            box-shadow: 0 0 10px rgba(0,0,0,0.05);
            margin-bottom: 30px;
        }
        h2 {
            margin-bottom: 20px;
            color: #343a40;
        }
    </style>
</head>
<body>

<div class="container py-4">

    <div class="section-card">
        <h2><i class="bi bi-list-check"></i> Detalhes da Venda #<?php echo $venda_id; ?></h2>

        <p><strong>Data:</strong> <?php echo date('d/m/Y H:i', strtotime($venda['data'])); ?></p>
        <p><strong>Total da Venda:</strong> R$ <?php echo number_format($venda['total'], 2, ',', '.'); ?></p>
        <p><strong>Valor Pago Total:</strong> R$ <?php echo number_format($venda['valor_pago'], 2, ',', '.'); ?></p>

        <h5>Formas de Pagamento:</h5>
        <ul class="list-group mb-3">
            <li class="list-group-item d-flex justify-content-between">
                <span>Dinheiro:</span>
                <strong>R$ <?php echo number_format($venda['valor_dinheiro'], 2, ',', '.'); ?></strong>
            </li>
            <li class="list-group-item d-flex justify-content-between">
                <span>Pix:</span>
                <strong>R$ <?php echo number_format($venda['valor_pix'], 2, ',', '.'); ?></strong>
            </li>
            <li class="list-group-item d-flex justify-content-between">
                <span>Cartão:</span>
                <strong>R$ <?php echo number_format($venda['valor_cartao'], 2, ',', '.'); ?></strong>
            </li>
        </ul>

        <h5>Itens da Venda:</h5>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Material</th>
                    <th>Quantidade</th>
                    <th>Preço Unitário</th>
                    <th>Subtotal</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($item = $itens->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo $item['material_nome']; ?></td>
                        <td><?php echo $item['quantidade']; ?></td>
                        <td>R$ <?php echo number_format($item['preco_unitario'], 2, ',', '.'); ?></td>
                        <td>R$ <?php echo number_format($item['subtotal'], 2, ',', '.'); ?></td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>

        <a href="historico_vendas.php" class="btn btn-secondary mt-3">
            <i class="bi bi-arrow-left"></i> Voltar ao Histórico
        </a>
    </div>

</div>

<?php include __DIR__.'/includes/footer.php'; ?>
</body>
</html>
