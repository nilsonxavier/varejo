<?php
require_once 'conexx/config.php';

// Adicionar Material
if (isset($_POST['adicionar_material'])) {
    $nome_material = trim($_POST['nome_material']);
    if ($nome_material != '') {
        $stmt = $conn->prepare("INSERT INTO materiais (nome) VALUES (?)");
        $stmt->bind_param("s", $nome_material);
        $stmt->execute();
    }
    header("Location: cadastro_materiais.php");
    exit;
}

// Criar Lista de Preços
if (isset($_POST['criar_lista_precos'])) {
    $nome_lista = trim($_POST['nome_lista']);
    if ($nome_lista != '') {
        $stmt = $conn->prepare("INSERT INTO listas_precos (nome) VALUES (?)");
        $stmt->bind_param("s", $nome_lista);
        $stmt->execute();
        $nova_lista_id = $stmt->insert_id;

        header("Location: definir_precos.php?lista_id=" . $nova_lista_id);
        exit;
    }
}

// Excluir Lista de Preços
if (isset($_GET['excluir_lista'])) {
    $excluir_id = intval($_GET['excluir_lista']);
    if ($excluir_id > 0) {
        $conn->query("DELETE FROM precos_materiais WHERE lista_id = $excluir_id");
        $conn->query("DELETE FROM listas_precos WHERE id = $excluir_id");
    }
    header("Location: cadastro_materiais.php");
    exit;
}

// Excluir Material
if (isset($_GET['excluir_material'])) {
    $excluir_id = intval($_GET['excluir_material']);
    if ($excluir_id > 0) {
        $conn->query("DELETE FROM materiais WHERE id = $excluir_id");
        $conn->query("DELETE FROM precos_materiais WHERE material_id = $excluir_id");
    }
    header("Location: cadastro_materiais.php");
    exit;
}

include __DIR__.'/includes/header.php';
include __DIR__.'/includes/navbar.php';
include __DIR__.'/includes/footer.php';
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Cadastro de Materiais e Listas de Preços</title>
  
    <style>
        body {
            background-color: #f8f9fa;
        }
        .section-card {
            background-color: #ffffff;
            border-radius: 12px;
            padding: 20px;
            box-shadow: 0 0 10px rgba(0,0,0,0.05);
            margin-bottom: 30px;
        }
        h2, h3 {
            margin-bottom: 20px;
            color: #343a40;
        }
        .btn-primary, .btn-success, .btn-danger, .btn-outline-secondary {
            border-radius: 8px;
        }
        .list-group-item {
            border-radius: 8px;
        }
    </style>
</head>
<body>

<div class="container py-4">

    <!-- Cadastro de Materiais -->
    <div class="section-card">
        <h2><i class="bi bi-box-seam"></i> Cadastro de Materiais</h2>
        <form method="post" class="mb-3">
            <div class="input-group">
                <input type="text" name="nome_material" class="form-control" placeholder="Nome do Material" required>
                <button type="submit" name="adicionar_material" class="btn btn-success">Adicionar</button>
            </div>
        </form>

        <h5 class="mt-4">Materiais Cadastrados:</h5>
        <?php
        $materiais = $conn->query("SELECT * FROM materiais ORDER BY nome");
        if ($materiais->num_rows > 0) {
            echo "<ul class='list-group'>";
            while ($m = $materiais->fetch_assoc()) {
                echo "<li class='list-group-item d-flex justify-content-between align-items-center'>
                        {$m['nome']}
                        <a href='cadastro_materiais.php?excluir_material={$m['id']}' class='btn btn-sm btn-outline-danger' onclick=\"return confirm('Tem certeza que deseja excluir este material?')\">
                            <i class='bi bi-trash'></i> Excluir
                        </a>
                    </li>";
            }
            echo "</ul>";
        } else {
            echo "<p class='text-muted'>Nenhum material cadastrado ainda.</p>";
        }
        ?>
    </div>

    <!-- Criar Lista de Preços -->
    <div class="section-card">
        <h2><i class="bi bi-tags"></i> Criar Nova Lista de Preços</h2>
        <form method="post">
            <div class="input-group mb-3">
                <input type="text" name="nome_lista" class="form-control" placeholder="Exemplo: Atacado, Promoção" required>
                <button type="submit" name="criar_lista_precos" class="btn btn-primary">Criar Lista</button>
            </div>
        </form>
    </div>

    <!-- Listas Existentes -->
    <div class="section-card">
        <h2><i class="bi bi-list-ul"></i> Listas de Preços Existentes</h2>
        <?php
        $listas = $conn->query("SELECT * FROM listas_precos ORDER BY created_at DESC");
        if ($listas->num_rows > 0) {
            echo "<ul class='list-group'>";
            while ($l = $listas->fetch_assoc()) {
                echo "<li class='list-group-item d-flex justify-content-between align-items-center'>
                        <strong>{$l['nome']}</strong>
                        <div class='btn-group btn-group-sm'>
                            <a href='definir_precos.php?lista_id={$l['id']}' class='btn btn-outline-secondary'>
                                <i class='bi bi-pencil'></i> Editar Preços
                            </a>
                            <a href='cadastro_materiais.php?excluir_lista={$l['id']}' class='btn btn-outline-danger' onclick=\"return confirm('Tem certeza que deseja excluir esta lista? Todos os preços vinculados serão apagados.')\">
                                <i class='bi bi-trash'></i> Excluir Lista
                            </a>
                        </div>
                    </li>";
            }
            echo "</ul>";
        } else {
            echo "<p class='text-muted'>Nenhuma lista de preços criada ainda.</p>";
        }
        ?>
    </div>

</div>

<!-- Bootstrap Icons -->

</body>
</html>
