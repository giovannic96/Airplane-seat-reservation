-- phpMyAdmin SQL Dump
-- version 4.5.4.1deb2ubuntu2.1
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Creato il: Giu 19, 2019 alle 16:10
-- Versione del server: 5.7.26-0ubuntu0.16.04.1
-- Versione PHP: 7.0.33-0ubuntu0.16.04.5

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `db`
--

-- --------------------------------------------------------

--
-- Struttura della tabella `reservations`
--

DROP TABLE IF EXISTS `reservations`;
CREATE TABLE `reservations` (
  `seat` varchar(8) NOT NULL,
  `status` varchar(16) NOT NULL,
  `user` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dump dei dati per la tabella `reservations`
--

INSERT INTO `reservations` (`seat`, `status`, `user`) VALUES
('2B', 'purchased', 'u2@p.it'),
('3B', 'purchased', 'u2@p.it'),
('4A', 'booked', 'u1@p.it'),
('4B', 'purchased', 'u2@p.it'),
('4D', 'booked', 'u1@p.it'),
('4F', 'booked', 'u2@p.it');

-- --------------------------------------------------------

--
-- Struttura della tabella `users`
--

DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dump dei dati per la tabella `users`
--

INSERT INTO `users` (`email`, `password`) VALUES
('u1@p.it', '$2y$10$Mln7IIo.W.C8BCoXUeGoI.VQL5Y3GosImmMDHhAWsVsnMlyktDs6G'),
('u2@p.it', '$2y$10$/qDDMUbGZVTHuNSg36L0ue1ZUMkD54EvmMYazscyf2/gf4Ijoi.XO');

--
-- Indici per le tabelle scaricate
--

--
-- Indici per le tabelle `reservations`
--
ALTER TABLE `reservations`
  ADD PRIMARY KEY (`seat`);

--
-- Indici per le tabelle `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`email`);

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
