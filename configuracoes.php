<?php
require_once 'verifica_login.php';
require_once 'conexx/config.php';

// Verificar se o usuário é admin
if ($_SESSION['usuario_tipo'] !== 'admin') {
    header('Location: index.php');
    exit;
}

$empresa_id = $_SESSION['usuario_empresa'];
$message = '';
$messageType = '';

// Buscar dados da empresa
$empresa = null;
$stmt = $conn->prepare("SELECT * FROM empresas WHERE id = ?");
$stmt->bind_param('i', $empresa_id);
$stmt->execute();
$result = $stmt->get_result();
if ($result && $result->num_rows > 0) {
    $empresa = $result->fetch_assoc();
}

// Buscar configurações existentes ou criar padrão
$configuracoes = null;
$stmt = $conn->prepare("SELECT * FROM configuracoes WHERE empresa_id = ?");
$stmt->bind_param('i', $empresa_id);
$stmt->execute();
$result = $stmt->get_result();
if ($result && $result->num_rows > 0) {
    $configuracoes = $result->fetch_assoc();
} else {
    // Criar configurações padrão se não existir
    $stmt = $conn->prepare("INSERT INTO configuracoes (empresa_id, tamanho_papel, tema_dark) VALUES (?, 'A4', 0)");
    $stmt->bind_param('i', $empresa_id);
    $stmt->execute();
    
    // Buscar novamente
    $stmt = $conn->prepare("SELECT * FROM configuracoes WHERE empresa_id = ?");
    $stmt->bind_param('i', $empresa_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $configuracoes = $result->fetch_assoc();
}

// Processar formulário
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if (isset($_POST['acao']) && $_POST['acao'] === 'empresa') {
            // Atualizar dados da empresa
            $razao_social = trim($_POST['razao_social']);
            $nome_fantasia = trim($_POST['nome_fantasia']);
            $cnpj = trim($_POST['cnpj']);
            $email = trim($_POST['email']);
            $telefone = trim($_POST['telefone']);
            $endereco = trim($_POST['endereco']);
            $cidade = trim($_POST['cidade']);
            $estado = trim($_POST['estado']);
            $cep = trim($_POST['cep']);
            
            $stmt = $conn->prepare("UPDATE empresas SET razao_social = ?, nome_fantasia = ?, cnpj = ?, email = ?, telefone = ?, endereco = ?, cidade = ?, estado = ?, cep = ? WHERE id = ?");
            $stmt->bind_param('sssssssssi', $razao_social, $nome_fantasia, $cnpj, $email, $telefone, $endereco, $cidade, $estado, $cep, $empresa_id);
            
            if ($stmt->execute()) {
                $message = 'Dados da empresa atualizados com sucesso!';
                $messageType = 'success';
                // Atualizar dados locais
                $empresa['razao_social'] = $razao_social;
                $empresa['nome_fantasia'] = $nome_fantasia;
                $empresa['cnpj'] = $cnpj;
                $empresa['email'] = $email;
                $empresa['telefone'] = $telefone;
                $empresa['endereco'] = $endereco;
                $empresa['cidade'] = $cidade;
                $empresa['estado'] = $estado;
                $empresa['cep'] = $cep;
            } else {
                $message = 'Erro ao atualizar dados da empresa!';
                $messageType = 'danger';
            }
            
        } elseif (isset($_POST['acao']) && $_POST['acao'] === 'configuracoes') {
            // Atualizar configurações
            $tamanho_papel = $_POST['tamanho_papel'];
            $tema_dark = isset($_POST['tema_dark']) ? 1 : 0;
            
            $stmt = $conn->prepare("UPDATE configuracoes SET tamanho_papel = ?, tema_dark = ? WHERE empresa_id = ?");
            $stmt->bind_param('sii', $tamanho_papel, $tema_dark, $empresa_id);
            
            if ($stmt->execute()) {
                $message = 'Configurações atualizadas com sucesso!';
                $messageType = 'success';
                // Atualizar dados locais
                $configuracoes['tamanho_papel'] = $tamanho_papel;
                $configuracoes['tema_dark'] = $tema_dark;
            } else {
                $message = 'Erro ao atualizar configurações!';
                $messageType = 'danger';
            }
        }
    } catch (Exception $e) {
        $message = 'Erro: ' . $e->getMessage();
        $messageType = 'danger';
    }
}

include __DIR__.'/includes/header.php';
include __DIR__.'/includes/navbar.php';
?>

<head>
    <meta charset="UTF-8">
    <title>Configurações - Sistema</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    
    <style>
        .section-card {
            background-color: #ffffff;
            border-radius: 12px;
            padding: 25px;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.08);
            margin-bottom: 25px;
            border: 1px solid #e9ecef;
        }

        .section-header {
            border-bottom: 2px solid #f8f9fa;
            padding-bottom: 15px;
            margin-bottom: 20px;
        }

        .section-header h4 {
            color: #495057;
            font-weight: 600;
            margin: 0;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .form-label {
            font-weight: 500;
            color: #495057;
            margin-bottom: 8px;
        }

        .form-control:focus {
            border-color: #86b7fe;
            box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
        }

        .btn-primary {
            background: linear-gradient(135deg, #0d6efd, #0a58ca);
            border: none;
            font-weight: 500;
            padding: 10px 25px;
            border-radius: 8px;
            transition: all 0.3s ease;
        }

        .btn-primary:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(13, 110, 253, 0.3);
        }

        .paper-preview {
            border: 2px solid #dee2e6;
            border-radius: 8px;
            padding: 15px;
            text-align: center;
            background: #f8f9fa;
            margin-top: 10px;
            transition: all 0.3s ease;
        }

        .paper-preview.active {
            border-color: #0d6efd;
            background: #e7f1ff;
        }

        .theme-toggle {
            position: relative;
            display: inline-block;
            width: 60px;
            height: 34px;
        }

        .theme-toggle input {
            opacity: 0;
            width: 0;
            height: 0;
        }

        .slider {
            position: absolute;
            cursor: pointer;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: #ccc;
            transition: .4s;
            border-radius: 34px;
        }

        .slider:before {
            position: absolute;
            content: "";
            height: 26px;
            width: 26px;
            left: 4px;
            bottom: 4px;
            background-color: white;
            transition: .4s;
            border-radius: 50%;
        }

        input:checked + .slider {
            background-color: #2196F3;
        }

        input:checked + .slider:before {
            transform: translateX(26px);
        }

        .preview-sample {
            background: #343a40;
            color: white;
            padding: 15px;
            border-radius: 8px;
            margin-top: 10px;
            transition: all 0.3s ease;
        }

        .preview-sample.light {
            background: #f8f9fa;
            color: #495057;
            border: 1px solid #dee2e6;
        }

        .alert {
            border-radius: 10px;
            border: none;
        }

        @media print {
            .no-print {
                display: none !important;
            }
        }
    </style>
</head>

<body>
    <div class="container py-4">
        <div class="row">
            <div class="col-12">
                <div class="d-flex align-items-center mb-4">
                    <i class="bi bi-gear-fill text-primary fs-2 me-3"></i>
                    <h2 class="mb-0">Configurações do Sistema</h2>
                </div>

                <?php if ($message): ?>
                    <div class="alert alert-<?php echo $messageType; ?> alert-dismissible fade show" role="alert">
                        <i class="bi bi-<?php echo $messageType === 'success' ? 'check-circle' : 'exclamation-triangle'; ?>-fill me-2"></i>
                        <?php echo $message; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="row g-4">
            <!-- Configurações da Empresa -->
            <div class="col-lg-8">
                <div class="section-card">
                    <div class="section-header">
                        <h4><i class="bi bi-building"></i> Dados da Empresa</h4>
                    </div>
                    
                    <form method="POST" action="">
                        <input type="hidden" name="acao" value="empresa">
                        
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="razao_social" class="form-label">Razão Social *</label>
                                <input type="text" class="form-control" id="razao_social" name="razao_social" 
                                       value="<?php echo htmlspecialchars($empresa['razao_social'] ?? ''); ?>" required>
                            </div>
                            
                            <div class="col-md-6">
                                <label for="nome_fantasia" class="form-label">Nome Fantasia</label>
                                <input type="text" class="form-control" id="nome_fantasia" name="nome_fantasia" 
                                       value="<?php echo htmlspecialchars($empresa['nome_fantasia'] ?? ''); ?>">
                            </div>
                            
                            <div class="col-md-6">
                                <label for="cnpj" class="form-label">CNPJ *</label>
                                <input type="text" class="form-control" id="cnpj" name="cnpj" 
                                       value="<?php echo htmlspecialchars($empresa['cnpj'] ?? ''); ?>" required>
                            </div>
                            
                            <div class="col-md-6">
                                <label for="email" class="form-label">E-mail</label>
                                <input type="email" class="form-control" id="email" name="email" 
                                       value="<?php echo htmlspecialchars($empresa['email'] ?? ''); ?>">
                            </div>
                            
                            <div class="col-md-6">
                                <label for="telefone" class="form-label">Telefone</label>
                                <input type="text" class="form-control" id="telefone" name="telefone" 
                                       value="<?php echo htmlspecialchars($empresa['telefone'] ?? ''); ?>">
                            </div>
                            
                            <div class="col-md-6">
                                <label for="cep" class="form-label">CEP</label>
                                <input type="text" class="form-control" id="cep" name="cep" 
                                       value="<?php echo htmlspecialchars($empresa['cep'] ?? ''); ?>">
                            </div>
                            
                            <div class="col-12">
                                <label for="endereco" class="form-label">Endereço</label>
                                <input type="text" class="form-control" id="endereco" name="endereco" 
                                       value="<?php echo htmlspecialchars($empresa['endereco'] ?? ''); ?>">
                            </div>
                            
                            <div class="col-md-6">
                                <label for="cidade" class="form-label">Cidade</label>
                                <input type="text" class="form-control" id="cidade" name="cidade" 
                                       value="<?php echo htmlspecialchars($empresa['cidade'] ?? ''); ?>">
                            </div>
                            
                            <div class="col-md-6">
                                <label for="estado" class="form-label">Estado</label>
                                <select class="form-control" id="estado" name="estado">
                                    <option value="">Selecione...</option>
                                    <option value="AC" <?php echo ($empresa['estado'] ?? '') === 'AC' ? 'selected' : ''; ?>>Acre</option>
                                    <option value="AL" <?php echo ($empresa['estado'] ?? '') === 'AL' ? 'selected' : ''; ?>>Alagoas</option>
                                    <option value="AP" <?php echo ($empresa['estado'] ?? '') === 'AP' ? 'selected' : ''; ?>>Amapá</option>
                                    <option value="AM" <?php echo ($empresa['estado'] ?? '') === 'AM' ? 'selected' : ''; ?>>Amazonas</option>
                                    <option value="BA" <?php echo ($empresa['estado'] ?? '') === 'BA' ? 'selected' : ''; ?>>Bahia</option>
                                    <option value="CE" <?php echo ($empresa['estado'] ?? '') === 'CE' ? 'selected' : ''; ?>>Ceará</option>
                                    <option value="DF" <?php echo ($empresa['estado'] ?? '') === 'DF' ? 'selected' : ''; ?>>Distrito Federal</option>
                                    <option value="ES" <?php echo ($empresa['estado'] ?? '') === 'ES' ? 'selected' : ''; ?>>Espírito Santo</option>
                                    <option value="GO" <?php echo ($empresa['estado'] ?? '') === 'GO' ? 'selected' : ''; ?>>Goiás</option>
                                    <option value="MA" <?php echo ($empresa['estado'] ?? '') === 'MA' ? 'selected' : ''; ?>>Maranhão</option>
                                    <option value="MT" <?php echo ($empresa['estado'] ?? '') === 'MT' ? 'selected' : ''; ?>>Mato Grosso</option>
                                    <option value="MS" <?php echo ($empresa['estado'] ?? '') === 'MS' ? 'selected' : ''; ?>>Mato Grosso do Sul</option>
                                    <option value="MG" <?php echo ($empresa['estado'] ?? '') === 'MG' ? 'selected' : ''; ?>>Minas Gerais</option>
                                    <option value="PA" <?php echo ($empresa['estado'] ?? '') === 'PA' ? 'selected' : ''; ?>>Pará</option>
                                    <option value="PB" <?php echo ($empresa['estado'] ?? '') === 'PB' ? 'selected' : ''; ?>>Paraíba</option>
                                    <option value="PR" <?php echo ($empresa['estado'] ?? '') === 'PR' ? 'selected' : ''; ?>>Paraná</option>
                                    <option value="PE" <?php echo ($empresa['estado'] ?? '') === 'PE' ? 'selected' : ''; ?>>Pernambuco</option>
                                    <option value="PI" <?php echo ($empresa['estado'] ?? '') === 'PI' ? 'selected' : ''; ?>>Piauí</option>
                                    <option value="RJ" <?php echo ($empresa['estado'] ?? '') === 'RJ' ? 'selected' : ''; ?>>Rio de Janeiro</option>
                                    <option value="RN" <?php echo ($empresa['estado'] ?? '') === 'RN' ? 'selected' : ''; ?>>Rio Grande do Norte</option>
                                    <option value="RS" <?php echo ($empresa['estado'] ?? '') === 'RS' ? 'selected' : ''; ?>>Rio Grande do Sul</option>
                                    <option value="RO" <?php echo ($empresa['estado'] ?? '') === 'RO' ? 'selected' : ''; ?>>Rondônia</option>
                                    <option value="RR" <?php echo ($empresa['estado'] ?? '') === 'RR' ? 'selected' : ''; ?>>Roraima</option>
                                    <option value="SC" <?php echo ($empresa['estado'] ?? '') === 'SC' ? 'selected' : ''; ?>>Santa Catarina</option>
                                    <option value="SP" <?php echo ($empresa['estado'] ?? '') === 'SP' ? 'selected' : ''; ?>>São Paulo</option>
                                    <option value="SE" <?php echo ($empresa['estado'] ?? '') === 'SE' ? 'selected' : ''; ?>>Sergipe</option>
                                    <option value="TO" <?php echo ($empresa['estado'] ?? '') === 'TO' ? 'selected' : ''; ?>>Tocantins</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="d-flex justify-content-end mt-4">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check-lg me-2"></i>Salvar Dados da Empresa
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Configurações do Sistema -->
            <div class="col-lg-4">
                <!-- Configurações de Impressão -->
                <div class="section-card">
                    <div class="section-header">
                        <h4><i class="bi bi-printer"></i> Impressão</h4>
                    </div>
                    
                    <form method="POST" action="">
                        <input type="hidden" name="acao" value="configuracoes">
                        
                        <div class="mb-4">
                            <label class="form-label">Tamanho do Papel</label>
                            
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="tamanho_papel" id="papel_a4" value="A4" 
                                       <?php echo ($configuracoes['tamanho_papel'] ?? 'A4') === 'A4' ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="papel_a4">
                                    <strong>A4</strong> (210 x 297 mm)
                                </label>
                                <div class="paper-preview" data-size="A4">
                                    <i class="bi bi-file-earmark-text fs-2"></i>
                                    <div>Documento completo</div>
                                </div>
                            </div>
                            
                            <div class="form-check mt-3">
                                <input class="form-check-input" type="radio" name="tamanho_papel" id="papel_80mm" value="80mm" 
                                       <?php echo ($configuracoes['tamanho_papel'] ?? '') === '80mm' ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="papel_80mm">
                                    <strong>80mm</strong> (Cupom fiscal)
                                </label>
                                <div class="paper-preview" data-size="80mm">
                                    <i class="bi bi-receipt fs-2"></i>
                                    <div>Cupom padrão</div>
                                </div>
                            </div>
                            
                            <div class="form-check mt-3">
                                <input class="form-check-input" type="radio" name="tamanho_papel" id="papel_60mm" value="60mm" 
                                       <?php echo ($configuracoes['tamanho_papel'] ?? '') === '60mm' ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="papel_60mm">
                                    <strong>60mm</strong> (Cupom pequeno)
                                </label>
                                <div class="paper-preview" data-size="60mm">
                                    <i class="bi bi-receipt-cutoff fs-2"></i>
                                    <div>Cupom compacto</div>
                                </div>
                            </div>
                        </div>

                        <!-- Configurações de Tema -->
                        <div class="section-header">
                            <h4><i class="bi bi-palette"></i> Aparência</h4>
                        </div>
                        
                        <div class="mb-4">
                            <div class="d-flex justify-content-between align-items-center">
                                <label class="form-label mb-0">Tema Escuro</label>
                                <label class="theme-toggle">
                                    <input type="checkbox" name="tema_dark" id="tema_dark" 
                                           <?php echo ($configuracoes['tema_dark'] ?? 0) ? 'checked' : ''; ?>>
                                    <span class="slider"></span>
                                </label>
                            </div>
                            
                            <div class="preview-sample mt-3" id="theme-preview">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span>Preview do tema</span>
                                    <i class="bi bi-sun-fill" id="theme-icon"></i>
                                </div>
                                <small class="d-block mt-2">Exemplo de como ficará a interface</small>
                            </div>
                        </div>
                        
                        <div class="d-flex justify-content-end">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check-lg me-2"></i>Salvar Configurações
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Preview do papel
            const paperRadios = document.querySelectorAll('input[name="tamanho_papel"]');
            const paperPreviews = document.querySelectorAll('.paper-preview');
            
            function updatePaperPreview() {
                paperPreviews.forEach(preview => preview.classList.remove('active'));
                const selected = document.querySelector('input[name="tamanho_papel"]:checked');
                if (selected) {
                    const preview = document.querySelector(`[data-size="${selected.value}"]`);
                    if (preview) preview.classList.add('active');
                }
            }
            
            paperRadios.forEach(radio => {
                radio.addEventListener('change', updatePaperPreview);
            });
            updatePaperPreview();
            
            // Preview do tema
            const themeToggle = document.getElementById('tema_dark');
            const themePreview = document.getElementById('theme-preview');
            const themeIcon = document.getElementById('theme-icon');
            
            function updateThemePreview() {
                if (themeToggle.checked) {
                    themePreview.classList.remove('light');
                    themeIcon.className = 'bi bi-moon-fill';
                } else {
                    themePreview.classList.add('light');
                    themeIcon.className = 'bi bi-sun-fill';
                }
            }
            
            themeToggle.addEventListener('change', updateThemePreview);
            updateThemePreview();
            
            // Máscara para CNPJ
            const cnpjInput = document.getElementById('cnpj');
            if (cnpjInput) {
                cnpjInput.addEventListener('input', function(e) {
                    let value = e.target.value.replace(/\D/g, '');
                    value = value.replace(/^(\d{2})(\d)/, '$1.$2');
                    value = value.replace(/^(\d{2})\.(\d{3})(\d)/, '$1.$2.$3');
                    value = value.replace(/\.(\d{3})(\d)/, '.$1/$2');
                    value = value.replace(/(\d{4})(\d)/, '$1-$2');
                    e.target.value = value;
                });
            }
            
            // Máscara para CEP
            const cepInput = document.getElementById('cep');
            if (cepInput) {
                cepInput.addEventListener('input', function(e) {
                    let value = e.target.value.replace(/\D/g, '');
                    value = value.replace(/^(\d{5})(\d)/, '$1-$2');
                    e.target.value = value;
                });
            }
            
            // Máscara para telefone
            const telefoneInput = document.getElementById('telefone');
            if (telefoneInput) {
                telefoneInput.addEventListener('input', function(e) {
                    let value = e.target.value.replace(/\D/g, '');
                    if (value.length <= 10) {
                        value = value.replace(/^(\d{2})(\d)/, '($1) $2');
                        value = value.replace(/(\d{4})(\d)/, '$1-$2');
                    } else {
                        value = value.replace(/^(\d{2})(\d)/, '($1) $2');
                        value = value.replace(/(\d{5})(\d)/, '$1-$2');
                    }
                    e.target.value = value;
                });
            }
        });
    </script>

    <?php include __DIR__.'/includes/footer.php'; ?>
</body>
</html>
