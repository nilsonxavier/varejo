# Sistema de Configurações - Guia Rápido de Implementação

## ✅ O que já está funcionando

1. **Tabela de configurações criada no banco**
2. **Sistema automático de carregamento** via `header.php`
3. **Página de configurações completa** (`configuracoes.php`)
4. **Exemplo funcional** (`exemplo_configuracoes.php` e `historico_vendas.php`)

## 🚀 Como aplicar em qualquer página existente

### Passo 1: Verificar se a página já inclui header.php
```php
include __DIR__.'/includes/header.php';
```
✅ Se sim, as configurações já estão carregadas automaticamente!

### Passo 2: Aplicar classes CSS nas seções

#### Para conteúdo que deve ser impresso:
```html
<!-- ANTES -->
<div class="container">
    <h2>Meu Relatório</h2>
    <table>...</table>
</div>

<!-- DEPOIS -->
<div class="container">
    <div class="section-card print-area">
        <div class="no-print d-flex justify-content-between align-items-center mb-3">
            <h2>Meu Relatório</h2>
            <button class="btn btn-outline-primary" onclick="imprimirPagina()">
                <i class="bi bi-printer"></i> Imprimir
            </button>
        </div>
        
        <table class="table">...</table>
    </div>
</div>
```

#### Para botões e controles que NÃO devem ser impressos:
```html
<!-- Adicione a classe .no-print -->
<div class="no-print">
    <button class="btn btn-primary">Editar</button>
    <button class="btn btn-danger">Excluir</button>
</div>
```

### Passo 3: Remover estilos customizados conflitantes

#### ❌ REMOVER estilos que podem conflitar:
```html
<!-- REMOVER estas linhas do <head> -->
<style>
    body { background: white; }
    .card { background: #f8f9fa; }
</style>
```

#### ✅ As configurações aplicam automaticamente:
- Tema claro/escuro
- Configurações de impressão
- Responsividade

## 📋 Checklist para cada página

### Páginas de Relatório/Listagem:
- [ ] Adicionar classe `.section-card .print-area` na seção principal
- [ ] Adicionar botão de impressão com `.no-print`
- [ ] Marcar controles com `.no-print`
- [ ] Testar impressão nos 3 formatos (A4, 80mm, 60mm)

### Páginas de Formulário/Cadastro:
- [ ] Usar `.section-card` para seções do formulário
- [ ] Manter botões de ação visíveis (não usar .no-print)
- [ ] Testar tema escuro nos campos de formulário

### Páginas de Dashboard/Gráficos:
- [ ] Usar `.section-card` para cada widget
- [ ] Adicionar `.no-print` em controles de navegação
- [ ] Verificar se gráficos funcionam no tema escuro

## 🔧 Páginas prioritárias para atualizar

### Já atualizadas ✅
- `configuracoes.php` - Página principal
- `historico_vendas.php` - Exemplo de relatório
- `exemplo_configuracoes.php` - Demonstração

### Para atualizar:

#### Alta prioridade 🔴
- `venda.php` - PDV mais usado
- `compra.php` - PDV de compras (já tem as configurações carregadas)
- `caixa.php` - Controle financeiro
- `estoque.php` - Controle de produtos

#### Média prioridade 🟡
- `cadastro_clientes.php` - Gestão de clientes
- `cadastro_materiais.php` - Gestão de produtos
- `extrato_cliente.php` - Relatórios de clientes

#### Baixa prioridade 🟢
- `index.php` - Dashboard principal
- `gerenciar_usuarios.php` - Administração
- `gerenciar_empresas.php` - Administração

## 🛠️ Comandos úteis para desenvolvimento

### Testar impressão:
```javascript
// No console do navegador
imprimirPagina();
```

### Verificar configurações atuais:
```javascript
// No console do navegador
console.log('Tema escuro:', document.querySelector(':root').style.getPropertyValue('--bs-body-bg') === '#1a1a1a');
```

### Força um tamanho de papel específico:
```javascript
// No console do navegador
sistemaConfiguracoes.aplicarConfiguracaoImpressao('80mm');
```

## 📱 Atalhos de teclado

- **Ctrl + P**: Imprimir respeitando configurações
- **F12 → Console**: Debugar configurações

## 🎨 Exemplo de página completa

```php
<?php
require_once 'verifica_login.php';
require_once 'conexx/config.php';
include __DIR__.'/includes/header.php';
include __DIR__.'/includes/navbar.php';
?>

<div class="container py-4">
    <!-- Seção principal (será impressa) -->
    <div class="section-card print-area">
        <!-- Cabeçalho com botão (botão não será impresso) -->
        <div class="no-print d-flex justify-content-between align-items-center mb-3">
            <h2><i class="bi bi-graph-up"></i> Meu Relatório</h2>
            <button class="btn btn-outline-primary" onclick="imprimirPagina()">
                <i class="bi bi-printer"></i> Imprimir
            </button>
        </div>
        
        <!-- Conteúdo que será impresso -->
        <div class="table-responsive">
            <table class="table table-striped">
                <thead class="table-dark">
                    <tr>
                        <th>Coluna 1</th>
                        <th>Coluna 2</th>
                        <th class="no-print">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>Dado 1</td>
                        <td>Dado 2</td>
                        <td class="no-print">
                            <button class="btn btn-sm btn-outline-primary">Editar</button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
    
    <!-- Controles (não serão impressos) -->
    <div class="section-card no-print">
        <h4>Controles</h4>
        <button class="btn btn-primary">Novo Item</button>
        <button class="btn btn-secondary">Filtrar</button>
    </div>
</div>

<?php include __DIR__.'/includes/footer.php'; ?>
```

## 🎯 Resultado esperado

Após aplicar o sistema, cada página terá automaticamente:

1. **Tema escuro/claro** conforme configuração da empresa
2. **Impressão otimizada** para A4, 80mm ou 60mm
3. **Botões de impressão** funcionais
4. **Layout responsivo** que funciona em qualquer dispositivo
5. **Experiência consistente** em todo o sistema

---

**🚀 Pronto para usar!** O sistema está configurado e funcionando. Agora é só aplicar nas páginas seguindo este guia.
