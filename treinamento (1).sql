-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Tempo de geração: 02/02/2026 às 21:37
-- Versão do servidor: 10.4.32-MariaDB
-- Versão do PHP: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Banco de dados: `treinamento`
--

-- --------------------------------------------------------

--
-- Estrutura para tabela `salas`
--

CREATE TABLE `salas` (
  `id` int(11) NOT NULL,
  `nome` varchar(100) NOT NULL,
  `criado_por` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `ativa` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `salas`
--

INSERT INTO `salas` (`id`, `nome`, `criado_por`, `created_at`, `ativa`) VALUES
(1, 'Teste_GERAL', 4, '2026-01-29 17:54:44', 0),
(2, 'Teste_GERAL2', 4, '2026-01-29 17:55:46', 1),
(3, 'Teste_GERAL3', 4, '2026-01-29 18:44:10', 1);

-- --------------------------------------------------------

--
-- Estrutura para tabela `sala_profs`
--

CREATE TABLE `sala_profs` (
  `id` int(11) NOT NULL,
  `id_sala` int(11) DEFAULT NULL,
  `id_prof` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `sala_profs`
--

INSERT INTO `sala_profs` (`id`, `id_sala`, `id_prof`) VALUES
(1, 1, 2),
(2, 1, 4),
(3, 2, 2),
(4, 2, 3),
(5, 2, 4),
(6, 3, 2),
(7, 3, 3),
(8, 3, 2);

-- --------------------------------------------------------

--
-- Estrutura para tabela `sala_users`
--

CREATE TABLE `sala_users` (
  `id` int(11) NOT NULL,
  `id_sala` int(11) NOT NULL,
  `id_user` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `sala_users`
--

INSERT INTO `sala_users` (`id`, `id_sala`, `id_user`) VALUES
(1, 1, 2),
(2, 2, 2),
(3, 2, 3),
(4, 3, 2),
(5, 3, 3);

-- --------------------------------------------------------

--
-- Estrutura para tabela `tb_login`
--

CREATE TABLE `tb_login` (
  `id_login` int(11) NOT NULL,
  `email` varchar(120) NOT NULL,
  `senha` varchar(255) NOT NULL,
  `tipo` enum('prof','user') NOT NULL,
  `ativo` tinyint(1) DEFAULT 1,
  `criado_em` datetime DEFAULT current_timestamp(),
  `last_activity` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `tb_login`
--

INSERT INTO `tb_login` (`id_login`, `email`, `senha`, `tipo`, `ativo`, `criado_em`, `last_activity`) VALUES
(3, 'felipe.helpdesk@masterhealth.com.br', '12345', 'user', 1, '2026-01-28 12:53:45', NULL),
(4, 'rossi.thiago@masterhealth.com.br', '2', 'prof', 1, '2026-01-28 17:15:56', '2026-01-28 17:55:06'),
(5, 'treinamento@masterhealth.com.br', '12345', 'prof', 1, '2026-01-29 12:55:46', NULL),
(6, 'ricardo.tecnologia@masterhealth.com.br', '12345', 'user', 1, '2026-01-29 13:01:03', NULL);

-- --------------------------------------------------------

--
-- Estrutura para tabela `tb_mensagens`
--

CREATE TABLE `tb_mensagens` (
  `id_msg` int(11) NOT NULL,
  `id_prof` int(11) NOT NULL,
  `id_user` int(11) DEFAULT NULL,
  `sala_id` int(11) NOT NULL,
  `mensagem` text NOT NULL,
  `tipo` enum('geral','privada','audio') NOT NULL DEFAULT 'privada',
  `tipo_remetente` enum('prof','user') NOT NULL,
  `data_envio` datetime NOT NULL DEFAULT current_timestamp(),
  `data_resposta` datetime DEFAULT NULL,
  `tempo_resposta` int(11) DEFAULT NULL,
  `lida` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `tb_mensagens`
--

INSERT INTO `tb_mensagens` (`id_msg`, `id_prof`, `id_user`, `sala_id`, `mensagem`, `tipo`, `tipo_remetente`, `data_envio`, `data_resposta`, `tempo_resposta`, `lida`) VALUES
(1, 2, NULL, 1, 'testee', 'geral', 'prof', '2026-01-29 14:54:54', NULL, NULL, 0),
(2, 2, 2, 1, 'que legal', 'privada', 'prof', '2026-01-29 14:55:04', NULL, NULL, 1),
(3, 2, 2, 1, 'legal mesmo', 'privada', 'user', '2026-01-29 14:55:11', NULL, 17, 1),
(4, 2, 2, 2, 'teste', 'privada', 'prof', '2026-01-29 14:55:51', NULL, NULL, 1),
(5, 2, 2, 2, 'wow top', 'privada', 'user', '2026-01-29 14:56:16', NULL, NULL, 1),
(6, 2, NULL, 2, 'ola', 'geral', 'prof', '2026-01-29 15:28:20', NULL, NULL, 0),
(7, 2, 2, 2, 'oie', 'privada', 'prof', '2026-01-29 15:28:31', NULL, NULL, 1),
(8, 2, 2, 2, 'ola', 'privada', 'user', '2026-01-29 15:28:42', NULL, 22, 1),
(9, 2, 2, 3, 'teste', 'privada', 'prof', '2026-01-30 14:52:54', NULL, NULL, 1),
(27, 2, 2, 3, 'audio_1769803533_849.webm', 'audio', 'prof', '2026-01-30 17:05:33', NULL, NULL, 1),
(28, 2, 2, 3, 'audio_1769803893_663.webm', 'audio', 'user', '2026-01-30 17:11:33', NULL, NULL, 1),
(29, 2, 2, 3, 'audio_1769805121_591.webm', 'audio', 'user', '2026-01-30 17:32:01', NULL, NULL, 1),
(30, 2, 2, 3, 'audio_1769805134_702.webm', 'audio', 'prof', '2026-01-30 17:32:14', NULL, NULL, 1),
(31, 2, 2, 3, 'audio_1769805296_205.webm', 'audio', 'prof', '2026-01-30 17:34:56', NULL, NULL, 1),
(32, 2, 2, 3, 'ola', 'privada', 'prof', '2026-02-02 13:38:05', NULL, NULL, 1),
(33, 2, 2, 3, 'audio_1770050295_728.webm', 'audio', 'user', '2026-02-02 13:38:15', NULL, NULL, 1);

-- --------------------------------------------------------

--
-- Estrutura para tabela `tb_professor`
--

CREATE TABLE `tb_professor` (
  `id_prof` int(11) NOT NULL,
  `id_login` int(11) NOT NULL,
  `nome` varchar(100) NOT NULL,
  `online` tinyint(1) NOT NULL,
  `last_ping` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `tb_professor`
--

INSERT INTO `tb_professor` (`id_prof`, `id_login`, `nome`, `online`, `last_ping`) VALUES
(2, 4, 'Thiago Rossi', 1, '2026-02-02 13:38:23'),
(3, 5, 'Leticia', 0, NULL);

-- --------------------------------------------------------

--
-- Estrutura para tabela `tb_user`
--

CREATE TABLE `tb_user` (
  `id_user` int(11) NOT NULL,
  `id_login` int(11) NOT NULL,
  `nome` varchar(100) NOT NULL,
  `online` tinyint(1) DEFAULT 0,
  `last_ping` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `tb_user`
--

INSERT INTO `tb_user` (`id_user`, `id_login`, `nome`, `online`, `last_ping`) VALUES
(2, 3, 'Felipe Mammana', 1, '2026-02-02 13:38:47'),
(3, 6, 'Ricardo Almeida', 0, NULL);

--
-- Índices para tabelas despejadas
--

--
-- Índices de tabela `salas`
--
ALTER TABLE `salas`
  ADD PRIMARY KEY (`id`);

--
-- Índices de tabela `sala_profs`
--
ALTER TABLE `sala_profs`
  ADD PRIMARY KEY (`id`);

--
-- Índices de tabela `sala_users`
--
ALTER TABLE `sala_users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `id_sala` (`id_sala`,`id_user`),
  ADD KEY `id_user` (`id_user`);

--
-- Índices de tabela `tb_login`
--
ALTER TABLE `tb_login`
  ADD PRIMARY KEY (`id_login`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Índices de tabela `tb_mensagens`
--
ALTER TABLE `tb_mensagens`
  ADD PRIMARY KEY (`id_msg`),
  ADD KEY `id_prof` (`id_prof`),
  ADD KEY `id_user` (`id_user`),
  ADD KEY `sala_id` (`sala_id`);

--
-- Índices de tabela `tb_professor`
--
ALTER TABLE `tb_professor`
  ADD PRIMARY KEY (`id_prof`),
  ADD KEY `id_login` (`id_login`);

--
-- Índices de tabela `tb_user`
--
ALTER TABLE `tb_user`
  ADD PRIMARY KEY (`id_user`),
  ADD KEY `id_login` (`id_login`);

--
-- AUTO_INCREMENT para tabelas despejadas
--

--
-- AUTO_INCREMENT de tabela `salas`
--
ALTER TABLE `salas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de tabela `sala_profs`
--
ALTER TABLE `sala_profs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT de tabela `sala_users`
--
ALTER TABLE `sala_users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de tabela `tb_login`
--
ALTER TABLE `tb_login`
  MODIFY `id_login` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de tabela `tb_mensagens`
--
ALTER TABLE `tb_mensagens`
  MODIFY `id_msg` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=34;

--
-- AUTO_INCREMENT de tabela `tb_professor`
--
ALTER TABLE `tb_professor`
  MODIFY `id_prof` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de tabela `tb_user`
--
ALTER TABLE `tb_user`
  MODIFY `id_user` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Restrições para tabelas despejadas
--

--
-- Restrições para tabelas `sala_users`
--
ALTER TABLE `sala_users`
  ADD CONSTRAINT `sala_users_ibfk_1` FOREIGN KEY (`id_sala`) REFERENCES `salas` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `sala_users_ibfk_2` FOREIGN KEY (`id_user`) REFERENCES `tb_user` (`id_user`) ON DELETE CASCADE;

--
-- Restrições para tabelas `tb_mensagens`
--
ALTER TABLE `tb_mensagens`
  ADD CONSTRAINT `tb_mensagens_ibfk_1` FOREIGN KEY (`id_prof`) REFERENCES `tb_professor` (`id_prof`),
  ADD CONSTRAINT `tb_mensagens_ibfk_2` FOREIGN KEY (`id_user`) REFERENCES `tb_user` (`id_user`),
  ADD CONSTRAINT `tb_mensagens_ibfk_3` FOREIGN KEY (`sala_id`) REFERENCES `salas` (`id`);

--
-- Restrições para tabelas `tb_professor`
--
ALTER TABLE `tb_professor`
  ADD CONSTRAINT `tb_professor_ibfk_1` FOREIGN KEY (`id_login`) REFERENCES `tb_login` (`id_login`);

--
-- Restrições para tabelas `tb_user`
--
ALTER TABLE `tb_user`
  ADD CONSTRAINT `tb_user_ibfk_1` FOREIGN KEY (`id_login`) REFERENCES `tb_login` (`id_login`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
