# Script PowerShell para testar otimizacoes.sql
# Execute este script antes de aplicar no banco principal

Write-Host "🔍 Verificando arquivo otimizacoes.sql..." -ForegroundColor Cyan

$sqlFile = "c:\wamp64\www\varejo\db\otimizacoes.sql"

# Verificar se arquivo existe
if (-not (Test-Path $sqlFile)) {
    Write-Host "❌ Arquivo otimizacoes.sql não encontrado!" -ForegroundColor Red
    exit 1
}

# Ler conteúdo do arquivo
$content = Get-Content $sqlFile -Raw

Write-Host "`n📋 Verificando estrutura SQL..." -ForegroundColor Yellow

# Verificar problemas conhecidos
$errors = @()
$warnings = @()
$success = @()

# Verificar referências problemáticas
if ($content -match "data_venda") {
    $errors += "❌ Ainda contém referência a 'data_venda' que não existe!"
} else {
    $success += "✅ Não há referências incorretas a 'data_venda'"
}

if ($content -match "vendas\(data\)") {
    $success += "✅ Referência correta a 'vendas(data)'"
}

if ($content -match "data_compra") {
    $success += "✅ Referência correta a 'data_compra'"
}

# Contar índices
$indexes = ($content | Select-String "CREATE INDEX" -AllMatches).Matches.Count
$success += "✅ $indexes índices encontrados"

# Verificar tabelas de sistema
if ($content -match "logs_auditoria") {
    $success += "✅ Tabela de auditoria será criada"
}

if ($content -match "cache_sistema") {
    $success += "✅ Sistema de cache será configurado"
}

# Verificar VIEW
if ($content -match "vw_vendas_resumo") {
    $success += "✅ View de relatórios será criada"
}

Write-Host "`n🎯 RESULTADO DA VERIFICAÇÃO:" -ForegroundColor Green

foreach ($item in $success) {
    Write-Host $item -ForegroundColor Green
}

foreach ($item in $warnings) {
    Write-Host $item -ForegroundColor Yellow
}

foreach ($item in $errors) {
    Write-Host $item -ForegroundColor Red
}

if ($errors.Count -eq 0) {
    Write-Host "`n🚀 ARQUIVO PRONTO PARA EXECUÇÃO!" -ForegroundColor Green -BackgroundColor DarkGreen
    Write-Host "`n📝 Para aplicar no seu banco:" -ForegroundColor Cyan
    Write-Host "   1. Abra o phpMyAdmin ou MySQL Workbench" -ForegroundColor White
    Write-Host "   2. Selecione seu banco de dados" -ForegroundColor White
    Write-Host "   3. Execute o conteúdo do arquivo otimizacoes.sql" -ForegroundColor White
    Write-Host "`n⚠️  OU via linha de comando:" -ForegroundColor Yellow
    Write-Host "   mysql -u root -p nome_do_banco < db/otimizacoes.sql" -ForegroundColor Gray
} else {
    Write-Host "`n🚨 CORREÇÕES NECESSÁRIAS ANTES DA EXECUÇÃO!" -ForegroundColor Red -BackgroundColor DarkRed
}

Write-Host "`n💡 DICA: Execute primeiro em ambiente de teste!" -ForegroundColor Magenta
