<?php
class DatabaseProducao {
    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
    }

    public function getProducaoPorProdutoPorDia($dataInicial, $dataFinal) {
        $sql = "
            SELECT produto_id, DATE(data_fim) as dia, SUM(quantidade) as total_quantidade 
            FROM ordens_producao 
            WHERE data_fim BETWEEN ? AND ?
            GROUP BY produto_id, DATE(data_fim)
            ORDER BY produto_id, DATE(data_fim)
        ";

        $stmt = $this->conn->prepare($sql);
        if (!$stmt) {
            die("Erro na preparação da consulta: " . htmlspecialchars($this->conn->error));
        }

        $stmt->bind_param("ss", $dataInicial, $dataFinal);
        $stmt->execute();
        $result = $stmt->get_result();

        $dados = [];
        while ($row = $result->fetch_assoc()) {
            $dados[] = $row;
        }

        $stmt->close();

        return $dados;
    }
}
?>
