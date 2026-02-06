CREATE TABLE IF NOT EXISTS `gpt_contragent_delivery` (
  `gpt_id` int(11) NOT NULL AUTO_INCREMENT,
  `gpt_contragent_id` int(11) NOT NULL,
  `gpt_title` varchar(120) NOT NULL DEFAULT '',
  `gpt_address` text,
  `gpt_is_default` tinyint(1) NOT NULL DEFAULT '0',
  `gpt_active` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`gpt_id`),
  KEY `idx_contragent` (`gpt_contragent_id`),
  KEY `idx_active` (`gpt_active`),
  KEY `idx_default` (`gpt_is_default`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
