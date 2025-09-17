<?php
/**
 * 🔧 CONFIGURAÇÕES DE DESENVOLVIMENTO
 * Sistema ERP - Versão 2.0 Responsiva
 * 
 * Este arquivo centraliza configurações importantes para desenvolvimento
 * e manutenção do sistema ERP de reciclagem.
 */

// Configurações de ambiente
define('ERP_VERSION', '2.0');
define('ERP_DEBUG', true); // Mudar para false em produção
define('ERP_MOBILE_FIRST', true);

// Configurações de cache
define('CACHE_ENABLED', true);
define('CACHE_TTL', 3600); // 1 hora

// Configurações de segurança
define('SESSION_TIMEOUT', 3600); // 1 hora
define('PASSWORD_MIN_LENGTH', 8);
define('MAX_LOGIN_ATTEMPTS', 5);

/**
 * Classe de configurações do sistema
 */
class ERPConfig {
    
    // CSS e JS essenciais que devem estar em todas as páginas
    public static $required_assets = [
        'css' => [
            'https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css',
            'https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css',
            'css/responsive-mobile.css'
        ],
        'js' => [
            'https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js',
            'https://code.jquery.com/jquery-3.6.0.min.js',
            'js/erp-utils.js'
        ]
    ];
    
    // Páginas que devem usar o template responsivo
    public static $responsive_pages = [
        'index.php',
        'venda.php', 
        'compra.php',
        'caixa.php',
        'estoque.php',
        'cadastro_clientes.php',
        'configuracoes.php',
        'historico_vendas.php'
    ];
    
    // Configurações de impressão por tipo de papel
    public static $print_configs = [
        'A4' => [
            'width' => '210mm',
            'height' => '297mm',
            'margin' => '15mm'
        ],
        '80mm' => [
            'width' => '80mm', 
            'height' => 'auto',
            'margin' => '5mm'
        ],
        '60mm' => [
            'width' => '60mm',
            'height' => 'auto', 
            'margin' => '3mm'
        ]
    ];
    
    // Validações específicas para ERP de reciclagem
    public static $validation_rules = [
        'materiais' => [
            'nome' => ['required', 'min:3', 'max:100'],
            'tipo' => ['required', 'in:plastico,papel,metal,vidro,organico'],
            'preco' => ['required', 'numeric', 'min:0']
        ],
        'clientes' => [
            'nome' => ['required', 'min:3', 'max:100'],
            'documento' => ['required', 'cpf_or_cnpj'],
            'telefone' => ['required', 'phone'],
            'email' => ['email']
        ],
        'vendas' => [
            'cliente_id' => ['required', 'exists:clientes,id'],
            'total' => ['required', 'numeric', 'min:0.01']
        ]
    ];
    
    /**
     * Obtém configuração ativa do banco
     */
    public static function getConfiguracaoAtiva($empresa_id) {
        global $conn;
        
        $cache_key = "config_empresa_" . $empresa_id;
        
        // Tentar cache primeiro
        if (CACHE_ENABLED) {
            $cached = self::getFromCache($cache_key);
            if ($cached !== false) return $cached;
        }
        
        $stmt = $conn->prepare("SELECT * FROM configuracoes WHERE empresa_id = ? LIMIT 1");
        $stmt->bind_param('i', $empresa_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $config = $result->fetch_assoc() ?: self::getDefaultConfig();
        
        // Salvar no cache
        if (CACHE_ENABLED) {
            self::setCache($cache_key, $config, CACHE_TTL);
        }
        
        return $config;
    }
    
    /**
     * Configuração padrão
     */
    public static function getDefaultConfig() {
        return [
            'tamanho_papel' => 'A4',
            'tema_dark' => false,
            'empresa_nome' => 'ERP Reciclagem',
            'empresa_cnpj' => '',
            'empresa_endereco' => '',
            'empresa_telefone' => '',
            'empresa_email' => ''
        ];
    }
    
    /**
     * Gera CSS dinâmico baseado na configuração
     */
    public static function generateDynamicCSS($config) {
        $css = "/* CSS Dinâmico - ERP v" . ERP_VERSION . " */\n";
        
        // Tema escuro
        if ($config['tema_dark']) {
            $css .= ":root {
                --bs-body-bg: #1a1a1a;
                --bs-body-color: #ffffff;
                --bs-card-bg: #2d2d2d;
                --bs-border-color: #404040;
            }\n";
        }
        
        // Configurações de impressão
        $paper = $config['tamanho_papel'] ?? 'A4';
        $print_config = self::$print_configs[$paper] ?? self::$print_configs['A4'];
        
        $css .= "@media print {
            @page { 
                size: {$print_config['width']} {$print_config['height']};
                margin: {$print_config['margin']};
            }
        }\n";
        
        return $css;
    }
    
    /**
     * Valida estrutura de página
     */
    public static function validatePageStructure($file_path) {
        $content = file_get_contents($file_path);
        $errors = [];
        
        // Verificar inclusão do header
        if (strpos($content, "include __DIR__.'/includes/header.php'") === false) {
            $errors[] = "Header não incluído corretamente";
        }
        
        // Verificar HTML duplicado
        if (preg_match('/<head>.*<head>/s', $content)) {
            $errors[] = "Tags <head> duplicadas detectadas";
        }
        
        // Verificar classes responsivas
        if (strpos($content, 'section-card') === false && strpos($content, 'form') !== false) {
            $errors[] = "Classes responsivas não aplicadas";
        }
        
        return $errors;
    }
    
    /**
     * Ferramentas de debug para desenvolvimento
     */
    public static function debugInfo() {
        if (!ERP_DEBUG) return '';
        
        $info = [
            'Versão' => ERP_VERSION,
            'PHP' => PHP_VERSION,
            'Memória Usada' => memory_get_usage(true) / 1024 / 1024 . ' MB',
            'Tempo Execução' => microtime(true) - $_SERVER['REQUEST_TIME_FLOAT'] . 's'
        ];
        
        $debug = "<div style='position:fixed;bottom:10px;right:10px;background:#000;color:#fff;padding:10px;border-radius:5px;font-size:12px;z-index:9999;'>";
        $debug .= "🔧 DEBUG INFO<br>";
        foreach ($info as $key => $value) {
            $debug .= "{$key}: {$value}<br>";
        }
        $debug .= "</div>";
        
        return $debug;
    }
    
    /**
     * Cache simples
     */
    private static function getFromCache($key) {
        $file = sys_get_temp_dir() . '/erp_cache_' . md5($key) . '.tmp';
        
        if (file_exists($file) && time() - filemtime($file) < CACHE_TTL) {
            return unserialize(file_get_contents($file));
        }
        
        return false;
    }
    
    private static function setCache($key, $data, $ttl) {
        $file = sys_get_temp_dir() . '/erp_cache_' . md5($key) . '.tmp';
        file_put_contents($file, serialize($data));
    }
    
    /**
     * Log de desenvolvimento
     */
    public static function devLog($message, $type = 'info') {
        if (!ERP_DEBUG) return;
        
        $timestamp = date('Y-m-d H:i:s');
        $log_entry = "[{$timestamp}] [{$type}] {$message}\n";
        
        $log_file = dirname(__FILE__) . '/../logs/dev.log';
        
        // Criar diretório se não existir
        $log_dir = dirname($log_file);
        if (!is_dir($log_dir)) {
            mkdir($log_dir, 0755, true);
        }
        
        file_put_contents($log_file, $log_entry, FILE_APPEND | LOCK_EX);
    }
}

// Auto-log de desenvolvimento
if (ERP_DEBUG && !headers_sent()) {
    ERPConfig::devLog("Página acessada: " . $_SERVER['REQUEST_URI']);
}

// Função helper para incluir assets essenciais
function incluir_assets_essenciais() {
    foreach (ERPConfig::$required_assets['css'] as $css) {
        echo "<link rel='stylesheet' href='{$css}'>\n";
    }
}

function incluir_scripts_essenciais() {
    foreach (ERPConfig::$required_assets['js'] as $js) {
        echo "<script src='{$js}'></script>\n";
    }
}

// Configuração global de timezone
date_default_timezone_set('America/Sao_Paulo');

// Headers de segurança básicos
if (!headers_sent()) {
    header('X-Content-Type-Options: nosniff');
    header('X-Frame-Options: SAMEORIGIN');
    header('X-XSS-Protection: 1; mode=block');
}

?>
