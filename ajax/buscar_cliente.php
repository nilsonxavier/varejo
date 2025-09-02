
<?php
error_reporting(E_ALL); ini_set('display_errors', 1);
session_start();
require_once '../conexx/config.php';
$empresa_id = intval($_SESSION['usuario_empresa'] ?? 0);
$termo = trim($_GET['termo'] ?? '');
if (strlen($termo) < 1) {
    echo json_encode([]);
    exit;
}
$sql = "SELECT id, nome, lista_preco_id FROM clientes WHERE empresa_id = ? AND (nome LIKE ? OR id LIKE ?) ORDER BY nome LIMIT 10";
$stmt = $conn->prepare($sql);
$like = "%$termo%";
$stmt->bind_param('iss', $empresa_id, $like, $like);
$stmt->execute();
$res = $stmt->get_result();
$clientes = [];
while ($row = $res->fetch_assoc()) {
    $clientes[] = $row;
}
header('Content-Type: application/json');
echo json_encode($clientes);
