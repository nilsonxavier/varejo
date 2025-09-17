<?php
session_start();

// Simular uma sessão para teste
if (!isset($_SESSION['usuario_id'])) {
    $_SESSION['usuario_id'] = 1;
    $_SESSION['usuario_empresa'] = 1;
}

require_once 'conexx/config.php';

$usuario_id = $_SESSION['usuario_id'];
$empresa_id = $_SESSION['usuario_empresa'];

echo "=== TESTE DIRETO DE MOVIMENTAÇÃO ===\n";
echo "Usuario ID: $usuario_id\n";
echo "Empresa ID: $empresa_id\n";

// Verificar conexão
if ($conn->connect_error) {
    die("Erro de conexão: " . $conn->connect_error);
}
echo "✓ Conexão com banco OK\n";

// Verificar se existe caixa aberto
$caixa_result = $conn->query("SELECT * FROM caixas WHERE status = 'aberto' AND empresa_id = " . intval($empresa_id) . " ORDER BY id DESC LIMIT 1");
if ($caixa_result->num_rows == 0) {
    echo "⚠ ERRO: Nenhum caixa aberto para empresa $empresa_id\n";
    
    // Listar todos os caixas
    $all_caixas = $conn->query("SELECT * FROM caixas ORDER BY id DESC LIMIT 5");
    echo "\n=== ÚLTIMOS 5 CAIXAS ===\n";
    while ($cx = $all_caixas->fetch_assoc()) {
        echo "ID: " . $cx['id'] . " | Status: " . $cx['status'] . " | Empresa: " . $cx['empresa_id'] . " | Data: " . $cx['data_abertura'] . "\n";
    }
    exit;
}

$caixa_aberto = $caixa_result->fetch_assoc();
echo "✓ Caixa aberto encontrado: ID " . $caixa_aberto['id'] . "\n";

// Simular uma movimentação
$tipo = 'entrada';
$valor = 10.50;
$descricao = 'Teste de movimentação';
$caixa_id = $caixa_aberto['id'];
$data_movimentacao = date('Y-m-d H:i:s');

echo "\n=== PREPARANDO INSERÇÃO ===\n";
echo "Caixa ID: $caixa_id\n";
echo "Tipo: $tipo\n";
echo "Valor: $valor\n";
echo "Descrição: $descricao\n";
echo "Data: $data_movimentacao\n";
echo "Empresa: $empresa_id\n";

// Teste da query preparada
try {
    $stmt = $conn->prepare("INSERT INTO movimentacoes (caixa_id, tipo, valor, descricao, data_movimentacao, empresa_id) VALUES (?, ?, ?, ?, ?, ?)");
    if (!$stmt) {
        echo "✗ Erro ao preparar statement: " . $conn->error . "\n";
        exit;
    }
    
    $stmt->bind_param("isdssi", $caixa_id, $tipo, $valor, $descricao, $data_movimentacao, $empresa_id);
    
    if ($stmt->execute()) {
        $mov_id = $conn->insert_id;
        echo "✓ Movimentação inserida com sucesso! ID: $mov_id\n";
        
        // Verificar se realmente foi inserida
        $check = $conn->query("SELECT * FROM movimentacoes WHERE id = $mov_id");
        if ($check->num_rows > 0) {
            $mov = $check->fetch_assoc();
            echo "✓ Confirmação: Movimentação ID $mov_id existe no banco\n";
            echo "  Valor: R$ " . number_format($mov['valor'], 2, ',', '.') . "\n";
            echo "  Descrição: " . $mov['descricao'] . "\n";
            
            // Limpar teste
            $conn->query("DELETE FROM movimentacoes WHERE id = $mov_id");
            echo "✓ Movimentação de teste removida\n";
        }
    } else {
        echo "✗ Erro ao executar statement: " . $stmt->error . "\n";
    }
} catch (Exception $e) {
    echo "✗ Exceção: " . $e->getMessage() . "\n";
}

echo "\n=== TESTE CONCLUÍDO ===\n";
?>
