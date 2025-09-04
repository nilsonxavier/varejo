// Funções globais para o sistema de configurações

// Função para imprimir com base nas configurações
function imprimirPagina() {
    // Remove elementos que não devem ser impressos
    const elementosNoprint = document.querySelectorAll('.no-print, .navbar, .btn:not(.btn-print)');
    elementosNoprint.forEach(el => {
        el.style.display = 'none';
    });
    
    // Executa a impressão
    window.print();
    
    // Restaura elementos após impressão
    setTimeout(() => {
        elementosNoprint.forEach(el => {
            el.style.display = '';
        });
    }, 100);
}

// Função para detectar mudança de tema
function detectarMudancaTema() {
    const observer = new MutationObserver((mutations) => {
        mutations.forEach((mutation) => {
            if (mutation.type === 'attributes' && mutation.attributeName === 'class') {
                // Atualizar componentes específicos se necessário
                atualizarComponentesTema();
            }
        });
    });
    
    observer.observe(document.body, {
        attributes: true,
        attributeFilter: ['class']
    });
}

// Função para atualizar componentes baseados no tema
function atualizarComponentesTema() {
    // Verificar se está no tema escuro
    const isDark = document.querySelector(':root').style.getPropertyValue('--bs-body-bg') === '#1a1a1a';
    
    // Atualizar charts se existirem
    if (typeof Chart !== 'undefined' && Chart.instances) {
        Chart.instances.forEach(chart => {
            if (chart && chart.options) {
                // Atualizar cores do gráfico baseado no tema
                const textColor = isDark ? '#ffffff' : '#333333';
                const gridColor = isDark ? '#495057' : '#dee2e6';
                
                if (chart.options.plugins && chart.options.plugins.legend) {
                    chart.options.plugins.legend.labels.color = textColor;
                }
                
                if (chart.options.scales) {
                    Object.keys(chart.options.scales).forEach(scaleKey => {
                        const scale = chart.options.scales[scaleKey];
                        if (scale.ticks) scale.ticks.color = textColor;
                        if (scale.grid) scale.grid.color = gridColor;
                    });
                }
                
                chart.update();
            }
        });
    }
    
    // Atualizar Select2 se existir
    if (typeof $ !== 'undefined' && $.fn.select2) {
        $('.select2-container').each(function() {
            const $select = $(this).prev('select');
            if ($select.length) {
                $select.select2('destroy').select2({
                    theme: isDark ? 'bootstrap-5-dark' : 'bootstrap-5',
                    width: '100%'
                });
            }
        });
    }
}

// Função para aplicar configurações de impressão via JavaScript
function aplicarConfiguracaoImpressao(tamanhoPapel = 'A4') {
    // Remove estilos de impressão existentes
    const existingStyle = document.getElementById('print-config-style');
    if (existingStyle) {
        existingStyle.remove();
    }
    
    // Cria novo estilo de impressão
    const style = document.createElement('style');
    style.id = 'print-config-style';
    style.media = 'print';
    
    let css = '';
    
    switch (tamanhoPapel) {
        case '80mm':
            css = `
                @page { size: 80mm auto; margin: 5mm; }
                body { font-size: 12px; line-height: 1.3; width: 70mm; }
                .container, .container-fluid { width: 100% !important; max-width: none !important; padding: 0 !important; margin: 0 !important; }
                .no-print, .navbar, .btn:not(.btn-print), nav, footer { display: none !important; }
                h1, h2, h3, h4, h5, h6 { font-size: 14px !important; margin: 3px 0 !important; }
                table { font-size: 11px !important; width: 100% !important; }
            `;
            break;
        case '60mm':
            css = `
                @page { size: 60mm auto; margin: 3mm; }
                body { font-size: 10px; line-height: 1.2; width: 54mm; }
                .container, .container-fluid { width: 100% !important; max-width: none !important; padding: 0 !important; margin: 0 !important; }
                .no-print, .navbar, .btn:not(.btn-print), nav, footer { display: none !important; }
                h1, h2, h3, h4, h5, h6 { font-size: 12px !important; margin: 2px 0 !important; }
                table { font-size: 9px !important; width: 100% !important; }
            `;
            break;
        default: // A4
            css = `
                @page { size: A4; margin: 15mm; }
                .no-print { display: none !important; }
                .section-card { box-shadow: none !important; break-inside: avoid; }
                table { break-inside: auto; }
                tr { break-inside: avoid; break-after: auto; }
            `;
            break;
    }
    
    style.textContent = css;
    document.head.appendChild(style);
}

// Função para criar botão de impressão automático
function adicionarBotaoImpressao(containerId = null) {
    const container = containerId ? document.getElementById(containerId) : document.querySelector('.section-card');
    
    if (container && !container.querySelector('.btn-print')) {
        const btnGroup = document.createElement('div');
        btnGroup.className = 'no-print d-flex justify-content-end mb-3';
        
        const btnImprimir = document.createElement('button');
        btnImprimir.className = 'btn btn-outline-secondary btn-sm btn-print';
        btnImprimir.innerHTML = '<i class="bi bi-printer"></i> Imprimir';
        btnImprimir.onclick = imprimirPagina;
        
        btnGroup.appendChild(btnImprimir);
        container.insertBefore(btnGroup, container.firstChild);
    }
}

// Função para salvar configurações via AJAX
function salvarConfiguracoes(dados, callback) {
    if (typeof $ !== 'undefined') {
        $.ajax({
            url: 'configuracoes.php',
            method: 'POST',
            data: dados,
            dataType: 'json',
            success: function(response) {
                if (callback) callback(response);
                // Recarregar página para aplicar novas configurações
                setTimeout(() => {
                    location.reload();
                }, 1000);
            },
            error: function(xhr, status, error) {
                console.error('Erro ao salvar configurações:', error);
                if (callback) callback({ success: false, message: 'Erro ao salvar configurações' });
            }
        });
    }
}

// Inicialização automática quando DOM estiver pronto
document.addEventListener('DOMContentLoaded', function() {
    // Detectar mudanças de tema
    detectarMudancaTema();
    
    // Atualizar componentes do tema
    atualizarComponentesTema();
    
    // Adicionar botões de impressão em páginas relevantes
    if (window.location.pathname.includes('historico_') || 
        window.location.pathname.includes('relatorio') ||
        window.location.pathname.includes('extrato')) {
        adicionarBotaoImpressao();
    }
    
    // Configurar atalhos de teclado
    document.addEventListener('keydown', function(e) {
        // Ctrl+P para imprimir
        if (e.ctrlKey && e.key === 'p') {
            e.preventDefault();
            imprimirPagina();
        }
        
        // Ctrl+Shift+D para alternar tema (apenas para desenvolvedores)
        if (e.ctrlKey && e.shiftKey && e.key === 'D') {
            e.preventDefault();
            // Esta funcionalidade pode ser implementada se necessário
            console.log('Atalho para alternar tema detectado');
        }
    });
});

// Exportar funções para uso global
window.sistemaConfiguracoes = {
    imprimirPagina,
    aplicarConfiguracaoImpressao,
    adicionarBotaoImpressao,
    salvarConfiguracoes,
    atualizarComponentesTema
};
