
<blockquote class="blockquote text-center mt-3">
  <p class="mb-0">Excluir Produto</p>
</blockquote>

<div class="d-inline-block">
    <form method="POST">
        <p><strong>Voce Realmente deseja Excluir o produto <?=$tabelaHTML['nome']?></strong></p>
        <button type="submit" class="btn btn-danger" name="excluir">Excluir</button>
        <button type="button" class="btn btn-primary" onclick="cancelar()">Cancelar</button>
    </form>
</div>

<script>
    function cancelar() {
        // Redireciona para a página desejada
        window.location.href = "produtos.php";
    }
</script>
