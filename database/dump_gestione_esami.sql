-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Creato il: Giu 29, 2025 alle 08:47
-- Versione del server: 10.4.32-MariaDB
-- Versione PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `prova`
--

DELIMITER $$
--
-- Procedure
--
CREATE DEFINER=`root`@`localhost` PROCEDURE `aggiorna_prenotabilita_esami` ()   BEGIN
    UPDATE esami
    SET prenotabile = CASE
        WHEN CURDATE() >= inizioPrenotazione AND CURDATE() <= finePrenotazione THEN 1
        ELSE 0
    END;
END$$

DELIMITER ;

-- --------------------------------------------------------

--
-- Struttura della tabella `corsi`
--

CREATE TABLE `corsi` (
  `id` int(11) NOT NULL,
  `nome` varchar(255) NOT NULL,
  `cfu` tinyint(4) NOT NULL,
  `docente` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dump dei dati per la tabella `corsi`
--

INSERT INTO `corsi` (`id`, `nome`, `cfu`, `docente`) VALUES
(1, 'Ingegneria Meccanica', 12, 'D0007'),
(2, 'Fondamenti di Elettronica', 9, 'D0005'),
(3, 'Programmazione Avanzata', 6, 'D0001'),
(4, 'Matematica Applicata', 8, 'D0002'),
(5, 'Fisica Tecnica', 10, 'D0005'),
(6, 'Sistemi di Controllo', 7, 'D0006'),
(7, 'Robotica', 6, 'D0001'),
(8, 'Machine Learning', 9, 'D0010'),
(9, 'Automazione Industriale', 8, 'D0001'),
(10, 'Tecnologia dei Materiali', 5, 'D0003');

-- --------------------------------------------------------

--
-- Struttura della tabella `dettaglivalutazione`
--

CREATE TABLE `dettaglivalutazione` (
  `id` int(11) NOT NULL,
  `tipologia` enum('scritto','orale') NOT NULL,
  `descrizione` text NOT NULL,
  `valutazione` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dump dei dati per la tabella `dettaglivalutazione`
--

INSERT INTO `dettaglivalutazione` (`id`, `tipologia`, `descrizione`, `valutazione`) VALUES
(1, 'scritto', 'Logica a relé', 10),
(2, 'scritto', 'Framework Robot Operating System', 11);

-- --------------------------------------------------------

--
-- Struttura della tabella `dipartimenti`
--

CREATE TABLE `dipartimenti` (
  `id` int(11) NOT NULL,
  `nome` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dump dei dati per la tabella `dipartimenti`
--

INSERT INTO `dipartimenti` (`id`, `nome`) VALUES
(1, 'Architettura e Disegno Industriale'),
(2, 'Economia'),
(3, 'Giurisprudenza'),
(4, 'Ingegneria'),
(5, 'Lettere e Beni Culturali'),
(6, 'Matematica e Fisica'),
(7, 'Medicina di Precisione'),
(8, 'Medicina Sperimentale'),
(9, 'Psicologia'),
(10, 'Scienze Biologiche'),
(11, 'Scienze Mediche e Chirurgiche Avanzate'),
(12, 'Scienze Politiche');

-- --------------------------------------------------------

--
-- Struttura della tabella `docenti`
--

CREATE TABLE `docenti` (
  `matricola` varchar(20) NOT NULL,
  `nome` varchar(255) NOT NULL,
  `cognome` varchar(255) NOT NULL,
  `cf` varchar(16) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `indirizzo` varchar(255) DEFAULT NULL,
  `ruolo` int(11) NOT NULL,
  `dipartimento` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dump dei dati per la tabella `docenti`
--

INSERT INTO `docenti` (`matricola`, `nome`, `cognome`, `cf`, `email`, `password`, `indirizzo`, `ruolo`, `dipartimento`) VALUES
('D0001', 'Alessandro', 'Verdi', 'vrdals85a01h501p', 'accessoprova@university.it', '$2y$10$.M2cLKL0sbn0vaZl5vJ4c.lPChH7PMMw8nHPppc.kJ9Fe3mL5eF/q', 'Via Milano 10, Torino', 1, 4),
('D0002', 'Francesca', 'Rossi', 'rssfnc90b15h501j', 'francesca.rossi@university.it', '$2y$10$.M2cLKL0sbn0vaZl5vJ4c.lPChH7PMMw8nHPppc.kJ9Fe3mL5eF/q', 'Via Roma 25, Milano', 2, 2),
('D0003', 'Marco', 'Bianchi', 'bncmrc88c12h501k', 'marco.bianchi@university.it', '$2y$10$.M2cLKL0sbn0vaZl5vJ4c.lPChH7PMMw8nHPppc.kJ9Fe3mL5eF/q', 'Via Firenze 7, Bologna', 1, 1),
('D0004', 'Giulia', 'Neri', 'nrgjla92d03h501l', 'giulia.neri@university.it', '$2y$10$.M2cLKL0sbn0vaZl5vJ4c.lPChH7PMMw8nHPppc.kJ9Fe3mL5eF/q', 'Via Napoli 12, Roma', 3, 3),
('D0005', 'Luca', 'Ferrari', 'frrlca87e21h501m', 'luca.ferrari@university.it', '$2y$10$.M2cLKL0sbn0vaZl5vJ4c.lPChH7PMMw8nHPppc.kJ9Fe3mL5eF/q', 'Via Venezia 33, Venezia', 2, 1),
('D0006', 'Sara', 'Fontana', 'fntsra90f11h501n', 'sara.fontana@university.it', '$2y$10$.M2cLKL0sbn0vaZl5vJ4c.lPChH7PMMw8nHPppc.kJ9Fe3mL5eF/q', 'Via Genova 20, Genova', 3, 2),
('D0007', 'Davide', 'Costa', 'cstdve86g05h501p', 'davide.costa@university.it', '$2y$10$.M2cLKL0sbn0vaZl5vJ4c.lPChH7PMMw8nHPppc.kJ9Fe3mL5eF/q', 'Via Torino 15, Torino', 1, 3),
('D0008', 'Elena', 'Greco', 'grceln89h19h501q', 'elena.greco@university.it', '$2y$10$.M2cLKL0sbn0vaZl5vJ4c.lPChH7PMMw8nHPppc.kJ9Fe3mL5eF/q', 'Via Palermo 5, Palermo', 2, 1),
('D0009', 'Simone', 'Barbieri', 'brbsim84i23h501r', 'simone.barbieri@university.it', '$2y$10$.M2cLKL0sbn0vaZl5vJ4c.lPChH7PMMw8nHPppc.kJ9Fe3mL5eF/q', 'Via Bologna 18, Bologna', 3, 2),
('D0010', 'Martina', 'Romano', 'rmnmtn91j07h501s', 'martina.romano@university.it', '$2y$10$.M2cLKL0sbn0vaZl5vJ4c.lPChH7PMMw8nHPppc.kJ9Fe3mL5eF/q', 'Via Firenze 9, Firenze', 1, 3),
('D0011', 'Miguel', 'Lopez', 'MGLLPZ02A11D172D', 'miguel.lopez@university.it', '$2y$10$.M2cLKL0sbn0vaZl5vJ4c.lPChH7PMMw8nHPppc.kJ9Fe3mL5eF/q', 'Via Toledo, Napoli', 4, 1);

-- --------------------------------------------------------

--
-- Struttura della tabella `esami`
--

CREATE TABLE `esami` (
  `id` int(11) NOT NULL,
  `data` date NOT NULL,
  `ora` time NOT NULL,
  `tipologia` enum('scritto','orale','scritto/orale') NOT NULL,
  `sessione` enum('Straordinaria','Estiva','Invernale') NOT NULL,
  `luogo` varchar(255) DEFAULT NULL,
  `inizioPrenotazione` date NOT NULL,
  `finePrenotazione` date NOT NULL,
  `prenotabile` tinyint(1) NOT NULL,
  `completato` tinyint(1) NOT NULL,
  `corso` int(11) NOT NULL
) ;

--
-- Dump dei dati per la tabella `esami`
--

INSERT INTO `esami` (`id`, `data`, `ora`, `tipologia`, `sessione`, `luogo`, `inizioPrenotazione`, `finePrenotazione`, `prenotabile`, `completato`, `corso`) VALUES
(1, '2025-07-29', '08:00:00', 'orale', 'Invernale', 'Laboratorio 1', '2025-06-29', '2025-07-26', 1, 0, 3),
(2, '2025-07-30', '17:00:00', 'scritto/orale', 'Invernale', 'Aula Magna', '2025-06-30', '2025-07-27', 1, 0, 3),
(3, '2025-08-21', '08:00:00', 'scritto', 'Straordinaria', 'Aula Magna', '2025-07-22', '2025-08-18', 0, 1, 7),
(4, '2025-07-08', '15:00:00', 'orale', 'Invernale', 'Aula 202', '2025-06-08', '2025-07-05', 1, 0, 9),
(5, '2025-08-26', '13:00:00', 'scritto', 'Estiva', 'Laboratorio 2', '2025-07-27', '2025-08-23', 0, 1, 9),
(6, '2025-09-16', '15:00:00', 'scritto/orale', 'Estiva', 'Aula Magna', '2025-08-17', '2025-09-13', 1, 0, 6),
(7, '2025-06-29', '11:00:00', 'scritto/orale', 'Estiva', 'Aula Magna', '2025-05-30', '2025-06-26', 1, 0, 1),
(8, '2025-09-12', '15:00:00', 'orale', 'Estiva', 'Aula 202', '2025-08-13', '2025-09-09', 1, 0, 7),
(9, '2025-07-10', '16:00:00', 'orale', 'Invernale', 'Aula Magna', '2025-06-10', '2025-07-07', 1, 0, 4),
(10, '2025-07-28', '15:00:00', 'scritto', 'Straordinaria', 'Laboratorio 1', '2025-06-28', '2025-07-25', 1, 0, 5),
(11, '2025-08-10', '16:00:00', 'scritto', 'Invernale', 'Aula 101', '2025-07-11', '2025-08-07', 1, 0, 6),
(12, '2025-08-08', '17:00:00', 'scritto', 'Estiva', 'Laboratorio 2', '2025-07-09', '2025-08-05', 1, 0, 5),
(13, '2025-08-18', '16:00:00', 'scritto', 'Estiva', 'Aula Magna', '2025-07-19', '2025-08-15', 1, 0, 2),
(14, '2025-09-13', '16:00:00', 'orale', 'Invernale', 'Aula 202', '2025-08-14', '2025-09-10', 1, 0, 6),
(15, '2025-09-25', '09:00:00', 'scritto/orale', 'Straordinaria', 'Aula 101', '2025-08-26', '2025-09-22', 1, 0, 5),
(16, '2025-07-15', '14:00:00', 'scritto/orale', 'Estiva', 'Aula 21', '2025-06-27', '2025-07-12', 0, 0, 9);

-- --------------------------------------------------------

--
-- Struttura della tabella `indicatori`
--

CREATE TABLE `indicatori` (
  `id` int(11) NOT NULL,
  `descrizione` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dump dei dati per la tabella `indicatori`
--

INSERT INTO `indicatori` (`id`, `descrizione`) VALUES
(7, 'Applicazione pratica delle conoscenze'),
(4, 'Capacità di problem solving'),
(1, 'Completezza del lavoro svolto'),
(6, 'Comprensione dei contenuti teorici'),
(2, 'Originalità e creatività'),
(3, 'Qualità della documentazione prodotta'),
(5, 'Risultati ottenuti nei test');

-- --------------------------------------------------------

--
-- Struttura della tabella `notifiche`
--

CREATE TABLE `notifiche` (
  `id` int(11) NOT NULL,
  `titolo` varchar(255) NOT NULL,
  `descrizione` varchar(255) NOT NULL,
  `data_creazione` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dump dei dati per la tabella `notifiche`
--

INSERT INTO `notifiche` (`id`, `titolo`, `descrizione`, `data_creazione`) VALUES
(1, 'Nuovo orario lezioni', 'Dal prossimo semestre cambiano gli orari delle lezioni.', '2025-06-27 16:47:08'),
(2, 'Aggiornamento piattaforma online', 'La piattaforma di e-learning sarà offline per manutenzione il 15/07.', '2025-06-27 16:47:08'),
(3, 'Conferenza su Intelligenza Artificiale', 'Partecipa alla conferenza il 20 giugno, aula magna, ore 14:00.', '2025-06-27 16:47:08'),
(4, 'Sospensione attività didattiche', 'Le lezioni sono sospese il 2 giugno per festività nazionale.', '2025-06-27 16:47:08'),
(6, 'Primo accesso', 'Gentile prof. Miguel Lopez, benvenuto!', '2025-06-29 06:45:13');

-- --------------------------------------------------------

--
-- Struttura della tabella `prenotazioni`
--

CREATE TABLE `prenotazioni` (
  `studente` varchar(20) NOT NULL,
  `esame` int(11) NOT NULL,
  `data` date NOT NULL,
  `ora` time NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dump dei dati per la tabella `prenotazioni`
--

INSERT INTO `prenotazioni` (`studente`, `esame`, `data`, `ora`) VALUES
('A0002', 8, '2025-09-12', '15:00:00'),
('A0002', 9, '2025-07-10', '16:00:00'),
('A0002', 14, '2025-09-13', '16:00:00'),
('A0003', 2, '2025-07-30', '17:00:00'),
('A0003', 3, '2025-08-21', '08:00:00'),
('A0003', 8, '2025-09-12', '15:00:00'),
('A0004', 11, '2025-08-10', '16:00:00'),
('A0004', 12, '2025-08-08', '17:00:00'),
('A0005', 5, '2025-08-26', '13:00:00'),
('A0006', 11, '2025-08-10', '16:00:00'),
('A0006', 12, '2025-08-08', '17:00:00'),
('A0007', 4, '2025-07-08', '15:00:00'),
('A0007', 5, '2025-08-26', '13:00:00'),
('A0007', 8, '2025-09-12', '15:00:00'),
('A0007', 9, '2025-07-10', '16:00:00'),
('A0007', 14, '2025-09-13', '16:00:00'),
('A0008', 12, '2025-08-08', '17:00:00'),
('A0009', 4, '2025-07-08', '15:00:00'),
('A0009', 11, '2025-08-10', '16:00:00'),
('A0010', 5, '2025-08-26', '13:00:00');

-- --------------------------------------------------------

--
-- Struttura della tabella `ricezioni`
--

CREATE TABLE `ricezioni` (
  `docente` varchar(20) NOT NULL,
  `notifica` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dump dei dati per la tabella `ricezioni`
--

INSERT INTO `ricezioni` (`docente`, `notifica`) VALUES
('D0001', 2),
('D0001', 3),
('D0005', 4),
('D0006', 1),
('D0006', 4),
('D0007', 4),
('D0009', 1),
('D0010', 3),
('D0010', 4),
('D0011', 6);

-- --------------------------------------------------------

--
-- Struttura della tabella `ruoli`
--

CREATE TABLE `ruoli` (
  `id` int(11) NOT NULL,
  `nome_ruolo` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dump dei dati per la tabella `ruoli`
--

INSERT INTO `ruoli` (`id`, `nome_ruolo`) VALUES
(1, 'Professore Ordinario'),
(2, 'Professore Associato'),
(3, 'Ricercatore'),
(4, 'Professore Aggregato'),
(5, 'Tutor'),
(6, 'Coordinatore di Corso'),
(7, 'Direttore di Dipartimento');

-- --------------------------------------------------------

--
-- Struttura della tabella `studenti`
--

CREATE TABLE `studenti` (
  `matricola` varchar(20) NOT NULL,
  `nome` varchar(255) NOT NULL,
  `cognome` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dump dei dati per la tabella `studenti`
--

INSERT INTO `studenti` (`matricola`, `nome`, `cognome`, `email`) VALUES
('A0001', 'Alessandro', 'Bianchi', 'alessandro.bianchi@university.it'),
('A0002', 'Giulia', 'Rossi', 'giulia.rossi@university.it'),
('A0003', 'Luca', 'Verdi', 'luca.verdi@university.it'),
('A0004', 'Martina', 'Neri', 'martina.neri@university.it'),
('A0005', 'Francesco', 'Russo', 'francesco.russo@university.it'),
('A0006', 'Sara', 'Ferrari', 'sara.ferrari@university.it'),
('A0007', 'Davide', 'Galli', 'davide.galli@university.it'),
('A0008', 'Chiara', 'Conti', 'chiara.conti@university.it'),
('A0009', 'Matteo', 'Marini', 'matteo.marini@university.it'),
('A0010', 'Elena', 'Villa', 'elena.villa@university.it');

-- --------------------------------------------------------

--
-- Struttura della tabella `supporto`
--

CREATE TABLE `supporto` (
  `valutazione` int(11) NOT NULL,
  `indicatore` int(11) NOT NULL,
  `valore` tinyint(4) NOT NULL CHECK (`valore` >= 1 and `valore` <= 10)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dump dei dati per la tabella `supporto`
--

INSERT INTO `supporto` (`valutazione`, `indicatore`, `valore`) VALUES
(8, 3, 10),
(10, 7, 2),
(11, 2, 7),
(11, 4, 5);

-- --------------------------------------------------------

--
-- Struttura della tabella `valutazioni`
--

CREATE TABLE `valutazioni` (
  `id` int(11) NOT NULL,
  `data` date NOT NULL,
  `voto` tinyint(4) NOT NULL CHECK (`voto` = 0 or `voto` >= 18 and `voto` <= 30),
  `lode` tinyint(1) NOT NULL CHECK (`lode` = 0 or `lode` = 1 and `voto` = 30),
  `stato` enum('Presente','Assente') NOT NULL CHECK (`stato` = 'presente' or `stato` = 'assente'),
  `ammesso` tinyint(1) NOT NULL,
  `esame` int(11) NOT NULL,
  `studente` varchar(20) NOT NULL
) ;

--
-- Dump dei dati per la tabella `valutazioni`
--

INSERT INTO `valutazioni` (`id`, `data`, `voto`, `lode`, `stato`, `ammesso`, `esame`, `studente`) VALUES
(8, '2025-06-27', 30, 1, 'Presente', 1, 3, 'A0003'),
(9, '2025-06-27', 0, 0, 'Assente', 0, 5, 'A0007'),
(10, '2025-06-27', 0, 0, 'Presente', 0, 5, 'A0005'),
(11, '2025-06-27', 27, 0, 'Presente', 1, 5, 'A0010');

--
-- Indici per le tabelle scaricate
--

--
-- Indici per le tabelle `corsi`
--
ALTER TABLE `corsi`
  ADD PRIMARY KEY (`id`),
  ADD KEY `docente` (`docente`);

--
-- Indici per le tabelle `dettaglivalutazione`
--
ALTER TABLE `dettaglivalutazione`
  ADD PRIMARY KEY (`id`),
  ADD KEY `valutazione` (`valutazione`);

--
-- Indici per le tabelle `dipartimenti`
--
ALTER TABLE `dipartimenti`
  ADD PRIMARY KEY (`id`);

--
-- Indici per le tabelle `docenti`
--
ALTER TABLE `docenti`
  ADD PRIMARY KEY (`matricola`),
  ADD UNIQUE KEY `cf` (`cf`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `ruolo` (`ruolo`),
  ADD KEY `dipartimento` (`dipartimento`);

--
-- Indici per le tabelle `esami`
--
ALTER TABLE `esami`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `corso` (`corso`,`data`,`ora`);

--
-- Indici per le tabelle `indicatori`
--
ALTER TABLE `indicatori`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `descrizione` (`descrizione`);

--
-- Indici per le tabelle `notifiche`
--
ALTER TABLE `notifiche`
  ADD PRIMARY KEY (`id`);

--
-- Indici per le tabelle `prenotazioni`
--
ALTER TABLE `prenotazioni`
  ADD PRIMARY KEY (`studente`,`esame`),
  ADD KEY `esame` (`esame`);

--
-- Indici per le tabelle `ricezioni`
--
ALTER TABLE `ricezioni`
  ADD PRIMARY KEY (`docente`,`notifica`),
  ADD KEY `notifica` (`notifica`);

--
-- Indici per le tabelle `ruoli`
--
ALTER TABLE `ruoli`
  ADD PRIMARY KEY (`id`);

--
-- Indici per le tabelle `studenti`
--
ALTER TABLE `studenti`
  ADD PRIMARY KEY (`matricola`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indici per le tabelle `supporto`
--
ALTER TABLE `supporto`
  ADD PRIMARY KEY (`valutazione`,`indicatore`),
  ADD KEY `indicatore` (`indicatore`);

--
-- Indici per le tabelle `valutazioni`
--
ALTER TABLE `valutazioni`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `valutazione_unica` (`esame`,`studente`),
  ADD KEY `studente` (`studente`);

--
-- AUTO_INCREMENT per le tabelle scaricate
--

--
-- AUTO_INCREMENT per la tabella `corsi`
--
ALTER TABLE `corsi`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT per la tabella `dettaglivalutazione`
--
ALTER TABLE `dettaglivalutazione`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT per la tabella `dipartimenti`
--
ALTER TABLE `dipartimenti`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT per la tabella `esami`
--
ALTER TABLE `esami`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT per la tabella `indicatori`
--
ALTER TABLE `indicatori`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT per la tabella `notifiche`
--
ALTER TABLE `notifiche`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT per la tabella `ruoli`
--
ALTER TABLE `ruoli`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT per la tabella `valutazioni`
--
ALTER TABLE `valutazioni`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Limiti per le tabelle scaricate
--

--
-- Limiti per la tabella `corsi`
--
ALTER TABLE `corsi`
  ADD CONSTRAINT `corsi_ibfk_1` FOREIGN KEY (`docente`) REFERENCES `docenti` (`matricola`);

--
-- Limiti per la tabella `dettaglivalutazione`
--
ALTER TABLE `dettaglivalutazione`
  ADD CONSTRAINT `dettaglivalutazione_ibfk_1` FOREIGN KEY (`valutazione`) REFERENCES `valutazioni` (`id`);

--
-- Limiti per la tabella `docenti`
--
ALTER TABLE `docenti`
  ADD CONSTRAINT `docenti_ibfk_1` FOREIGN KEY (`ruolo`) REFERENCES `ruoli` (`id`),
  ADD CONSTRAINT `docenti_ibfk_2` FOREIGN KEY (`dipartimento`) REFERENCES `dipartimenti` (`id`);

--
-- Limiti per la tabella `esami`
--
ALTER TABLE `esami`
  ADD CONSTRAINT `esami_ibfk_1` FOREIGN KEY (`corso`) REFERENCES `corsi` (`id`);

--
-- Limiti per la tabella `prenotazioni`
--
ALTER TABLE `prenotazioni`
  ADD CONSTRAINT `prenotazioni_ibfk_1` FOREIGN KEY (`esame`) REFERENCES `esami` (`id`),
  ADD CONSTRAINT `prenotazioni_ibfk_2` FOREIGN KEY (`studente`) REFERENCES `studenti` (`matricola`);

--
-- Limiti per la tabella `ricezioni`
--
ALTER TABLE `ricezioni`
  ADD CONSTRAINT `ricezioni_ibfk_1` FOREIGN KEY (`notifica`) REFERENCES `notifiche` (`id`),
  ADD CONSTRAINT `ricezioni_ibfk_2` FOREIGN KEY (`docente`) REFERENCES `docenti` (`matricola`);

--
-- Limiti per la tabella `supporto`
--
ALTER TABLE `supporto`
  ADD CONSTRAINT `supporto_ibfk_1` FOREIGN KEY (`valutazione`) REFERENCES `valutazioni` (`id`),
  ADD CONSTRAINT `supporto_ibfk_2` FOREIGN KEY (`indicatore`) REFERENCES `indicatori` (`id`);

--
-- Limiti per la tabella `valutazioni`
--
ALTER TABLE `valutazioni`
  ADD CONSTRAINT `valutazioni_ibfk_1` FOREIGN KEY (`esame`) REFERENCES `esami` (`id`),
  ADD CONSTRAINT `valutazioni_ibfk_2` FOREIGN KEY (`studente`) REFERENCES `studenti` (`matricola`);

DELIMITER $$
--
-- Eventi
--
CREATE DEFINER=`root`@`localhost` EVENT `aggiorna_prenotabilita_esami` ON SCHEDULE EVERY 1 DAY STARTS '2025-06-27 00:00:00' ON COMPLETION NOT PRESERVE ENABLE DO UPDATE esami
  SET prenotabile = CASE
    WHEN CURDATE() >= inizioPrenotazione AND CURDATE() <= finePrenotazione THEN 1
    ELSE 0
  END$$

DELIMITER ;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
