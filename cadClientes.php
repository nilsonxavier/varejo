<?php

// Inclua a classe Database
//require_once 'conexx/database.php';
// Inclua a classe Database
require_once 'conexx/databaseCliente.php';

 include __DIR__.'/includes/header.php';
 include __DIR__.'/includes/navbar.php';
 
 if(isset($_POST['nome'], $_POST['rua'])) {
   // Conexão com o banco de dados
   $database = new DatabaseCliente($conn);
   $database->inserirDados("clientes", $_POST['nome'], $_POST['rua'], $_POST['ruaNumero'], $_POST['cep'], $_POST['bairro'], $_POST['cidade'], $_POST['complemento'], $_POST['estado'], $_POST['telefone1'], $_POST['telefone2'], $_POST['vendedor'], $_POST['cpf']);
   } else {
      echo "Por favor, preencha todos os campos obrigatórios.";
   }
 include __DIR__.'/includes/formularioClientes.php';

 

// verifica dados do POST
//echo "<pre>"; print_r($_POST); echo "</pre>"; exit;
// Inserir dados na tabela
// $database->inserirDados("produtos", "sacola-G", 55);


 include __DIR__.'/includes/footer.php';