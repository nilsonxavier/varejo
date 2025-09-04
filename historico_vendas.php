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
        LEFT JOIN clientes c 
            ON v.cliente_id = c.id 
            AND c.empresa_id = " . intval($empresa_id) . "
        WHERE v.empresa_id = " . intval($empresa_id) . "
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
    <!-- Seção principal com área de impressão -->
    <div class="section-card print-area">
        <!-- Cabeçalho com botão de impressão -->
        <div class="no-print d-flex justify-content-between align-items-center mb-3">
            <h2><i class="bi bi-receipt"></i> Histórico de Vendas</h2>
            <button class="btn btn-outline-primary btn-sm" onclick="imprimirPagina()">
                <i class="bi bi-printer"></i> Imprimir Relatório
            </button>
        </div>
        
        <!-- Título para impressão -->
        <div class="d-none d-print-block text-center mb-4">
            <h2>Relatório de Vendas</h2>
            <p>Gerado em: <?php echo date('d/m/Y H:i'); ?></p>
        </div>

        <div class="table-responsive">
            <table class="table table-bordered table-striped">
                <thead class="table-dark">
                    <tr>
                        <th>ID Venda</th>
                        <th>Data</th>
                        <th>Cliente</th>
                        <th>Total Venda (R$)</th>
                        <th>Total Pago (R$)</th>
                        <th>Diferença (Saldo)</th>
                        <th class="no-print">Detalhes</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result->num_rows > 0): 
                        $total_vendas = 0;
                        $total_pago = 0;
                    ?>
                        <?php while ($venda = $result->fetch_assoc()): 
                            $diferenca = $venda['valor_pago'] - $venda['total'];
                            $total_vendas += $venda['total'];
                            $total_pago += $venda['valor_pago'];
                            ?>
                            <tr>
                                <td><?php echo $venda['id']; ?></td>
                                <td><?php echo date('d/m/Y H:i', strtotime($venda['data'])); ?></td>
                                <td><?php echo $venda['cliente_nome'] ? $venda['cliente_nome'] : 'Sem cliente'; ?></td>
                                <td>R$ <?php echo number_format($venda['total'], 2, ',', '.'); ?></td>
                                <td>R$ <?php echo number_format($venda['valor_pago'], 2, ',', '.'); ?></td>
                                <td>
                                    <span class="badge bg-<?php echo $diferenca >= 0 ? 'success' : 'danger'; ?>">
                                        R$ <?php echo number_format($diferenca, 2, ',', '.'); ?>
                                    </span>
                                </td>
                                <td class="no-print">
                                    <a href="detalhes_venda.php?venda_id=<?php echo $venda['id']; ?>" class="btn btn-sm btn-outline-primary">
                                        <i class="bi bi-search"></i> Ver Itens
                                    </a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                        
                        <!-- Totais -->
                        <tr class="table-info fw-bold">
                            <td colspan="3">TOTAIS</td>
                            <td>R$ <?php echo number_format($total_vendas, 2, ',', '.'); ?></td>
                            <td>R$ <?php echo number_format($total_pago, 2, ',', '.'); ?></td>
                            <td>
                                <span class="badge bg-<?php echo ($total_pago - $total_vendas) >= 0 ? 'success' : 'danger'; ?>">
                                    R$ <?php echo number_format($total_pago - $total_vendas, 2, ',', '.'); ?>
                                </span>
                            </td>
                            <td class="no-print"></td>
                        </tr>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" class="text-center">
                                <i class="bi bi-inbox"></i> Nenhuma venda registrada.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    
    <!-- Filtros e controles (não aparecem na impressão) -->
    <div class="section-card no-print">
        <h4><i class="bi bi-funnel"></i> Filtros e Controles</h4>
        
        <form method="GET" class="row g-3">
            <div class="col-md-3">
                <label class="form-label">Data Início</label>
                <input type="date" class="form-control" name="data_inicio" 
                       value="<?php echo $_GET['data_inicio'] ?? ''; ?>">
            </div>
            <div class="col-md-3">
                <label class="form-label">Data Fim</label>
                <input type="date" class="form-control" name="data_fim" 
                       value="<?php echo $_GET['data_fim'] ?? ''; ?>">
            </div>
            <div class="col-md-3">
                <label class="form-label">Cliente</label>
                <select class="form-select" name="cliente_id">
                    <option value="">Todos os clientes</option>
                    <!-- Adicionar opções de clientes aqui -->
                </select>
            </div>
            <div class="col-md-3 d-flex align-items-end">
                <button type="submit" class="btn btn-primary me-2">
                    <i class="bi bi-search"></i> Filtrar
                </button>
                <a href="?" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-clockwise"></i> Limpar
                </a>
            </div>
        </form>
    </div>
    
    <!-- Resumo estatístico (não aparece na impressão) -->
    <div class="section-card no-print">
        <h4><i class="bi bi-graph-up"></i> Resumo Estatístico</h4>
        
        <div class="row g-3">
            <?php if ($result->num_rows > 0): 
                $conn->data_seek(0); // Reset do ponteiro
                $total_vendas_count = 0;
                $total_vendas_valor = 0;
                $total_pago_valor = 0;
                
                while ($venda = $result->fetch_assoc()) {
                    $total_vendas_count++;
                    $total_vendas_valor += $venda['total'];
                    $total_pago_valor += $venda['valor_pago'];
                }
                
                $ticket_medio = $total_vendas_count > 0 ? $total_vendas_valor / $total_vendas_count : 0;
                $diferenca_total = $total_pago_valor - $total_vendas_valor;
            ?>
                <div class="col-md-3">
                    <div class="card bg-primary text-white">
                        <div class="card-body text-center">
                            <i class="bi bi-receipt fs-1"></i>
                            <h4><?php echo $total_vendas_count; ?></h4>
                            <small>Total de Vendas</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-success text-white">
                        <div class="card-body text-center">
                            <i class="bi bi-currency-dollar fs-1"></i>
                            <h4>R$ <?php echo number_format($total_vendas_valor, 2, ',', '.'); ?></h4>
                            <small>Valor Total</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-info text-white">
                        <div class="card-body text-center">
                            <i class="bi bi-calculator fs-1"></i>
                            <h4>R$ <?php echo number_format($ticket_medio, 2, ',', '.'); ?></h4>
                            <small>Ticket Médio</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-<?php echo $diferenca_total >= 0 ? 'success' : 'danger'; ?> text-white">
                        <div class="card-body text-center">
                            <i class="bi bi-graph-<?php echo $diferenca_total >= 0 ? 'up' : 'down'; ?> fs-1"></i>
                            <h4>R$ <?php echo number_format($diferenca_total, 2, ',', '.'); ?></h4>
                            <small>Saldo Total</small>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Log das configurações atuais
    console.log('Histórico de Vendas carregado');
    console.log('Tema:', <?php echo json_encode($TEMA_DARK ?? false); ?> ? 'escuro' : 'claro');
    console.log('Papel:', <?php echo json_encode($TAMANHO_PAPEL ?? 'A4'); ?>);
    
    // Exemplo de funcionalidade específica
    const tabela = document.querySelector('table');
    if (tabela) {
        // Adicionar funcionalidade de ordenação ou filtros em tempo real se necessário
        console.log('Tabela de vendas encontrada com', tabela.rows.length - 1, 'vendas');
    }
});
</script>

<?php include __DIR__.'/includes/footer.php'; ?>
</body>
</html>
