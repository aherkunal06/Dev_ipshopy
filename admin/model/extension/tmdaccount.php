<?php
class ModelExtensionTmdAccount extends Model {
	public function install() {
		$this->db->query("CREATE TABLE IF NOT EXISTS `".DB_PREFIX."profileimage` (
		`profileimage_id` int(11) NOT NULL AUTO_INCREMENT,
		`customer_id` int(11) NOT NULL,
		`image` varchar(255) NOT NULL,
		PRIMARY KEY (`profileimage_id`)
		) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=16 ;");
	}
	public function uninstall() {
		$this->db->query("DROP TABLE IF EXISTS `".DB_PREFIX."profileimage`");
	}

	
        public function getSeoUrls($value) {
        $setting_seo_url_data = array();
            
        $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "seo_url WHERE query = '" . $this->db->escape($value) . "'");
        if(isset($query->rows)){
            foreach ($query->rows as $result) {
                $setting_seo_url_data[$result['store_id']][$result['language_id']] = $result['keyword'];
              }
            }

            return $setting_seo_url_data;
        }
}