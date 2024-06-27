<?php
require_once 'conexx/config.php';
require_once 'conexx/DatabaseVenda.php';

$venda = new DatabaseVenda($conn);

include __DIR__.'/includes/header.php';
include __DIR__.'/includes/navbar.php';
?>

    <div class="container mt-5">
        <h1 class="text-center">Lista de Vendas</h1>
        <?php echo $venda->listarVendas('vendas'); ?>
        <a href="FormularioVendas.php" class="btn btn-primary mt-3">Adicionar Venda</a>
    </div>

<?php include __DIR__.'/includes/footer.php'; ?>