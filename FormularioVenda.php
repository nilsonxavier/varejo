<?php
//require_once 'config.php';
require_once 'conexx/DatabaseVenda.php';

$venda = new DatabaseVenda($conn);


// Consulta para obter os produtos cadastrados
$sqlProdutos = "SELECT id, nome FROM produtos";
$resultProdutos = $conn->query($sqlProdutos);
$produtos = [];
while ($row = $resultProdutos->fetch_assoc()) {
    $produtos[] = $row;
}

// Consulta para obter os clientes cadastrados
$sqlClientes = "SELECT id, nome FROM clientes";
$resultClientes = $conn->query($sqlClientes);
$clientes = [];
while ($row = $resultClientes->fetch_assoc()) {
    $clientes[] = $row;
}

include __DIR__.'/includes/header.php';
include __DIR__.'/includes/navbar.php';
?>

<script>
    function adicionarItem() {
        const itemGroup = document.createElement('div');
        itemGroup.className = 'item-group';
        itemGroup.innerHTML = `
            <div class="item-group d-flex flex-row align-items-center">
            <div class="form-group mr-3">
                <label>Produto: 
                <select name="itens[][produto_id]" class="form-control">
                    <option value="">Selecione um produto</option>
                    <?php foreach ($produtos as $produto): ?>
                        <option value="<?php echo $produto['id']; ?>"><?php echo $produto['nome']; ?></option>
                    <?php endforeach; ?>
                </select>
            </label></div>
            <div class="form-group mr-3"><label>Quantidade: <input type="number" name="itens[][quantidade]" min="1" oninput="calcularValorTotal()" class="form-control"></label></div>
            <div class="form-group mr-3"><label>Preço KG: <input type="number" step="0.01" name="itens[][preco_unitario]" min="0" oninput="calcularValorTotal()" class="form-control"></label></div>
            <button type="button" onclick="removerItem(this)" class="btn btn-danger">Remover</button>
        </div>
            `;
        document.getElementById('itens').appendChild(itemGroup);
    }

    function removerItem(button) {
        button.parentElement.remove();
        calcularValorTotal();
    }

    function calcularValorTotal() {
        let valorTotal = 0;
        document.querySelectorAll('.item-group').forEach(function(itemGroup) {
            const quantidade = itemGroup.querySelector('input[name="itens[][quantidade]"]').value;
            const precoUnitario = itemGroup.querySelector('input[name="itens[][preco_unitario]"]').value;
            if (quantidade && precoUnitario) {
                valorTotal += quantidade * precoUnitario;
            }
        });
        document.getElementById('valor_total').value = valorTotal.toFixed(2);
    }
</script>

<blockquote class="blockquote text-center mt-3">
  <p class="mb-0">PDV Vendas</p>
</blockquote>

<form method="post">
    <div class="form-group">
        <label for="id_cliente">Cliente:</label>
        <select class="form-control" id="id_cliente" name="id_cliente">
            <option value="">Selecione um cliente</option>
            <?php foreach ($clientes as $cliente): ?>
                <option value="<?php echo $cliente['id']; ?>"><?php echo $cliente['nome']; ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div id="itens">
    <div class="item-group d-flex flex-row align-items-center">
        <div class="form-group mr-3">
            <label for="produto">Produto:</label>
            <select class="form-control" id="produto" name="itens[][produto_id]">
                <option value="">Selecione um produto</option>
                <?php foreach ($produtos as $produto): ?>
                    <option value="<?php echo $produto['id']; ?>"><?php echo $produto['nome']; ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-group mr-3">
            <label for="quantidade">Quantidade:</label>
            <input type="number" class="form-control" id="quantidade" name="itens[][quantidade]" min="1" oninput="calcularValorTotal()">
        </div>
        <div class="form-group mr-3">
            <label for="preco_unitario">Preço KG:</label>
            <input type="number" step="0.01" class="form-control" id="preco_unitario" name="itens[][preco_unitario]" min="0" oninput="calcularValorTotal()">
        </div>
        <button type="button" class="btn btn-danger" onclick="removerItem(this)">Remover</button>
    </div>
</div>




    <button type="button" class="btn btn-primary" onclick="adicionarItem()">Adicionar Item</button><br>
    <div class="form-group">
        <label for="valor_total">Valor Total:</label>
        <input type="text" class="form-control" id="valor_total" name="valor_total" readonly>
    </div>
    <button type="submit" class="btn btn-success">Inserir Venda</button>
</form>
<?php include __DIR__.'/includes/footer.php'; ?>
