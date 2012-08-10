-- phpMyAdmin SQL Dump
-- version 3.2.4
-- http://www.phpmyadmin.net
--
-- Host: devdb.uni-oldenburg.de
-- Erstellungszeit: 07. Dezember 2010 um 14:06
-- Server Version: 5.0.77
-- PHP-Version: 5.3.1



-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `stg_ansprechpartner`
--

DROP TABLE IF EXISTS `stg_ansprechpartner`;
CREATE TABLE `stg_ansprechpartner` (
  `ansprechpartner_id` int(10) unsigned NOT NULL auto_increment,
  `range_id` char(32) NOT NULL,
  `ansprechpartner_typ_id` int(11) default NULL,
  `range_typ` varchar(45) default NULL,
  `freitext_name` varchar(200) default NULL,
  `freitext_mail` varchar(100) default NULL,
  `freitext_telefon` varchar(100) default NULL,
  `freitext_homepage` varchar(200) default NULL,
  PRIMARY KEY  (`ansprechpartner_id`),
  KEY `fk_stg_ansprechpartner_stg_ansprechpartner_typ1` (`range_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=10 ;


--
-- Tabellenstruktur für Tabelle `stg_ansprechpartner_typ`
--

DROP TABLE IF EXISTS `stg_ansprechpartner_typ`;
CREATE TABLE `stg_ansprechpartner_typ` (
  `ansprechpartner_typ_id` int(11) NOT NULL auto_increment,
  `name` varchar(200) default NULL,
  `stg_bereichs_id` int(11) default NULL,
  PRIMARY KEY  (`ansprechpartner_typ_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=17 ;

--
-- Daten für Tabelle `stg_ansprechpartner_typ`
--

INSERT INTO `stg_ansprechpartner_typ` (`ansprechpartner_typ_id`, `name`, `stg_bereichs_id`) VALUES
(1, 'Prüfungsamt Sachbearbeiter/in', 1),
(7, 'Fachstudienberatung', 1),
(6, 'Praktikumbeauftragte', 1),
(11, 'Personen', 2),
(12, 'Vorsitz im Prüfungsausschus', 1),
(14, 'Einrichtungen', 1),
(13, 'Fachschaften', 1),
(15, 'Personen', 1),
(16, 'Praktikumsbeauftragte', 1);

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `stg_ansprech_zuord`
--

DROP TABLE IF EXISTS `stg_ansprech_zuord`;
CREATE TABLE `stg_ansprech_zuord` (
  `ansprech_zuord_id` int(11) NOT NULL auto_increment,
  `stg_ansprechpartner_id` int(11) NOT NULL,
  `stg_profil_id` int(11) NOT NULL,
  `position` smallint(6) NOT NULL,
  PRIMARY KEY  (`ansprech_zuord_id`),
  UNIQUE KEY `stg_ansprech_zuord_unique` (`stg_ansprechpartner_id`,`stg_profil_id`),
  KEY `fk_stg_ansprech_zuord_stg_ansprechpartner1` (`stg_ansprechpartner_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=6 ;


--
-- Tabellenstruktur für Tabelle `stg_aufbaustudiengang`
--

DROP TABLE IF EXISTS `stg_aufbaustudiengang`;
CREATE TABLE `stg_aufbaustudiengang` (
  `stg_range_id` int(11) NOT NULL,
  `aufbau_stg_profil_id` int(11) NOT NULL,
  `range_typ` varchar(45) default NULL,
  PRIMARY KEY  (`stg_range_id`,`aufbau_stg_profil_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Daten für Tabelle `stg_aufbaustudiengang`
--


-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `stg_bereiche`
--

DROP TABLE IF EXISTS `stg_bereiche`;
CREATE TABLE `stg_bereiche` (
  `bereichs_id` int(11) NOT NULL auto_increment,
  `bereich_name` varchar(200) default NULL,
  `sichtbar_fsb` tinyint(1) default NULL,
  `sichtbar_pamt` tinyint(1) default NULL,
  `sichtbar_iamt` tinyint(1) default NULL,
  PRIMARY KEY  (`bereichs_id`),
  KEY `fk_stg_bereiche_stg_ansprechpartner_typ1` (`bereichs_id`),
  KEY `fk_stg_bereiche_stg_dokument_typ1` (`bereichs_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=3 ;

--
-- Daten für Tabelle `stg_bereiche`
--

INSERT INTO `stg_bereiche` (`bereichs_id`, `bereich_name`, `sichtbar_fsb`, `sichtbar_pamt`, `sichtbar_iamt`) VALUES
(1, 'Prüfungsamt', 0, 1, 0),
(2, 'Immatrikulationsamt', 0, 0, 1);

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `stg_bewerben`
--

DROP TABLE IF EXISTS `stg_bewerben`;
CREATE TABLE `stg_bewerben` (
  `stg_profil_id` int(11) NOT NULL,
  `zielgruppen_id` int(11) NOT NULL,
  `startzeit_wise` timestamp NULL default NULL,
  `endzeit_wise` timestamp NULL default NULL,
  `startzeit_sose` timestamp NULL default NULL,
  `endzeit_sose` timestamp NULL default NULL,
  `begin_wise` tinyint(1) default NULL,
  `begin_sose` tinyint(1) default NULL,
  PRIMARY KEY  (`stg_profil_id`,`zielgruppen_id`),
  KEY `fk_stg_bewerben_stg_zielgruppen1` (`zielgruppen_id`),
  KEY `fk_stg_bewerben_stg_info1` (`stg_profil_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;



--
-- Tabellenstruktur für Tabelle `stg_dokumente`
--

DROP TABLE IF EXISTS `stg_dokumente`;
CREATE TABLE `stg_dokumente` (
  `doku_id` int(11) NOT NULL auto_increment,
  `user_id` char(32) default NULL,
  `name` varchar(400) default NULL,
  `quick_link` varchar(100) default NULL,
  `filename` varchar(400) default NULL,
  `mkdate` timestamp NULL default NULL,
  `chdate` timestamp NULL default NULL,
  `filesize` int(20) default NULL,
  `doku_typ_id` int(11) default NULL,
  `sichtbar` tinyint(1) default NULL,
  `jahr` smallint(4) default NULL,
  `version` varchar(128) default NULL,
  PRIMARY KEY  (`doku_id`),
  KEY `fk_stg_dokumente_stg_dokument_typ1` (`doku_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=16 ;


--
-- Tabellenstruktur für Tabelle `stg_dokument_tags`
--

DROP TABLE IF EXISTS `stg_dokument_tags`;
CREATE TABLE `stg_dokument_tags` (
  `doku_id` int(11) NOT NULL,
  `tag` varchar(64) NOT NULL,
  KEY `Index_doku` (`doku_id`),
  KEY `Index_tags` (`tag`),
  KEY `fk_stg_dokument_tags_stg_dokumente1` (`doku_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;


--
-- Tabellenstruktur für Tabelle `stg_dokument_typ`
--

DROP TABLE IF EXISTS `stg_dokument_typ`;
CREATE TABLE `stg_dokument_typ` (
  `doku_typ_id` int(11) NOT NULL auto_increment,
  `name` varchar(200) default NULL,
  PRIMARY KEY  (`doku_typ_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=19 ;

--
-- Daten für Tabelle `stg_dokument_typ`
--

INSERT INTO `stg_dokument_typ` (`doku_typ_id`, `name`) VALUES
(1, 'Zugangsordnungen'),
(2, 'Prüfungsordnungen'),
(3, 'Ordnungen (allgemein)'),
(4, 'Kurzinformationen'),
(5, 'Dateien (allgemein)'),
(6, 'Vordrucke und Formulare'),
(7, 'Infobroschüren'),
(8, 'Praktika'),
(9, 'Klausuren und Prüende'),
(10, 'Fachflyer'),
(11, 'Flyer'),
(12, 'Verlaufspläne'),
(13, 'Formulare'),
(14, 'I-Amt-spezifische Dokumente'),
(15, 'ZSB-spezifische Dokumente'),
(16, 'P-Amt spezifische Dokumente'),
(17, 'Testdokutyp'),
(18, 'Internetlink');

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `stg_doku_typ_bereich_zuord`
--

DROP TABLE IF EXISTS `stg_doku_typ_bereich_zuord`;
CREATE TABLE `stg_doku_typ_bereich_zuord` (
  `stg_doku_typ_id` int(11) NOT NULL,
  `stg_bereichs_id` int(11) NOT NULL,
  PRIMARY KEY  (`stg_doku_typ_id`,`stg_bereichs_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Daten für Tabelle `stg_doku_typ_bereich_zuord`
--

INSERT INTO `stg_doku_typ_bereich_zuord` (`stg_doku_typ_id`, `stg_bereichs_id`) VALUES
(1, 1),
(2, 1),
(3, 1),
(3, 2),
(4, 1),
(5, 1),
(5, 2),
(6, 1),
(7, 1),
(7, 2),
(8, 1),
(9, 1),
(10, 1),
(11, 1),
(12, 1),
(13, 1),
(13, 2),
(14, 1),
(14, 2),
(15, 1),
(16, 1);

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `stg_doku_zuord`
--

DROP TABLE IF EXISTS `stg_doku_zuord`;
CREATE TABLE `stg_doku_zuord` (
  `doku_zuord_id` int(11) NOT NULL auto_increment,
  `doku_id` int(11) NOT NULL,
  `stg_profil_id` int(11) NOT NULL,
  `position` smallint(6) default NULL,
  PRIMARY KEY  (`doku_zuord_id`),
  KEY `fk_stg_dokument_zuordnung_stg_dokumente1` (`doku_id`),
  KEY `fk_stg_dokument_zuordnung_stg_profil1` (`stg_profil_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=18 ;


--
-- Tabellenstruktur für Tabelle `stg_fach_kombination`
--

DROP TABLE IF EXISTS `stg_fach_kombination`;
CREATE TABLE `stg_fach_kombination` (
  `fach_kombi_id` int(11) NOT NULL,
  `stg_profil_id` int(11) NOT NULL,
  `kombi_stg_profil_id` int(11) NOT NULL,
  `beschreibung` text,
  PRIMARY KEY  (`stg_profil_id`,`kombi_stg_profil_id`,`fach_kombi_id`),
  KEY `fk_stg_fach_kombination_stg_info1` (`stg_profil_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;



--
-- Tabellenstruktur für Tabelle `stg_fsb_rollen`
--

DROP TABLE IF EXISTS `stg_fsb_rollen`;
CREATE TABLE `stg_fsb_rollen` (
  `user_id` char(32) NOT NULL,
  `studiengang_id` char(32) NOT NULL,
  `lehreinheit_id` char(32) default NULL,
  `rollen_typ` enum('FSB','StuKo') default NULL,
  PRIMARY KEY  (`user_id`,`studiengang_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;


--
-- Tabellenstruktur für Tabelle `stg_profil`
--

DROP TABLE IF EXISTS `stg_profil`;
CREATE TABLE `stg_profil` (
  `profil_id` int(11) NOT NULL auto_increment,
  `fach_id` char(32) default NULL,
  `abschluss_id` char(32) default NULL,
  `sichtbar` tinyint(1) default NULL,
  `studiendauer` tinyint(4) default NULL,
  `studienplaetze` smallint(6) default NULL,
  `zulassungsvoraussetzung` enum('ja','nein','voraussichtlich ja','voraussichtlich nein') default NULL,
  `ausland` text,
  PRIMARY KEY  (`profil_id`),
  KEY `fk_stg_info_stg_aufbaustudiengang1` (`profil_id`),
  KEY `fk_stg_info_studiengaenge1` (`fach_id`),
  KEY `fk_stg_info_abschluss1` (`abschluss_id`),
  KEY `fk_stg_profil_stg_profil_information1` (`profil_id`),
  KEY `fk_stg_profil_stg_ansprech_zuord1` (`profil_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=5 ;


--
-- Tabellenstruktur für Tabelle `stg_profil_information`
--

DROP TABLE IF EXISTS `stg_profil_information`;
CREATE TABLE `stg_profil_information` (
  `information_id` int(11) NOT NULL auto_increment,
  `stg_profil_id` int(11) NOT NULL,
  `info_form` enum('kurz','lang') default NULL,
  `sprache` enum('deutsch','englisch') default NULL,
  `einleitung` text,
  `profil` text,
  `inhalte` text,
  `lernformen` text,
  `gruende` text,
  `berufsfelder` text,
  `weitere_infos` text,
  `aktuelles` text,
  `einschreibungsverfahren` text,
  `bewerbungsverfahren` text,
  `besonderezugangsvoraussetzungen` text,
  `schwerpunkte` text,
  `sprachkenntnisse` varchar(45) default NULL,
  `sichtbar` tinyint(1) default NULL,
  `vollstaendig` tinyint(1) default NULL,
  PRIMARY KEY  (`information_id`),
  KEY `INFO_FORM` (`information_id`,`info_form`),
  KEY `STG_PROFIL_ID` (`stg_profil_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=17 ;


--
-- Tabellenstruktur für Tabelle `stg_typ`
--

DROP TABLE IF EXISTS `stg_typ`;
CREATE TABLE `stg_typ` (
  `stg_typ_id` int(11) NOT NULL auto_increment,
  `typ_name` varchar(100) default NULL COMMENT 'Typen: FB, ZWB, Weiterbildener, OnlineStg, Kostenpflichtig',
  PRIMARY KEY  (`stg_typ_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=7 ;

--
-- Daten für Tabelle `stg_typ`
--

INSERT INTO `stg_typ` (`stg_typ_id`, `typ_name`) VALUES
(1, 'grundständige Studiengänge'),
(2, 'weiterführende Studiengänge'),
(3, 'auslaufende Studiengänge'),
(4, 'weiterbildende/berufsbegleitende Studiengänge'),
(5, 'Online-Studiengänge'),
(6, 'kostenpflichtige Studiengänge');

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `stg_typ_zuordnung`
--

DROP TABLE IF EXISTS `stg_typ_zuordnung`;
CREATE TABLE `stg_typ_zuordnung` (
  `stg_typ_id` int(11) NOT NULL,
  `stg_profil_id` int(11) NOT NULL,
  PRIMARY KEY  (`stg_profil_id`,`stg_typ_id`),
  KEY `fk_stg_typ_zuordnung_stg_info1` (`stg_profil_id`),
  KEY `fk_stg_typ_zuordnung_stg_typ1` (`stg_typ_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;


--
-- Tabellenstruktur für Tabelle `stg_verlaufsplan`
--

DROP TABLE IF EXISTS `stg_verlaufsplan`;
CREATE TABLE `stg_verlaufsplan` (
  `verlaufsplan_id` int(11) NOT NULL auto_increment,
  `stg_profil_id` int(11) NOT NULL,
  `version` tinyint(4) NOT NULL,
  `titel` varchar(255) NOT NULL,
  `untertitel` varchar(255) default NULL,
  `notiz` text,
  `fach_kombi_id` int(11) default NULL,
  `sichtbar_fach1` tinyint(1) default NULL,
  `sichtbar_fach2` tinyint(1) default NULL,
  `user_id` char(32) default NULL,
  PRIMARY KEY  (`verlaufsplan_id`),
  KEY `fk_stg_verlaufsplan_stg_profil1` (`stg_profil_id`),
  KEY `fk_stg_verlaufsplan_stg_verlaufsplan_eintraege1` (`verlaufsplan_id`),
  KEY `fk_stg_verlaufsplan_stg_fach_kombination1` (`fach_kombi_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;


--
-- Tabellenstruktur für Tabelle `stg_verlaufsplan_eintraege`
--

DROP TABLE IF EXISTS `stg_verlaufsplan_eintraege`;
CREATE TABLE `stg_verlaufsplan_eintraege` (
  `stg_verlaufsplan_id` int(11) NOT NULL,
  `fachsem` tinyint(4) NOT NULL,
  `position` tinyint(4) NOT NULL,
  `position_hoehe` tinyint(4) default NULL,
  `sem_tree_id` char(32) default NULL,
  `verlauf_typ_id` int(11) default NULL,
  `modul_notiz` text,
  PRIMARY KEY  USING BTREE (`stg_verlaufsplan_id`,`fachsem`,`position`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;


--
-- Tabellenstruktur für Tabelle `stg_verlauf_typ`
--

DROP TABLE IF EXISTS `stg_verlauf_typ`;
CREATE TABLE `stg_verlauf_typ` (
  `verlauf_typ_id` int(11) NOT NULL auto_increment,
  `farbcode` varchar(16) default NULL,
  `typ_name` varchar(200) default NULL,
  PRIMARY KEY  (`verlauf_typ_id`),
  KEY `fk_stg_verlauf_typ_stg_verlaufsplan1` (`verlauf_typ_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;


--
-- Tabellenstruktur für Tabelle `stg_zielgruppen`
--

DROP TABLE IF EXISTS `stg_zielgruppen`;
CREATE TABLE `stg_zielgruppen` (
  `zielgruppen_id` int(11) NOT NULL auto_increment,
  `name` varchar(100) default NULL,
  PRIMARY KEY  (`zielgruppen_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=5 ;

--
-- Daten für Tabelle `stg_zielgruppen`
--

INSERT INTO `stg_zielgruppen` (`zielgruppen_id`, `name`) VALUES
(4, 'Zielgruppe 4 Bewerbung ins here Semester, internat. HSZ'),
(3, 'Zielgruppe 3 Bewerbung ins erste Semester, internat. HSZ'),
(1, 'Zielgruppe 1 Bewerbung ins erste Semester, dt. HSZ'),
(2, 'Zielgruppe 2 Bewerbung ins h ?here Semester, dt. HSZ');


