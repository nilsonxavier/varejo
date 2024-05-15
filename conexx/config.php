<?php
$servername = "localhost";
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
