<?php
require_once '../conexx/config.php';

$termo = $_GET['termo'] ?? '';
$idCliente = $_GET['cliente_id'] ?? 0;

// Permite busca por nome ou id, inclusive vazio, mas filtra por empresa

// Buscar qual tabela de preços o cliente usa

$query = "SELECT lista_preco_id FROM clientes WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $idCliente);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();

$listaId = $row['lista_preco_id'] ?? null;

// Se cliente não tem lista, busca padrão da empresa, senão primeira lista
if (!$listaId) {
    $empresa_id = intval($_GET['empresa_id'] ?? ($_SESSION['usuario_empresa'] ?? 0));
    $stmt_lp = $conn->prepare("SELECT id FROM listas_precos WHERE empresa_id = ? AND padrao = 1 LIMIT 1");
    $stmt_lp->bind_param('i', $empresa_id);
    $stmt_lp->execute();
    $res_lp = $stmt_lp->get_result();
    if ($res_lp && $r_lp = $res_lp->fetch_assoc()) {
        $listaId = intval($r_lp['id']);
    } else {
        $stmt_lp2 = $conn->prepare("SELECT id FROM listas_precos WHERE empresa_id = ? ORDER BY id LIMIT 1");
        $stmt_lp2->bind_param('i', $empresa_id);
        $stmt_lp2->execute();
        $res_lp2 = $stmt_lp2->get_result();
        if ($res_lp2 && $r_lp2 = $res_lp2->fetch_assoc()) $listaId = intval($r_lp2['id']);
    }
    if (!$listaId) $listaId = 1;
}


// Buscar produtos com o preço da lista correta

$empresa_id = intval($_GET['empresa_id'] ?? ($_SESSION['usuario_empresa'] ?? 0));
$query = "
    SELECT m.id, m.nome,
        pm.preco AS preco,
        m.empresa_id
    FROM materiais m
    LEFT JOIN precos_materiais pm ON pm.material_id = m.id AND pm.lista_id = ?
    WHERE (m.nome LIKE ? OR m.id LIKE ?) AND m.empresa_id = ?
    LIMIT 10
";
$stmt = $conn->prepare($query);
$buscaLike = '%' . $termo . '%';
$stmt->bind_param("issi", $listaId, $buscaLike, $buscaLike, $empresa_id);
$stmt->execute();
$result = $stmt->get_result();

$materiais = [];
while ($row = $result->fetch_assoc()) {
    $materiais[] = $row;
}

header('Content-Type: application/json');
echo json_encode($materiais);
