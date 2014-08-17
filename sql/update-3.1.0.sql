CREATE TABLE IF NOT EXISTS `promotions_redeemed` (
  `id` int(8) NOT NULL AUTO_INCREMENT,
  `promo_id` int(8) NOT NULL,
  `promo_code` varchar(32) NOT NULL,
  `steam_id` varchar(32) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ;