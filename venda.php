<?php
require_once 'conexx/config.php';

// Verificar se há caixa aberto
$caixa_aberto = $conn->query("SELECT id FROM caixas WHERE status = 'aberto' ORDER BY id DESC LIMIT 1")->fetch_assoc();
if (!$caixa_aberto) {
    echo "<div style='padding: 20px; color: red; font-weight: bold;'>Nenhum caixa aberto. Abra um caixa para realizar vendas.</div>";
    exit;
}

$total_venda = 0;
$total_pago = 0;
$total_diferenca = 0;
$venda_finalizada = false;

// Buscar materiais e preços de antemão (para o JS)
$precos_materiais = [];
$result_materiais = $conn->query("SELECT mp.material_id, m.nome, mp.preco FROM precos_materiais mp 
JOIN materiais m ON mp.material_id = m.id 
WHERE mp.precos_materiais_id = (SELECT id FROM listas_precos WHERE nome='padrão' LIMIT 1)");
while ($row = $result_materiais->fetch_assoc()) {
    $precos_materiais[$row['material_id']] = [
        'nome' => $row['nome'],
        'preco' => floatval($row['preco'])
    ];
}

// Processar a venda
if (isset($_POST['finalizar_venda'])) {
    $cliente_id = isset($_POST['cliente_id']) ? intval($_POST['cliente_id']) : null;
    $materiais = $_POST['materiais'];
    $quantidades = $_POST['quantidades'];
    $pagamentos = $_POST['pagamentos'];

    // Calcular total da venda
    foreach ($materiais as $index => $material_id) {
        $qtd = floatval($quantidades[$index]);

        // Buscar preço do material conforme lista de preço
        if ($cliente_id) {
            $cliente = $conn->query("SELECT precos_materiais_id FROM clientes WHERE id = $cliente_id")->fetch_assoc();
            $precos_materiais_id = $cliente['precos_materiais_id'] ?: "(SELECT id FROM listas_precos WHERE nome='padrão' LIMIT 1)";
        } else {
            $precos_materiais_id = "(SELECT id FROM listas_precos WHERE nome='padrão' LIMIT 1)";
        }

        $preco_result = $conn->query("SELECT preco FROM precos_materiais WHERE material_id = $material_id AND precos_materiais_id = $precos_materiais_id LIMIT 1");
        $preco_row = $preco_result->fetch_assoc();
        $preco = $preco_row ? floatval($preco_row['preco']) : 0;

        $total_venda += $preco * $qtd;
    }

    // Total pago
    foreach ($pagamentos as $forma => $valor) {
        $valor = floatval($valor);
        if ($valor > 0) {
            $total_pago += $valor;

            // Registrar em caixa se for dinheiro
            if (strtolower($forma) == 'dinheiro') {
                $stmt = $conn->prepare("INSERT INTO caixa_movimentos (caixa_id, tipo, valor, descricao) VALUES (?, 'entrada', ?, 'Venda - pagamento em dinheiro')");
                $stmt->bind_param("id", $caixa_aberto['id'], $valor);
                $stmt->execute();
            }
        }
    }

    $total_diferenca = $total_pago - $total_venda;

    // Atualizar saldo cliente
    if ($cliente_id && $total_diferenca != 0) {
        $conn->query("UPDATE clientes SET saldo = saldo + $total_diferenca WHERE id = $cliente_id");
    }

    $venda_finalizada = true;
}

// Buscar clientes
$clientes = $conn->query("SELECT id, nome FROM clientes ORDER BY nome");

// Buscar materiais (para o formulário)
$materiais = $conn->query("SELECT id, nome FROM materiais ORDER BY nome");

// Formas de pagamento
$formas_pagamento = ['dinheiro', 'pix', 'cartao', 'outros'];
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Vendas - PDV</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #f8f9fa; }
        .card { border-radius: 12px; box-shadow: 0 4px 8px rgba(0,0,0,0.05); }
        .form-control, .form-select { border-radius: 8px; }
    </style>
</head>
<body>
<div class="container py-4">
    <h2><i class="bi bi-cart-plus"></i> Nova Venda</h2>

    <?php if ($venda_finalizada): ?>
        <div class="alert alert-success">
            <strong>Venda realizada com sucesso!</strong><br>
            <strong>Total da Venda:</strong> R$ <?= number_format($total_venda, 2, ',', '.') ?><br>
            <strong>Total Pago:</strong> R$ <?= number_format($total_pago, 2, ',', '.') ?><br>
            <strongDiferença:</strong> R$ <?= number_format($total_diferenca, 2, ',', '.') ?>
        </div>
    <?php endif; ?>

    <form method="post">
        <!-- Cliente -->
        <div class="mb-3">
            <label>Cliente (Opcional):</label>
            <select name="cliente_id" class="form-select">
                <option value="">Venda sem cliente</option>
                <?php while ($cli = $clientes->fetch_assoc()) {
                    echo "<option value='{$cli['id']}'>{$cli['nome']}</option>";
                } ?>
            </select>
        </div>

        <!-- Itens -->
        <div class="card p-3 mb-3">
            <h5>Itens da Venda:</h5>
            <div id="itens-venda">
                <div class="row mb-2 item-venda">
                    <div class="col-md-6">
                        <select name="materiais[]" class="form-select" onchange="calcularTotal()" required>
                            <option value="">Selecione o Material</option>
                            <?php
                            $materiais2 = $conn->query("SELECT id, nome FROM materiais ORDER BY nome");
                            while ($m = $materiais2->fetch_assoc()) {
                                echo "<option value='{$m['id']}'>{$m['nome']}</option>";
                            }
                            ?>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <input type="number" name="quantidades[]" step="0.01" placeholder="Quantidade" class="form-control" oninput="calcularTotal()" required>
                    </div>
                    <div class="col-md-2">
                        <button type="button" class="btn btn-danger btn-sm" onclick="this.parentElement.parentElement.remove(); calcularTotal();">Remover</button>
                    </div>
                </div>
            </div>
            <button type="button" class="btn btn-secondary btn-sm" onclick="adicionarItem()">Adicionar Item</button>
        </div>

        <!-- Total Atual -->
        <div class="alert alert-info">
            <strong>Total Parcial da Venda:</strong> R$ <span id="total-venda">0,00</span>
        </div>

        <!-- Formas de Pagamento -->
        <div class="card p-3 mb-3">
            <h5>Pagamento:</h5>
            <?php foreach ($formas_pagamento as $forma) { ?>
                <div class="mb-2">
                    <label><?= ucfirst($forma) ?>:</label>
                    <input type="number" step="0.01" name="pagamentos[<?= $forma ?>]" class="form-control" placeholder="R$ 0,00">
                </div>
            <?php } ?>
        </div>

        <button type="submit" name="finalizar_venda" class="btn btn-success"><i class="bi bi-check-circle"></i> Finalizar Venda</button>
    </form>
</div>

<script>
// Preços vindos do PHP (preço padrão)
const precosMateriais = <?= json_encode(array_map(function($m){ return $m['preco']; }, $precos_materiais)) ?>;

function calcularTotal() {
    let total = 0;
    document.querySelectorAll('#itens-venda .item-venda').forEach(function(item) {
        const material = item.querySelector('select').value;
        const qtd = parseFloat(item.querySelector('input').value) || 0;
        const preco = precosMateriais[material] || 0;
        total += preco * qtd;
    });
    document.getElementById('total-venda').innerText = total.toFixed(2).replace('.', ',');
}

function adicionarItem() {
    const container = document.getElementById('itens-venda');
    const item = container.firstElementChild.cloneNode(true);
    item.querySelectorAll('input').forEach(e => e.value = '');
    item.querySelector('select').value = '';
    container.appendChild(item);
}
</script>

<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
</body>
</html>
