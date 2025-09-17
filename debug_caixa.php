<?php
require_once 'conexx/config.php';

echo "=== VERIFICAÇÃO ESTRUTURA MOVIMENTAÇÕES ===\n";

// Verificar se a tabela existe
$result = $conn->query("SHOW TABLES LIKE 'movimentacoes'");
if ($result->num_rows == 0) {
    echo "ERRO: Tabela 'movimentacoes' não existe!\n";
    exit;
}
echo "✓ Tabela 'movimentacoes' existe\n";

// Verificar estrutura
echo "\n=== ESTRUTURA DA TABELA ===\n";
$result = $conn->query("DESCRIBE movimentacoes");
while ($row = $result->fetch_assoc()) {
    echo $row['Field'] . " | " . $row['Type'] . " | " . $row['Null'] . " | " . $row['Key'] . "\n";
}

// Verificar se há caixa aberto
echo "\n=== VERIFICAÇÃO CAIXA ABERTO ===\n";
$result = $conn->query("SELECT id, status FROM caixas WHERE status = 'aberto' LIMIT 1");
if ($result->num_rows > 0) {
    $caixa = $result->fetch_assoc();
    echo "✓ Caixa aberto encontrado: ID " . $caixa['id'] . "\n";
} else {
    echo "⚠ Nenhum caixa aberto encontrado\n";
}

// Verificar movimentações recentes
echo "\n=== MOVIMENTAÇÕES RECENTES ===\n";
$result = $conn->query("SELECT * FROM movimentacoes ORDER BY data_movimentacao DESC LIMIT 5");
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        echo "ID: " . $row['id'] . " | Caixa: " . $row['caixa_id'] . " | Tipo: " . $row['tipo'] . " | Valor: " . $row['valor'] . " | Data: " . $row['data_movimentacao'] . "\n";
    }
} else {
    echo "Nenhuma movimentação encontrada\n";
}

// Teste de inserção simples
echo "\n=== TESTE DE INSERÇÃO ===\n";
try {
    $caixa_teste = $conn->query("SELECT id FROM caixas WHERE status = 'aberto' LIMIT 1")->fetch_assoc();
    if ($caixa_teste) {
        $caixa_id = $caixa_teste['id'];
        $data_teste = date('Y-m-d H:i:s');
        $stmt = $conn->prepare("INSERT INTO movimentacoes (caixa_id, tipo, valor, descricao, data_movimentacao, empresa_id) VALUES (?, 'entrada', 0.01, 'Teste debug', ?, 1)");
        $stmt->bind_param("is", $caixa_id, $data_teste);
        
        if ($stmt->execute()) {
            echo "✓ Teste de inserção bem-sucedido (ID: " . $conn->insert_id . ")\n";
            // Remover o teste
            $conn->query("DELETE FROM movimentacoes WHERE id = " . $conn->insert_id);
        } else {
            echo "✗ Erro na inserção: " . $stmt->error . "\n";
        }
    } else {
        echo "Sem caixa aberto para testar\n";
    }
} catch (Exception $e) {
    echo "✗ Exceção no teste: " . $e->getMessage() . "\n";
}
?>
