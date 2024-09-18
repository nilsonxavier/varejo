<?php
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


<blockquote class="blockquote text-center mt-3" onload="formatarDataHora()">
    <p class="mb-0">Cadastra Produção</p>
</blockquote>
<form action="conexx/databaseProducao.php" method="POST">
    <input type="hidden" id="data_hora" name="data_inicial" readonly>
         
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

<script>
        var agora = new Date();
    
            // Formatando a data (DD/MM/AAAA)
            var dia = String(agora.getDate()).padStart(2, '0');
            var mes = String(agora.getMonth() + 1).padStart(2, '0'); // Meses começam do 0
            var ano = agora.getFullYear();
            var dataFormatada = `${dia}/${mes}/${ano}`; // Formato brasileiro de data

            // Formatando a hora (HH:MM:SS)
            var horas = String(agora.getHours()).padStart(2, '0');
            var minutos = String(agora.getMinutes()).padStart(2, '0');
            var segundos = String(agora.getSeconds()).padStart(2, '0');
            var horaFormatada = `${horas}:${minutos}:${segundos}`; // Formato de hora

            // Concatenando data e hora
            var dataHoraFormatada = `${dataFormatada} ${horaFormatada}`; // Formato "DD/MM/AAAA HH:MM:SS"

            // Inserindo no campo de input (caso tenha um campo para exibir data e hora)
            document.getElementById('data_hora').value = dataHoraFormatada;

        formatarDataHora(); // Chama a função após o conteúdo do body ter sido carregado
</script>






<?php include __DIR__.'/includes/footer.php'; 