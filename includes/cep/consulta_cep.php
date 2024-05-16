<?php
function consultarCEP($cep) {
    $cep = preg_replace("/[^0-9]/", "", $cep);
    $url = "https://viacep.com.br/ws/{$cep}/json/";
    $resultado = file_get_contents($url);
    return json_decode($resultado, true);
}

$cep = $_GET['cep'];
echo json_encode(consultarCEP($cep));
?>
