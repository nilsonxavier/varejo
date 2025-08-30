<?php
require_once 'conexx/config.php';
require_once 'verifica_login.php';

include __DIR__.'/includes/header.php';
include __DIR__.'/includes/navbar.php';

// Buscar histórico de vendas
// Buscar histórico de vendas apenas da empresa do usuário
$empresa_id = $_SESSION['usuario_empresa'];
$sql = "SELECT v.id, v.data, v.total, v.valor_pago, c.nome AS cliente_nome
        FROM vendas v
        LEFT JOIN clientes c ON v.cliente_id = c.id
        WHERE c.empresa_id = " . intval($empresa_id) . "
        ORDER BY v.data DESC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Histórico de Vendas</title>
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
        .table th, .table td {
            vertical-align: middle;
        }
    </style>
</head>
<body>

<div class="container py-4">

    <div class="section-card">
        <h2><i class="bi bi-receipt"></i> Histórico de Vendas</h2>

        <table class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>ID Venda</th>
                    <th>Data</th>
                    <th>Cliente</th>
                    <th>Total Venda (R$)</th>
                    <th>Total Pago (R$)</th>
                    <th>Diferença (Saldo)</th>
                    <th>Detalhes</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($result->num_rows > 0): ?>
                    <?php while ($venda = $result->fetch_assoc()): 
                        $diferenca = $venda['valor_pago'] - $venda['total'];
                        ?>
                        <tr>
                            <td><?php echo $venda['id']; ?></td>
                            <td><?php echo date('d/m/Y H:i', strtotime($venda['data'])); ?></td>
                            <td><?php echo $venda['cliente_nome'] ? $venda['cliente_nome'] : 'Sem cliente'; ?></td>
                            <td><?php echo number_format($venda['total'], 2, ',', '.'); ?></td>
                            <td><?php echo number_format($venda['valor_pago'], 2, ',', '.'); ?></td>
                            <td><?php echo number_format($diferenca, 2, ',', '.'); ?></td>
                            <td>
                                <a href="detalhes_venda.php?venda_id=<?php echo $venda['id']; ?>" class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-search"></i> Ver Itens
                                </a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7" class="text-center">Nenhuma venda registrada.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

</div>

<?php include __DIR__.'/includes/footer.php'; ?>
</body>
</html>
