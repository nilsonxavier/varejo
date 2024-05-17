
<blockquote class="blockquote text-center mt-3">
  <p class="mb-0">Cadastra Cliente</p>
</blockquote>
<form method="POST" class="text-center">
  <div class="form-group row mt-1">
    <label for="nome" class="col-sm-2 col-form-label">Nome:</label>
    <div class="col-sm-10">
      <input type="text" class="form-control" name="nome" placeholder="Nome">
    </div>
  </div>
  <div class="form-group row mt-1">
    <label for="cpf" class="col-sm-2 col-form-label">CPF:</label>
    <div class="col-sm-10">
      <input type="text" class="form-control" name="cpf" placeholder="ex: 15648569852">
    </div>
  </div>
  <div class="form-group row ">
    <label for="telefone1" class="col-sm-2 col-form-label">Whatsapp:</label>
    <div class="col-sm-10">
      <input type="text" class="form-control" name="telefone1" placeholder="ex: 8599874569">
    </div>
  </div>
  <div class="form-group row ">
    <label for="telefone2" class="col-sm-2 col-form-label">Telefone:</label>
    <div class="col-sm-10">
      <input type="text" class="form-control" name="telefone2" placeholder="ex: 8599874569">
    </div>
  </div>
  <div class="form-group row ">
    <label for="cep" class="col-sm-2 col-form-label">CEP:</label>
    <div class="col-sm-10">
      <input type="text" class="form-control" id="cep" name="cep" placeholder="ex: 60785458">
    </div>
  </div>
  <div class="form-group row">
    <label for="rua" class="col-sm-2 col-form-label">Rua:</label>
    <div class="col-sm-10">
      <input type="text" class="form-control" name="rua" id="rua">
    </div>
  </div>
  <div class="form-group row">
    <label for="ruaNumero" class="col-sm-2 col-form-label">Numero:</label>
    <div class="col-sm-10">
      <input type="text" class="form-control" name="ruaNumero" >
    </div>
  </div>
  <div class="form-group row">
    <label for="bairro" class="col-sm-2 col-form-label">Bairro:</label>
    <div class="col-sm-10">
      <input type="text" class="form-control" name="bairro" id="bairro">
    </div>
  </div>
  <div class="form-group row">
    <label for="cidade" class="col-sm-2 col-form-label">Cidade:</label>
    <div class="col-sm-10">
      <input type="text" class="form-control" name="cidade" id="cidade">
    </div>
  </div>
  <div class="form-group row">
    <label for="estado" class="col-sm-2 col-form-label">Estado:</label>
    <div class="col-sm-10">
      <input type="text" class="form-control" name="estado" id="estado">
    </div>
  </div>
  <div class="form-group row">
    <label for="complemento" class="col-sm-2 col-form-label">Complemento:</label>
    <div class="col-sm-10">
      <input type="text" class="form-control" name="complemento" placeholder="ex: bl 15 ap 108">
    </div>
  </div>

  <script> 
    document.getElementById('cep').addEventListener('change', function() {
      var cep = this.value.replace(/\D/g, '');

      // Verifica se o CEP possui 8 dígitos
      if (cep.length !== 8) {
          alert('CEP inválido');
          return;
      }

      // Requisição AJAX para consultar o CEP
      var xhr = new XMLHttpRequest();
      xhr.open('GET', 'includes/cep/consulta_cep.php?cep=' + cep, true);
      xhr.onload = function() {
          if (xhr.status === 200) {
              var endereco = JSON.parse(xhr.responseText);
              document.getElementById('rua').value = endereco.logradouro;
              document.getElementById('bairro').value = endereco.bairro;
              document.getElementById('cidade').value = endereco.localidade;
              document.getElementById('estado').value = endereco.uf;
          } else {
              alert('Erro ao consultar o CEP');
          }
      };
      xhr.send();
    });
  </script> 













































  <div class="form-group row">
    <label for="vendedor" class="col-sm-2 col-form-label">Vendedor:</label>
    <div class="col-sm-10">
      <input type="text" class="form-control" name="vendedor" >
    </div>
  </div>
  <div class="form-group row">
    <div class="col-sm-10">
      <button type="submit" class="btn btn-primary">Cadastra</button>
    </div>
  </div>
</form>