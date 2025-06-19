<?php
require_once 'conexx/config.php';
include __DIR__.'/includes/header.php';
include __DIR__.'/includes/navbar.php';
include __DIR__.'/includes/footer.php';


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
        <!-- Lado Esquerdo: Formulário -->
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

                    <button type="button" id="adicionarItemBtn" class="btn btn-outline-primary w-100">Adicionar/Editar Item</button>

                    <!-- ... (seu HTML/PHP até o botão Finalizar Venda) ... -->

<!-- Botão Finalizar agora apenas abre o modal -->
<button type="button" class="btn btn-success mt-3 w-100" id="btnAbrirModalPagamento">Finalizar Venda</button>

<!-- Modal de Pagamento -->
<div class="modal fade" id="modalPagamento" tabindex="-1" aria-labelledby="modalPagamentoLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Formas de Pagamento</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
      </div>
      <div class="modal-body">

        <h6>Total da Venda: R$ <span id="modal_total_venda">0.00</span></h6>

        <div class="mb-2">
          <label><strong>Dinheiro:</strong></label>
          <input type="number" step="0.01" name="valor_dinheiro" id="valor_dinheiro" class="form-control" placeholder="Ex: 100.00">
        </div>

        <div class="mb-2">
          <label><strong>Pix:</strong></label>
          <input type="number" step="0.01" name="valor_pix" id="valor_pix" class="form-control" placeholder="Ex: 50.00">
        </div>

        <div class="mb-2">
          <label><strong>Cartão:</strong></label>
          <input type="number" step="0.01" name="valor_cartao" id="valor_cartao" class="form-control" placeholder="Ex: 150.00">
        </div>

        <h6>Total Pago: R$ <span id="modal_total_pago">0.00</span></h6>
        <div id="aviso_pagamento" class="mt-2 fw-bold text-danger"></div>

      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
        <button type="submit" class="btn btn-primary" id="btnConfirmarPagamento">Confirmar Pagamento</button>
      </div>
    </div>
  </div>
</div>


<script>
function atualizarModalPagamento() {
  let totalVenda = parseFloat(document.getElementById('total_venda').innerText) || 0;
  let dinheiro = parseFloat(document.getElementById('valor_dinheiro').value) || 0;
  let pix = parseFloat(document.getElementById('valor_pix').value) || 0;
  let cartao = parseFloat(document.getElementById('valor_cartao').value) || 0;

  let totalPago = dinheiro + pix + cartao;
  let diferenca = totalPago - totalVenda;

  document.getElementById('modal_total_venda').innerText = totalVenda.toFixed(2);
  document.getElementById('modal_total_pago').innerText = totalPago.toFixed(2);

  let aviso = '';
  if (totalPago < totalVenda) {
    aviso = `⚠️ Valor pago é menor que o total. Será gerado saldo devedor para o cliente.`;
  } else if (totalPago > totalVenda) {
    aviso = `⚠️ Valor pago é maior que o total. Será gerado troco ou saldo positivo.`;
  } else {
    aviso = `✅ Pagamento exato.`;
  }
  document.getElementById('aviso_pagamento').innerText = aviso;
}

document.addEventListener('DOMContentLoaded', function() {
  // Controle do ENTER: Só executa botão se o foco estiver num botão
  document.getElementById('formVenda').addEventListener('keydown', function(event) {
    const activeEl = document.activeElement;
    if (event.key === 'Enter') {
      const isButton = activeEl.tagName.toLowerCase() === 'button';
      if (isButton) {
        event.preventDefault();
        activeEl.click();
      } else {
        event.preventDefault();
        let inputs = Array.from(this.querySelectorAll('input, button, textarea, select')).filter(el => !el.disabled && el.offsetParent !== null);
        let index = inputs.indexOf(activeEl);
        if (index > -1 && index < inputs.length - 1) {
          inputs[index + 1].focus();
        }
      }
    }
  });

  // Atualiza cálculo ao digitar em qualquer campo de pagamento
  ['valor_dinheiro', 'valor_pix', 'valor_cartao'].forEach(function(id) {
    document.getElementById(id).addEventListener('input', atualizarModalPagamento);
  });

  // Abre o Modal
  document.getElementById('btnAbrirModalPagamento').addEventListener('click', function() {
    atualizarModalPagamento();
    let modal = new bootstrap.Modal(document.getElementById('modalPagamento'));
    modal.show();
  });

  // Submete o formulário só ao confirmar no modal
  document.getElementById('btnConfirmarPagamento').addEventListener('click', function() {
    document.getElementById('formVenda').submit();
  });
});
</script>


                </form>
            </div>
        </div>

        <!-- Lado Direito: Resumo -->
        <div class="col-md-5">
            <div class="section-card">
                <h4>Resumo da Venda</h4>
                <div id="resumo_itens"></div>
                <h5 class="mt-3">Total: R$ <span id="total_venda">0.00</span></h5>
            </div>
        </div>
    </div>
</div>

<script>
var clientes = <?php echo json_encode($clientes_arr); ?>;
var listas_precos = <?php echo json_encode($listas_precos_arr); ?>;
var materiais = <?php echo json_encode($materiais_arr); ?>;

function autocomplete(inputId, dataArray, extraCallback) {
    const input = document.getElementById(inputId);
    const listId = inputId + "_list";

    let datalist = document.getElementById(listId);
    if (!datalist) {
        datalist = document.createElement('datalist');
        datalist.id = listId;
        document.body.appendChild(datalist);
        input.setAttribute('list', listId);
    }

    input.addEventListener('input', function() {
        let term = this.value.toLowerCase();
        datalist.innerHTML = '';

        dataArray.forEach(function(item) {
            const textMatch = item.nome.toLowerCase().includes(term);
            const idMatch = !isNaN(term) && item.id == term;

            if (textMatch || idMatch) {
                let option = document.createElement('option');
                option.value = item.id + " - " + item.nome;
                datalist.appendChild(option);
            }
        });

        if (extraCallback) extraCallback();
    });
}

function atualizarResumo() {
    let total = 0;
    let html = '';
    document.querySelectorAll('input[name="material_id[]"]').forEach(function(input, index) {
        let mat = input.value;
        let qtd = document.getElementsByName('quantidade[]')[index].value;
        total += (parseFloat(qtd) || 0) * 10;
        html += `<div>${mat} - Qtd: ${qtd} 
            <span class="item-actions">
                <button type="button" onclick="editarItem(${index})">✏️</button>
                <button type="button" onclick="removerItem(${index})">🗑️</button>
            </span>
        </div>`;
    });
    document.getElementById('resumo_itens').innerHTML = html;
    document.getElementById('total_venda').innerText = total.toFixed(2);
}

function adicionarOuEditarItem() {
    let material = document.getElementById('material_input').value.trim();
    let quantidade = document.getElementById('quantidade_input').value;
    let editIndex = document.getElementById('edit_index').value;

    if (!material || quantidade <= 0) {
        alert("Preencha material e quantidade corretamente");
        return;
    }

    if (editIndex !== '') {
        document.getElementsByName('material_id[]')[editIndex].value = material;
        document.getElementsByName('quantidade[]')[editIndex].value = quantidade;
        document.getElementById('edit_index').value = '';
    } else {
        let inputMat = document.createElement('input');
        inputMat.type = 'hidden';
        inputMat.name = 'material_id[]';
        inputMat.value = material;

        let inputQtd = document.createElement('input');
        inputQtd.type = 'hidden';
        inputQtd.name = 'quantidade[]';
        inputQtd.value = quantidade;

        document.getElementById('formVenda').appendChild(inputMat);
        document.getElementById('formVenda').appendChild(inputQtd);
    }

    document.getElementById('material_input').value = '';
    document.getElementById('quantidade_input').value = '';
    document.getElementById('material_input').focus();
    atualizarResumo();
}

function editarItem(index) {
    document.getElementById('material_input').value = document.getElementsByName('material_id[]')[index].value;
    document.getElementById('quantidade_input').value = document.getElementsByName('quantidade[]')[index].value;
    document.getElementById('edit_index').value = index;
    document.getElementById('material_input').focus();
}

function removerItem(index) {
    document.getElementsByName('material_id[]')[index].remove();
    document.getElementsByName('quantidade[]')[index].remove();
    atualizarResumo();
}

document.addEventListener('DOMContentLoaded', function() {
    autocomplete('cliente', clientes);
    autocomplete('lista_preco', listas_precos);
    autocomplete('material_input', materiais);

    document.getElementById('adicionarItemBtn').addEventListener('click', adicionarOuEditarItem);

    // Controle de ENTER
    document.getElementById('formVenda').addEventListener('keydown', function(event) {
        const activeEl = document.activeElement;

        if (event.key === 'Enter') {
            const isButton = activeEl.tagName.toLowerCase() === 'button';

            if (isButton) {
                event.preventDefault();
                activeEl.click();
            } else {
                event.preventDefault();

                let inputs = Array.from(this.querySelectorAll('input, button, textarea, select')).filter(el => !el.disabled && el.offsetParent !== null);
                let index = inputs.indexOf(activeEl);
                if (index > -1 && index < inputs.length - 1) {
                    inputs[index + 1].focus();
                }
            }
        }
    });
});
</script>
