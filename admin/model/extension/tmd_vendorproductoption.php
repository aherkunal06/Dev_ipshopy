<?php
class ModelExtensionTmdVendorProductOption extends Model {
	public function install() {
		$this->db->query("CREATE TABLE IF NOT EXISTS `".DB_PREFIX."product_option_image` (
		  `option_image_id` int(20) NOT NULL AUTO_INCREMENT,
		  `product_option_value_id` int(20) NOT NULL,
		  `option_image` varchar(250) NOT NULL,
		  `option_sku` varchar(64) NOT NULL,
		  `option_upc` varchar(64) NOT NULL,
		  PRIMARY KEY (`option_image_id`)
		) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;");
	}
	public function uninstall() {
		$this->db->query("DROP TABLE IF EXISTS `".DB_PREFIX."product_option_image`");
	}
}