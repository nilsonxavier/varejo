<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <!-- Chart.js -->
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

  <!-- Bootstrap CSS -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">

<?php 
// Incluir configurações globais do sistema
if (file_exists(__DIR__ . '/configuracoes_globais.php')) {
    include_once __DIR__ . '/configuracoes_globais.php';
    // Aplicar estilos automaticamente
    incluirEstilosConfiguracoes();
}
?>

<!-- Bootstrap JS Bundle -->
<!-- <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script> -->


  <style>
    html, body {
      margin: 0;
      padding: 0;
    }

    .btn-image {
      border: none;
      padding: 0;
      background-color: transparent;
      display: block;
      text-align: center;
      width: 100%;
    }

    .btn-image img {
      width: 150px;
      height: 150px;
      object-fit: cover;
      border-radius: 8px;
    }

    .image-title {
      margin-top: 8px;
      font-weight: bold;
      font-size: 1rem;
      color: #333;
      text-align: center;
      word-wrap: break-word;
    }

    /* Responsividade */
    @media (max-width: 576px) {
      .btn-image img {
        width: 100%;
        height: auto;
      }
    }
  </style>


<!-- jQuery e Select2 CDN -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<!-- Sistema de Configurações -->
<script src="js/configuracoes.js"></script>






</head>
<body class="m-0 p-0">
  <div class="container-fluid p-0">
