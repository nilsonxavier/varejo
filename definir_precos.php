<?php
require_once 'conexx/config.php';
require_once 'verifica_login.php';

$empresa_id = $_SESSION['usuario_empresa'];

$lista_id = intval($_GET['lista_id'] ?? 0);
if ($lista_id <= 0) die("Lista inválida");

// Salvar Preços
if (isset($_POST['salvar_precos'])) {
    foreach ($_POST['precos'] as $material_id => $preco) {
        $material_id = intval($material_id);
        $preco = floatval($preco);

        $check = $conn->query("SELECT id FROM precos_materiais WHERE lista_id = $lista_id AND material_id = $material_id")->num_rows;

        if ($check > 0) {
            $stmt = $conn->prepare("UPDATE precos_materiais SET preco = ? WHERE lista_id = ? AND material_id = ?");
            $stmt->bind_param("dii", $preco, $lista_id, $material_id);
        } else {
            $stmt = $conn->prepare("INSERT INTO precos_materiais (lista_id, material_id, preco) VALUES (?, ?, ?)");
            $stmt->bind_param("iid", $lista_id, $material_id, $preco);
        }
        $stmt->execute();
    }
    header("Location: cadastro_materiais.php");
    exit;
}

// Nome da Lista
$lista = $conn->query("SELECT nome FROM listas_precos WHERE id = $lista_id")->fetch_assoc();

include __DIR__.'/includes/header.php';
include __DIR__.'/includes/navbar.php';
include __DIR__.'/includes/footer.php';
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Definir Preços - <?= htmlspecialchars($lista['nome']) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .section-card {
            background-color: #ffffff;
            border-radius: 12px;
            padding: 20px;
            box-shadow: 0 0 10px rgba(0,0,0,0.05);
            margin-top: 30px;
        }
        h2 {
            margin-bottom: 20px;
            color: #343a40;
        }
        table input {
            border-radius: 8px;
        }
        .btn-success, .btn-secondary {
            border-radius: 8px;
        }
    </style>
</head>
<body>

<div class="container py-4">

    <div class="section-card">
        <h2><i class="bi bi-currency-dollar"></i> Definir Preços - <?= htmlspecialchars($lista['nome']) ?></h2>

        <form method="post">
            <div class="table-responsive">
                <table class="table table-bordered align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Material</th>
                            <th>Preço (R$)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        // Mostra somente materiais da empresa do usuário
                        $materiais = $conn->query("SELECT * FROM materiais WHERE empresa_id = $empresa_id ORDER BY nome");
                        while ($m = $materiais->fetch_assoc()):
                            $preco = $conn->query("SELECT preco FROM precos_materiais WHERE lista_id = $lista_id AND material_id = {$m['id']}")->fetch_assoc()['preco'] ?? '';
                        ?>
                            <tr>
                                <td><?= htmlspecialchars($m['nome']) ?></td>
                                <td>
                                    <input type="number" name="precos[<?= $m['id'] ?>]" step="0.01" min="0" class="form-control" value="<?= htmlspecialchars($preco) ?>">
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>

            <div class="d-flex gap-2 mt-3">
                <button type="submit" name="salvar_precos" class="btn btn-success">
                    <i class="bi bi-save"></i> Salvar Preços
                </button>
                <a href="cadastro_materiais.php" class="btn btn-secondary">
                    <i class="bi bi-arrow-left"></i> Voltar
                </a>
            </div>
        </form>
    </div>

</div>

<!-- Bootstrap Icons -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
</body>
</html>
