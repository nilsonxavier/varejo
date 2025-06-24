<?php
require_once 'verifica_login.php';
require_once 'conexx/config.php';

$usuario_id = $_SESSION['usuario_id'];
$cliente_id = isset($_GET['cliente_id']) ? intval($_GET['cliente_id']) : null;

if ($cliente_id) {
    $res = $conn->query("SELECT * FROM vendas_suspensas WHERE usuario_id = $usuario_id AND cliente_id = $cliente_id LIMIT 1");
} else {
    $res = $conn->query("SELECT * FROM vendas_suspensas WHERE usuario_id = $usuario_id AND cliente_id IS NULL LIMIT 1");
}

$sql = "SELECT * FROM vendas_suspensas WHERE usuario_id = $usuario_id AND cliente_id = $cliente_id LIMIT 1";
$res = $conn->query($sql);
$venda = $res->fetch_assoc();

if ($venda) {
    echo json_encode(['status' => 'ok', 'dados' => $venda]);
} else {
    echo json_encode(['status' => 'vazio']);
}
?>
