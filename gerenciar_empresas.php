<?php
require_once 'verifica_login.php';
require_once 'conexx/config.php';

// Apenas admin pode acessar
if ($_SESSION['usuario_tipo'] !== 'admin') {
    echo "<h2 style='color:red;text-align:center;'>Acesso negado. Apenas administradores podem gerenciar empresas.</h2>";
    exit;
}

// Processamento
if (isset($_POST['atualizar'])) {
    $id = intval($_POST['id']);
    $razao = trim($_POST['razao_social']);
    $fantasia = trim($_POST['nome_fantasia']);
    $cnpj = trim($_POST['cnpj']);
    $email = trim($_POST['email']);
    $telefone = trim($_POST['telefone']);
    $endereco = trim($_POST['endereco']);
    $cidade = trim($_POST['cidade']);
    $estado = trim($_POST['estado']);
    $cep = trim($_POST['cep']);

    $query = "UPDATE empresas SET razao_social=?, nome_fantasia=?, cnpj=?, email=?, telefone=?, endereco=?, cidade=?, estado=?, cep=? WHERE id=?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("sssssssssi", $razao, $fantasia, $cnpj, $email, $telefone, $endereco, $cidade, $estado, $cep, $id);
    $stmt->execute();

    header("Location: gerenciar_empresas.php?msg=atualizado");
    exit;
}

if (isset($_POST['adicionar'])) {
    $razao = trim($_POST['razao_social']);
    $fantasia = trim($_POST['nome_fantasia']);
    $cnpj = trim($_POST['cnpj']);
    $email = trim($_POST['email']);
    $telefone = trim($_POST['telefone']);
    $endereco = trim($_POST['endereco']);
    $cidade = trim($_POST['cidade']);
    $estado = trim($_POST['estado']);
    $cep = trim($_POST['cep']);

    $stmt = $conn->prepare("INSERT INTO empresas (razao_social, nome_fantasia, cnpj, email, telefone, endereco, cidade, estado, cep) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssssssss", $razao, $fantasia, $cnpj, $email, $telefone, $endereco, $cidade, $estado, $cep);
    $stmt->execute();

    header("Location: gerenciar_empresas.php?msg=adicionado");
    exit;
}

if (isset($_GET['excluir'])) {
    $id = intval($_GET['excluir']);
    $stmt = $conn->prepare("DELETE FROM empresas WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    header("Location: gerenciar_empresas.php?msg=excluido");
    exit;
}

$editar_empresa = null;
if (isset($_GET['editar'])) {
    $id_editar = intval($_GET['editar']);
    $stmt = $conn->prepare("SELECT * FROM empresas WHERE id = ?");
    $stmt->bind_param("i", $id_editar);
    $stmt->execute();
    $result = $stmt->get_result();
    $editar_empresa = $result->fetch_assoc();
}

$empresas = $conn->query("SELECT * FROM empresas ORDER BY id DESC");

include __DIR__.'/includes/header.php';
include __DIR__.'/includes/navbar.php';
?>

<div class="container mt-4">
    <h2 class="mb-4">
        <i class="bi bi-building me-2"></i><?= $editar_empresa ? "Editar Empresa" : "Gerenciar Empresas" ?>
    </h2>

    <?php if (isset($_GET['msg'])): ?>
        <div class="alert alert-success">
            <?php
            switch ($_GET['msg']) {
                case 'adicionado': echo "Empresa adicionada com sucesso!"; break;
                case 'atualizado': echo "Empresa atualizada com sucesso!"; break;
                case 'excluido': echo "Empresa excluída com sucesso!"; break;
            }
            ?>
        </div>
    <?php endif; ?>

    <form method="post" class="row g-3 mb-4">
        <?php if ($editar_empresa): ?>
            <input type="hidden" name="id" value="<?= $editar_empresa['id'] ?>">
        <?php endif; ?>

        <div class="col-md-4">
            <input type="text" name="razao_social" class="form-control" placeholder="Razão Social" required value="<?= htmlspecialchars($editar_empresa['razao_social'] ?? '') ?>">
        </div>
        <div class="col-md-4">
            <input type="text" name="nome_fantasia" class="form-control" placeholder="Nome Fantasia" value="<?= htmlspecialchars($editar_empresa['nome_fantasia'] ?? '') ?>">
        </div>
        <div class="col-md-4">
            <input type="text" name="cnpj" class="form-control" placeholder="CNPJ" required value="<?= htmlspecialchars($editar_empresa['cnpj'] ?? '') ?>">
        </div>
        <div class="col-md-4">
            <input type="email" name="email" class="form-control" placeholder="Email" value="<?= htmlspecialchars($editar_empresa['email'] ?? '') ?>">
        </div>
        <div class="col-md-4">
            <input type="text" name="telefone" class="form-control" placeholder="Telefone" value="<?= htmlspecialchars($editar_empresa['telefone'] ?? '') ?>">
        </div>
        <div class="col-md-4">
            <input type="text" name="endereco" class="form-control" placeholder="Endereço" value="<?= htmlspecialchars($editar_empresa['endereco'] ?? '') ?>">
        </div>
        <div class="col-md-3">
            <input type="text" name="cidade" class="form-control" placeholder="Cidade" value="<?= htmlspecialchars($editar_empresa['cidade'] ?? '') ?>">
        </div>
        <div class="col-md-1">
            <input type="text" name="estado" class="form-control" placeholder="UF" maxlength="2" value="<?= htmlspecialchars($editar_empresa['estado'] ?? '') ?>">
        </div>
        <div class="col-md-2">
            <input type="text" name="cep" class="form-control" placeholder="CEP" value="<?= htmlspecialchars($editar_empresa['cep'] ?? '') ?>">
        </div>
        <div class="col-md-2">
            <button type="submit" name="<?= $editar_empresa ? 'atualizar' : 'adicionar' ?>" class="btn btn-<?= $editar_empresa ? 'success' : 'primary' ?> w-100">
                <?= $editar_empresa ? 'Salvar' : 'Adicionar' ?>
            </button>
        </div>
    </form>

    <div class="table-responsive">
        <table class="table table-striped table-hover align-middle">
            <thead class="table-light">
                <tr>
                    <th>ID</th>
                    <th>Razão Social</th>
                    <th>CNPJ</th>
                    <th>Email</th>
                    <th class="text-end">Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($empresa = $empresas->fetch_assoc()): ?>
                    <tr>
                        <td><?= $empresa['id'] ?></td>
                        <td><?= htmlspecialchars($empresa['razao_social']) ?></td>
                        <td><?= htmlspecialchars($empresa['cnpj']) ?></td>
                        <td><?= htmlspecialchars($empresa['email']) ?></td>
                        <td class="text-end">
                            <a href="gerenciar_empresas.php?editar=<?= $empresa['id'] ?>" class="btn btn-warning btn-sm">
                                <i class="bi bi-pencil"></i>
                            </a>
                            <a href="gerenciar_empresas.php?excluir=<?= $empresa['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Deseja realmente excluir esta empresa?')">
                                <i class="bi bi-trash"></i>
                            </a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include __DIR__.'/includes/footer.php'; ?>
