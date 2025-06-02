<?php
require_once 'config.php';

class DatabaseVenda {
    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
    }

    // Método para inserir uma nova venda
    public function inserirVenda($nomeDaTabela, $idCliente, $dataVenda, $valorTotal, $itens) {

        $sql = "INSERT INTO $nomeDaTabela (id_cliente, data_venda, valor_total, itens) VALUES (?, ?, ?, ?)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("isss", $idCliente, $dataVenda, $valorTotal, $itens);

        if ($stmt->execute() === TRUE) {
            echo '<div class="alert alert-dark text-center mt-2">Nova venda cadastrada com sucesso</div>';
        } else {
            echo '<div class="alert alert-dark text-center mt-2">Erro ao cadastrar venda</div>';
        }

        $stmt->close();
    }

    // Método para listar todas as vendas
    public function listarVendas($nomeDaTabela) {
    $sql = "SELECT * FROM $nomeDaTabela";
    $stmt = $this->conn->prepare($sql);
    $stmt->execute();
    $result = $stmt->get_result();

    $html = '<div class="table-responsive mt-3">';
    $html .= '<table class="table table-bordered table-hover align-middle text-center">';
    $html .= '
        <thead class="table-dark">
            <tr>
                <th scope="col">ID Cliente</th>
                <th scope="col">Data da Venda</th>
                <th scope="col">Valor Total (R$)</th>
                <th scope="col">Itens</th>
                <th scope="col">Ações</th>
            </tr>
        </thead>
        <tbody>
    ';

    while ($row = $result->fetch_assoc()) {
        $html .= '<tr>';
        $html .= '<td>' . htmlspecialchars($row['id_cliente']) . '</td>';
        $html .= '<td>' . htmlspecialchars($row['data_venda']) . '</td>';
        $html .= '<td>' . number_format($row['valor_total'], 2, ',', '.') . '</td>';
        $html .= '<td style="max-width: 300px; word-break: break-word;">' . nl2br(htmlspecialchars($row['itens'])) . '</td>';
        $html .= '<td>
            <a href="./editarVenda.php?id=' . $row['id'] . '" class="btn btn-sm btn-warning me-1">Editar</a>
            <a href="./excluirVenda.php?id=' . $row['id'] . '" class="btn btn-sm btn-danger" onclick="return confirm(\'Tem certeza que deseja excluir esta venda?\')">Excluir</a>
        </td>';
        $html .= '</tr>';
    }

    $html .= '</tbody></table></div>';
    return $html;
    }


     

    // Método para obter uma venda específica por ID
    public function obterVenda($nomeDaTabela, $id) {
        $sql = "SELECT * FROM $nomeDaTabela WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }

    // Método para atualizar uma venda
    public function atualizarVenda($nomeDaTabela, $id, $idCliente, $dataVenda, $valorTotal, $itens) {
        $sql = "UPDATE $nomeDaTabela SET id_cliente = ?, data_venda = ?, valor_total = ?, itens = ? WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("isssi", $idCliente, $dataVenda, $valorTotal, $itens, $id);

        if ($stmt->execute() === TRUE) {
            echo '<div class="alert alert-dark text-center mt-2">Venda atualizada com sucesso</div>';
        } else {
            echo '<div class="alert alert-dark text-center mt-2">Erro ao atualizar venda</div>';
        }

        $stmt->close();
    }

    // Método para excluir uma venda
    public function excluirVenda($nomeDaTabela, $id) {
        $sql = "DELETE FROM $nomeDaTabela WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $id);

        if ($stmt->execute() === TRUE) {
            echo '<div class="alert alert-dark text-center mt-2">Venda excluída com sucesso</div>';
        } else {
            echo '<div class="alert alert-dark text-center mt-2">Erro ao excluir venda</div>';
        }

        $stmt->close();
    }
}
?>
