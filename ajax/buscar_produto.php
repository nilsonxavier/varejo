<?php
require_once '../conexx/config.php';

$termo = $_GET['termo'] ?? '';
$idCliente = $_GET['cliente_id'] ?? 0;

if (strlen($termo) < 2) {
    echo json_encode([]);
    exit;
}

// Buscar qual tabela de preços o cliente usa
$query = "SELECT tabela_preco_id FROM clientes WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $idCliente);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();

$tabelaId = $row['tabela_preco_id'] ?? null;

// Se cliente não tem tabela, tenta buscar a lista padrão da empresa (recebe empresa_id via GET ou usa sessao)
if (!$tabelaId) {
    $empresa_id = intval($_GET['empresa_id'] ?? ($_SESSION['usuario_empresa'] ?? 0));
    $stmt_lp = $conn->prepare("SELECT id FROM listas_precos WHERE empresa_id = ? AND padrao = 1 LIMIT 1");
    $stmt_lp->bind_param('i', $empresa_id);
    $stmt_lp->execute();
    $res_lp = $stmt_lp->get_result();
    if ($res_lp && $r_lp = $res_lp->fetch_assoc()) {
        $tabelaId = intval($r_lp['id']);
    } else {
        // fallback para primeira lista da empresa
        $stmt_lp2 = $conn->prepare("SELECT id FROM listas_precos WHERE empresa_id = ? ORDER BY id LIMIT 1");
        $stmt_lp2->bind_param('i', $empresa_id);
        $stmt_lp2->execute();
        $res_lp2 = $stmt_lp2->get_result();
        if ($res_lp2 && $r_lp2 = $res_lp2->fetch_assoc()) $tabelaId = intval($r_lp2['id']);
    }
    // se ainda vazio, mantém 1 como último recurso
    if (!$tabelaId) $tabelaId = 1;
}

// Buscar produtos com o preço da tabela do cliente
$query = "
    SELECT p.id, p.nome, 
        COALESCE(tp.preco, p.preco) AS preco
    FROM produtos p
    LEFT JOIN precos_tabelados tp 
        ON tp.id_produto = p.id AND tp.id_tabela = ?
    WHERE p.nome LIKE ? OR p.id LIKE ?
    LIMIT 10
";

$stmt = $conn->prepare($query);
$buscaLike = '%' . $termo . '%';
$stmt->bind_param("iss", $tabelaId, $buscaLike, $buscaLike);
$stmt->execute();
$result = $stmt->get_result();

$produtos = [];
while ($row = $result->fetch_assoc()) {
    $produtos[] = $row;
}

header('Content-Type: application/json');
echo json_encode($produtos);
