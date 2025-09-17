#!/usr/bin/env php
<?php
/**
 * 🔍 VERIFICADOR DE INTEGRIDADE DO SISTEMA ERP
 * 
 * Script para verificar a saúde do sistema após aplicar melhorias
 * Executa testes automatizados e gera relatório detalhado
 */

require_once __DIR__ . '/../includes/config_dev.php';

class ERPHealthChecker {
    
    private $checks = [];
    private $warnings = [];
    private $errors = [];
    private $root_path;
    
    public function __construct() {
        $this->root_path = dirname(__DIR__);
        echo "🔍 ERP Health Checker v" . ERP_VERSION . "\n";
        echo "=====================================\n\n";
    }
    
    public function runAllChecks() {
        $this->checkFileStructure();
        $this->checkDatabaseConnection();
        $this->checkCSSConsistency();
        $this->checkJavaScriptErrors();
        $this->checkSecurityHeaders();
        $this->checkMobileResponsiveness();
        $this->checkPerformance();
        
        $this->generateReport();
    }
    
    private function checkFileStructure() {
        echo "📁 Verificando estrutura de arquivos...\n";
        
        $required_files = [
            'includes/header.php',
            'includes/navbar.php', 
            'includes/footer.php',
            'includes/configuracoes_globais.php',
            'includes/config_dev.php',
            'css/responsive-mobile.css',
            'js/erp-utils.js'
        ];
        
        foreach ($required_files as $file) {
            $full_path = $this->root_path . '/' . $file;
            if (file_exists($full_path)) {
                $this->checks[] = "✅ {$file} existe";
            } else {
                $this->errors[] = "❌ {$file} não encontrado";
            }
        }
        
        // Verificar se todas as páginas PHP incluem o header corretamente
        $php_files = glob($this->root_path . '/*.php');
        foreach ($php_files as $file) {
            $content = file_get_contents($file);
            $filename = basename($file);
            
            if (strpos($content, 'include') !== false) {
                if (strpos($content, "includes/header.php") !== false) {
                    $this->checks[] = "✅ {$filename} inclui header";
                } else {
                    $this->warnings[] = "⚠️ {$filename} não inclui header corretamente";
                }
            }
        }
        
        echo "   Arquivos verificados: " . count($php_files) . "\n\n";
    }
    
    private function checkDatabaseConnection() {
        echo "🗄️ Verificando conexão com banco de dados...\n";
        
        try {
            require_once $this->root_path . '/conexx/config.php';
            
            if (isset($conn) && $conn->ping()) {
                $this->checks[] = "✅ Conexão com banco ativa";
                
                // Verificar tabelas essenciais
                $tables = ['materiais', 'clientes', 'vendas', 'compras', 'configuracoes'];
                foreach ($tables as $table) {
                    $result = $conn->query("SHOW TABLES LIKE '{$table}'");
                    if ($result->num_rows > 0) {
                        $this->checks[] = "✅ Tabela {$table} existe";
                    } else {
                        $this->errors[] = "❌ Tabela {$table} não encontrada";
                    }
                }
                
                // Verificar índices de performance
                $indexes = [
                    'materiais' => 'idx_materiais_empresa',
                    'clientes' => 'idx_clientes_empresa'
                ];
                
                foreach ($indexes as $table => $index) {
                    $result = $conn->query("SHOW INDEX FROM {$table} WHERE Key_name = '{$index}'");
                    if ($result->num_rows > 0) {
                        $this->checks[] = "✅ Índice {$index} configurado";
                    } else {
                        $this->warnings[] = "⚠️ Índice {$index} recomendado para performance";
                    }
                }
                
            } else {
                $this->errors[] = "❌ Falha na conexão com banco de dados";
            }
        } catch (Exception $e) {
            $this->errors[] = "❌ Erro ao conectar banco: " . $e->getMessage();
        }
        
        echo "   Banco verificado\n\n";
    }
    
    private function checkCSSConsistency() {
        echo "🎨 Verificando consistência do CSS...\n";
        
        $css_file = $this->root_path . '/css/responsive-mobile.css';
        if (file_exists($css_file)) {
            $css_content = file_get_contents($css_file);
            
            // Verificar classes essenciais
            $required_classes = [
                '.section-card',
                '.table-responsive', 
                '.btn-responsive',
                '@media (max-width: 768px)'
            ];
            
            foreach ($required_classes as $class) {
                if (strpos($css_content, $class) !== false) {
                    $this->checks[] = "✅ Classe {$class} definida";
                } else {
                    $this->warnings[] = "⚠️ Classe {$class} não encontrada no CSS";
                }
            }
            
            // Verificar tema escuro
            if (strpos($css_content, '--bs-body-bg') !== false) {
                $this->checks[] = "✅ Suporte a tema escuro implementado";
            } else {
                $this->warnings[] = "⚠️ Variáveis de tema escuro podem estar faltando";
            }
            
        } else {
            $this->errors[] = "❌ Arquivo CSS responsivo não encontrado";
        }
        
        echo "   CSS verificado\n\n";
    }
    
    private function checkJavaScriptErrors() {
        echo "⚡ Verificando JavaScript...\n";
        
        $js_file = $this->root_path . '/js/erp-utils.js';
        if (file_exists($js_file)) {
            $js_content = file_get_contents($js_file);
            
            // Verificar funções essenciais
            $required_functions = [
                'validateForm',
                'showToast',
                'formatCurrency',
                'applyCPFMask',
                'applyCNPJMask'
            ];
            
            foreach ($required_functions as $func) {
                if (strpos($js_content, $func) !== false) {
                    $this->checks[] = "✅ Função {$func} implementada";
                } else {
                    $this->warnings[] = "⚠️ Função {$func} pode estar faltando";
                }
            }
            
            // Verificar sintaxe básica JavaScript
            $syntax_errors = [];
            
            // Verificar parênteses balanceados
            $open_parens = substr_count($js_content, '(');
            $close_parens = substr_count($js_content, ')');
            if ($open_parens !== $close_parens) {
                $syntax_errors[] = "Parênteses desbalanceados";
            }
            
            // Verificar chaves balanceadas
            $open_braces = substr_count($js_content, '{');
            $close_braces = substr_count($js_content, '}');
            if ($open_braces !== $close_braces) {
                $syntax_errors[] = "Chaves desbalanceadas";
            }
            
            if (empty($syntax_errors)) {
                $this->checks[] = "✅ Sintaxe JavaScript básica OK";
            } else {
                foreach ($syntax_errors as $error) {
                    $this->errors[] = "❌ JavaScript: {$error}";
                }
            }
            
        } else {
            $this->errors[] = "❌ Arquivo JavaScript utilitário não encontrado";
        }
        
        echo "   JavaScript verificado\n\n";
    }
    
    private function checkSecurityHeaders() {
        echo "🔒 Verificando configurações de segurança...\n";
        
        // Verificar se arquivos de segurança existem
        $security_file = $this->root_path . '/includes/security_functions.php';
        if (file_exists($security_file)) {
            $this->checks[] = "✅ Arquivo de funções de segurança existe";
            
            $content = file_get_contents($security_file);
            if (strpos($content, 'validateCNPJ') !== false) {
                $this->checks[] = "✅ Validação CNPJ implementada";
            }
            if (strpos($content, 'sanitizeInput') !== false) {
                $this->checks[] = "✅ Sanitização de entrada implementada";
            }
        } else {
            $this->warnings[] = "⚠️ Arquivo de segurança recomendado";
        }
        
        // Verificar prepared statements em arquivos críticos
        $critical_files = ['salvar_venda.php', 'salvar_compra.php', 'cadastro_clientes.php'];
        foreach ($critical_files as $file) {
            $full_path = $this->root_path . '/' . $file;
            if (file_exists($full_path)) {
                $content = file_get_contents($full_path);
                if (strpos($content, 'prepare') !== false) {
                    $this->checks[] = "✅ {$file} usa prepared statements";
                } else {
                    $this->warnings[] = "⚠️ {$file} deveria usar prepared statements";
                }
            }
        }
        
        echo "   Segurança verificada\n\n";
    }
    
    private function checkMobileResponsiveness() {
        echo "📱 Verificando responsividade móvel...\n";
        
        $responsive_indicators = 0;
        $total_pages = 0;
        
        $php_files = glob($this->root_path . '/*.php');
        foreach ($php_files as $file) {
            if (in_array(basename($file), ['login.php', 'logout.php'])) continue;
            
            $content = file_get_contents($file);
            $total_pages++;
            
            $has_responsive = false;
            
            // Verificar viewport meta tag
            if (strpos($content, 'viewport') !== false) {
                $has_responsive = true;
            }
            
            // Verificar classes responsivas
            if (strpos($content, 'section-card') !== false || 
                strpos($content, 'table-responsive') !== false) {
                $has_responsive = true;
            }
            
            // Verificar CSS responsivo
            if (strpos($content, 'responsive-mobile.css') !== false) {
                $has_responsive = true;
            }
            
            if ($has_responsive) {
                $responsive_indicators++;
                $this->checks[] = "✅ " . basename($file) . " tem indicadores de responsividade";
            } else {
                $this->warnings[] = "⚠️ " . basename($file) . " pode não estar responsivo";
            }
        }
        
        $percentage = $total_pages > 0 ? round(($responsive_indicators / $total_pages) * 100) : 0;
        
        if ($percentage >= 80) {
            $this->checks[] = "✅ {$percentage}% das páginas são responsivas";
        } else {
            $this->warnings[] = "⚠️ Apenas {$percentage}% das páginas são responsivas";
        }
        
        echo "   Responsividade verificada: {$percentage}%\n\n";
    }
    
    private function checkPerformance() {
        echo "⚡ Verificando performance...\n";
        
        // Verificar tamanho dos arquivos CSS/JS
        $css_file = $this->root_path . '/css/responsive-mobile.css';
        if (file_exists($css_file)) {
            $size = filesize($css_file) / 1024; // KB
            if ($size < 100) {
                $this->checks[] = "✅ CSS responsivo otimizado ({$size}KB)";
            } else {
                $this->warnings[] = "⚠️ CSS pode estar grande ({$size}KB)";
            }
        }
        
        $js_file = $this->root_path . '/js/erp-utils.js';
        if (file_exists($js_file)) {
            $size = filesize($js_file) / 1024; // KB
            if ($size < 50) {
                $this->checks[] = "✅ JavaScript otimizado ({$size}KB)";
            } else {
                $this->warnings[] = "⚠️ JavaScript pode estar grande ({$size}KB)";
            }
        }
        
        // Verificar cache de configurações
        if (function_exists('opcache_get_status')) {
            $status = opcache_get_status();
            if ($status !== false && $status['opcache_enabled']) {
                $this->checks[] = "✅ OPCache ativo";
            } else {
                $this->warnings[] = "⚠️ OPCache recomendado para performance";
            }
        }
        
        echo "   Performance verificada\n\n";
    }
    
    private function generateReport() {
        echo "📊 RELATÓRIO DE INTEGRIDADE\n";
        echo "===============================\n\n";
        
        echo "✅ VERIFICAÇÕES APROVADAS (" . count($this->checks) . "):\n";
        foreach ($this->checks as $check) {
            echo "   {$check}\n";
        }
        echo "\n";
        
        if (!empty($this->warnings)) {
            echo "⚠️ AVISOS (" . count($this->warnings) . "):\n";
            foreach ($this->warnings as $warning) {
                echo "   {$warning}\n";
            }
            echo "\n";
        }
        
        if (!empty($this->errors)) {
            echo "❌ ERROS CRÍTICOS (" . count($this->errors) . "):\n";
            foreach ($this->errors as $error) {
                echo "   {$error}\n";
            }
            echo "\n";
        }
        
        // Score de saúde
        $total = count($this->checks) + count($this->warnings) + count($this->errors);
        $score = $total > 0 ? round((count($this->checks) / $total) * 100) : 0;
        
        echo "🎯 SCORE DE SAÚDE: {$score}%\n";
        
        if ($score >= 90) {
            echo "   🎉 Excelente! Sistema em ótimas condições.\n";
        } elseif ($score >= 70) {
            echo "   👍 Bom! Algumas melhorias recomendadas.\n";
        } elseif ($score >= 50) {
            echo "   ⚠️ Atenção! Várias melhorias necessárias.\n";
        } else {
            echo "   🚨 Crítico! Correções urgentes necessárias.\n";
        }
        
        echo "\n📅 Verificação realizada em: " . date('d/m/Y H:i:s') . "\n";
        echo "🔄 Execute novamente após aplicar correções.\n";
    }
}

// Executar verificação se chamado diretamente
if (basename($_SERVER['SCRIPT_NAME']) === 'verificar_integridade.php') {
    $checker = new ERPHealthChecker();
    $checker->runAllChecks();
}

?>
