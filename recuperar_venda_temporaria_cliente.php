<?php
require_once 'verifica_login.php';
// ... resto da página protegida ...
require_once 'conexx/config.php';


$user_id = $_SESSION['user_id'] ?? 0;
$cliente_id = intval($_GET['cliente_id']);

$res = $conn->query("SELECT * FROM vendas_temporarias WHERE user_id = $user_id AND cliente_id = $cliente_id LIMIT 1");

if ($res && $res->num_rows > 0) {
    $row = $res->fetch_assoc();
    echo json_encode([
        'status' => 'ok',
        'dados' => $row
    ]);
} else {
    echo json_encode(['status' => 'empty']);
}
?>
