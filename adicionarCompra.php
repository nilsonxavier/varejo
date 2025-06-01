<?php
require_once 'conexx/config.php';
require_once 'conexx/DatabaseCompra.php'; // voce precisa criar essa classe semelhante ao DatabaseVenda

$compra = new DatabaseCompras($conn);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $idCliente = $_POST['id_cliente'];
    $dataCompra = date('Y-m-d H:i:s');
    $itens = $_POST['itens'];

    $valorTotal = 0;
    foreach ($itens as $item) {
        $valorTotal += $item['quantidade'] * $item['preco_unitario'];
    }

    $itens = json_encode($itens);
    $compra->inserirCompra('compras', $idCliente, $dataCompra, $valorTotal, $itens);
}

$sqlClientes = "SELECT id, nome FROM clientes";
$resultClientes = $conn->query($sqlClientes);
$clientes = [];
while ($row = $resultClientes->fetch_assoc()) {
    $clientes[] = $row;
}

include __DIR__.'/includes/header.php';
include __DIR__.'/includes/navbar.php';
?>

<div class="container mt-5">
    <h1 class="text-center">Compras de Mercadoria</h1>
    <form method="post">
        <div class="form-group">
            <label for="id_cliente">Cliente:</label>
            <select name="id_cliente" id="id_cliente" class="form-control">
                <option value="">Selecione um cliente</option>
                <?php foreach ($clientes as $cliente): ?>
                    <option value="<?php echo $cliente['id']; ?>"><?php echo $cliente['nome']; ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group">
            <label for="busca_produto">Buscar Produto:</label>
            <input type="text" id="busca_produto" class="form-control" placeholder="Digite nome ou código">
        </div>

        <div id="itens" class="mb-4"></div>

        <div class="form-group">
            <label>Valor Total:</label>
            <input type="text" id="valor_total" name="valor_total" readonly class="form-control">
        </div>

        <button type="submit" class="btn btn-primary">Inserir Compra</button>
    </form>
</div>

<script>
    let itens = [];

    document.getElementById('busca_produto').addEventListener('input', function () {
        const termo = this.value;
        const clienteId = document.getElementById('id_cliente').value;
        if (termo.length >= 2 && clienteId) {
            fetch(`buscar_produto.php?termo=${termo}&id_cliente=${clienteId}`)
                .then(response => response.json())
                .then(produtos => {
                    if (produtos.length) {
                        const produto = produtos[0]; // usar o primeiro como exemplo
                        adicionarItem(produto);
                        this.value = '';
                    }
                });
        }
    });

    function adicionarItem(produto) {
        const itemIndex = itens.length;
        itens.push(produto);

        const itemGroup = document.createElement('div');
        itemGroup.className = 'item-group';
        itemGroup.innerHTML = `
            <hr>
            <input type="hidden" name="itens[${itemIndex}][produto_id]" value="${produto.id}">
            <div class="form-group">
                <label>Produto: ${produto.nome}</label>
            </div>
            <div class="form-group">
                <label>Quantidade:</label>
                <input type="number" name="itens[${itemIndex}][quantidade]" min="1" class="form-control quantidade" data-index="${itemIndex}" value="1">
            </div>
            <div class="form-group">
                <label>Preço Unitário:</label>
                <input type="number" step="0.01" name="itens[${itemIndex}][preco_unitario]" class="form-control preco" data-index="${itemIndex}" value="${produto.preco}">
            </div>
            <button type="button" class="btn btn-danger mb-3" onclick="removerItem(this, ${itemIndex})">Remover</button>
        `;
        document.getElementById('itens').appendChild(itemGroup);
        calcularValorTotal();

        itemGroup.querySelector('.quantidade').addEventListener('input', calcularValorTotal);
        itemGroup.querySelector('.preco').addEventListener('input', calcularValorTotal);
    }

    function removerItem(botao, index) {
        botao.parentElement.remove();
        delete itens[index];
        calcularValorTotal();
    }

    function calcularValorTotal() {
        let total = 0;
        document.querySelectorAll('.item-group').forEach((group, i) => {
            const qtd = parseFloat(group.querySelector('.quantidade').value);
            const preco = parseFloat(group.querySelector('.preco').value);
            if (!isNaN(qtd) && !isNaN(preco)) {
                total += qtd * preco;
            }
        });
        document.getElementById('valor_total').value = total.toFixed(2);
    }
</script>

<?php include __DIR__.'/includes/footer.php'; ?>
