# --------------------------------------------------------
# Host:                         127.0.0.1
# Server version:               5.1.41
# Server OS:                    Win32
# HeidiSQL version:             5.1.0.3546
# Date/time:                    2010-09-30 02:55:52
# --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;

# Dumping structure for table enzyme.countries
CREATE TABLE IF NOT EXISTS `countries` (
  `code` varchar(7) COLLATE utf8_unicode_ci NOT NULL,
  `name` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `continent` varchar(13) COLLATE utf8_unicode_ci NOT NULL,
  UNIQUE KEY `unique` (`code`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

# Dumping data for table enzyme.countries: 77 rows
/*!40000 ALTER TABLE `countries` DISABLE KEYS */;
INSERT INTO `countries` (`code`, `name`, `continent`) VALUES
	('at', 'Austria', 'europe'),
	('ba', 'Bosnia and Herzegovina', 'europe'),
	('be', 'Belgium', 'europe'),
	('bg', 'Bulgaria', 'europe'),
	('by', 'Belarus', 'europe'),
	('ch', 'Switzerland', 'europe'),
	('cs', 'Serbia and Montenegro', 'europe'),
	('cy', 'Cyprus', 'europe'),
	('cz', 'Czech Republic', 'europe'),
	('de', 'Germany', 'europe'),
	('dk', 'Denmark', 'europe'),
	('ee', 'Estonia', 'europe'),
	('es', 'Spain', 'europe'),
	('fi', 'Finland', 'europe'),
	('fr', 'France', 'europe'),
	('gr', 'Greece', 'europe'),
	('hu', 'Hungary', 'europe'),
	('ie', 'Ireland', 'europe'),
	('il', 'Israel', 'europe'),
	('is', 'Iceland', 'europe'),
	('it', 'Italy', 'europe'),
	('lt', 'Latvia', 'europe'),
	('lu', 'Luxembourg', 'europe'),
	('md', 'Moldova', 'europe'),
	('mk', 'Macedonia', 'europe'),
	('mt', 'Malta', 'europe'),
	('nl', 'Netherlands', 'europe'),
	('no', 'Norway', 'europe'),
	('pl', 'Poland', 'europe'),
	('rs', 'Serbia', 'europe'),
	('pt', 'Portugal', 'europe'),
	('ro', 'Romania', 'europe'),
	('ru', 'Russia', 'europe'),
	('se', 'Sweden', 'europe'),
	('si', 'Slovenia', 'europe'),
	('sk', 'Slovak Republic', 'europe'),
	('tr', 'Turkey', 'europe'),
	('ua', 'Ukraine', 'europe'),
	('uk', 'United Kingdom', 'europe'),
	('yu', 'Yugoslavia', 'europe'),
	('za', 'South Africa', 'africa'),
	('ma', 'Morocco', 'africa'),
	('ca', 'Canada', 'north-america'),
	('mx', 'Mexico', 'north-america'),
	('us', 'United States', 'north-america'),
	('ar', 'Argentina', 'south-america'),
	('br', 'Brazil', 'south-america'),
	('cl', 'Chile', 'south-america'),
	('co', 'Colombia', 'south-america'),
	('cu', 'Cuba', 'south-america'),
	('af', 'Afganistan', 'asia'),
	('az', 'Azerbaijan', 'asia'),
	('bd', 'Bangladesh', 'asia'),
	('cn', 'China', 'asia'),
	('in', 'India', 'asia'),
	('jp', 'Japan', 'asia'),
	('ge', 'Georgia', 'asia'),
	('kz', 'Kazakhstan', 'asia'),
	('tw', 'Taiwan', 'asia'),
	('th', 'Thailand', 'asia'),
	('kh', 'Cambodia', 'asia'),
	('tj', 'Tajikistan', 'asia'),
	('kw', 'Kuwait', 'asia'),
	('ir', 'Iran', 'asia'),
	('my', 'Malaysia', 'asia'),
	('np', 'Nepal', 'asia'),
	('kr', 'South Korea', 'asia'),
	('lk', 'Sri Lanka', 'asia'),
	('ph', 'Philippines', 'asia'),
	('tm', 'Turkmenistan', 'asia'),
	('vn', 'Vietnam', 'asia'),
	('au', 'Australia', 'oceania'),
	('nz', 'New Zealand', 'oceania'),
	('unknown', '(unknown)', ''),
	('other', '(other)', ''),
	('lv', 'Latvia', 'europe'),
	('pe', 'Peru', 'south-america');
/*!40000 ALTER TABLE `countries` ENABLE KEYS */;
/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
