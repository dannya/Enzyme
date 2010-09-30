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

# Dumping structure for table enzyme.commits_reviewed
CREATE TABLE IF NOT EXISTS `commits_reviewed` (
  `revision` int(8) NOT NULL,
  `marked` tinyint(1) NOT NULL,
  `reviewer` varchar(30) COLLATE utf8_unicode_ci NOT NULL,
  `reviewed` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `type` int(2) DEFAULT NULL,
  `area` int(2) DEFAULT NULL,
  `classifier` varchar(30) COLLATE utf8_unicode_ci DEFAULT NULL,
  `classified` timestamp NULL DEFAULT NULL,
  UNIQUE KEY `unique` (`revision`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

# Data exporting was unselected.
/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
