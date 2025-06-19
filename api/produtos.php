<?php
require_once '../conexx/config.php';

$q = isset($_GET['q']) ? trim($_GET['q']) : '';

if (strlen($q) < 2) {
    echo json_encode([]);
    exit;
}

$stmt = $conn->prepare("SELECT id, nome, preco_venda AS preco FROM produtos WHERE nome LIKE ? OR id = ?");
$like = "%$q%";
$stmt->bind_param("ss", $like, $q);
$stmt->execute();
$result = $stmt->get_result();

$produtos = [];

while ($row = $result->fetch_assoc()) {
    $produtos[] = [
        'id' => $row['id'],
        'nome' => $row['nome'],
        'preco' => number_format($row['preco'], 2, ',', '.')
    ];
}

header('Content-Type: application/json');
echo json_encode($produtos);
