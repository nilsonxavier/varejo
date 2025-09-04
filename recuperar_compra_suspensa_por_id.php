<?php
require_once 'verifica_login.php';
require_once 'conexx/config.php';

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$stmt = $conn->prepare("SELECT compra_json FROM compras_suspensas WHERE id = ?");
$stmt->bind_param('i', $id);
$stmt->execute();
$res = $stmt->get_result();

if ($res && $row = $res->fetch_assoc()) {
    $compra = json_decode($row['compra_json'], true);
    echo json_encode(['status' => 'ok', 'dados' => $compra]);
} else {
    echo json_encode(['status' => 'erro', 'msg' => 'Compra não encontrada']);
}
