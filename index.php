<?php

include __DIR__.'/includes/header.php';
include __DIR__.'/includes/navbar.php';

// Função para consultar o CEP e retornar o endereço
function consultarCEP($cep) {
    // Formata o CEP removendo caracteres especiais
    $cep = preg_replace("/[^0-9]/", "", $cep);
    
    // URL da API do ViaCEP
    $url = "https://viacep.com.br/ws/{$cep}/json/";

    // Faz a requisição para a API
    $resultado = file_get_contents($url);

    // Decodifica o JSON de resposta
    $endereco = json_decode($resultado);

    // Retorna o endereço como um array associativo
    return [
        'cep' => $endereco->cep ?? '',
        'logradouro' => $endereco->logradouro ?? '',
        'bairro' => $endereco->bairro ?? '',
        'localidade' => $endereco->localidade ?? '',
        'uf' => $endereco->uf ?? ''
    ];
}

// Exemplo de uso da função
$cep = "60-762465"; // Substitua pelo CEP que deseja consultar
$endereco = consultarCEP($cep);

// Exibe o endereço
echo "CEP: " . $endereco['cep'] . "<br>";
echo "Logradouro: " . $endereco['logradouro'] . "<br>";
echo "Bairro: " . $endereco['bairro'] . "<br>";
echo "Localidade: " . $endereco['localidade'] . "<br>";
echo "UF: " . $endereco['uf'] . "<br>";



include __DIR__.'/includes/footer.php';

