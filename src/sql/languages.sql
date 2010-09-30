# --------------------------------------------------------
# Host:                         127.0.0.1
# Server version:               5.1.41
# Server OS:                    Win32
# HeidiSQL version:             5.1.0.3546
# Date/time:                    2010-09-30 02:55:53
# --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;

# Dumping structure for table enzyme.languages
CREATE TABLE IF NOT EXISTS `languages` (
  `code` varchar(15) COLLATE utf8_unicode_ci NOT NULL,
  `language` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  UNIQUE KEY `unique` (`code`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

# Dumping data for table enzyme.languages: 73 rows
/*!40000 ALTER TABLE `languages` DISABLE KEYS */;
INSERT INTO `languages` (`code`, `language`) VALUES
	('ar', 'Arabic'),
	('ast', 'Asturian'),
	('eu', 'Basque'),
	('be', 'Belarusian'),
	('bn', 'Bengali'),
	('bn_IN', 'Bengali'),
	('pt_BR', 'Brazilian Portuguese'),
	('en_GB', 'British English'),
	('bg', 'Bulgarian'),
	('ca', 'Catalan'),
	('ca@valencia', 'Catalan'),
	('zh_CN', 'Chinese Simplified'),
	('zh_TW', 'Chinese Traditional'),
	('hr', 'Croatian [Hrvatski]'),
	('cs', 'Czech'),
	('da', 'Danish'),
	('nl', 'Dutch'),
	('eo', 'Esperanto'),
	('et', 'Estonian'),
	('fi', 'Finnish'),
	('fr', 'French'),
	('fy', 'Frisian'),
	('gl', 'Galician'),
	('ka', 'Georgian'),
	('de', 'German'),
	('el', 'Greek'),
	('gu', 'Gujarati'),
	('he', 'Hebrew'),
	('hi', 'Hindi'),
	('hu', 'Hungarian'),
	('is', 'Icelandic'),
	('id', 'Indonesian'),
	('ia', 'Interlingua'),
	('ga', 'Irish'),
	('it', 'Italian'),
	('ja', 'Japanese'),
	('kn', 'Kannada'),
	('csb', 'Kashubian'),
	('kk', 'Kazakh'),
	('km', 'Khmer'),
	('ko', 'Korean'),
	('ku', 'Kurdish'),
	('lv', 'Latvian'),
	('lt', 'Lithuanian'),
	('nds', 'Low Saxon'),
	('mk', 'Macedonian'),
	('mai', 'Maithili'),
	('ms', 'Malay'),
	('ml', 'Malayalam | മലയാളം'),
	('mr', 'Marathi'),
	('se', 'Northern Sami'),
	('nb', 'Norwegian Bookmal'),
	('nn', 'Norwegian Nynorsk'),
	('or', 'Oriya'),
	('pl', 'Polish'),
	('pt', 'Portuguese'),
	('pa', 'Punjabi'),
	('ro', 'Romanian'),
	('ru', 'Russian'),
	('sr', 'Serbian'),
	('si', 'Sinhala'),
	('sk', 'Slovak'),
	('sl', 'Slovenian'),
	('es', 'Spanish'),
	('sv', 'Swedish'),
	('tg', 'Tajik'),
	('ta', 'Tamil'),
	('te', 'Telugu'),
	('th', 'Thai'),
	('tr', 'Turkish'),
	('uk', 'Ukrainian'),
	('uz@cyrillic', 'Uzbek'),
	('wa', 'Walloon');
/*!40000 ALTER TABLE `languages` ENABLE KEYS */;
/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
