<?php
require_once 'conexx/config.php';
include __DIR__.'/includes/header.php';
include __DIR__.'/includes/navbar.php';

// Clientes
$clientes_arr = [];
$res = $conn->query("SELECT id, nome, lista_preco_id FROM clientes");
while ($c = $res->fetch_assoc()) {
    $clientes_arr[] = ["id" => $c['id'], "nome" => $c['nome'], "lista_preco_id" => $c['lista_preco_id']];
}

// Listas de Preço
$listas_precos_arr = [];
$res = $conn->query("SELECT id, nome FROM listas_precos");
while ($l = $res->fetch_assoc()) {
    $listas_precos_arr[] = ["id" => $l['id'], "nome" => $l['nome']];
}

// Materiais
$materiais_arr = [];
$res = $conn->query("SELECT id, nome FROM materiais");
while ($m = $res->fetch_assoc()) {
    $materiais_arr[] = ["id" => $m['id'], "nome" => $m['nome']];
}

// Preços por lista
$precos_materiais = [];
$res = $conn->query("SELECT lista_id, material_id, preco FROM precos_materiais");
while ($p = $res->fetch_assoc()) {
    $precos_materiais[$p['lista_id']][$p['material_id']] = floatval($p['preco']);
}
?>

<style>
.section-card {
    background-color: #fff;
    border-radius: 10px;
    padding: 20px;
    box-shadow: 0 0 10px rgba(0,0,0,0.05);
    margin-bottom: 20px;
}
.item-actions button {
    margin-left: 5px;
    padding: 2px 6px;
    font-size: 0.8rem;
}
</style>

<div class="container-fluid py-4">
    <div class="row g-4">
        <div class="col-md-7">
            <div class="section-card">
                <h4>Cadastro da Venda</h4>
                <form method="POST" action="salvar_venda.php" id="formVenda">

                    <label><strong>Cliente (ID ou Nome):</strong></label>
                    <input type="text" name="cliente_id" id="cliente" class="form-control mb-2">

                    <label><strong>Lista de Preços:</strong></label>
                    <input type="text" name="lista_preco_id" id="lista_preco" class="form-control mb-2">

                    <h5>Adicionar Item:</h5>
                    <input type="hidden" id="edit_index" value="">
                    <input type="text" id="material_input" class="form-control mb-2" placeholder="Material (ID ou Nome)">
                    <input type="number" id="quantidade_input" class="form-control mb-2" placeholder="Quantidade" step="0.01" min="0">
                    <input type="number" id="preco_input" class="form-control mb-2" placeholder="Preço Unitário" step="0.01" min="0">

                    <button type="button" id="adicionarItemBtn" class="btn btn-outline-primary w-100">Adicionar/Editar Item</button>

                    <button type="button" class="btn btn-success mt-3 w-100" id="btnAbrirModalPagamento">Finalizar Venda</button>

                    <!-- Modal Pagamento -->
                    <div class="modal fade" id="modalPagamento" tabindex="-1">
                      <div class="modal-dialog">
                        <div class="modal-content">
                          <div class="modal-header">
                            <h5 class="modal-title">Formas de Pagamento</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                          </div>
                          <div class="modal-body">
                            <h6>Total da Venda: R$ <span id="modal_total_venda">0.00</span></h6>

                            <div class="mb-2">
                              <label>Dinheiro:</label>
                              <input type="number" step="0.01" name="valor_dinheiro" id="valor_dinheiro" class="form-control">
                            </div>
                            <div class="mb-2">
                              <label>Pix:</label>
                              <input type="number" step="0.01" name="valor_pix" id="valor_pix" class="form-control">
                            </div>
                            <div class="mb-2">
                              <label>Cartão:</label>
                              <input type="number" step="0.01" name="valor_cartao" id="valor_cartao" class="form-control">
                            </div>

                            <h6>Total Pago: R$ <span id="modal_total_pago">0.00</span></h6>
                            <div id="aviso_pagamento" class="text-danger mt-2"></div>

                            <div id="opcao_troco" class="form-check mt-2" style="display:none;">
                              <input class="form-check-input" type="checkbox" name="gerar_troco" id="gerar_troco" checked>
                              <label class="form-check-label">Gerar troco no caixa (se desmarcar, vai para saldo do cliente)</label>
                            </div>
                          </div>
                          <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                            <button type="submit" class="btn btn-primary" id="btnConfirmarPagamento">Confirmar Pagamento</button>
                          </div>
                        </div>
                      </div>
                    </div>

                </form>
            </div>
        </div>

        <div class="col-md-5">
            <div class="section-card">
                <h4>Resumo da Venda</h4>
                <div id="resumo_itens"></div>
                <h5>Total: R$ <span id="total_venda">0.00</span></h5>
            </div>
        </div>
    </div>
</div>

<script>
var clientes = <?php echo json_encode($clientes_arr); ?>;
var listas_precos = <?php echo json_encode($listas_precos_arr); ?>;
var materiais = <?php echo json_encode($materiais_arr); ?>;
var precos = <?php echo json_encode($precos_materiais); ?>;

function autocomplete(inputId, dataArray) {
    const input = document.getElementById(inputId);
    input.addEventListener('input', function() {
        let term = this.value.toLowerCase();
        let options = dataArray.filter(function(item) {
            return item.nome.toLowerCase().includes(term) || item.id == term;
        }).map(function(item) {
            return item.id + " - " + item.nome;
        });
        input.setAttribute('list', inputId + '_list');
        let datalist = document.getElementById(inputId + '_list') || document.createElement('datalist');
        datalist.id = inputId + '_list';
        datalist.innerHTML = options.map(opt => `<option value="${opt}">`).join('');
        document.body.appendChild(datalist);
    });
}

function atualizarResumo() {
    let total = 0;
    let html = '';

    document.querySelectorAll('input[name="material_id[]"]').forEach(function(input, index) {
        let material = input.value;
        let qtd = parseFloat(document.getElementsByName('quantidade[]')[index].value) || 0;
        let preco = parseFloat(document.getElementsByName('preco_unitario[]')[index].value) || 0;
        let subtotal = qtd * preco;
        total += subtotal;

        html += `<div>${material} - Qtd: ${qtd} | R$ ${preco.toFixed(2)} = R$ ${subtotal.toFixed(2)}
            <span class="item-actions">
                <button type="button" onclick="editarItem(${index})">✏️</button>
                <button type="button" onclick="removerItem(${index})">🗑️</button>
            </span></div>`;
    });

    document.getElementById('resumo_itens').innerHTML = html;
    document.getElementById('total_venda').innerText = total.toFixed(2);
}

function preencherPrecoAutomatico() {
    let listaId = parseInt(document.getElementById('lista_preco').value.split(' ')[0]);
    let materialId = parseInt(document.getElementById('material_input').value.split(' ')[0]);
    if (precos[listaId] && precos[listaId][materialId]) {
        document.getElementById('preco_input').value = precos[listaId][materialId];
    }
}

function adicionarOuEditarItem() {
    let material = document.getElementById('material_input').value.trim();
    let quantidade = document.getElementById('quantidade_input').value;
    let precoUnitario = document.getElementById('preco_input').value;
    let editIndex = document.getElementById('edit_index').value;

    if (!material || quantidade <= 0 || precoUnitario <= 0) {
        alert("Preencha material, quantidade e preço corretamente");
        return;
    }

    if (editIndex !== '') {
        document.getElementsByName('material_id[]')[editIndex].value = material;
        document.getElementsByName('quantidade[]')[editIndex].value = quantidade;
        document.getElementsByName('preco_unitario[]')[editIndex].value = precoUnitario;
        document.getElementById('edit_index').value = '';
    } else {
        ['material_id', 'quantidade', 'preco_unitario'].forEach(function(field) {
            let input = document.createElement('input');
            input.type = 'hidden';
            input.name = field + '[]';
            input.value = (field === 'material_id') ? material : (field === 'quantidade' ? quantidade : precoUnitario);
            document.getElementById('formVenda').appendChild(input);
        });
    }

    document.getElementById('material_input').value = '';
    document.getElementById('quantidade_input').value = '';
    document.getElementById('preco_input').value = '';
    document.getElementById('material_input').focus();
    atualizarResumo();
}

function editarItem(index) {
    document.getElementById('material_input').value = document.getElementsByName('material_id[]')[index].value;
    document.getElementById('quantidade_input').value = document.getElementsByName('quantidade[]')[index].value;
    document.getElementById('preco_input').value = document.getElementsByName('preco_unitario[]')[index].value;
    document.getElementById('edit_index').value = index;
    document.getElementById('material_input').focus();
}

function removerItem(index) {
    document.getElementsByName('material_id[]')[index].remove();
    document.getElementsByName('quantidade[]')[index].remove();
    document.getElementsByName('preco_unitario[]')[index].remove();
    atualizarResumo();
}

function atualizarModalPagamento() {
    let totalVenda = parseFloat(document.getElementById('total_venda').innerText) || 0;
    let dinheiro = parseFloat(document.getElementById('valor_dinheiro').value) || 0;
    let pix = parseFloat(document.getElementById('valor_pix').value) || 0;
    let cartao = parseFloat(document.getElementById('valor_cartao').value) || 0;

    let totalPago = dinheiro + pix + cartao;
    document.getElementById('modal_total_venda').innerText = totalVenda.toFixed(2);
    document.getElementById('modal_total_pago').innerText = totalPago.toFixed(2);

    let aviso = '';
    let opcaoTroco = document.getElementById('opcao_troco');

    if (totalPago < totalVenda) {
        aviso = "⚠️ Valor pago é menor que o total. Será gerado saldo devedor.";
        opcaoTroco.style.display = 'none';
    } else if (totalPago > totalVenda && dinheiro > 0) {
        aviso = "⚠️ Haverá troco no caixa.";
        opcaoTroco.style.display = 'block';
    } else {
        aviso = "✅ Pagamento exato.";
        opcaoTroco.style.display = 'none';
    }
    document.getElementById('aviso_pagamento').innerText = aviso;
}

document.addEventListener('DOMContentLoaded', function() {
    autocomplete('cliente', clientes);
    autocomplete('lista_preco', listas_precos);
    autocomplete('material_input', materiais);

    document.getElementById('material_input').addEventListener('blur', preencherPrecoAutomatico);
    document.getElementById('adicionarItemBtn').addEventListener('click', adicionarOuEditarItem);
    document.getElementById('btnAbrirModalPagamento').addEventListener('click', function() {
        atualizarResumo();
        atualizarModalPagamento();
        new bootstrap.Modal(document.getElementById('modalPagamento')).show();
    });

    ['valor_dinheiro', 'valor_pix', 'valor_cartao'].forEach(function(id) {
        document.getElementById(id).addEventListener('input', atualizarModalPagamento);
    });

    document.getElementById('formVenda').addEventListener('keydown', function(event) {
        const activeEl = document.activeElement;
        if (event.key === 'Enter' && activeEl.tagName.toLowerCase() !== 'button') {
            event.preventDefault();
            let inputs = Array.from(this.querySelectorAll('input, button, textarea, select')).filter(el => !el.disabled && el.offsetParent !== null);
            let index = inputs.indexOf(activeEl);
            if (index > -1 && index < inputs.length - 1) {
                inputs[index + 1].focus();
            }
        }
    });
});
</script>

<?php include __DIR__.'/includes/footer.php'; ?>
