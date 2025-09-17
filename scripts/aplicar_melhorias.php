<?php
/**
 * Script para aplicar melhorias automaticamente no sistema ERP
 * Executa as correções prioritárias identificadas no plano de implementação
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

class ERPMelhorias {
    private $root_path;
    private $arquivos_corrigidos = [];
    private $logs = [];
    
    public function __construct() {
        $this->root_path = dirname(__DIR__);
        echo "<h2>🚀 Sistema de Melhorias Automáticas - ERP Reciclagem</h2>\n";
        echo "<p>Iniciando aplicação das melhorias prioritárias...</p>\n";
    }
    
    public function executar() {
        echo "<div style='font-family: monospace; background: #f8f9fa; padding: 20px; border-radius: 8px;'>\n";
        
        $this->corrigirEstruturasHTML();
        $this->adicionarClassesResponsivas(); 
        $this->implementarSegurancaBasica();
        $this->otimizarConsultasSQL();
        $this->criarArquivoFuncoes();
        
        echo "</div>\n";
        $this->gerarRelatorio();
    }
    
    private function log($message, $type = 'info') {
        $timestamp = date('Y-m-d H:i:s');
        $icon = $type === 'success' ? '✅' : ($type === 'error' ? '❌' : 'ℹ️');
        echo "<p style='margin: 5px 0;'>{$icon} [{$timestamp}] {$message}</p>\n";
        $this->logs[] = ['timestamp' => $timestamp, 'message' => $message, 'type' => $type];
    }
    
    private function corrigirEstruturasHTML() {
        $this->log("🔧 Iniciando correção de estruturas HTML duplicadas...");
        
        $arquivos = [
            'configuracoes.php',
            'venda.php', 
            'caixa.php',
            'cadastro_clientes.php'
        ];
        
        foreach ($arquivos as $arquivo) {
            $caminho = $this->root_path . '/' . $arquivo;
            if (file_exists($caminho)) {
                $this->corrigirArquivoHTML($caminho);
            }
        }
    }
    
    private function corrigirArquivoHTML($caminho_arquivo) {
        $nome_arquivo = basename($caminho_arquivo);
        $this->log("Verificando {$nome_arquivo}...");
        
        $conteudo = file_get_contents($caminho_arquivo);
        
        // Padrões problemáticos comuns
        $padroes_problematicos = [
            // HTML duplicado no cabeçalho
            '/(<\?php.*?include.*?header\.php.*?\?>)\s*<head>/s' => '$1',
            
            // Meta tags duplicadas
            '/<meta charset="UTF-8">\s*<title>.*?<\/title>\s*<link.*?bootstrap.*?>/s' => '',
            
            // Scripts duplicados no final
            '/(<script src="https:\/\/cdn\.jsdelivr\.net\/npm\/bootstrap.*?><\/script>)\s*\1/s' => '$1',
            
            // CSS inline desnecessário quando já temos responsive-mobile.css
            '/<style>\s*\.section-card\s*\{[^}]*\}\s*<\/style>/s' => ''
        ];
        
        $conteudo_original = $conteudo;
        
        foreach ($padroes_problematicos as $padrao => $substituicao) {
            $conteudo = preg_replace($padrao, $substituicao, $conteudo);
        }
        
        // Garantir que header.php seja incluído corretamente
        if (strpos($conteudo, "include __DIR__.'/includes/header.php';") === false && 
            strpos($conteudo, 'include') !== false) {
            
            $conteudo = preg_replace(
                '/(<\?php[^?]*)(include.*?navbar\.php.*?\?>)/s',
                "$1include __DIR__.'/includes/header.php';\n$2",
                $conteudo
            );
        }
        
        if ($conteudo !== $conteudo_original) {
            file_put_contents($caminho_arquivo, $conteudo);
            $this->log("✅ {$nome_arquivo} corrigido com sucesso", 'success');
            $this->arquivos_corrigidos[] = $nome_arquivo;
        } else {
            $this->log("ℹ️ {$nome_arquivo} já está correto ou não precisava de correções");
        }
    }
    
    private function adicionarClassesResponsivas() {
        $this->log("📱 Adicionando classes responsivas aos formulários e cards...");
        
        $arquivos_php = glob($this->root_path . '/*.php');
        $contador_melhorias = 0;
        
        foreach ($arquivos_php as $arquivo) {
            $nome = basename($arquivo);
            if (in_array($nome, ['includes', 'conexx', 'ajax', 'api'])) continue;
            
            $conteudo = file_get_contents($arquivo);
            $conteudo_original = $conteudo;
            
            // Adicionar classes responsivas
            $melhorias = [
                // Cards sem classe responsiva
                '/<div class="([^"]*(?:card|box|panel)[^"]*)"(?![^>]*section-card)/' => '<div class="$1 section-card"',
                
                // Tabelas sem responsividade
                '/<table class="([^"]*)"(?![^>]*table-responsive)/' => '<div class="table-responsive"><table class="$1"',
                '/<\/table>(?![^<]*<\/div>)/' => '</table></div>',
                
                // Formulários sem classes mobile
                '/<form([^>]*)class="([^"]*)"/' => '<form$1class="$2 needs-validation"',
                
                // Botões sem responsividade adequada
                '/<button([^>]*)class="btn btn-primary"/' => '<button$1class="btn btn-primary btn-responsive"'
            ];
            
            foreach ($melhorias as $padrao => $substituicao) {
                $novo_conteudo = preg_replace($padrao, $substituicao, $conteudo);
                if ($novo_conteudo !== $conteudo) {
                    $conteudo = $novo_conteudo;
                    $contador_melhorias++;
                }
            }
            
            if ($conteudo !== $conteudo_original) {
                file_put_contents($arquivo, $conteudo);
                $this->log("📱 Classes responsivas adicionadas em {$nome}");
            }
        }
        
        $this->log("✅ {$contador_melhorias} melhorias de responsividade aplicadas", 'success');
    }
    
    private function implementarSegurancaBasica() {
        $this->log("🔒 Implementando melhorias de segurança básicas...");
        
        // Criar arquivo de funções de segurança
        $arquivo_seguranca = $this->root_path . '/includes/security_functions.php';
        $conteudo_seguranca = '<?php
/**
 * Funções de segurança para o ERP
 * Implementa validações e sanitizações básicas
 */

class ERPSecurity {
    
    /**
     * Sanitiza entrada de texto
     */
    public static function sanitizeInput($input, $type = "string") {
        if (is_array($input)) {
            return array_map([self::class, "sanitizeInput"], $input);
        }
        
        $input = trim($input);
        
        switch ($type) {
            case "email":
                return filter_var($input, FILTER_SANITIZE_EMAIL);
            case "int":
                return filter_var($input, FILTER_SANITIZE_NUMBER_INT);
            case "float":
                return filter_var($input, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
            case "url":
                return filter_var($input, FILTER_SANITIZE_URL);
            default:
                return htmlspecialchars($input, ENT_QUOTES, "UTF-8");
        }
    }
    
    /**
     * Valida CNPJ
     */
    public static function validateCNPJ($cnpj) {
        $cnpj = preg_replace("/[^0-9]/", "", $cnpj);
        if (strlen($cnpj) != 14) return false;
        
        // Validação algoritmo CNPJ
        for ($i = 0, $j = 5, $soma = 0; $i < 12; $i++) {
            $soma += $cnpj[$i] * $j;
            $j = ($j == 2) ? 9 : $j - 1;
        }
        $resto = $soma % 11;
        if ($cnpj[12] != ($resto < 2 ? 0 : 11 - $resto)) return false;
        
        for ($i = 0, $j = 6, $soma = 0; $i < 13; $i++) {
            $soma += $cnpj[$i] * $j;
            $j = ($j == 2) ? 9 : $j - 1;
        }
        $resto = $soma % 11;
        return $cnpj[13] == ($resto < 2 ? 0 : 11 - $resto);
    }
    
    /**
     * Valida CPF
     */
    public static function validateCPF($cpf) {
        $cpf = preg_replace("/[^0-9]/", "", $cpf);
        if (strlen($cpf) != 11) return false;
        if (preg_match("/^(\d)\1{10}$/", $cpf)) return false;
        
        for ($t = 9; $t < 11; $t++) {
            for ($d = 0, $c = 0; $c < $t; $c++) {
                $d += $cpf[$c] * (($t + 1) - $c);
            }
            $d = ((10 * $d) % 11) % 10;
            if ($cpf[$c] != $d) return false;
        }
        return true;
    }
    
    /**
     * Log de auditoria melhorado
     */
    public static function logAction($user_id, $action, $details = "", $ip = null) {
        global $conn;
        
        if (!$ip) {
            $ip = $_SERVER["REMOTE_ADDR"] ?? "unknown";
        }
        
        $stmt = $conn->prepare("INSERT INTO logs_auditoria (user_id, action, details, ip_address, created_at) VALUES (?, ?, ?, ?, NOW())");
        $stmt->bind_param("isss", $user_id, $action, $details, $ip);
        $stmt->execute();
    }
    
    /**
     * Verifica força da senha
     */
    public static function validatePasswordStrength($password) {
        $errors = [];
        
        if (strlen($password) < 8) {
            $errors[] = "Senha deve ter pelo menos 8 caracteres";
        }
        if (!preg_match("/[a-z]/", $password)) {
            $errors[] = "Senha deve conter pelo menos uma letra minúscula";
        }
        if (!preg_match("/[A-Z]/", $password)) {
            $errors[] = "Senha deve conter pelo menos uma letra maiúscula";
        }
        if (!preg_match("/\d/", $password)) {
            $errors[] = "Senha deve conter pelo menos um número";
        }
        
        return empty($errors) ? true : $errors;
    }
}
?>';
        
        file_put_contents($arquivo_seguranca, $conteudo_seguranca);
        $this->log("✅ Arquivo de funções de segurança criado", 'success');
    }
    
    private function otimizarConsultasSQL() {
        $this->log("⚡ Criando script de otimização SQL...");
        
        $arquivo_sql = $this->root_path . '/db/otimizacoes.sql';
        $sql_otimizacoes = '-- Otimizações de performance para o ERP
-- Execute estas queries no seu banco de dados

-- Índices para melhorar performance
CREATE INDEX IF NOT EXISTS idx_materiais_empresa ON materiais(empresa_id);
CREATE INDEX IF NOT EXISTS idx_clientes_empresa ON clientes(empresa_id);
CREATE INDEX IF NOT EXISTS idx_vendas_data ON vendas(data_venda);
CREATE INDEX IF NOT EXISTS idx_compras_data ON compras(data_compra);
CREATE INDEX IF NOT EXISTS idx_estoque_material ON estoque(material_id);

-- Tabela de logs de auditoria (se não existir)
CREATE TABLE IF NOT EXISTS logs_auditoria (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT,
    action VARCHAR(255) NOT NULL,
    details TEXT,
    ip_address VARCHAR(45),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_logs_user (user_id),
    INDEX idx_logs_date (created_at)
);

-- Tabela de configurações de cache
CREATE TABLE IF NOT EXISTS cache_sistema (
    cache_key VARCHAR(255) PRIMARY KEY,
    cache_value LONGTEXT,
    expiry_time TIMESTAMP,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- View para relatórios de vendas otimizada
CREATE OR REPLACE VIEW vw_vendas_resumo AS
SELECT 
    v.id,
    v.data_venda,
    c.nome as cliente_nome,
    SUM(vi.quantidade) as total_quantidade,
    SUM(vi.subtotal) as total_valor,
    COUNT(vi.id) as total_itens
FROM vendas v
LEFT JOIN clientes c ON v.cliente_id = c.id
LEFT JOIN vendas_itens vi ON v.id = vi.venda_id
GROUP BY v.id, v.data_venda, c.nome;

-- Limpeza de dados antigos (comentado para segurança)
-- DELETE FROM logs_auditoria WHERE created_at < DATE_SUB(NOW(), INTERVAL 1 YEAR);

-- Estatísticas das tabelas
-- ANALYZE TABLE materiais, clientes, vendas, compras, estoque;
';
        
        file_put_contents($arquivo_sql, $sql_otimizacoes);
        $this->log("✅ Script de otimização SQL criado em /db/otimizacoes.sql", 'success');
    }
    
    private function criarArquivoFuncoes() {
        $this->log("🛠️ Criando arquivo de funções auxiliares...");
        
        $arquivo_funcoes = $this->root_path . '/includes/functions.php';
        $conteudo_funcoes = '<?php
/**
 * Funções auxiliares para o Sistema ERP
 * Centraliza operações comuns e utilitários
 */

class ERPFunctions {
    
    /**
     * Formata moeda brasileira
     */
    public static function formatCurrency($value) {
        return "R$ " . number_format(floatval($value), 2, ",", ".");
    }
    
    /**
     * Formata peso (kg)
     */
    public static function formatWeight($value) {
        return number_format(floatval($value), 3, ",", ".") . " kg";
    }
    
    /**
     * Formata data brasileira
     */
    public static function formatDate($date) {
        return date("d/m/Y", strtotime($date));
    }
    
    /**
     * Formata data e hora brasileira  
     */
    public static function formatDateTime($datetime) {
        return date("d/m/Y H:i", strtotime($datetime));
    }
    
    /**
     * Gera código único para lotes
     */
    public static function generateLoteCode($material_id, $empresa_id) {
        $prefix = "L" . str_pad($empresa_id, 2, "0", STR_PAD_LEFT);
        $suffix = str_pad($material_id, 3, "0", STR_PAD_LEFT);
        $timestamp = date("ymd");
        $random = str_pad(rand(1, 999), 3, "0", STR_PAD_LEFT);
        
        return $prefix . $timestamp . $suffix . $random;
    }
    
    /**
     * Calcula peso líquido (bruto - tara)
     */
    public static function calculatePesoLiquido($peso_bruto, $tara = 0) {
        return max(0, floatval($peso_bruto) - floatval($tara));
    }
    
    /**
     * Valida e formata telefone
     */
    public static function formatPhone($phone) {
        $phone = preg_replace("/[^0-9]/", "", $phone);
        
        if (strlen($phone) == 11) {
            return preg_replace("/(\d{2})(\d{5})(\d{4})/", "($1) $2-$3", $phone);
        } elseif (strlen($phone) == 10) {
            return preg_replace("/(\d{2})(\d{4})(\d{4})/", "($1) $2-$3", $phone);
        }
        
        return $phone;
    }
    
    /**
     * Gera hash seguro para senhas
     */
    public static function hashPassword($password) {
        return password_hash($password, PASSWORD_ARGON2ID);
    }
    
    /**
     * Verifica senha contra hash
     */
    public static function verifyPassword($password, $hash) {
        return password_verify($password, $hash);
    }
    
    /**
     * Cache simples de dados
     */
    public static function cache($key, $data = null, $ttl = 3600) {
        global $conn;
        
        if ($data === null) {
            // Buscar do cache
            $stmt = $conn->prepare("SELECT cache_value FROM cache_sistema WHERE cache_key = ? AND expiry_time > NOW()");
            $stmt->bind_param("s", $key);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($row = $result->fetch_assoc()) {
                return unserialize($row["cache_value"]);
            }
            return false;
        } else {
            // Salvar no cache
            $expiry = date("Y-m-d H:i:s", time() + $ttl);
            $serialized_data = serialize($data);
            
            $stmt = $conn->prepare("REPLACE INTO cache_sistema (cache_key, cache_value, expiry_time) VALUES (?, ?, ?)");
            $stmt->bind_param("sss", $key, $serialized_data, $expiry);
            return $stmt->execute();
        }
    }
    
    /**
     * Limpa cache expirado
     */
    public static function clearExpiredCache() {
        global $conn;
        $conn->query("DELETE FROM cache_sistema WHERE expiry_time < NOW()");
    }
    
    /**
     * Gera relatório de materiais mais vendidos
     */
    public static function getMaterialsMaisVendidos($empresa_id, $limit = 10) {
        global $conn;
        
        $cache_key = "materiais_mais_vendidos_" . $empresa_id;
        $cached = self::cache($cache_key);
        
        if ($cached !== false) {
            return $cached;
        }
        
        $stmt = $conn->prepare("
            SELECT m.nome, SUM(vi.quantidade) as total_vendido, SUM(vi.subtotal) as total_valor
            FROM materiais m
            JOIN vendas_itens vi ON m.id = vi.material_id
            JOIN vendas v ON vi.venda_id = v.id
            WHERE m.empresa_id = ? 
            AND v.data_venda >= DATE_SUB(NOW(), INTERVAL 30 DAY)
            GROUP BY m.id, m.nome
            ORDER BY total_vendido DESC
            LIMIT ?
        ");
        
        $stmt->bind_param("ii", $empresa_id, $limit);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $materials = [];
        while ($row = $result->fetch_assoc()) {
            $materials[] = $row;
        }
        
        // Cache por 1 hora
        self::cache($cache_key, $materials, 3600);
        
        return $materials;
    }
}
?>';
        
        file_put_contents($arquivo_funcoes, $conteudo_funcoes);
        $this->log("✅ Arquivo de funções auxiliares criado", 'success');
    }
    
    private function gerarRelatorio() {
        echo "\n<hr>\n";
        echo "<h3>📊 Relatório de Melhorias Aplicadas</h3>\n";
        echo "<div style='background: #e8f5e8; padding: 15px; border-radius: 8px; margin: 10px 0;'>\n";
        echo "<h4>✅ Arquivos Criados/Modificados:</h4>\n";
        echo "<ul>\n";
        
        $arquivos_novos = [
            'includes/security_functions.php' => 'Funções de segurança e validação',
            'includes/functions.php' => 'Funções auxiliares e utilitários',
            'db/otimizacoes.sql' => 'Script de otimização do banco de dados'
        ];
        
        foreach ($arquivos_novos as $arquivo => $descricao) {
            echo "<li><strong>{$arquivo}</strong> - {$descricao}</li>\n";
        }
        
        if (!empty($this->arquivos_corrigidos)) {
            echo "<li><strong>Arquivos HTML corrigidos:</strong> " . implode(', ', $this->arquivos_corrigidos) . "</li>\n";
        }
        
        echo "</ul>\n";
        echo "</div>\n";
        
        echo "<div style='background: #fff3cd; padding: 15px; border-radius: 8px; margin: 10px 0;'>\n";
        echo "<h4>⚠️ Próximos Passos Manuais:</h4>\n";
        echo "<ol>\n";
        echo "<li>Execute o arquivo <code>db/otimizacoes.sql</code> no seu banco de dados</li>\n";
        echo "<li>Inclua as novas funções nos arquivos que precisam: <code>require_once 'includes/functions.php';</code></li>\n";
        echo "<li>Teste as páginas corrigidas em dispositivos móveis</li>\n";
        echo "<li>Implemente prepared statements nos arquivos de inserção/atualização</li>\n";
        echo "<li>Configure backup automático do banco de dados</li>\n";
        echo "</ol>\n";
        echo "</div>\n";
        
        echo "<p><strong>Total de logs gerados:</strong> " . count($this->logs) . "</p>\n";
        echo "<p><strong>Tempo de execução:</strong> " . date('H:i:s') . "</p>\n";
    }
}

// Executar as melhorias se chamado diretamente
if (basename($_SERVER['SCRIPT_NAME']) === 'aplicar_melhorias.php') {
    $melhorias = new ERPMelhorias();
    $melhorias->executar();
}
?>
