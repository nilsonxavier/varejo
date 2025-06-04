<?php
require_once 'conexx/config.php';
session_start();

// Verifica se o formulário foi enviado
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $senha = trim($_POST['senha']);

    // Validação simples
    if (empty($email) || empty($senha)) {
        $erro = "Por favor, preencha todos os campos.";
    } else {
        // Consulta segura com prepared statement
        $stmt = $conn->prepare("SELECT * FROM usuarios WHERE email = ? LIMIT 1");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $resultado = $stmt->get_result();

        if ($resultado->num_rows === 1) {
            $usuario = $resultado->fetch_assoc();

            // Verificação de senha (com hash)
            if (password_verify($senha, $usuario['senha'])) {
            
                $_SESSION['usuario_id'] = $usuario['id'];
                $_SESSION['usuario_nome'] = $usuario['nome'];
                $_SESSION['usuario_tipo'] = $usuario['tipo'];

                header("Location: dashboard.php");
                //$erro = "Senha correta";
                exit;
            } else {
                $erro = "Senha incorreta.";
            }
        } else {
            $erro = "Usuário não encontrado.";
        }
    }
}
?>

<?php include __DIR__.'/includes/header.php'; ?>

<style>
    body {
        background: linear-gradient(135deg, #4f46e5, #3b82f6);
        min-height: 100vh;
        display: flex;
        align-items: center;
        justify-content: center;
        font-family: 'Segoe UI', sans-serif;
    }

    .login-card {
        background-color: #fff;
        border-radius: 1rem;
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
        padding: 2.5rem;
        width: 100%;
        max-width: 400px;
    }

    .login-card .form-control {
        border-radius: 0.5rem;
    }

    .login-card h3 {
        color: #1f2937;
    }

    .login-card .btn-primary {
        border-radius: 0.5rem;
        background-color: #4f46e5;
        border: none;
    }

    .login-card .btn-primary:hover {
        background-color: #4338ca;
    }

    .logo {
        width: 60px;
        margin-bottom: 1rem;
    }
</style>

<div class="login-card">
    <div class="text-center mb-4">
        <img src="logo.png" alt="Logo" class="logo">
        <h3>Entrar no Sistema</h3>
    </div>
    <?php if (isset($erro)): ?>
        <div class="alert alert-danger text-center"><?php echo $erro; ?></div>
    <?php endif; ?>
    <form method="post" action="">
        <div class="mb-3">
            <label for="email" class="form-label">E-mail</label>
            <input type="email" name="email" class="form-control" id="email" required placeholder="Digite seu e-mail">
        </div>
        <div class="mb-3">
            <label for="senha" class="form-label">Senha</label>
            <input type="password" name="senha" class="form-control" id="senha" required placeholder="Digite sua senha">
        </div>
        <button type="submit" class="btn btn-primary w-100">Entrar</button>
    </form>
</div>

<?php include __DIR__.'/includes/footer.php'; ?>
