<?php

require_once 'conexx/database.php';

// validar id
if(!isset($_GET['id']) or !is_numeric($_GET['id'])){
   header('location: produtos.php?status=error');
   exit;
}

$database = new Database($conn);
$tabelaHTML = $database->getItemById("produtos", $_GET['id']);

if(isset($_POST['excluir'])){
    if ($database->excluirItem("produtos", $_GET['id'])) {
        header('location: produtos.php?status=Produto Excluido!');
        exit;
    } else {
        header('location: produtos.php?status=Erro ao Excluir Produto!');
        exit;
    }
}

include __DIR__.'/includes/header.php';
include __DIR__.'/includes/navbar.php';
include __DIR__.'/includes/exclProd.php';
include __DIR__.'/includes/footer.php';
