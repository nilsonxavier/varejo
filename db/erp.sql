-- phpMyAdmin SQL Dump
-- version 5.2.1deb1+deb12u1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Tempo de geração: 30/08/2025 às 03:17
-- Versão do servidor: 10.11.9-MariaDB-deb12-log
-- Versão do PHP: 8.2.29

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Banco de dados: `erp`
--

-- --------------------------------------------------------

--
-- Estrutura para tabela `auditoria`
--

CREATE TABLE `auditoria` (
  `id` int(11) NOT NULL,
  `tabela_afetada` varchar(50) DEFAULT NULL,
  `id_registro` int(11) DEFAULT NULL,
  `acao` enum('insercao','atualizacao','remocao') DEFAULT NULL,
  `usuario_id` int(11) DEFAULT NULL,
  `data_hora` datetime DEFAULT current_timestamp(),
  `detalhes` mediumtext DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `caixas`
--

CREATE TABLE `caixas` (
  `id` int(11) NOT NULL,
  `usuario_id` int(11) DEFAULT NULL,
  `empresa_id` int(11) NOT NULL,
  `data_abertura` datetime DEFAULT NULL,
  `valor_inicial` decimal(10,2) DEFAULT NULL,
  `data_fechamento` datetime DEFAULT NULL,
  `valor_final` decimal(10,2) DEFAULT NULL,
  `status` enum('aberto','fechado') DEFAULT 'aberto'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `carrinho`
--

CREATE TABLE `carrinho` (
  `id` int(11) NOT NULL,
  `id_empresa` int(11) NOT NULL,
  `id_cliente` int(11) DEFAULT NULL,
  `id_tabela_preco` int(11) DEFAULT NULL,
  `sessao_id` varchar(100) NOT NULL,
  `produto_id` int(11) NOT NULL,
  `nome_produto` varchar(255) DEFAULT NULL,
  `quantidade` decimal(10,2) NOT NULL DEFAULT 1.00,
  `valor_unitario` decimal(10,2) NOT NULL,
  `valor_total` decimal(10,2) NOT NULL,
  `criado_em` timestamp NULL DEFAULT current_timestamp(),
  `atualizado_em` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `categorias`
--

CREATE TABLE `categorias` (
  `id` int(11) NOT NULL,
  `nome` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `clientes`
--

CREATE TABLE `clientes` (
  `id` int(11) NOT NULL,
  `nome` varchar(255) NOT NULL,
  `telefone` varchar(50) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `lista_preco_id` int(11) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `cpf` varchar(20) DEFAULT NULL,
  `endereco` varchar(255) DEFAULT NULL,
  `cep` varchar(20) DEFAULT NULL,
  `saldo` decimal(10,2) NOT NULL DEFAULT 0.00,
  `empresa_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `clientes`
--

INSERT INTO `clientes` (`id`, `nome`, `telefone`, `email`, `lista_preco_id`, `created_at`, `cpf`, `endereco`, `cep`, `saldo`, `empresa_id`) VALUES
(1, 'pedro paulo santos pereira xavier', '85991853813', 'nilsonxavier12@gmail.com', 4, '2025-06-15 19:38:24', '', 'rua cristo redentor', '60762465', 0.00, 1),
(2, 'nilson xavier', '85991853813', 'nilson@gmail.com', 1, '2025-06-17 20:11:00', '10397258488', 'rua marcelo costa 770', '60766190', 0.00, 2);

-- --------------------------------------------------------

--
-- Estrutura para tabela `clientes_anterior`
--

CREATE TABLE `clientes_anterior` (
  `id` int(11) NOT NULL,
  `nome` varchar(100) NOT NULL,
  `rua` varchar(255) DEFAULT NULL,
  `numero` varchar(10) DEFAULT NULL,
  `cep` varchar(9) DEFAULT NULL,
  `bairro` varchar(100) DEFAULT NULL,
  `cidade` varchar(100) DEFAULT NULL,
  `complemento` varchar(255) DEFAULT NULL,
  `estado` varchar(100) DEFAULT NULL,
  `telefone1` varchar(20) DEFAULT NULL,
  `telefone2` varchar(20) DEFAULT NULL,
  `vendedor` varchar(100) DEFAULT NULL,
  `cpf` varchar(14) DEFAULT NULL,
  `tabela_preco_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `clientes_anterior`
--

INSERT INTO `clientes_anterior` (`id`, `nome`, `rua`, `numero`, `cep`, `bairro`, `cidade`, `complemento`, `estado`, `telefone1`, `telefone2`, `vendedor`, `cpf`, `tabela_preco_id`) VALUES
(4, 'Nilson Xavier de Freitas', 'Rua Marcelo Costa', '770', '60766190', 'Planalto Ayrton Senna', 'Fortaleza', 'casa', 'CE', '84999923574', '', 'michele', '10397258488', 1),
(5, 'teste', 'Rua Cristo Redentor', '', '60762465', 'Mondubim', 'Fortaleza', '', 'CE', '85991853813', '', '', '095.032.553-84', NULL);

-- --------------------------------------------------------

--
-- Estrutura para tabela `compras`
--

CREATE TABLE `compras` (
  `id` int(11) NOT NULL,
  `id_cliente` int(11) NOT NULL,
  `empresa_id` int(11) NOT NULL,
  `data_compra` datetime NOT NULL,
  `valor_total` decimal(10,2) NOT NULL,
  `itens` longtext NOT NULL CHECK (json_valid(`itens`)),
  `id_forma_pagamento` int(11) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `contas_financeiras`
--

CREATE TABLE `contas_financeiras` (
  `id` int(11) NOT NULL,
  `id_cliente` int(11) NOT NULL,
  `tipo` enum('receber','pagar') NOT NULL,
  `valor` decimal(10,2) NOT NULL,
  `data_movimento` date NOT NULL,
  `descricao` varchar(255) DEFAULT NULL,
  `id_compra` int(11) DEFAULT NULL,
  `pago` tinyint(1) DEFAULT 0,
  `data_pagamento` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `empresas`
--

CREATE TABLE `empresas` (
  `id` int(11) NOT NULL,
  `razao_social` varchar(255) NOT NULL,
  `nome_fantasia` varchar(255) DEFAULT NULL,
  `cnpj` varchar(20) NOT NULL,
  `email` varchar(150) DEFAULT NULL,
  `telefone` varchar(50) DEFAULT NULL,
  `endereco` varchar(255) DEFAULT NULL,
  `cidade` varchar(100) DEFAULT NULL,
  `estado` varchar(2) DEFAULT NULL,
  `cep` varchar(10) DEFAULT NULL,
  `criado_em` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `empresas`
--

INSERT INTO `empresas` (`id`, `razao_social`, `nome_fantasia`, `cnpj`, `email`, `telefone`, `endereco`, `cidade`, `estado`, `cep`, `criado_em`) VALUES
(1, 'Hightec', 'Hightec', '39820695000193', 'nilson@hightectelecom.com.br', '85991853813', 'rua marcelo costa 770', 'fortaleza', 'ce', '60762376', '2025-06-16 05:57:16'),
(2, 'NC PAPELAO', 'NC PAPELAO', '19.921.071/0001-55', 'financeiro@hightectelecom.com.br', '', 'rua 24 de maio 535', 'fortaleza', 'ce', '', '2025-06-29 20:30:29');

-- --------------------------------------------------------

--
-- Estrutura para tabela `estoque`
--

CREATE TABLE `estoque` (
  `id` int(11) NOT NULL,
  `material_id` int(11) DEFAULT NULL,
  `tipo` enum('entrada','saida') DEFAULT NULL,
  `quantidade` decimal(10,2) DEFAULT NULL,
  `descricao` text DEFAULT NULL,
  `data_movimentacao` datetime DEFAULT current_timestamp(),
  `empresa_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `estoque`
--

INSERT INTO `estoque` (`id`, `material_id`, `tipo`, `quantidade`, `descricao`, `data_movimentacao`, `empresa_id`) VALUES
(1, 4, 'entrada', 100.00, 'testando', '2025-06-25 01:46:34', 0),
(2, 4, 'entrada', 1000.00, 'testando', '2025-06-25 01:46:46', 0),
(3, 4, 'entrada', 100.00, NULL, '2025-06-25 01:53:42', 0),
(4, 1, 'saida', 1000.00, 'Venda ID 46', '2025-06-25 02:10:23', 0),
(5, 2, 'saida', 1000.00, 'Venda ID 47', '2025-06-25 02:16:26', 0),
(6, 2, 'saida', 10.00, 'Venda ID 48', '2025-06-26 15:49:30', 0),
(7, 2, 'saida', 10.00, 'Venda ID 49', '2025-06-29 16:21:14', 0),
(8, 1, 'entrada', 1000.00, NULL, '2025-06-29 21:01:35', 0),
(9, 1, 'entrada', 100.00, NULL, '2025-06-29 21:01:50', 0),
(10, 1, 'saida', 100.00, 'Venda ID 50', '2025-06-29 21:12:53', 0),
(11, 2, 'saida', 15000.00, 'Venda ID 50', '2025-06-29 21:12:53', 0),
(12, 1, 'saida', 10.00, 'Venda ID 51', '2025-08-29 23:09:38', 0),
(13, 2, 'saida', 3.00, 'Venda ID 51', '2025-08-29 23:09:38', 0),
(14, 1, 'saida', 10.00, 'Venda ID 53', '2025-08-29 23:18:18', 2),
(15, 2, 'saida', 3.00, 'Venda ID 53', '2025-08-29 23:18:18', 2),
(16, 1, 'saida', 10.00, 'Venda ID 54', '2025-08-29 23:19:41', 2),
(17, 2, 'saida', 3.00, 'Venda ID 54', '2025-08-29 23:19:41', 2),
(18, 1, 'saida', 10.00, 'Venda ID 55', '2025-08-29 23:21:23', 2),
(19, 2, 'saida', 3.00, 'Venda ID 55', '2025-08-29 23:21:23', 2);

-- --------------------------------------------------------

--
-- Estrutura para tabela `formas_pagamento`
--

CREATE TABLE `formas_pagamento` (
  `id` int(11) NOT NULL,
  `descricao` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `funcionario`
--

CREATE TABLE `funcionario` (
  `id` int(11) NOT NULL,
  `nome` varchar(100) NOT NULL,
  `telefone` varchar(100) DEFAULT NULL,
  `endereco` varchar(100) DEFAULT NULL,
  `funcao` varchar(100) NOT NULL,
  `cpf` varchar(14) NOT NULL,
  `tipo_pagamento` enum('producao','fixo','','') NOT NULL DEFAULT 'fixo',
  `salario` float DEFAULT NULL,
  `usuario` varchar(100) NOT NULL,
  `senha` varchar(100) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `funcionario`
--

INSERT INTO `funcionario` (`id`, `nome`, `telefone`, `endereco`, `funcao`, `cpf`, `tipo_pagamento`, `salario`, `usuario`, `senha`) VALUES
(1, 'nilson xavier', '(85)991853813', 'rua marcelo costa 770 planalto airton senna fortaleza', 'gerente', '10397258488', 'producao', 0, 'nilsonxavier', '33213264'),
(2, 'curio', NULL, NULL, 'balao', '', 'producao', NULL, 'curio', '123456');

-- --------------------------------------------------------

--
-- Estrutura para tabela `itens_venda_anterior`
--

CREATE TABLE `itens_venda_anterior` (
  `id` int(11) NOT NULL,
  `id_venda` int(11) NOT NULL,
  `id_produto` int(11) NOT NULL,
  `quantidade` int(11) NOT NULL,
  `preco_unitario` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `listas_precos`
--

CREATE TABLE `listas_precos` (
  `id` int(11) NOT NULL,
  `nome` varchar(100) NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `empresa_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `listas_precos`
--

INSERT INTO `listas_precos` (`id`, `nome`, `created_at`, `empresa_id`) VALUES
(1, 'compra', '2025-06-14 15:29:55', 1),
(2, 'teste', '2025-06-14 15:38:25', 2),
(3, 'teste', '2025-06-14 15:45:20', 1),
(4, 'Atacado1', '2025-06-16 05:07:41', 2),
(7, 'papelao celso', '2025-06-30 00:05:22', 2);

-- --------------------------------------------------------

--
-- Estrutura para tabela `materiais`
--

CREATE TABLE `materiais` (
  `id` int(11) NOT NULL,
  `nome` varchar(100) NOT NULL,
  `empresa_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `materiais`
--

INSERT INTO `materiais` (`id`, `nome`, `empresa_id`) VALUES
(1, 'papelao', 1),
(2, 'papelao fardo', 2),
(4, 'ferro', 1),
(7, 'papelÃ£o', 2);

-- --------------------------------------------------------

--
-- Estrutura para tabela `movimentacoes`
--

CREATE TABLE `movimentacoes` (
  `id` int(11) NOT NULL,
  `caixa_id` int(11) DEFAULT NULL,
  `empresa_id` int(11) NOT NULL,
  `tipo` enum('entrada','saida') DEFAULT NULL,
  `valor` decimal(10,2) DEFAULT NULL,
  `descricao` varchar(255) DEFAULT NULL,
  `data_movimentacao` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `movimentos_caixa`
--

CREATE TABLE `movimentos_caixa` (
  `id` int(11) NOT NULL,
  `caixa_id` int(11) NOT NULL,
  `tipo` enum('entrada','saida') NOT NULL,
  `valor` decimal(10,2) NOT NULL,
  `descricao` varchar(255) DEFAULT NULL,
  `data` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `precos_materiais`
--

CREATE TABLE `precos_materiais` (
  `id` int(11) NOT NULL,
  `lista_id` int(11) NOT NULL,
  `material_id` int(11) NOT NULL,
  `preco` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `precos_materiais`
--

INSERT INTO `precos_materiais` (`id`, `lista_id`, `material_id`, `preco`) VALUES
(1, 1, 1, 1.50),
(2, 1, 2, 290.00),
(3, 3, 4, 12.00),
(4, 3, 1, 55.00),
(5, 3, 2, 7.00),
(6, 1, 4, 1.00),
(7, 4, 4, 0.90),
(8, 4, 1, 0.50),
(9, 4, 2, 1.00),
(15, 7, 2, 1.25),
(16, 7, 7, 0.60);

-- --------------------------------------------------------

--
-- Estrutura para tabela `precos_tabelados`
--

CREATE TABLE `precos_tabelados` (
  `id` int(11) NOT NULL,
  `id_produto` int(11) NOT NULL,
  `id_tabela` int(11) NOT NULL,
  `preco` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `precos_tabelados`
--

INSERT INTO `precos_tabelados` (`id`, `id_produto`, `id_tabela`, `preco`) VALUES
(1, 13, 1, 5.50),
(2, 14, 1, 6.70),
(3, 15, 1, 2.80);

-- --------------------------------------------------------

--
-- Estrutura para tabela `produtos`
--

CREATE TABLE `produtos` (
  `id` int(11) NOT NULL,
  `nome` varchar(255) DEFAULT NULL,
  `qtd` int(11) DEFAULT NULL,
  `data_criacao` timestamp NULL DEFAULT current_timestamp(),
  `preco` decimal(10,2) NOT NULL DEFAULT 0.00,
  `id_categoria` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `produtos`
--

INSERT INTO `produtos` (`id`, `nome`, `qtd`, `data_criacao`, `preco`, `id_categoria`) VALUES
(13, 'Sacola - P', 1500, '2024-06-20 22:28:38', 0.00, NULL),
(14, 'Sacola - M', 380, '2024-06-20 22:28:56', 0.00, NULL),
(15, 'PAPELAO', 50, '2025-06-01 20:24:32', 0.00, NULL);

-- --------------------------------------------------------

--
-- Estrutura para tabela `tabelas_precos`
--

CREATE TABLE `tabelas_precos` (
  `id` int(11) NOT NULL,
  `nome` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Despejando dados para a tabela `tabelas_precos`
--

INSERT INTO `tabelas_precos` (`id`, `nome`) VALUES
(1, 'padrao');

-- --------------------------------------------------------

--
-- Estrutura para tabela `usuarios`
--

CREATE TABLE `usuarios` (
  `id` int(11) NOT NULL,
  `nome` varchar(100) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `senha` varchar(255) DEFAULT NULL,
  `tipo` enum('admin','vendedor','estoquista') DEFAULT 'vendedor',
  `empresa_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Despejando dados para a tabela `usuarios`
--

INSERT INTO `usuarios` (`id`, `nome`, `email`, `senha`, `tipo`, `empresa_id`) VALUES
(1, 'nilson', 'nilsonxavier12@gmail.com', '$2y$10$KCev7oxGJ1CwQ90/qJGLL.AERKJm5qBIa0cFTbjNohBsdCkFjBOkS', 'admin', 1),
(2, 'pedro', 'pedropaulomfp11@Gmail.com', '$2y$10$C6ifQl76YOuURAmc9QafA.x4GsV7Lx.Sxpejy60MQ2EJK2Kol3YEC', 'admin', 1),
(13, 'michele', 'michelesantosmfp@gmail.com', '$2y$10$VC7PUG./lN5f6IBEwqCn5Or7Shg352PA/IChihLhzWs36r54DVQqm', 'admin', 2),
(14, 'teste', 'teste@gmail.com', '$2y$10$NpYrs./MCnFP27jByxKiMOVmyUvCGM5hSEg9JsI17xCBHFC9aBx6O', 'admin', 1),
(15, 'papelao', 'papelao@gmail.com', '$2y$10$IiW7EqCEBglhX263IT9hMOOtb.Uyk/v/rFNXdAt.gueyceYne7dr.', 'admin', 2),
(18, 'ttbum', '1111@gmail', '$2y$10$yiypy8UUs.KI90/4T9FdruLpjrN1a.r/KFsPNUmg9q/dkVsClZyUC', 'vendedor', 2),
(19, 'pedro', 'abdc@gmail.com', '$2y$10$BMleRnPM.kMHHhyw1PQl2u4QNR3UkTkHdaM4uN4ZaE6aXYq39kOTa', 'vendedor', 1),
(20, 'teste', '123@gmail', '$2y$10$QySTo5A0UFvh3dmTQR9SH.yF5Tbixp0LyH3y9jI0uKwU3LyOVyYY.', 'estoquista', 1);

-- --------------------------------------------------------

--
-- Estrutura para tabela `vendas`
--

CREATE TABLE `vendas` (
  `id` int(11) NOT NULL,
  `cliente_id` int(11) DEFAULT NULL,
  `empresa_id` int(11) NOT NULL,
  `lista_preco_id` int(11) NOT NULL,
  `total` decimal(10,2) NOT NULL,
  `data` timestamp NULL DEFAULT current_timestamp(),
  `valor_dinheiro` decimal(10,2) DEFAULT 0.00,
  `valor_pix` decimal(10,2) DEFAULT 0.00,
  `valor_cartao` decimal(10,2) DEFAULT 0.00,
  `valor_pago` decimal(10,2) DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `vendas_antigo`
--

CREATE TABLE `vendas_antigo` (
  `id` int(11) NOT NULL,
  `id_cliente` int(11) NOT NULL,
  `data_venda` datetime DEFAULT current_timestamp(),
  `valor_total` decimal(10,2) NOT NULL,
  `status` enum('pendente','paga','cancelada') DEFAULT 'pendente'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `vendas_antigo`
--

INSERT INTO `vendas_antigo` (`id`, `id_cliente`, `data_venda`, `valor_total`, `status`) VALUES
(1, 4, '2025-06-02 02:37:43', 14.00, 'pendente');

-- --------------------------------------------------------

--
-- Estrutura para tabela `vendas_excluir_`
--

CREATE TABLE `vendas_excluir_` (
  `id` int(11) NOT NULL,
  `cliente_id` int(11) DEFAULT NULL,
  `data_hora` datetime DEFAULT current_timestamp(),
  `total` decimal(10,2) NOT NULL,
  `pago` decimal(10,2) NOT NULL,
  `status` enum('pago','pendente') DEFAULT 'pago',
  `observacoes` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `vendas_itens`
--

CREATE TABLE `vendas_itens` (
  `id` int(11) NOT NULL,
  `venda_id` int(11) NOT NULL,
  `material_id` int(11) NOT NULL,
  `quantidade` decimal(10,2) NOT NULL,
  `preco_unitario` decimal(10,2) NOT NULL,
  `subtotal` decimal(10,2) NOT NULL,
  `empresa_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `vendas_itens`
--

INSERT INTO `vendas_itens` (`id`, `venda_id`, `material_id`, `quantidade`, `preco_unitario`, `subtotal`, `empresa_id`) VALUES
(1, 1, 1, 3333.00, 1.50, 4999.00, 0),
(2, 2, 1, 10.00, 1.50, 15.00, 0),
(3, 3, 2, 1.00, 290.00, 290.00, 0),
(4, 4, 1, 12.00, 1.50, 18.00, 0),
(5, 5, 1, 44.00, 1.50, 66.00, 0),
(6, 6, 2, 10.00, 290.00, 2900.00, 0),
(7, 7, 2, 12.00, 1.00, 12.00, 0),
(8, 8, 2, 12.00, 1.00, 12.00, 0),
(9, 9, 2, 12.00, 1.00, 12.00, 0),
(10, 10, 2, 12.00, 1.00, 12.00, 0),
(11, 11, 2, 12.00, 1.00, 12.00, 0),
(12, 12, 2, 10.00, 1.00, 10.00, 0),
(13, 13, 1, 10.00, 0.50, 5.00, 0),
(14, 14, 1, 1.00, 0.50, 0.00, 0),
(15, 16, 1, 10000.00, 1.50, 15000.00, 0),
(16, 17, 1, 10.00, 0.50, 5.00, 0),
(17, 18, 2, 2.00, 1.00, 2.00, 0),
(18, 19, 2, 22.00, 1.00, 22.00, 0),
(19, 20, 1, 100.00, 1.50, 150.00, 0),
(20, 21, 2, 100.00, 7.00, 700.00, 0),
(21, 22, 2, 1.00, 100.00, 100.00, 0),
(22, 22, 2, 2.00, 100.00, 200.00, 0),
(23, 23, 2, 1.00, 100.00, 100.00, 0),
(24, 24, 1, 1.00, 100.00, 100.00, 0),
(25, 25, 2, 1.00, 100.00, 100.00, 0),
(26, 25, 1, 5.00, 100.00, 500.00, 0),
(27, 26, 2, 1.00, 100.00, 100.00, 0),
(28, 26, 2, 6.00, 100.00, 600.00, 0),
(29, 27, 2, 30.00, 100.00, 3000.00, 0),
(30, 28, 2, 10.00, 100.00, 1000.00, 0),
(31, 29, 1, 3000.00, 100.99, 302970.00, 0),
(32, 30, 2, 10.00, 100.00, 1000.00, 0),
(33, 31, 2, 100.00, 123.00, 12300.00, 0),
(34, 32, 2, 10.00, 0.00, 0.00, 0),
(35, 32, 2, 1.00, 100.00, 100.00, 0),
(36, 33, 2, 12.00, 100.00, 1200.00, 0),
(37, 34, 1, 23.00, 55.00, 1265.00, 0),
(38, 34, 2, 5.00, 7.00, 35.00, 0),
(39, 34, 1, 10.00, 55.00, 550.00, 0),
(40, 34, 2, 10.00, 100.00, 1000.00, 0),
(41, 35, 4, 10.00, 100.00, 1000.00, 0),
(42, 35, 1, 11.94, 100.00, 1194.00, 0),
(43, 36, 2, 12.00, 1.00, 12.00, 0),
(44, 36, 2, 10.00, 1.00, 10.00, 0),
(45, 36, 1, 30.00, 0.50, 15.00, 0),
(46, 37, 2, 12.00, 290.00, 3480.00, 0),
(47, 37, 1, 3.00, 1.50, 4.00, 0),
(48, 38, 2, 12.00, 1.00, 12.00, 0),
(49, 38, 1, 12.00, 0.49, 5.00, 0),
(50, 39, 1, 100.00, 100.00, 10000.00, 0),
(51, 40, 1, 100.00, 0.50, 50.00, 0),
(52, 41, 1, 1.00, 100.90, 100.00, 0),
(53, 42, 1, 1.00, 100.90, 100.00, 0),
(54, 43, 1, 1.00, 100.90, 100.00, 0),
(55, 44, 1, 200.10, 0.50, 100.00, 0),
(56, 45, 1, 1000.00, 100.00, 100000.00, 0),
(57, 46, 1, 1000.00, 100.00, 100000.00, 0),
(58, 47, 2, 1000.00, 100.00, 100000.00, 0),
(59, 48, 2, 10.00, 290.90, 2909.00, 0),
(60, 49, 2, 10.00, 1.00, 10.00, 0),
(61, 50, 1, 100.00, 0.60, 60.00, 0),
(62, 50, 2, 15000.00, 1.15, 17250.00, 0),
(63, 51, 1, 10.00, 1.50, 15.00, 0),
(64, 51, 2, 3.00, 290.00, 870.00, 0),
(65, 53, 1, 10.00, 1.50, 15.00, 2),
(66, 53, 2, 3.00, 290.00, 870.00, 2),
(67, 54, 1, 10.00, 1.50, 15.00, 2),
(68, 54, 2, 3.00, 290.00, 870.00, 2),
(69, 55, 1, 10.00, 1.50, 15.00, 2),
(70, 55, 2, 3.00, 290.00, 870.00, 2);

-- --------------------------------------------------------

--
-- Estrutura para tabela `vendas_pagamentos`
--

CREATE TABLE `vendas_pagamentos` (
  `id` int(11) NOT NULL,
  `venda_id` int(11) NOT NULL,
  `forma_pagamento` enum('dinheiro','pix','cartao','credito_cliente','outros') NOT NULL,
  `valor` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `vendas_suspensas`
--

CREATE TABLE `vendas_suspensas` (
  `id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `empresa_id` int(11) DEFAULT NULL,
  `cliente_id` int(11) DEFAULT NULL,
  `lista_preco_id` int(11) DEFAULT NULL,
  `venda_json` text NOT NULL,
  `data_salva` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `vendas_suspensas`
--

INSERT INTO `vendas_suspensas` (`id`, `usuario_id`, `empresa_id`, `cliente_id`, `lista_preco_id`, `venda_json`, `data_salva`) VALUES
(93, 1, 1, 1, 4, '{\"cliente_id\":1,\"lista_preco_id\":4,\"itens\":[{\"material_id\":\"1\",\"quantidade\":\"5\",\"preco_unitario\":\"0.5\"},{\"material_id\":\"2\",\"quantidade\":\"5\",\"preco_unitario\":\"1\"},{\"material_id\":\"2 - papelao fardo\",\"quantidade\":\"10\",\"preco_unitario\":\"1\"}]}', '2025-08-30 02:50:46');

-- --------------------------------------------------------

--
-- Estrutura para tabela `vendas_temp`
--

CREATE TABLE `vendas_temp` (
  `id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `cliente_id` int(11) DEFAULT NULL,
  `lista_preco_id` int(11) DEFAULT NULL,
  `data_salva` datetime DEFAULT current_timestamp(),
  `dados` longtext NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `vendas_temp`
--

INSERT INTO `vendas_temp` (`id`, `usuario_id`, `cliente_id`, `lista_preco_id`, `data_salva`, `dados`) VALUES
(1, 1, 1, 5, '2025-06-23 20:11:33', '');

--
-- Índices para tabelas despejadas
--

--
-- Índices de tabela `auditoria`
--
ALTER TABLE `auditoria`
  ADD PRIMARY KEY (`id`);

--
-- Índices de tabela `caixas`
--
ALTER TABLE `caixas`
  ADD PRIMARY KEY (`id`);

--
-- Índices de tabela `carrinho`
--
ALTER TABLE `carrinho`
  ADD PRIMARY KEY (`id`);

--
-- Índices de tabela `categorias`
--
ALTER TABLE `categorias`
  ADD PRIMARY KEY (`id`);

--
-- Índices de tabela `clientes`
--
ALTER TABLE `clientes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nome` (`nome`),
  ADD UNIQUE KEY `cpf` (`cpf`),
  ADD KEY `lista_preco_id` (`lista_preco_id`);

--
-- Índices de tabela `clientes_anterior`
--
ALTER TABLE `clientes_anterior`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `cpf` (`cpf`),
  ADD KEY `tabela_preco_id` (`tabela_preco_id`);

--
-- Índices de tabela `compras`
--
ALTER TABLE `compras`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_cliente` (`id_cliente`),
  ADD KEY `fk_forma_pagamento` (`id_forma_pagamento`);

--
-- Índices de tabela `contas_financeiras`
--
ALTER TABLE `contas_financeiras`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_cliente` (`id_cliente`),
  ADD KEY `id_compra` (`id_compra`);

--
-- Índices de tabela `empresas`
--
ALTER TABLE `empresas`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `cnpj` (`cnpj`);

--
-- Índices de tabela `estoque`
--
ALTER TABLE `estoque`
  ADD PRIMARY KEY (`id`),
  ADD KEY `material_id` (`material_id`);

--
-- Índices de tabela `formas_pagamento`
--
ALTER TABLE `formas_pagamento`
  ADD PRIMARY KEY (`id`);

--
-- Índices de tabela `funcionario`
--
ALTER TABLE `funcionario`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `cpf` (`cpf`),
  ADD UNIQUE KEY `usuario` (`usuario`),
  ADD UNIQUE KEY `usuario_2` (`usuario`),
  ADD UNIQUE KEY `cpf_2` (`cpf`);

--
-- Índices de tabela `itens_venda_anterior`
--
ALTER TABLE `itens_venda_anterior`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_venda` (`id_venda`),
  ADD KEY `id_produto` (`id_produto`);

--
-- Índices de tabela `listas_precos`
--
ALTER TABLE `listas_precos`
  ADD PRIMARY KEY (`id`);

--
-- Índices de tabela `materiais`
--
ALTER TABLE `materiais`
  ADD PRIMARY KEY (`id`);

--
-- Índices de tabela `movimentacoes`
--
ALTER TABLE `movimentacoes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `caixa_id` (`caixa_id`);

--
-- Índices de tabela `movimentos_caixa`
--
ALTER TABLE `movimentos_caixa`
  ADD PRIMARY KEY (`id`);

--
-- Índices de tabela `precos_materiais`
--
ALTER TABLE `precos_materiais`
  ADD PRIMARY KEY (`id`),
  ADD KEY `lista_id` (`lista_id`),
  ADD KEY `material_id` (`material_id`);

--
-- Índices de tabela `precos_tabelados`
--
ALTER TABLE `precos_tabelados`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_produto` (`id_produto`),
  ADD KEY `id_tabela` (`id_tabela`);

--
-- Índices de tabela `produtos`
--
ALTER TABLE `produtos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_categoria` (`id_categoria`);

--
-- Índices de tabela `tabelas_precos`
--
ALTER TABLE `tabelas_precos`
  ADD PRIMARY KEY (`id`);

--
-- Índices de tabela `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `fk_empresa` (`empresa_id`);

--
-- Índices de tabela `vendas`
--
ALTER TABLE `vendas`
  ADD PRIMARY KEY (`id`);

--
-- Índices de tabela `vendas_antigo`
--
ALTER TABLE `vendas_antigo`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_cliente` (`id_cliente`);

--
-- Índices de tabela `vendas_excluir_`
--
ALTER TABLE `vendas_excluir_`
  ADD PRIMARY KEY (`id`),
  ADD KEY `cliente_id` (`cliente_id`);

--
-- Índices de tabela `vendas_itens`
--
ALTER TABLE `vendas_itens`
  ADD PRIMARY KEY (`id`);

--
-- Índices de tabela `vendas_pagamentos`
--
ALTER TABLE `vendas_pagamentos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `venda_id` (`venda_id`);

--
-- Índices de tabela `vendas_suspensas`
--
ALTER TABLE `vendas_suspensas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `usuario_id` (`usuario_id`),
  ADD KEY `empresa_id` (`empresa_id`),
  ADD KEY `cliente_id` (`cliente_id`);

--
-- Índices de tabela `vendas_temp`
--
ALTER TABLE `vendas_temp`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `usuario_id` (`usuario_id`);

--
-- AUTO_INCREMENT para tabelas despejadas
--

--
-- AUTO_INCREMENT de tabela `auditoria`
--
ALTER TABLE `auditoria`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `caixas`
--
ALTER TABLE `caixas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT de tabela `carrinho`
--
ALTER TABLE `carrinho`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `categorias`
--
ALTER TABLE `categorias`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `clientes`
--
ALTER TABLE `clientes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT de tabela `clientes_anterior`
--
ALTER TABLE `clientes_anterior`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de tabela `compras`
--
ALTER TABLE `compras`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de tabela `contas_financeiras`
--
ALTER TABLE `contas_financeiras`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `empresas`
--
ALTER TABLE `empresas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de tabela `estoque`
--
ALTER TABLE `estoque`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT de tabela `formas_pagamento`
--
ALTER TABLE `formas_pagamento`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `funcionario`
--
ALTER TABLE `funcionario`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de tabela `itens_venda_anterior`
--
ALTER TABLE `itens_venda_anterior`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `listas_precos`
--
ALTER TABLE `listas_precos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT de tabela `materiais`
--
ALTER TABLE `materiais`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT de tabela `movimentacoes`
--
ALTER TABLE `movimentacoes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=42;

--
-- AUTO_INCREMENT de tabela `movimentos_caixa`
--
ALTER TABLE `movimentos_caixa`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `precos_materiais`
--
ALTER TABLE `precos_materiais`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT de tabela `precos_tabelados`
--
ALTER TABLE `precos_tabelados`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de tabela `produtos`
--
ALTER TABLE `produtos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT de tabela `tabelas_precos`
--
ALTER TABLE `tabelas_precos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de tabela `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT de tabela `vendas`
--
ALTER TABLE `vendas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=56;

--
-- AUTO_INCREMENT de tabela `vendas_antigo`
--
ALTER TABLE `vendas_antigo`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de tabela `vendas_excluir_`
--
ALTER TABLE `vendas_excluir_`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `vendas_itens`
--
ALTER TABLE `vendas_itens`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=71;

--
-- AUTO_INCREMENT de tabela `vendas_pagamentos`
--
ALTER TABLE `vendas_pagamentos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `vendas_suspensas`
--
ALTER TABLE `vendas_suspensas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=94;

--
-- AUTO_INCREMENT de tabela `vendas_temp`
--
ALTER TABLE `vendas_temp`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Restrições para tabelas despejadas
--

--
-- Restrições para tabelas `clientes`
--
ALTER TABLE `clientes`
  ADD CONSTRAINT `clientes_ibfk_1` FOREIGN KEY (`lista_preco_id`) REFERENCES `listas_precos` (`id`) ON DELETE SET NULL;

--
-- Restrições para tabelas `clientes_anterior`
--
ALTER TABLE `clientes_anterior`
  ADD CONSTRAINT `clientes_anterior_ibfk_1` FOREIGN KEY (`tabela_preco_id`) REFERENCES `tabelas_precos` (`id`);

--
-- Restrições para tabelas `compras`
--
ALTER TABLE `compras`
  ADD CONSTRAINT `compras_ibfk_1` FOREIGN KEY (`id_cliente`) REFERENCES `clientes_anterior` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_forma_pagamento` FOREIGN KEY (`id_forma_pagamento`) REFERENCES `formas_pagamento` (`id`);

--
-- Restrições para tabelas `contas_financeiras`
--
ALTER TABLE `contas_financeiras`
  ADD CONSTRAINT `contas_financeiras_ibfk_1` FOREIGN KEY (`id_cliente`) REFERENCES `clientes_anterior` (`id`),
  ADD CONSTRAINT `contas_financeiras_ibfk_2` FOREIGN KEY (`id_compra`) REFERENCES `compras` (`id`);

--
-- Restrições para tabelas `estoque`
--
ALTER TABLE `estoque`
  ADD CONSTRAINT `estoque_ibfk_1` FOREIGN KEY (`material_id`) REFERENCES `materiais` (`id`);

--
-- Restrições para tabelas `itens_venda_anterior`
--
ALTER TABLE `itens_venda_anterior`
  ADD CONSTRAINT `itens_venda_anterior_ibfk_1` FOREIGN KEY (`id_venda`) REFERENCES `vendas_antigo` (`id`),
  ADD CONSTRAINT `itens_venda_anterior_ibfk_2` FOREIGN KEY (`id_produto`) REFERENCES `produtos` (`id`);

--
-- Restrições para tabelas `movimentacoes`
--
ALTER TABLE `movimentacoes`
  ADD CONSTRAINT `movimentacoes_ibfk_1` FOREIGN KEY (`caixa_id`) REFERENCES `caixas` (`id`);

--
-- Restrições para tabelas `precos_materiais`
--
ALTER TABLE `precos_materiais`
  ADD CONSTRAINT `precos_materiais_ibfk_1` FOREIGN KEY (`lista_id`) REFERENCES `listas_precos` (`id`),
  ADD CONSTRAINT `precos_materiais_ibfk_2` FOREIGN KEY (`material_id`) REFERENCES `materiais` (`id`);

--
-- Restrições para tabelas `precos_tabelados`
--
ALTER TABLE `precos_tabelados`
  ADD CONSTRAINT `precos_tabelados_ibfk_1` FOREIGN KEY (`id_produto`) REFERENCES `produtos` (`id`),
  ADD CONSTRAINT `precos_tabelados_ibfk_2` FOREIGN KEY (`id_tabela`) REFERENCES `tabelas_precos` (`id`);

--
-- Restrições para tabelas `produtos`
--
ALTER TABLE `produtos`
  ADD CONSTRAINT `produtos_ibfk_1` FOREIGN KEY (`id_categoria`) REFERENCES `categorias` (`id`);

--
-- Restrições para tabelas `usuarios`
--
ALTER TABLE `usuarios`
  ADD CONSTRAINT `fk_empresa` FOREIGN KEY (`empresa_id`) REFERENCES `empresas` (`id`) ON DELETE SET NULL;

--
-- Restrições para tabelas `vendas_antigo`
--
ALTER TABLE `vendas_antigo`
  ADD CONSTRAINT `vendas_antigo_ibfk_1` FOREIGN KEY (`id_cliente`) REFERENCES `clientes_anterior` (`id`);

--
-- Restrições para tabelas `vendas_excluir_`
--
ALTER TABLE `vendas_excluir_`
  ADD CONSTRAINT `vendas_excluir__ibfk_1` FOREIGN KEY (`cliente_id`) REFERENCES `clientes` (`id`);

--
-- Restrições para tabelas `vendas_pagamentos`
--
ALTER TABLE `vendas_pagamentos`
  ADD CONSTRAINT `vendas_pagamentos_ibfk_1` FOREIGN KEY (`venda_id`) REFERENCES `vendas_excluir_` (`id`);

--
-- Restrições para tabelas `vendas_suspensas`
--
ALTER TABLE `vendas_suspensas`
  ADD CONSTRAINT `vendas_suspensas_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`),
  ADD CONSTRAINT `vendas_suspensas_ibfk_2` FOREIGN KEY (`empresa_id`) REFERENCES `empresas` (`id`),
  ADD CONSTRAINT `vendas_suspensas_ibfk_3` FOREIGN KEY (`cliente_id`) REFERENCES `clientes` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
