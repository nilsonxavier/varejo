<?php

include __DIR__.'/includes/header.php';
include __DIR__.'/includes/navbar.php';

?>

<div class="container mt-5">
        <div class="row g-3">
            <?php
            // Exemplo de dados de imagens, URLs e títulos
            $imagens = [
                ['src' => 'https://via.placeholder.com/150', 'url' => 'pagina1.php', 'titulo' => 'Vendas'],
                ['src' => 'imgs/estoque.jpg', 'url' => 'produtos.php', 'titulo' => 'Estoque'],
                ['src' => 'https://via.placeholder.com/150', 'url' => 'pagina3.php', 'titulo' => 'Imagem 3'],
                ['src' => 'https://via.placeholder.com/150', 'url' => 'pagina4.php', 'titulo' => 'Imagem 4'],
                // Adicione mais imagens conforme necessário
            ];

            // Gerando a grade de botões com imagens e títulos
            foreach ($imagens as $imagem) {
                echo '
                <div class="col-6 col-md-3">
                    <form action="'.$imagem['url'].'" method="get">
                        <button class="btn-image" type="submit">
                            <img src="'.$imagem['src'].'" alt="'.$imagem['titulo'].'">
                            <div class="image-title">'.$imagem['titulo'].'</div>
                        </button>
                    </form>
                </div>';
            }
            ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>

<?php


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

include __DIR__.'/includes/footer.php';

