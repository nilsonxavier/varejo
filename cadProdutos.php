<?php

require_once 'verifica_login.php';
// ... resto da página protegida ...

// Inclua a classe Database
//require_once 'conexx/database.php';
// Inclua a classe Database
require_once 'conexx/database.php';

 include __DIR__.'/includes/header.php';
 include __DIR__.'/includes/navbar.php';
 include __DIR__.'/includes/formularioProduto.php';

 if(isset($_POST['nome'],$_POST['qtd'])){
    //Inserir dados na tabela
    $database = new Database($conn);
    $database->inserirDados("produtos", $_POST['nome'], $_POST['qtd']);
 }

//echo "<pre>"; print_r($_POST); echo "</pre>"; exit;
// Inserir dados na tabela
// $database->inserirDados("produtos", "sacola-G", 55);


 include __DIR__.'/includes/footer.php';

