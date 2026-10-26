-- Estrutura do banco de dados "encarregado" (Ajuda do Encarregado - PMPR)
-- Gerada a partir do banco local (mysqldump --no-data).
-- Uso: importe no phpMyAdmin ou rode  mysql -u root < database.sql

CREATE DATABASE IF NOT EXISTS encarregado CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE encarregado;

CREATE TABLE IF NOT EXISTS `usuarios` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `usuario` varchar(50) NOT NULL,
  `nome` varchar(100) NOT NULL,
  `rg` varchar(20) NOT NULL,
  `cpf` varchar(14) NOT NULL,
  `posto_graduacao` varchar(50) NOT NULL,
  `unidade` varchar(100) NOT NULL,
  `telefone` varchar(15) NOT NULL,
  `email` varchar(255) DEFAULT NULL,
  `senha` varchar(255) NOT NULL,
  `criado_em` timestamp NOT NULL DEFAULT current_timestamp(),
  `perfil` enum('admin','operador') NOT NULL DEFAULT 'operador',
  `status` enum('pendente','aprovado','recusado') NOT NULL DEFAULT 'pendente',
  `data_criacao` datetime DEFAULT current_timestamp(),
  `ultimo_acesso` datetime DEFAULT NULL,
  `termo_versao` varchar(10) DEFAULT NULL,
  `termo_aceito_em` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `usuario` (`usuario`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `procedimentos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_usuario` int(11) NOT NULL,
  `tipo` varchar(30) NOT NULL,
  `numero` varchar(50) NOT NULL,
  `portaria` varchar(150) DEFAULT NULL,
  `data_instauracao` date NOT NULL,
  `prazo_dias` int(11) NOT NULL DEFAULT 10,
  `data_vencimento` date NOT NULL,
  `status` varchar(30) NOT NULL DEFAULT 'Em Andamento',
  `sintese` text DEFAULT NULL,
  `data_criacao` datetime DEFAULT current_timestamp(),
  `data_atualizacao` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `id_usuario` (`id_usuario`),
  CONSTRAINT `procedimentos_ibfk_1` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `procedimento_atos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_procedimento` int(11) NOT NULL,
  `id_usuario` int(11) NOT NULL,
  `titulo` varchar(150) NOT NULL,
  `descricao` text DEFAULT NULL,
  `tipo_ato` varchar(50) NOT NULL,
  `data_ato` date NOT NULL,
  `arquivo_anexo` varchar(255) DEFAULT NULL,
  `data_criacao` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `id_procedimento` (`id_procedimento`),
  CONSTRAINT `procedimento_atos_ibfk_1` FOREIGN KEY (`id_procedimento`) REFERENCES `procedimentos` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `procedimento_envolvidos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_procedimento` int(11) NOT NULL,
  `papel_legal` varchar(50) NOT NULL,
  `tipo_pessoa` enum('militar','civil') NOT NULL DEFAULT 'militar',
  `nome` varchar(150) NOT NULL,
  `posto_graduacao` varchar(50) DEFAULT NULL,
  `quadro` varchar(30) DEFAULT NULL,
  `cpf_cin` varchar(14) DEFAULT NULL,
  `rg` varchar(30) DEFAULT NULL,
  `telefone` varchar(20) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `data_criacao` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `id_procedimento` (`id_procedimento`),
  CONSTRAINT `procedimento_envolvidos_ibfk_1` FOREIGN KEY (`id_procedimento`) REFERENCES `procedimentos` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `recuperacao_senha` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `usuario_id` int(11) NOT NULL,
  `token` varchar(255) NOT NULL,
  `expira_em` datetime NOT NULL,
  `criado_em` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `usuario_id` (`usuario_id`),
  CONSTRAINT `recuperacao_senha_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Migração (bancos criados antes do Termo de Consentimento): registro do aceite
-- da Política de Privacidade. Rode uma vez no servidor de produção.
-- ALTER TABLE usuarios ADD COLUMN termo_versao VARCHAR(10) NULL DEFAULT NULL, ADD COLUMN termo_aceito_em DATETIME NULL DEFAULT NULL;

-- Correção de dados: versões antigas do cadastro gravavam perfil 'usuario',
-- valor que não existe no ENUM (o MySQL não estrito salvava vazio).
UPDATE usuarios SET perfil = 'operador' WHERE perfil = '';

-- Primeiro administrador: gere o hash com  php gerar_senha.php "SuaSenha"  e substitua abaixo.
-- INSERT INTO usuarios (usuario, nome, rg, cpf, posto_graduacao, unidade, telefone, email, senha, perfil, status)
-- VALUES ('admin', 'Administrador', '0', '000.000.000-00', 'Cap.', '6º BPM - Cascavel', '(45) 00000-0000', 'admin@exemplo.com', '<HASH>', 'admin', 'aprovado');
