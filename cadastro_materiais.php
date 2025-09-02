<?php
require_once 'conexx/config.php';
require_once 'verifica_login.php';

$empresa_id = intval($_SESSION['usuario_empresa']);

// Adicionar Material
if (isset($_POST['adicionar_material'])) {
    $nome_material = trim($_POST['nome_material']);
    if ($nome_material != '') {
        $stmt = $conn->prepare("INSERT INTO materiais (nome, empresa_id) VALUES (?, ?)");
        $stmt->bind_param("si", $nome_material, $empresa_id);
        $stmt->execute();
    }
    header("Location: estoque.php");
    exit;
}

// Criar Lista de Preços
if (isset($_POST['criar_lista_precos'])) {
    $nome_lista = trim($_POST['nome_lista']);
    if ($nome_lista != '') {
        $is_padrao = isset($_POST['padrao']) ? 1 : 0;
        // Se marca como padrão, removemos a marca de outras listas da mesma empresa
        if ($is_padrao) {
            $upd = $conn->prepare("UPDATE listas_precos SET padrao = 0 WHERE empresa_id = ?");
            $upd->bind_param('i', $empresa_id);
            $upd->execute();
        }
        $stmt = $conn->prepare("INSERT INTO listas_precos (nome, empresa_id, padrao) VALUES (?,?,?)");
        $stmt->bind_param("sii", $nome_lista, $empresa_id, $is_padrao);
        $stmt->execute();
        $nova_lista_id = $stmt->insert_id;

        header("Location: definir_precos.php?lista_id=" . $nova_lista_id);
        exit;
    }
}

// Marcar/Desmarcar lista padrão via GET
if (isset($_GET['acao']) && $_GET['acao'] === 'marcar_padrao' && isset($_GET['id'])) {
    $lid = intval($_GET['id']);
    // verifica propriedade
    $chk = $conn->prepare("SELECT id FROM listas_precos WHERE id = ? AND empresa_id = ?");
    $chk->bind_param('ii', $lid, $empresa_id);
    $chk->execute();
    $res = $chk->get_result();
    if ($res->num_rows > 0) {
        // tira padrao das outras
        $upd0 = $conn->prepare("UPDATE listas_precos SET padrao = 0 WHERE empresa_id = ?");
        $upd0->bind_param('i', $empresa_id);
        $upd0->execute();
        // seta padrao na escolhida
        $upd1 = $conn->prepare("UPDATE listas_precos SET padrao = 1 WHERE id = ? AND empresa_id = ?");
        $upd1->bind_param('ii', $lid, $empresa_id);
        $upd1->execute();
    }
    header('Location: cadastro_materiais.php');
    exit;
}

// Excluir Lista de Preços
if (isset($_GET['excluir_lista'])) {
    $excluir_id = intval($_GET['excluir_lista']);
    if ($excluir_id > 0) {
        // Verifica se a lista pertence à empresa antes de excluir
        $check = $conn->prepare("SELECT id FROM listas_precos WHERE id = ? AND empresa_id = ?");
        $check->bind_param('ii', $excluir_id, $empresa_id);
        $check->execute();
        $resCheck = $check->get_result();
        if ($resCheck->num_rows > 0) {
            $del1 = $conn->prepare("DELETE FROM precos_materiais WHERE lista_id = ?");
            $del1->bind_param('i', $excluir_id);
            $del1->execute();

            $del2 = $conn->prepare("DELETE FROM listas_precos WHERE id = ? AND empresa_id = ?");
            $del2->bind_param('ii', $excluir_id, $empresa_id);
            $del2->execute();
        }
    }
    header("Location: cadastro_materiais.php");
    exit;
}

// Excluir Material
if (isset($_GET['excluir_material'])) {
    $excluir_id = intval($_GET['excluir_material']);
    if ($excluir_id > 0) {
        // Verifica se o material pertence à empresa antes de excluir
        $check = $conn->prepare("SELECT id FROM materiais WHERE id = ? AND empresa_id = ?");
        $check->bind_param('ii', $excluir_id, $empresa_id);
        $check->execute();
        $resCheck = $check->get_result();
        if ($resCheck->num_rows > 0) {
            // Exclui dependências para evitar erro de FK
            $delEstoque = $conn->prepare("DELETE FROM estoque WHERE material_id = ?");
            $delEstoque->bind_param('i', $excluir_id);
            $delEstoque->execute();

            $delVendasItens = $conn->prepare("DELETE FROM vendas_itens WHERE material_id = ?");
            $delVendasItens->bind_param('i', $excluir_id);
            $delVendasItens->execute();

            $delPrecos = $conn->prepare("DELETE FROM precos_materiais WHERE material_id = ?");
            $delPrecos->bind_param('i', $excluir_id);
            $delPrecos->execute();

            $delMaterial = $conn->prepare("DELETE FROM materiais WHERE id = ? AND empresa_id = ?");
            $delMaterial->bind_param('ii', $excluir_id, $empresa_id);
            $delMaterial->execute();
        }
    }
    header("Location: estoque.php");
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
        $materiais = $conn->query("SELECT * FROM materiais WHERE empresa_id = '$empresa_id' ORDER BY nome");
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
        $listas = $conn->query("SELECT * FROM listas_precos WHERE empresa_id = '$empresa_id' ORDER BY created_at DESC");
        if ($listas->num_rows > 0) {
            echo "<ul class='list-group'>";
            while ($l = $listas->fetch_assoc()) {
                $padrao_badge = $l['padrao'] == 1 ? "<span class='badge bg-success me-2'>Padrão</span>" : "";
                $marcar_btn = $l['padrao'] == 1 ? "" : "<a href='cadastro_materiais.php?acao=marcar_padrao&id={$l['id']}' class='btn btn-outline-primary' title='Marcar como padrão'>Marcar Padrão</a>";
                echo "<li class='list-group-item d-flex justify-content-between align-items-center'>
                        <div>
                            {$padrao_badge}<strong>{$l['nome']}</strong>
                        </div>
                        <div class='btn-group btn-group-sm'>
                            <a href='definir_precos.php?lista_id={$l['id']}' class='btn btn-outline-secondary'>
                                <i class='bi bi-pencil'></i> Editar Preços
                            </a>
                            {$marcar_btn}
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
