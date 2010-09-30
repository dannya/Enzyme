# --------------------------------------------------------
# Host:                         127.0.0.1
# Server version:               5.1.41
# Server OS:                    Win32
# HeidiSQL version:             5.1.0.3546
# Date/time:                    2010-09-30 02:54:32
# --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;

# Dumping structure for table enzyme.commit_bugs
CREATE TABLE IF NOT EXISTS `commit_bugs` (
  `revision` int(8) unsigned NOT NULL,
  `bug` int(7) unsigned NOT NULL,
  `date` datetime DEFAULT NULL,
  `title` text COLLATE utf8_unicode_ci,
  `product` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `component` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `votes` int(5) unsigned DEFAULT NULL,
  `status` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `resolution` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  UNIQUE KEY `unique` (`revision`,`bug`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

# Data exporting was unselected.
/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
