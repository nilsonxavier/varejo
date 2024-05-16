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

    // metodo lista dados 
    public function listarDados($nomeDaTabela) {
        // Query SQL para selecionar todos os dados da tabela
        $sql = "SELECT * FROM $nomeDaTabela";
        
        // Prepara a declaração
        $stmt = $this->conn->prepare($sql);
        
        // Executa a declaração
        $stmt->execute();
        
        // Obtém os resultados
        $result = $stmt->get_result();
        
        // Inicializa a string da tabela
        $html = '<table class="table table-striped table-dark mt-2">';
        $html .= '<tr><th>Nome</th><th>Quantidade</th><th>Ação</th></tr>';
        
        // Loop através dos resultados e adicionar cada linha à tabela HTML
    while ($row = $result->fetch_assoc()) {
        $html .= '<tr>';
        $html .= '<td>' . $row['nome'] . '</td>';
        $html .= '<td>' . $row['qtd'] . '</td>';
        // Adiciona botões de excluir e editar
        $html .= '<td>';
        $html .= '<a href="excluir.php?id=' . $row['id'] . '">Excluir</a> | ';
        $html .= '<a href="editar.php?id=' . $row['id'] . '">Editar</a>';
        $html .= '</td>';
        $html .= '</tr>';
    }
        
        // Fecha a tag da tabela
        $html .= '</table>';
        
        // Retorna a tabela HTML
        return $html;
    }
    
}

// Exemplo de uso
$database = new Database($conn);
?>
