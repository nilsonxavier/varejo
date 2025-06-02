<?php
require_once 'conexx/config.php';

// Obter clientes
$sqlClientes = "SELECT id, nome FROM clientes";
$resultClientes = $conn->query($sqlClientes);
$clientes = [];
while ($row = $resultClientes->fetch_assoc()) {
    $clientes[] = $row;
}

include __DIR__.'/includes/header.php';
include __DIR__.'/includes/navbar.php';
?>

<div class="container mt-4">
    <h2 class="text-center mb-4">Compras de Mercadoria</h2>

    <div class="form-group mb-4">
        <label for="cliente">Cliente:</label>
        <select id="cliente" class="form-control">
            <option value="">Selecione um cliente</option>
            <?php foreach ($clientes as $cliente): ?>
                <option value="<?= $cliente['id'] ?>"><?= $cliente['nome'] ?></option>
            <?php endforeach; ?>
        </select>
    </div>

    <div class="row">
        <!-- Lado esquerdo: busca e detalhe -->
        <div class="col-lg-6 col-md-12 mb-4">
            <div class="form-group">
                <label for="busca_produto">Buscar Produto (nome ou código):</label>
                <input type="text" id="busca_produto" class="form-control" placeholder="Digite nome ou código" autocomplete="off" autofocus>
                <div id="resultado_busca" class="list-group mt-2"></div>
            </div>

            <div id="form_detalhes" class="mt-4" style="display: none;">
                <h5>Produto Selecionado: <span id="produto_selecionado"></span></h5>
                <input type="hidden" id="produto_id">

                <div class="form-group mt-3">
                    <label for="quantidade">Quantidade (kg):</label>
                    <input type="number" id="quantidade" class="form-control" min="0.01" step="0.01" placeholder="Informe a quantidade">
                </div>

                <div class="form-group mt-3">
                    <label for="preco_unitario">Preço Unitário (R$):</label>
                    <input type="number" id="preco_unitario" class="form-control" step="0.01" placeholder="Informe o preço unitário">
                </div>
            </div>
        </div>

        <!-- Lado direito: carrinho -->
        <div class="col-lg-6 col-md-12">
            <h4>Carrinho</h4>
            <table class="table table-bordered" id="tabela_carrinho">
                <thead>
                    <tr>
                        <th>Produto</th>
                        <th>Quantidade (kg)</th>
                        <th>Preço Unitário</th>
                        <th>Subtotal</th>
                        <th>Ação</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
            <h5>Total: R$ <span id="total_compra">0.00</span></h5>

            <button class="btn btn-primary mt-3" onclick="finalizarCompra()">Finalizar Compra</button>
        </div>
    </div>
</div>

<script>
let carrinho = [];
let produtosCache = [];
let indiceSelecionado = 0;

const inputBusca = document.getElementById('busca_produto');
const resultadoBusca = document.getElementById('resultado_busca');
const formDetalhes = document.getElementById('form_detalhes');
const produtoSelecionadoSpan = document.getElementById('produto_selecionado');
const produtoIdInput = document.getElementById('produto_id');
const quantidadeInput = document.getElementById('quantidade');
const precoInput = document.getElementById('preco_unitario');

inputBusca.addEventListener('input', () => {
    const termo = inputBusca.value.trim();
    const clienteId = document.getElementById('cliente').value;

    if (termo.length < 2) {
        resultadoBusca.innerHTML = '';
        produtosCache = [];
        return;
    }

    fetch(`ajax/buscar_produto.php?termo=${encodeURIComponent(termo)}&cliente_id=${clienteId}`)
        .then(res => res.json())
        .then(produtos => {
            produtosCache = produtos;
            indiceSelecionado = 0;
            mostrarResultados(produtos);
        });
});

inputBusca.addEventListener('keydown', (e) => {
    if (resultadoBusca.children.length === 0) return;

    if (e.key === 'ArrowDown') {
        e.preventDefault();
        indiceSelecionado = (indiceSelecionado + 1) % produtosCache.length;
        atualizarSelecao();
    } else if (e.key === 'ArrowUp') {
        e.preventDefault();
        indiceSelecionado = (indiceSelecionado - 1 + produtosCache.length) % produtosCache.length;
        atualizarSelecao();
    } else if (e.key === 'Enter') {
        e.preventDefault();
        // Se o termo for número e bate com o ID de algum produto, adiciona direto
        const termo = inputBusca.value.trim();
        const produtoIdDigitado = parseInt(termo, 10);
        if (!isNaN(produtoIdDigitado)) {
            const produtoAchado = produtosCache.find(p => p.id === produtoIdDigitado);
            if (produtoAchado) {
                selecionarProduto(produtoAchado.id, produtoAchado.nome, produtoAchado.preco);
                quantidadeInput.focus();
                resultadoBusca.innerHTML = '';
                return;
            }
        }
        // Senão seleciona o item destacado
        if (produtosCache.length > 0) {
            const p = produtosCache[indiceSelecionado];
            selecionarProduto(p.id, p.nome, p.preco);
            resultadoBusca.innerHTML = '';
            quantidadeInput.focus();
        }
    }
});

// Quando estiver no campo quantidade, ao pressionar Enter, foca no preço e valida se tem valor
quantidadeInput.addEventListener('keydown', e => {
    if (e.key === 'Enter') {
        e.preventDefault();
        const qtd = parseFloat(quantidadeInput.value);
        if (!qtd || qtd <= 0) {
            alert('Por favor, informe uma quantidade válida.');
            quantidadeInput.focus();
            return;
        }
        precoInput.focus();
    }
});

// Quando estiver no campo preço, ao pressionar Enter, adiciona o produto no carrinho e reseta o formulário
precoInput.addEventListener('keydown', e => {
    if (e.key === 'Enter') {
        e.preventDefault();
        const preco = parseFloat(precoInput.value);
        if (!preco || preco <= 0) {
            alert('Por favor, informe um preço válido.');
            precoInput.focus();
            return;
        }
        adicionarAoCarrinho();
        inputBusca.focus();
    }
});

function mostrarResultados(produtos) {
    if (produtos.length === 0) {
        resultadoBusca.innerHTML = '<div class="list-group-item">Nenhum produto encontrado</div>';
        return;
    }
    let html = '';
    produtos.forEach((prod, i) => {
        html += `<a href="#" class="list-group-item list-group-item-action${i === 0 ? ' active' : ''}" data-index="${i}" onclick="selecionarProduto(${prod.id}, '${prod.nome}', ${prod.preco}); return false;">${prod.nome} (ID: ${prod.id})</a>`;
    });
    resultadoBusca.innerHTML = html;
}

function atualizarSelecao() {
    const itens = resultadoBusca.querySelectorAll('a.list-group-item');
    itens.forEach((item, i) => {
        if (i === indiceSelecionado) {
            item.classList.add('active');
            item.scrollIntoView({ block: "nearest" });
        } else {
            item.classList.remove('active');
        }
    });
}

function selecionarProduto(id, nome, preco) {
    document.getElementById('produto_id').value = id;
    document.getElementById('preco_unitario').value = preco.toFixed(2);
    document.getElementById('resultado_busca').innerHTML = '';
    document.getElementById('form_detalhes').style.display = 'block';

    // Atualiza também o texto do produto selecionado, se estiver visível
    document.getElementById('produto_selecionado_texto').textContent = nome;

    document.getElementById('quantidade').focus();
}



function adicionarAoCarrinho() {
    let id = parseInt(produtoIdInput.value);
    let nome = produtoSelecionadoSpan.textContent;
    let qtd = parseFloat(quantidadeInput.value);
    let preco = parseFloat(precoInput.value);

    if (isNaN(qtd) || qtd <= 0) {
        alert('Digite uma quantidade válida.');
        quantidadeInput.focus();
        return;
    }

    if (isNaN(preco) || preco <= 0) {
        alert('Digite um preço válido.');
        precoInput.focus();
        return;
    }

    let subtotal = qtd * preco;

    carrinho.push({ id, nome, qtd, preco, subtotal });
    atualizarTabelaCarrinho();

    resetarFormulario();
}

function resetarFormulario() {
    produtoIdInput.value = '';
    produtoSelecionadoSpan.textContent = '';
    quantidadeInput.value = '';
    precoInput.value = '';
    formDetalhes.style.display = 'none';
    inputBusca.value = '';
    produtosCache = [];
    indiceSelecionado = 0;
    resultadoBusca.innerHTML = '';
}

function atualizarTabelaCarrinho() {
    let tbody = document.querySelector('#tabela_carrinho tbody');
    tbody.innerHTML = '';
    let total = 0;

    carrinho.forEach((item, index) => {
        total += item.subtotal;
        tbody.innerHTML += `
            <tr>
                <td>${item.nome}</td>
                <td>${item.qtd.toFixed(2)}</td>
                <td>R$ ${item.preco.toFixed(2)}</td>
                <td>R$ ${item.subtotal.toFixed(2)}</td>
                <td><button class="btn btn-danger btn-sm" onclick="removerDoCarrinho(${index})">X</button></td>
            </tr>
        `;
    });

    document.getElementById('total_compra').textContent = total.toFixed(2);
}

function removerDoCarrinho(index) {
    carrinho.splice(index, 1);
    atualizarTabelaCarrinho();
}

function finalizarCompra() {
    if (carrinho.length === 0) {
        alert('O carrinho está vazio.');
        return;
    }
    alert('Compra finalizada! (implemente a ação desejada)');
    // Aqui você pode implementar o envio para o backend etc.
}
</script>

<?php include __DIR__.'/includes/footer.php'; ?>
