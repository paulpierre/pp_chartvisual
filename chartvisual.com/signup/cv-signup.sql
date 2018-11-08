
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
 `user_settings` varchar(1255) NOT NULL,
 `user_email_notifications` int(1) NOT NULL, /* whether we should email notifications or not */
 `user_brokers` varchar(255) NOT NULL,
 `user_is_enabled` int(3) NOT NULL,
 `user_tlogin` DATETIME NOT NULL,
 `user_tmodified` DATETIME NOT NULL,
 `user_tcreate` DATETIME NOT NULL,
  PRIMARY KEY (`network_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;