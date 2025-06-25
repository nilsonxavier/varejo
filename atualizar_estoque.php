<?php
require_once 'verifica_login.php';
require_once 'conexx/config.php';

$material_id = intval($_POST['material_id']);
$tipo = $_POST['tipo'] === 'saida' ? 'saida' : 'entrada';
$quantidade = floatval($_POST['quantidade']);
$data = date('Y-m-d H:i:s');

if ($material_id > 0 && $quantidade > 0) {
    $stmt = $conn->prepare("INSERT INTO estoque (material_id, tipo, quantidade, data_movimentacao) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("isds", $material_id, $tipo, $quantidade, $data);
    $stmt->execute();
}

header('Location: estoque.php');
exit;
