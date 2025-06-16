<?php
require_once 'verifica_login.php';
require_once 'conexx/config.php';

if (!$conn) {
    die("Erro na conexão com o banco de dados.");
}

$sqlClientes = "SELECT id, nome FROM clientes";
$resultClientes = $conn->query($sqlClientes);
if (!$resultClientes) {
    die("Erro ao buscar clientes: " . $conn->error);
}
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
        <select id="cliente" name="cliente" class="form-control">
            <option value="">Selecione um cliente</option>
            <?php foreach ($clientes as $cliente): ?>
                <option value="<?= $cliente['id'] ?>"><?= htmlspecialchars($cliente['nome']) ?></option>
            <?php endforeach; ?>
        </select>
    </div>

    <div class="row">
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

<script src="assets/js/pdvcompras.js"></script>

<?php include __DIR__.'/includes/footer.php'; ?>
