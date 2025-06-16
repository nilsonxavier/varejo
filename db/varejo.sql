-- phpMyAdmin SQL Dump
-- version 5.2.1deb1+deb12u1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Tempo de geração: 16/06/2025 às 00:47
-- Versão do servidor: 10.11.9-MariaDB-deb12-log
-- Versão do PHP: 8.2.28

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
  `data_abertura` datetime DEFAULT NULL,
  `valor_inicial` decimal(10,2) DEFAULT NULL,
  `data_fechamento` datetime DEFAULT NULL,
  `valor_final` decimal(10,2) DEFAULT NULL,
  `status` enum('aberto','fechado') DEFAULT 'aberto'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `caixas`
--

INSERT INTO `caixas` (`id`, `usuario_id`, `data_abertura`, `valor_inicial`, `data_fechamento`, `valor_final`, `status`) VALUES
(1, 1, '2025-06-11 03:32:38', 200.00, '2025-06-11 03:33:54', 132.00, 'fechado'),
(2, 1, '2025-06-11 03:38:46', 450.00, '2025-06-11 03:48:41', 450.00, 'fechado'),
(3, 1, '2025-06-11 03:52:53', 800.00, '2025-06-11 03:59:31', 844.00, 'fechado'),
(4, 1, '2025-06-11 04:09:54', 12.00, '2025-06-11 04:10:08', -2.00, 'fechado'),
(5, 1, '2025-06-11 20:27:00', 222.00, '2025-06-11 20:31:47', 222.00, 'fechado'),
(6, 1, '2025-06-11 20:33:23', 223.00, '2025-06-11 20:34:44', 223.00, 'fechado'),
(7, 1, '2025-06-11 20:34:50', 21.00, '2025-06-11 20:35:01', 21.00, 'fechado'),
(8, 1, '2025-06-11 20:44:54', 123.00, '2025-06-11 20:45:10', 100.00, 'fechado'),
(9, 1, '2025-06-11 20:48:07', 500.00, '2025-06-11 20:48:32', 823.00, 'fechado'),
(10, 1, '2025-06-12 20:00:55', 400.00, '2025-06-12 20:01:16', 401.00, 'fechado'),
(11, 1, '2025-06-15 20:20:25', 1000.00, '2025-06-15 22:14:32', 1351.00, 'fechado'),
(12, 1, '2025-06-15 22:46:00', 200.00, NULL, NULL, 'aberto');

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
  `saldo` decimal(10,2) NOT NULL DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `clientes`
--

INSERT INTO `clientes` (`id`, `nome`, `telefone`, `email`, `lista_preco_id`, `created_at`, `cpf`, `endereco`, `cep`, `saldo`) VALUES
(1, 'pedro paulo santos pereira xavier', '85991853813', 'nilsonxavier12@gmail.com', 2, '2025-06-15 19:38:24', '', 'rua cristo redentor', '60762465', -26.00);

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
  `data_compra` datetime NOT NULL,
  `valor_total` decimal(10,2) NOT NULL,
  `itens` longtext NOT NULL CHECK (json_valid(`itens`)),
  `id_forma_pagamento` int(11) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `compras`
--

INSERT INTO `compras` (`id`, `id_cliente`, `data_compra`, `valor_total`, `itens`, `id_forma_pagamento`) VALUES
(1, 4, '2025-06-01 23:40:50', 0.00, 'null', 1);

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
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `listas_precos`
--

INSERT INTO `listas_precos` (`id`, `nome`, `created_at`) VALUES
(1, 'compra', '2025-06-14 15:29:55'),
(2, 'teste', '2025-06-14 15:38:25'),
(3, 'teste', '2025-06-14 15:45:20');

-- --------------------------------------------------------

--
-- Estrutura para tabela `materiais`
--

CREATE TABLE `materiais` (
  `id` int(11) NOT NULL,
  `nome` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `materiais`
--

INSERT INTO `materiais` (`id`, `nome`) VALUES
(1, 'papelÃ£o'),
(2, 'papelÃ£o fardo'),
(4, 'ferro');

-- --------------------------------------------------------

--
-- Estrutura para tabela `movimentacoes`
--

CREATE TABLE `movimentacoes` (
  `id` int(11) NOT NULL,
  `caixa_id` int(11) DEFAULT NULL,
  `tipo` enum('entrada','saida') DEFAULT NULL,
  `valor` decimal(10,2) DEFAULT NULL,
  `descricao` varchar(255) DEFAULT NULL,
  `data_movimentacao` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `movimentacoes`
--

INSERT INTO `movimentacoes` (`id`, `caixa_id`, `tipo`, `valor`, `descricao`, `data_movimentacao`) VALUES
(1, 1, 'entrada', 265.00, 'compra', '2025-06-11 03:33:36'),
(2, 1, 'saida', 333.00, 'compra', '2025-06-11 03:33:44'),
(3, 3, 'entrada', 44.00, 'cafÃ©', '2025-06-11 03:59:27'),
(4, 4, 'saida', 14.00, 'cafÃ©', '2025-06-11 04:10:01'),
(5, 8, 'saida', 23.00, 'teste', '2025-06-11 20:45:07'),
(6, 9, 'entrada', 323.00, 'venda', '2025-06-11 20:48:25'),
(7, 10, 'saida', 32.00, 'ao mosso', '2025-06-12 20:01:05'),
(8, 10, 'entrada', 33.00, 'venda', '2025-06-12 20:01:13'),
(9, 11, 'entrada', 223.00, 'Venda ID 1 - pagamento em dinheiro', NULL),
(10, 11, 'entrada', 10.00, 'Venda ID 2 - pagamento em dinheiro', NULL),
(11, 11, 'entrada', 90.00, 'Venda ID 3 - pagamento em dinheiro', NULL),
(12, 11, 'entrada', 8.00, 'Venda ID 4 - pagamento em dinheiro', NULL),
(13, 11, 'entrada', 20.00, 'Venda ID 5 - pagamento em dinheiro', NULL);

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
(6, 1, 4, 1.00);

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
  `tipo` enum('admin','vendedor','estoquista') DEFAULT 'vendedor'
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Despejando dados para a tabela `usuarios`
--

INSERT INTO `usuarios` (`id`, `nome`, `email`, `senha`, `tipo`) VALUES
(1, 'nilson', 'nilsonxavier12@gmail.com', '$2y$10$vrmh/5/vAh.3a0qKEWhpMuZoja.5aoM.j9ApBIJhv/q.htNam5SEW', 'admin'),
(2, 'pedro', 'pedropaulomfp11@Gmail.com', '$2y$10$C6ifQl76YOuURAmc9QafA.x4GsV7Lx.Sxpejy60MQ2EJK2Kol3YEC', 'admin'),
(13, 'michele', 'michelesantosmfp@gmail.com', '$2y$10$VC7PUG./lN5f6IBEwqCn5Or7Shg352PA/IChihLhzWs36r54DVQqm', 'admin'),
(14, 'teste', 'teste@gmail.com', '$2y$10$m5g7jJkcIKj/W4v7DlAHGujgbsHAldtsWFanYFhnBnIDo3uBj40fK', 'admin');

-- --------------------------------------------------------

--
-- Estrutura para tabela `vendas`
--

CREATE TABLE `vendas` (
  `id` int(11) NOT NULL,
  `cliente_id` int(11) DEFAULT NULL,
  `lista_preco_id` int(11) NOT NULL,
  `total` decimal(10,2) NOT NULL,
  `data` timestamp NULL DEFAULT current_timestamp(),
  `valor_dinheiro` decimal(10,2) DEFAULT 0.00,
  `valor_pix` decimal(10,2) DEFAULT 0.00,
  `valor_cartao` decimal(10,2) DEFAULT 0.00,
  `valor_pago` decimal(10,2) DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `vendas`
--

INSERT INTO `vendas` (`id`, `cliente_id`, `lista_preco_id`, `total`, `data`, `valor_dinheiro`, `valor_pix`, `valor_cartao`, `valor_pago`) VALUES
(3, 1, 1, 290.00, '2025-06-15 21:49:51', 90.00, 200.00, 0.00, 290.00),
(4, 1, 1, 18.00, '2025-06-15 21:54:24', 8.00, 9.00, 0.00, 17.00),
(5, 1, 1, 66.00, '2025-06-15 22:13:49', 20.00, 20.00, 0.00, 40.00);

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
  `subtotal` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `vendas_itens`
--

INSERT INTO `vendas_itens` (`id`, `venda_id`, `material_id`, `quantidade`, `preco_unitario`, `subtotal`) VALUES
(1, 1, 1, 3333.00, 1.50, 4999.00),
(2, 2, 1, 10.00, 1.50, 15.00),
(3, 3, 2, 1.00, 290.00, 290.00),
(4, 4, 1, 12.00, 1.50, 18.00),
(5, 5, 1, 44.00, 1.50, 66.00);

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
-- Índices de tabela `categorias`
--
ALTER TABLE `categorias`
  ADD PRIMARY KEY (`id`);

--
-- Índices de tabela `clientes`
--
ALTER TABLE `clientes`
  ADD PRIMARY KEY (`id`),
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
  ADD UNIQUE KEY `email` (`email`);

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT de tabela `categorias`
--
ALTER TABLE `categorias`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `clientes`
--
ALTER TABLE `clientes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de tabela `materiais`
--
ALTER TABLE `materiais`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de tabela `movimentacoes`
--
ALTER TABLE `movimentacoes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT de tabela `movimentos_caixa`
--
ALTER TABLE `movimentos_caixa`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `precos_materiais`
--
ALTER TABLE `precos_materiais`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT de tabela `vendas`
--
ALTER TABLE `vendas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de tabela `vendas_pagamentos`
--
ALTER TABLE `vendas_pagamentos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

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
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
