<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <meta name="description" content="Sistema ERP para Reciclagem de Materiais">
  <meta name="author" content="ERP Reciclagem">

  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
  
  <!-- CSS Responsivo Mobile -->
  <link href="css/responsive-mobile.css" rel="stylesheet">

  <!-- jQuery (carregado primeiro) -->
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  
  <!-- Bootstrap JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  
  <!-- Select2 -->
  <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
  <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
  
  <!-- Chart.js (carregado condicionalmente) -->
  <?php
  // Carregar Chart.js apenas em páginas que precisam
  $chart_pages = ['index.php', 'historico_vendas.php', 'dashboard.php', 'relatorios.php'];
  $current_page = basename($_SERVER['PHP_SELF']);
  if (in_array($current_page, $chart_pages)): ?>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <?php endif; ?>

<?php 
// Incluir configurações globais do sistema
if (file_exists(__DIR__ . '/configuracoes_globais.php')) {
    include_once __DIR__ . '/configuracoes_globais.php';
    // Aplicar estilos automaticamente
    incluirEstilosConfiguracoes();
}
?>

<!-- Sistema de Configurações -->
<script src="js/configuracoes.js"></script>

<!-- Utilitários ERP -->
<script src="js/erp-utils.js"></script>

  <style>
    html, body {
      margin: 0;
      padding: 0;
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }

    /* Botões com imagem responsivos */
    .btn-image {
      border: none;
      padding: 0;
      background-color: transparent;
      display: block;
      text-align: center;
      width: 100%;
      transition: transform 0.3s ease;
    }

    .btn-image:hover {
      transform: translateY(-2px);
    }

    .btn-image img {
      width: 150px;
      height: 150px;
      object-fit: cover;
      border-radius: 12px;
      transition: all 0.3s ease;
    }

    .btn-image:hover img {
      box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
    }

    .image-title {
      margin-top: 12px;
      font-weight: 600;
      font-size: 1rem;
      color: #333;
      text-align: center;
      word-wrap: break-word;
    }

    /* Responsividade para botões com imagem */
    @media (max-width: 768px) {
      .btn-image img {
        width: 120px;
        height: 120px;
      }
      
      .image-title {
        font-size: 0.9rem;
      }
    }
    
    @media (max-width: 576px) {
      .btn-image img {
        width: 100px;
        height: 100px;
      }
      
      .image-title {
        font-size: 0.85rem;
        margin-top: 8px;
      }
    }

    /* Loading overlay */
    .loading-overlay {
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background: rgba(0, 0, 0, 0.5);
      display: none;
      justify-content: center;
      align-items: center;
      z-index: 9999;
    }

    .loading-spinner {
      width: 40px;
      height: 40px;
      border: 4px solid #f3f3f3;
      border-top: 4px solid #007bff;
      border-radius: 50%;
      animation: spin 1s linear infinite;
    }

    @keyframes spin {
      0% { transform: rotate(0deg); }
      100% { transform: rotate(360deg); }
    }
  </style>
</head>
<body class="m-0 p-0">
  <!-- Loading Overlay -->
  <div class="loading-overlay" id="loadingOverlay">
    <div class="loading-spinner"></div>
  </div>

  <div class="container-fluid p-0">
