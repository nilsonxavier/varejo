<?php
require_once 'conexx/config.php';

echo "Criando tabela de configurações...\n";

// SQL para criar a tabela
$sql_create_table = "
CREATE TABLE IF NOT EXISTS `configuracoes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `empresa_id` int(11) NOT NULL,
  `tamanho_papel` enum('A4','80mm','60mm') NOT NULL DEFAULT 'A4',
  `tema_dark` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `empresa_id` (`empresa_id`),
  CONSTRAINT `fk_configuracoes_empresa` FOREIGN KEY (`empresa_id`) REFERENCES `empresas` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci
";

// SQL para inserir configurações padrão
$sql_insert_defaults = "
INSERT IGNORE INTO `configuracoes` (`empresa_id`, `tamanho_papel`, `tema_dark`)
SELECT `id`, 'A4', 0 FROM `empresas`
";

try {
    // Criar tabela
    if ($conn->query($sql_create_table)) {
        echo "✓ Tabela 'configuracoes' criada com sucesso!\n";
    } else {
        echo "✗ Erro ao criar tabela: " . $conn->error . "\n";
    }
    
    // Inserir dados padrão
    if ($conn->query($sql_insert_defaults)) {
        echo "✓ Configurações padrão inseridas com sucesso!\n";
    } else {
        echo "✗ Erro ao inserir configurações padrão: " . $conn->error . "\n";
    }
    
    echo "\nMigração concluída com sucesso!\n";
    
} catch (Exception $e) {
    echo "✗ Erro: " . $e->getMessage() . "\n";
}

$conn->close();
?>
