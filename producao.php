<?php
require_once 'conexx/config.php';
require_once 'conexx/classProducao.php';

// Instanciando a classe DatabaseProducao
$dbProducao = new DatabaseProducao($conn);

// Processar os parâmetros de data
$dataInicial = isset($_GET['data_inicial']) ? $_GET['data_inicial'] : null;
$dataFinal = isset($_GET['data_final']) ? $_GET['data_final'] : null;

// Se ambas as datas estiverem definidas, obter a produção por produto por dia no intervalo de datas especificado
if ($dataInicial && $dataFinal) {
    $producaoPorProdutoPorDia = $dbProducao->getProducaoPorProdutoPorDia($dataInicial, $dataFinal);
} else {
    // Se uma ou ambas as datas não estiverem definidas, exibir o gráfico vazio
    $producaoPorProdutoPorDia = [];
}

// Preparar os dados para o gráfico
$labels = []; // Serão os dias
$data = [];   // Serão as quantidades produzidas para cada dia

// Inicializando array multidimensional para armazenar os dados por produto
$dadosPorProduto = [];
foreach ($producaoPorProdutoPorDia as $producao) {
    $produto_id = $producao['produto_id'];
    $dia = $producao['dia'];
    $quantidade = $producao['total_quantidade'];

    // Armazenando a quantidade produzida para cada dia e produto
    if (!isset($dadosPorProduto[$produto_id])) {
        $dadosPorProduto[$produto_id] = [];
    }
    // Verificando se o dia já existe para o produto e adicionando a quantidade
    if (!isset($dadosPorProduto[$produto_id][$dia])) {
        $dadosPorProduto[$produto_id][$dia] = 0;
    }
    $dadosPorProduto[$produto_id][$dia] += $quantidade;
}

// Convertendo os dados para o formato esperado pelo Chart.js
foreach ($dadosPorProduto as $produto_id => $dados) {
    $labels = array_keys($dados);
    $data[] = array_values($dados);
}

// Convertendo os dados para JSON
$labels_json = json_encode($labels);
$data_json = json_encode($data);


include __DIR__.'/includes/header.php';
include __DIR__.'/includes/navbar.php';
?>

    <div style="width: 75%; margin: 0 auto;">
        <form method="GET" action="">
            Data Inicial: <input type="date" name="data_inicial">
            Data Final: <input type="date" name="data_final">
            <button type="submit">Visualizar</button>
        </form>
        <canvas id="myChart"></canvas>
    </div>

    <script>
        // Obtendo os dados do PHP
        var labels = <?php echo $labels_json; ?>;
        var data = <?php echo $data_json; ?>;

        // Configurando o gráfico
        var ctx = document.getElementById('myChart').getContext('2d');
        var myChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [
                    <?php
                    foreach ($data as $index => $valores) {
                        echo "{";
                        echo "label: 'Produto ID " . ($index + 1) . "',"; // Adicionando 1 para começar do ID 1
                        echo "data: " . json_encode($valores) . ",";
                        echo "backgroundColor: 'rgba(75, 192, 192, 0.2)',";
                        echo "borderColor: 'rgba(75, 192, 192, 1)',";
                        echo "borderWidth: 1";
                        echo "},";
                    }
                    ?>
                ]
            },
            options: {
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    </script>
<?php include __DIR__.'/includes/footer.php'; ?>

