<?php
require_once 'verifica_login.php';
require_once 'conexx/config.php';

$id = isset($_POST['id']) ? intval($_POST['id']) : 0;
$stmt = $conn->prepare("DELETE FROM compras_suspensas WHERE id = ?");
$stmt->bind_param('i', $id);
if ($stmt->execute()) {
    echo json_encode(['status' => 'ok']);
} else {
    echo json_encode(['status' => 'erro', 'msg' => 'Erro ao excluir']);
}
