--
-- class.email.php
--

DROP TABLE IF EXISTS `{TABLE_PREFIX}mod_wbs_core_templates_of_letter`;
CREATE TABLE `{TABLE_PREFIX}mod_wbs_core_templates_of_letter` (
  `letter_template_id` int(11) NOT NULL AUTO_INCREMENT,
  `letter_template_name` varchar(100) NOT NULL,
  `letter_template_body` text NOT NULL,
  `letter_template_subject` varchar(255) NOT NULL DEFAULT '',
  `letter_template_description` varchar(255) NOT NULL DEFAULT ''
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
  `group_latname` varchar(10) NOT NULL,
  `md5` varchar(32) NOT NULL,
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

--
-- class.storage_contact.php
--