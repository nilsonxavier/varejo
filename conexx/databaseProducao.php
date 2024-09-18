<?php

require_once 'config.php';
require_once 'database.php';

if (isset($_POST['data_inicio'], $_POST['produto_id'], $_POST['quantidade'], $_POST['material_utilizado'], $_POST['data_fim'])) {
    $data_inicio = $_POST['data_inicio'];
    $produto_id = $_POST['produto_id'];
    $quantidade = $_POST['quantidade'];
    $material_utilizado = $_POST['material_utilizado'];
    $data_fim = $_POST['data_fim'];
    $status = "Finalizado"; // status inicial

        // Criando objetos DateTime
    $datetimeInicio = new DateTime($data_inicio);
    $datetimeFim = new DateTime($data_fim);
    
    // ok
    // Calculando a diferença em horas
    
    $diferencaSegundos = $datetimeFim->getTimestamp() - $datetimeInicio->getTimestamp();
    $totalHoras = round($diferencaSegundos / (60 * 60), 2);
    
    //echo $totalHoras;

    //echo "<pre>"; print_r($_POST); echo "</pre>";  exit;
    
    // echo "<pre>"; print_r($tabelaHTML); echo "</pre>";  exit;
    
    
    $sql = "INSERT INTO ordens_producao (data_inicio, data_fim, produto_id, quantidade, material_utilizado, tempo_producao, status) VALUES (?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssssss", $data_inicio, $data_fim, $produto_id, $quantidade, $material_utilizado, $totalHoras, $status);


    //pega a quantidade e atualizar 
    $database = new Database($conn);
    $tabelaHTML = $database->getCampoById("produtos", $produto_id, "qtd");
    $atualizado = $tabelaHTML + $quantidade;
    $novosDados = array('qtd' => $atualizado);
    $database->editarItem("produtos", $produto_id, $novosDados);


    
    if ($stmt->execute()) {
        $mens = '<div class="alert alert-success text-center mt-2">Produção inserida com sucesso</div>';
        // Redireciona para outra_pagina.php e envia a mensagem como parâmetro na URL
        header("Location: ../cadProducaoSacola.php?mensagem=" . urlencode($mens));
        exit; // É importante sair do script após o redirecionamento

    
    } else {
        $mens = '<div class="alert alert-danger text-center mt-2">erro ao inserir: </div>' . $stmt->error;
        header("Location: ../cadProducaoSacola.php?mensagem=" . urlencode($mens));
        exit;
    }

    $stmt->close();

}


try {
    // Conectando ao banco de dados usando PDO
    $pdo = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Consultando os dados
    $stmt = $pdo->prepare("SELECT id, nome FROM produtos");
    $stmt->execute();

    // Obtendo o resultado
    $usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "Erro: " . $e->getMessage();
    exit;
}
$conn->close();


?>