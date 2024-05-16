
<blockquote class="blockquote text-center mt-3">
  <p class="mb-0">Editar Produto</p>
</blockquote>
<form method="POST">
  <div class="form-group row mt-1">
    <label for="nome" class="col-sm-2 col-form-label">Nome:</label>
    <div class="col-sm-10">
      <input type="text" class="form-control" name="nome" placeholder="Nome" value="<?=$tabelaHTML['nome']?>">
    </div>
  </div>
  <div class="form-group row">
    <label for="inputPassword3" class="col-sm-2 col-form-label">Quantidade</label>
    <div class="col-sm-10">
      <input type="number" class="form-control" name="qtd" placeholder="Quantidade" value="<?=$tabelaHTML['qtd']?>">
    </div>
  </div>
  <div class="form-group row">
    <div class="col-sm-10">
      <button type="submit" class="btn btn-primary">Editar</button>
      <button type="button" class="btn btn-danger" onclick="cancelar()">Cancelar</button>
    </div>
  </div>
</form>

<script>
    function cancelar() {
        // Redireciona para a página desejada
        window.location.href = "produtos.php";
    }
</script>