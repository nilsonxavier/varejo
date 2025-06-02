<?php
require_once 'config.php';

class DatabaseCompras {
    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
    }

    // Inserir nova compra
    public function inserirCompra($tabela, $idCliente, $dataCompra, $valorTotal, $itens) {
        $sql = "INSERT INTO $tabela (id_cliente, data_compra, valor_total, itens) VALUES (?, ?, ?, ?)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("isss", $idCliente, $dataCompra, $valorTotal, $itens);

        if ($stmt->execute()) {
            echo '<div class="alert alert-success text-center mt-2">Compra registrada com sucesso.</div>';
        } else {
            echo '<div class="alert alert-danger text-center mt-2">Erro ao registrar a compra.</div>';
        }

        $stmt->close();
    }

    // Listar todas as compras
    public function listarCompras($tabela) {
        $sql = "SELECT * FROM $tabela";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        $result = $stmt->get_result();

        $html = '<table class="table table-bordered table-striped table-dark mt-2 table-responsive">';
        $html .= '<tr><th>ID Cliente</th><th>Data da Compra</th><th>Valor Total</th><th>Itens</th><th>Ação</th></tr>';

        while ($row = $result->fetch_assoc()) {
            $html .= '<tr>';
            $html .= '<td>' . $row['id_cliente'] . '</td>';
            $html .= '<td>' . $row['data_compra'] . '</td>';
            $html .= '<td>' . $row['valor_total'] . '</td>';
            $html .= '<td>' . $row['itens'] . '</td>';
            $html .= '<td>';
            $html .= '<a href="./excluirCompra.php?id=' . $row['id'] . '">Excluir</a> | ';
            $html .= '<a href="./editarCompra.php?id=' . $row['id'] . '">Editar</a>';
            $html .= '</td>';
            $html .= '</tr>';
        }

        $html .= '</table>';
        return $html;
    }

    // Obter uma compra específica
    public function obterCompra($tabela, $id) {
        $sql = "SELECT * FROM $tabela WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }

    // Atualizar uma compra
    public function atualizarCompra($tabela, $id, $idCliente, $dataCompra, $valorTotal, $itens) {
        $sql = "UPDATE $tabela SET id_cliente = ?, data_compra = ?, valor_total = ?, itens = ? WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("isssi", $idCliente, $dataCompra, $valorTotal, $itens, $id);

        if ($stmt->execute()) {
            echo '<div class="alert alert-success text-center mt-2">Compra atualizada com sucesso.</div>';
        } else {
            echo '<div class="alert alert-danger text-center mt-2">Erro ao atualizar a compra.</div>';
        }

        $stmt->close();
    }

    // Excluir uma compra
    public function excluirCompra($tabela, $id) {
        $sql = "DELETE FROM $tabela WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $id);

        if ($stmt->execute()) {
            echo '<div class="alert alert-success text-center mt-2">Compra excluída com sucesso.</div>';
        } else {
            echo '<div class="alert alert-danger text-center mt-2">Erro ao excluir a compra.</div>';
        }

        $stmt->close();
    }
}
?>
