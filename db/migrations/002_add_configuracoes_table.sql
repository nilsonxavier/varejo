-- Criação da tabela de configurações do sistema
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Inserir configurações padrão para empresas existentes
INSERT IGNORE INTO `configuracoes` (`empresa_id`, `tamanho_papel`, `tema_dark`)
SELECT `id`, 'A4', 0 FROM `empresas`;
