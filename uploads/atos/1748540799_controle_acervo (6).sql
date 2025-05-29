-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Tempo de geração: 28/05/2025 às 19:34
-- Versão do servidor: 10.4.32-MariaDB
-- Versão do PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Banco de dados: `controle_acervo`
--

-- --------------------------------------------------------

--
-- Estrutura para tabela `anpp`
--

CREATE TABLE `anpp` (
  `id` int(11) NOT NULL,
  `numero_inquerito` varchar(50) NOT NULL,
  `indiciado` varchar(255) NOT NULL,
  `crime_id` int(11) NOT NULL,
  `nome_vitima` varchar(255) DEFAULT NULL,
  `data_audiencia` date DEFAULT NULL,
  `acordo_realizado` enum('sim','nao') NOT NULL,
  `valor_reparacao` decimal(10,2) DEFAULT NULL,
  `tempo_servico` int(11) DEFAULT NULL,
  `valor_multa` decimal(10,2) DEFAULT NULL,
  `restituicao` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `anpp`
--

INSERT INTO `anpp` (`id`, `numero_inquerito`, `indiciado`, `crime_id`, `nome_vitima`, `data_audiencia`, `acordo_realizado`, `valor_reparacao`, `tempo_servico`, `valor_multa`, `restituicao`) VALUES
(3, '123', 'jua', 19, 'jo', '2025-03-18', '', 10.00, 10, 20.00, '10'),
(4, '1234', 'juao', 19, 'jo', '2025-03-18', '', 10.00, 10, 20.00, '10'),
(6, '12367657', 'jua', 1, 'jo', NULL, '', NULL, NULL, NULL, NULL),
(8, '123242', 'juao', 1, 'joa', '2025-03-11', 'sim', NULL, NULL, NULL, NULL),
(12, '1232424', '3434', 19, 'jo', NULL, 'nao', NULL, NULL, NULL, NULL),
(13, '1245', 'juao', 1, 'joa', '2025-03-10', 'sim', NULL, NULL, NULL, NULL),
(14, '1', 'ju', 1, 'joa', '2025-03-12', 'sim', NULL, NULL, NULL, NULL),
(15, '12333433', 'jua', 1, 'jo', '2025-03-18', 'sim', NULL, NULL, NULL, '1'),
(16, '999', 'juao', 19, 'jo', '2025-03-30', 'sim', NULL, NULL, NULL, '100'),
(17, '1233333', 'jua', 19, 'jo', '2025-03-11', 'nao', NULL, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Estrutura para tabela `atos`
--

CREATE TABLE `atos` (
  `id` int(11) NOT NULL,
  `nome_arquivo` varchar(255) DEFAULT NULL,
  `caminho` text DEFAULT NULL,
  `categoria` varchar(100) DEFAULT NULL,
  `tipo` enum('arquivo','link') DEFAULT NULL,
  `data_upload` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `atos`
--

INSERT INTO `atos` (`id`, `nome_arquivo`, `caminho`, `categoria`, `tipo`, `data_upload`) VALUES
(1, 'favicon-32x32.png', '../uploads/atos/1744595976_favicon-32x32.png', 'CGMPAM', 'arquivo', '2025-04-13 22:59:36'),
(2, 'https://www.youtube.com/', 'https://www.youtube.com/', 'CGMPAM', 'link', '2025-04-13 23:00:39'),
(3, 'logoWhitedd.png', '../uploads/atos/1744596689_logoWhitedd.png', 'CGMPAM', 'arquivo', '2025-04-13 23:11:29'),
(4, 'https://www.youtube.com/', 'https://www.youtube.com/', 'CGMPAM', 'link', '2025-04-13 23:11:39'),
(5, 'https://www.youtube.com/', 'https://www.youtube.com/', 'CGMPAM', 'link', '2025-04-13 23:11:42'),
(6, 'https://www.youtube.com/', 'https://www.youtube.com/', 'CGMPAM', 'link', '2025-04-13 23:11:43'),
(7, 'https://www.youtube.com/', 'https://www.youtube.com/', 'CGMPAM', 'link', '2025-04-13 23:11:46'),
(8, 'favicon-32x32.png', '../uploads/atos/1744596752_favicon-32x32.png', 'CSMPAM', 'arquivo', '2025-04-13 23:12:32'),
(9, 'https://www.youtube.com/', 'https://www.youtube.com/', 'CGMPAM', 'link', '2025-04-14 10:52:48'),
(10, 'CONTRATO - cdapc.pdf', '../uploads/atos/1744638787_CONTRATO - cdapc.pdf', 'CGMPAM', 'arquivo', '2025-04-14 10:53:07');

-- --------------------------------------------------------

--
-- Estrutura para tabela `bairros`
--

CREATE TABLE `bairros` (
  `id` int(11) NOT NULL,
  `nome` varchar(255) NOT NULL,
  `municipio` varchar(100) NOT NULL,
  `municipio_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Despejando dados para a tabela `bairros`
--

INSERT INTO `bairros` (`id`, `nome`, `municipio`, `municipio_id`) VALUES
(1, 'Centro', '', 1),
(2, 'Adrianópolis', '', 1),
(3, 'Cidade Nova', '', 1),
(4, 'Aleixo', '', 1),
(5, 'Centro', '', 2),
(8, 'Centro', '', 3),
(9, 'Itaúna', '', 3),
(10, 'Francesa', '', 3),
(21, 'Jorge Teixeira', '', 1);

-- --------------------------------------------------------

--
-- Estrutura para tabela `crimes`
--

CREATE TABLE `crimes` (
  `id` int(11) NOT NULL,
  `nome` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Despejando dados para a tabela `crimes`
--

INSERT INTO `crimes` (`id`, `nome`) VALUES
(13, 'Furto'),
(5, 'Homicídio'),
(3, 'Latrocínio'),
(14, 'Receptação'),
(15, 'Roubo');

-- --------------------------------------------------------

--
-- Estrutura para tabela `crimes_anpp`
--

CREATE TABLE `crimes_anpp` (
  `id` int(11) NOT NULL,
  `nome` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `crimes_anpp`
--

INSERT INTO `crimes_anpp` (`id`, `nome`) VALUES
(1, 'Furto'),
(19, 'tal');

-- --------------------------------------------------------

--
-- Estrutura para tabela `logs`
--

CREATE TABLE `logs` (
  `id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `acao` varchar(255) NOT NULL,
  `tabela_afetada` varchar(50) NOT NULL,
  `registro_id` int(11) NOT NULL,
  `valores_anteriores` text DEFAULT NULL,
  `valores_novos` text DEFAULT NULL,
  `data_hora` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `logs`
--

INSERT INTO `logs` (`id`, `usuario_id`, `acao`, `tabela_afetada`, `registro_id`, `valores_anteriores`, `valores_novos`, `data_hora`) VALUES
(1, 2, 'Cadastrou um novo processo', 'processos', 8, NULL, '{\"numero\":\"12346555\",\"tipo\":\"tal\",\"data_inicio\":\"2025-02-06\",\"crime\":\"tal\",\"denunciado\":\"roberto\",\"status\":\"Cadastrado\"}', '2025-02-07 16:34:19'),
(2, 2, 'Editou um processo', 'processos', 8, '{\"id\":8,\"numero\":\"12346555\",\"tipo\":\"tal\",\"data_inicio\":\"2025-02-06\",\"crime\":\"tal\",\"denunciado\":\"roberto\",\"status\":\"Cadastrado\",\"usuario_id\":2}', '{\"numero\":\"123467777\",\"tipo\":\"talll\",\"data_inicio\":\"2025-02-06\",\"crime\":\"tal\",\"denunciado\":\"roberto\",\"status\":\"Cadastrado\"}', '2025-02-07 16:44:06'),
(3, 2, 'Deletou um processo', 'processos', 8, '{\"id\":8,\"numero\":\"123467777\",\"tipo\":\"talll\",\"data_inicio\":\"2025-02-06\",\"crime\":\"tal\",\"denunciado\":\"roberto\",\"status\":\"Cadastrado\",\"usuario_id\":2}', NULL, '2025-02-07 16:47:27'),
(4, 6, 'Cadastrou um novo processo', 'processos', 9, NULL, '{\"numero\":\"123458888\",\"tipo\":\"tal\",\"data_inicio\":\"2025-02-07\",\"crime\":\"tal\",\"denunciado\":\"roberto\",\"status\":\"Cadastrado\"}', '2025-02-07 17:03:06'),
(5, 6, 'Cadastrou um novo processo', 'processos', 10, NULL, '{\"numero\":\"123456677\",\"tipo\":\"tal1\",\"data_inicio\":\"2025-02-11\",\"crime\":\"tal\",\"denunciado\":\"roberto\",\"status\":\"Cadastrado\"}', '2025-02-10 14:31:01'),
(6, 2, 'Deletou um processo', 'processos', 10, '{\"id\":10,\"numero\":\"123456677\",\"tipo\":\"tal1\",\"data_inicio\":\"2025-02-11\",\"crime\":\"tal\",\"denunciado\":\"roberto\",\"status\":\"Cadastrado\",\"usuario_id\":6}', NULL, '2025-02-10 14:38:20'),
(7, 2, 'Editou um processo', 'processos', 9, '{\"id\":9,\"numero\":\"123458888\",\"tipo\":\"tal\",\"data_inicio\":\"2025-02-07\",\"crime\":\"tal\",\"denunciado\":\"roberto\",\"status\":\"Cadastrado\",\"usuario_id\":6}', '{\"numero\":\"123458888\",\"tipo\":\"tal\",\"data_inicio\":\"2025-02-07\",\"crime\":\"tal\",\"denunciado\":\"roberto\",\"status\":\"Finalizado\"}', '2025-02-10 14:46:16'),
(8, 2, 'Editou um processo', 'processos', 7, '{\"id\":7,\"numero\":\"123456\",\"tipo\":\"talll\",\"data_inicio\":\"2025-02-20\",\"crime\":\"talll\",\"denunciado\":\"roberto\",\"status\":\"Cadastrado\",\"usuario_id\":2}', '{\"numero\":\"123456\",\"tipo\":\"talll\",\"data_inicio\":\"2025-02-20\",\"crime\":\"talll\",\"denunciado\":\"roberto\",\"status\":\"Finalizado\"}', '2025-02-10 14:46:34'),
(9, 2, 'Editou um processo', 'processos', 4, '{\"id\":4,\"numero\":\"12345\",\"tipo\":\"tal\",\"data_inicio\":\"2025-02-20\",\"crime\":\"talll\",\"denunciado\":\"robertoo\",\"status\":\"Cadastrado\",\"usuario_id\":2}', '{\"numero\":\"12345\",\"tipo\":\"tal\",\"data_inicio\":\"2025-02-20\",\"crime\":\"talll\",\"denunciado\":\"robertoo\",\"status\":\"Finalizado\"}', '2025-02-10 14:46:54'),
(10, 2, 'Cadastrou um novo processo', 'processos', 11, NULL, '{\"numero\":\"1234555555555555\",\"tipo\":\"tal1\",\"data_inicio\":\"2025-02-10\",\"crime\":\"tal\",\"denunciado\":\"roberto\",\"status\":\"Cadastrado\"}', '2025-02-10 14:59:17'),
(11, 2, 'Editou um processo', 'processos', 11, '{\"id\":11,\"numero\":\"1234555555555555\",\"tipo\":\"tal1\",\"data_inicio\":\"2025-02-10\",\"crime\":\"tal\",\"denunciado\":\"roberto\",\"status\":\"Cadastrado\",\"usuario_id\":2}', '{\"numero\":\"1234555555555555\",\"tipo\":\"tal1\",\"data_inicio\":\"2025-02-10\",\"crime\":\"tal\",\"denunciado\":\"roberto\",\"status\":\"Finalizado\"}', '2025-02-10 14:59:25'),
(12, 2, 'Deletou um processo', 'processos', 7, '{\"id\":7,\"numero\":\"123456\",\"tipo\":\"talll\",\"data_inicio\":\"2025-02-20\",\"crime\":\"talll\",\"denunciado\":\"roberto\",\"status\":\"Finalizado\",\"usuario_id\":2}', NULL, '2025-02-10 14:59:37'),
(13, 2, 'Deletou um processo', 'processos', 4, '{\"id\":4,\"numero\":\"12345\",\"tipo\":\"tal\",\"data_inicio\":\"2025-02-20\",\"crime\":\"talll\",\"denunciado\":\"robertoo\",\"status\":\"Finalizado\",\"usuario_id\":2}', NULL, '2025-02-10 14:59:38'),
(14, 2, 'Editou um processo', 'processos', 11, '{\"id\":11,\"numero\":\"1234555555555555\",\"tipo\":\"tal1\",\"data_inicio\":\"2025-02-10\",\"crime\":\"tal\",\"denunciado\":\"roberto\",\"status\":\"Finalizado\",\"usuario_id\":2}', '{\"numero\":\"1234555555555555\",\"tipo\":\"tal1\",\"data_inicio\":\"2025-02-10\",\"crime\":\"tal\",\"denunciado\":\"roberto\",\"status\":\"Finalizado\"}', '2025-02-13 10:56:43'),
(15, 2, 'Cadastrou um novo processo', 'processos', 12, NULL, '{\"numero\":\"1234224344\",\"natureza\":\"Inqu\\u00e9rito Policial\",\"outra_natureza\":\"\",\"data_denuncia\":\"2025-02-12\",\"crime\":\"Roubo\",\"outro_crime\":\"\",\"denunciado\":\"roberto\",\"vitima\":null,\"local_municipio\":\"Manaus\",\"local_bairro\":\"Centro\",\"sentenca\":\"Prescri\\u00e7\\u00e3o\",\"outra_sentenca\":\"\",\"data_sentenca\":\"2025-02-20\",\"recursos\":\"N\\u00e3o h\\u00e1\",\"status\":\"Cadastrado\"}', '2025-02-13 14:23:05'),
(16, 2, 'Cadastrou um novo processo', 'processos', 13, NULL, '{\"numero\":\"123456\",\"natureza\":\"Inqu\\u00e9rito Policial\",\"outra_natureza\":\"\",\"data_denuncia\":\"2025-02-13\",\"crime\":\"Roubo\",\"outro_crime\":\"\",\"denunciado\":\"roberto\",\"vitima\":null,\"local_municipio\":\"Manaus\",\"local_bairro\":\"Adrian\\u00f3polis\",\"sentenca\":\"Outra\",\"outra_sentenca\":\"tal\",\"data_sentenca\":\"2025-02-21\",\"recursos\":\"Defesa\",\"status\":\"Cadastrado\"}', '2025-02-13 14:43:11'),
(17, 2, 'Deletou um processo', 'processos', 13, '{\"id\":13,\"numero\":\"123456\",\"natureza\":\"Inqu\\u00e9rito Policial\",\"data_denuncia\":\"2025-02-13\",\"crime\":\"Roubo\",\"denunciado\":\"roberto\",\"status\":\"Cadastrado\",\"usuario_id\":2,\"outra_natureza\":\"\",\"local_municipio\":\"Manaus\",\"local_bairro\":\"Adrian\\u00f3polis\",\"vitima\":null,\"outro_crime\":\"\",\"sentenca\":\"Outra\",\"outra_sentenca\":\"tal\",\"data_sentenca\":\"2025-02-21\",\"recursos\":\"Defesa\"}', NULL, '2025-02-13 14:43:29'),
(18, 2, 'Cadastrou um novo processo', 'processos', 14, NULL, '{\"numero\":\"1234\",\"natureza\":\"Inqu\\u00e9rito Policial\",\"outra_natureza\":\"\",\"data_denuncia\":\"2025-02-13\",\"crime\":\"Furto\",\"outro_crime\":\"\",\"denunciado\":\"roberto\",\"vitima\":null,\"local_municipio\":\"Manaus\",\"local_bairro\":\"Cidade Nova\",\"sentenca\":\"Absolut\\u00f3ria\",\"outra_sentenca\":\"\",\"data_sentenca\":\"2025-02-13\",\"recursos\":\"N\\u00e3o h\\u00e1\",\"status\":\"Cadastrado\"}', '2025-02-13 15:29:08'),
(19, 2, 'Cadastrou um novo processo', 'processos', 15, NULL, '{\"numero\":\"12\",\"natureza\":\"Inqu\\u00e9rito Policial\",\"outra_natureza\":\"\",\"data_denuncia\":\"2025-02-08\",\"crime\":\"Furto\",\"outro_crime\":\"\",\"denunciado\":\"roberto\",\"vitima\":null,\"local_municipio\":\"Manaus\",\"local_bairro\":\"Adrian\\u00f3polis\",\"sentenca\":\"Absolut\\u00f3ria\",\"outra_sentenca\":\"\",\"data_sentenca\":\"2025-02-13\",\"recursos\":\"Defesa\",\"status\":\"Cadastrado\"}', '2025-02-13 15:57:59'),
(20, 2, 'Cadastrou um novo processo', 'processos', 16, NULL, '{\"numero\":\"2321313\",\"natureza\":\"Outra\",\"outra_natureza\":\"tal\",\"data_denuncia\":\"2025-02-17\",\"crime_id\":3,\"denunciado\":\"tal\",\"vitima\":null,\"local_municipio\":\"Itacoatiara\",\"local_bairro\":\"Centro\",\"sentenca\":\"Outra\",\"outra_sentenca\":\"tal\",\"data_sentenca\":\"2025-02-18\",\"recursos\":\"Defesa\",\"status\":\"Cadastrado\"}', '2025-02-17 14:29:52'),
(21, 2, 'Cadastrou um novo processo', 'processos', 17, NULL, '{\"numero\":\"1234567\",\"natureza\":\"PICNF\",\"outra_natureza\":\"\",\"data_denuncia\":\"2025-02-11\",\"crime_id\":5,\"denunciado\":\"roberto\",\"vitima\":\"joao\",\"local_municipio\":\"1\",\"local_bairro\":\"4\",\"sentenca\":\"Absolut\\u00f3ria\",\"outra_sentenca\":\"\",\"data_sentenca\":\"\",\"recursos\":\"Defesa\",\"status\":\"Cadastrado\"}', '2025-02-17 14:49:56'),
(22, 2, 'Cadastrou um novo processo', 'processos', 18, NULL, '{\"numero\":\"1234655534\",\"natureza\":\"Outra\",\"outra_natureza\":\"tal\",\"data_denuncia\":\"2025-02-18\",\"crime_id\":5,\"denunciado\":\"roberto\",\"vitima\":null,\"local_municipio\":\"1\",\"local_bairro\":\"4\",\"sentenca\":\"Absolut\\u00f3ria\",\"outra_sentenca\":\"\",\"data_sentenca\":\"2025-02-25\",\"recursos\":\"N\\u00e3o h\\u00e1\",\"status\":\"Cadastrado\"}', '2025-02-17 14:55:32'),
(23, 2, 'Deletou um processo', 'processos', 18, '{\"id\":18,\"numero\":\"1234655534\",\"natureza\":\"Outra\",\"data_denuncia\":\"2025-02-18\",\"denunciado\":\"roberto\",\"status\":\"Cadastrado\",\"usuario_id\":2,\"outra_natureza\":\"tal\",\"local_municipio\":\"1\",\"local_bairro\":\"4\",\"vitima\":null,\"outro_crime\":null,\"sentenca\":\"Absolut\\u00f3ria\",\"outra_sentenca\":\"\",\"data_sentenca\":\"2025-02-25\",\"recursos\":\"N\\u00e3o h\\u00e1\",\"crime_id\":5}', NULL, '2025-02-17 16:27:30'),
(24, 2, 'Deletou um processo', 'processos', 17, '{\"id\":17,\"numero\":\"1234567\",\"natureza\":\"PICNF\",\"data_denuncia\":\"2025-02-11\",\"denunciado\":\"roberto\",\"status\":\"Cadastrado\",\"usuario_id\":2,\"outra_natureza\":\"\",\"local_municipio\":\"1\",\"local_bairro\":\"4\",\"vitima\":\"joao\",\"outro_crime\":null,\"sentenca\":\"Absolut\\u00f3ria\",\"outra_sentenca\":\"\",\"data_sentenca\":\"0000-00-00\",\"recursos\":\"Defesa\",\"crime_id\":5}', NULL, '2025-02-17 16:27:34'),
(25, 2, 'Cadastrou um novo processo', 'processos', 19, NULL, '{\"numero\":\"123453434344\",\"natureza\":\"A\\u00e7\\u00e3o Penal\",\"outra_natureza\":\"\",\"data_denuncia\":\"2025-02-18\",\"crime_id\":1,\"denunciado\":\"roberto\",\"vitima\":null,\"local_municipio\":\"1\",\"local_bairro\":\"4\",\"sentenca\":\"Condenat\\u00f3ria\",\"outra_sentenca\":\"\",\"data_sentenca\":\"2025-02-18\",\"recursos\":\"Acusa\\u00e7\\u00e3o\",\"status\":\"Cadastrado\"}', '2025-02-18 15:27:31'),
(26, 2, 'Cadastrou um novo processo', 'processos', 20, NULL, '{\"numero\":\"11111111111\",\"natureza\":\"Inqu\\u00e9rito Policial\",\"outra_natureza\":\"\",\"data_denuncia\":\"2025-02-11\",\"crime_id\":3,\"denunciado\":\"lucas\",\"vitima\":null,\"local_municipio\":\"2\",\"local_bairro\":\"6\",\"sentenca\":\"Absolut\\u00f3ria\",\"outra_sentenca\":\"\",\"data_sentenca\":\"2025-02-18\",\"recursos\":\"Defesa\",\"status\":\"Cadastrado\"}', '2025-02-18 15:35:04'),
(27, 2, 'Cadastrou um novo processo', 'processos', 21, NULL, '{\"numero\":\"2312312313\",\"natureza\":\"A\\u00e7\\u00e3o Penal\",\"outra_natureza\":\"\",\"data_denuncia\":\"2025-02-04\",\"crime_id\":5,\"denunciado\":\"roberto\",\"vitima\":null,\"local_municipio\":\"2\",\"local_bairro\":\"5\",\"sentenca\":\"Outra\",\"outra_sentenca\":\"tal\",\"data_sentenca\":\"2025-02-20\",\"recursos\":\"Defesa\",\"status\":\"Cadastrado\"}', '2025-02-18 15:35:26'),
(28, 2, 'Cadastrou um novo processo', 'processos', 22, NULL, '{\"numero\":\"123465558983123123\",\"natureza\":\"Inqu\\u00e9rito Policial\",\"outra_natureza\":\"\",\"data_denuncia\":\"2025-02-11\",\"crime_id\":5,\"denunciado\":\"lucas\",\"vitima\":\"dsd\",\"local_municipio\":\"2\",\"local_bairro\":\"5\",\"sentenca\":\"Absolut\\u00f3ria\",\"outra_sentenca\":\"\",\"data_sentenca\":\"2025-02-18\",\"recursos\":\"Defesa\",\"status\":\"Cadastrado\"}', '2025-02-18 15:35:57'),
(29, 2, 'Cadastrou um novo processo', 'processos', 23, NULL, '{\"numero\":\"23123123123\",\"natureza\":\"Inqu\\u00e9rito Policial\",\"outra_natureza\":\"\",\"data_denuncia\":\"2025-02-12\",\"crime_id\":3,\"denunciado\":\"roberto\",\"vitima\":null,\"local_municipio\":\"2\",\"local_bairro\":\"5\",\"sentenca\":\"Absolut\\u00f3ria\",\"outra_sentenca\":\"\",\"data_sentenca\":\"2025-02-19\",\"recursos\":\"Defesa\",\"status\":\"Cadastrado\"}', '2025-02-18 15:37:13'),
(30, 2, 'Cadastrou um novo processo', 'processos', 24, NULL, '{\"numero\":\"234344\",\"natureza\":\"Inqu\\u00e9rito Policial\",\"outra_natureza\":\"\",\"data_denuncia\":\"2025-02-17\",\"crime_id\":3,\"denunciado\":\"lucas\",\"vitima\":null,\"local_municipio\":\"2\",\"local_bairro\":\"6\",\"sentenca\":\"Absolut\\u00f3ria\",\"outra_sentenca\":\"\",\"data_sentenca\":\"2025-02-19\",\"recursos\":\"Defesa\",\"status\":\"Cadastrado\"}', '2025-02-18 15:37:52'),
(31, 2, 'Cadastrou um novo processo', 'processos', 25, NULL, '{\"numero\":\"3434244\",\"natureza\":\"PICNF\",\"outra_natureza\":\"\",\"data_denuncia\":\"2025-02-04\",\"crime_id\":5,\"denunciado\":\"roberto\",\"vitima\":null,\"local_municipio\":\"2\",\"local_bairro\":\"5\",\"sentenca\":\"Prescri\\u00e7\\u00e3o\",\"outra_sentenca\":\"\",\"data_sentenca\":\"2025-02-25\",\"recursos\":\"Defesa\",\"status\":\"Cadastrado\"}', '2025-02-18 15:38:31'),
(32, 2, 'Cadastrou um novo processo', 'processos', 26, NULL, '{\"numero\":\"123\",\"natureza\":\"PICNF\",\"outra_natureza\":\"\",\"data_denuncia\":\"2025-02-19\",\"crime_id\":3,\"denunciado\":\"tal\",\"vitima\":null,\"local_municipio\":\"1\",\"local_bairro\":\"4\",\"sentenca\":\"Prescri\\u00e7\\u00e3o\",\"outra_sentenca\":\"\",\"data_sentenca\":\"2025-02-12\",\"recursos\":\"N\\u00e3o h\\u00e1\",\"status\":\"Cadastrado\"}', '2025-02-25 16:00:00'),
(33, 2, 'Cadastrou um novo processo', 'processos', 27, NULL, '{\"numero\":\"1234\",\"natureza\":\"Inqu\\u00e9rito Policial\",\"outra_natureza\":\"\",\"data_denuncia\":\"2025-02-18\",\"crime_id\":3,\"denunciado\":\"tal\",\"vitima\":null,\"local_municipio\":\"1\",\"local_bairro\":\"4\",\"sentenca\":\"Absolut\\u00f3ria\",\"outra_sentenca\":\"\",\"data_sentenca\":\"2025-02-05\",\"recursos\":\"Defesa\",\"status\":\"Cadastrado\"}', '2025-02-25 16:02:25'),
(34, 2, 'Deletou um processo', 'processos', 19, '{\"id\":19,\"numero\":\"123453434344\",\"natureza\":\"A\\u00e7\\u00e3o Penal\",\"data_denuncia\":\"2025-02-18\",\"denunciado\":\"roberto carlos\",\"status\":null,\"usuario_id\":2,\"outra_natureza\":null,\"local_municipio\":\"2\",\"local_bairro\":\"5\",\"vitima\":null,\"outro_crime\":null,\"sentenca\":\"Condenat\\u00f3ria\",\"outra_sentenca\":null,\"data_sentenca\":\"2025-02-18\",\"recursos\":\"Acusa\\u00e7\\u00e3o\",\"crime_id\":1}', NULL, '2025-03-17 17:46:32'),
(35, 2, 'Cadastro', 'anpp', 17, NULL, '{\"numero_inquerito\":\"1233333\",\"indiciado\":\"jua\",\"crime\":\"19\",\"nome_vitima\":\"jo\",\"data_audiencia\":\"2025-03-11\",\"acordo\":\"nao_realizado\",\"reparacao\":\"nao\",\"valor_reparacao\":\"\",\"servico_comunitario\":\"nao\",\"tempo_servico\":\"\",\"multa\":\"nao\",\"valor_multa\":\"\",\"restituicao\":\"\"}', '2025-03-30 08:24:52'),
(36, 2, 'Excluiu um ANPP', 'anpp', 9, '{\"id\":9,\"numero_inquerito\":\"123242323\",\"indiciado\":\"jua\",\"crime_id\":1,\"nome_vitima\":\"jo\",\"data_audiencia\":\"2025-03-12\",\"acordo_realizado\":\"nao\",\"valor_reparacao\":null,\"tempo_servico\":null,\"valor_multa\":null,\"restituicao\":null}', NULL, '2025-03-30 19:24:48'),
(37, 2, 'Excluiu um ANPP', 'anpp', 5, '{\"id\":5,\"numero_inquerito\":\"12343\",\"indiciado\":\"jua\",\"crime_id\":1,\"nome_vitima\":\"jo\",\"data_audiencia\":\"2025-03-11\",\"acordo_realizado\":\"\",\"valor_reparacao\":\"0.00\",\"tempo_servico\":0,\"valor_multa\":null,\"restituicao\":null}', NULL, '2025-03-30 19:26:34'),
(38, 2, 'Cadastrou um novo processo', 'processos', 28, NULL, '{\"numero\":\"1232322\",\"natureza\":\"Inqu\\u00e9rito Policial\",\"outra_natureza\":\"\",\"data_denuncia\":\"2025-04-09\",\"crime_id\":13,\"denunciado\":\"tal\",\"vitima\":null,\"local_municipio\":\"1\",\"local_bairro\":\"2\",\"sentenca\":\"N\\u00e3o h\\u00e1\",\"outra_sentenca\":\"\",\"data_sentenca\":\"\",\"recursos\":\"N\\u00e3o h\\u00e1\",\"status\":\"Cadastrado\"}', '2025-04-13 23:58:00'),
(39, 2, 'Cadastrou um novo processo', 'processos', 29, NULL, '{\"numero\":\"1232322222\",\"natureza\":\"A\\u00e7\\u00e3o Penal\",\"outra_natureza\":\"\",\"data_denuncia\":\"2025-04-13\",\"crime_id\":13,\"denunciado\":\"tal\",\"vitima\":null,\"local_municipio\":\"2\",\"local_bairro\":\"5\",\"sentenca\":\"N\\u00e3o h\\u00e1\",\"outra_sentenca\":\"\",\"data_sentenca\":\"\",\"recursos\":\"N\\u00e3o h\\u00e1\",\"status\":\"Cadastrado\"}', '2025-04-13 23:59:33'),
(40, 2, 'Cadastrou um novo processo', 'processos', 30, NULL, '{\"numero\":\"123232223333333333\",\"natureza\":\"A\\u00e7\\u00e3o Penal\",\"outra_natureza\":\"\",\"data_denuncia\":\"2025-04-14\",\"crime_id\":13,\"denunciado\":\"tal\",\"vitima\":\"N\\u00e3o h\\u00e1\",\"local_municipio\":\"2\",\"local_bairro\":\"5\",\"sentenca\":\"N\\u00e3o h\\u00e1\",\"outra_sentenca\":\"\",\"data_sentenca\":\"\",\"recursos\":\"N\\u00e3o h\\u00e1\",\"status\":\"Cadastrado\"}', '2025-04-14 00:03:58');

-- --------------------------------------------------------

--
-- Estrutura para tabela `municipios`
--

CREATE TABLE `municipios` (
  `id` int(11) NOT NULL,
  `nome` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Despejando dados para a tabela `municipios`
--

INSERT INTO `municipios` (`id`, `nome`) VALUES
(2, 'Itacoatiara'),
(1, 'Manaus'),
(3, 'Parintins');

-- --------------------------------------------------------

--
-- Estrutura para tabela `processos`
--

CREATE TABLE `processos` (
  `id` int(11) NOT NULL,
  `numero` varchar(50) NOT NULL,
  `natureza` varchar(255) NOT NULL,
  `data_denuncia` date NOT NULL,
  `denunciado` varchar(100) NOT NULL,
  `status` enum('Cadastrado','Finalizado') DEFAULT 'Cadastrado',
  `usuario_id` int(11) NOT NULL,
  `outra_natureza` varchar(255) DEFAULT NULL,
  `local_municipio` varchar(100) NOT NULL,
  `local_bairro` varchar(100) NOT NULL,
  `vitima` varchar(255) DEFAULT NULL,
  `outro_crime` varchar(255) DEFAULT NULL,
  `sentenca` varchar(100) DEFAULT NULL,
  `outra_sentenca` varchar(255) DEFAULT NULL,
  `data_sentenca` date DEFAULT NULL,
  `recursos` varchar(50) DEFAULT NULL,
  `crime_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `processos`
--

INSERT INTO `processos` (`id`, `numero`, `natureza`, `data_denuncia`, `denunciado`, `status`, `usuario_id`, `outra_natureza`, `local_municipio`, `local_bairro`, `vitima`, `outro_crime`, `sentenca`, `outra_sentenca`, `data_sentenca`, `recursos`, `crime_id`) VALUES
(20, '11111111111', 'Inquérito Policial', '2025-02-11', 'lucas', 'Cadastrado', 2, '', '2', '6', NULL, NULL, 'Absolutória', '', '2025-02-18', 'Defesa', 3),
(21, '2312312313', 'Ação Penal', '2025-02-04', 'roberto', 'Finalizado', 2, NULL, '2', '5', NULL, NULL, 'Outra', 'tal', '2025-02-20', 'Defesa', 5),
(22, '123465558983123123', 'Inquérito Policial', '2025-02-11', 'lucas', 'Cadastrado', 2, '', '2', '5', 'dsd', NULL, 'Absolutória', '', '2025-02-18', 'Defesa', 5),
(23, '23123123123', 'Inquérito Policial', '2025-02-12', 'roberto', 'Cadastrado', 2, '', '2', '5', NULL, NULL, 'Absolutória', '', '2025-02-19', 'Defesa', 3),
(24, '234344', 'Inquérito Policial', '2025-02-17', 'lucas', 'Cadastrado', 2, '', '2', '6', NULL, NULL, 'Absolutória', '', '2025-02-19', 'Defesa', 3),
(25, '3434244', 'PICNF', '2025-02-04', 'roberto', 'Cadastrado', 2, '', '2', '5', NULL, NULL, 'Prescrição', '', '2025-02-25', 'Defesa', 5),
(26, '123', 'PICNF', '2025-02-19', 'tal', 'Cadastrado', 2, '', '1', '4', NULL, NULL, 'Prescrição', '', '2025-02-12', 'Não há', 3),
(27, '1234', 'Inquérito Policial', '2025-02-18', 'tal', 'Cadastrado', 2, '', '1', '4', NULL, NULL, 'Absolutória', '', '2025-02-05', 'Defesa', 3),
(28, '1232322', 'Inquérito Policial', '2025-04-09', 'tal', 'Cadastrado', 2, '', '1', '2', NULL, NULL, 'Não há', '', '0000-00-00', 'Não há', 13),
(29, '1232322222', 'Ação Penal', '2025-04-13', 'tal', 'Cadastrado', 2, '', '2', '5', NULL, NULL, 'Não há', '', '0000-00-00', 'Não há', 13),
(30, '123232223333333333', 'Ação Penal', '2025-04-14', 'tal', 'Cadastrado', 2, '', '2', '5', 'Não há', NULL, 'Não há', '', '0000-00-00', 'Não há', 13);

-- --------------------------------------------------------

--
-- Estrutura para tabela `usuarios`
--

CREATE TABLE `usuarios` (
  `id` int(11) NOT NULL,
  `nome` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `senha` varchar(255) NOT NULL,
  `perfil` enum('administrador','cadastrador','consultor') NOT NULL,
  `aprovado` tinyint(1) DEFAULT 0,
  `data_criacao` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `usuarios`
--

INSERT INTO `usuarios` (`id`, `nome`, `email`, `senha`, `perfil`, `aprovado`, `data_criacao`) VALUES
(2, 'Usuário Teste', 'teste@exemplo.com', '$2y$10$TVhagk159mar2ThnRzAUq.i95/AY5h484GHJnx/1WArre3ltBI.9q', 'administrador', 1, '2025-02-05 21:02:35'),
(6, 'João Pedro Lima Rodrigues', 'joaopedrolimar@gmail.com', '$2y$10$9OkEseSuAHL9tnMzRtHFBeXHMz99qNK01fCrg4vjpvBOz.ccDFGOC', 'consultor', 0, '2025-02-07 16:56:35'),
(7, 'faf', 'joaopedrolimar@gmail.comm', '$2y$10$djl5jpzL8IW29UUUMhZCXeWZcqnbFlj.DYerpLgB9PkmAGAnR5mI2', 'cadastrador', 1, '2025-02-10 14:09:35');

--
-- Índices para tabelas despejadas
--

--
-- Índices de tabela `anpp`
--
ALTER TABLE `anpp`
  ADD PRIMARY KEY (`id`),
  ADD KEY `crime_id` (`crime_id`);

--
-- Índices de tabela `atos`
--
ALTER TABLE `atos`
  ADD PRIMARY KEY (`id`);

--
-- Índices de tabela `bairros`
--
ALTER TABLE `bairros`
  ADD PRIMARY KEY (`id`),
  ADD KEY `municipio_id` (`municipio_id`);

--
-- Índices de tabela `crimes`
--
ALTER TABLE `crimes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nome` (`nome`);

--
-- Índices de tabela `crimes_anpp`
--
ALTER TABLE `crimes_anpp`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nome` (`nome`);

--
-- Índices de tabela `logs`
--
ALTER TABLE `logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `usuario_id` (`usuario_id`);

--
-- Índices de tabela `municipios`
--
ALTER TABLE `municipios`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nome` (`nome`);

--
-- Índices de tabela `processos`
--
ALTER TABLE `processos`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `numero` (`numero`),
  ADD KEY `usuario_id` (`usuario_id`),
  ADD KEY `fk_crime` (`crime_id`);

--
-- Índices de tabela `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT para tabelas despejadas
--

--
-- AUTO_INCREMENT de tabela `anpp`
--
ALTER TABLE `anpp`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT de tabela `atos`
--
ALTER TABLE `atos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT de tabela `bairros`
--
ALTER TABLE `bairros`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;

--
-- AUTO_INCREMENT de tabela `crimes`
--
ALTER TABLE `crimes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT de tabela `crimes_anpp`
--
ALTER TABLE `crimes_anpp`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT de tabela `logs`
--
ALTER TABLE `logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=41;

--
-- AUTO_INCREMENT de tabela `municipios`
--
ALTER TABLE `municipios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT de tabela `processos`
--
ALTER TABLE `processos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;

--
-- AUTO_INCREMENT de tabela `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- Restrições para tabelas despejadas
--

--
-- Restrições para tabelas `anpp`
--
ALTER TABLE `anpp`
  ADD CONSTRAINT `anpp_ibfk_1` FOREIGN KEY (`crime_id`) REFERENCES `crimes_anpp` (`id`) ON DELETE CASCADE;

--
-- Restrições para tabelas `bairros`
--
ALTER TABLE `bairros`
  ADD CONSTRAINT `bairros_ibfk_1` FOREIGN KEY (`municipio_id`) REFERENCES `municipios` (`id`) ON DELETE CASCADE;

--
-- Restrições para tabelas `logs`
--
ALTER TABLE `logs`
  ADD CONSTRAINT `logs_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`);

--
-- Restrições para tabelas `processos`
--
ALTER TABLE `processos`
  ADD CONSTRAINT `fk_crime` FOREIGN KEY (`crime_id`) REFERENCES `crimes` (`id`),
  ADD CONSTRAINT `processos_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
