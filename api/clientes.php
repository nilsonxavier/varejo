<?php
require_once '../conexx/config.php';

$termo = $_GET['q'] ?? '';
$clientes = [];


// retorna nome cliente e lista de preços
if ($termo !== '') {
    $sql = "
    SELECT c.id, c.nome, c.lista_preco_id, lp.nome AS listas_precos_nome
    FROM clientes c
    LEFT JOIN listas_precos lp ON c.lista_preco_id = lp.id
    WHERE c.nome LIKE CONCAT('%', ?, '%')
    GROUP BY c.id
    ORDER BY c.nome
    LIMIT 10
";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $termo);
$stmt->execute();
$res = $stmt->get_result();
while ($row = $res->fetch_assoc()) {
    $clientes[] = $row;
}

}

header('Content-Type: application/json');
echo json_encode($clientes);
