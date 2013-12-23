CREATE TABLE `restaurant_payment_type` (
  `id_restaurant_payment_type` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `id_restaurant` int(11) unsigned DEFAULT NULL,
  `payment_method` enum('check','deposit') NOT NULL DEFAULT 'check',
  `id_restaurant_pay_another_restaurant` int(10) unsigned DEFAULT NULL,
  `check_address` varchar(255) DEFAULT NULL,
  `contact_name` varchar(255) DEFAULT NULL,
  `summary_fax` varchar(255) DEFAULT NULL,
  `summary_email` varchar(255) DEFAULT NULL,
  `summary_frequency` int(10) unsigned DEFAULT NULL,
  `legal_name_payment` varchar(255) DEFAULT NULL,
  `summary_method` enum('fax','email') DEFAULT NULL,
  `tax_id` varchar(255) DEFAULT NULL,
  `charge_credit_fee` tinyint(1) NOT NULL DEFAULT '0',
  `waive_fee_first_month` tinyint(1) NOT NULL DEFAULT '0',
  `pay_promotions` tinyint(1) NOT NULL DEFAULT '0',
  `pay_apology_credits` tinyint(1) NOT NULL DEFAULT '0',
  `max_apology_credit` int(11) DEFAULT '5',
  `stripe_id` varchar(255) DEFAULT NULL,
  `stripe_account_id` varchar(255) DEFAULT NULL,
  `balanced_id` varchar(255) DEFAULT NULL,
  `balanced_bank` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id_restaurant_payment_type`),
  KEY `id_restaurant_pay_another_restaurant` (`id_restaurant_pay_another_restaurant`),
  KEY `restaurant_payment_type_ibfk1` (`id_restaurant`),
  CONSTRAINT `restaurant_payment_type_ibfk1` FOREIGN KEY (`id_restaurant`) REFERENCES `restaurant` (`id_restaurant`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `restaurant_payment_type_ibfk2` FOREIGN KEY (`id_restaurant_pay_another_restaurant`) REFERENCES `restaurant` (`id_restaurant`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;