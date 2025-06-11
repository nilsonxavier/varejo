<?php
require_once 'verifica_login.php';
require_once 'conexx/config.php';

// Apenas admin pode acessar
if ($_SESSION['usuario_tipo'] !== 'admin') {
    echo "<h2 style='color:red;text-align:center;'>Acesso negado. Apenas administradores podem gerenciar usuários.</h2>";
    exit;
}

// Processamento
if (isset($_POST['atualizar'])) {
    $id = intval($_POST['id']);
    $nome = trim($_POST['nome']);
    $email = trim($_POST['email']);
    $tipo = $_POST['tipo'];

    if (!empty($nome) && !empty($email)) {
        $query = "UPDATE usuarios SET nome=?, email=?, tipo=?";
        $params = [$nome, $email, $tipo];
        $types = "sss";

        if (!empty($_POST['senha'])) {
            $senha = password_hash(trim($_POST['senha']), PASSWORD_DEFAULT);
            $query .= ", senha=?";
            $params[] = $senha;
            $types .= "s";
        }

        $query .= " WHERE id=?";
        $params[] = $id;
        $types .= "i";

        $stmt = $conn->prepare($query);
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
    }
    header("Location: gerenciar_usuarios.php?msg=atualizado");
    exit;
}

if (isset($_POST['adicionar'])) {
    $nome = trim($_POST['nome']);
    $email = trim($_POST['email']);
    $senha = password_hash(trim($_POST['senha']), PASSWORD_DEFAULT);
    $tipo = $_POST['tipo'];

    if (!empty($nome) && !empty($email) && !empty($_POST['senha'])) {
        $stmt = $conn->prepare("INSERT INTO usuarios (nome, email, senha, tipo) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $nome, $email, $senha, $tipo);
        $stmt->execute();
    }
    header("Location: gerenciar_usuarios.php?msg=adicionado");
    exit;
}

if (isset($_GET['excluir'])) {
    $id = intval($_GET['excluir']);
    if ($id !== $_SESSION['usuario_id']) {
        $stmt = $conn->prepare("DELETE FROM usuarios WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
    }
    header("Location: gerenciar_usuarios.php?msg=excluido");
    exit;
}

// Buscar para editar
$editar_usuario = null;
if (isset($_GET['editar'])) {
    $id_editar = intval($_GET['editar']);
    $stmt = $conn->prepare("SELECT * FROM usuarios WHERE id = ?");
    $stmt->bind_param("i", $id_editar);
    $stmt->execute();
    $result = $stmt->get_result();
    $editar_usuario = $result->fetch_assoc();
}

// Lista
$usuarios = $conn->query("SELECT * FROM usuarios ORDER BY id DESC");

// UI
include __DIR__.'/includes/header.php';
include __DIR__.'/includes/navbar.php';
?>

<div class="container mt-4">
    <h2 class="mb-4">
        <i class="bi bi-person-lines-fill me-2"></i><?= $editar_usuario ? "Editar Usuário" : "Gerenciar Usuários" ?>
    </h2>

    <?php if (isset($_GET['msg'])): ?>
        <div class="alert alert-success">
            <?php
            switch ($_GET['msg']) {
                case 'adicionado': echo "Usuário adicionado com sucesso!"; break;
                case 'atualizado': echo "Usuário atualizado com sucesso!"; break;
                case 'excluido': echo "Usuário excluído com sucesso!"; break;
            }
            ?>
        </div>
    <?php endif; ?>

    <form method="post" class="row g-3 mb-4">
        <?php if ($editar_usuario): ?>
            <input type="hidden" name="id" value="<?= $editar_usuario['id'] ?>">
        <?php endif; ?>

        <div class="col-md-3">
            <input type="text" name="nome" class="form-control" placeholder="Nome" required value="<?= htmlspecialchars($editar_usuario['nome'] ?? '') ?>">
        </div>
        <div class="col-md-3">
            <input type="email" name="email" class="form-control" placeholder="E-mail" required value="<?= htmlspecialchars($editar_usuario['email'] ?? '') ?>">
        </div>
        <div class="col-md-2">
            <input type="password" name="senha" class="form-control" placeholder="<?= $editar_usuario ? 'Nova Senha (opcional)' : 'Senha' ?>" <?= $editar_usuario ? '' : 'required' ?>>
        </div>
        <div class="col-md-2">
            <select name="tipo" class="form-select" required>
                <option value="admin" <?= ($editar_usuario['tipo'] ?? '') === 'admin' ? 'selected' : '' ?>>Administrador</option>
                <option value="vendedor" <?= ($editar_usuario['tipo'] ?? '') === 'vendedor' ? 'selected' : '' ?>>Vendedor</option>
                <option value="estoquista" <?= ($editar_usuario['tipo'] ?? '') === 'estoquista' ? 'selected' : '' ?>>Estoquista</option>
            </select>
        </div>
        <div class="col-md-2">
            <button type="submit" name="<?= $editar_usuario ? 'atualizar' : 'adicionar' ?>" class="btn btn-<?= $editar_usuario ? 'success' : 'primary' ?> w-100">
                <?= $editar_usuario ? 'Salvar' : 'Adicionar' ?>
            </button>
        </div>
    </form>

    <div class="table-responsive">
        <table class="table table-striped table-hover align-middle">
            <thead class="table-light">
                <tr>
                    <th>ID</th>
                    <th>Nome</th>
                    <th>Email</th>
                    <th>Tipo</th>
                    <th class="text-end">Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($user = $usuarios->fetch_assoc()): ?>
                    <tr>
                        <td><?= $user['id'] ?></td>
                        <td><?= htmlspecialchars($user['nome']) ?></td>
                        <td><?= htmlspecialchars($user['email']) ?></td>
                        <td>
                            <span class="badge bg-<?= $user['tipo'] === 'admin' ? 'primary' : ($user['tipo'] === 'vendedor' ? 'success' : 'warning') ?>">
                                <?= ucfirst($user['tipo']) ?>
                            </span>
                        </td>
                        <td class="text-end">
                            <?php if ($user['id'] !== $_SESSION['usuario_id']): ?>
                                <a href="gerenciar_usuarios.php?editar=<?= $user['id'] ?>" class="btn btn-warning btn-sm">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <a href="gerenciar_usuarios.php?excluir=<?= $user['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Deseja realmente excluir este usuário?')">
                                    <i class="bi bi-trash"></i>
                                </a>
                            <?php else: ?>
                                <span class="text-muted">Você</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include __DIR__.'/includes/footer.php'; ?>
