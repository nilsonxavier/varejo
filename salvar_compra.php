<?php
require_once 'verifica_login.php';
require_once 'conexx/config.php';

// Recebe dados do formulário
$cliente_id = isset($_POST['cliente_id']) ? intval(explode(' ', $_POST['cliente_id'])[0]) : 0;
$lista_preco_id = isset($_POST['lista_preco_id']) ? intval(explode(' ', $_POST['lista_preco_id'])[0]) : 0;
$empresa_id = $_SESSION['usuario_empresa'];

$materiais = isset($_POST['material_id']) ? $_POST['material_id'] : [];
$quantidades = isset($_POST['quantidade']) ? $_POST['quantidade'] : [];
$precos = isset($_POST['preco_unitario']) ? $_POST['preco_unitario'] : [];

if (!$cliente_id || !$empresa_id || count($materiais) == 0) {
    echo '<div class="alert alert-danger">Dados inválidos!</div>';
    exit;
}

// Salva a compra (exemplo simples, ajuste conforme sua lógica)
$stmt = $conn->prepare("INSERT INTO compras (empresa_id, cliente_id, lista_preco_id, data) VALUES (?, ?, ?, NOW())");
$stmt->bind_param('iii', $empresa_id, $cliente_id, $lista_preco_id);
$stmt->execute();
$compra_id = $conn->insert_id;
$stmt->close();

for ($i = 0; $i < count($materiais); $i++) {
    $material_id = intval(explode(' ', $materiais[$i])[0]);
    $quantidade = floatval($quantidades[$i]);
    $preco_unitario = floatval($precos[$i]);
    $stmt = $conn->prepare("INSERT INTO compras_itens (compra_id, material_id, quantidade, preco_unitario) VALUES (?, ?, ?, ?)");
    $stmt->bind_param('iidd', $compra_id, $material_id, $quantidade, $preco_unitario);
    $stmt->execute();
    $stmt->close();
}

// Redireciona ou mostra mensagem de sucesso
header('Location: compra.php?sucesso=1');
exit;
