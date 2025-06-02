<?php
require_once '../conexx/config.php';

$busca = $_GET['busca'] ?? '';
$idCliente = $_GET['idCliente'] ?? 0;

// 1. Buscar qual tabela de preços o cliente usa
$query = "SELECT tabela_preco_id FROM clientes WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $idCliente);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();

$tabelaId = $row['tabela_preco_id'] ?? null;

// Se cliente não tem tabela, usa a padrão (id = 1)
if (!$tabelaId) {
    $tabelaId = 1;
}

// 2. Buscar produtos com o preço da tabela do cliente
$query = "
    SELECT p.id, p.nome, 
        COALESCE(tp.preco, p.preco) AS preco
    FROM produtos p
    LEFT JOIN precos_tabelados tp 
        ON tp.id_produto = p.id AND tp.id_tabela = ?
    WHERE p.nome LIKE ? OR p.id LIKE ?
    LIMIT 10
";

$stmt = $conn->prepare($query);
$buscaLike = '%' . $busca . '%';
$stmt->bind_param("iss", $tabelaId, $buscaLike, $buscaLike);
$stmt->execute();
$result = $stmt->get_result();

$produtos = [];
while ($row = $result->fetch_assoc()) {
    $produtos[] = $row;
}

header('Content-Type: application/json');
echo json_encode($produtos);