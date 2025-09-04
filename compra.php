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

// Determina lista padrão da empresa
$lista_preco_padrao = null;
$stmt_lp = $conn->prepare("SELECT id, nome FROM listas_precos WHERE empresa_id = ? AND padrao = 1 LIMIT 1");
$stmt_lp->bind_param('i', $empresa_id);
$stmt_lp->execute();
$res_lp = $stmt_lp->get_result();
if ($res_lp && $r_lp = $res_lp->fetch_assoc()) {
    $lista_preco_padrao = ['id' => intval($r_lp['id']), 'nome' => $r_lp['nome']];
} else {
    $stmt_lp2 = $conn->prepare("SELECT id, nome FROM listas_precos WHERE empresa_id = ? ORDER BY id LIMIT 1");
    $stmt_lp2->bind_param('i', $empresa_id);
    $stmt_lp2->execute();
    $res_lp2 = $stmt_lp2->get_result();
    if ($res_lp2 && $r_lp2 = $res_lp2->fetch_assoc()) $lista_preco_padrao = ['id' => intval($r_lp2['id']), 'nome' => $r_lp2['nome']];
}

// Materiais (somente da empresa para melhorar performance)
$materiais_arr = [];
$res = $conn->query("SELECT id, nome FROM materiais WHERE empresa_id = " . intval($empresa_id));
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
                        <div style="position:relative;">
                            <input type="text" name="cliente_id" id="cliente" class="form-control mb-2">
                            <ul id="dropdown-clientes" class="list-group" style="position:absolute; left:0; top:100%; width:100%; z-index:9999; display:none; max-height:220px; overflow-y:auto;"></ul>
                        </div>

                        <label><strong>Lista de Preços:</strong></label>
                        <input type="text" name="lista_preco_id" id="lista_preco" class="form-control mb-2">

                        <h5>Adicionar Item:</h5>
                        <input type="hidden" id="edit_index" value="">
                        <div style="position:relative;">
                            <input type="text" id="material_input" class="form-control mb-2" placeholder="Material (ID ou Nome)">
                            <ul id="dropdown-materiais" class="list-group" style="position:absolute; left:0; top:100%; width:100%; z-index:9999; display:none; max-height:220px; overflow-y:auto;"></ul>
                        </div>
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
var empresa_id = <?php echo json_encode($empresa_id); ?>;
var precos = <?php echo json_encode($precos_materiais); ?>;
var lista_preco_padrao = <?php echo json_encode($lista_preco_padrao); ?>;

(function(){
    // util debounce
    function debounce(fn, wait){
        let t;
        return function(){
            const args = arguments;
            clearTimeout(t);
            t = setTimeout(() => fn.apply(this, args), wait);
        };
    }

    function atualizarResumo(){
        let total = 0;
        let rows = '';
        const mats = document.getElementsByName('material_id[]');
        for(let i=0;i<mats.length;i++){
            const mid = parseInt(mats[i].value.split(' ')[0]);
            const nome = (materiais.find(m=>m.id==mid)||{}).nome || ('ID '+mid);
            const qtd = parseFloat(document.getElementsByName('quantidade[]')[i].value) || 0;
            const preco = parseFloat(document.getElementsByName('preco_unitario[]')[i].value) || 0;
            const subtotal = qtd*preco;
            total += subtotal;
            rows += `<tr><td style="padding:8px;border-bottom:1px solid #ddd;">${nome}</td>`+
                    `<td style="padding:8px;text-align:right;border-bottom:1px solid #ddd;">${qtd}</td>`+
                    `<td style="padding:8px;text-align:right;border-bottom:1px solid #ddd;">R$ ${preco.toFixed(2)}</td>`+
                    `<td style="padding:8px;text-align:right;border-bottom:1px solid #ddd;">R$ ${subtotal.toFixed(2)}</td>`+
                    `<td style="padding:8px;text-align:center;border-bottom:1px solid #ddd;">`+
                    `<button type="button" onclick="editarItem(${i})" style="background:none;border:none;cursor:pointer;">✏️</button>`+
                    `<button type="button" onclick="removerItem(${i})" style="background:none;border:none;cursor:pointer;margin-left:8px;">🗑️</button>`+
                    `</td></tr>`;
        }
        const table = `<table style="width:100%;border-collapse:collapse;font-family:Arial,sans-serif;"><thead><tr style="background:#f0f0f0;"><th style="text-align:left;padding:8px;border-bottom:2px solid #ccc;">Material</th><th style="text-align:right;padding:8px;border-bottom:2px solid #ccc;">Qtd</th><th style="text-align:right;padding:8px;border-bottom:2px solid #ccc;">Preço Unit.</th><th style="text-align:right;padding:8px;border-bottom:2px solid #ccc;">Subtotal</th><th style="padding:8px;border-bottom:2px solid #ccc;">Ações</th></tr></thead><tbody>`+rows+`</tbody></table>`;
        const resumo = document.getElementById('resumo_itens'); if(resumo) resumo.innerHTML = table;
        const totalEl = document.getElementById('total_compra'); if(totalEl) totalEl.innerText = total.toFixed(2);
    }

    window.adicionarOuEditarItem = function(){
        const materialEl = document.getElementById('material_input');
        const qtdEl = document.getElementById('quantidade_input');
        const precoEl = document.getElementById('preco_input');
        const editIndexEl = document.getElementById('edit_index');
        if(!materialEl || !qtdEl || !precoEl) return;
        const material = materialEl.value.trim();
        const quantidade = parseFloat(qtdEl.value);
        const precoUnitario = parseFloat(precoEl.value);
        const editIndex = editIndexEl && editIndexEl.value !== '' ? parseInt(editIndexEl.value) : null;
        if(!material || isNaN(quantidade) || quantidade<=0 || isNaN(precoUnitario) || precoUnitario<=0){
            alert('Preencha material, quantidade e preço corretamente');
            return;
        }
        const materialId = parseInt(material.split(' ')[0]);
        const materialObj = materiais.find(m=>m.id==materialId);
        if(!materialObj){ alert('Material não encontrado no banco de dados!'); return; }
        const materialValue = materialObj.id+' - '+materialObj.nome;
        if(editIndex !== null && !isNaN(editIndex)){
            const mid = document.getElementsByName('material_id[]')[editIndex];
            const q = document.getElementsByName('quantidade[]')[editIndex];
            const p = document.getElementsByName('preco_unitario[]')[editIndex];
            if(mid) mid.value = materialValue;
            if(q) q.value = quantidade;
            if(p) p.value = precoUnitario;
            if(editIndexEl) editIndexEl.value = '';
        } else {
            ['material_id','quantidade','preco_unitario'].forEach(function(field){
                const input = document.createElement('input');
                input.type = 'hidden'; input.name = field+'[]';
                input.value = field==='material_id' ? materialValue : (field==='quantidade' ? quantidade : precoUnitario);
                document.getElementById('formCompra').appendChild(input);
            });
        }
        materialEl.value=''; qtdEl.value=''; precoEl.value=''; materialEl.focus(); atualizarResumo();
    };

    window.editarItem = function(index){
        const mid = document.getElementsByName('material_id[]')[index];
        const q = document.getElementsByName('quantidade[]')[index];
        const p = document.getElementsByName('preco_unitario[]')[index];
        if(mid) document.getElementById('material_input').value = mid.value;
        if(q) document.getElementById('quantidade_input').value = q.value;
        if(p) document.getElementById('preco_input').value = p.value;
        const editIndexEl = document.getElementById('edit_index'); if(editIndexEl) editIndexEl.value = index;
        document.getElementById('material_input').focus();
    };

    window.removerItem = function(index){
        const mids = document.getElementsByName('material_id[]');
        const qtds = document.getElementsByName('quantidade[]');
        const precs = document.getElementsByName('preco_unitario[]');
        if(mids[index]) mids[index].remove();
        if(qtds[index]) qtds[index].remove();
        if(precs[index]) precs[index].remove();
        atualizarResumo();
    };

    function preencherPrecoAutomatico(){
        try{
            let listaVal = document.getElementById('lista_preco')?.value || '';
            let listaId = parseInt(listaVal.split(' ')[0]);
            if(!listaId){
                const clienteVal = document.getElementById('cliente')?.value || '';
                const clienteId = parseInt(clienteVal.split(' ')[0]);
                if(clienteId){
                    const clienteObj = clientes.find(c=>c.id==clienteId);
                    if(clienteObj && clienteObj.lista_preco_id) listaId = clienteObj.lista_preco_id;
                }
            }
            if(!listaId && lista_preco_padrao && lista_preco_padrao.id) listaId = lista_preco_padrao.id;
            const materialId = parseInt((document.getElementById('material_input')?.value||'').split(' ')[0]);
            if(listaId && materialId && precos && precos[listaId] && precos[listaId][materialId]){
                document.getElementById('preco_input').value = precos[listaId][materialId];
            }
        }catch(e){/* noop */}
    }

    // busca produtos local (filtra array materiais)
    const resultadoMateriais = document.getElementById('dropdown-materiais');
    const materialInput = document.getElementById('material_input');
    const debounceProdutos = debounce(function(termo){
        if(!termo || termo.length<1){ resultadoMateriais.innerHTML=''; resultadoMateriais.style.display='none'; return; }
        // filtrar localmente (nome ou id)
        const termoLower = termo.toLowerCase();
        const data = materiais.filter(m => String(m.id) === termo || m.nome.toLowerCase().includes(termoLower) || String(m.id) === termoLower);
        resultadoMateriais.innerHTML=''; resultadoMateriais.style.display = data.length ? 'block' : 'none';
        data.forEach(function(mat, idx){
            const li = document.createElement('li'); li.className='list-group-item list-group-item-action';
            li.tabIndex = 0;
            li.dataset.idx = idx;
            li.textContent = mat.id+' - '+mat.nome;
            li.onclick = function(){ materialInput.value = mat.id+' - '+mat.nome; resultadoMateriais.innerHTML=''; resultadoMateriais.style.display='none'; preencherPrecoAutomatico(); document.getElementById('quantidade_input').focus(); };
            li.addEventListener('mouseenter', function(){ resultadoMateriais.querySelectorAll('li').forEach(x=>x.classList.remove('active')); this.classList.add('active'); });
            resultadoMateriais.appendChild(li);
        });
        // selecionar primeiro resultado visualmente
        const first = resultadoMateriais.querySelector('li'); if(first){ resultadoMateriais.querySelectorAll('li').forEach(x=>x.classList.remove('active')); first.classList.add('active'); }
    },180);

    if(materialInput){
        materialInput.addEventListener('input', function(){ debounceProdutos(this.value.trim()); });
        materialInput.addEventListener('keydown', function(e){
            const items = resultadoMateriais?.querySelectorAll('li')||[];
            if(e.key==='Enter'){
                if(this.value.trim()===''){
                    // abrir modal se tiver itens
                    if(document.getElementsByName('material_id[]').length>0){ document.getElementById('btnAbrirModalPagamento').click(); }
                    e.preventDefault();
                    return;
                }
                // se usuário digitou ID, preencher
                const maybeId = parseInt(this.value.split(' ')[0]);
                const found = materiais.find(m=>m.id==maybeId);
                if(found){ this.value = found.id+' - '+found.nome; preencherPrecoAutomatico(); document.getElementById('quantidade_input').focus(); }
            }
        });
        materialInput.addEventListener('blur', function(){ setTimeout(()=>{ resultadoMateriais.innerHTML=''; resultadoMateriais.style.display='none'; },150); });
    }

    // busca clientes local (filtra array clientes)
    const clienteInput = document.getElementById('cliente');
    const resultadoClientes = document.getElementById('dropdown-clientes');
    if(clienteInput){
        const debCli = debounce(function(termo){
            if(!termo || termo.length<1){ resultadoClientes.innerHTML=''; resultadoClientes.style.display='none'; return; }
            const termoLower = termo.toLowerCase();
            const data = clientes.filter(c => String(c.id) === termo || c.nome.toLowerCase().includes(termoLower) || String(c.id) === termoLower);
            resultadoClientes.innerHTML=''; resultadoClientes.style.display = data.length ? 'block' : 'none';
            data.forEach(function(cli, idx){
                const li = document.createElement('li'); li.className='list-group-item list-group-item-action'; li.tabIndex = 0; li.dataset.idx = idx;
                li.textContent = cli.id+' - '+cli.nome;
                li.onclick = function(){ clienteInput.value = cli.id+' - '+cli.nome; resultadoClientes.innerHTML=''; resultadoClientes.style.display='none'; if(cli.lista_preco_id){ const l = listas_precos.find(x=>x.id==cli.lista_preco_id); document.getElementById('lista_preco').value = l ? (l.id+' - '+l.nome) : cli.lista_preco_id; } document.getElementById('material_input').focus(); };
                li.addEventListener('mouseenter', function(){ resultadoClientes.querySelectorAll('li').forEach(x=>x.classList.remove('active')); this.classList.add('active'); });
                resultadoClientes.appendChild(li);
            });
            // selecionar primeiro cliente
            const firstCli = resultadoClientes.querySelector('li'); if(firstCli){ resultadoClientes.querySelectorAll('li').forEach(x=>x.classList.remove('active')); firstCli.classList.add('active'); }
        },180);
        clienteInput.addEventListener('input', function(){ debCli(this.value.trim()); });
        clienteInput.addEventListener('keydown', function(e){
            if(e.key==='Enter'){
                e.preventDefault();
                if(resultadoClientes.style.display==='block'){
                    const active = resultadoClientes.querySelector('li.active') || resultadoClientes.querySelector('li');
                    if(active){ clienteInput.value = active.textContent; resultadoClientes.innerHTML=''; resultadoClientes.style.display='none'; if(active) document.getElementById('material_input').focus(); return; }
                }
            }
            if(e.key==='ArrowDown' && resultadoClientes.style.display==='block'){
                e.preventDefault(); const current = resultadoClientes.querySelector('li.active'); const next = current && current.nextElementSibling ? current.nextElementSibling : resultadoClientes.querySelector('li'); if(current) current.classList.remove('active'); if(next) next.classList.add('active');
            }
            if(e.key==='ArrowUp' && resultadoClientes.style.display==='block'){
                e.preventDefault(); const current = resultadoClientes.querySelector('li.active'); const prev = current && current.previousElementSibling ? current.previousElementSibling : resultadoClientes.querySelector('li:last-child'); if(current) current.classList.remove('active'); if(prev) prev.classList.add('active');
            }
        });
        clienteInput.addEventListener('blur', function(){ setTimeout(()=>{ resultadoClientes.innerHTML=''; resultadoClientes.style.display='none'; },150); });
    }

    // eventos de teclado para quantidade/preco
    const quantidadeInput = document.getElementById('quantidade_input');
    const precoInput = document.getElementById('preco_input');
    if(quantidadeInput){ quantidadeInput.addEventListener('keydown', function(e){ if(e.key==='Enter'){ e.preventDefault(); const v=parseFloat(this.value); if(!isNaN(v)&&v>0) precoInput.focus(); else alert('Quantidade obrigatória!'); } }); }
    if(precoInput){ precoInput.addEventListener('keydown', function(e){ if(e.key==='Enter'){ e.preventDefault(); const v=parseFloat(this.value); if(!isNaN(v)&&v>0){ adicionarOuEditarItem(); setTimeout(()=>materialInput.focus(),50); } else alert('Preço obrigatório!'); } }); }

    // preencher preço ao focar
    if(precoInput){ precoInput.addEventListener('focus', preencherPrecoAutomatico); }

    // botões modal pagamento
    const btnAbrir = document.getElementById('btnAbrirModalPagamento');
    if(btnAbrir){ btnAbrir.addEventListener('click', function(e){ e.preventDefault(); // atualizar modal totals
            const total = parseFloat(document.getElementById('total_compra')?.innerText)||0; document.getElementById('modal_total_compra').innerText = total.toFixed(2);
            // show modal
            new bootstrap.Modal(document.getElementById('modalPagamentoCompra')).show();
        }); }

    // confirmar pagamento (copiar campos)
    const btnConfirmar = document.getElementById('btnConfirmarPagamentoCompra');
    if(btnConfirmar){ btnConfirmar.addEventListener('click', function(){ const form = document.getElementById('formCompra'); ['valor_dinheiro_compra','valor_pix_compra','valor_cartao_compra'].forEach(function(id){ const el = document.getElementById(id); if(!el) return; const name = id.replace('_compra',''); let existing = form.querySelector('input[name="'+name+'"]'); if(existing) existing.value = el.value||0; else{ const h = document.createElement('input'); h.type='hidden'; h.name = name; h.value = el.value||0; form.appendChild(h); } }); const gerar = document.getElementById('gerar_troco_compra')?.checked ? 1 : 0; let existingTroco = form.querySelector('input[name="gerar_troco"]'); if(existingTroco) existingTroco.value = gerar; else{ const h2 = document.createElement('input'); h2.type='hidden'; h2.name='gerar_troco'; h2.value = gerar; form.appendChild(h2); } form.submit(); }); }

    // garantir foco ao abrir/fechar modal
    const modalCompraEl = document.getElementById('modalPagamentoCompra');
    if(modalCompraEl){ modalCompraEl.addEventListener('shown.bs.modal', function(){ const vd = document.getElementById('valor_dinheiro_compra'); if(vd) vd.focus(); }); modalCompraEl.addEventListener('hidden.bs.modal', function(){ setTimeout(()=>{ if(materialInput) materialInput.focus(); document.querySelectorAll('.modal-backdrop').forEach(b=>b.remove()); },50); }); }

    // foco inicial
    setTimeout(()=>{ try{ document.getElementById('material_input')?.focus(); }catch(e){} },200);

    // expose preencherPrecoAutomatico globally for other handlers
    window.preencherPrecoAutomatico = preencherPrecoAutomatico;
    window.atualizarResumo = atualizarResumo;
})();
</script>
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