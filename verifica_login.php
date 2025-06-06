<?php
session_start();

if (!isset($_SESSION['usuario_id'])) {
    // Redireciona para login se não estiver logado
    header("Location: login.php");
    exit;
}
