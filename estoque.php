<?php
require_once 'verifica_login.php';
require_once 'conexx/config.php';
include __DIR__.'/includes/header.php';
include __DIR__.'/includes/navbar.php';

// Carrega materiais com saldo atual
$res = $conn->query("
    SELECT m.id, m.nome, 
           COALESCE(SUM(CASE WHEN e.tipo = 'entrada' THEN e.quantidade ELSE 0 END), 0) -
           COALESCE(SUM(CASE WHEN e.tipo = 'saida' THEN e.quantidade ELSE 0 END), 0) AS saldo
    FROM materiais m
    LEFT JOIN estoque e ON e.material_id = m.id
    GROUP BY m.id
    ORDER BY m.nome
");

$materiais = [];
while ($row = $res->fetch_assoc()) {
    $materiais[] = $row;
}

// Carrega movimentações para listagem
$resMov = $conn->query("SELECT e.*, m.nome AS material_nome FROM estoque e JOIN materiais m ON m.id = e.material_id ORDER BY e.data_movimentacao DESC");
$movimentacoes = [];
while ($row = $resMov->fetch_assoc()) {
    $movimentacoes[] = $row;
}
?>

<div class="container py-4">
    <div class="section-card mb-4">
        <h4>Controle de Estoque</h4>

        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Material</th>
                    <th>Estoque Atual</th>
                    <th>Ação</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($materiais as $m): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($m['nome']); ?></td>
                        <td><?php echo number_format($m['saldo'], 2, ',', '.'); ?></td>
                        <td>
                            <form action="atualizar_estoque.php" method="POST" class="d-flex flex-wrap gap-1">
                                <input type="hidden" name="material_id" value="<?php echo $m['id']; ?>">
                                <select name="tipo" class="form-select" required>
                                    <option value="entrada">Entrada</option>
                                    <option value="saida">Saída</option>
                                </select>
                                <input type="number" name="quantidade" step="0.01" min="0" required class="form-control" placeholder="Qtd.">
                                <input type="text" name="descricao" class="form-control" placeholder="Descrição">
                                <button type="submit" class="btn btn-primary btn-sm">Atualizar</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <div class="section-card">
        <h5>Histórico de Movimentações</h5>
        <input type="text" class="form-control mb-3" id="filtroMov" placeholder="Filtrar por material ou descrição...">

        <table class="table table-striped">
            <thead>
                <tr>
                    <th>Data</th>
                    <th>Material</th>
                    <th>Tipo</th>
                    <th>Quantidade</th>
                    <th>Descrição</th>
                </tr>
            </thead>
            <tbody id="tabelaMovimentacoes">
                <?php foreach ($movimentacoes as $mov): ?>
                    <tr>
                        <td><?php echo date('d/m/Y H:i', strtotime($mov['data_movimentacao'])); ?></td>
                        <td><?php echo htmlspecialchars($mov['material_nome']); ?></td>
                        <td><?php echo ucfirst($mov['tipo']); ?></td>
                        <td><?php echo number_format($mov['quantidade'], 2, ',', '.'); ?></td>
                        <td><?php echo htmlspecialchars($mov['descricao'] ?? 'Sem descrição'); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
document.getElementById('filtroMov').addEventListener('input', function() {
    const termo = this.value.toLowerCase();
    document.querySelectorAll('#tabelaMovimentacoes tr').forEach(function(row) {
        const texto = row.innerText.toLowerCase();
        row.style.display = texto.includes(termo) ? '' : 'none';
    });
});
</script>

<?php include __DIR__.'/includes/footer.php'; ?>
