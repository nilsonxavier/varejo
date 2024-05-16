<?php
require_once './conexx/database.php';


$database = new Database($conn);
$tabelaHTML = $database->listarDados("produtos");

echo $tabelaHTML;
?>