<?php
require_once 'verifica_login.php';
require_once 'conexx/config.php';
include __DIR__.'/includes/header.php';
include __DIR__.'/includes/navbar.php';

// Carrega materiais com saldo atual (filtrado pela empresa do usuário)
$empresa_id = intval($_SESSION['usuario_empresa']);

$sql = "SELECT m.id, m.nome,
           COALESCE(SUM(CASE WHEN e.tipo = 'entrada' THEN e.quantidade ELSE 0 END), 0) -
           COALESCE(SUM(CASE WHEN e.tipo = 'saida' THEN e.quantidade ELSE 0 END), 0) AS saldo
    FROM materiais m
    LEFT JOIN estoque e ON e.material_id = m.id AND e.empresa_id = ?
    WHERE m.empresa_id = ?
    GROUP BY m.id

    ORDER BY m.nome";

// Paginação de materiais
$mat_page = max(1, intval($_GET['mat_page'] ?? 1));
$mat_per_page = 10; //quantidade de produtos por pagina
$mat_offset = ($mat_page - 1) * $mat_per_page;

// Conta total de materiais para paginação
$countStmt = $conn->prepare("SELECT COUNT(*) as total FROM materiais WHERE empresa_id = ?");
$countStmt->bind_param('i', $empresa_id);
$countStmt->execute();
$countRes = $countStmt->get_result();
$totalMaterials = ($rowc = $countRes->fetch_assoc()) ? intval($rowc['total']) : 0;
$totalMatPages = max(1, (int) ceil($totalMaterials / $mat_per_page));

// Busca materiais com LIMIT/OFFSET
$sql_pag = $sql . " LIMIT ? OFFSET ?";
$stmt = $conn->prepare($sql_pag);
$stmt->bind_param('iiii', $empresa_id, $empresa_id, $mat_per_page, $mat_offset);
$stmt->execute();
$res = $stmt->get_result();

$materiais = [];
while ($row = $res->fetch_assoc()) {
    $materiais[] = $row;
}

$page = max(1, intval($_GET['page'] ?? 1));
$per_page = max(10, intval($_GET['per_page'] ?? 25));
$offset = ($page - 1) * $per_page;

// Filtros de data
$where = 'e.empresa_id = ? AND m.empresa_id = ?';
$params = [$empresa_id, $empresa_id];
$types = 'ii';
if (!empty($_GET['start_date'])) {
    $where .= ' AND e.data_movimentacao >= ?';
    $params[] = $_GET['start_date'] . ' 00:00:00';
    $types .= 's';
}
if (!empty($_GET['end_date'])) {
    $where .= ' AND e.data_movimentacao <= ?';
    $params[] = $_GET['end_date'] . ' 23:59:59';
    $types .= 's';
}

$sqlMov = "SELECT e.*, m.nome AS material_nome FROM estoque e JOIN materiais m ON m.id = e.material_id WHERE $where ORDER BY e.data_movimentacao DESC LIMIT ? OFFSET ?";
$stmt2 = $conn->prepare($sqlMov);
// bind dinâmico simplificado: adicionar per_page e offset
$types .= 'ii';
$params[] = $per_page;
$params[] = $offset;
$bindParams = [];
$bindParams[] = & $types;
foreach ($params as $k => $v) {
    // criar referência para cada parâmetro
    $bindParams[] = & $params[$k];
}
call_user_func_array([$stmt2, 'bind_param'], $bindParams);
$stmt2->execute();
$resMov = $stmt2->get_result();
$movimentacoes = [];
while ($row = $resMov->fetch_assoc()) {
    $movimentacoes[] = $row;
}
?>

<div class="container py-4">
    <div class="section-card mb-4">
        <h4>Controle de Estoque</h4>
        <div class="mb-3">
            <button id="btnCadastrarProd" class="btn btn-success btn-sm">Cadastrar Produto</button>
        </div>
        <?php if (!empty($_GET['error'])): ?>
            <?php if ($_GET['error'] === 'nome_required'): ?>
                <div class="alert alert-danger">Nome do produto é obrigatório.</div>
            <?php elseif ($_GET['error'] === 'nome_duplicado'): ?>
                <div class="alert alert-danger">Já existe um produto com esse nome nesta empresa.</div>
            <?php endif; ?>
        <?php elseif (!empty($_GET['success']) && $_GET['success'] === 'material_created'): ?>
            <div class="alert alert-success">Produto cadastrado com sucesso.</div>
        <?php endif; ?>

        <div class="row mb-2">
            <div class="col-md-6">
                <input type="text" class="form-control" id="filtroMateriais" placeholder="Pesquisar materiais por id ou nome...">
            </div>
        </div>

    <div class="table-responsive">
    <table class="table table-bordered" id="tabelaMateriais">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nome</th>
                    <th>Estoque Atual</th>
                    <th>Ação</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($materiais as $m): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($m['id']); ?></td>
                        <td><?php echo htmlspecialchars($m['nome']); ?></td>
                        <td><?php echo number_format($m['saldo'], 2, ',', '.'); ?></td>
                        <td>
                            <div class="d-flex flex-column flex-sm-row gap-1">
                                <button type="button" class="btn btn-primary btn-sm btnAtualizar" data-id="<?php echo $m['id']; ?>" data-nome="<?php echo htmlspecialchars($m['nome'], ENT_QUOTES); ?>">
                                    <i class="bi bi-pencil"></i>
                                    <span class="d-none d-sm-inline"> Atualizar</span>
                                </button>
                                <button type="button" class="btn btn-danger btn-sm btnExcluir" data-id="<?php echo $m['id']; ?>" data-nome="<?php echo htmlspecialchars($m['nome'], ENT_QUOTES); ?>">
                                    <i class="bi bi-trash"></i>
                                    <span class="d-none d-sm-inline"> Excluir</span>
                                </button>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
    </table>
    </div>
        <nav aria-label="Paginação materiais">
            <ul class="pagination">
                <?php if ($mat_page > 1): ?>
                    <li class="page-item"><a class="page-link" href="?mat_page=<?php echo $mat_page-1; ?>">Anterior</a></li>
                <?php endif; ?>
                <?php for ($p = 1; $p <= $totalMatPages; $p++): ?>
                    <li class="page-item <?php echo $p == $mat_page ? 'active' : ''; ?>">
                        <a class="page-link" href="?mat_page=<?php echo $p; ?>"><?php echo $p; ?></a>
                    </li>
                <?php endfor; ?>
                <?php if ($mat_page < $totalMatPages): ?>
                    <li class="page-item"><a class="page-link" href="?mat_page=<?php echo $mat_page+1; ?>">Próxima</a></li>
                <?php endif; ?>
            </ul>
        </nav>
    </div>

    <div class="section-card">
        <h5>Histórico de Movimentações</h5>
        <?php if (!empty($_GET['error']) && $_GET['error'] === 'descricao_required'): ?>
            <div class="alert alert-danger">A descrição é obrigatória para registrar a movimentação.</div>
        <?php endif; ?>
        <div class="row mb-2">
            <div class="col-md-4">
                <input type="text" class="form-control" id="filtroMov" placeholder="Filtrar por material ou descrição...">
            </div>
            <div class="col-md-3">
                <input type="date" id="start_date" class="form-control" placeholder="Data início">
            </div>
            <div class="col-md-3">
                <input type="date" id="end_date" class="form-control" placeholder="Data fim">
            </div>
        </div>

    <div class="table-responsive">
    <table class="table table-striped">
            <thead>
                <tr>
                    <th class="d-none d-sm-table-cell">ID</th>
                    <th class="d-none d-sm-table-cell">Data</th>
                    <th>Material</th>
                    <th>Tipo</th>
                    <th class="d-none d-sm-table-cell">Quantidade</th>
                    <th>Descrição</th>
                </tr>
            </thead>
            <tbody id="tabelaMovimentacoes">
                <?php foreach ($movimentacoes as $mov): ?>
                    <tr>
                        <td class="d-none d-sm-table-cell"><?php echo htmlspecialchars($mov['material_id']); ?></td>
                        <td class="d-none d-sm-table-cell"><?php echo date('d/m/Y H:i', strtotime($mov['data_movimentacao'])); ?></td>
                        <td><?php echo htmlspecialchars($mov['material_nome']); ?></td>
                        <td><?php echo ucfirst($mov['tipo']); ?></td>
                        <td class="d-none d-sm-table-cell"><?php echo number_format($mov['quantidade'], 2, ',', '.'); ?></td>
                        <?php $fullDesc = htmlspecialchars($mov['descricao'] ?? 'Sem descrição', ENT_QUOTES); ?>
                        <?php $movDate = date('d/m/Y H:i', strtotime($mov['data_movimentacao'])); ?>
                        <?php $movQty = number_format($mov['quantidade'], 2, ',', '.'); ?>
                        <td class="text-truncate d-flex align-items-center" style="max-width:280px" data-bs-toggle="tooltip" data-bs-placement="top" title="<?php echo $fullDesc; ?>">
                            <span class="flex-fill"><?php echo $fullDesc; ?></span>
                            <!-- botão visível apenas no mobile para ver detalhes (data/quantidade) -->
                            <button type="button" class="btn btn-sm btn-outline-secondary ms-2 d-inline d-sm-none btnMovInfo" 
                                data-date="<?php echo $movDate; ?>" data-qty="<?php echo $movQty; ?>" data-desc="<?php echo $fullDesc; ?>">
                                <i class="bi bi-info-circle"></i>
                            </button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
    </table>
    </div>

        <nav aria-label="Paginação movimentações">
            <ul class="pagination">
                <?php if (!empty($_GET['page']) && intval($_GET['page']) > 1): ?>
                    <li class="page-item"><a class="page-link" href="?page=<?php echo intval($_GET['page'])-1; ?>">Anterior</a></li>
                <?php endif; ?>
                <li class="page-item"><a class="page-link" href="?page=<?php echo max(1, intval($_GET['page'] ?? 1)); ?>"><?php echo max(1, intval($_GET['page'] ?? 1)); ?></a></li>
                <li class="page-item"><a class="page-link" href="?page=<?php echo max(1, intval($_GET['page'] ?? 1))+1; ?>">Próxima</a></li>
            </ul>
        </nav>
    </div>
</div>

<script>
document.getElementById('filtroMov').addEventListener('input', function() {
    const termo = this.value.toLowerCase();
    document.querySelectorAll('#tabelaMovimentacoes tr').forEach(function(row) {
        const texto = row.innerText.toLowerCase();
        row.style.display = texto.includes(termo) ? '' : 'none';
    });
});

// filtro para materiais
document.getElementById('filtroMateriais').addEventListener('input', function() {
    const termo = this.value.toLowerCase();
    document.querySelectorAll('#tabelaMateriais tbody tr').forEach(function(row) {
        const texto = row.innerText.toLowerCase();
        row.style.display = texto.includes(termo) ? '' : 'none';
    });
});
</script>

<script>
// inicializar tooltips Bootstrap
var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
    return new bootstrap.Tooltip(tooltipTriggerEl)
})
</script>

<!-- Modal detalhes movimentação (mobile) -->
<div class="modal fade" id="modalMovInfo" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Detalhes da Movimentação</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
            </div>
            <div class="modal-body">
                <p><strong>Data:</strong> <span id="mi_date"></span></p>
                <p><strong>Quantidade:</strong> <span id="mi_qty"></span></p>
                <p><strong>Descrição:</strong> <span id="mi_desc"></span></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
            </div>
        </div>
    </div>
</div>

<script>
// abrir modal de info quando clicar no botão mobile
document.querySelectorAll('.btnMovInfo').forEach(function(btn) {
        btn.addEventListener('click', function() {
                var date = this.getAttribute('data-date');
                var qty = this.getAttribute('data-qty');
                var desc = this.getAttribute('data-desc');
                document.getElementById('mi_date').innerText = date;
                document.getElementById('mi_qty').innerText = qty;
                document.getElementById('mi_desc').innerText = desc;
                var modal = new bootstrap.Modal(document.getElementById('modalMovInfo'));
                modal.show();
        });
});
</script>

<script>
// abrir modal cadastrar produto
document.getElementById('btnCadastrarProd').addEventListener('click', function() {
        var modalHtml = `
        <div class="modal fade" id="modalCadastrar" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Cadastrar Produto</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
                    </div>
                    <form id="formCadastrar" action="cadastrar_material.php" method="POST">
                    <div class="modal-body">
                        <div class="mb-2">
                            <label class="form-label">Nome do Produto</label>
                            <input type="text" name="nome_material" id="cad_nome" class="form-control" required>
                        </div>
                        <div class="mb-2">
                            <label class="form-label">Quantidade em Estoque Inicial</label>
                            <input type="number" name="quantidade_inicial" id="cad_qtd" class="form-control" step="0.01" min="0">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-success">Cadastrar</button>
                    </div>
                    </form>
                </div>
            </div>
        </div>`;

        var div = document.createElement('div');
        div.innerHTML = modalHtml;
        document.body.appendChild(div);
        var modalEl = document.getElementById('modalCadastrar');
        var modal = new bootstrap.Modal(modalEl);
        modal.show();
        modalEl.addEventListener('hidden.bs.modal', function () { div.remove(); });
});

    // confirmar exclusão de material
    document.querySelectorAll('.btnExcluir').forEach(function(btn) {
        btn.addEventListener('click', function() {
            var id = this.getAttribute('data-id');
            var nome = this.getAttribute('data-nome');
            if (confirm('Confirma exclusão do produto: ' + nome + ' ? Esta ação não pode ser desfeita.')) {
                window.location.href = 'cadastro_materiais.php?excluir_material=' + encodeURIComponent(id);
            }
        });
    });
</script>

<?php include __DIR__.'/includes/footer.php'; ?>

<!-- Modal Atualizar Estoque -->
<div class="modal fade" id="modalAtualizar" tabindex="-1" aria-labelledby="modalAtualizarLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalAtualizarLabel">Atualizar Estoque</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
            </div>
            <form id="formAtualizar" action="atualizar_estoque.php" method="POST">
            <div class="modal-body">
                    <input type="hidden" name="material_id" id="modal_material_id">
                    <div class="mb-2"><strong id="modal_material_nome"></strong></div>

                    <div class="mb-2">
                        <label class="form-label">Tipo</label>
                        <select name="tipo" id="modal_tipo" class="form-select" required>
                            <option value="entrada">Entrada</option>
                            <option value="saida">Saída</option>
                        </select>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Quantidade</label>
                        <input type="number" name="quantidade" id="modal_quantidade" step="0.01" min="0.01" class="form-control" required>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Descrição</label>
                        <input type="text" name="descricao" id="modal_descricao" class="form-control" required>
                    </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" class="btn btn-primary">Salvar</button>
            </div>
            </form>
        </div>
    </div>
</div>

<script>
// abrir modal ao clicar em Atualizar
document.querySelectorAll('.btnAtualizar').forEach(function(btn) {
        btn.addEventListener('click', function() {
                var id = this.getAttribute('data-id');
                var nome = this.getAttribute('data-nome');
                document.getElementById('modal_material_id').value = id;
                document.getElementById('modal_material_nome').innerText = nome;
                // reset campos
                document.getElementById('modal_tipo').value = 'entrada';
                document.getElementById('modal_quantidade').value = '';
                document.getElementById('modal_descricao').value = '';
                var modal = new bootstrap.Modal(document.getElementById('modalAtualizar'));
                modal.show();
        });
});

// validação adicional no submit
document.getElementById('formAtualizar').addEventListener('submit', function(e) {
        var qtd = parseFloat(document.getElementById('modal_quantidade').value);
        var desc = document.getElementById('modal_descricao').value.trim();
        if (!qtd || qtd <= 0) {
                e.preventDefault();
                alert('Informe uma quantidade válida maior que zero.');
                return;
        }
        if (desc === '') {
                e.preventDefault();
                alert('Descrição é obrigatória.');
                return;
        }
});
</script>
