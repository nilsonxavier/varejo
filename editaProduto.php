<?php

require_once 'verifica_login.php';
// ... resto da página protegida ...

require_once 'conexx/database.php';

// validar id
if(!isset($_GET['id']) or !is_numeric($_GET['id'])){
   header('location: produtos.php?status=error');
   exit;
}

$database = new Database($conn);
$tabelaHTML = $database->getItemById("produtos", $_GET['id']);

 if(isset($_POST['nome'],$_POST['qtd'])){
   $novosDados = array('nome' => $_POST['nome'], 'qtd' => $_POST['qtd']);
   if ($database->editarItem("produtos", $_GET['id'], $novosDados)) {
      header('location: produtos.php?status=Produto '.$_POST['nome'].' editado com sucesso');
      exit;
   } else {
      header('location: produtos.php?status=Erro ao Editar '.$_POST['nome'].' tente novamente');
      exit;
   }
}
 
include __DIR__.'/includes/header.php';
include __DIR__.'/includes/navbar.php';
include __DIR__.'/includes/formularioEditarProduto.php';

include __DIR__.'/includes/footer.php';

