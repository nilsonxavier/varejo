<?php
require_once 'verifica_login.php';
// ... resto da página protegida ...
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





// Buscar vendas suspensas para exibir na lateral
$vendas_suspensas_arr = [];
$sqlSuspensas = "SELECT vs.id, vs.cliente_id, vs.venda_json, u.nome AS usuario, c.nome AS cliente_nome
                 FROM vendas_suspensas vs
                 LEFT JOIN usuarios u ON vs.usuario_id = u.id
                 LEFT JOIN clientes c ON vs.cliente_id = c.id
                 ORDER BY vs.id DESC";
$resSuspensas = $conn->query($sqlSuspensas);
while ($v = $resSuspensas->fetch_assoc()) {
    $dadosVenda = json_decode($v['venda_json'], true);

    $total_venda = 0;
    if (isset($dadosVenda['itens'])) {
        foreach ($dadosVenda['itens'] as $item) {
            $total_venda += $item['quantidade'] * $item['preco_unitario'];
        }
    }

    $vendas_suspensas_arr[] = [
        'id' => $v['id'],
        'usuario' => $v['usuario'],
        'cliente' => $v['cliente_nome'] ?? 'Cliente nulo',
        'cliente_id' => $v['cliente_id'],
        'total' => $total_venda
    ];
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




    <hr>

        <h5>Vendas Suspensas</h5>

        <input type="text" id="filtroSuspensos" placeholder="Filtrar por cliente ou usuário" class="form-control mb-2">

        <div id="listaSuspensos">
            <?php foreach ($vendas_suspensas_arr as $venda): ?>
                <div class="mb-2 border-bottom pb-2 item-suspenso">
                    <strong><span class="usuario-suspenso"><?php echo htmlspecialchars($venda['usuario']); ?></span></strong><br>
                    Cliente: <span class="cliente-suspenso"><?php echo htmlspecialchars($venda['cliente']); ?></span><br>
                    Total: R$ <?php echo number_format($venda['total'], 2, ',', '.'); ?><br>

                    <button type="button" class="btn btn-sm btn-outline-primary mt-1" onclick="abrirVendaSuspensaPorId(<?php echo $venda['id']; ?>)">Abrir</button>

                    <button type="button" class="btn btn-sm btn-outline-danger mt-1" onclick="excluirVendaSuspensa(<?php echo $venda['id']; ?>)">Excluir</button>
                </div>
            <?php endforeach; ?>
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

function carregarVendaSuspensa(clienteId) {
    fetch('recuperar_venda_temporaria.php?cliente_id=' + clienteId)
    .then(r => r.json())
    .then(data => {
        // Limpa itens atuais sempre que troca cliente
        document.querySelectorAll('input[name="material_id[]"], input[name="quantidade[]"], input[name="preco_unitario[]"]').forEach(e => e.remove());
        atualizarResumo();

        if (data.status === 'ok') {
            let venda = JSON.parse(data.dados.venda_json);

            if (venda.lista_preco_id) {
                const lista = listas_precos.find(l => l.id == venda.lista_preco_id);
                if (lista) document.getElementById('lista_preco').value = `${lista.id} - ${lista.nome}`;
            }

            if (venda.itens && Array.isArray(venda.itens)) {
                venda.itens.forEach(item => {
                    document.getElementById('material_input').value = item.material_id;
                    document.getElementById('quantidade_input').value = item.quantidade;
                    document.getElementById('preco_input').value = item.preco_unitario;
                    adicionarOuEditarItem();
                });
            }
        }
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

    let materialId = parseInt(material.split(' ')[0]);
    let materialExiste = materiais.some(function(item) {
        return item.id == materialId;
    });
    if (!materialExiste) {
        alert("Material não encontrado no banco de dados!");
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
    salvarVendaTemporaria();
    
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
    } else if(totalPago > totalVenda) {
        aviso = "⚠️ Haverá saldo positivo no cliente.";
        opcaoTroco.style.display = 'none';
    
    } else {
        aviso = "✅ Pagamento exato.";
        opcaoTroco.style.display = 'none';
    }
    document.getElementById('aviso_pagamento').innerText = aviso;
}

document.addEventListener('DOMContentLoaded', function() {

    fetch('recuperar_venda_temporaria.php')
        .then(r => r.json())
        .then(data => {
            if (data.status === 'ok') {
                let venda = JSON.parse(data.dados.venda_json);
                if (venda.cliente_id) document.getElementById('cliente').value = venda.cliente_id;
                if (venda.lista_preco_id) document.getElementById('lista_preco').value = venda.lista_preco_id;
                venda.itens.forEach(item => {
                    document.getElementById('material_input').value = item.material_id;
                    document.getElementById('quantidade_input').value = item.quantidade;
                    document.getElementById('preco_input').value = item.preco_unitario;
                    adicionarOuEditarItem();
                });
            }
        });

    autocomplete('cliente', clientes);
    autocomplete('lista_preco', listas_precos);
    autocomplete('material_input', materiais);

    document.getElementById('cliente').addEventListener('blur', function() {
        const valor = this.value.trim();
        const idCliente = parseInt(valor.split(' ')[0]);
        const clienteSelecionado = clientes.find(c => c.id == idCliente);
        if (clienteSelecionado && clienteSelecionado.lista_preco_id) {
            const lista = listas_precos.find(l => l.id == clienteSelecionado.lista_preco_id);
            if (lista) {
                document.getElementById('lista_preco').value = `${lista.id} - ${lista.nome}`;
                preencherPrecoAutomatico();
            }
        }
    });

    document.getElementById('lista_preco').addEventListener('blur', preencherPrecoAutomatico);
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




document.getElementById('cliente').addEventListener('blur', function() {
    const clienteValor = this.value.trim();
    const clienteId = parseInt(clienteValor.split(' ')[0]);
    if (clienteId) {
        carregarVendaSuspensa(clienteId);
    }
});




function salvarVendaTemporaria() {
    const formData = new FormData(document.getElementById('formVenda'));
    fetch('salvar_venda_temporaria.php', {
        method: 'POST',
        body: formData
    })
    .then(r => r.json())
    .then(data => {
        console.log(data.msg);
    })
    .catch(err => console.error('Erro ao salvar venda temporária:', err));
}










document.getElementById('cliente').addEventListener('input', function () {
    const clienteValor = this.value.trim();
    const clienteId = parseInt(clienteValor.split(' ')[0]);

    if (!clienteId) {
        // Se cliente for apagado, limpa tudo
        limparResumoVenda();
        return;
    }

    // Limpar os itens atuais antes de carregar outro cliente
    document.querySelectorAll('input[name="material_id[]"], input[name="quantidade[]"], input[name="preco_unitario[]"]').forEach(e => e.remove());
    atualizarResumo();

    fetch(`recuperar_venda_temporaria_cliente.php?cliente_id=${clienteId}`)
        .then(r => r.json())
        .then(data => {
            if (data.status === 'ok') {
                let venda = JSON.parse(data.dados.venda_json);
                if (venda.lista_preco_id) document.getElementById('lista_preco').value = venda.lista_preco_id;
                venda.itens.forEach(item => {
                    document.getElementById('material_input').value = item.material_id;
                    document.getElementById('quantidade_input').value = item.quantidade;
                    document.getElementById('preco_input').value = item.preco_unitario;
                    adicionarOuEditarItem();
                });
            } else {
                console.log('Nenhuma venda aberta para o cliente.');
            }
        });
});




function abrirVendaSuspensaPorId(vendaId) {
    fetch('recuperar_venda_suspensa_por_id.php?id=' + vendaId)
        .then(response => response.json())
        .then(data => {
            if (data.status === 'ok') {
                const venda = data.dados;

                // Preencher o campo cliente
                if (venda.cliente_id) {
                    document.getElementById('cliente').value = venda.cliente_id;
                } else {
                    document.getElementById('cliente').value = '';
                }

                // Preencher o campo lista de preço
                if (venda.lista_preco_id) {
                    const lista = listas_precos.find(l => l.id == venda.lista_preco_id);
                    if (lista) {
                        document.getElementById('lista_preco').value = `${lista.id} - ${lista.nome}`;
                    } else {
                        document.getElementById('lista_preco').value = venda.lista_preco_id;
                    }
                } else {
                    document.getElementById('lista_preco').value = '';
                }

                // Limpar todos os itens antigos da venda
                document.querySelectorAll('input[name="material_id[]"]').forEach(e => e.remove());
                document.querySelectorAll('input[name="quantidade[]"]').forEach(e => e.remove());
                document.querySelectorAll('input[name="preco_unitario[]"]').forEach(e => e.remove());

                // Adicionar os itens da venda suspensa
                if (venda.itens && Array.isArray(venda.itens)) {
                    venda.itens.forEach(item => {
                        ['material_id', 'quantidade', 'preco_unitario'].forEach(function(field) {
                            let input = document.createElement('input');
                            input.type = 'hidden';
                            input.name = field + '[]';
                            if (field === 'material_id') {
                                input.value = item.material_id;
                            } else if (field === 'quantidade') {
                                input.value = item.quantidade;
                            } else {
                                input.value = item.preco_unitario;
                            }
                            document.getElementById('formVenda').appendChild(input);
                        });
                    });
                }

                // Atualizar o resumo visual da venda
                atualizarResumo();

                // Scrollar para o topo (se quiser pode remover)
                window.scrollTo({
                    top: 0,
                    behavior: 'smooth'
                });
            } else {
                alert('Erro: ' + data.msg);
            }
        })
        .catch(error => {
            console.error('Erro ao buscar a venda aberta:', error);
            alert('Erro ao buscar a venda aberta.');
        });
}




// Função para excluir uma venda suspensa
function excluirVendaSuspensa(id) {
    if (!confirm("Deseja excluir esta venda aberta?")) return;

    fetch('excluir_venda_suspensa.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'id=' + id
    })
    .then(r => r.json())
    .then(data => {
        if (data.status === 'ok') {
            alert('Venda aberta excluída.');
            location.reload();
        } else {
            alert('Erro ao excluir.');
        }
    });
}






document.getElementById('filtroSuspensos').addEventListener('input', function() {
    const termo = this.value.toLowerCase();
    document.querySelectorAll('#listaSuspensos .item-suspenso').forEach(function(item) {
        const clienteTexto = item.querySelector('.cliente-suspenso')?.innerText.toLowerCase() || '';
        const usuarioTexto = item.querySelector('.usuario-suspenso')?.innerText.toLowerCase() || '';

        // Se termo aparecer no cliente OU (se não tiver no cliente) então buscar no usuário
        if (clienteTexto.includes(termo) || (!clienteTexto.includes(termo) && usuarioTexto.includes(termo))) {
            item.style.display = '';
        } else {
            item.style.display = 'none';
        }
    });
});




</script>

<?php include __DIR__.'/includes/footer.php'; ?>
