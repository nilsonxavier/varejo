<?php
require_once 'config.php';

class DatabaseCliente {
    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
    }

    public function inserirDados($nomeDaTabela, $nome, $rua, $ruaNumero, $cep, $bairro, $cidade, $complemento, $estado, $telefone1, $telefone2, $vendedor, $cpf) {
        

        // Query SQL para inserir os dados
        $sql = "INSERT INTO $nomeDaTabela (nome, rua, numero, cep, bairro, cidade, complemento, estado, telefone1, telefone2, vendedor, cpf) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

        // Prepara a declaração
        $stmt = $this->conn->prepare($sql);
        
        // Bind dos parâmetros
        $stmt->bind_param("ssssssssssss", $nome, $rua, $ruaNumero, $cep, $bairro, $cidade, $complemento, $estado, $telefone1, $telefone2, $vendedor, $cpf);
        
        if ($this->cpfExiste($cpf)) {
            echo '<div class="alert alert-dark text-center mt-2">CPF JA CADASTRADO</div>';
            return;
        }

        // Executa a declaração
        if ($stmt->execute() === TRUE) {
            echo '<div class="alert alert-dark text-center mt-2">Novo cliente cadastrado com sucesso</div>';
        } else {
            echo '<div class="alert alert-dark text-center mt-2">Erro ao cadastra cliente</div>';
        }
        
        // Fecha a declaração
        $stmt->close();
    }

    private function cpfExiste($cpf) {
        $sql = "SELECT cpf FROM clientes WHERE cpf = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("s", $cpf);
        $stmt->execute();
        $stmt->store_result();
        $num_rows = $stmt->num_rows;
        $stmt->close();
        return $num_rows > 0;
    }

    // metodo lista dados 
    public function listarCliente($nomeDaTabela) {
        // Query SQL para selecionar todos os dados da tabela
        $sql = "SELECT * FROM $nomeDaTabela";
        
        // Prepara a declaração
        $stmt = $this->conn->prepare($sql);
        
        // Executa a declaração
        $stmt->execute();
        
        // Obtém os resultados
        $result = $stmt->get_result();
        
        // Inicializa a string da tabela
        $html = '<table class="table table-bordered table-striped table-dark mt-2 table-responsive">';
        $html .= '<tr><th>Nome</th><th>CPF</th><th>Rua</th><th>Numero</th><th>Bairro</th>
        <th>Cidade</th><th>Estado</th><th>Complemento</th><th>Telefone 1</th><th>Telefone 2</th><th>Vendedor</th>
        <th>Ação</th></tr>';
        
        // Loop através dos resultados e adicionar cada linha à tabela HTML
    while ($row = $result->fetch_assoc()) {
        $html .= '<tr>';
        $html .= '<td>' . $row['nome'] . '</td>';
        $html .= '<td>' . $row['cpf'] . '</td>';
        $html .= '<td>' . $row['rua'] . '</td>';
        $html .= '<td>' . $row['numero'] . '</td>';
        $html .= '<td>' . $row['bairro'] . '</td>';
        $html .= '<td>' . $row['cidade'] . '</td>';
        $html .= '<td>' . $row['estado'] . '</td>';
        $html .= '<td>' . $row['complemento'] . '</td>';
        $html .= '<td>' . $row['telefone1'] . '</td>';
        $html .= '<td>' . $row['telefone2'] . '</td>';
        $html .= '<td>' . $row['vendedor'] . '</td>';

        $endereco_completo = urlencode($row['rua'] . ", " . $row['numero'] . ", " . $row['bairro'] . ", " . $row['cidade']. ", " . $row['estado']);
        // Adiciona botões de excluir e editar
        $html .= '<td>';
        $html .= '<a href="./excluirProduto.php?id=' . $row['id'] . '">Excluir</a> | ';
        $html .= '<a href="./editaProduto.php?id=' . $row['id'] . '">Editar</a> | ';
        $html .= '<a href="https://wa.me/55' . $row['telefone1'] . '">whatsapp</a> | ';
        $html .= '<a href="https://www.google.com/maps/search/?api=1&query=' . $endereco_completo . '" target="_blank">Mapa</a>';
        $html .= '</td>';
        $html .= '</tr>';
    }

    

        
        // Fecha a tag da tabela
        $html .= '</table>';
        
        // Retorna a tabela HTML
        return $html;
    }
}


?>
