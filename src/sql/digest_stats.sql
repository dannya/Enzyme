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

# Dumping structure for table enzyme.digest_stats
CREATE TABLE IF NOT EXISTS `digest_stats` (
  `date` date NOT NULL,
  `revision_start` int(8) NOT NULL,
  `revision_end` int(8) NOT NULL,
  `total_commits` int(8) NOT NULL,
  `total_lines` int(8) NOT NULL,
  `new_files` int(8) NOT NULL,
  `total_files` int(8) NOT NULL,
  `active_developers` int(8) NOT NULL,
  `open_bugs` int(8) NOT NULL,
  `open_wishes` int(8) NOT NULL,
  `bugs_opened` int(8) NOT NULL,
  `bugs_closed` int(8) NOT NULL,
  `wishes_opened` int(8) NOT NULL,
  `wishes_closed` int(8) NOT NULL,
  UNIQUE KEY `unique` (`date`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

# Data exporting was unselected.
/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
