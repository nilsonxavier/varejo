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

        $html = '<table class="table table-bordered table-striped table-dark mt-2 table-responsive">';
        $html .= '<tr><th>ID Cliente</th><th>Data da Venda</th><th>Valor Total</th><th>Itens</th><th>Ação</th></tr>';

        while ($row = $result->fetch_assoc()) {
            $html .= '<tr>';
            $html .= '<td>' . $row['id_cliente'] . '</td>';
            $html .= '<td>' . $row['data_venda'] . '</td>';
            $html .= '<td>' . $row['valor_total'] . '</td>';
            $html .= '<td>' . $row['itens'] . '</td>';
            $html .= '<td>';
            $html .= '<a href="./excluirVenda.php?id=' . $row['id'] . '">Excluir</a> | ';
            $html .= '<a href="./editarVenda.php?id=' . $row['id'] . '">Editar</a>';
            $html .= '</td>';
            $html .= '</tr>';
        }

        $html .= '</table>';
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
