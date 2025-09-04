<?php
// Arquivo para carregar configurações globais do sistema
if (!isset($_SESSION)) {
    session_start();
}

// Função para buscar configurações da empresa
function buscarConfiguracoes($conn, $empresa_id) {
    static $configuracoes_cache = null;
    static $empresa_cache = null;
    
    // Cache para evitar múltiplas consultas na mesma requisição
    if ($configuracoes_cache !== null && $empresa_cache == $empresa_id) {
        return $configuracoes_cache;
    }
    
    try {
        $stmt = $conn->prepare("SELECT * FROM configuracoes WHERE empresa_id = ?");
        $stmt->bind_param('i', $empresa_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result && $result->num_rows > 0) {
            $configuracoes_cache = $result->fetch_assoc();
            $empresa_cache = $empresa_id;
        } else {
            // Criar configurações padrão se não existir
            $stmt_insert = $conn->prepare("INSERT IGNORE INTO configuracoes (empresa_id, tamanho_papel, tema_dark) VALUES (?, 'A4', 0)");
            $stmt_insert->bind_param('i', $empresa_id);
            $stmt_insert->execute();
            
            // Buscar novamente
            $stmt = $conn->prepare("SELECT * FROM configuracoes WHERE empresa_id = ?");
            $stmt->bind_param('i', $empresa_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $configuracoes_cache = $result ? $result->fetch_assoc() : [
                'tamanho_papel' => 'A4',
                'tema_dark' => 0
            ];
            $empresa_cache = $empresa_id;
        }
        
        return $configuracoes_cache;
    } catch (Exception $e) {
        // Retorna configurações padrão em caso de erro
        return [
            'tamanho_papel' => 'A4',
            'tema_dark' => 0
        ];
    }
}

// Buscar configurações se estiver logado
$configuracoes_sistema = [
    'tamanho_papel' => 'A4',
    'tema_dark' => 0
];

if (isset($_SESSION['usuario_empresa']) && isset($conn)) {
    $configuracoes_sistema = buscarConfiguracoes($conn, $_SESSION['usuario_empresa']);
}

// Função para obter CSS do tema
function obterCssTema($tema_dark = false) {
    if ($tema_dark) {
        return "
        <style>
        :root {
            --bs-body-bg: #1a1a1a;
            --bs-body-color: #ffffff;
            --bs-secondary-bg: #2d2d2d;
            --bs-tertiary-bg: #404040;
            --bs-border-color: #495057;
            --bs-secondary-color: #adb5bd;
        }
        
        body {
            background-color: var(--bs-body-bg) !important;
            color: var(--bs-body-color) !important;
        }
        
        .section-card, .card {
            background-color: var(--bs-secondary-bg) !important;
            border-color: var(--bs-border-color) !important;
            color: var(--bs-body-color) !important;
        }
        
        .form-control, .form-select {
            background-color: var(--bs-tertiary-bg) !important;
            border-color: var(--bs-border-color) !important;
            color: var(--bs-body-color) !important;
        }
        
        .form-control:focus, .form-select:focus {
            background-color: var(--bs-tertiary-bg) !important;
            border-color: #86b7fe !important;
            color: var(--bs-body-color) !important;
            box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25) !important;
        }
        
        .table {
            --bs-table-bg: var(--bs-secondary-bg);
            --bs-table-striped-bg: var(--bs-tertiary-bg);
            color: var(--bs-body-color) !important;
        }
        
        .table th, .table td {
            border-color: var(--bs-border-color) !important;
        }
        
        .modal-content {
            background-color: var(--bs-secondary-bg) !important;
            border-color: var(--bs-border-color) !important;
        }
        
        .modal-header, .modal-footer {
            border-color: var(--bs-border-color) !important;
        }
        
        .dropdown-menu {
            background-color: var(--bs-secondary-bg) !important;
            border-color: var(--bs-border-color) !important;
        }
        
        .dropdown-item {
            color: var(--bs-body-color) !important;
        }
        
        .dropdown-item:hover, .dropdown-item:focus {
            background-color: var(--bs-tertiary-bg) !important;
            color: var(--bs-body-color) !important;
        }
        
        .alert-success {
            background-color: #0f2027 !important;
            border-color: #0a6847 !important;
            color: #75b798 !important;
        }
        
        .alert-danger {
            background-color: #2c0b0e !important;
            border-color: #842029 !important;
            color: #ea868f !important;
        }
        
        .alert-warning {
            background-color: #332701 !important;
            border-color: #997404 !important;
            color: #ffda6a !important;
        }
        
        .alert-info {
            background-color: #055160 !important;
            border-color: #087990 !important;
            color: #6edff6 !important;
        }
        
        .btn-outline-primary, .btn-outline-secondary, .btn-outline-success, 
        .btn-outline-danger, .btn-outline-warning, .btn-outline-info {
            border-color: currentColor !important;
        }
        
        .list-group-item {
            background-color: var(--bs-secondary-bg) !important;
            border-color: var(--bs-border-color) !important;
            color: var(--bs-body-color) !important;
        }
        
        .list-group-item:hover, .list-group-item.active {
            background-color: var(--bs-tertiary-bg) !important;
        }
        
        /* Scrollbar personalizada para tema escuro */
        ::-webkit-scrollbar {
            width: 8px;
            height: 8px;
        }
        
        ::-webkit-scrollbar-track {
            background: var(--bs-secondary-bg);
        }
        
        ::-webkit-scrollbar-thumb {
            background: var(--bs-border-color);
            border-radius: 4px;
        }
        
        ::-webkit-scrollbar-thumb:hover {
            background: #6c757d;
        }
        </style>
        ";
    }
    
    return ""; // Tema claro usa estilos padrão do Bootstrap
}

// Função para obter CSS de impressão baseado no tamanho do papel
function obterCssImpressao($tamanho_papel = 'A4') {
    $css = "<style media='print'>";
    
    switch ($tamanho_papel) {
        case '80mm':
            $css .= "
            @page {
                size: 80mm auto;
                margin: 5mm;
            }
            
            body {
                font-size: 12px;
                line-height: 1.3;
                width: 70mm;
            }
            
            .container, .container-fluid {
                width: 100% !important;
                max-width: none !important;
                padding: 0 !important;
                margin: 0 !important;
            }
            
            .no-print, .navbar, .btn, .section-card .d-flex,
            .modal, .dropdown, nav, footer {
                display: none !important;
            }
            
            .section-card {
                box-shadow: none !important;
                border: none !important;
                padding: 5px !important;
                margin: 0 !important;
            }
            
            h1, h2, h3, h4, h5, h6 {
                font-size: 14px !important;
                margin: 3px 0 !important;
            }
            
            table {
                font-size: 11px !important;
                width: 100% !important;
            }
            
            .row, .col, .col-md-12 {
                width: 100% !important;
                margin: 0 !important;
                padding: 0 !important;
            }
            ";
            break;
            
        case '60mm':
            $css .= "
            @page {
                size: 60mm auto;
                margin: 3mm;
            }
            
            body {
                font-size: 10px;
                line-height: 1.2;
                width: 54mm;
            }
            
            .container, .container-fluid {
                width: 100% !important;
                max-width: none !important;
                padding: 0 !important;
                margin: 0 !important;
            }
            
            .no-print, .navbar, .btn, .section-card .d-flex,
            .modal, .dropdown, nav, footer {
                display: none !important;
            }
            
            .section-card {
                box-shadow: none !important;
                border: none !important;
                padding: 3px !important;
                margin: 0 !important;
            }
            
            h1, h2, h3, h4, h5, h6 {
                font-size: 12px !important;
                margin: 2px 0 !important;
            }
            
            table {
                font-size: 9px !important;
                width: 100% !important;
            }
            
            .row, .col, .col-md-12 {
                width: 100% !important;
                margin: 0 !important;
                padding: 0 !important;
            }
            ";
            break;
            
        default: // A4
            $css .= "
            @page {
                size: A4;
                margin: 15mm;
            }
            
            .no-print, .navbar .dropdown, .btn.no-print {
                display: none !important;
            }
            
            .section-card {
                box-shadow: none !important;
                break-inside: avoid;
            }
            
            table {
                break-inside: auto;
            }
            
            tr {
                break-inside: avoid;
                break-after: auto;
            }
            ";
            break;
    }
    
    $css .= "</style>";
    return $css;
}

// Definir configurações como variáveis globais para uso nas páginas
$TEMA_DARK = isset($configuracoes_sistema['tema_dark']) ? (bool)$configuracoes_sistema['tema_dark'] : false;
$TAMANHO_PAPEL = isset($configuracoes_sistema['tamanho_papel']) ? $configuracoes_sistema['tamanho_papel'] : 'A4';

// Função para incluir automaticamente os estilos nas páginas
function incluirEstilosConfiguracoes() {
    global $TEMA_DARK, $TAMANHO_PAPEL;
    
    echo obterCssTema($TEMA_DARK);
    echo obterCssImpressao($TAMANHO_PAPEL);
}
?>
