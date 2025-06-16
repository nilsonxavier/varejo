<?php
include __DIR__.'/includes/header.php';
include __DIR__.'/includes/navbar.php';
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
</head>

<body>
    <div class="container py-4">
        <div class="row g-4">
            <!-- Coluna do Formulário -->
            <div class="col-md-6">
                <div class="section-card">
                    <h4><i class="bi bi-cart-plus"></i> PDV de Compra</h4>
                    <form id="form-compra">

                        <!-- Itens -->
                        <h5>Itens:</h5>
                        <div id="itens">
                            <div class="row g-2 mb-2">
                                <div class="col-6">
                                    <select class="form-select" name="material_id[]">
                                        <option value="1">Material A</option>
                                        <option value="2">Material B</option>
                                    </select>
                                </div>
                                <div class="col-4">
                                    <input type="number" class="form-control" name="quantidade[]" placeholder="Qtd">
                                </div>
                                <div class="col-2">
                                    <button type="button" class="btn btn-outline-danger" onclick="removerItem(this)"><i
                                            class="bi bi-trash"></i></button>
                                </div>
                            </div>
                        </div>
                        <button type="button" class="btn btn-outline-secondary" onclick="adicionarItem()">
                            <i class="bi bi-plus-circle"></i> Adicionar Item
                        </button>
                    </form>
                </div>
            </div>

            <!-- Coluna do Carrinho -->
            <div class="col-md-6">
                <div class="section-card">
                    <h4><i class="bi bi-basket"></i> Itens no Carrinho</h4>
                    <div class="mb-2">
                        <strong>Cliente Selecionado: </strong><span id="cliente-selecionado">Nenhum</span>
                    </div>
                    <div class="mb-2">
                        <strong>Tabela Selecionada: </strong><span id="tabela-selecionada">Nenhum</span>
                    </div>
                    <ul class="list-group" id="lista-carrinho"></ul>
                    <div class="mt-3">
                        <strong>Total: R$ <span id="total-carrinho">0.00</span></strong>
                    </div>
                    <button onclick="limparCarrinho()" class="btn btn-sm btn-outline-danger mt-2">Limpar
                        Carrinho</button>
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

    <!-- Ícones fixos no rodapé -->
    <div class="footer-icons">
        <div class="icon"><i class="bi bi-house-door"></i><small>Início</small></div>
        <div class="icon"><i class="bi bi-bag-plus"></i><small>Nova Compra</small></div>
        <div class="icon"><i class="bi bi-clock-history"></i><small>Histórico</small></div>
        <div class="icon" onclick="abrirModalCliente()"><i class="bi bi-person-plus"></i><small>Cliente+</small></div>
    </div>

    <script>
    const caixaAberto = false;
    const precos = {
        1: {
            1: 10.00,
            2: 20.00
        }
    };

    let clienteSelecionado = null;

    function abrirModalCliente() {
        const modal = new bootstrap.Modal(document.getElementById('modalSelecionarCliente'));
        document.getElementById('busca-cliente').value = '';
        document.getElementById('resultado-clientes').innerHTML = '';
        modal.show();
    }

    function adicionarItem() {
        const container = document.getElementById('itens');
        const modelo = container.children[0].cloneNode(true);
        modelo.querySelectorAll('input').forEach(input => input.value = '');
        container.appendChild(modelo);
    }

    function removerItem(btn) {
        const row = btn.closest('.row');
        const container = document.getElementById('itens');
        if (container.children.length > 1) row.remove();
        salvarCarrinho();
    }

    function salvarCarrinho() {
        const lista_id = document.getElementById('lista_preco').value;
        const itens = [];
        document.querySelectorAll('#itens .row').forEach(row => {
            const sel = row.querySelector('select');
            const id = sel.value;
            const nome = sel.options[sel.selectedIndex].text;
            const qtd = parseFloat(row.querySelector('input').value) || 0;
            itens.push({
                material_id: id,
                material_nome: nome,
                quantidade: qtd
            });
        });
        localStorage.setItem('carrinho_compra', JSON.stringify(itens));
    }

    function carregarCarrinho() {
        const lista = document.getElementById('lista-carrinho');
        const lista_id = document.getElementById('lista_preco').value;
        const dados = JSON.parse(localStorage.getItem('carrinho_compra') || '[]');
        let total = 0;
        lista.innerHTML = '';
        dados.forEach(item => {
            const preco = precos[lista_id]?. [item.material_id] || 0;
            total += preco * item.quantidade;
            const li = document.createElement('li');
            li.className = 'list-group-item d-flex justify-content-between';
            li.innerHTML =
                `<span>${item.material_nome} (${item.quantidade})</span><strong>R$ ${(preco * item.quantidade).toFixed(2)}</strong>`;
            lista.appendChild(li);
        });
        document.getElementById('total-carrinho').textContent = total.toFixed(2);
    }

    function limparCarrinho() {
        localStorage.removeItem('carrinho_compra');
        carregarCarrinho();
    }

    document.addEventListener('input', () => {
        salvarCarrinho();
        carregarCarrinho();
    });

    document.addEventListener('DOMContentLoaded', () => {
        carregarCarrinho();
    });

    let abortController = null;

document.getElementById('busca-cliente').addEventListener('input', function() {
    const termo = this.value.trim();
    const lista = document.getElementById('resultado-clientes');
    lista.innerHTML = '';

    if (abortController) {
        // Aborta requisição anterior se existir
        abortController.abort();
    }
    abortController = new AbortController();
    const signal = abortController.signal;

    if (termo.length < 2) return;

    fetch('api/clientes.php?q=' + encodeURIComponent(termo), { signal })
        .then(res => res.json())
        .then(data => {
            // Verifica se o termo atual ainda é o mesmo
            if (termo !== document.getElementById('busca-cliente').value.trim()) {
                // Se mudou, ignora essa resposta
                return;
            }

            if (data.length === 0) {
                lista.innerHTML = `
                    <li class="list-group-item text-danger">Nenhum cliente encontrado</li>
                    <li class="list-group-item text-center">
                        <a href="cadastro_clientes.php" class="btn btn-sm btn-primary">
                            <i class="bi bi-person-plus-fill"></i> Cadastrar Novo Cliente
                        </a>
                    </li>`;
                return;
            }

            data.forEach(cliente => {
                const li = document.createElement('li');
                li.className = 'list-group-item list-group-item-action';
                li.setAttribute('tabindex', '0');
                li.textContent = cliente.nome;
                const listaPrecos = cliente.listas_precos_nome || 'Nenhuma lista';

                li.addEventListener('click', () => {
                    clienteSelecionado = cliente;
                    document.getElementById('cliente-selecionado').textContent = cliente.nome;
                    document.getElementById('tabela-selecionada').textContent = listaPrecos;
                    bootstrap.Modal.getInstance(document.getElementById('modalSelecionarCliente')).hide();
                });

                li.addEventListener('keydown', (e) => {
                    if (e.key === 'Enter') {
                        li.click();
                    }
                });

                lista.appendChild(li);
            });

            // Foca no primeiro item
            const primeiroItem = document.querySelector('#resultado-clientes .list-group-item-action');
            if (primeiroItem) primeiroItem.focus();
        })
        .catch(err => {
            if (err.name === 'AbortError') {
                // fetch abortado, não precisa fazer nada
                return;
            }
            console.error('Erro na busca:', err);
        });
});

    </script>
    

    <?php include __DIR__.'/includes/footer.php'; ?>
