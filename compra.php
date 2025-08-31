<?php
require_once 'verifica_login.php';
require_once 'conexx/config.php';
include __DIR__.'/includes/header.php';
include __DIR__.'/includes/navbar.php';

// Clientes
$clientes_arr = [];
$empresa_id = $_SESSION['usuario_empresa'];
$res = $conn->query("SELECT id, nome, lista_preco_id FROM clientes WHERE empresa_id = " . intval($empresa_id));
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


<head>
    <meta charset="UTF-8">
    <title>Compra - PDV</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
   
   

    <style>
    .section-card {
        background-color: #ffffff;
        border-radius: 12px;
        padding: 20px;
        box-shadow: 0 0 10px rgba(0, 0, 0, 0.05);
        margin-bottom: 30px;
    }

    .footer-icons {
        position: fixed;
        bottom: 0;
        left: 0;
        width: 100%;
        background: #f8f9fa;
        border-top: 1px solid #dee2e6;
        padding: 10px 0;
        display: flex;
        justify-content: space-around;
        z-index: 1050;
    }

    .footer-icons .icon {
        text-align: center;
        font-size: 1.2rem;
        color: #495057;
        cursor: pointer;
    }

    .footer-icons .icon i {
        display: block;
        font-size: 1.5rem;
    }
    </style>

    <script>
    
    </script>
</head>

<body>
    <div class="container py-4">
        <div class="row g-4">
            <!-- Coluna do Formulário -->
            <div class="col-md-7">
                <div class="section-card">
                    <h4>Cadastro da Compra</h4>
                    <form method="POST" action="salvar_compra.php" id="formCompra">

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

                        <button type="button" class="btn btn-success mt-3 w-100" id="btnAbrirModalPagamento">Finalizar Compra</button>
                    </form>
                </div>
            </div>

            <div class="col-md-5">
                <div class="section-card">
                    <h4>Resumo da Compra</h4>
                    <div id="resumo_itens"></div>
                    <h5>Total: R$ <span id="total_compra">0.00</span></h5>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Selecionar Cliente -->
    <div class="modal fade" id="modalSelecionarCliente" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Selecionar Cliente</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="text" class="form-control mb-3" id="busca-cliente"
                        placeholder="Digite o nome do cliente">
                    <ul class="list-group" id="resultado-clientes"></ul>
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
            return item.nome.toLowerCase().includes(term) || String(item.id).includes(term);
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
    let html = `
        <table style="width:100%; border-collapse: collapse; font-family: Arial, sans-serif;">
            <thead>
                <tr style="background-color: #f0f0f0;">
                    <th style="text-align: left; padding: 8px; border-bottom: 2px solid #ccc;">Material</th>
                    <th style="text-align: right; padding: 8px; border-bottom: 2px solid #ccc;">Qtd</th>
                    <th style="text-align: right; padding: 8px; border-bottom: 2px solid #ccc;">Preço Unit.</th>
                    <th style="text-align: right; padding: 8px; border-bottom: 2px solid #ccc;">Subtotal</th>
                    <th style="padding: 8px; border-bottom: 2px solid #ccc;">Ações</th>
                </tr>
            </thead>
            <tbody>
    `;

    document.querySelectorAll('input[name="material_id[]"]').forEach(function(input, index) {
        let materialId = parseInt(input.value.split(' ')[0]);
        let materialNome = materiais.find(m => m.id == materialId)?.nome || "ID " + materialId;
        let qtd = parseFloat(document.getElementsByName('quantidade[]')[index].value) || 0;
        let preco = parseFloat(document.getElementsByName('preco_unitario[]')[index].value) || 0;
        let subtotal = qtd * preco;
        total += subtotal;

        html += `
            <tr>
                <td style="padding: 8px; border-bottom: 1px solid #ddd;">${materialNome}</td>
                <td style="padding: 8px; text-align: right; border-bottom: 1px solid #ddd;">${qtd}</td>
                <td style="padding: 8px; text-align: right; border-bottom: 1px solid #ddd;">R$ ${preco.toFixed(2)}</td>
                <td style="padding: 8px; text-align: right; border-bottom: 1px solid #ddd;">R$ ${subtotal.toFixed(2)}</td>
                <td style="padding: 8px; text-align: center; border-bottom: 1px solid #ddd;">
                    <button type="button" onclick="editarItem(${index})" style="background:none; border:none; cursor:pointer;" title="Editar">✏️</button>
                    <button type="button" onclick="removerItem(${index})" style="background:none; border:none; cursor:pointer; margin-left:8px;" title="Remover">🗑️</button>
                </td>
            </tr>
        `;
    });

    html += `
            </tbody>
        </table>
    `;

    document.getElementById('resumo_itens').innerHTML = html;
    document.getElementById('total_compra').innerText = total.toFixed(2);
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

    let materialId = parseInt(material.split(' ')[0]);
    let materialObj = materiais.find(function(item) { return item.id == materialId; });
    if (!materialObj) {
        alert("Material não encontrado no banco de dados!");
        return;
    }
    let materialValue = materialObj.id + ' - ' + materialObj.nome;

    if (editIndex !== '') {
        document.getElementsByName('material_id[]')[editIndex].value = materialValue;
        document.getElementsByName('quantidade[]')[editIndex].value = quantidade;
        document.getElementsByName('preco_unitario[]')[editIndex].value = precoUnitario;
        document.getElementById('edit_index').value = '';
    } else {
        ['material_id', 'quantidade', 'preco_unitario'].forEach(function(field) {
            let input = document.createElement('input');
            input.type = 'hidden';
            input.name = field + '[]';
            input.value = (field === 'material_id') ? materialValue : (field === 'quantidade' ? quantidade : precoUnitario);
            document.getElementById('formCompra').appendChild(input);
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

document.addEventListener('DOMContentLoaded', function() {
    autocomplete('cliente', clientes);
    autocomplete('material_input', materiais);
    document.getElementById('adicionarItemBtn').addEventListener('click', adicionarOuEditarItem);

    // Enter no campo cliente seleciona e busca tabela de preço
    document.getElementById('cliente').addEventListener('keydown', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            let val = this.value.trim();
            let idCliente = parseInt(val.split(' ')[0]);
            let clienteSelecionado = clientes.find(c => c.id == idCliente);
            if (clienteSelecionado) {
                this.value = clienteSelecionado.id + ' - ' + clienteSelecionado.nome;
                // Preencher/preencher tabela de preço
                if (clienteSelecionado.lista_preco_id) {
                    window.listaPrecoAtual = clienteSelecionado.lista_preco_id;
                    let listaObj = listas_precos.find(l => l.id == clienteSelecionado.lista_preco_id);
                    if (listaObj) {
                        document.getElementById('lista_preco').value = listaObj.id + ' - ' + listaObj.nome;
                    } else {
                        document.getElementById('lista_preco').value = clienteSelecionado.lista_preco_id;
                    }
                } else {
                    window.listaPrecoAtual = null;
                    document.getElementById('lista_preco').value = '';
                }
                document.getElementById('material_input').focus();
            } else {
                alert('Cliente não encontrado!');
            }
        }
    });

    // Função para preencher preço automático
    function preencherPrecoAutomatico() {
        let listaId = parseInt(document.getElementById('lista_preco').value.split(' ')[0]);
        let materialId = parseInt(document.getElementById('material_input').value.split(' ')[0]);
        if (precos[listaId] && precos[listaId][materialId]) {
            document.getElementById('preco_input').value = precos[listaId][materialId];
        } else {
            document.getElementById('preco_input').value = '';
        }
    }

    document.getElementById('lista_preco').addEventListener('blur', preencherPrecoAutomatico);
    document.getElementById('material_input').addEventListener('blur', preencherPrecoAutomatico);
    document.getElementById('material_input').addEventListener('keydown', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            let val = this.value.trim();
            let materialId = parseInt(val.split(' ')[0]);
            let materialObj = materiais.find(function(item) { return item.id == materialId; });
            if (val && materialObj) {
                this.value = materialObj.id + ' - ' + materialObj.nome;
                document.getElementById('quantidade_input').focus();
                preencherPrecoAutomatico();
            } else {
                alert('Material não encontrado!');
            }
        }
    });

    document.getElementById('quantidade_input').addEventListener('keydown', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            if (this.value > 0) {
                document.getElementById('preco_input').focus();
            } else {
                alert('Quantidade obrigatória!');
            }
        }
    });
    document.getElementById('preco_input').addEventListener('keydown', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            if (this.value > 0) {
                adicionarOuEditarItem();
            } else {
                alert('Preço obrigatório!');
            }
        }
    });

    // Preencher preço automático ao focar no campo preço
    document.getElementById('preco_input').addEventListener('focus', function() {
        let listaId = window.listaPrecoAtual;
        let materialId = parseInt(document.getElementById('material_input').value.split(' ')[0]);
        function preencherPrecoFocus() {
            if (listaId && materialId && window.precos && window.precos[listaId] && window.precos[listaId][materialId]) {
                document.getElementById('preco_input').value = window.precos[listaId][materialId];
            } else {
                document.getElementById('preco_input').value = '';
            }
        }
        if (window.precosCarregando) {
            let tentativas = 0;
            let intervalo = setInterval(function() {
                tentativas++;
                if (!window.precosCarregando) {
                    preencherPrecoFocus();
                    clearInterval(intervalo);
                }
                if (tentativas > 10) clearInterval(intervalo);
            }, 100);
        } else {
            preencherPrecoFocus();
        }
    });
});
</script>

<!-- Modal Pagamento (compra) -->
<div class="modal fade" id="modalPagamentoCompra" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Formas de Pagamento</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <h6>Total da Compra: R$ <span id="modal_total_compra">0.00</span></h6>

                <div class="mb-2">
                    <label>Dinheiro:</label>
                    <input type="number" step="0.01" name="valor_dinheiro" id="valor_dinheiro_compra" class="form-control">
                </div>
                <div class="mb-2">
                    <label>Pix:</label>
                    <input type="number" step="0.01" name="valor_pix" id="valor_pix_compra" class="form-control">
                </div>
                <div class="mb-2">
                    <label>Cartão:</label>
                    <input type="number" step="0.01" name="valor_cartao" id="valor_cartao_compra" class="form-control">
                </div>

                <h6>Total Pago: R$ <span id="modal_total_pago_compra">0.00</span></h6>
                <div id="aviso_pagamento_compra" class="text-danger mt-2"></div>

                <div id="opcao_troco_compra" class="form-check mt-2" style="display:none;">
                    <input class="form-check-input" type="checkbox" name="gerar_troco" id="gerar_troco_compra" checked>
                    <label class="form-check-label">Gerar troco no caixa (se desmarcar, vai para saldo do cliente)</label>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" id="btnConfirmarPagamentoCompra">Confirmar Pagamento</button>
            </div>
        </div>
    </div>
</div>

<script>
function atualizarModalPagamentoCompra() {
        let totalCompra = parseFloat(document.getElementById('total_compra').innerText) || 0;
        let dinheiro = parseFloat(document.getElementById('valor_dinheiro_compra').value) || 0;
        let pix = parseFloat(document.getElementById('valor_pix_compra').value) || 0;
        let cartao = parseFloat(document.getElementById('valor_cartao_compra').value) || 0;
        let totalPago = dinheiro + pix + cartao;

        document.getElementById('modal_total_compra').innerText = totalCompra.toFixed(2);
        document.getElementById('modal_total_pago_compra').innerText = totalPago.toFixed(2);

        let aviso = '';
        let opcaoTroco = document.getElementById('opcao_troco_compra');

        if (totalPago < totalCompra) {
                aviso = "⚠️ Valor pago é menor que o total. Será gerado saldo devedor.";
                opcaoTroco.style.display = 'none';
        } else if (totalPago > totalCompra && dinheiro > 0) {
                aviso = "⚠️ Haverá troco no caixa.";
                opcaoTroco.style.display = 'block';
        } else if (totalPago > totalCompra) {
                aviso = "⚠️ Haverá saldo positivo no cliente.";
                opcaoTroco.style.display = 'none';
        } else {
                aviso = "✅ Pagamento exato.";
                opcaoTroco.style.display = 'none';
        }
        document.getElementById('aviso_pagamento_compra').innerText = aviso;
}

document.addEventListener('DOMContentLoaded', function() {
        document.getElementById('btnAbrirModalPagamento').addEventListener('click', function(e) {
                e.preventDefault();
                atualizarModalPagamentoCompra();
                new bootstrap.Modal(document.getElementById('modalPagamentoCompra')).show();
        });

        ['valor_dinheiro_compra', 'valor_pix_compra', 'valor_cartao_compra'].forEach(function(id) {
                let el = document.getElementById(id);
                if (el) el.addEventListener('input', atualizarModalPagamentoCompra);
        });

        document.getElementById('btnConfirmarPagamentoCompra').addEventListener('click', function() {
                // Copia os campos para o form e submete
                let form = document.getElementById('formCompra');
                ['valor_dinheiro_compra', 'valor_pix_compra', 'valor_cartao_compra'].forEach(function(id) {
                        let el = document.getElementById(id);
                        let name = id.replace('_compra','');
                        let existing = form.querySelector('input[name="' + name + '"]');
                        if (existing) existing.value = el.value || 0;
                        else {
                                let h = document.createElement('input');
                                h.type = 'hidden';
                                h.name = name;
                                h.value = el.value || 0;
                                form.appendChild(h);
                        }
                });
                // adicionar gerar_troco
                let gerar = document.getElementById('gerar_troco_compra').checked ? 1 : 0;
                let existingTroco = form.querySelector('input[name="gerar_troco"]');
                if (existingTroco) existingTroco.value = gerar;
                else {
                        let h2 = document.createElement('input');
                        h2.type = 'hidden';
                        h2.name = 'gerar_troco';
                        h2.value = gerar;
                        form.appendChild(h2);
                }

                form.submit();
        });
});
</script>


    <?php include __DIR__.'/includes/footer.php'; ?>