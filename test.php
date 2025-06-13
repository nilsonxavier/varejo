<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Grade com Botões Imagens</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .btn-image {
            border: none;
            padding: 0;
            background-color: transparent;
        }

        .btn-image img {
            width: 100%;
            height: auto;
            border-radius: 8px;
        }
    </style>
</head>
<body>
    <div class="container mt-5">
        <div class="row g-3">
            <?php
            // Exemplo de dados de imagens e URLs
            $imagens = [
                ['src' => 'https://via.placeholder.com/150', 'url' => 'pagina1.php', 'alt' => 'Imagem 1'],
                ['src' => 'https://via.placeholder.com/150', 'url' => 'pagina2.php', 'alt' => 'Imagem 2'],
                ['src' => 'https://via.placeholder.com/150', 'url' => 'pagina3.php', 'alt' => 'Imagem 3'],
                ['src' => 'https://via.placeholder.com/150', 'url' => 'pagina4.php', 'alt' => 'Imagem 4'],
                // Adicione mais imagens conforme necessário
            ];

            // Gerando a grade de botões com imagens
            foreach ($imagens as $imagem) {
                echo '
                <div class="col-6 col-md-3">
                    <form action="'.$imagem['url'].'" method="get">
                        <button class="btn-image" type="submit">
                            <img src="'.$imagem['src'].'" alt="'.$imagem['alt'].'">
                        </button>
                    </form>
                </div>';
            }
            ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
