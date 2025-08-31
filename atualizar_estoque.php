<?php
require_once 'verifica_login.php';
require_once 'conexx/config.php';

$material_id = intval($_POST['material_id']);
$tipo = $_POST['tipo'] === 'saida' ? 'saida' : 'entrada';
$quantidade = floatval($_POST['quantidade']);
$data = date('Y-m-d H:i:s');
$empresa_id = intval($_SESSION['usuario_empresa']);
$descricao = isset($_POST['descricao']) ? trim($_POST['descricao']) : '';

// Descrição é obrigatória
if ($material_id > 0 && $quantidade > 0) {
    if ($descricao === '') {
        header('Location: estoque.php?error=descricao_required');
        exit;
    }

    // Agora persistimos também a empresa associada à movimentação
    $stmt = $conn->prepare("INSERT INTO estoque (material_id, tipo, quantidade, data_movimentacao, empresa_id, descricao) VALUES (?, ?, ?, ?, ?, ?)");
    // tipos: i (material_id), s (tipo), d (quantidade), s (data), i (empresa_id), s (descricao)
    $stmt->bind_param("isdsis", $material_id, $tipo, $quantidade, $data, $empresa_id, $descricao);
    $stmt->execute();
}

header('Location: estoque.php');
exit;
