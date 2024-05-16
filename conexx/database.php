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
        $html = '<table class="table table-striped table-dark mt-2 text-center">';
        $html .= '<tr><th>Nome</th><th>Quantidade</th><th>Ação</th></tr>';
        
        // Loop através dos resultados e adicionar cada linha à tabela HTML
    while ($row = $result->fetch_assoc()) {
        $html .= '<tr>';
        $html .= '<td>' . $row['nome'] . '</td>';
        $html .= '<td>' . $row['qtd'] . '</td>';
        // Adiciona botões de excluir e editar
        $html .= '<td>';
        $html .= '<a href="./excluirProduto.php?id=' . $row['id'] . '">Excluir</a> | ';
        $html .= '<a href="./editaProduto.php?id=' . $row['id'] . '">Editar</a>';
        $html .= '</td>';
        $html .= '</tr>';
    }
        
        // Fecha a tag da tabela
        $html .= '</table>';
        
        // Retorna a tabela HTML
        return $html;
    }


    // metodo pela produto pelo ID
    public function getItemById($nomeDaTabela, $id) {
        // Query SQL para selecionar um item da tabela pelo ID
        $sql = "SELECT * FROM $nomeDaTabela WHERE id = ?";
        
        // Prepara a declaração
        $stmt = $this->conn->prepare($sql);
        
        // Bind do parâmetro
        $stmt->bind_param("i", $id);
        
        // Executa a declaração
        $stmt->execute();
        
        // Obtém o resultado
        $result = $stmt->get_result();
        
        // Verifica se o item foi encontrado
        if ($result->num_rows > 0) {
            // Retorna o item encontrado como um array associativo
            return $result->fetch_assoc();
        } else {
            // Se o item não foi encontrado, retorna null
            return null;
        }
    }


    // metodo editar produto 
    public function editarItem($nomeDaTabela, $id, $novosDados) {
        // Monta a string da query SQL para editar o item
        $sql = "UPDATE $nomeDaTabela SET ";
    
        // Monta os pares de campo=valor para a atualização
        $updates = [];
        foreach ($novosDados as $campo => $valor) {
            $updates[] = "$campo = ?";
        }
        $sql .= implode(", ", $updates);
        $sql .= " WHERE id = ?";
    
        // Prepara a declaração
        $stmt = $this->conn->prepare($sql);
    
        // Prepara os valores para bind_param
        $valores = array_values($novosDados);
        $valores[] = $id;
    
        // Faz o bind dos parâmetros
        $tipos = str_repeat("s", count($novosDados)) . "i"; // "s" para strings, "i" para inteiros
        $stmt->bind_param($tipos, ...$valores);
    
        // Executa a declaração
        if ($stmt->execute()) {
            return true; // Edição bem-sucedida
        } else {
            return false; // Erro ao editar item
        }
    }
    

    // metodo excluir produto pelo id
    public function excluirItem($nomeDaTabela, $id) {
        // Query SQL para excluir o item da tabela pelo ID
        $sql = "DELETE FROM $nomeDaTabela WHERE id = ?";
        
        // Prepara a declaração
        $stmt = $this->conn->prepare($sql);
        
        // Bind do parâmetro
        $stmt->bind_param("i", $id);
        
        // Executa a declaração
        if ($stmt->execute()) {
            return true; // Exclusão bem-sucedida
        } else {
            return false; // Erro ao excluir item
        }
    }
    
    
}

// Exemplo de uso
//$database = new Database($conn);
?>
