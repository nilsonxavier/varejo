<?php

require_once 'config.php';
require_once 'database.php';


if (isset($_POST['produto_nome'], $_POST['operador_nome'], $_POST['quantidade'], $_POST['grao'])) {
    $produto_nome = $_POST['produto_nome'];
    $operador_nome = $_POST['operador_nome'];
    $quantidade = $_POST['quantidade'];
    $grao = $_POST['grao'];


    
    //echo "<pre>"; print_r($_POST); echo "</pre>";  exit;
    
    // echo "<pre>"; print_r($tabelaHTML); echo "</pre>";  exit;
    
    
    $sql = "INSERT INTO producao_bobina (produto, qtd, grao, funcionario) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssss", $produto_nome, $quantidade, $grao, $operador_nome);


    // //pega a quantidade e atualizar 
    // $database = new Database($conn);
    // $tabelaHTML = $database->getCampoById("produtos", $produto_id, "qtd");
    // $atualizado = $tabelaHTML + $quantidade;
    // $novosDados = array('qtd' => $atualizado);
    // $database->editarItem("produtos", $produto_id, $novosDados);


    
    if ($stmt->execute()) {
        $mens = '<div class="alert alert-success text-center mt-2">Produção inserida com sucesso</div>';
        // Redireciona para outra_pagina.php e envia a mensagem como parâmetro na URL
        header("Location: ../cadProducaoBobina.php?mensagem=" . urlencode($mens));
        exit; // É importante sair do script após o redirecionamento

    
    } else {
        $mens = '<div class="alert alert-danger text-center mt-2">erro ao inserir: </div>' . $stmt->error;
        header("Location: ../cadProducaoBobina.php?mensagem=" . urlencode($mens));
        exit;
    }

    $stmt->close();

}


try {
    // Conectando ao banco de dados usando PDO
    $pdo = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Consultando os dados
    $stmt = $pdo->prepare("SELECT id, nome FROM bobina");
    $stmt->execute();

    // Obtendo o resultado
    $usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $stmt2 = $pdo->prepare("SELECT nome FROM funcionario WHERE funcao = 'balao';");
    $stmt2->execute();
    // Obtendo o resultado
    $funcionario = $stmt2->fetchAll(PDO::FETCH_ASSOC);

    
} catch (PDOException $e) {
    echo "Erro: " . $e->getMessage();
    exit;
}
$conn->close();


?>