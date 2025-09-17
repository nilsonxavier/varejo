<?php
/**
 * TEMPLATE DE PÁGINA MODELO - BOAS PRÁTICAS
 * Este arquivo serve como exemplo de como implementar uma página seguindo
 * as melhores práticas de desenvolvimento do sistema ERP
 */

// 1. SEMPRE incluir verificação de login primeiro
require_once 'verifica_login.php';
require_once 'conexx/config.php';

// 2. Definir variáveis iniciais
$page_title = 'Exemplo de Página';
$empresa_id = $_SESSION['usuario_empresa'];
$success_message = '';
$error_message = '';

// 3. Processar formulários (POST) - SEMPRE usar prepared statements
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if (isset($_POST['acao'])) {
            switch ($_POST['acao']) {
                case 'criar':
                    // Validar entrada
                    $nome = trim($_POST['nome']);
                    $email = filter_var($_POST['email'], FILTER_VALIDATE_EMAIL);
                    
                    if (empty($nome)) {
                        throw new Exception('Nome é obrigatório');
                    }
                    
                    if (!$email) {
                        throw new Exception('E-mail inválido');
                    }
                    
                    // Executar operação com prepared statement
                    $stmt = $conn->prepare("INSERT INTO exemplo (nome, email, empresa_id, created_at) VALUES (?, ?, ?, NOW())");
                    $stmt->bind_param('ssi', $nome, $email, $empresa_id);
                    
                    if ($stmt->execute()) {
                        $success_message = 'Registro criado com sucesso!';
                    } else {
                        throw new Exception('Erro ao criar registro');
                    }
                    break;
                    
                case 'atualizar':
                    $id = intval($_POST['id']);
                    $nome = trim($_POST['nome']);
                    
                    $stmt = $conn->prepare("UPDATE exemplo SET nome = ?, updated_at = NOW() WHERE id = ? AND empresa_id = ?");
                    $stmt->bind_param('sii', $nome, $id, $empresa_id);
                    
                    if ($stmt->execute()) {
                        $success_message = 'Registro atualizado com sucesso!';
                    } else {
                        throw new Exception('Erro ao atualizar registro');
                    }
                    break;
            }
        }
    } catch (Exception $e) {
        $error_message = $e->getMessage();
    }
}

// 4. Buscar dados para exibição
$registros = [];
try {
    $stmt = $conn->prepare("SELECT * FROM exemplo WHERE empresa_id = ? ORDER BY created_at DESC");
    $stmt->bind_param('i', $empresa_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    while ($row = $result->fetch_assoc()) {
        $registros[] = $row;
    }
} catch (Exception $e) {
    $error_message = 'Erro ao buscar dados: ' . $e->getMessage();
}

// 5. Incluir header e navbar - NUNCA duplicar HTML
include __DIR__ . '/includes/header.php';
include __DIR__ . '/includes/navbar.php';
?>

<!-- 6. Estrutura HTML limpa e responsiva -->
<div class="container py-4">
    <!-- Título da página -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>
            <i class="bi bi-file-earmark-text me-2"></i>
            <?php echo htmlspecialchars($page_title); ?>
        </h2>
        <button class="btn btn-primary btn-responsive" data-bs-toggle="modal" data-bs-target="#novoRegistroModal">
            <i class="bi bi-plus-circle me-2"></i>
            <span class="d-none d-sm-inline">Novo Registro</span>
            <span class="d-sm-none">Novo</span>
        </button>
    </div>

    <!-- Alertas de feedback -->
    <?php if ($success_message): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i>
            <?php echo htmlspecialchars($success_message); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if ($error_message): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-2"></i>
            <?php echo htmlspecialchars($error_message); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- Conteúdo principal em card responsivo -->
    <div class="section-card section-card">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="mb-0">
                <i class="bi bi-list-ul me-2"></i>
                Lista de Registros
            </h5>
            <div class="d-flex gap-2">
                <!-- Busca responsiva -->
                <div class="input-group" style="max-width: 300px;">
                    <input type="text" class="form-control form-control-sm" 
                           placeholder="Buscar..." id="searchInput">
                    <button class="btn btn-outline-secondary btn-sm" type="button">
                        <i class="bi bi-search"></i>
                    </button>
                </div>
            </div>
        </div>

        <!-- Tabela responsiva -->
        <div class="table-responsive">
            <div class="table-responsive"><table class="table table-hover">
                <thead class="table-dark">
                    <tr>
                        <th>ID</th>
                        <th>Nome</th>
                        <th>E-mail</th>
                        <th>Data</th>
                        <th width="120" class="no-print">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($registros)): ?>
                        <tr>
                            <td colspan="5" class="text-center text-muted py-4">
                                <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                                Nenhum registro encontrado
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($registros as $registro): ?>
                            <tr>
                                <td data-label="ID"><?php echo htmlspecialchars($registro['id']); ?></td>
                                <td data-label="Nome"><?php echo htmlspecialchars($registro['nome']); ?></td>
                                <td data-label="E-mail"><?php echo htmlspecialchars($registro['email']); ?></td>
                                <td data-label="Data">
                                    <?php echo date('d/m/Y H:i', strtotime($registro['created_at'])); ?>
                                </td>
                                <td data-label="Ações" class="no-print">
                                    <div class="btn-group-mobile">
                                        <button class="btn btn-sm btn-outline-primary" 
                                                onclick="editarRegistro(<?php echo $registro['id']; ?>)"
                                                data-bs-toggle="tooltip" title="Editar">
                                            <i class="bi bi-pencil"></i>
                                            <span class="d-sm-none ms-1">Editar</span>
                                        </button>
                                        <button class="btn btn-sm btn-outline-danger" 
                                                data-confirm="Tem certeza que deseja excluir este registro?"
                                                onclick="excluirRegistro(<?php echo $registro['id']; ?>)"
                                                data-bs-toggle="tooltip" title="Excluir">
                                            <i class="bi bi-trash"></i>
                                            <span class="d-sm-none ms-1">Excluir</span>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal para novo/editar registro -->
<div class="modal fade" id="novoRegistroModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" id="formRegistro">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="bi bi-plus-circle me-2"></i>
                        Novo Registro
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                
                <div class="modal-body">
                    <input type="hidden" name="acao" value="criar" id="formAcao">
                    <input type="hidden" name="id" id="formId">
                    
                    <div class="mb-3">
                        <label for="nome" class="form-label">Nome *</label>
                        <input type="text" class="form-control" name="nome" id="nome" required>
                        <div class="invalid-feedback"></div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="email" class="form-label">E-mail *</label>
                        <input type="email" class="form-control" name="email" id="email" required>
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="bi bi-x-lg me-1"></i>Cancelar
                    </button>
                    <button type="submit" class="btn btn-primary btn-responsive">
                        <i class="bi bi-check-lg me-1"></i>
                        <span id="btnSubmitText">Salvar</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// JavaScript específico da página - sempre após o DOM
document.addEventListener('DOMContentLoaded', function() {
    
    // Busca em tempo real
    const searchInput = document.getElementById('searchInput');
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            const termo = this.value.toLowerCase();
            const rows = document.querySelectorAll('tbody tr');
            
            rows.forEach(row => {
                const texto = row.textContent.toLowerCase();
                row.style.display = texto.includes(termo) ? '' : 'none';
            });
        });
    }
    
    // Limpar modal ao fechar
    const modal = document.getElementById('novoRegistroModal');
    if (modal) {
        modal.addEventListener('hidden.bs.modal', function() {
            document.getElementById('formRegistro').reset();
            document.getElementById('formAcao').value = 'criar';
            document.getElementById('formId').value = '';
            document.querySelector('.modal-title').innerHTML = '<i class="bi bi-plus-circle me-2"></i>Novo Registro';
            document.getElementById('btnSubmitText').textContent = 'Salvar';
            
            // Remover validações
            document.querySelectorAll('.is-valid, .is-invalid').forEach(el => {
                el.classList.remove('is-valid', 'is-invalid');
            });
        });
    }
});

// Funções específicas
function editarRegistro(id) {
    // Buscar dados via AJAX (implementar conforme necessário)
    const modal = new bootstrap.Modal(document.getElementById('novoRegistroModal'));
    document.getElementById('formAcao').value = 'atualizar';
    document.getElementById('formId').value = id;
    document.querySelector('.modal-title').innerHTML = '<i class="bi bi-pencil me-2"></i>Editar Registro';
    document.getElementById('btnSubmitText').textContent = 'Atualizar';
    modal.show();
}

function excluirRegistro(id) {
    // A confirmação já é tratada pelo sistema de utilitários (erp-utils.js)
    // Implementar exclusão via AJAX ou redirect
    window.location.href = `?acao=excluir&id=${id}`;
}
</script>

<?php 
// 7. SEMPRE incluir o footer no final
include __DIR__ . '/includes/footer.php'; 
?>
</body>
</html>
