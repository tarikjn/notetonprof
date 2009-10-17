#!/usr/local/php5/bin/php -q
<?php
require("../jobs/_ini.php");

DBPal::mquery("
ALTER TABLE  `notes` CHANGE  `pedagogie`  `clarity` TINYINT( 1 ) UNSIGNED NULL DEFAULT NULL;
ALTER TABLE  `notes` CHANGE  `interet`  `interest` TINYINT( 1 ) UNSIGNED NULL DEFAULT NULL;
ALTER TABLE  `notes` CHANGE  `connaissances`  `knowledgeable` TINYINT( 1 ) UNSIGNED NULL DEFAULT NULL;
ALTER TABLE  `notes` CHANGE  `regularite`  `regularity` TINYINT( 1 ) UNSIGNED NULL DEFAULT NULL;
ALTER TABLE  `notes` CHANGE  `ambiance`  `atmosphere` TINYINT( 1 ) UNSIGNED NULL DEFAULT NULL;
ALTER TABLE  `notes` CHANGE  `justesse`  `difficulty` TINYINT( 1 ) UNSIGNED NULL DEFAULT NULL;
ALTER TABLE  `notes` ADD  `fairness` TINYINT( 1 ) UNSIGNED NULL DEFAULT NULL AFTER  `difficulty` ,
ADD  `availability` TINYINT( 1 ) UNSIGNED NULL DEFAULT NULL AFTER  `fairness`;

DROP TABLE `update_notifications`;
CREATE TABLE IF NOT EXISTS `update_notifications` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `rating_id` int(11) NOT NULL,
  `email` varchar(255) NOT NULL DEFAULT '',
  `user_id` int(11) unsigned DEFAULT NULL,
  `active` enum('yes','no') NOT NULL DEFAULT 'yes',
  PRIMARY KEY (`id`)
);
");
