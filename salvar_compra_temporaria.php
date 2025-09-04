<?php
require_once 'verifica_login.php';
require_once 'conexx/config.php';

$usuario_id = $_SESSION['usuario_id'];
$empresa_id = $_SESSION['usuario_empresa'];

$cliente_id = isset($_POST['cliente_id']) ? intval(explode(' ', $_POST['cliente_id'])[0]) : 0;
// Permite cliente_id = 0 (sem cliente)
// Monta o JSON da compra temporária
$compra = [
    'cliente_id' => $cliente_id,
    'lista_preco_id' => isset($_POST['lista_preco_id']) ? $_POST['lista_preco_id'] : '',
    'itens' => []
];

if (isset($_POST['material_id']) && is_array($_POST['material_id'])) {
    foreach ($_POST['material_id'] as $i => $mat) {
        $compra['itens'][] = [
            'material_id' => $mat,
            'quantidade' => $_POST['quantidade'][$i],
            'preco_unitario' => $_POST['preco_unitario'][$i]
        ];
    }
}

$compra_json = json_encode($compra);

// Salva ou atualiza a compra suspensa
// Para cliente_id = 0 (sem cliente), verificar se já existe um registro recente para não duplicar
if($cliente_id == 0) {
    // Verificar se já existe um registro sem cliente criado recentemente (últimos 5 minutos)
    $stmt = $conn->prepare("SELECT id FROM compras_suspensas WHERE cliente_id = 0 AND usuario_id = ? AND empresa_id = ? AND data_criacao > DATE_SUB(NOW(), INTERVAL 5 MINUTE) ORDER BY data_criacao DESC LIMIT 1");
    $stmt->bind_param('ii', $usuario_id, $empresa_id);
    $stmt->execute();
    $res = $stmt->get_result();

    if ($res && $row = $res->fetch_assoc()) {
        // Atualizar o registro existente recente
        $id = $row['id'];
        $stmt2 = $conn->prepare("UPDATE compras_suspensas SET compra_json = ?, data_criacao = NOW() WHERE id = ?");
        $stmt2->bind_param('si', $compra_json, $id);
        $stmt2->execute();
    } else {
        // Inserir novo registro apenas se não houver um recente
        $stmt2 = $conn->prepare("INSERT INTO compras_suspensas (cliente_id, usuario_id, empresa_id, compra_json) VALUES (?, ?, ?, ?)");
        $stmt2->bind_param('iiis', $cliente_id, $usuario_id, $empresa_id, $compra_json);
        $stmt2->execute();
    }
} else {
    // Para clientes específicos, verificar se já existe para atualizar
    $stmt = $conn->prepare("SELECT id FROM compras_suspensas WHERE cliente_id = ? AND empresa_id = ?");
    $stmt->bind_param('ii', $cliente_id, $empresa_id);
    $stmt->execute();
    $res = $stmt->get_result();

    if ($res && $row = $res->fetch_assoc()) {
        $id = $row['id'];
        $stmt2 = $conn->prepare("UPDATE compras_suspensas SET compra_json = ?, usuario_id = ?, data_criacao = NOW() WHERE id = ?");
        $stmt2->bind_param('sii', $compra_json, $usuario_id, $id);
        $stmt2->execute();
    } else {
        $stmt2 = $conn->prepare("INSERT INTO compras_suspensas (cliente_id, usuario_id, empresa_id, compra_json) VALUES (?, ?, ?, ?)");
        $stmt2->bind_param('iiis', $cliente_id, $usuario_id, $empresa_id, $compra_json);
        $stmt2->execute();
    }
}

echo json_encode(['status' => 'ok', 'msg' => 'Compra temporária salva']);
