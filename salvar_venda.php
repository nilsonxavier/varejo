<?php
require_once 'conexx/config.php';
include __DIR__.'/includes/header.php';
include __DIR__.'/includes/navbar.php';

$data_atual = date('Y-m-d H:i:s');

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

$valor_dinheiro = isset($_POST['valor_dinheiro']) ? floatval($_POST['valor_dinheiro']) : 0;
$valor_pix = isset($_POST['valor_pix']) ? floatval($_POST['valor_pix']) : 0;
$valor_cartao = isset($_POST['valor_cartao']) ? floatval($_POST['valor_cartao']) : 0;
$gerar_troco = isset($_POST['gerar_troco']); // Checkbox opcional

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

// Total pago
$valor_pago = $valor_dinheiro + $valor_pix + $valor_cartao;
$diferenca = $valor_pago - $total;

// Salvar a venda
$stmt = $conn->prepare("INSERT INTO vendas (cliente_id, lista_preco_id, total, valor_dinheiro, valor_pix, valor_cartao, valor_pago, data) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
$stmt->bind_param("iiddddds", $cliente_id, $lista_preco_id, $total, $valor_dinheiro, $valor_pix, $valor_cartao, $valor_pago, $data_atual);
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

// Registrar entradas no caixa
if ($valor_dinheiro > 0) {
    $stmt_caixa = $conn->prepare("INSERT INTO movimentacoes (caixa_id, tipo, valor, descricao, data_movimentacao) VALUES (?, 'entrada', ?, ?, ?)");
    $descricao = "Venda ID $venda_id - pagamento em dinheiro";
    $stmt_caixa->bind_param("idss", $caixa_id, $valor_dinheiro, $descricao, $data_atual);
    $stmt_caixa->execute();
}

// Se houve troco (dinheiro > total e cliente pagou mais) e usuário marcou para gerar troco
if ($diferenca > 0 && $valor_dinheiro > 0 && $gerar_troco) {
    $stmt_troco = $conn->prepare("INSERT INTO movimentacoes (caixa_id, tipo, valor, descricao, data_movimentacao) VALUES (?, 'saida', ?, ?, ?)");
    $descricao_troco = "Troco da Venda ID $venda_id";
    $stmt_troco->bind_param("idss", $caixa_id, $diferenca, $descricao_troco, $data_atual);
    $stmt_troco->execute();
}

// Atualizar saldo do cliente (positivo ou negativo)
if ($cliente_id) {
    $ajuste_saldo = 0;

    if ($diferenca < 0) {
        // Cliente pagou menos
        $ajuste_saldo = $diferenca;
    } elseif ($diferenca > 0 && (! $gerar_troco || $valor_dinheiro <= 0)) {
        // Cliente pagou mais, mas sem troco no caixa (ou pagou via pix/cartão)
        $ajuste_saldo = $diferenca;
    }

    if ($ajuste_saldo != 0) {
        $stmt_saldo = $conn->prepare("UPDATE clientes SET saldo = saldo + ? WHERE id = ?");
        $stmt_saldo->bind_param("di", $ajuste_saldo, $cliente_id);
        $stmt_saldo->execute();
    }
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
