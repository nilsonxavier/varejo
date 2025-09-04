<?php
require_once 'verifica_login.php';
require_once 'conexx/config.php';
include __DIR__.'/includes/header.php';
include __DIR__.'/includes/navbar.php';

$empresa_id = $_SESSION['usuario_empresa'];

// Buscar movimentações recentes para exemplo
$movimentacoes = [];
$stmt = $conn->prepare("
    SELECT m.*, c.nome as cliente_nome 
    FROM movimentacoes_clientes m 
    LEFT JOIN clientes c ON m.cliente_id = c.id 
    WHERE m.empresa_id = ? 
    ORDER BY m.data_movimentacao DESC 
    LIMIT 10
");
$stmt->bind_param('i', $empresa_id);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $movimentacoes[] = $row;
}
?>

<head>
    <meta charset="UTF-8">
    <title>Exemplo com Configurações - Sistema</title>
    <style>
        .print-area {
            background: white;
            padding: 20px;
            margin: 20px 0;
        }
        
        .stats-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
        }
    </style>
</head>

<body>
    <div class="container py-4">
        <!-- Cabeçalho com botão de impressão automático -->
        <div class="section-card print-area">
            <div class="no-print d-flex justify-content-between align-items-center mb-3">
                <h2><i class="bi bi-graph-up"></i> Relatório de Exemplo</h2>
                <button class="btn btn-outline-primary btn-sm" onclick="imprimirPagina()">
                    <i class="bi bi-printer"></i> Imprimir
                </button>
            </div>
            
            <div class="row g-3 mb-4">
                <div class="col-md-3">
                    <div class="stats-card text-center">
                        <i class="bi bi-people-fill fs-1"></i>
                        <h4><?php echo count($movimentacoes); ?></h4>
                        <small>Movimentações Recentes</small>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stats-card text-center">
                        <i class="bi bi-currency-dollar fs-1"></i>
                        <h4>R$ 1.234,56</h4>
                        <small>Total do Mês</small>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stats-card text-center">
                        <i class="bi bi-graph-up fs-1"></i>
                        <h4>+15%</h4>
                        <small>Crescimento</small>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stats-card text-center">
                        <i class="bi bi-check-circle fs-1"></i>
                        <h4>98%</h4>
                        <small>Satisfação</small>
                    </div>
                </div>
            </div>

            <h4><i class="bi bi-list-ul"></i> Movimentações Recentes</h4>
            
            <?php if (empty($movimentacoes)): ?>
                <div class="alert alert-info">
                    <i class="bi bi-info-circle"></i> Nenhuma movimentação encontrada.
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead class="table-dark">
                            <tr>
                                <th>Data</th>
                                <th>Cliente</th>
                                <th>Tipo</th>
                                <th>Valor</th>
                                <th>Descrição</th>
                                <th class="no-print">Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($movimentacoes as $mov): ?>
                            <tr>
                                <td><?php echo date('d/m/Y H:i', strtotime($mov['data_movimentacao'])); ?></td>
                                <td><?php echo htmlspecialchars($mov['cliente_nome'] ?? 'N/A'); ?></td>
                                <td>
                                    <span class="badge bg-<?php echo $mov['tipo'] === 'credito' ? 'success' : 'danger'; ?>">
                                        <?php echo ucfirst($mov['tipo']); ?>
                                    </span>
                                </td>
                                <td>R$ <?php echo number_format($mov['valor'], 2, ',', '.'); ?></td>
                                <td><?php echo htmlspecialchars($mov['descricao'] ?? ''); ?></td>
                                <td class="no-print">
                                    <button class="btn btn-sm btn-outline-primary">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>

        <!-- Exemplo de como o tema escuro afeta diferentes componentes -->
        <div class="section-card">
            <h4><i class="bi bi-palette"></i> Demonstração de Componentes</h4>
            
            <div class="row g-3">
                <div class="col-md-6">
                    <h5>Formulário de Exemplo</h5>
                    <form>
                        <div class="mb-3">
                            <label class="form-label">Nome</label>
                            <input type="text" class="form-control" placeholder="Digite o nome">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Categoria</label>
                            <select class="form-select">
                                <option>Selecione...</option>
                                <option>Categoria 1</option>
                                <option>Categoria 2</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Observações</label>
                            <textarea class="form-control" rows="3"></textarea>
                        </div>
                    </form>
                </div>
                
                <div class="col-md-6">
                    <h5>Alertas e Badges</h5>
                    <div class="alert alert-success">
                        <i class="bi bi-check-circle"></i> Operação realizada com sucesso!
                    </div>
                    <div class="alert alert-warning">
                        <i class="bi bi-exclamation-triangle"></i> Atenção: Verifique os dados.
                    </div>
                    <div class="alert alert-danger">
                        <i class="bi bi-x-circle"></i> Erro ao processar solicitação.
                    </div>
                    
                    <div class="mt-3">
                        <span class="badge bg-primary me-2">Primary</span>
                        <span class="badge bg-secondary me-2">Secondary</span>
                        <span class="badge bg-success me-2">Success</span>
                        <span class="badge bg-danger me-2">Danger</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Demonstração de configurações atuais -->
        <div class="section-card no-print">
            <h4><i class="bi bi-gear"></i> Configurações Atuais do Sistema</h4>
            
            <div class="row g-3">
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-body">
                            <h6 class="card-title">
                                <i class="bi bi-printer"></i> Configuração de Impressão
                            </h6>
                            <p class="card-text">
                                <strong>Tamanho do Papel:</strong> 
                                <span class="badge bg-info"><?php echo $TAMANHO_PAPEL ?? 'A4'; ?></span>
                            </p>
                            <button class="btn btn-sm btn-outline-primary" onclick="window.print()">
                                Testar Impressão
                            </button>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-body">
                            <h6 class="card-title">
                                <i class="bi bi-palette"></i> Configuração de Tema
                            </h6>
                            <p class="card-text">
                                <strong>Tema Atual:</strong> 
                                <span class="badge bg-<?php echo $TEMA_DARK ? 'dark' : 'light'; ?>">
                                    <?php echo $TEMA_DARK ? 'Escuro' : 'Claro'; ?>
                                </span>
                            </p>
                            <a href="configuracoes.php" class="btn btn-sm btn-outline-primary">
                                Alterar Configurações
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Instruções para desenvolvedores -->
        <div class="section-card no-print">
            <h4><i class="bi bi-code-slash"></i> Instruções para Desenvolvedores</h4>
            
            <div class="accordion" id="accordionInstrucoes">
                <div class="accordion-item">
                    <h2 class="accordion-header">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseUm">
                            Como aplicar o sistema de configurações em uma página
                        </button>
                    </h2>
                    <div id="collapseUm" class="accordion-collapse collapse" data-bs-parent="#accordionInstrucoes">
                        <div class="accordion-body">
                            <ol>
                                <li>Inclua o <code>header.php</code> normalmente - ele já carrega as configurações</li>
                                <li>Use a classe <code>.section-card</code> para seções que podem ser impressas</li>
                                <li>Use a classe <code>.no-print</code> para elementos que não devem aparecer na impressão</li>
                                <li>As variáveis <code>$TEMA_DARK</code> e <code>$TAMANHO_PAPEL</code> estão disponíveis globalmente</li>
                            </ol>
                        </div>
                    </div>
                </div>
                
                <div class="accordion-item">
                    <h2 class="accordion-header">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseDois">
                            Funções JavaScript disponíveis
                        </button>
                    </h2>
                    <div id="collapseDois" class="accordion-collapse collapse" data-bs-parent="#accordionInstrucoes">
                        <div class="accordion-body">
                            <ul>
                                <li><code>imprimirPagina()</code> - Imprime a página respeitando configurações</li>
                                <li><code>sistemaConfiguracoes.adicionarBotaoImpressao()</code> - Adiciona botão de impressão</li>
                                <li><code>sistemaConfiguracoes.aplicarConfiguracaoImpressao(tamanho)</code> - Aplica configuração específica</li>
                                <li><code>sistemaConfiguracoes.atualizarComponentesTema()</code> - Atualiza componentes para o tema atual</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Exemplo de uso das configurações em JavaScript
        document.addEventListener('DOMContentLoaded', function() {
            // Adicionar funcionalidades específicas desta página
            console.log('Página carregada com tema:', <?php echo json_encode($TEMA_DARK); ?> ? 'escuro' : 'claro');
            console.log('Tamanho de papel configurado:', <?php echo json_encode($TAMANHO_PAPEL); ?>);
            
            // Exemplo de como reagir a mudanças de configuração
            // (isso seria útil em uma SPA ou página que não recarrega)
            window.addEventListener('configuracaoAlterada', function(event) {
                console.log('Configuração alterada:', event.detail);
                // Aqui você pode atualizar componentes específicos
            });
        });
    </script>

    <?php include __DIR__.'/includes/footer.php'; ?>
</body>
</html>
