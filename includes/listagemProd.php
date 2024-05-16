<?php
require_once './conexx/database.php';

$mensagem = '';
if(isset($_GET['status'])){
    $mensagem = '<div class="alert alert-dark text-center mt-2"><strong>'.$_GET['status'].'</strong></div>';
}

$database = new Database($conn);
$tabelaHTML = $database->listarDados("produtos");

echo $mensagem;
echo $tabelaHTML;
?>