<?php
require_once 'verifica_login.php';
require_once 'conexx/config.php';

$usuario_id = $_SESSION['usuario_id'];

// Verifica se existe caixa aberto
$caixa_aberto = $conn->query("SELECT * FROM caixas WHERE status = 'aberto' ORDER BY id DESC LIMIT 1")->fetch_assoc();

// Abertura de caixa
if (isset($_POST['abrir_caixa'])) {
    // Revalida para evitar duplicação em requisições simultâneas
    $existe_caixa_aberto = $conn->query("SELECT id FROM caixas WHERE status = 'aberto'")->num_rows;
    if ($existe_caixa_aberto > 0) {
        header("Location: caixa.php");
        exit;
    }

    $valor_inicial = floatval($_POST['valor_inicial']);
    $stmt = $conn->prepare("INSERT INTO caixas (usuario_id, data_abertura, valor_inicial, status) VALUES (?, NOW(), ?, 'aberto')");
    $stmt->bind_param("id", $usuario_id, $valor_inicial);
    $stmt->execute();
    header("Location: caixa.php");
    exit;
}

// Registrar movimentação
if (isset($_POST['registrar_movimentacao']) && $caixa_aberto) {
    $tipo = $_POST['tipo'];
    $valor = floatval($_POST['valor']);
    $descricao = trim($_POST['descricao']);
    $caixa_id = $caixa_aberto['id'];

    $stmt = $conn->prepare("INSERT INTO movimentacoes (caixa_id, tipo, valor, descricao, data_movimentacao) VALUES (?, ?, ?, ?, NOW())");
    $stmt->bind_param("isds", $caixa_id, $tipo, $valor, $descricao);
    $stmt->execute();
    header("Location: caixa.php");
    exit;
}

// Fechar caixa com transação
if (isset($_POST['fechar_caixa']) && $caixa_aberto) {
    $conn->begin_transaction();

    try {
        $caixa_id = $caixa_aberto['id'];

        $total_entradas = $conn->query("SELECT SUM(valor) as total FROM movimentacoes WHERE caixa_id = $caixa_id AND tipo = 'entrada'")->fetch_assoc()['total'] ?? 0;
        $total_saidas = $conn->query("SELECT SUM(valor) as total FROM movimentacoes WHERE caixa_id = $caixa_id AND tipo = 'saida'")->fetch_assoc()['total'] ?? 0;

        $valor_final = $caixa_aberto['valor_inicial'] + $total_entradas - $total_saidas;

        $stmt = $conn->prepare("UPDATE caixas SET data_fechamento = NOW(), valor_final = ?, status = 'fechado' WHERE id = ?");
        $stmt->bind_param("di", $valor_final, $caixa_id);
        $stmt->execute();

        $conn->commit();
    } catch (Exception $e) {
        $conn->rollback();
        die("Erro ao fechar caixa: " . $e->getMessage());
    }

    header("Location: caixa.php");
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
    <title>Controle de Caixa</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="p-4 bg-light">
<div class="container bg-white p-4 rounded shadow">
    <h2 class="mb-4">Controle de Caixa</h2>

    <?php if (!$caixa_aberto): ?>
        <form method="post" class="mb-4">
            <label class="form-label">Valor Inicial:</label>
            <input type="number" name="valor_inicial" step="0.01" min="0" required class="form-control mb-2">
            <button type="submit" name="abrir_caixa" class="btn btn-success">Abrir Caixa</button>
        </form>
    <?php else: ?>
        <div class="mb-4">
            <p><strong>Caixa aberto em:</strong> <?= $caixa_aberto['data_abertura'] ?></p>
            <p><strong>Valor inicial:</strong> R$ <?= number_format($caixa_aberto['valor_inicial'], 2, ',', '.') ?></p>
        </div>

        <form method="post" class="row g-2 mb-4">
            <div class="col-md-2">
                <select name="tipo" class="form-select" required>
                    <option value="entrada">Entrada</option>
                    <option value="saida">Saída</option>
                </select>
            </div>
            <div class="col-md-2">
                <input type="number" name="valor" step="0.01" min="0" required class="form-control" placeholder="Valor">
            </div>
            <div class="col-md-6">
                <input type="text" name="descricao" required class="form-control" placeholder="Descrição">
            </div>
            <div class="col-md-2">
                <button type="submit" name="registrar_movimentacao" class="btn btn-primary w-100">Registrar</button>
            </div>
        </form>

        <form method="post">
            <button type="submit" name="fechar_caixa" class="btn btn-danger">Fechar Caixa</button>
        </form>

        <hr>

        <h4>Movimentações</h4>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Tipo</th>
                    <th>Valor</th>
                    <th>Descrição</th>
                    <th>Data</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $caixa_id = $caixa_aberto['id'];
                $stmt = $conn->prepare("SELECT tipo, valor, descricao, data_movimentacao FROM movimentacoes WHERE caixa_id = ? ORDER BY data_movimentacao DESC");
                $stmt->bind_param("i", $caixa_id);
                $stmt->execute();
                $movs = $stmt->get_result();

                $total_entradas = 0;
                $total_saidas = 0;
                while ($mov = $movs->fetch_assoc()):
                    if ($mov['tipo'] === 'entrada') {
                        $total_entradas += $mov['valor'];
                    } else {
                        $total_saidas += $mov['valor'];
                    }
                ?>
                    <tr>
                        <td><?= ucfirst($mov['tipo']) ?></td>
                        <td>R$ <?= number_format($mov['valor'], 2, ',', '.') ?></td>
                        <td><?= htmlspecialchars($mov['descricao']) ?></td>
                        <td><?= $mov['data_movimentacao'] ?></td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>

        <?php
        $saldo_atual = $caixa_aberto['valor_inicial'] + $total_entradas - $total_saidas;
        ?>
        <div class="mt-4">
            <p><strong>Total de Entradas:</strong> R$ <?= number_format($total_entradas, 2, ',', '.') ?></p>
            <p><strong>Total de Saídas:</strong> R$ <?= number_format($total_saidas, 2, ',', '.') ?></p>
            <p><strong>Saldo Atual:</strong> R$ <?= number_format($saldo_atual, 2, ',', '.') ?></p>
        </div>
    <?php endif; ?>
    <hr class="my-5">

<h3>Caixas Anteriores</h3>
<table class="table table-bordered table-striped">
    <thead class="table-secondary">
        <tr>
            <th>ID</th>
            <th>Aberto em</th>
            <th>Fechado em</th>
            <th>Valor Inicial</th>
            <th>Valor Final</th>
            <th>Ações</th>
        </tr>
    </thead>
    <tbody>
        <?php
        $result = $conn->query("SELECT * FROM caixas WHERE status = 'fechado' ORDER BY id DESC LIMIT 10");
        while ($cx = $result->fetch_assoc()):
        ?>
            <tr>
                <td><?= $cx['id'] ?></td>
                <td><?= $cx['data_abertura'] ?></td>
                <td><?= $cx['data_fechamento'] ?></td>
                <td>R$ <?= number_format($cx['valor_inicial'], 2, ',', '.') ?></td>
                <td>R$ <?= number_format($cx['valor_final'], 2, ',', '.') ?></td>
                <td>
                    <a href="detalhes_caixa.php?id=<?= $cx['id'] ?>" class="btn btn-sm btn-outline-primary">Ver</a>
                </td>
            </tr>
        <?php endwhile; ?>
    </tbody>
</table>

</div>
</body>
</html>
