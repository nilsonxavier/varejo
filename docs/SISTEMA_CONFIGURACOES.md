# Sistema de Configurações - Manual de Implementação

## Visão Geral

O sistema de configurações permite que cada empresa tenha suas próprias configurações de:
- **Tema**: Claro ou Escuro
- **Tamanho de Papel**: A4, 80mm, ou 60mm (para impressão)
- **Dados da Empresa**: Informações completas da empresa

## Arquivos Criados/Modificados

### Novos Arquivos:
- `configuracoes.php` - Página principal de configurações
- `includes/configuracoes_globais.php` - Sistema de carregamento automático
- `js/configuracoes.js` - Funcionalidades JavaScript
- `setup_configuracoes.php` - Script de instalação do banco
- `exemplo_configuracoes.php` - Página de demonstração

### Arquivos Modificados:
- `includes/header.php` - Inclui configurações automaticamente
- `includes/navbar.php` - Adiciona link para configurações
- `db/migrations/002_add_configuracoes_table.sql` - Migração do banco

## Como Usar nas Páginas

### 1. Automático (Recomendado)
Todas as páginas que incluem `header.php` já recebem automaticamente:
- Estilos do tema (claro/escuro)
- Configurações de impressão
- Variáveis PHP globais: `$TEMA_DARK` e `$TAMANHO_PAPEL`

### 2. Classes CSS Importantes

#### `.section-card`
Use para seções que devem ser impressas:
```html
<div class="section-card">
    <h4>Conteúdo Principal</h4>
    <!-- Este conteúdo será impresso -->
</div>
```

#### `.no-print`
Use para elementos que NÃO devem ser impressos:
```html
<div class="no-print">
    <button class="btn btn-primary">Botão não imprime</button>
</div>
```

#### `.print-area`
Use para marcar áreas específicas de impressão:
```html
<div class="section-card print-area">
    <!-- Área otimizada para impressão -->
</div>
```

### 3. Funções JavaScript Disponíveis

#### `imprimirPagina()`
Imprime a página respeitando as configurações:
```javascript
// Botão de impressão
<button onclick="imprimirPagina()">Imprimir</button>
```

#### `sistemaConfiguracoes.adicionarBotaoImpressao()`
Adiciona automaticamente um botão de impressão:
```javascript
// Adiciona botão na primeira .section-card
sistemaConfiguracoes.adicionarBotaoImpressao();

// Adiciona botão em container específico
sistemaConfiguracoes.adicionarBotaoImpressao('meuContainer');
```

#### `sistemaConfiguracoes.aplicarConfiguracaoImpressao(tamanho)`
Aplica configuração de impressão específica:
```javascript
// Força tamanho 80mm
sistemaConfiguracoes.aplicarConfiguracaoImpressao('80mm');
```

### 4. Variáveis PHP Globais

```php
// Verificar se tema está escuro
if ($TEMA_DARK) {
    echo "Modo escuro ativado";
}

// Verificar tamanho do papel
if ($TAMANHO_PAPEL === '80mm') {
    echo "Impressão em formato cupom";
}
```

### 5. Configurações de Impressão por Tamanho

#### A4 (Padrão)
- Formato: 210x297mm
- Margens: 15mm
- Uso: Relatórios completos, documentos

#### 80mm (Cupom Fiscal)
- Formato: 80mm de largura
- Fonte: 12px
- Uso: Cupons, recibos, vendas

#### 60mm (Cupom Pequeno)
- Formato: 60mm de largura  
- Fonte: 10px
- Uso: Cupons compactos, etiquetas

## Exemplo de Implementação Completa

```php
<?php
require_once 'verifica_login.php';
require_once 'conexx/config.php';
include __DIR__.'/includes/header.php';
include __DIR__.'/includes/navbar.php';
?>

<div class="container py-4">
    <!-- Cabeçalho com botão de impressão -->
    <div class="section-card print-area">
        <div class="no-print d-flex justify-content-between align-items-center mb-3">
            <h2>Minha Página</h2>
            <button class="btn btn-outline-primary" onclick="imprimirPagina()">
                <i class="bi bi-printer"></i> Imprimir
            </button>
        </div>
        
        <!-- Conteúdo que será impresso -->
        <div class="table-responsive">
            <table class="table">
                <!-- dados da tabela -->
            </table>
        </div>
    </div>
    
    <!-- Controles que não devem ser impressos -->
    <div class="section-card no-print">
        <h4>Controles</h4>
        <button class="btn btn-primary">Novo Item</button>
        <button class="btn btn-secondary">Editar</button>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Código específico da página
    console.log('Tema atual:', <?php echo json_encode($TEMA_DARK); ?> ? 'escuro' : 'claro');
});
</script>

<?php include __DIR__.'/includes/footer.php'; ?>
```

## Atalhos de Teclado

- **Ctrl+P**: Imprimir página (respeitando configurações)

## Personalização de Tema

### Variáveis CSS Disponíveis (Tema Escuro)
```css
:root {
    --bs-body-bg: #1a1a1a;           /* Fundo principal */
    --bs-body-color: #ffffff;         /* Texto principal */
    --bs-secondary-bg: #2d2d2d;      /* Fundo secundário */
    --bs-tertiary-bg: #404040;       /* Fundo terciário */
    --bs-border-color: #495057;      /* Bordas */
    --bs-secondary-color: #adb5bd;   /* Texto secundário */
}
```

## Troubleshooting

### Problema: Configurações não carregam
**Solução**: Verifique se `header.php` está sendo incluído e se a tabela `configuracoes` existe no banco.

### Problema: Impressão não respeita configurações
**Solução**: Certifique-se de usar `imprimirPagina()` em vez de `window.print()`.

### Problema: Tema escuro não aplica
**Solução**: Verifique se não há CSS conflitante e se `configuracoes_globais.php` está sendo carregado.

## Próximos Passos

1. Aplicar o sistema em todas as páginas importantes
2. Testar impressão em diferentes tamanhos
3. Ajustar estilos específicos conforme necessário
4. Adicionar mais opções de configuração se necessário

## Páginas Prioritárias para Aplicar

- [x] `configuracoes.php` - Página principal ✓
- [ ] `venda.php` - PDV Vendas
- [ ] `compra.php` - PDV Compras  
- [ ] `historico_vendas.php` - Relatórios
- [ ] `historico_compras.php` - Relatórios
- [ ] `extrato_cliente.php` - Extratos
- [ ] `estoque.php` - Controle de estoque
- [ ] `caixa.php` - Controle de caixa
