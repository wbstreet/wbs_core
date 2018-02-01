--
-- class.email.php
--

DROP TABLE IF EXISTS `{TABLE_PREFIX}mod_wbs_core_templates_of_letter`;
CREATE TABLE `{TABLE_PREFIX}mod_wbs_core_templates_of_letter` (
  `letter_template_id` int(11) NOT NULL AUTO_INCREMENT,
  `letter_template_name` varchar(100) NOT NULL,
  `letter_template_body` text NOT NULL,
  `letter_template_subject` varchar(255) NOT NULL DEFAULT '',
  `letter_template_description` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`letter_template_id`)
){TABLE_ENGINE=MyISAM}; 

DROP TABLE IF EXISTS `{TABLE_PREFIX}mod_wbs_core_templates_of_letter_sended`;
CREATE TABLE `{TABLE_PREFIX}mod_wbs_core_templates_of_letter_sended` (
  `sended_letter_id` int(11) NOT NULL AUTO_INCREMENT,
  `letter_body` text NOT NULL,
  `letter_subject` varchar(255) NOT NULL,
  `letter_email` varchar(255) NOT NULL,
  `letter_template_id` int(11) NOT NULL,
  `date` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `is_sended` int(11) NOT NULL DEFAULT '0',
  `sender_user_id` int(11) DEFAULT NULL,
  `send_from_page_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`sended_letter_id`)
){TABLE_ENGINE=MyISAM};

--
-- class.storage_image.php
--

DROP TABLE IF EXISTS `{TABLE_PREFIX}mod_wbs_core_img`;
CREATE TABLE `{TABLE_PREFIX}mod_wbs_core_img` (
  `img_id` int(11) NOT NULL AUTO_INCREMENT,
  `md5` varchar(32) NOT NULL, -- uniq
  `ext` varchar(10) NOT NULL,
  `user_id` int(11),
  --  `is_deleted` int(11) NOT NULL DEFAULT '0',
--  `sizes` int(11),
--  `size_y` int(11),
  PRIMARY KEY (`img_id`)
){TABLE_ENGINE=MyISAM};

-- DROP TABLE IF EXISTS `{TABLE_PREFIX}mod_wbs_core_img_settings`;
-- CREATE TABLE `{TABLE_PREFIX}mod_wbs_core_img_settings` (
--  `settings_id` int(11) NOT NULL AUTO_INCREMENT,
--  `formats` varchar(1) NOT NULL,
--  `max_size` varchar(1) NOT NULL,
--  `latname` varchar(10) NOT NULL,
--  PRIMARY KEY (`settings_id`)
-- ){TABLE_ENGINE=MyISAM};

--
-- class.storage_settlement.php
--

DROP TABLE IF EXISTS `{TABLE_PREFIX}mod_wbs_core_settlement`;
CREATE TABLE `rf_mod3_settlement` (
  `settlement_id` int(11) NOT NULL AUTO_INCREMENT,
  `country_id` int(11) NOT NULL DEFAULT '1',
  `region_id` int(11) NOT NULL,
  `rayon_id` int(11) NOT NULL,
  `any_settlement_id` int(11) NOT NULL,
  `settlement_type_id` int(11) NOT NULL,
  PRIMARY KEY (`settlement_id`)
){TABLE_ENGINE=MyISAM};

DROP TABLE IF EXISTS `{TABLE_PREFIX}mod_wbs_core_settlement_any_settlement`;
CREATE TABLE `rf_mod3_settlement_any_settlement` (
  `any_settlement_id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(200) NOT NULL,
  PRIMARY KEY (`any_settlement_id`)
){TABLE_ENGINE=MyISAM};

DROP TABLE IF EXISTS `{TABLE_PREFIX}mod_wbs_core_settlement_country`;
CREATE TABLE `rf_mod3_settlement_country` (
  `country_id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(200) NOT NULL,
  PRIMARY KEY (`country_id`)
){TABLE_ENGINE=MyISAM};

DROP TABLE IF EXISTS `{TABLE_PREFIX}mod_wbs_core_settlement_rayon`;
CREATE TABLE `rf_mod3_settlement_rayon` (
  `rayon_id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(200) NOT NULL,
  PRIMARY KEY (`rayon_id`)
){TABLE_ENGINE=MyISAM};

DROP TABLE IF EXISTS `{TABLE_PREFIX}mod_wbs_core_settlement_region`;
CREATE TABLE `rf_mod3_settlement_region` (
  `region_id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(200) NOT NULL,
  PRIMARY KEY (`region_id`)
){TABLE_ENGINE=MyISAM};

DROP TABLE IF EXISTS `{TABLE_PREFIX}mod_wbs_core_settlement_type`;
CREATE TABLE `rf_mod3_settlement_type` (
  `type_id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(200) NOT NULL,
  `short_name` varchar(4) NOT NULL,
  PRIMARY KEY (`type_id`)
){TABLE_ENGINE=MyISAM};

--
-- class.storage_contact.php
--