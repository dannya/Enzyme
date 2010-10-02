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