<?php
require_once 'conexx/config.php';
require_once 'verifica_login.php';
include __DIR__.'/includes/header.php';
include __DIR__.'/includes/navbar.php';

$data_atual = date('Y-m-d H:i:s');
$usuario_id = $_SESSION['usuario_id'];
$empresa_id = $_SESSION['usuario_empresa'];

// Verificar caixa aberto
// Buscar caixa aberto da empresa
$result = $conn->query("SELECT id FROM caixas WHERE status='aberto' AND empresa_id = " . intval($empresa_id) . " LIMIT 1");
$caixa = $result->fetch_assoc();
if (!$caixa) {
    echo "<div class='alert alert-danger container mt-4'>Não há caixa aberto. Venda cancelada.</div>";
    include __DIR__.'/includes/footer.php';
    exit;
}
$caixa_id = $caixa['id'];

// Função para saldo de estoque
function obterSaldoEstoque($conn, $material_id) {
    $sql = "SELECT 
                COALESCE(SUM(CASE WHEN tipo = 'entrada' THEN quantidade ELSE 0 END), 0) -
                COALESCE(SUM(CASE WHEN tipo = 'saida' THEN quantidade ELSE 0 END), 0) AS saldo 
            FROM estoque WHERE material_id = $material_id";
    $res = $conn->query($sql);
    $row = $res->fetch_assoc();
    return floatval($row['saldo']);
}

// Dados do formulário
$cliente_id = isset($_POST['cliente_id']) ? intval($_POST['cliente_id']) : null;
// Determina a lista de preço a ser usada: POST > cliente > lista padrão da empresa > primeira lista da empresa
$lista_preco_id = isset($_POST['lista_preco_id']) ? intval(explode(' ', $_POST['lista_preco_id'])[0]) : 0;
if ($lista_preco_id <= 0) {
    // tenta usar lista do cliente
    if ($cliente_id) {
        $row = $conn->query("SELECT lista_preco_id FROM clientes WHERE id = " . intval($cliente_id) . " LIMIT 1")->fetch_assoc();
        if (!empty($row['lista_preco_id'])) $lista_preco_id = intval($row['lista_preco_id']);
    }
}
if ($lista_preco_id <= 0) {
    // tenta lista marcada como padrao para a empresa
    $stmt_lp = $conn->prepare("SELECT id FROM listas_precos WHERE empresa_id = ? AND padrao = 1 LIMIT 1");
    $stmt_lp->bind_param('i', $empresa_id);
    $stmt_lp->execute();
    $res_lp = $stmt_lp->get_result();
    if ($res_lp && $r_lp = $res_lp->fetch_assoc()) {
        $lista_preco_id = intval($r_lp['id']);
    } else {
        // fallback para primeira lista da empresa
        $stmt_lp2 = $conn->prepare("SELECT id FROM listas_precos WHERE empresa_id = ? ORDER BY id LIMIT 1");
        $stmt_lp2->bind_param('i', $empresa_id);
        $stmt_lp2->execute();
        $res_lp2 = $stmt_lp2->get_result();
        if ($res_lp2 && $r_lp2 = $res_lp2->fetch_assoc()) $lista_preco_id = intval($r_lp2['id']);
    }
}
$material_ids = $_POST['material_id'] ?? [];
$quantidades = $_POST['quantidade'] ?? [];
$precos_unitarios = $_POST['preco_unitario'] ?? [];

$valor_dinheiro = isset($_POST['valor_dinheiro']) ? floatval($_POST['valor_dinheiro']) : 0;
$valor_pix = isset($_POST['valor_pix']) ? floatval($_POST['valor_pix']) : 0;
$valor_cartao = isset($_POST['valor_cartao']) ? floatval($_POST['valor_cartao']) : 0;
$gerar_troco = isset($_POST['gerar_troco']);

$ids_verificar = array_map(fn($m) => intval(explode(' ', $m)[0]), $material_ids);
$ids_verificar_str = implode(',', $ids_verificar);
$result = $conn->query("SELECT id FROM materiais WHERE id IN ($ids_verificar_str)");
$materiais_encontrados = [];
while ($row = $result->fetch_assoc()) {
    $materiais_encontrados[] = $row['id'];
}
foreach ($ids_verificar as $id) {
    if (!in_array($id, $materiais_encontrados)) {
        echo "<div class='alert alert-danger container mt-4'>Erro: Material ID $id não encontrado no banco de dados.</div>";
        include __DIR__.'/includes/footer.php';
        exit;
    }
}

// Montar itens e verificar estoque
$total = 0;
$itens = [];
$itens_faltando = [];
foreach ($material_ids as $index => $material_id_raw) {
    $material_id = intval(explode(' ', $material_id_raw)[0]);
    $quantidade = floatval($quantidades[$index]);
    $preco_unitario = floatval($precos_unitarios[$index]);
    $subtotal = $preco_unitario * $quantidade;
    $total += $subtotal;

    $saldo_atual = obterSaldoEstoque($conn, $material_id);
    if ($quantidade > $saldo_atual) {
        $nome_material = $conn->query("SELECT nome FROM materiais WHERE id = $material_id")->fetch_assoc()['nome'];
        $itens_faltando[] = [
            'id' => $material_id,
            'nome' => $nome_material,
            'saldo' => $saldo_atual,
            'requisitado' => $quantidade
        ];
    }

    $itens[] = [
        'material_id' => $material_id,
        'quantidade' => $quantidade,
        'preco_unitario' => $preco_unitario,
        'subtotal' => $subtotal
    ];
}

// Interromper se estoque for insuficiente e não houver confirmação
if (!empty($itens_faltando) && !isset($_POST['forcar_venda'])) {
    echo "<div class='container py-4'><div class='alert alert-warning'><h4>Estoque insuficiente:</h4><ul>";
    foreach ($itens_faltando as $falta) {
        echo "<li><strong>{$falta['nome']}</strong> — Requisitado: {$falta['requisitado']}kg, Disponível: {$falta['saldo']}kg</li>";
    }
    echo "</ul><p>Deseja continuar? O estoque pode ficar negativo.</p><form method='POST'>";
    foreach ($_POST as $key => $value) {
        if (is_array($value)) {
            foreach ($value as $v) {
                echo "<input type='hidden' name='{$key}[]' value='".htmlspecialchars($v, ENT_QUOTES)."'>";
            }
        } else {
            echo "<input type='hidden' name='$key' value='".htmlspecialchars($value, ENT_QUOTES)."'>";
        }
    }
    echo "<input type='hidden' name='forcar_venda' value='1'>";
    echo "<button class='btn btn-danger me-2' onclick=\"window.location.href='venda.php'\">Cancelar Venda</button>";
    echo "<button type='submit' class='btn btn-success'>Continuar Mesmo Assim</button>";
    echo "</form></div></div>";
    include __DIR__.'/includes/footer.php';
    exit;
}

// Continuar com a venda
$valor_pago = $valor_dinheiro + $valor_pix + $valor_cartao;
$diferenca = $valor_pago - $total;

$stmt = $conn->prepare("INSERT INTO vendas 
    (cliente_id, lista_preco_id, total, valor_dinheiro, valor_pix, valor_cartao, valor_pago, data, empresa_id) 
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");

$stmt->bind_param(
    "iidddddsi", 
    $cliente_id, 
    $lista_preco_id, 
    $total, 
    $valor_dinheiro, 
    $valor_pix, 
    $valor_cartao, 
    $valor_pago, 
    $data_atual, 
    $empresa_id
);

// tenta executar
if ($stmt->execute()) {
    $venda_id = $stmt->insert_id; // pega ID da venda
    
} else {
    echo "<pre style='color:red'>";
    echo "Erro ao inserir venda: " . $stmt->error . "\n";
    echo "SQLSTATE: " . $stmt->sqlstate . "\n";
    var_dump([
        'cliente_id'     => $cliente_id,
        'lista_preco_id' => $lista_preco_id,
        'total'          => $total,
        'valor_dinheiro' => $valor_dinheiro,
        'valor_pix'      => $valor_pix,
        'valor_cartao'   => $valor_cartao,
        'valor_pago'     => $valor_pago,
        'data'           => $data_atual,
        'empresa_id'     => $empresa_id
    ]);
    echo "</pre>";
}

foreach ($itens as $item) {
    $stmt_item = $conn->prepare("INSERT INTO vendas_itens (venda_id, material_id, quantidade, preco_unitario, subtotal, empresa_id) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt_item->bind_param("iiddii", $venda_id, $item['material_id'], $item['quantidade'], $item['preco_unitario'], $item['subtotal'], $empresa_id);
    $stmt_item->execute();

    // Registrar movimentação de estoque
    $stmt_estoque = $conn->prepare("INSERT INTO estoque (material_id, tipo, quantidade, data_movimentacao, descricao, empresa_id) VALUES (?, 'saida', ?, ?, ?, ?)");
    $descricao_estoque = "Venda ID $venda_id";
    $stmt_estoque->bind_param("idssi", $item['material_id'], $item['quantidade'], $data_atual, $descricao_estoque, $empresa_id);
    $stmt_estoque->execute();
}

// Limpar venda suspensa
if ($cliente_id) {
    $conn->query("DELETE FROM vendas_suspensas WHERE usuario_id = $usuario_id AND cliente_id = $cliente_id AND empresa_id = $empresa_id");
} else {
    $conn->query("DELETE FROM vendas_suspensas WHERE usuario_id = $usuario_id AND cliente_id IS NULL AND empresa_id = $empresa_id");
}

// Movimentação no caixa
if ($valor_dinheiro > 0) {
    $stmt_caixa = $conn->prepare("INSERT INTO movimentacoes (caixa_id, tipo, valor, descricao, data_movimentacao, empresa_id) VALUES (?, 'entrada', ?, ?, ?, ?)");
    $descricao = "Venda ID $venda_id - pagamento em dinheiro";
    $stmt_caixa->bind_param("idssi", $caixa_id, $valor_dinheiro, $descricao, $data_atual, $empresa_id);
    $stmt_caixa->execute();
}

if ($diferenca > 0 && $valor_dinheiro > 0 && $gerar_troco) {
    $stmt_troco = $conn->prepare("INSERT INTO movimentacoes (caixa_id, tipo, valor, descricao, data_movimentacao, empresa_id) VALUES (?, 'saida', ?, ?, ?, ?)");
    $descricao_troco = "Troco da Venda ID $venda_id";
    $stmt_troco->bind_param("idssi", $caixa_id, $diferenca, $descricao_troco, $data_atual, $empresa_id);
    $stmt_troco->execute();
}

if ($cliente_id) {
    $ajuste_saldo = 0;
    if ($diferenca < 0) {
        $ajuste_saldo = $diferenca;
    } elseif ($diferenca > 0 && (!$gerar_troco || $valor_dinheiro <= 0)) {
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