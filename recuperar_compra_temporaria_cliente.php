<?php
require_once 'verifica_login.php';
require_once 'conexx/config.php';

$empresa_id = $_SESSION['usuario_empresa'];
$cliente_id = isset($_GET['cliente_id']) ? intval($_GET['cliente_id']) : 0;

$stmt = $conn->prepare("SELECT compra_json FROM compras_suspensas WHERE cliente_id = ? AND empresa_id = ?");
$stmt->bind_param('ii', $cliente_id, $empresa_id);
$stmt->execute();
$res = $stmt->get_result();

if ($res && $row = $res->fetch_assoc()) {
    echo json_encode(['status' => 'ok', 'dados' => ['compra_json' => $row['compra_json']]]);
} else {
    echo json_encode(['status' => 'erro', 'msg' => 'Nenhuma compra temporária encontrada']);
}
