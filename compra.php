<?php
require_once 'conexx/config.php';

// Verificar caixa aberto
$result = $conn->query("SELECT id FROM caixas WHERE status='aberto' LIMIT 1");
$caixa_aberto = $result->fetch_assoc();

?>
<script>
var caixaAberto = <?= $caixa_aberto ? 'true' : 'false' ?>;
</script>

<?php

// Clientes
$clientes = $conn->query("SELECT id, nome, lista_preco_id FROM clientes");

// Listas de Preços
$listas_precos = $conn->query("SELECT id, nome FROM listas_precos");

// Materiais
$materiais = $conn->query("SELECT id, nome FROM materiais");

// Preços por Lista
$precos = [];
$listas_precos->data_seek(0);
while ($l = $listas_precos->fetch_assoc()) {
    $lista_id = $l['id'];
    $precos[$lista_id] = [];
    $res = $conn->query("SELECT material_id, preco FROM precos_materiais WHERE lista_id = $lista_id");
    while ($p = $res->fetch_assoc()) {
        $precos[$lista_id][$p['material_id']] = $p['preco'];
    }
}

include __DIR__.'/includes/header.php';
include __DIR__.'/includes/navbar.php';
?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <title>Nova Venda - PDV</title>
    <style>
    .section-card {
        background-color: #ffffff;
        border-radius: 12px;
        padding: 20px;
        box-shadow: 0 0 10px rgba(0, 0, 0, 0.05);
        margin-bottom: 30px;
    }

    h2,
    h3 {
        margin-bottom: 20px;
        color: #343a40;
    }

    .btn-primary,
    .btn-success,
    .btn-danger,
    .btn-outline-secondary {
        border-radius: 8px;
    }

    .list-group-item {
        border-radius: 8px;
    }
    </style>
    <script>
    var precos = <?php echo json_encode($precos); ?>;

    function carregarListaPreco() {
        var clienteId = document.getElementById('cliente').value;
        var listaPreco = document.getElementById('lista_preco');

        <?php
        $clientes->data_seek(0);
        while ($c = $clientes->fetch_assoc()) {
            echo "if(clienteId == '{$c['id']}') { listaPreco.value = '{$c['lista_preco_id']}'; }\n";
        }
        ?>
        calcularTotal();
    }

    function adicionarItem() {
        var container = document.getElementById('itens');
        var item = container.children[0].cloneNode(true);
        item.querySelectorAll('input').forEach(input => input.value = '');
        container.appendChild(item);
        calcularTotal();
    }

    function removerItem(btn) {
        var item = btn.parentElement.parentElement;
        if (document.getElementById('itens').children.length > 1) {
            item.remove();
            calcularTotal();
        }
    }

    function calcularTotal() {
        var lista_id = document.getElementById('lista_preco').value;
        var total = 0;

        document.querySelectorAll('#itens > div').forEach(function(itemDiv) {
            var material_id = itemDiv.querySelector('select').value;
            var quantidade = parseFloat(itemDiv.querySelector('input').value) || 0;
            var preco = 0;

            if (precos[lista_id] && precos[lista_id][material_id]) {
                preco = parseFloat(precos[lista_id][material_id]);
            }

            total += quantidade * preco;
        });

        document.getElementById('total_venda').innerText = total.toFixed(2);
    }

    function toggleCamposPagamento() {
        document.getElementById('campo_dinheiro').style.display = document.getElementById('dinheiro').checked ?
            'block' : 'none';
        document.getElementById('campo_pix').style.display = document.getElementById('pix').checked ? 'block' : 'none';
        document.getElementById('campo_cartao').style.display = document.getElementById('cartao').checked ? 'block' :
            'none';
    }

    document.addEventListener('change', function(e) {
        if (e.target.matches(
                '#lista_preco, select[name="material_id[]"], input[name="quantidade[]"], input[type="checkbox"]'
            )) {
            calcularTotal();
            toggleCamposPagamento();
        }
    });

    document.addEventListener('input', function(e) {
        if (e.target.matches('input[name="quantidade[]"]')) {
            calcularTotal();
        }
    });

    window.onload = function() {
        toggleCamposPagamento();
    };
    </script>
</head>

<body>
    <!-- MODAL ABERTURA CAIXA -->
    <div class="modal fade" id="modalCaixaFechado" tabindex="-1" aria-labelledby="modalCaixaFechadoLabel"
        aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Caixa Fechado</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
                </div>
                <div class="modal-body">
                    O caixa está fechado. Deseja abrir agora?
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" id="btnCancelar">Cancelar</button>
                    <button type="button" class="btn btn-primary" id="btnAbrirCaixa">Abrir Caixa</button>
                </div>
            </div>
        </div>
    </div>
    <!-- fim Modal para abrir caixa -->



    <div class="section-card">
        <h2><i class="bi bi-cart-plus"></i> Nova Venda</h2>

        <form method="POST" action="salvar_venda.php">

            <!-- Cliente -->
            <div class="mb-3">
                <label><strong>Cliente (opcional):</strong></label>
                <select name="cliente_id" id="cliente" class="form-select" onchange="carregarListaPreco()">
                    <option value="">-- Sem cliente --</option>
                    <?php
                    $clientes->data_seek(0);
                    while ($c = $clientes->fetch_assoc()) {
                        echo "<option value='{$c['id']}'>{$c['nome']}</option>";
                    }
                    ?>
                </select>
            </div>

            <!-- Lista de Preços -->
            <div class="mb-3">
                <label><strong>Lista de Preços:</strong></label>
                <select name="lista_preco_id" id="lista_preco" class="form-select" onchange="calcularTotal()">
                    <?php
                    $listas_precos->data_seek(0);
                    while ($l = $listas_precos->fetch_assoc()) {
                        echo "<option value='{$l['id']}'>{$l['nome']}</option>";
                    }
                    ?>
                </select>
            </div>

            <!-- Itens da Venda -->
            <h5>Itens da Venda:</h5>
            <div id="itens">
                <div class="row g-2 mb-2">
                    <div class="col-md-5">
                        <select name="material_id[]" class="form-select" required>
                            <?php
                            $materiais->data_seek(0);
                            while ($m = $materiais->fetch_assoc()) {
                                echo "<option value='{$m['id']}'>{$m['nome']}</option>";
                            }
                            ?>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <input type="number" step="0.01" name="quantidade[]" class="form-control"
                            placeholder="Quantidade" required>
                    </div>
                    <div class="col-md-3">
                        <button type="button" class="btn btn-sm btn-outline-danger" onclick="removerItem(this)">
                            <i class="bi bi-trash"></i> Remover
                        </button>
                    </div>
                </div>
            </div>
            <button type="button" onclick="adicionarItem()" class="btn btn-outline-secondary mb-3">
                <i class="bi bi-plus-circle"></i> Adicionar Item
            </button>

            <!-- Formas de Pagamento -->
            <h5>Formas de Pagamento:</h5>
            <div class="form-check">
                <input class="form-check-input" type="checkbox" name="formas_pagamento[]" value="dinheiro"
                    id="dinheiro">
                <label class="form-check-label" for="dinheiro">Dinheiro</label>
            </div>
            <div class="form-check">
                <input class="form-check-input" type="checkbox" name="formas_pagamento[]" value="pix" id="pix">
                <label class="form-check-label" for="pix">Pix</label>
            </div>
            <div class="form-check">
                <input class="form-check-input" type="checkbox" name="formas_pagamento[]" value="cartao" id="cartao">
                <label class="form-check-label" for="cartao">Cartão</label>
            </div>

            <div class="mt-3" id="campo_dinheiro" style="display:none;">
                <label><strong>Valor em Dinheiro:</strong></label>
                <input type="number" step="0.01" name="valor_dinheiro" class="form-control" placeholder="Ex: 100.00">
            </div>

            <div class="mt-3" id="campo_pix" style="display:none;">
                <label><strong>Valor em Pix:</strong></label>
                <input type="number" step="0.01" name="valor_pix" class="form-control" placeholder="Ex: 100.00">
            </div>

            <div class="mt-3" id="campo_cartao" style="display:none;">
                <label><strong>Valor em Cartão:</strong></label>
                <input type="number" step="0.01" name="valor_cartao" class="form-control" placeholder="Ex: 100.00">
            </div>

            <!-- Total -->
            <div class="mt-4">
                <h4>Total da Venda: R$ <span id="total_venda">0.00</span></h4>
            </div>

            <button type="submit" class="btn btn-success mt-3">
                <i class="bi bi-check-circle"></i> Finalizar Venda
            </button>
        </form>
    </div>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        var caixaAberto = <?php echo $caixa_aberto ? 'true' : 'false'; ?>;

        if (!caixaAberto) {
            var modalElement = document.getElementById('modalCaixaFechado');
            var modalCaixa = new bootstrap.Modal(modalElement, {
                backdrop: 'static', // impede clique fora
                keyboard: false // impede fechar com ESC
            });

            modalCaixa.show();

            // Botão abrir caixa
            document.getElementById('btnAbrirCaixa').addEventListener('click', function() {
                window.location.href = 'caixa.php';
            });

            // Botão cancelar
            document.getElementById('btnCancelar').addEventListener('click', function() {
                window.location.href = 'index.php';
            });

            // Qualquer fechamento (inclusive X ou código)
            modalElement.addEventListener('hidden.bs.modal', function() {
                window.location.href = 'index.php';
            });
        }
    });
    </script>



    </div>

    <?php include __DIR__.'/includes/footer.php'; ?>
</body>



</html>