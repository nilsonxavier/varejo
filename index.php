<?php

require_once 'verifica_login.php';
// ... resto da página protegida ...


include __DIR__.'/includes/header.php';
include __DIR__.'/includes/navbar.php';
?>




<!-- Layout fluido, com padding para respirar -->
<div class="container-fluid p-3 mt-4">
  <div class="row g-4 justify-content-center">
    <?php
    // Dados de imagens, URLs e títulos
    $imagens = [
      ['src' => 'imgs/vendas.png', 'url' => 'venda.php', 'titulo' => 'PDV VENDAS'],
      ['src' => 'imgs/vendas.png', 'url' => 'compra.php', 'titulo' => 'PDV COMPRAS'],
      ['src' => 'imgs/estoque.png', 'url' => 'estoque.php', 'titulo' => 'Estoque'],
      ['src' => 'imgs/clientes.png', 'url' => 'cadastro_clientes.php', 'titulo' => 'clientes'],
      //['src' => 'imgs/financeiro.png', 'url' => 'pagina4.php', 'titulo' => 'financeiro'],
    ];

    // Gerar os botões com imagens e títulos
    foreach ($imagens as $imagem) {
      echo '
      <div class="col-6 col-sm-4 col-md-3 col-lg-2 d-flex justify-content-center">
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

<?php
// Função para consultar o CEP e retornar o endereço
function consultarCEP($cep) {
    $cep = preg_replace("/[^0-9]/", "", $cep);
    $url = "https://viacep.com.br/ws/{$cep}/json/";
    $resultado = file_get_contents($url);
    $endereco = json_decode($resultado);
    return [
        'cep' => $endereco->cep ?? '',
        'logradouro' => $endereco->logradouro ?? '',
        'bairro' => $endereco->bairro ?? '',
        'localidade' => $endereco->localidade ?? '',
        'uf' => $endereco->uf ?? ''
    ];
}

include __DIR__.'/includes/footer.php';
?>
