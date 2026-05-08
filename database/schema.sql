SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

CREATE TABLE `salas` (
  `id` int(11) NOT NULL,
  `nome` varchar(100) NOT NULL,
  `criado_por` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `ativa` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `sala_profs` (
  `id` int(11) NOT NULL,
  `id_sala` int(11) DEFAULT NULL,
  `id_prof` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `sala_users` (
  `id` int(11) NOT NULL,
  `id_sala` int(11) NOT NULL,
  `id_user` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `tb_login` (
  `id_login` int(11) NOT NULL,
  `email` varchar(120) NOT NULL,
  `senha` varchar(255) NOT NULL,
  `tipo` enum('prof','user') NOT NULL,
  `ativo` tinyint(1) DEFAULT 1,
  `criado_em` datetime DEFAULT current_timestamp(),
  `last_activity` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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

CREATE TABLE `tb_professor` (
  `id_prof` int(11) NOT NULL,
  `id_login` int(11) NOT NULL,
  `nome` varchar(100) NOT NULL,
  `online` tinyint(1) NOT NULL,
  `last_ping` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `tb_user` (
  `id_user` int(11) NOT NULL,
  `id_login` int(11) NOT NULL,
  `nome` varchar(100) NOT NULL,
  `online` tinyint(1) DEFAULT 0,
  `last_ping` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

ALTER TABLE `salas`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `sala_profs`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `sala_users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `id_sala` (`id_sala`,`id_user`),
  ADD KEY `id_user` (`id_user`);

ALTER TABLE `tb_login`
  ADD PRIMARY KEY (`id_login`),
  ADD UNIQUE KEY `email` (`email`);

ALTER TABLE `tb_mensagens`
  ADD PRIMARY KEY (`id_msg`),
  ADD KEY `id_prof` (`id_prof`),
  ADD KEY `id_user` (`id_user`),
  ADD KEY `sala_id` (`sala_id`);

ALTER TABLE `tb_professor`
  ADD PRIMARY KEY (`id_prof`),
  ADD KEY `id_login` (`id_login`);

ALTER TABLE `tb_user`
  ADD PRIMARY KEY (`id_user`),
  ADD KEY `id_login` (`id_login`);

ALTER TABLE `salas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `sala_profs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `sala_users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `tb_login`
  MODIFY `id_login` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `tb_mensagens`
  MODIFY `id_msg` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `tb_professor`
  MODIFY `id_prof` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `tb_user`
  MODIFY `id_user` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `sala_users`
  ADD CONSTRAINT `sala_users_ibfk_1` FOREIGN KEY (`id_sala`) REFERENCES `salas` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `sala_users_ibfk_2` FOREIGN KEY (`id_user`) REFERENCES `tb_user` (`id_user`) ON DELETE CASCADE;

ALTER TABLE `tb_mensagens`
  ADD CONSTRAINT `tb_mensagens_ibfk_1` FOREIGN KEY (`id_prof`) REFERENCES `tb_professor` (`id_prof`),
  ADD CONSTRAINT `tb_mensagens_ibfk_2` FOREIGN KEY (`id_user`) REFERENCES `tb_user` (`id_user`),
  ADD CONSTRAINT `tb_mensagens_ibfk_3` FOREIGN KEY (`sala_id`) REFERENCES `salas` (`id`);

ALTER TABLE `tb_professor`
  ADD CONSTRAINT `tb_professor_ibfk_1` FOREIGN KEY (`id_login`) REFERENCES `tb_login` (`id_login`);

ALTER TABLE `tb_user`
  ADD CONSTRAINT `tb_user_ibfk_1` FOREIGN KEY (`id_login`) REFERENCES `tb_login` (`id_login`);

COMMIT;
