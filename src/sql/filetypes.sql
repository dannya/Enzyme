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

# Dumping structure for table enzyme.filetypes
CREATE TABLE IF NOT EXISTS `filetypes` (
  `extension` varchar(10) COLLATE utf8_unicode_ci NOT NULL,
  `name` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `type` varchar(30) COLLATE utf8_unicode_ci NOT NULL,
  UNIQUE KEY `unique` (`extension`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

# Dumping data for table enzyme.filetypes: 85 rows
/*!40000 ALTER TABLE `filetypes` DISABLE KEYS */;
INSERT INTO `filetypes` (`extension`, `name`, `type`) VALUES
	('po', 'GNU Gettext Message Catalogue', 'translation'),
	('pot', 'GNU Gettext Message Catalogue', 'translation'),
	('i18n', '', 'translation'),
	('svg', 'Scalable Vector Graphics', 'artwork'),
	('svgz', 'Scalable Vector Graphics (Compressed)', 'artwork'),
	('png', 'Portable Network Graphics', 'artwork'),
	('jpg', 'Joint Photography Experts Group', 'artwork'),
	('jpeg', 'Joint Photography Experts Group', 'artwork'),
	('gif', 'Graphics Interchange Format', 'artwork'),
	('bmp', 'Bitmap', 'artwork'),
	('ico', 'Icon', 'artwork'),
	('mng', 'Multiple-image Network Graphics', 'artwork'),
	('xpm', 'X PixMap Image', 'artwork'),
	('ogg', 'Ogg Vorbis', 'multimedia'),
	('mp3', 'MPEG Audio Layer 3', 'multimedia'),
	('wav', 'Waveform', 'multimedia'),
	('flac', 'Free Lossless Audio Codec', 'multimedia'),
	('avi', '', 'multimedia'),
	('mpeg', 'MPEG Video', 'multimedia'),
	('ogm', 'Ogg Multimedia', 'multimedia'),
	('smil', '', 'multimedia'),
	('docbook', 'DocBook', 'documentation'),
	('dox', 'API Documentation', 'documentation'),
	('odp', '', 'documents'),
	('ods', '', 'documents'),
	('odt', '', 'documents'),
	('kvtml', '', 'documents'),
	('kwd', '', 'documents'),
	('gcc', '', 'development'),
	('m4', '', 'development'),
	('moc', '', 'development'),
	('patch', '', 'development'),
	('qt', '', 'development'),
	('cmake', '', 'development'),
	('doxy', '', 'development'),
	('doxygen', '', 'development'),
	('kcfg', '', 'development'),
	('krazy', '', 'development'),
	('ui', 'User Interface', 'interface'),
	('ui4', 'User Interface (Qt 4)', 'interface'),
	('cpp', 'C++ Source', 'code'),
	('cxx', 'C++ Source', 'code'),
	('cc', 'C++ Source', 'code'),
	('h', 'C Headers', 'code'),
	('py', 'Python', 'code'),
	('rb', 'Ruby', 'code'),
	('pl', 'Perl', 'code'),
	('sh', 'Shell Script', 'code'),
	('jar', '', 'code'),
	('java', '', 'code'),
	('js', 'Javascript', 'code'),
	('ruby', 'Ruby', 'code'),
	('bz2', '', 'packages'),
	('tar', '', 'packages'),
	('tgz', '', 'packages'),
	('zip', '', 'packages'),
	('txt', 'Plain Text', 'other'),
	('pdf', 'Portable Document Format', 'other'),
	('sgml', 'Standard Generalised Markup Language', 'other'),
	('html', 'HyperText Markup Language', 'other'),
	('rdf', 'Resource Description Framework', 'other'),
	('am', 'Automake Makefile', 'other'),
	('desktop', 'Desktop Config File', 'other'),
	('inc', 'HTML Include File', 'other'),
	('odg', 'OASIS OpenDocument Graphics', 'other'),
	('php', 'PHP Script', 'other'),
	('xml', 'Extensible Markup Language', 'other'),
	('pc', 'Package Configuration', 'other'),
	('in', 'Input', 'other'),
	('rc', 'C++ Resource Compiler Script', 'other'),
	('dict', 'Dictionary', 'other'),
	('theme', 'Theme Configuration', 'other'),
	('diff', 'Differences Between Files', 'other'),
	('xsl', 'XSLT Style Sheet', 'other'),
	('ics', 'iCalendar File', 'other'),
	('css', 'Cascading Style Sheet', 'other'),
	('dtd', 'Document Type Declaration', 'other'),
	('readme', '', 'other'),
	('rss', 'Really Simple Syndication', 'other'),
	('dvi', '', 'other'),
	('eps', '', 'other'),
	('exe', '', 'other'),
	('ttf', '', 'other'),
	('xcf', '', 'other'),
	('flac####Fr', '', 'multimedia');
/*!40000 ALTER TABLE `filetypes` ENABLE KEYS */;
/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
