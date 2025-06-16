<?php
require_once 'conexx/config.php';

include __DIR__.'/includes/header.php';
include __DIR__.'/includes/navbar.php';

// Verificar caixa aberto
$result = $conn->query("SELECT id FROM caixas WHERE status='aberto' LIMIT 1");
$caixa = $result->fetch_assoc();
if (!$caixa) {
    echo "<div class='alert alert-danger container mt-4'>Não há caixa aberto. Venda cancelada.</div>";
    include __DIR__.'/includes/footer.php';
    exit;
}
$caixa_id = $caixa['id'];

// Dados do formulário
$cliente_id = isset($_POST['cliente_id']) ? intval($_POST['cliente_id']) : null;
$lista_preco_id = intval($_POST['lista_preco_id']);
$material_ids = $_POST['material_id'] ?? [];
$quantidades = $_POST['quantidade'] ?? [];
$formas_pagamento = $_POST['formas_pagamento'] ?? [];

$valor_dinheiro = isset($_POST['valor_dinheiro']) ? floatval($_POST['valor_dinheiro']) : 0;
$valor_pix = isset($_POST['valor_pix']) ? floatval($_POST['valor_pix']) : 0;
$valor_cartao = isset($_POST['valor_cartao']) ? floatval($_POST['valor_cartao']) : 0;

// Buscar preços da lista
$precos = [];
$res = $conn->query("SELECT material_id, preco FROM precos_materiais WHERE lista_id = $lista_preco_id");
while ($p = $res->fetch_assoc()) {
    $precos[$p['material_id']] = $p['preco'];
}

// Calcular total da venda
$total = 0;
$itens = [];

foreach ($material_ids as $index => $material_id) {
    $material_id = intval($material_id);
    $quantidade = floatval($quantidades[$index]);

    if (!isset($precos[$material_id])) {
        echo "<div class='alert alert-danger container mt-4'>Erro: Preço não encontrado para o material ID $material_id na lista de preço selecionada.</div>";
        include __DIR__.'/includes/footer.php';
        exit;
    }

    $preco_unitario = $precos[$material_id];
    $subtotal = $preco_unitario * $quantidade;
    $total += $subtotal;

    $itens[] = [
        'material_id' => $material_id,
        'quantidade' => $quantidade,
        'preco_unitario' => $preco_unitario,
        'subtotal' => $subtotal
    ];
}

// Total pago (soma de todos os métodos)
$valor_pago = $valor_dinheiro + $valor_pix + $valor_cartao;

// Salvar a venda
$stmt = $conn->prepare("INSERT INTO vendas (cliente_id, lista_preco_id, total, valor_dinheiro, valor_pix, valor_cartao, valor_pago) VALUES (?, ?, ?, ?, ?, ?, ?)");
$stmt->bind_param("iiddddd", $cliente_id, $lista_preco_id, $total, $valor_dinheiro, $valor_pix, $valor_cartao, $valor_pago);
$stmt->execute();
$venda_id = $stmt->insert_id;

// Salvar os itens da venda
foreach ($itens as $item) {
    $stmt_item = $conn->prepare("INSERT INTO vendas_itens (venda_id, material_id, quantidade, preco_unitario, subtotal) VALUES (?, ?, ?, ?, ?)");
    $stmt_item->bind_param(
        "iiddi",
        $venda_id,
        $item['material_id'],
        $item['quantidade'],
        $item['preco_unitario'],
        $item['subtotal']
    );
    $stmt_item->execute();
}

// Registrar no caixa apenas o dinheiro
if (in_array('dinheiro', $formas_pagamento) && $valor_dinheiro > 0) {
    $stmt_caixa = $conn->prepare("INSERT INTO movimentacoes (caixa_id, tipo, valor, descricao) VALUES (?, 'entrada', ?, ?)");
    $descricao = "Venda ID $venda_id - pagamento em dinheiro";
    $stmt_caixa->bind_param("ids", $caixa_id, $valor_dinheiro, $descricao);
    $stmt_caixa->execute();
}

// Atualizar saldo do cliente
if ($cliente_id) {
    $diferenca = $valor_pago - $total;

    $stmt_saldo = $conn->prepare("UPDATE clientes SET saldo = saldo + ? WHERE id = ?");
    $stmt_saldo->bind_param("di", $diferenca, $cliente_id);
    $stmt_saldo->execute();
}
?>

<div class="container py-4">
    <div class="section-card">
        <h2><i class="bi bi-check-circle"></i> Venda Concluída</h2>

        <p><strong>Total da Venda:</strong> R$ <?php echo number_format($total, 2, ',', '.'); ?></p>
        <p><strong>Valor Pago:</strong> R$ <?php echo number_format($valor_pago, 2, ',', '.'); ?></p>

        <div class="mt-3">
            <a href="venda.php" class="btn btn-primary">
                <i class="bi bi-cart-plus"></i> Nova Venda
            </a>
            <a href="historico_vendas.php" class="btn btn-outline-secondary">
                <i class="bi bi-list-ul"></i> Ver Histórico
            </a>
        </div>
    </div>
</div>

<?php include __DIR__.'/includes/footer.php'; ?>
