
/**
  +-------------------+
  | Chart Visual API |
 +-------------------+
  by Paul Pierre
 
  ====
  user
  ====
  A user table tracking their settings
 */
DROP TABLE IF EXISTS `user`;
CREATE TABLE `user`(
 `user_id` int(10) NOT NULL AUTO_INCREMENT,
 `user_first_name` varchar(255) NOT NULL,
 `user_last_name` varchar(255) NOT NULL,
 `user_email` varchar(255) NOT NULL,
 `user_name` varchar(255) NOT NULL,
 `user_password` varchar(255) NOT NULL,
 `user_country` varchar(255) NOT NULL,
 `user_phone` varchar(255) NOT NULL,
 `user_broker` varchar(255) NOT NULL,
 `user_membership_level` int(3) NOT NULL,
 `user_sponsor_id` varchar(255) NOT NULL,
 `user_referrer_id` varchar(255) NOT NULL,
 `user_settings` varchar(1255) NOT NULL,
 `user_email_notifications` int(1) NOT NULL, /* whether we should email notifications or not */
 `user_is_enabled` int(3) NOT NULL,
 `user_tlogin` DATETIME NOT NULL,
 `user_tmodified` DATETIME NOT NULL,
 `user_tcreate` DATETIME NOT NULL,
  PRIMARY KEY (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/** =====
 *  alert
 *  =====
 */

DROP TABLE IF EXISTS `alert`;
CREATE TABLE `alert`(
`alert_id` int(10) NOT NULL AUTO_INCREMENT,
`user_id` int(10) NOT NULL,
`alert_indicator` varchar(100) NOT NULL,
`alert_param` varchar(255) NOT NULL,
`alert_timeframe` varchar(3) NOT NULL,
`alert_currency_pair` varchar(10) NOT NULL,
`alert_is_enabled` int(3) NOT NULL,
`alert_tmodified` DATETIME NOT NULL,
`alert_tcreate` DATETIME NOT NULL,
  PRIMARY KEY(`alert_id`),
  FOREIGN KEY (`user_id`) REFERENCES user(`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/** ==========
 *  alert_sent
 *  ==========
 */

 DROP TABLE IF EXISTS `alert_sent`;
 CREATE TABLE `alert_sent`(
 `id` int(10) NOT NULL AUTO_INCREMENT,
 `indicator` varchar(3) NOT NULL,
 `alert_param` varchar(255) NOT NULL,
 `tf` varchar(3) NOT NULL,
 `alert_currency_pair` varchar(10) NOT NULL,
 `time` varchar(30) NOT NULL,
 `tcreate` DATETIME NOT NULL,
  PRIMARY KEY(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/**
*
*
*/