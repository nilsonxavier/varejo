<?php
require_once 'verifica_login.php';
require_once 'conexx/config.php';

$empresa_id = $_SESSION['usuario_empresa'];
$res = $conn->query("SELECT cs.id, cs.cliente_id, cs.compra_json, c.nome AS cliente_nome
                     FROM compras_suspensas cs
                     LEFT JOIN clientes c ON cs.cliente_id = c.id
                     WHERE cs.empresa_id = " . intval($empresa_id) . "
                     ORDER BY cs.data_criacao DESC");

$compras = [];
while ($row = $res->fetch_assoc()) {
    $compra = json_decode($row['compra_json'], true);
    $total = 0;
    if (isset($compra['itens'])) {
        foreach ($compra['itens'] as $item) {
            $total += $item['quantidade'] * $item['preco_unitario'];
        }
    }
    $compras[] = [
        'id' => $row['id'],
        'cliente_id' => $row['cliente_id'],
        'cliente_nome' => $row['cliente_nome'] ?? 'Sem cliente',
        'total' => $total
    ];
}
echo json_encode(['status' => 'ok', 'compras' => $compras]);
