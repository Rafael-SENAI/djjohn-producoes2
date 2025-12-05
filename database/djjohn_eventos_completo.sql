-- ========================================
-- DJ JOHN PRODUCOES - BANCO COMPLETO
-- Versão com campos em português
-- ========================================

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

-- Criar banco
CREATE DATABASE IF NOT EXISTS `djjohn_eventos` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `djjohn_eventos`;

-- ========================================
-- TABELA: Usuários
-- ========================================
DROP TABLE IF EXISTS `usuarios`;
CREATE TABLE `usuarios` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nome` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `senha` varchar(255) NOT NULL,
  `funcao` enum('admin','funcionario') DEFAULT 'funcionario',
  `status` enum('ativo','inativo') DEFAULT 'ativo',
  `criado_em` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `atualizado_em` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `usuarios` (`id`, `nome`, `email`, `senha`, `funcao`, `status`) VALUES
(1, 'DJ John Admin', 'admin@djjohn.com', '$2y$10$e0MYzXyjpJS7Pd0i5qGIhOr5NkU5U4FI0A8.LMYqrJ5Xrs.4/0EJu', 'admin', 'ativo');

-- ========================================
-- TABELA: Categorias de Eventos
-- ========================================
DROP TABLE IF EXISTS `categorias_eventos`;
CREATE TABLE `categorias_eventos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nome` varchar(100) NOT NULL,
  `descricao` text,
  `cor` varchar(7) DEFAULT '#FF0040',
  `criado_em` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `categorias_eventos` (`nome`, `descricao`, `cor`) VALUES
('Casamentos', 'Casamentos e cerimônias', '#FF69B4'),
('Eventos Corporativos', 'Eventos empresariais', '#3498db'),
('Festas de 15 Anos', 'Festas de debutante', '#9b59b6'),
('Shows e Apresentações', 'Shows musicais', '#e74c3c');

-- ========================================
-- TABELA: Eventos
-- ========================================
DROP TABLE IF EXISTS `eventos`;
CREATE TABLE `eventos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `titulo` varchar(200) NOT NULL,
  `categoria_id` int(11) DEFAULT NULL,
  `descricao` text,
  `data_evento` date DEFAULT NULL,
  `horario_evento` time DEFAULT NULL,
  `local` varchar(200) DEFAULT NULL,
  `nome_cliente` varchar(100) DEFAULT NULL,
  `email_cliente` varchar(100) DEFAULT NULL,
  `telefone_cliente` varchar(20) DEFAULT NULL,
  `numero_convidados` int(11) DEFAULT NULL,
  `valor_orcamento` decimal(10,2) DEFAULT NULL,
  `status_evento` enum('pendente','confirmado','concluido','cancelado') DEFAULT 'pendente',
  `criado_por` int(11) DEFAULT NULL,
  `criado_em` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `atualizado_em` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `categoria_id` (`categoria_id`),
  KEY `criado_por` (`criado_por`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ========================================
-- TABELA: Atrações
-- ========================================
DROP TABLE IF EXISTS `atracoes`;
CREATE TABLE `atracoes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nome` varchar(200) NOT NULL,
  `categoria` varchar(100) DEFAULT NULL,
  `preco` decimal(10,2) DEFAULT NULL,
  `descricao` text,
  `imagem` varchar(255) DEFAULT NULL,
  `url_video` varchar(255) DEFAULT NULL,
  `status_atracao` enum('disponivel','indisponivel','manutencao') DEFAULT 'disponivel',
  `criado_em` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `atualizado_em` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ========================================
-- TABELA: Salões
-- ========================================
DROP TABLE IF EXISTS `saloes`;
CREATE TABLE `saloes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nome` varchar(200) NOT NULL,
  `endereco` text,
  `cidade` varchar(100) DEFAULT NULL,
  `estado` varchar(50) DEFAULT NULL,
  `cep` varchar(20) DEFAULT NULL,
  `telefone` varchar(20) DEFAULT NULL,
  `capacidade` int(11) DEFAULT NULL,
  `faixa_preco` varchar(100) DEFAULT NULL,
  `descricao` text,
  `comodidades` text,
  `status_salao` enum('ativo','inativo') DEFAULT 'ativo',
  `criado_em` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `atualizado_em` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `saloes` (`nome`, `endereco`, `cidade`, `estado`, `capacidade`, `status_salao`) VALUES
('KRIK EVENTOS', 'Rua Principal, 123', 'Votuporanga', 'SP', 500, 'ativo'),
('Espaço Elegance', 'Av. República, 456', 'Votuporanga', 'SP', 300, 'ativo'),
('Salão Crystal', 'Rua das Flores, 789', 'Votuporanga', 'SP', 400, 'ativo'),
('Buffet Premium', 'Av. Brasil, 321', 'Votuporanga', 'SP', 250, 'ativo');

-- ========================================
-- TABELA: Galeria
-- ========================================
DROP TABLE IF EXISTS `galeria`;
CREATE TABLE `galeria` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `evento_id` int(11) DEFAULT NULL,
  `imagem` varchar(255) NOT NULL,
  `categoria` varchar(100) DEFAULT NULL,
  `enviado_por` int(11) DEFAULT NULL,
  `criado_em` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `evento_id` (`evento_id`),
  KEY `enviado_por` (`enviado_por`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ========================================
-- TABELA: Orçamentos
-- ========================================
DROP TABLE IF EXISTS `orcamentos`;
CREATE TABLE `orcamentos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nome` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `telefone` varchar(20) DEFAULT NULL,
  `tipo_evento` varchar(100) DEFAULT NULL,
  `data_evento` date DEFAULT NULL,
  `local` varchar(200) DEFAULT NULL,
  `numero_convidados` int(11) DEFAULT NULL,
  `faixa_orcamento` varchar(100) DEFAULT NULL,
  `mensagem` text,
  `observacoes` text,
  `status_orcamento` enum('novo','contatado','enviado','aprovado','rejeitado') DEFAULT 'novo',
  `criado_em` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ========================================
-- TABELA: Tarefas
-- ========================================
DROP TABLE IF EXISTS `tarefas`;
CREATE TABLE `tarefas` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `titulo` varchar(200) NOT NULL,
  `descricao` text,
  `atribuido_para` int(11) DEFAULT NULL,
  `evento_id` int(11) DEFAULT NULL,
  `data_vencimento` date DEFAULT NULL,
  `prioridade` enum('baixa','media','alta') DEFAULT 'media',
  `status_tarefa` enum('pendente','em_andamento','concluida','cancelada') DEFAULT 'pendente',
  `criado_em` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `atualizado_em` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `atribuido_para` (`atribuido_para`),
  KEY `evento_id` (`evento_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ========================================
-- TABELA: Depoimentos
-- ========================================
DROP TABLE IF EXISTS `depoimentos`;
CREATE TABLE `depoimentos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nome_cliente` varchar(100) NOT NULL,
  `tipo_evento` varchar(100) DEFAULT NULL,
  `data_evento` date DEFAULT NULL,
  `avaliacao` int(11) DEFAULT NULL,
  `mensagem` text,
  `aprovado` tinyint(1) DEFAULT '0',
  `criado_em` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ========================================
-- TABELA: Comunicados
-- ========================================
DROP TABLE IF EXISTS `comunicados`;
CREATE TABLE `comunicados` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `titulo` varchar(200) NOT NULL,
  `mensagem` text,
  `tipo` enum('info','aviso','urgente') DEFAULT 'info',
  `criado_por` int(11) DEFAULT NULL,
  `criado_em` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `criado_por` (`criado_por`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ========================================
-- TABELA: Materiais
-- ========================================
DROP TABLE IF EXISTS `materiais`;
CREATE TABLE `materiais` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nome` varchar(200) NOT NULL,
  `descricao` text,
  `quantidade` int(11) DEFAULT '0',
  `unidade` varchar(50) DEFAULT NULL,
  `preco` decimal(10,2) DEFAULT NULL,
  `fornecedor` varchar(200) DEFAULT NULL,
  `criado_em` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `atualizado_em` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ========================================
-- TABELAS DE RELACIONAMENTO
-- ========================================

DROP TABLE IF EXISTS `eventos_atracoes`;
CREATE TABLE `eventos_atracoes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `evento_id` int(11) NOT NULL,
  `atracao_id` int(11) NOT NULL,
  `criado_em` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `evento_id` (`evento_id`),
  KEY `atracao_id` (`atracao_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `eventos_materiais`;
CREATE TABLE `eventos_materiais` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `evento_id` int(11) NOT NULL,
  `material_id` int(11) NOT NULL,
  `quantidade` int(11) DEFAULT '1',
  `criado_em` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `evento_id` (`evento_id`),
  KEY `material_id` (`material_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

COMMIT;

SELECT 'Banco de dados criado com sucesso em PORTUGUÊS!' as status;
