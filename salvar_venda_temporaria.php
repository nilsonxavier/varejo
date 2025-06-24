<?php
require_once 'verifica_login.php';
// ... resto da página protegida ...
require_once 'conexx/config.php';



$usuario_id = $_SESSION['usuario_id'];

// Buscar empresa_id do usuário
$resUser = $conn->query("SELECT empresa_id FROM usuarios WHERE id = $usuario_id");
$userData = $resUser->fetch_assoc();
$empresa_id = $userData ? $userData['empresa_id'] : null;

// Pega os dados do POST
$cliente_id = isset($_POST['cliente_id']) && trim($_POST['cliente_id']) !== '' ? intval($_POST['cliente_id']) : null;

$lista_preco_id = isset($_POST['lista_preco_id']) ? intval($_POST['lista_preco_id']) : null;

// Pega os arrays de itens
$material_ids = $_POST['material_id'] ?? [];
$quantidades = $_POST['quantidade'] ?? [];
$precos = $_POST['preco_unitario'] ?? [];

$itens = [];
foreach ($material_ids as $index => $material_id) {
    $itens[] = [
        'material_id' => $material_id,
        'quantidade' => $quantidades[$index],
        'preco_unitario' => $precos[$index]
    ];
}

$venda_json = json_encode([
    'cliente_id' => $cliente_id,
    'lista_preco_id' => $lista_preco_id,
    'itens' => $itens
], JSON_UNESCAPED_UNICODE);


if ($cliente_id) {
    // Limpa a venda suspensa anterior só desse cliente para este usuário
    $conn->query("DELETE FROM vendas_suspensas WHERE usuario_id = $usuario_id AND cliente_id = $cliente_id");
} else {
    // Se não tem cliente, só mantém uma sem cliente por usuário
    $conn->query("DELETE FROM vendas_suspensas WHERE usuario_id = $usuario_id AND cliente_id IS NULL");
}



// Salva a nova venda suspensa
$stmt = $conn->prepare("INSERT INTO vendas_suspensas (usuario_id, empresa_id, cliente_id, lista_preco_id, venda_json) VALUES (?, ?, ?, ?, ?)");
$stmt->bind_param("iiiss", $usuario_id, $empresa_id, $cliente_id, $lista_preco_id, $venda_json);

// Se cliente_id for NULL, ajuste manualmente:
if ($cliente_id === null) {
    $stmt->bind_param("iiiss", $usuario_id, $empresa_id, null, $lista_preco_id, $venda_json);
}

$stmt->execute();

