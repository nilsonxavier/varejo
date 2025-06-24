<?php
require_once 'conexx/config.php';

$id = isset($_POST['id']) ? intval($_POST['id']) : 0;
if ($id > 0) {
    $conn->query("DELETE FROM vendas_suspensas WHERE id = $id");
    echo json_encode(['status' => 'ok']);
} else {
    echo json_encode(['status' => 'erro']);
}
?>
