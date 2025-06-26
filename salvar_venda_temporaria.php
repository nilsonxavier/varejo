<?php
require_once 'verifica_login.php';
require_once 'conexx/config.php';

$usuario_id = $_SESSION['usuario_id'];

// Buscar empresa_id do usuário
$resUser = $conn->query("SELECT empresa_id FROM usuarios WHERE id = $usuario_id");
$userData = $resUser->fetch_assoc();
$empresa_id = $userData ? $userData['empresa_id'] : null;

// Pega os dados do POST
$cliente_id = isset($_POST['cliente_id']) && trim($_POST['cliente_id']) !== '' ? intval($_POST['cliente_id']) : null;
if ($cliente_id === 0) {
    $cliente_id = null;
}

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

// Apagar venda anterior para este cliente (ou sem cliente)
if ($cliente_id !== null) {
    $conn->query("DELETE FROM vendas_suspensas WHERE usuario_id = $usuario_id AND cliente_id = $cliente_id");
 } //parte que da o limite de vendas abertas sem cliente por user -- else {
//     $conn->query("DELETE FROM vendas_suspensas WHERE usuario_id = $usuario_id AND cliente_id IS NULL");
// }

// Salvar a nova venda suspensa
$stmt = $conn->prepare("INSERT INTO vendas_suspensas (usuario_id, empresa_id, cliente_id, lista_preco_id, venda_json) VALUES (?, ?, ?, ?, ?)");
$stmt->bind_param("iiiss", $usuario_id, $empresa_id, $cliente_id, $lista_preco_id, $venda_json);
$stmt->execute();

echo json_encode(['status' => 'ok', 'msg' => 'Venda suspensa salva com sucesso.']);
?>
