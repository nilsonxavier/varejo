<style>
  .navbar-custom {
    background: linear-gradient(90deg, #212529, #343a40);
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.5);
    font-family: 'Segoe UI', sans-serif;
    font-weight: 500;
  }

  .navbar-brand {
    font-weight: bold;
    font-size: 1.4rem;
    letter-spacing: 1px;
  }

  .nav-link {
    transition: color 0.3s ease, background-color 0.3s ease;
  }

  .nav-link:hover,
  .dropdown-item:hover {
    color: #ffffff;
    background-color: rgba(255, 255, 255, 0.1);
  }

  .dropdown-menu {
    background-color: #343a40;
    border: none;
    border-radius: 8px;
    padding: 0.5rem;
    animation: fadeIn 0.2s ease-in-out;
  }

  .dropdown-item {
    color: #ccc;
    transition: background 0.3s;
    border-radius: 4px;
    padding: 0.5rem 1rem;
  }

  @keyframes fadeIn {
    from { opacity: 0; transform: translateY(-5px); }
    to { opacity: 1; transform: translateY(0); }
  }
</style>

<nav class="navbar navbar-expand-lg navbar-dark navbar-custom sticky-top">
  <div class="container-fluid">
    <a class="navbar-brand" href="index.php">Nc Sacolas</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarTogglerDemo03" aria-controls="navbarTogglerDemo03" aria-expanded="false" aria-label="Alternar navegação">
      <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse" id="navbarTogglerDemo03">
      <ul class="navbar-nav me-auto mb-2 mb-lg-0">

        <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle" href="#" id="produtoDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
            Produto
          </a>
          <ul class="dropdown-menu" aria-labelledby="produtoDropdown">
            <li><a class="dropdown-item" href="produtos.php">Lista</a></li>
            <li><a class="dropdown-item" href="cadProdutos.php">Cadastra</a></li>
          
          </ul>
        </li>

        <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle" href="#" id="clienteDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
            Cliente
          </a>
          <ul class="dropdown-menu" aria-labelledby="clienteDropdown">
            <li><a class="dropdown-item" href="clientes.php">Lista</a></li>
            <li><a class="dropdown-item" href="cadClientes.php">Cadastra</a></li>
        
          </ul>
        </li>

        <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle" href="#" id="producaoDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
            Produção
          </a>
          <ul class="dropdown-menu" aria-labelledby="producaoDropdown">
            <li><a class="dropdown-item" href="producao.php">Lista Sacolas</a></li>
            <li><a class="dropdown-item" href="producao.php">Lista Bobinas</a></li>
            <li><a class="dropdown-item" href="cadProducaoBobina.php">Cadastra Bobina</a></li>
            <li><a class="dropdown-item" href="cadProducaoSacola.php">Cadastra Sacola</a></li>
           
          </ul>
        </li>

        <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle" href="#" id="vendasDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
            Vendas
          </a>
          <ul class="dropdown-menu" aria-labelledby="vendasDropdown">
            <li><a class="dropdown-item" href="FormularioVenda.php">Cad Venda</a></li>
            <li><a class="dropdown-item" href="listarVendas.php">Lista Vendas</a></li>
            
          </ul>
        </li>
        
        <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle" href="#" id="configuracaoDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
            Configurações
          </a>
          <ul class="dropdown-menu" aria-labelledby="configuracaoDropdown">
            <li><a class="dropdown-item" href="gerenciar_usuarios.php">Usuarios</a></li>
            <li><a class="dropdown-item" href="caixa.php">Caixa</a></li>
            
          </ul>
        </li>
      </ul>
    </div>
  </div>
</nav>
