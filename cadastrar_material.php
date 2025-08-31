<?php
require_once 'verifica_login.php';
require_once 'conexx/config.php';

$nome = trim($_POST['nome_material'] ?? '');
$quantidade_inicial = isset($_POST['quantidade_inicial']) ? floatval($_POST['quantidade_inicial']) : 0;
$descricao_inicial = isset($_POST['descricao_inicial']) ? trim($_POST['descricao_inicial']) : '';
$empresa_id = intval($_SESSION['usuario_empresa']);

if ($nome === '') {
    header('Location: estoque.php?error=nome_required');
    exit;
}

// Verifica duplicidade de nome na mesma empresa
$chk = $conn->prepare("SELECT id FROM materiais WHERE nome = ? AND empresa_id = ?");
$chk->bind_param('si', $nome, $empresa_id);
$chk->execute();
$resChk = $chk->get_result();
if ($resChk && $resChk->num_rows > 0) {
    header('Location: estoque.php?error=nome_duplicado');
    exit;
}

// Inserir material
$stmt = $conn->prepare("INSERT INTO materiais (nome, empresa_id) VALUES (?, ?)");
$stmt->bind_param('si', $nome, $empresa_id);
$stmt->execute();
$material_id = $stmt->insert_id;

// Se houver quantidade inicial, criar movimentação de entrada
if ($material_id > 0 && $quantidade_inicial > 0) {
    $data = date('Y-m-d H:i:s');
    $descricao = $descricao_inicial !== '' ? $descricao_inicial : 'Estoque inicial';
    $stmt2 = $conn->prepare("INSERT INTO estoque (material_id, tipo, quantidade, data_movimentacao, empresa_id, descricao) VALUES (?, 'entrada', ?, ?, ?, ?)");
    $stmt2->bind_param('idiss', $material_id, $quantidade_inicial, $data, $empresa_id, $descricao);
    $stmt2->execute();
}

header('Location: estoque.php?success=material_created');
exit;
?>
