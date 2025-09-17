# 🌱 Sistema ERP para Empresa de Reciclagem v2.0

Sistema ERP completo e **mobile-first** desenvolvido em PHP para empresas de reciclagem, com foco na sustentabilidade e facilidade de uso em dispositivos móveis.

![Version](https://img.shields.io/badge/version-2.0-green)
![PHP](https://img.shields.io/badge/PHP-7.4%2B-blue)
![License](https://img.shields.io/badge/license-MIT-blue)
![Mobile](https://img.shields.io/badge/Mobile-Responsive-brightgreen)

## 🚀 Novidades da Versão 2.0

### ✨ **Interface Mobile-First**
- Design completamente responsivo
- Navegação otimizada para dispositivos móveis
- Tema escuro/claro personalizável
- Interface intuitiva com feedback visual

### 🔧 **Sistema de Configurações**
- Configuração centralizada de papel de impressão (A4, 80mm, 60mm)
- Temas personalizáveis (claro/escuro)
- Dados da empresa editáveis
- Configurações salvas automaticamente

### 🛡️ **Segurança Aprimorada**
- Validação robusta de dados
- Sanitização de entradas
- Headers de segurança implementados
- Sistema de auditoria melhorado

### ⚡ **Performance Otimizada**
- Cache inteligente de configurações
- Consultas SQL otimizadas com índices
- Assets minificados
- Carregamento assíncrono

## 📋 Funcionalidades Principais

### 💰 **Gestão Comercial**
- ✅ Sistema de vendas com carrinho inteligente
- ✅ Compras de materiais com controle de qualidade
- ✅ Múltiplas formas de pagamento
- ✅ Controle de crédito por cliente
- ✅ Histórico completo de transações

### 📦 **Controle de Estoque**
- ✅ Rastreabilidade completa por lote
- ✅ Controle de peso bruto/líquido/tara
- ✅ Alertas de estoque baixo
- ✅ Movimentações automáticas

### 👥 **Gestão de Clientes**
- ✅ Cadastro completo com validação
- ✅ Sistema de listas de preços
- ✅ Extrato de movimentações
- ✅ Integração com API de CEP

### 💵 **Caixa e Financeiro**
- ✅ Controle de caixa por usuário
- ✅ Relatórios financeiros detalhados
- ✅ Fechamento de caixa automático
- ✅ Múltiplas empresas

### 🏢 **Multi-Empresa**
- ✅ Gestão de múltiplas filiais
- ✅ Configurações independentes
- ✅ Relatórios consolidados
- ✅ Controle de acesso por empresa

## 💻 Tecnologias Utilizadas

### **Backend**
- **PHP 7.4+** - Core do sistema
- **MySQL/MariaDB** - Banco de dados principal
- **Prepared Statements** - Segurança SQL

### **Frontend**
- **HTML5 Semântico** - Estrutura moderna
- **CSS3 + Custom Properties** - Estilização avançada
- **Bootstrap 5** - Framework responsivo
- **JavaScript ES6+** - Interatividade moderna

### **Bibliotecas & APIs**
- **jQuery 3.6+** - Manipulação DOM
- **Select2** - Seletores avançados
- **Chart.js** - Gráficos e relatórios
- **Bootstrap Icons** - Ícones vetoriais
- **API ViaCEP** - Consulta de endereços

## 🛠️ Instalação e Configuração

### **Requisitos Mínimos**
- PHP 7.4 ou superior
- MySQL 5.7+ ou MariaDB 10.3+
- Apache/Nginx
- Extensões PHP: mysqli, curl, json

### **Instalação Rápida**

1. **Configure o banco de dados:**
```bash
# Importe o banco principal
mysql -u root -p < db/erp.sql

# Execute otimizações (opcional)
mysql -u root -p < db/otimizacoes.sql
```

2. **Configure a conexão:**
```php
// conexx/config.php
$servername = "localhost";
$username = "seu_usuario";
$password = "sua_senha";
$dbname = "erp_reciclagem";
```

3. **Execute o script de melhorias:**
```bash
php scripts/aplicar_melhorias.php
```

4. **Verifique a instalação:**
```bash
php scripts/verificar_integridade.php
```

## 📱 Responsividade Mobile

### **Breakpoints Principais**
- **Mobile**: 320px - 767px
- **Tablet**: 768px - 1023px  
- **Desktop**: 1024px+

### **Componentes Responsivos**
- ✅ Tabelas com scroll horizontal
- ✅ Formulários otimizados para touch
- ✅ Navegação colapsável
- ✅ Cards adaptativos
- ✅ Modais mobile-friendly

## 🗂️ Estrutura do Projeto

```
varejo/
├── 📄 Páginas principais (*.php)
├── 🗃️ api/                     # Endpoints da API
├── 🔌 conexx/                  # Conexões de banco
├── 🗄️ db/                      # Scripts SQL
├── 📚 includes/                # Arquivos compartilhados
│   ├── header.php ✨          # Header responsivo
│   ├── navbar.php ✨          # Menu mobile-first
│   ├── configuracoes_globais.php # Sistema de configurações
│   ├── config_dev.php ✨       # Configurações de desenvolvimento
│   └── functions.php ✨        # Funções auxiliares
├── 🎨 css/
│   └── responsive-mobile.css ✨ # Framework responsivo customizado
├── ⚡ js/
│   ├── erp-utils.js ✨         # Utilitários JavaScript avançados
│   └── configuracoes.js       # Gerenciamento de configurações
├── 📋 scripts/ ✨              # Scripts de manutenção
│   ├── aplicar_melhorias.php  # Automatização de melhorias
│   └── verificar_integridade.php # Health checker
└── 📖 docs/                    # Documentação
```

**Legenda:** ✨ = Novo na v2.0

## 🎯 Como Usar o Sistema

### **1. Configuração Inicial**
1. Acesse `configuracoes.php`
2. Configure dados da empresa
3. Escolha tema (claro/escuro)
4. Defina tamanho do papel de impressão

### **2. Cadastros Básicos**
1. **Materiais**: Defina tipos (papel, plástico, metal, etc.)
2. **Clientes**: Cadastre fornecedores e compradores
3. **Usuários**: Configure acessos por empresa

### **3. Operações Diárias**
1. **Compras**: Registre entrada de materiais
2. **Vendas**: Processe saídas com múltiplas formas de pagamento
3. **Caixa**: Controle movimentações financeiras
4. **Relatórios**: Acompanhe performance

## 🔧 Manutenção e Desenvolvimento

### **Scripts de Automação**

**Aplicar Melhorias:**
```bash
php scripts/aplicar_melhorias.php
```

**Verificar Integridade:**
```bash
php scripts/verificar_integridade.php
```

### **Debug Mode**
```php
// includes/config_dev.php
define('ERP_DEBUG', true);
// Habilita logs detalhados e info de debug
```

## 🤝 Contribuindo

### **Roadmap v3.0**
- [ ] API REST completa
- [ ] PWA (Progressive Web App)
- [ ] Sistema de relatórios avançado
- [ ] Integração com e-commerce
- [ ] Dashboard analítico com BI

## 📞 Suporte

### **Documentação**
- 📖 [Análise Completa](docs/ANALISE_SISTEMA_ERP.md)
- 📋 [Plano de Implementação](PLANO_IMPLEMENTACAO.md)
- ⚙️ [Sistema de Configurações](docs/SISTEMA_CONFIGURACOES.md)

---

**Desenvolvido com ♻️ para um mundo mais sustentável**

*Sistema ERP v2.0 - Otimizado para empresas de reciclagem modernas*
