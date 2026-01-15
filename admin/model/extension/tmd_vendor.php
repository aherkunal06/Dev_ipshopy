<?php
class ModelExtensionTmdVendor extends Model {
	public function install() {
	
	$this->db->query("CREATE TABLE IF NOT EXISTS `".DB_PREFIX."vendor` (
	  `vendor_id` int(11) NOT NULL AUTO_INCREMENT,
	  `firstname` text NOT NULL,
	  `lastname` text NOT NULL,
	  `display_name` text NOT NULL,
	  `email` text NOT NULL,
	  `image` varchar(200) NOT NULL,
	  `telephone` varchar(12) NOT NULL,
	  `salt` varchar(50) NOT NULL,
	  `password` varchar(100) NOT NULL,
	  `fax` varchar(255) NOT NULL,
	  `about` text NOT NULL,
	  `company` text NOT NULL,
	  `postcode` varchar(20) NOT NULL,
	  `address_1` text NOT NULL,
	  `address_2` text NOT NULL,
	  `country_id` int(100) NOT NULL,
	  `zone_id` int(100) NOT NULL,
	  `city` text NOT NULL,
	  `map_url` text NOT NULL,
	  `facebook_url` text NOT NULL,
	  `google_url` text NOT NULL,
	  `status` int(10) NOT NULL,
	  `product_status` int(11) NOT NULL,
	  `approved` int(10) NOT NULL,
	  `language_id` int(10) NOT NULL,
	  `payment_method` text NOT NULL,
	  `paypal` text NOT NULL,
	  `bank_name` text NOT NULL,
	  `bank_branch_number` text NOT NULL,
	  `bank_swift_code` varchar(100) NOT NULL,
	  `bank_account_name` text NOT NULL,
	  `bank_account_number` varchar(50) NOT NULL,
	  `store_about` text NOT NULL,
	  `bank_detail` text NOT NULL,
	  `tax_number` varchar(100) NOT NULL,
	  `shipping_charge` varchar(100) NOT NULL,
	  `logo` varchar(200) NOT NULL,
	  `banner` varchar(200) NOT NULL,
	  `store_logowidth` varchar(255) NOT NULL,
	  `store_logoheight` varchar(255) NOT NULL,
	  `store_bannerwidth` varchar(255) NOT NULL,
	  `store_bannerheight` varchar(255) NOT NULL,
	  `commission` varchar(100) NOT NULL,
	  `date_added` date NOT NULL,
	  `date_modified` date NOT NULL,
	  PRIMARY KEY (`vendor_id`)
	) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;");

	$this->db->query("CREATE TABLE IF NOT EXISTS `".DB_PREFIX."vendorproduct_wishlist` (
	  `wishlist_id` int(11) NOT NULL AUTO_INCREMENT,
	  `vendor_id` int(11) NOT NULL,
	  `product_id` int(111) NOT NULL,
	  `date_added` datetime NOT NULL,
	  PRIMARY KEY (`wishlist_id`)
	) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;");

	$this->db->query("CREATE TABLE IF NOT EXISTS `".DB_PREFIX."vendor_amount_pay` (
	  `pay_id` int(11) NOT NULL AUTO_INCREMENT,
	  `vendor_id` int(11) NOT NULL,
	  `amount` decimal(15,4) NOT NULL,
	  `comment` varchar(250) NOT NULL,
	  `payment_method` text NOT NULL,
	  `date_added` date NOT NULL,
	   PRIMARY KEY (`pay_id`)
	) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;");

	$this->db->query("CREATE TABLE IF NOT EXISTS `".DB_PREFIX."vendor_commission` (
	  `commission_id` int(11) NOT NULL AUTO_INCREMENT,
	  `category_id` int(111) NOT NULL,
	  `percentage` int(11) NOT NULL,
	  `fixed` int(11) NOT NULL,
	  `date_added` datetime NOT NULL,
	  `date_modified` datetime NOT NULL,
	  PRIMARY KEY (`commission_id`)
	) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;");

	$this->db->query("CREATE TABLE IF NOT EXISTS `".DB_PREFIX."vendor_description` (
	  `vendor_id` int(11) NOT NULL,
	  `language_id` int(11) NOT NULL,
	  `name` varchar(500) NOT NULL,
	  `description` text NOT NULL,
	  `shipping_policy` text NOT NULL,
	  `return_policy` text NOT NULL,
	  `meta_description` text NOT NULL,
	  `meta_keyword` text NOT NULL
	) ENGINE=MyISAM  DEFAULT CHARSET=utf8;");

	$this->db->query("CREATE TABLE IF NOT EXISTS `".DB_PREFIX."vendor_follow` (
	  `follow_id` int(11) NOT NULL AUTO_INCREMENT,
	  `vendor_id` int(250) NOT NULL,
	  `customer_id` int(250) NOT NULL,
	  `date_added` date NOT NULL,
	  PRIMARY KEY (`follow_id`)
	) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;");

	$this->db->query("CREATE TABLE IF NOT EXISTS `".DB_PREFIX."vendor_mail` (
	  `mail_id` int(11) NOT NULL AUTO_INCREMENT,
	  `sellertype` text NOT NULL,
	  `status` int(11) NOT NULL,
	  `date_added` date NOT NULL,
	  `date_modified` date NOT NULL,
	  PRIMARY KEY (`mail_id`)
	) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;");

	$this->db->query("CREATE TABLE IF NOT EXISTS `".DB_PREFIX."vendor_mail_language` (
	  `mail_id` int(11) NOT NULL,
	  `language_id` int(11) NOT NULL,
	  `name` text NOT NULL,
	  `subject` text NOT NULL,
	  `message` text NOT NULL
	) ENGINE=MyISAM  DEFAULT CHARSET=utf8;");

	$this->db->query("CREATE TABLE IF NOT EXISTS `".DB_PREFIX."vendor_notification` (
	  `notification_id` int(11) NOT NULL AUTO_INCREMENT,
	  `type` text NOT NULL,
	  `date` varchar(200) NOT NULL,
	  `date_added` date NOT NULL,
	  `date_modified` date NOT NULL,
	  PRIMARY KEY (`notification_id`)
	) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;");

	$this->db->query("CREATE TABLE IF NOT EXISTS `".DB_PREFIX."vendor_notification_customer` (
	  `notification_id` int(11) NOT NULL,
	  `customer_id` int(100) NOT NULL
	) ENGINE=MyISAM  DEFAULT CHARSET=utf8;");

	$this->db->query("CREATE TABLE IF NOT EXISTS `".DB_PREFIX."vendor_notification_message` (
	  `notification_id` int(11) NOT NULL,
	  `language_id` int(11) NOT NULL,
	  `message` text NOT NULL
	) ENGINE=MyISAM  DEFAULT CHARSET=utf8;");

	$this->db->query("CREATE TABLE IF NOT EXISTS `".DB_PREFIX."vendor_notification_seller` (
	  `notification_id` int(11) NOT NULL,
	  `vendor_id` int(11) NOT NULL
	) ENGINE=MyISAM  DEFAULT CHARSET=utf8;");

	$this->db->query("CREATE TABLE IF NOT EXISTS `".DB_PREFIX."vendor_order_product` (
	  `order_product_id` int(11) NOT NULL,
	  `vendor_id` int(11) NOT NULL,
	  `order_id` int(11) NOT NULL,
	  `product_id` int(11) NOT NULL,
	  `tmdshippingcost` varchar(255) NOT NULL,
	  `name` varchar(200) NOT NULL,
	  `model` varchar(200) NOT NULL,
	  `quantity` int(4) NOT NULL,
	  `price` decimal(15,4) NOT NULL,
	  `total` decimal(15,4) NOT NULL,
	  `tax` decimal(15,4) NOT NULL,
	  `reward` int(8) NOT NULL,
	  `commissionper` int(11) NOT NULL,
	  `commissionfix` int(11) NOT NULL,
	  `totalcommission` FLOAT(15,4) NOT NULL,
	  `order_status_id` int(11) NOT NULL,
	  `tracking` varchar(200) NOT NULL,
	  `date_added` date NOT NULL,
	  `date_modified` date NOT NULL
	) ENGINE=MyISAM  DEFAULT CHARSET=utf8;");

	$this->db->query("CREATE TABLE IF NOT EXISTS `".DB_PREFIX."vendor_review` (
	  `review_id` int(11) NOT NULL AUTO_INCREMENT,
	  `customer_id` int(11) NOT NULL,
	  `vendor_id` int(11) NOT NULL,
	  `text` text NOT NULL,
	  `status` int(11) NOT NULL,
	  `date_added` date NOT NULL,
	  `date_modified` date NOT NULL,
	  PRIMARY KEY (`review_id`)
	) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;");

	$this->db->query("CREATE TABLE IF NOT EXISTS `".DB_PREFIX."vendor_review_field` (
	  `rf_id` int(11) NOT NULL AUTO_INCREMENT,
	  `sort_order` int(10) NOT NULL,
	  `status` int(11) NOT NULL,
	  `date_added` date NOT NULL,
	  `date_modified` date NOT NULL,
	  PRIMARY KEY (`rf_id`)
	) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;");

	$this->db->query("CREATE TABLE IF NOT EXISTS `".DB_PREFIX."vendor_review_field_description` (
	  `rf_id` int(11) NOT NULL,
	  `language_id` int(11) NOT NULL,
	  `field_name` varchar(200) NOT NULL
	) ENGINE=MyISAM  DEFAULT CHARSET=utf8;");

	$this->db->query("CREATE TABLE IF NOT EXISTS `".DB_PREFIX."vendor_review_field_submit` (
	  `fs_id` int(11) NOT NULL AUTO_INCREMENT,
	  `review_id` int(11) NOT NULL,
	  `vendor_id` int(11) NOT NULL,
	  `customer_id` int(11) NOT NULL,
	  `rf_id` int(11) NOT NULL,
	  `value` varchar(200) NOT NULL,
	  PRIMARY KEY (`fs_id`)
	) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;");

	$this->db->query("CREATE TABLE IF NOT EXISTS `".DB_PREFIX."vendor_to_attribute` (
	  `vendor_id` int(11) NOT NULL,
	  `attribute_id` int(11) NOT NULL
	) ENGINE=MyISAM  DEFAULT CHARSET=utf8;");

	$this->db->query("CREATE TABLE IF NOT EXISTS `".DB_PREFIX."vendor_to_attribute_group` (
	  `vendor_id` int(11) NOT NULL,
	  `attribute_group_id` int(11) NOT NULL
	) ENGINE=MyISAM  DEFAULT CHARSET=utf8;");

	$this->db->query("CREATE TABLE IF NOT EXISTS `".DB_PREFIX."vendor_to_download` (
	  `vendor_id` int(11) NOT NULL,
	  `download_id` int(11) NOT NULL
	) ENGINE=MyISAM  DEFAULT CHARSET=utf8;");

	$this->db->query("CREATE TABLE IF NOT EXISTS `".DB_PREFIX."vendor_to_filter` (
	  `vendor_id` int(11) NOT NULL,
	  `filter_id` int(11) NOT NULL
	) ENGINE=MyISAM  DEFAULT CHARSET=utf8;");

	$this->db->query("CREATE TABLE IF NOT EXISTS `".DB_PREFIX."vendor_to_information` (
	  `vendor_id` int(11) NOT NULL,
	  `information_id` int(11) NOT NULL
	) ENGINE=MyISAM  DEFAULT CHARSET=utf8;");

	$this->db->query("CREATE TABLE IF NOT EXISTS `".DB_PREFIX."vendor_to_manufacturer` (
	  `vendor_id` int(11) NOT NULL,
	  `manufacturer_id` int(11) NOT NULL
	) ENGINE=MyISAM  DEFAULT CHARSET=utf8;");

	$this->db->query("CREATE TABLE IF NOT EXISTS `".DB_PREFIX."vendor_to_option` (
	  `vendor_id` int(11) NOT NULL,
	  `option_id` int(11) NOT NULL
	) ENGINE=MyISAM  DEFAULT CHARSET=utf8;");

	$this->db->query("CREATE TABLE IF NOT EXISTS `".DB_PREFIX."vendor_to_order_product` (
	  `vendor_id` int(11) NOT NULL,
	  `order_id` int(11) NOT NULL
	) ENGINE=MyISAM  DEFAULT CHARSET=utf8;");

	$this->db->query("CREATE TABLE IF NOT EXISTS `".DB_PREFIX."vendor_to_product` (
	  `vendor_id` int(11) NOT NULL,
	  `product_id` int(11) NOT NULL
	) ENGINE=MyISAM  DEFAULT CHARSET=utf8;");

	$this->db->query("CREATE TABLE IF NOT EXISTS `".DB_PREFIX."vendor_to_recurring` (
	  `vendor_id` int(11) NOT NULL,
	  `recurring_id` int(11) NOT NULL
	) ENGINE=MyISAM  DEFAULT CHARSET=utf8;");

	$this->db->query("CREATE TABLE IF NOT EXISTS `".DB_PREFIX."vendor_to_review` (
	  `vendor_id` int(11) NOT NULL,
	  `review_id` int(11) NOT NULL
	) ENGINE=MyISAM  DEFAULT CHARSET=utf8;");

	$this->db->query("CREATE TABLE IF NOT EXISTS `".DB_PREFIX."vendor_inquiry` (
	  `inquiry_id` int(11) NOT NULL AUTO_INCREMENT,
	  `product_id` int(11) NOT NULL,
	  `vendor_id` int(11) NOT NULL,
	  `customer_id` int(11) NOT NULL,
	  `name` varchar(200) NOT NULL,
	  `email` varchar(200) NOT NULL,
	  `description` text NOT NULL,
	  `telephone` varchar(11) NOT NULL,
	  `status` int(11) NOT NULL,
	  `date_added` date NOT NULL,
	  `date_modified` date NOT NULL,
	  PRIMARY KEY (`inquiry_id`)
	) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;");

	$this->db->query("CREATE TABLE IF NOT EXISTS `".DB_PREFIX."shipping` (
	  `shipping_id` int(11) NOT NULL AUTO_INCREMENT,
	  `vendor_id` int(11) NOT NULL,
	  `country_id` int(11) NOT NULL,
	  `zip_from` varchar(255) NOT NULL,  
	  `weight_from` decimal(10,2) NOT NULL,
	  `weight_to` decimal(10,2) NOT NULL,
	  `price` int(11) NOT NULL,
	  `date_added` date NOT NULL,
	   PRIMARY KEY (`shipping_id`)
	) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;");

	$this->db->query("CREATE TABLE IF NOT EXISTS `".DB_PREFIX."vendor_chatsystem` (
	 `message_id` int(11) NOT NULL AUTO_INCREMENT,
	  `vendor_id` int(11) NOT NULL,
	  `chatstatus` int(11) NOT NULL,
	  `message` varchar(255) NOT NULL,
	  PRIMARY KEY (`message_id`)
	) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;");

	$this->db->query("CREATE TABLE IF NOT EXISTS `".DB_PREFIX."order_vendorhistory` (
	   `order_vendorhistory_id` int(11) NOT NULL AUTO_INCREMENT,
	   `order_id` int(11) NOT NULL,
	   `order_status_id` int(11) NOT NULL,
	   `vendor_id` int(11) NOT NULL DEFAULT '0',
	   `order_product_id` int(11) NOT NULL,
	   `comment` text NOT NULL,
	   `updateby` varchar(255) NOT NULL,
	   `date_added` datetime NOT NULL,
	  PRIMARY KEY (`order_vendorhistory_id`)
	) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;");

	$this->db->query("CREATE TABLE IF NOT EXISTS `".DB_PREFIX."commissionrange` (
	   `commissionrange_id` int(11) NOT NULL AUTO_INCREMENT,
	   `category_id` int(11) NOT NULL,
	   `totalstart` int(11) NOT NULL,
	   `totalend` int(11) NOT NULL DEFAULT '0',
	   `commision` decimal(10,5) NOT NULL, 
	   `date_added` datetime NOT NULL,
	   `date_modified` datetime NOT NULL,
	  PRIMARY KEY (`commissionrange_id`)
	) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;");
	
	}
	
	public function uninstall() {
	$this->db->query("DROP TABLE IF EXISTS `".DB_PREFIX."vendor`");
	$this->db->query("DROP TABLE IF EXISTS `".DB_PREFIX."vendorproduct_wishlist`");
	$this->db->query("DROP TABLE IF EXISTS `".DB_PREFIX."vendor_amount_pay`");
	$this->db->query("DROP TABLE IF EXISTS `".DB_PREFIX."vendor_commission`");
	$this->db->query("DROP TABLE IF EXISTS `".DB_PREFIX."vendor_description`");
	$this->db->query("DROP TABLE IF EXISTS `".DB_PREFIX."vendor_follow`");
	$this->db->query("DROP TABLE IF EXISTS `".DB_PREFIX."vendor_mail`");
	$this->db->query("DROP TABLE IF EXISTS `".DB_PREFIX."vendor_mail_language`");
	$this->db->query("DROP TABLE IF EXISTS `".DB_PREFIX."vendor_notification`");
	$this->db->query("DROP TABLE IF EXISTS `".DB_PREFIX."vendor_notification_customer`");
	$this->db->query("DROP TABLE IF EXISTS `".DB_PREFIX."vendor_notification_message`");
	$this->db->query("DROP TABLE IF EXISTS `".DB_PREFIX."vendor_notification_seller`");
	$this->db->query("DROP TABLE IF EXISTS `".DB_PREFIX."vendor_order_product`");
	$this->db->query("DROP TABLE IF EXISTS `".DB_PREFIX."vendor_review`");
	$this->db->query("DROP TABLE IF EXISTS `".DB_PREFIX."vendor_review_field`");
	$this->db->query("DROP TABLE IF EXISTS `".DB_PREFIX."vendor_review_field_description`");
	$this->db->query("DROP TABLE IF EXISTS `".DB_PREFIX."vendor_review_field_submit`");
	$this->db->query("DROP TABLE IF EXISTS `".DB_PREFIX."vendor_to_attribute`");
	$this->db->query("DROP TABLE IF EXISTS `".DB_PREFIX."vendor_to_attribute_group`");
	$this->db->query("DROP TABLE IF EXISTS `".DB_PREFIX."vendor_to_download`");
	$this->db->query("DROP TABLE IF EXISTS `".DB_PREFIX."vendor_to_filter`");
	$this->db->query("DROP TABLE IF EXISTS `".DB_PREFIX."vendor_to_information`");
	$this->db->query("DROP TABLE IF EXISTS `".DB_PREFIX."vendor_to_manufacturer`");
	$this->db->query("DROP TABLE IF EXISTS `".DB_PREFIX."vendor_to_option`");
	$this->db->query("DROP TABLE IF EXISTS `".DB_PREFIX."vendor_to_order_product`");
	$this->db->query("DROP TABLE IF EXISTS `".DB_PREFIX."vendor_to_product`");
	$this->db->query("DROP TABLE IF EXISTS `".DB_PREFIX."vendor_to_recurring`");
	$this->db->query("DROP TABLE IF EXISTS `".DB_PREFIX."vendor_to_review`");
	$this->db->query("DROP TABLE IF EXISTS `".DB_PREFIX."vendor_inquiry`");
	$this->db->query("DROP TABLE IF EXISTS `".DB_PREFIX."shipping`");
	$this->db->query("DROP TABLE IF EXISTS `".DB_PREFIX."vendor_chatsystem`");
	$this->db->query("DROP TABLE IF EXISTS `".DB_PREFIX."order_vendorhistory`");
	$this->db->query("DROP TABLE IF EXISTS `".DB_PREFIX."commissionrange`");
	}
}
