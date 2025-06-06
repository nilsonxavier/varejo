<?php
require_once 'conexx/config.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $senha = trim($_POST['senha']);

    if (empty($email) || empty($senha)) {
        $erro = "Por favor, preencha todos os campos.";
    } else {
        $stmt = $conn->prepare("SELECT * FROM usuarios WHERE email = ? LIMIT 1");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $resultado = $stmt->get_result();

        if ($resultado->num_rows === 1) {
            $usuario = $resultado->fetch_assoc();

            if (password_verify($senha, $usuario['senha'])) {
                $_SESSION['usuario_id'] = $usuario['id'];
                $_SESSION['usuario_nome'] = $usuario['nome'];
                $_SESSION['usuario_tipo'] = $usuario['tipo'];
                header("Location: index.php");
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

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Login - NC Recicláveis</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #0f2027, #203a43, #2c5364);
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .login-card {
            background: #ffffff;
            border-radius: 20px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.25);
            padding: 40px;
            width: 100%;
            max-width: 400px;
            animation: fadeIn 1s ease-in-out;
        }

        .login-card h2 {
            font-weight: 700;
            color: #2c3e50;
            text-align: center;
            margin-bottom: 30px;
        }

        .form-control {
            border-radius: 12px;
            padding: 12px 15px;
        }

        .btn-primary {
            background: #2c5364;
            border: none;
            border-radius: 12px;
            padding: 12px;
            font-weight: 600;
            transition: background 0.3s;
        }

        .btn-primary:hover {
            background: #203a43;
        }

        .form-text {
            text-align: center;
            margin-top: 10px;
        }

        @keyframes fadeIn {
            0% { transform: translateY(-20px); opacity: 0; }
            100% { transform: translateY(0); opacity: 1; }
        }
    </style>
</head>
<body>
    <div class="login-card">
        <h2>NC Recicláveis</h2>
        <?php if (isset($erro)): ?>
            <div class="alert alert-danger text-center"><?php echo $erro; ?></div>
        <?php endif; ?>
        <form method="post" action="">
            <div class="mb-3">
                <label for="email" class="form-label">E-mail</label>
                <input type="email" class="form-control" id="email" name="email" required placeholder="Digite seu e-mail">
            </div>
            <div class="mb-3">
                <label for="senha" class="form-label">Senha</label>
                <input type="password" class="form-control" id="senha" name="senha" required placeholder="Digite sua senha">
            </div>
            <button type="submit" class="btn btn-primary w-100">Entrar</button>
            <div class="form-text text-muted">Acesso restrito</div>
        </form>
    </div>
</body>
</html>
