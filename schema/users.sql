CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `access_token` varchar(255) NOT NULL,
  `name` varchar(50) NOT NULL,
  `bio` varchar(300),
  `website` varchar(50),
  `profile_picture` varchar(300),
  `insta_id` int(11) NOT NULL,
  `created` datetime DEFAULT NULL,
  `modified` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;