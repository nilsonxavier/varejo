<?php
require_once 'config.php';

class Database {
    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
    }

    public function inserirDados($nomeDaTabela, $nome, $qtd) {
        // Query SQL para inserir os dados
        $sql = "INSERT INTO $nomeDaTabela (nome, qtd) VALUES (?, ?)";
        
        // Prepara a declaração
        $stmt = $this->conn->prepare($sql);
        
        // Bind dos parâmetros
        $stmt->bind_param("ss", $nome, $qtd);
        
        // Executa a declaração
        if ($stmt->execute() === TRUE) {
            echo "Novo Produto inserido com sucesso";
        } else {
            echo "Erro ao inserir registro: ";
        }
        
        // Fecha a declaração
        $stmt->close();
    }
}

// Exemplo de uso
$database = new Database($conn);
?>
