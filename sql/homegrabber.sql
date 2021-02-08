-- phpMyAdmin SQL Dump
-- version 5.0.2
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Creato il: Feb 01, 2021 alle 12:49
-- Versione del server: 8.0.17
-- Versione PHP: 7.2.24

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `bikemon`
--

-- --------------------------------------------------------

--
-- Struttura della tabella `grabbed_articles`
--

CREATE TABLE `grabbed_articles` (
  `id` int(11) NOT NULL,
  `listurl` mediumtext CHARACTER SET latin1 COLLATE latin1_swedish_ci,
  `update_date` datetime DEFAULT NULL,
  `ag_domain` varchar(254) CHARACTER SET latin1 COLLATE latin1_swedish_ci DEFAULT NULL,
  `ag_url` mediumtext CHARACTER SET latin1 COLLATE latin1_swedish_ci,
  `ag_id` varchar(254) CHARACTER SET latin1 COLLATE latin1_swedish_ci DEFAULT NULL,
  `ag_title` mediumtext CHARACTER SET latin1 COLLATE latin1_swedish_ci,
  `ag_description` mediumtext CHARACTER SET latin1 COLLATE latin1_swedish_ci,
  `ag_cod` mediumtext CHARACTER SET latin1 COLLATE latin1_swedish_ci,
  `ag_tipoofferta` mediumtext CHARACTER SET latin1 COLLATE latin1_swedish_ci,
  `ag_provincia` mediumtext CHARACTER SET latin1 COLLATE latin1_swedish_ci,
  `ag_localita` mediumtext CHARACTER SET latin1 COLLATE latin1_swedish_ci,
  `ag_piano` varchar(254) CHARACTER SET latin1 COLLATE latin1_swedish_ci DEFAULT NULL,
  `ag_dimensioni` mediumtext CHARACTER SET latin1 COLLATE latin1_swedish_ci,
  `ag_prezzo` varchar(254) CHARACTER SET latin1 COLLATE latin1_swedish_ci DEFAULT NULL,
  `ag_indirizzo` mediumtext CHARACTER SET latin1 COLLATE latin1_swedish_ci,
  `geoc_latitude` varchar(20) DEFAULT NULL,
  `geoc_longitude` varchar(20) DEFAULT NULL,
  `dist_centro` int(11) DEFAULT NULL,
  `insert_date` datetime DEFAULT NULL,
  `ag_dataannuncio` varchar(45) DEFAULT NULL,
  `ag_contratto` varchar(254) DEFAULT NULL,
  `ag_tipologia` varchar(254) DEFAULT NULL,
  `ag_locali` varchar(45) DEFAULT NULL,
  `ag_tipoproprieta` varchar(254) DEFAULT NULL,
  `ag_infocatastali` varchar(45) DEFAULT NULL,
  `ag_annocostruzione` varchar(45) DEFAULT NULL,
  `ag_statocasa` varchar(45) DEFAULT NULL,
  `ag_riscaldamento` varchar(45) DEFAULT NULL,
  `ag_climatizzazione` varchar(45) DEFAULT NULL,
  `ag_classe_energetica` varchar(45) DEFAULT NULL,
  `agenzia_nome` varchar(254) DEFAULT NULL,
  `agenzia_alltext` text,
  `agenzia_tel` varchar(254) DEFAULT NULL,
  `deleted` varchar(6) DEFAULT NULL,
  `favorite` varchar(6) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Struttura della tabella `grabbed_articles_images`
--

CREATE TABLE `grabbed_articles_images` (
  `id` int(11) NOT NULL,
  `ag_domain` varchar(254) CHARACTER SET latin1 COLLATE latin1_swedish_ci DEFAULT NULL,
  `ag_id` varchar(254) CHARACTER SET latin1 COLLATE latin1_swedish_ci DEFAULT NULL,
  `imageData` mediumblob NOT NULL,
  `imageType` varchar(25) NOT NULL DEFAULT '',
  `title` varchar(254) CHARACTER SET latin1 COLLATE latin1_swedish_ci DEFAULT NULL,
  `update_date` datetime DEFAULT NULL,
  `insert_date` datetime DEFAULT NULL,
  `ag_url` tinytext
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Struttura della tabella `grabbed_articles_meta`
--

CREATE TABLE `grabbed_articles_meta` (
  `id` int(11) NOT NULL,
  `ag_domain` varchar(254) CHARACTER SET latin1 COLLATE latin1_swedish_ci DEFAULT NULL,
  `ag_id` varchar(254) CHARACTER SET latin1 COLLATE latin1_swedish_ci DEFAULT NULL,
  `a_key` varchar(254) CHARACTER SET latin1 COLLATE latin1_swedish_ci DEFAULT NULL,
  `a_value` longtext CHARACTER SET latin1 COLLATE latin1_swedish_ci,
  `a_value_2` mediumtext,
  `update_date` datetime DEFAULT NULL,
  `insert_date` datetime DEFAULT NULL,
  `ag_url` tinytext
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Indici per le tabelle scaricate
--

--
-- Indici per le tabelle `grabbed_articles`
--
ALTER TABLE `grabbed_articles`
  ADD PRIMARY KEY (`id`);

--
-- Indici per le tabelle `grabbed_articles_images`
--
ALTER TABLE `grabbed_articles_images`
  ADD PRIMARY KEY (`id`);

--
-- Indici per le tabelle `grabbed_articles_meta`
--
ALTER TABLE `grabbed_articles_meta`
  ADD PRIMARY KEY (`id`),
  ADD KEY `ag_id` (`ag_id`,`a_key`);

--
-- AUTO_INCREMENT per le tabelle scaricate
--

--
-- AUTO_INCREMENT per la tabella `grabbed_articles`
--
ALTER TABLE `grabbed_articles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT per la tabella `grabbed_articles_images`
--
ALTER TABLE `grabbed_articles_images`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT per la tabella `grabbed_articles_meta`
--
ALTER TABLE `grabbed_articles_meta`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
