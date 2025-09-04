<?php
require_once 'verifica_login.php';
require_once 'conexx/config.php';

// Recebe dados do formulário
$cliente_id = isset($_POST['cliente_id']) ? intval(explode(' ', $_POST['cliente_id'])[0]) : 0;
$lista_preco_id = isset($_POST['lista_preco_id']) ? intval(explode(' ', $_POST['lista_preco_id'])[0]) : 0;

// Fallback para lista de preços: POST > cliente > padrao da empresa > primeira lista da empresa
if ($lista_preco_id <= 0) {
    if ($cliente_id) {
        $cli_row = $conn->query("SELECT lista_preco_id FROM clientes WHERE id = " . intval($cliente_id) . " LIMIT 1")->fetch_assoc();
        if (!empty($cli_row['lista_preco_id'])) $lista_preco_id = intval($cli_row['lista_preco_id']);
    }
}
if ($lista_preco_id <= 0) {
    $stmt_lp = $conn->prepare("SELECT id FROM listas_precos WHERE empresa_id = ? AND padrao = 1 LIMIT 1");
    $stmt_lp->bind_param('i', $empresa_id);
    $stmt_lp->execute();
    $res_lp = $stmt_lp->get_result();
    if ($res_lp && $r_lp = $res_lp->fetch_assoc()) {
        $lista_preco_id = intval($r_lp['id']);
    } else {
        $stmt_lp2 = $conn->prepare("SELECT id FROM listas_precos WHERE empresa_id = ? ORDER BY id LIMIT 1");
        $stmt_lp2->bind_param('i', $empresa_id);
        $stmt_lp2->execute();
        $res_lp2 = $stmt_lp2->get_result();
        if ($res_lp2 && $r_lp2 = $res_lp2->fetch_assoc()) $lista_preco_id = intval($r_lp2['id']);
    }
}
$empresa_id = $_SESSION['usuario_empresa'];

$materiais = isset($_POST['material_id']) ? $_POST['material_id'] : [];
$quantidades = isset($_POST['quantidade']) ? $_POST['quantidade'] : [];
$precos = isset($_POST['preco_unitario']) ? $_POST['preco_unitario'] : [];

// Receber valores de pagamento
$valor_dinheiro = isset($_POST['valor_dinheiro']) ? floatval($_POST['valor_dinheiro']) : 0;
$valor_pix = isset($_POST['valor_pix']) ? floatval($_POST['valor_pix']) : 0;
$valor_cartao = isset($_POST['valor_cartao']) ? floatval($_POST['valor_cartao']) : 0;
$valor_abater = isset($_POST['valor_abater']) ? floatval($_POST['valor_abater']) : 0;
$gerar_troco = isset($_POST['gerar_troco']) ? intval($_POST['gerar_troco']) : 0;

if (!$empresa_id || count($materiais) == 0) {
    echo "<script>
        alert('Erro: Dados inválidos!');
        window.location.href = 'compra.php';
    </script>";
    exit;
}

// Salva a compra (exemplo simples, ajuste conforme sua lógica)
$result = $conn->query("SELECT id FROM caixas WHERE status='aberto' AND empresa_id = " . intval($empresa_id) . " LIMIT 1");
$caixa = $result->fetch_assoc();
if (!$caixa) {
        // Exibe modal solicitando valor de abertura do caixa
        include 'includes/header.php';
        ?>
        <div class="modal fade" id="abrirCaixaModal" tabindex="-1" aria-labelledby="abrirCaixaLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="abrirCaixaLabel">Caixa fechado</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
                    </div>
                    <form method="post" action="abrir_caixa.php">
                        <div class="modal-body">
                            <p>Não há caixa aberto para registrar a compra. Informe o valor para abrir o caixa:</p>
                            <div class="mb-3">
                                <label for="valor_abertura" class="form-label">Valor de abertura</label>
                                <input type="number" step="0.01" min="0" class="form-control" name="valor_abertura" id="valor_abertura" required>
                            </div>
                            <input type="hidden" name="empresa_id" value="<?php echo htmlspecialchars($empresa_id); ?>">
                            <!-- Preserva os dados da compra para reenviar após abrir o caixa -->
                            <?php foreach ($materiais as $idx => $m): ?>
                                <input type="hidden" name="material_id[]" value="<?php echo htmlspecialchars($m); ?>">
                            <?php endforeach; ?>
                            <?php foreach ($quantidades as $q): ?>
                                <input type="hidden" name="quantidade[]" value="<?php echo htmlspecialchars($q); ?>">
                            <?php endforeach; ?>
                            <?php foreach ($precos as $p): ?>
                                <input type="hidden" name="preco_unitario[]" value="<?php echo htmlspecialchars($p); ?>">
                            <?php endforeach; ?>
                            <input type="hidden" name="cliente_id" value="<?php echo htmlspecialchars($cliente_id); ?>">
                            <input type="hidden" name="lista_preco_id" value="<?php echo htmlspecialchars($lista_preco_id); ?>">
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                            <button type="submit" class="btn btn-primary">Abrir caixa e salvar compra</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <script>
            document.addEventListener('DOMContentLoaded', function() {
                var modal = new bootstrap.Modal(document.getElementById('abrirCaixaModal'));
                modal.show();
            });
        </script>

        <?php
        include 'includes/footer.php';
        exit;
}
$caixa_id = $caixa['id'];

// Monta itens como JSON e calcula total
$itens_array = [];
$total = 0.0;
for ($i = 0; $i < count($materiais); $i++) {
    $material_id = intval(explode(' ', $materiais[$i])[0]);
    $quantidade = floatval($quantidades[$i]);
    $preco_unitario = floatval($precos[$i]);
    $subtotal = $quantidade * $preco_unitario;
    $itens_array[] = [
        'material_id' => $material_id,
        'quantidade' => $quantidade,
        'preco_unitario' => $preco_unitario,
        'subtotal' => $subtotal
    ];
    $total += $subtotal;
}

$itens_json = json_encode($itens_array, JSON_UNESCAPED_UNICODE);

// Garantir que existe um registro em clientes_anterior para satisfazer a FK
$cliente_ant_id = null;
$res_ca = $conn->query("SELECT id FROM clientes_anterior WHERE id = " . intval($cliente_id));
if ($res_ca && $res_ca->num_rows > 0) {
    $cliente_ant_id = $cliente_id;
} else {
    // Tenta obter dados do cliente atual e criar um registro em clientes_anterior
    $cli = $conn->query("SELECT nome, cpf, lista_preco_id FROM clientes WHERE id = " . intval($cliente_id))->fetch_assoc();
    if ($cli) {
        $nome_cli = $cli['nome'];
        $cpf_cli = $cli['cpf'];
        $tabela_preco_cli = $cli['lista_preco_id'] ?? null;
        // Verifica se a tabela_preco_cli existe em tabelas_precos
        if ($tabela_preco_cli) {
            $res_tp = $conn->query("SELECT id FROM tabelas_precos WHERE id = " . intval($tabela_preco_cli));
            if (!$res_tp || $res_tp->num_rows == 0) {
                $tabela_preco_cli = null;
            }
        }
    } else {
        // Fallback: cria cliente com nome genérico
        $nome_cli = 'Cliente ' . intval($cliente_id);
        $cpf_cli = null;
        $tabela_preco_cli = null;
    }

    // Normaliza CPF: se vazio ou apenas espaços, trata como NULL para evitar duplicate key
    if (isset($cpf_cli)) {
        $cpf_cli = trim($cpf_cli);
        if ($cpf_cli === '') $cpf_cli = null;
    } else {
        $cpf_cli = null;
    }

    // Preparar query adequada dependendo se tabela_preco_cli e cpf são nulos
    if ($tabela_preco_cli === null) {
        if ($cpf_cli === null) {
            $stmt_ca = $conn->prepare("INSERT INTO clientes_anterior (nome) VALUES (?)");
            $stmt_ca->bind_param('s', $nome_cli);
        } else {
            $stmt_ca = $conn->prepare("INSERT INTO clientes_anterior (nome, cpf) VALUES (?, ?)");
            $stmt_ca->bind_param('ss', $nome_cli, $cpf_cli);
        }
    } else {
        if ($cpf_cli === null) {
            $stmt_ca = $conn->prepare("INSERT INTO clientes_anterior (nome, tabela_preco_id) VALUES (?, ?)");
            $stmt_ca->bind_param('si', $nome_cli, $tabela_preco_cli);
        } else {
            $stmt_ca = $conn->prepare("INSERT INTO clientes_anterior (nome, cpf, tabela_preco_id) VALUES (?, ?, ?)");
            $stmt_ca->bind_param('ssi', $nome_cli, $cpf_cli, $tabela_preco_cli);
        }
    }
    $stmt_ca->execute();
    $cliente_ant_id = $conn->insert_id;
    $stmt_ca->close();
}

$id_forma_pagamento = null;
// Garantir que existe pelo menos uma forma de pagamento válida (satisfazer FK)
$res_fp = $conn->query("SELECT id FROM formas_pagamento LIMIT 1");
if ($res_fp && $res_fp->num_rows > 0) {
    $id_forma_pagamento = $res_fp->fetch_assoc()['id'];
} else {
    // Insere formas padrão e obtém um id
    $conn->query("INSERT INTO formas_pagamento (descricao) VALUES ('Dinheiro'), ('Cartão'), ('Pix')");
    $res_fp2 = $conn->query("SELECT id FROM formas_pagamento ORDER BY id LIMIT 1");
    $id_forma_pagamento = $res_fp2->fetch_assoc()['id'];
}

// Processar valor a abater do saldo do cliente (se houver cliente selecionado e valor_abater > 0)
if ($cliente_id > 0 && $valor_abater > 0) {
    // Verificar saldo atual do cliente
    $stmt_saldo = $conn->prepare("SELECT saldo FROM clientes WHERE id = ?");
    $stmt_saldo->bind_param('i', $cliente_id);
    $stmt_saldo->execute();
    $res_saldo = $stmt_saldo->get_result();
    
    if ($res_saldo && $res_saldo->num_rows > 0) {
        $saldo_atual = floatval($res_saldo->fetch_assoc()['saldo']);
        $novo_saldo = $saldo_atual + $valor_abater; // SOMAR para reduzir dívida ou aumentar crédito
        
        // Atualizar saldo do cliente
        $stmt_upd_saldo = $conn->prepare("UPDATE clientes SET saldo = ? WHERE id = ?");
        $stmt_upd_saldo->bind_param('di', $novo_saldo, $cliente_id);
        $stmt_upd_saldo->execute();
        $stmt_upd_saldo->close();
        
        // Registrar movimentação no extrato do cliente
        $descricao_extrato = "Abatimento em compra (valor abatido: R$ " . number_format($valor_abater, 2, ',', '.') . ")";
        $stmt_extrato = $conn->prepare("INSERT INTO movimentacoes_clientes (cliente_id, tipo, valor, descricao, data_movimentacao, empresa_id, saldo_apos) VALUES (?, 'credito', ?, ?, NOW(), ?, ?)");
        $stmt_extrato->bind_param('idsid', $cliente_id, $valor_abater, $descricao_extrato, $empresa_id, $novo_saldo);
        $stmt_extrato->execute();
        $stmt_extrato->close();
    }
    $stmt_saldo->close();
}

// Inserir compra (nota: tabela usa coluna `id_cliente` e armazena itens em JSON)
$stmt = $conn->prepare("INSERT INTO compras (id_cliente, empresa_id, data_compra, valor_total, itens, id_forma_pagamento) VALUES (?, ?, NOW(), ?, ?, ?)");
$stmt->bind_param('iidsi', $cliente_ant_id, $empresa_id, $total, $itens_json, $id_forma_pagamento);
$stmt->execute();
$compra_id = $conn->insert_id;
$stmt->close();

// Registrar entradas no estoque por item
foreach ($itens_array as $item) {
    $material_id = $item['material_id'];
    $quantidade = $item['quantidade'];
    $stmt_estoque = $conn->prepare("INSERT INTO estoque (material_id, tipo, quantidade, data_movimentacao, descricao, empresa_id) VALUES (?, 'entrada', ?, NOW(), ?, ?)");
    $descricao = "Compra ID $compra_id";
    $stmt_estoque->bind_param('idsi', $material_id, $quantidade, $descricao, $empresa_id);
    $stmt_estoque->execute();
    $stmt_estoque->close();
}

// Registrar movimentação no caixa (saida) - apenas valores em dinheiro, pix e cartão
$valor_caixa = $valor_dinheiro + $valor_pix + $valor_cartao;
if ($valor_caixa > 0) {
    $stmt_mov = $conn->prepare("INSERT INTO movimentacoes (caixa_id, tipo, valor, descricao, data_movimentacao, empresa_id) VALUES (?, 'saida', ?, ?, NOW(), ?)");
    $descricao_mov = "Compra ID $compra_id - pagamento (Dinheiro: R$" . number_format($valor_dinheiro, 2, ',', '.') . 
                     ", Pix: R$" . number_format($valor_pix, 2, ',', '.') . 
                     ", Cartão: R$" . number_format($valor_cartao, 2, ',', '.') . ")";
    if ($valor_abater > 0) {
        $descricao_mov .= " - Abatido R$" . number_format($valor_abater, 2, ',', '.') . " do saldo do cliente";
    }
    $stmt_mov->bind_param('idsi', $caixa_id, $valor_caixa, $descricao_mov, $empresa_id);
    $stmt_mov->execute();
    $stmt_mov->close();
}

// Limpar compra suspensa do cliente (se houver) após finalização bem-sucedida
if ($cliente_id > 0) {
    $stmt_limpar = $conn->prepare("DELETE FROM compras_suspensas WHERE cliente_id = ? AND empresa_id = ?");
    $stmt_limpar->bind_param('ii', $cliente_id, $empresa_id);
    $stmt_limpar->execute();
    $stmt_limpar->close();
}

// Redireciona ou mostra mensagem de sucesso
header('Location: compra.php?sucesso=1');
exit;
