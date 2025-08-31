<?php
require_once 'verifica_login.php';
require_once 'conexx/config.php';

$usuario_id = $_SESSION['usuario_id'];
$empresa_id = isset($_POST['empresa_id']) ? intval($_POST['empresa_id']) : $_SESSION['usuario_empresa'];

// Evita abrir caixa se já existe aberto
$existe_caixa_aberto = $conn->query("SELECT id FROM caixas WHERE status='aberto' AND empresa_id = " . intval($empresa_id))->num_rows;
if ($existe_caixa_aberto > 0) {
    // Se já existe, redireciona de volta para compra
    header('Location: compra.php');
    exit;
}

$valor_inicial = isset($_POST['valor_abertura']) ? floatval($_POST['valor_abertura']) : 0.0;
$data_abertura = date('Y-m-d H:i:s');

$stmt = $conn->prepare("INSERT INTO caixas (usuario_id, empresa_id, data_abertura, valor_inicial, status) VALUES (?, ?, ?, ?, 'aberto')");
$stmt->bind_param("iisd", $usuario_id, $empresa_id, $data_abertura, $valor_inicial);
$stmt->execute();
$caixa_id = $conn->insert_id;
$stmt->close();

// Nota: não registrar movimentação de abertura aqui para evitar duplicar o valor inicial

// Se vierem dados da compra, reenviá-los para salvar_compra.php via formulário automático
if (!empty($_POST['material_id'])) {
    ?>
    <form id="reenviarCompra" method="post" action="salvar_compra.php">
        <input type="hidden" name="cliente_id" value="<?php echo htmlspecialchars($_POST['cliente_id'] ?? ''); ?>">
        <input type="hidden" name="lista_preco_id" value="<?php echo htmlspecialchars($_POST['lista_preco_id'] ?? ''); ?>">
        <input type="hidden" name="empresa_id" value="<?php echo htmlspecialchars($empresa_id); ?>">
        <?php foreach ($_POST['material_id'] as $m): ?>
            <input type="hidden" name="material_id[]" value="<?php echo htmlspecialchars($m); ?>">
        <?php endforeach; ?>
        <?php foreach ($_POST['quantidade'] as $q): ?>
            <input type="hidden" name="quantidade[]" value="<?php echo htmlspecialchars($q); ?>">
        <?php endforeach; ?>
        <?php foreach ($_POST['preco_unitario'] as $p): ?>
            <input type="hidden" name="preco_unitario[]" value="<?php echo htmlspecialchars($p); ?>">
        <?php endforeach; ?>
    </form>
    <script>
        document.getElementById('reenviarCompra').submit();
    </script>
    <?php
    exit;
} else {
    header('Location: compra.php?caixa_aberto=1');
    exit;
}

?>
