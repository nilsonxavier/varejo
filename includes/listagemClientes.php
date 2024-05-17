<?php
require_once './conexx/databaseCliente.php';

$mensagem = '';
if(isset($_GET['status'])){
    $mensagem = '<div class="alert alert-dark text-center mt-2"><strong>'.$_GET['status'].'</strong></div>';
}

$database = new DatabaseCliente($conn);
$tabelaHTML = $database->listarCliente("clientes");

echo $mensagem;
echo $tabelaHTML;
?>