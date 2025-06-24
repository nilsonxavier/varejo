<?php
require_once 'verifica_login.php';
// ... resto da página protegida ...
require_once 'conexx/config.php';

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

$res = $conn->query("SELECT venda_json FROM vendas_suspensas WHERE id = $id");
if ($res && $row = $res->fetch_assoc()) {
    echo json_encode([
        'status' => 'ok',
        'dados' => json_decode($row['venda_json'], true)
    ]);
} else {
    echo json_encode(['status' => 'erro', 'msg' => 'Venda não encontrada']);
}
?>
