<?php
// ativa a linha abaixo para usar o servidor local
//$servername = "localhost";
// servidor teste remoto ativa esse e comenta a linha de cima

date_default_timezone_set('America/Fortaleza');



$servername = "bdd.elitevenda.com.br";
$username = "erp";
$password = "@Ni33213264";
$dbname = "erp";

// Cria uma conexão com o banco de dados
$conn = new mysqli($servername, $username, $password, $dbname);

// Verifica a conexão
if ($conn->connect_error) {
    die("Erro de conexão: " . $conn->connect_error);
}
// echo "Conexão bem-sucedida";
?>
