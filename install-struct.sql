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
  `md5` varchar(32) NOT NULL,
  `ext` varchar(10) NOT NULL,
  `user_id` int(11),
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
CREATE TABLE `{TABLE_PREFIX}mod_wbs_core_settlement` (
  `settlement_id` int(11) NOT NULL AUTO_INCREMENT,
  `country_id` int(11) NOT NULL DEFAULT '1',
  `region_id` int(11) NOT NULL,
  `rayon_id` int(11) NOT NULL,
  `any_settlement_id` int(11) NOT NULL,
  `settlement_type_id` int(11) NOT NULL,
  PRIMARY KEY (`settlement_id`)
){TABLE_ENGINE=MyISAM};

DROP TABLE IF EXISTS `{TABLE_PREFIX}mod_wbs_core_settlement_any_settlement`;
CREATE TABLE `{TABLE_PREFIX}mod_wbs_core_settlement_any_settlement` (
  `any_settlement_id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(200) NOT NULL,
  PRIMARY KEY (`any_settlement_id`)
){TABLE_ENGINE=MyISAM};

DROP TABLE IF EXISTS `{TABLE_PREFIX}mod_wbs_core_settlement_country`;
CREATE TABLE `{TABLE_PREFIX}mod_wbs_core_settlement_country` (
  `country_id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(200) NOT NULL,
  PRIMARY KEY (`country_id`)
){TABLE_ENGINE=MyISAM};

DROP TABLE IF EXISTS `{TABLE_PREFIX}mod_wbs_core_settlement_rayon`;
CREATE TABLE `{TABLE_PREFIX}mod_wbs_core_settlement_rayon` (
  `rayon_id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(200) NOT NULL,
  PRIMARY KEY (`rayon_id`)
){TABLE_ENGINE=MyISAM};

DROP TABLE IF EXISTS `{TABLE_PREFIX}mod_wbs_core_settlement_region`;
CREATE TABLE `{TABLE_PREFIX}mod_wbs_core_settlement_region` (
  `region_id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(200) NOT NULL,
  PRIMARY KEY (`region_id`)
){TABLE_ENGINE=MyISAM};

DROP TABLE IF EXISTS `{TABLE_PREFIX}mod_wbs_core_settlement_type`;
CREATE TABLE `{TABLE_PREFIX}mod_wbs_core_settlement_type` (
  `type_id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(200) NOT NULL,
  `short_name` varchar(4) NOT NULL,
  PRIMARY KEY (`type_id`)
){TABLE_ENGINE=MyISAM};

--
-- class.storage_visitor.php
--

DROP TABLE IF EXISTS `{TABLE_PREFIX}mod_wbs_core_visitor`;
CREATE TABLE `{TABLE_PREFIX}mod_wbs_core_visitor` (
  `id_visitor` int(11) NOT NULL AUTO_INCREMENT,
  `date` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `ip` varchar(39) NOT NULL,
  `browser` int(11) NOT NULL,
  `refer` int(11) DEFAULT NULL,
  `page_id` int(11) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id_visitor`)
){TABLE_ENGINE=MyISAM};

DROP TABLE IF EXISTS `{TABLE_PREFIX}mod_wbs_core_visitor_browser`;
CREATE TABLE `{TABLE_PREFIX}mod_wbs_core_visitor_browser` (
  `browser_id` int(11) NOT NULL AUTO_INCREMENT,
  `browser_name` varchar(255) NOT NULL,
  `browser_is_bot` int(11) NOT NULL DEFAULT '0',
  `date` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`browser_id`)
){TABLE_ENGINE=MyISAM};

DROP TABLE IF EXISTS `{TABLE_PREFIX}mod_wbs_core_visitor_refer`;
CREATE TABLE `{TABLE_PREFIX}mod_wbs_core_visitor_refer` (
  `refer_id` int(11) NOT NULL AUTO_INCREMENT,
  `refer_url` varchar(255) NOT NULL,
  PRIMARY KEY (`refer_id`)
){TABLE_ENGINE=MyISAM};

--
-- class.storage_contact.php
--

--
-- module wbs_admin
--

DROP TABLE IF EXISTS `{TABLE_PREFIX}mod_wbs_core_any_variables`;
CREATE TABLE `{TABLE_PREFIX}mod_wbs_core_any_variables` (
  `variable_id` int(11) NOT NULL AUTO_INCREMENT,
  `variable_lang` varchar(3) NOT NULL,
  `variable_code_name` varchar(255) NOT NULL,
  `variable_name` varchar(255) NOT NULL,
  `variable_value` varchar(255) NOT NULL,
  `variable_is_active` int(1) NOT NULL DEFAULT 1,
  `is_deleted` int(11) NOT NULL,
  PRIMARY KEY (`variable_id`)
){TABLE_ENGINE=MyISAM};