--
-- class.email.php
--

DROP TABLE IF EXISTS `{TABLE_PREFIX}mod_wbscore_templates_of_letter`;
CREATE TABLE `{TABLE_PREFIX}mod_wbscore_templates_of_letter` (
  `letter_template_id` int(11) NOT NULL,
  `letter_template_name` varchar(100) NOT NULL,
  `letter_template_body` text NOT NULL,
  `letter_template_subject` varchar(255) NOT NULL DEFAULT ''
){TABLE_ENGINE=MyISAM}; 

DROP TABLE IF EXISTS `{TABLE_PREFIX}mod_wbscore_templates_of_letter_sended`;
CREATE TABLE `{TABLE_PREFIX}mod_wbscore_templates_of_letter_sended` (
  `sended_letter_id` int(11) NOT NULL,
  `letter_body` text NOT NULL,
  `letter_subject` varchar(255) NOT NULL,
  `letter_email` varchar(255) NOT NULL,
  `letter_template_id` int(11) NOT NULL,
  `date` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `is_sended` int(11) NOT NULL DEFAULT '0',
  `sender_user_id` int(11) DEFAULT NULL,
  `send_from_page_id` int(11) DEFAULT NULL
){TABLE_ENGINE=MyISAM};
