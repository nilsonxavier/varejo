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
                        <input type="number" id="quantidade_input" class="form-control mb-2" placeholder="Quantidade em KG" step="0.01" min="0">
                        <input type="number" id="tara_input" class="form-control mb-2" placeholder="Tara (impureza, berg em KG.)" step="0.01" min="0">
                        <input type="number" id="preco_input" class="form-control mb-2" placeholder="Preço KG" step="0.01" min="0">

                        <button type="button" id="adicionarItemBtn" class="btn btn-outline-primary w-100">Adicionar/Editar Item</button>

                        <button type="button" class="btn btn-success mt-3 w-100" id="btnAbrirModalPagamento" onclick="abrirModalPagamentoCompra(); return false;">Finalizar Compra</button>
                    </form>
                </div>
            </div>

            <div class="col-md-5">
                <div class="section-card">
                    <h4>Resumo da Compra</h4>
                    <div id="resumo_itens"></div>
                    <h5>Total: R$ <span id="total_compra">0.00</span></h5>
                    <button type="button" class="btn btn-sm btn-outline-info mt-2" onclick="mesclarItens()" style="display:none;">Mesclar Itens</button>
                </div>
                <hr>
                <h5>Compras Suspensas</h5>
                <input type="text" id="filtroSuspensosCompra" placeholder="Filtrar por cliente" class="form-control mb-2">
                <div id="listaSuspensosCompra"></div>
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

        <!-- Modal Pagamento Compra -->
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
                            <input type="number" step="0.01" name="valor_dinheiro_compra" id="valor_dinheiro_compra" class="form-control">
                        </div>
                        <div class="mb-2">
                            <label>Pix:</label>
                            <input type="number" step="0.01" name="valor_pix_compra" id="valor_pix_compra" class="form-control">
                        </div>
                        <div class="mb-2">
                            <label>Cartão:</label>
                            <input type="number" step="0.01" name="valor_cartao_compra" id="valor_cartao_compra" class="form-control">
                        </div>
                        <div class="mb-2" id="div_abater_compra" style="display:none;">
                            <label>Abater (saldo do cliente):</label>
                            <input type="number" step="0.01" name="valor_abater_compra" id="valor_abater_compra" class="form-control">
                        </div>

                        <h6>Total Pago: R$ <span id="modal_total_pago_compra">0.00</span></h6>
                        <h6>Falta: R$ <span id="modal_falta_compra">0.00</span></h6>
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
var clientes = <?php echo json_encode($clientes_arr); ?>;
var listas_precos = <?php echo json_encode($listas_precos_arr); ?>;
var materiais = <?php echo json_encode($materiais_arr); ?>;
var empresa_id = <?php echo json_encode($empresa_id); ?>;
var precos = <?php echo json_encode($precos_materiais); ?>;
var lista_preco_padrao = <?php echo json_encode($lista_preco_padrao); ?>;

// Variável de controle para impedir salvamento durante finalização
var finalizandoCompra = false;

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
        
        // Verificar se há itens duplicados (mesmo material_id e preço)
        let temDuplicados = false;
        let itensMap = {};
        
        for(let i=0;i<mats.length;i++){
            const mid = parseInt(mats[i].value.split(' ')[0]);
            const preco = parseFloat(document.getElementsByName('preco_unitario[]')[i].value) || 0;
            const chave = mid + '_' + preco;
            
            if(itensMap[chave]){
                temDuplicados = true;
            } else {
                itensMap[chave] = true;
            }
        }
        
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
        
        // Mostrar/ocultar botão mesclar
        const btnMesclar = document.querySelector('button[onclick="mesclarItens()"]');
        if(btnMesclar){
            btnMesclar.style.display = temDuplicados ? 'inline-block' : 'none';
        }
    }

    window.adicionarOuEditarItem = function(){
        const materialEl = document.getElementById('material_input');
        const qtdEl = document.getElementById('quantidade_input');
        const precoEl = document.getElementById('preco_input');
        const editIndexEl = document.getElementById('edit_index');
        if(!materialEl || !qtdEl || !precoEl) return;
        const material = materialEl.value.trim();
        const quantidade = parseFloat(qtdEl.value);
        const taraEl = document.getElementById('tara_input');
        let tara = taraEl ? parseFloat(taraEl.value) : 0;
        if(isNaN(tara)) tara = 0;
        let quantidadeFinal = quantidade - tara;
        if(quantidadeFinal <= 0){
            alert('A quantidade final (quantidade - tara) deve ser maior que zero!');
            return;
        }
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
            if(q) q.value = quantidadeFinal;
            if(p) p.value = precoUnitario;
            if(editIndexEl) editIndexEl.value = '';
        } else {
            ['material_id','quantidade','preco_unitario'].forEach(function(field){
                const input = document.createElement('input');
                input.type = 'hidden'; input.name = field+'[]';
                input.value = field==='material_id' ? materialValue : (field==='quantidade' ? quantidadeFinal : precoUnitario);
                document.getElementById('formCompra').appendChild(input);
            });
        }
    materialEl.value=''; qtdEl.value=''; precoEl.value=''; if(taraEl) taraEl.value=''; materialEl.focus(); atualizarResumo();
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

    window.mesclarItens = function(){
        const mats = document.getElementsByName('material_id[]');
        const qtds = document.getElementsByName('quantidade[]');
        const precs = document.getElementsByName('preco_unitario[]');
        
        let itensUnicos = {};
        
        // Agrupa itens por material_id + preco_unitario
        for(let i=0;i<mats.length;i++){
            const materialId = mats[i].value;
            const quantidade = parseFloat(qtds[i].value);
            const preco = parseFloat(precs[i].value);
            const chave = materialId + '_' + preco;
            
            if(itensUnicos[chave]){
                itensUnicos[chave].quantidade += quantidade;
            } else {
                itensUnicos[chave] = {
                    material_id: materialId,
                    quantidade: quantidade,
                    preco_unitario: preco
                };
            }
        }
        
        // Remove todos os itens atuais
        document.querySelectorAll('input[name="material_id[]"], input[name="quantidade[]"], input[name="preco_unitario[]"]').forEach(e => e.remove());
        
        // Adiciona itens mesclados
        Object.values(itensUnicos).forEach(item => {
            ['material_id','quantidade','preco_unitario'].forEach(function(field){
                const input = document.createElement('input');
                input.type = 'hidden'; input.name = field+'[]';
                input.value = field==='material_id' ? item.material_id : (field==='quantidade' ? item.quantidade : item.preco_unitario);
                document.getElementById('formCompra').appendChild(input);
            });
        });
        
        atualizarResumo();
        alert('Itens mesclados com sucesso!');
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
                if(resultadoMateriais.style.display==='block' && items.length>0){
                    // Confirma o item ativo do dropdown
                    const active = resultadoMateriais.querySelector('li.active') || resultadoMateriais.querySelector('li');
                    if(active){
                        materialInput.value = active.textContent;
                        resultadoMateriais.innerHTML=''; resultadoMateriais.style.display='none';
                        preencherPrecoAutomatico();
                        document.getElementById('quantidade_input').focus();
                        e.preventDefault();
                        return;
                    }
                }
                if(this.value.trim()===''){
                    // abrir modal se tiver itens
                    if(document.getElementsByName('material_id[]').length>0){ abrirModalPagamentoCompra(); }
                    e.preventDefault();
                    return;
                }
                // se usuário digitou ID, preencher
                const maybeId = parseInt(this.value.split(' ')[0]);
                const found = materiais.find(m=>m.id==maybeId);
                if(found){ this.value = found.id+' - '+found.nome; preencherPrecoAutomatico(); document.getElementById('quantidade_input').focus(); }
            }
            if(e.key==='ArrowDown' && resultadoMateriais.style.display==='block'){
                e.preventDefault();
                const current = resultadoMateriais.querySelector('li.active');
                const next = current && current.nextElementSibling ? current.nextElementSibling : resultadoMateriais.querySelector('li');
                if(current) current.classList.remove('active');
                if(next) next.classList.add('active');
            }
            if(e.key==='ArrowUp' && resultadoMateriais.style.display==='block'){
                e.preventDefault();
                const current = resultadoMateriais.querySelector('li.active');
                const prev = current && current.previousElementSibling ? current.previousElementSibling : resultadoMateriais.querySelector('li:last-child');
                if(current) current.classList.remove('active');
                if(prev) prev.classList.add('active');
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
                        if(active){
                            // preenche o campo com o item ativo
                            clienteInput.value = active.textContent;
                            // tenta preencher a lista de preço automaticamente como no clique
                            try{
                                const clientId = parseInt(active.textContent.split(' ')[0]);
                                const cliObj = clientes.find(c=>c.id==clientId);
                                if(cliObj && cliObj.lista_preco_id){ const l = listas_precos.find(x=>x.id==cliObj.lista_preco_id); document.getElementById('lista_preco').value = l ? (l.id+' - '+l.nome) : cliObj.lista_preco_id; }
                            }catch(e){}
                            resultadoClientes.innerHTML=''; resultadoClientes.style.display='none';
                            if(active) document.getElementById('material_input').focus();
                            return;
                        }
                    } else {
                        // Se a lista não está aberta, tenta interpretar o valor digitado (ex: "123 - Nome") e preencher a lista
                        try{
                            const val = clienteInput.value.trim();
                            const clientId = parseInt(val.split(' ')[0]);
                            if(clientId){ const cliObj = clientes.find(c=>c.id==clientId); if(cliObj && cliObj.lista_preco_id){ const l = listas_precos.find(x=>x.id==cliObj.lista_preco_id); document.getElementById('lista_preco').value = l ? (l.id+' - '+l.nome) : cliObj.lista_preco_id; } }
                        }catch(e){}
                        // mover foco para material
                        if(document.getElementById('material_input')) document.getElementById('material_input').focus();
                        return;
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

    // eventos de teclado para quantidade/tara/preco
    const quantidadeInput = document.getElementById('quantidade_input');
    const taraInput = document.getElementById('tara_input');
    const precoInput = document.getElementById('preco_input');
    if(quantidadeInput){
        quantidadeInput.addEventListener('keydown', function(e){
            if(e.key==='Enter'){
                e.preventDefault();
                const v=parseFloat(this.value);
                if(!isNaN(v)&&v>0){
                    if(taraInput) taraInput.focus();
                } else alert('Quantidade obrigatória!');
            }
        });
    }
    if(taraInput){
        taraInput.addEventListener('keydown', function(e){
            if(e.key==='Enter'){
                e.preventDefault();
                if(precoInput) precoInput.focus();
            }
        });
    }
    if(precoInput){
        precoInput.addEventListener('keydown', function(e){
            if(e.key==='Enter'){
                e.preventDefault();
                const v=parseFloat(this.value);
                if(!isNaN(v)&&v>0){
                    window.adicionarOuEditarItem();
                    setTimeout(()=>materialInput.focus(),50);
                } else alert('Preço obrigatório!');
            }
        });
        precoInput.addEventListener('focus', preencherPrecoAutomatico);
    }

    // botões modal pagamento
    const btnAbrir = document.getElementById('btnAbrirModalPagamento');
    if(btnAbrir){ btnAbrir.addEventListener('click', function(e){ e.preventDefault(); // atualizar modal totals
            const total = parseFloat(document.getElementById('total_compra')?.innerText)||0; document.getElementById('modal_total_compra').innerText = total.toFixed(2);
            // show modal
            new bootstrap.Modal(document.getElementById('modalPagamentoCompra')).show();
        }); }

    // confirmar pagamento (copiar campos)
    const btnConfirmar = document.getElementById('btnConfirmarPagamentoCompra');
    if(btnConfirmar){ btnConfirmar.addEventListener('click', function(){ finalizandoCompra = true; const form = document.getElementById('formCompra'); ['valor_dinheiro_compra','valor_pix_compra','valor_cartao_compra','valor_abater_compra'].forEach(function(id){ const el = document.getElementById(id); if(!el) return; const name = id.replace('_compra',''); let existing = form.querySelector('input[name="'+name+'"]'); if(existing) existing.value = el.value||0; else{ const h = document.createElement('input'); h.type='hidden'; h.name = name; h.value = el.value||0; form.appendChild(h); } }); const gerar = document.getElementById('gerar_troco_compra')?.checked ? 1 : 0; let existingTroco = form.querySelector('input[name="gerar_troco"]'); if(existingTroco) existingTroco.value = gerar; else{ const h2 = document.createElement('input'); h2.type='hidden'; h2.name='gerar_troco'; h2.value = gerar; form.appendChild(h2); } form.submit(); }); }

    // garantir foco ao abrir/fechar modal
    const modalCompraEl = document.getElementById('modalPagamentoCompra');
    if(modalCompraEl){ 
        modalCompraEl.addEventListener('shown.bs.modal', function(){ 
            const vd = document.getElementById('valor_dinheiro_compra'); 
            if(vd) vd.focus(); 
        }); 
        modalCompraEl.addEventListener('hidden.bs.modal', function(){ 
            // Restaurar estado normal quando modal é fechado/cancelado
            finalizandoCompra = false;
            setTimeout(()=>{ 
                if(materialInput) materialInput.focus(); 
                document.querySelectorAll('.modal-backdrop').forEach(b=>b.remove()); 
            },50); 
        }); 
    }

    // botão adicionar item
    const btnAdicionarItem = document.getElementById('adicionarItemBtn');
    if(btnAdicionarItem){
        btnAdicionarItem.addEventListener('click', function(e){
            e.preventDefault();
            window.adicionarOuEditarItem();
        });
    }

    // foco inicial
    setTimeout(()=>{ try{ document.getElementById('material_input')?.focus(); }catch(e){} },200);

    // Salvamento automático periódico (a cada 30 segundos)
    setInterval(function(){
        if(finalizandoCompra) return; // Não salvar se estiver finalizando compra
        const mats = document.getElementsByName('material_id[]');
        if(mats.length > 0) { // Só salvar se tiver itens no carrinho
            salvarCompraTemporaria();
        }
    }, 30000); // 30 segundos

    // Salvar antes de sair da página
    window.addEventListener('beforeunload', function(e) {
        if(finalizandoCompra) return; // Não salvar se estiver finalizando compra
        const mats = document.getElementsByName('material_id[]');
        if(mats.length > 0) { // Só salvar se tiver itens
            salvarCompraTemporaria();
        }
    });

    // expose preencherPrecoAutomatico globally for other handlers
    window.preencherPrecoAutomatico = preencherPrecoAutomatico;
    window.atualizarResumo = atualizarResumo;
    // função para abrir modal de pagamento (usada por botão e Enter)
    window.abrirModalPagamentoCompra = function(){
        try{
            atualizarResumo();
            // atualizar valores do modal
            const total = parseFloat(document.getElementById('total_compra')?.innerText) || 0;
            document.getElementById('modal_total_compra').innerText = total.toFixed(2);
            // reset total pago display
            document.getElementById('modal_total_pago_compra').innerText = '0.00';
            document.getElementById('aviso_pagamento_compra').innerText = '';
            // reset campos de pagamento
            document.getElementById('valor_dinheiro_compra').value = '';
            document.getElementById('valor_pix_compra').value = '';
            document.getElementById('valor_cartao_compra').value = '';
            document.getElementById('valor_abater_compra').value = '';
            // atualizar visibilidade e cálculos
            atualizarModalPagamentoCompra();
            new bootstrap.Modal(document.getElementById('modalPagamentoCompra')).show();
        }catch(e){ console.error('Erro ao abrir modal pagamento:', e); }
    };
    
})();

    // Função para salvar compra temporária
var ultimoSalvamento = 0; // Timestamp do último salvamento

function salvarCompraTemporaria(){
    if(finalizandoCompra) return; // Não salvar se estiver finalizando compra
    
    // Evitar salvamentos muito frequentes (mínimo 2 segundos entre salvamentos)
    const agora = Date.now();
    if(agora - ultimoSalvamento < 2000) return;
    ultimoSalvamento = agora;
    
    const form = document.getElementById('formCompra');
    if(!form) return;
    const formData = new FormData(form);
    fetch('salvar_compra_temporaria.php', {
        method: 'POST',
        body: formData
    })
    .then(r => r.json())
    .then(data => {
        // opcional: console.log(data.msg);
    })
    .catch(err => console.error('Erro ao salvar compra temporária:', err));
}

// Função para carregar compra temporária do cliente
function carregarCompraTemporaria(clienteId){
    fetch('recuperar_compra_temporaria_cliente.php?cliente_id='+clienteId)
    .then(r => r.json())
    .then(data => {
        // Captura itens do carrinho atual antes de limpar
        let itensAtuais = [];
        const mats = document.getElementsByName('material_id[]');
        const qtds = document.getElementsByName('quantidade[]');
        const precs = document.getElementsByName('preco_unitario[]');
        for(let i=0;i<mats.length;i++){
            itensAtuais.push({
                material_id: mats[i].value,
                quantidade: parseFloat(qtds[i].value),
                preco_unitario: parseFloat(precs[i].value)
            });
        }
        // Limpa itens atuais sempre que troca cliente
        document.querySelectorAll('input[name="material_id[]"], input[name="quantidade[]"], input[name="preco_unitario[]"]').forEach(e => e.remove());
        atualizarResumo();
        let itensSomados = [];
        if(data.status==='ok'){
            let compra = JSON.parse(data.dados.compra_json);
            if(compra.lista_preco_id){
                const lista = listas_precos.find(l=>l.id==compra.lista_preco_id);
                if(lista) document.getElementById('lista_preco').value = `${lista.id} - ${lista.nome}`;
            }
            // Soma itens do carrinho atual com os do cliente
            let itensCliente = Array.isArray(compra.itens) ? compra.itens : [];
            itensSomados = [...itensCliente];
            itensAtuais.forEach(itemAtual => {
                let idx = itensSomados.findIndex(x => x.material_id == itemAtual.material_id && parseFloat(x.preco_unitario) == parseFloat(itemAtual.preco_unitario));
                if(idx >= 0){
                    itensSomados[idx].quantidade += itemAtual.quantidade;
                }else{
                    itensSomados.push(itemAtual);
                }
            });
        }else{
            itensSomados = [...itensAtuais];
        }
        // Preenche os itens somados
        itensSomados.forEach(item => {
            document.getElementById('material_input').value = item.material_id;
            document.getElementById('quantidade_input').value = item.quantidade;
            document.getElementById('preco_input').value = item.preco_unitario;
            adicionarOuEditarItem();
        });
        atualizarResumo();
        salvarCompraTemporaria();
    });
}

// Carregar compras suspensas na lateral
function carregarComprasSuspensas(){
    fetch('recuperar_compras_suspensas.php')
    .then(r => r.json())
    .then(data => {
        const listaDiv = document.getElementById('listaSuspensosCompra');
        if(!listaDiv) return;
        listaDiv.innerHTML = '';
        if(data.status==='ok' && Array.isArray(data.compras)){
            data.compras.forEach(compra => {
                let html = `<div class="mb-2 border-bottom pb-2 item-suspenso-compra">
                    <strong>Cliente:</strong> <span class="cliente-suspenso-compra">${compra.cliente_nome}</span><br>
                    Total: R$ ${parseFloat(compra.total).toFixed(2)}<br>
                    <button type="button" class="btn btn-sm btn-outline-primary mt-1" onclick="abrirCompraSuspensaPorId(${compra.id})">Abrir</button>
                    <button type="button" class="btn btn-sm btn-outline-danger mt-1" onclick="excluirCompraSuspensa(${compra.id})">Excluir</button>
                </div>`;
                listaDiv.innerHTML += html;
            });
        } else {
            listaDiv.innerHTML = '<em>Nenhuma compra suspensa.</em>';
        }
    });
}

// Função para abrir compra suspensa por id
function abrirCompraSuspensaPorId(compraId){
    // Primeiro, salvar o carrinho atual como suspensa (se tiver itens)
    const matsAtuais = document.getElementsByName('material_id[]');
    if(matsAtuais.length > 0) {
        salvarCompraTemporaria();
    }
    
    fetch('recuperar_compra_suspensa_por_id.php?id='+compraId)
    .then(r => r.json())
    .then(data => {
        if(data.status==='ok'){
            const compra = data.dados;
            
            // Preencher ou limpar cliente
            if(compra.cliente_id && compra.cliente_id > 0){
                // Buscar dados do cliente
                const clienteObj = clientes.find(c => c.id == compra.cliente_id);
                if(clienteObj){
                    document.getElementById('cliente').value = `${clienteObj.id} - ${clienteObj.nome}`;
                } else {
                    document.getElementById('cliente').value = compra.cliente_id;
                }
            } else {
                // Limpar cliente se não tiver
                document.getElementById('cliente').value = '';
            }
            
            // Preencher ou limpar lista de preço
            if(compra.lista_preco_id){
                const lista = listas_precos.find(l=>l.id==compra.lista_preco_id);
                if(lista) {
                    document.getElementById('lista_preco').value = `${lista.id} - ${lista.nome}`;
                } else {
                    document.getElementById('lista_preco').value = compra.lista_preco_id;
                }
            } else {
                document.getElementById('lista_preco').value = '';
            }
            
            // Limpar carrinho atual
            document.querySelectorAll('input[name="material_id[]"], input[name="quantidade[]"], input[name="preco_unitario[]"]').forEach(e => e.remove());
            
            // Adicionar itens da compra suspensa (substituindo o carrinho atual)
            if(compra.itens && Array.isArray(compra.itens)){
                compra.itens.forEach(item => {
                    ['material_id','quantidade','preco_unitario'].forEach(function(field){
                        const input = document.createElement('input');
                        input.type = 'hidden'; input.name = field+'[]';
                        input.value = field==='material_id' ? item.material_id : (field==='quantidade' ? item.quantidade : item.preco_unitario);
                        document.getElementById('formCompra').appendChild(input);
                    });
                });
            }
            
            atualizarResumo();
            
            // Excluir a compra suspensa da lista após abrir
            fetch('excluir_compra_suspensa.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: 'id='+compraId
            })
            .then(r => r.json())
            .then(data => {
                if(data.status==='ok'){
                    carregarComprasSuspensas(); // Atualizar lista
                }
            });
            
            window.scrollTo({behavior:'smooth'});
        } else {
            alert('Erro: '+data.msg);
        }
    });
}

// Função para excluir compra suspensa
function excluirCompraSuspensa(id){
    if(!confirm('Deseja excluir esta compra aberta?')) return;
    fetch('excluir_compra_suspensa.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'id='+id
    })
    .then(r => r.json())
    .then(data => {
        if(data.status==='ok'){
            alert('Compra aberta excluída.');
            carregarComprasSuspensas();
        } else {
            alert('Erro ao excluir.');
        }
    });
}

// Filtro de compras suspensas
const filtroSuspensosCompra = document.getElementById('filtroSuspensosCompra');
if(filtroSuspensosCompra){
    filtroSuspensosCompra.addEventListener('input', function(){
        const termo = this.value.toLowerCase();
        document.querySelectorAll('#listaSuspensosCompra .item-suspenso-compra').forEach(function(item){
            const clienteTexto = item.querySelector('.cliente-suspenso-compra')?.innerText.toLowerCase() || '';
            if(clienteTexto.includes(termo)){
                item.style.display = '';
            } else {
                item.style.display = 'none';
            }
        });
    });
}

// Carregar compras suspensas ao abrir página
carregarComprasSuspensas();

// Carregar compra temporária ao selecionar cliente
const clienteInput = document.getElementById('cliente');
if(clienteInput){
    let ultimoClienteProcessado = null; // Evitar processamento duplicado
    
    clienteInput.addEventListener('blur', function(){
        const valor = this.value.trim();
        const idCliente = parseInt(valor.split(' ')[0]);
        if(idCliente && idCliente !== ultimoClienteProcessado){ 
            ultimoClienteProcessado = idCliente;
            carregarCompraTemporaria(idCliente); 
        }
    });
    
    // Remover o event listener 'input' que pode causar salvamentos duplicados
    // clienteInput.addEventListener('input', function(){
    //     const valor = this.value.trim();
    //     const idCliente = parseInt(valor.split(' ')[0]);
    //     if(idCliente){ carregarCompraTemporaria(idCliente); }
    // });
}

// Adicionar chamada para salvarCompraTemporaria em adicionarOuEditarItem e removerItem
var oldAdicionarOuEditarItem = window.adicionarOuEditarItem;
window.adicionarOuEditarItem = function(){
    oldAdicionarOuEditarItem.apply(this, arguments);
    if(!finalizandoCompra) salvarCompraTemporaria();
};
var oldRemoverItem = window.removerItem;
window.removerItem = function(index){
    oldRemoverItem.apply(this, arguments);
    if(!finalizandoCompra) salvarCompraTemporaria();
};
</script>
         

<script>
function atualizarModalPagamentoCompra() {
        let totalCompra = parseFloat(document.getElementById('total_compra').innerText) || 0;
        let dinheiro = parseFloat(document.getElementById('valor_dinheiro_compra').value) || 0;
        let pix = parseFloat(document.getElementById('valor_pix_compra').value) || 0;
        let cartao = parseFloat(document.getElementById('valor_cartao_compra').value) || 0;
        let abater = parseFloat(document.getElementById('valor_abater_compra').value) || 0;
        let totalPago = dinheiro + pix + cartao + abater;

        // Verificar se há cliente selecionado para mostrar campo abater
        const clienteInput = document.getElementById('cliente');
        const divAbater = document.getElementById('div_abater_compra');
        const clienteId = clienteInput ? parseInt(clienteInput.value.split(' ')[0]) : null;
        
        if(clienteId && !isNaN(clienteId) && clienteId > 0){
            divAbater.style.display = 'block';
        } else {
            divAbater.style.display = 'none';
            document.getElementById('valor_abater_compra').value = '';
            abater = 0;
            totalPago = dinheiro + pix + cartao;
        }

        document.getElementById('modal_total_compra').innerText = totalCompra.toFixed(2);
        document.getElementById('modal_total_pago_compra').innerText = totalPago.toFixed(2);
        
        // Calcular e mostrar quanto falta
        let falta = totalCompra - totalPago;
        if(falta < 0) falta = 0; // Não mostrar valores negativos
        document.getElementById('modal_falta_compra').innerText = falta.toFixed(2);

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

        ['valor_dinheiro_compra', 'valor_pix_compra', 'valor_cartao_compra', 'valor_abater_compra'].forEach(function(id) {
                let el = document.getElementById(id);
                if (el) el.addEventListener('input', atualizarModalPagamentoCompra);
        });

        // Navegação por Enter entre campos de pagamento
        document.getElementById('valor_dinheiro_compra').addEventListener('keydown', function(e) {
                if (e.key === 'Enter') {
                        e.preventDefault();
                        document.getElementById('valor_pix_compra').focus();
                }
        });

        document.getElementById('valor_pix_compra').addEventListener('keydown', function(e) {
                if (e.key === 'Enter') {
                        e.preventDefault();
                        document.getElementById('valor_cartao_compra').focus();
                }
        });

        document.getElementById('valor_cartao_compra').addEventListener('keydown', function(e) {
                if (e.key === 'Enter') {
                        e.preventDefault();
                        const divAbater = document.getElementById('div_abater_compra');
                        if (divAbater && divAbater.style.display !== 'none') {
                                // Se o campo abater está visível, ir para ele
                                document.getElementById('valor_abater_compra').focus();
                        } else {
                                // Senão, verificar se o pagamento está ok e finalizar
                                verificarEFinalizarCompra();
                        }
                }
        });

        document.getElementById('valor_abater_compra').addEventListener('keydown', function(e) {
                if (e.key === 'Enter') {
                        e.preventDefault();
                        verificarEFinalizarCompra();
                }
        });

        // Função para verificar se o pagamento está ok e finalizar a compra
        function verificarEFinalizarCompra() {
                let totalCompra = parseFloat(document.getElementById('total_compra').innerText) || 0;
                let dinheiro = parseFloat(document.getElementById('valor_dinheiro_compra').value) || 0;
                let pix = parseFloat(document.getElementById('valor_pix_compra').value) || 0;
                let cartao = parseFloat(document.getElementById('valor_cartao_compra').value) || 0;
                let abater = parseFloat(document.getElementById('valor_abater_compra').value) || 0;
                let totalPago = dinheiro + pix + cartao + abater;

                if (totalPago >= totalCompra) {
                        // Pagamento está ok, finalizar compra
                        document.getElementById('btnConfirmarPagamentoCompra').click();
                } else {
                        // Pagamento insuficiente, focar no próximo campo ou mostrar alerta
                        let falta = totalCompra - totalPago;
                        alert('Pagamento insuficiente! Falta R$ ' + falta.toFixed(2));
                        document.getElementById('valor_dinheiro_compra').focus();
                }
        }

        document.getElementById('btnConfirmarPagamentoCompra').addEventListener('click', function() {
                // Definir que está finalizando compra para impedir salvamento automático
                finalizandoCompra = true;
                
                // Copia os campos para o form e submete
                let form = document.getElementById('formCompra');
                ['valor_dinheiro_compra', 'valor_pix_compra', 'valor_cartao_compra', 'valor_abater_compra'].forEach(function(id) {
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

        // Event listener adicional para quando o modal é fechado
        const modalEl = document.getElementById('modalPagamentoCompra');
        if (modalEl) {
                modalEl.addEventListener('hidden.bs.modal', function() {
                        // Restaurar estado quando modal é fechado
                        finalizandoCompra = false;
                });
        }
});
</script>


    <?php include __DIR__.'/includes/footer.php'; ?>