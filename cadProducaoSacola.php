<?php

    require_once 'verifica_login.php';
// ... resto da página protegida ...

  include __DIR__.'/includes/header.php';
  include __DIR__.'/includes/navbar.php';
  // if($mens == "producao cadastrada"){
  //   $tes = '<div class="alert alert-dark text-center mt-2">Novo cliente cadastrado com sucesso</div>';
  // }
  // echo $tes;

  if (isset($_GET['mensagem'])) {
    // Exibe a mensagem dentro de uma <div>
    echo $_GET['mensagem'];
  }
?>

<blockquote class="blockquote text-center mt-3">
    <p class="mb-0">Cadastra Produção</p>
</blockquote>
<form action="conexx/databaseProducao.php" method="POST">
    <div class="form-group row mt-1">
        <label for="nome" class="col-sm-2 col-form-label">Data Inicio:</label>
        <div class="col-sm-10">
            <input type="datetime-local" class="form-control" name="data_inicio" placeholder="ex: 19/05/2024">
        </div>
    </div>
    <div class="form-group row mt-1">
        <label for="nome" class="col-sm-2 col-form-label">Data Fim:</label>
        <div class="col-sm-10">
            <input type="datetime-local" class="form-control" name="data_fim" placeholder="ex: 19/05/2024">
        </div>
    </div>
    <div class="form-group row">
        <label for="inputPassword3" class="col-sm-2 col-form-label">Produto</label>
        <div class="col-sm-10">
            <select id="usuario" name="produto_id" class="custom-select">
                <?php
              // Incluindo o arquivo PHP que busca os dados do banco
              include 'conexx/databaseProducao.php';

              // Gerando as opções do dropdown
              foreach ($usuarios as $usuario) {
                  echo '<option value="' . htmlspecialchars($usuario['id']) . '">' . htmlspecialchars($usuario['nome']) . '</option>';
              }
              ?>
            </select><br><br>
        </div>
    </div>
    <div class="form-group row mt-1">
        <label for="nome" class="col-sm-2 col-form-label">Qantidade Produzida:</label>
        <div class="col-sm-10">
            <input type="number" class="form-control" name="quantidade" placeholder="Sacolas em KG">
        </div>
    </div>
    <div class="form-group row mt-1">
        <label for="nome" class="col-sm-2 col-form-label">Material Utilizado:</label>
        <div class="col-sm-10">
            <input type="number" class="form-control" name="material_utilizado" placeholder="bobina em KG">
        </div>
    </div>

    <div class="form-group row">
        <div class="col-sm-10">
            <button type="submit" class="btn btn-primary">Cadastra</button>
        </div>
    </div>

</form>






<?php include __DIR__.'/includes/footer.php'; 