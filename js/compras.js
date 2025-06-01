let carrinho = [];
let tabelaPrecoId = null;

// Carrega clientes no select
$(document).ready(function () {
  $.getJSON('api/clientes.php', function (clientes) {
    clientes.forEach(cli => {
      $('#cliente').append(`<option value="${cli.id}" data-tabela="${cli.tabela_preco_id}">${cli.nome}</option>`);
    });
  });
});

// Ao selecionar cliente, salva tabela de preço
$('#cliente').on('change', function () {
  const option = $(this).find(':selected');
  tabelaPrecoId = option.data('tabela') || 1; // se não tiver, usa tabela padrão
  $('#resultadoBusca').empty();
});

// Buscar produtos digitando
$('#buscaProduto').on('input', function () {
  const termo = $(this).val();
  if (termo.length < 2) {
    $('#resultadoBusca').empty();
    return;
  }

  $.getJSON(`api/buscar_produtos.php?termo=${termo}&tabela=${tabelaPrecoId}`, function (produtos) {
    let html = '';
    produtos.forEach(p => {
      html += `<button class="list-group-item list-group-item-action"
        onclick="adicionarCarrinho(${p.id}, '${p.nome}', ${p.preco})">
        ${p.nome} - R$ ${p.preco.toFixed(2)}
      </button>`;
    });
    $('#resultadoBusca').html(html);
  });
});

// Adiciona ao carrinho
function adicionarCarrinho(id, nome, preco) {
  let item = carrinho.find(p => p.id === id);
  if (item) {
    item.qtd++;
  } else {
    carrinho.push({ id, nome, preco, qtd: 1 });
  }
  renderCarrinho();
  $('#resultadoBusca').empty();
}

// Atualiza o carrinho na tabela
function renderCarrinho() {
  const tbody = $('#carrinhoBody');
  tbody.empty();

  carrinho.forEach((item, index) => {
    const total = (item.qtd * item.preco).toFixed(2);
    tbody.append(`
      <tr>
        <td>${item.nome}</td>
        <td><input type="number" class="form-control" value="${item.qtd}" min="1" onchange="alterarQtd(${index}, this.value)"></td>
        <td>R$ ${item.preco.toFixed(2)}</td>
        <td>R$ ${total}</td>
        <td><button class="btn btn-danger btn-sm" onclick="remover(${index})">Remover</button></td>
      </tr>
    `);
  });
}

function alterarQtd(index, qtd) {
  carrinho[index].qtd = parseInt(qtd);
  renderCarrinho();
}

function remover(index) {
  carrinho.splice(index, 1);
  renderCarrinho();
}
