<?php
require_once 'conexx/config.php';
require_once 'conexx/DatabaseVenda.php';

$venda = new DatabaseVenda($conn);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $idCliente = $_POST['id_cliente'];
    $dataVenda = date('Y-m-d H:i:s');
    $itens = $_POST['itens'];
    
    $valorTotal = 0;
    foreach ($itens as $item) {
        $valorTotal += $item['quantidade'] * $item['preco_unitario'];
    }
    
    $itens = json_encode($itens);
    $venda->inserirVenda('vendas', $idCliente, $dataVenda, $valorTotal, $itens);
}

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

    <div class="container mt-5">
        <h1 class="text-center">PDV Vendas</h1>
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
            <div id="itens" class="mb-4">
                <div class="item-group">
                    <div class="form-group">
                        <label>Produto:</label>
                        <select name="itens[][produto_id]" class="form-control">
                            <option value="">Selecione um produto</option>
                            <?php foreach ($produtos as $produto): ?>
                                <option value="<?php echo $produto['id']; ?>"><?php echo $produto['nome']; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Quantidade:</label>
                        <input type="number" name="itens[][quantidade]" min="1" class="form-control" oninput="calcularValorTotal()">
                    </div>
                    <div class="form-group">
                        <label>Preço Unitário:</label>
                        <input type="number" step="0.01" name="itens[][preco_unitario]" min="0" class="form-control" oninput="calcularValorTotal()">
                    </div>
                    <button type="button" class="btn btn-danger mb-3" onclick="removerItem(this)">Remover</button>
                </div>
            </div>
            <button type="button" class="btn btn-secondary mb-3" onclick="adicionarItem()">Adicionar Item</button>
            <div class="form-group">
                <label>Valor Total:</label>
                <input type="text" id="valor_total" name="valor_total" readonly class="form-control">
            </div>
            <button type="submit" class="btn btn-primary">Inserir Venda</button>
        </form>
    </div>

    <script>
        function adicionarItem() {
            const itemGroup = document.createElement('div');
            itemGroup.className = 'item-group';
            itemGroup.innerHTML = `
                <div class="form-group">
                    <label>Produto:</label>
                    <select name="itens[][produto_id]" class="form-control">
                        <option value="">Selecione um produto</option>
                        <?php foreach ($produtos as $produto): ?>
                            <option value="<?php echo $produto['id']; ?>"><?php echo $produto['nome']; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Quantidade:</label>
                    <input type="number" name="itens[][quantidade]" min="1" class="form-control" oninput="calcularValorTotal()">
                </div>
                <div class="form-group">
                    <label>Preço Unitário:</label>
                    <input type="number" step="0.01" name="itens[][preco_unitario]" min="0" class="form-control" oninput="calcularValorTotal()">
                </div>
                <button type="button" class="btn btn-danger mb-3" onclick="removerItem(this)">Remover</button>
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

<?php include __DIR__.'/includes/footer.php'; ?>