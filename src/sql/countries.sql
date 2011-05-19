CREATE TABLE `countries` (
  `code` char(2) COLLATE utf8_unicode_ci NOT NULL COMMENT 'Two-letter country code (ISO 3166-1 alpha-2)',
  `continent` enum('asia','africa','europe','north-america','south-america','oceania','antarctica') COLLATE utf8_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL COMMENT 'English country name',
  `full_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL COMMENT 'Full English country name',
  `iso3` char(3) COLLATE utf8_unicode_ci NOT NULL COMMENT 'Three-letter country code (ISO 3166-1 alpha-3)',
  `number` smallint(3) unsigned zerofill NOT NULL COMMENT 'Three-digit country number (ISO 3166-1 numeric)',
  PRIMARY KEY (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci