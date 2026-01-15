<?php

namespace mergado;

class Dbmodel {

    private $registry;
    private $version;
    private $extension_fullname;
    private $is_db_compatible;

    const DB_LOG_TABLE = 'mergado_marketing_pack_log';
    const DB_GENERATOR_TABLE = 'mergado_marketing_pack_generator';
  
    //added in version 1.1
    const DB_PRODUCT_TABLE = 'mergado_marketing_pack_product';

    //added in version 1.2
    const DB_NEWS_TABLE = 'mergado_marketing_pack_news';
  
    //added in version 1.1
    const DB_GENERATOR_C_CURRENT_OFFSET = "current_offset";
    const DB_GENERATOR_C_ITEMS_COUNT = "items_count";
    const DB_GENERATOR_C_LAST_CREATION_DATE = "last_creation_date";

    const DB_NEWS_C_URL = "url";

    function __construct($registry, $extension_fullname, $version, $is_db_compatible) {
        $this->registry = $registry;
        $this->version = $version;
        $this->is_db_compatible = $is_db_compatible;
        $this->extension_fullname = $extension_fullname;
    }

    public function __get($name) {
      return $this->registry->get($name);
    }
  
    public function create() {
    
      //create log table
      $this->db->query("CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . self::DB_LOG_TABLE . "` (
        `log_id` INT(11) NOT NULL AUTO_INCREMENT,
        `store_id` INT(11) NOT NULL,
        `lang_code` VARCHAR(6) NOT NULL,
        `currency` VARCHAR(3) NOT NULL,
        `log_date` DATETIME NOT NULL,
        `log_label` VARCHAR(255) NOT NULL,
        `log_msg` TEXT NOT NULL,
        PRIMARY KEY (`log_id`)
      ) ENGINE=MyISAM DEFAULT COLLATE=utf8_general_ci");
  
      //create generator table
      $this->db->query("CREATE TABLE IF NOT EXISTS `" . DB_PREFIX  . self::DB_GENERATOR_TABLE . "` (
        `record_id` INT(11) NOT NULL AUTO_INCREMENT,
        `hash_code` varchar(255) NOT NULL,
        `current_offset` INT NULL,
        `items_count` INT NULL,
        `change_date` DATETIME NOT NULL,
        `last_creation_date` DATETIME NULL,
        `status` INT NOT NULL,
        PRIMARY KEY (`record_id`)
      ) ENGINE=MyISAM DEFAULT COLLATE=utf8_general_ci");
  
      //create product table
      $this->db->query("CREATE TABLE IF NOT EXISTS `" . DB_PREFIX  . self::DB_PRODUCT_TABLE . "` (
        `product_id` INT(11) NOT NULL AUTO_INCREMENT,
        PRIMARY KEY (`product_id`)
      ) ENGINE=MyISAM DEFAULT COLLATE=utf8_general_ci");

      //create news table
      $this->db->query("CREATE TABLE IF NOT EXISTS `" . DB_PREFIX  . SELF::DB_NEWS_TABLE. "` (
        `news_id` INT(11) NOT NULL AUTO_INCREMENT,
        `guid` INT(11) NOT NULL,
        `lang` VARCHAR(3) NOT NULL,
        `title` VARCHAR(255) NOT NULL,
        `content` TEXT NOT NULL,
        `category` VARCHAR(255) NOT NULL,
        `pubdate` DATETIME NOT NULL,
        `url` VARCHAR(255) NOT NULL,
        PRIMARY KEY (`news_id`)
      ) ENGINE=MyISAM DEFAULT COLLATE=utf8_general_ci");

      $this->setDbVersion();
      $this->setInstallDate();
  
    }
  
    public function delete(){
  
      //drop log table
      $this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX  . self::DB_LOG_TABLE . "`");
  
      //drop generator table
      $this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX  . self::DB_GENERATOR_TABLE . "`");
  
      //drop product table
      $this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX  . self::DB_PRODUCT_TABLE . "`");

      //drop product table
      $this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX  . self::DB_NEWS_TABLE . "`");
  
    }

    public function needUpdate() {

      if(!$this->is_db_compatible) {
        $this->update();
      } else {
        return false;
      }

    }

    public function update() {
      $this->updateV1_1();
      $this->updateV1_2();
      $this->setDbVersion();
      $this->setInstallDate();
    }

    public function updateV1_1() {

        /** new tables */
        $this->db->query("CREATE TABLE IF NOT EXISTS `" . DB_PREFIX  . SELF::DB_PRODUCT_TABLE. "` (
          `product_id` INT(11) NOT NULL AUTO_INCREMENT,
          PRIMARY KEY (`product_id`)
        ) ENGINE=MyISAM DEFAULT COLLATE=utf8_general_ci");
    
        /** modify old tables */
        $result = $this->db->query("SHOW COLUMNS FROM `" . DB_PREFIX . SELF::DB_GENERATOR_TABLE . "` LIKE '" . SELF::DB_GENERATOR_C_CURRENT_OFFSET . "'");
        if($result->num_rows == 0) {
          $this->db->query("ALTER TABLE `". DB_PREFIX . SELF::DB_GENERATOR_TABLE."` ADD `" . SELF::DB_GENERATOR_C_CURRENT_OFFSET . "` INT NULL");
        }
    
        $result = $this->db->query("SHOW COLUMNS FROM `" . DB_PREFIX . SELF::DB_GENERATOR_TABLE . "` LIKE '" . SELF::DB_GENERATOR_C_ITEMS_COUNT . "'");
        if($result->num_rows == 0) {
          $this->db->query("ALTER TABLE `". DB_PREFIX  . SELF::DB_GENERATOR_TABLE."` ADD `" . SELF::DB_GENERATOR_C_ITEMS_COUNT . "` INT NULL");
        }
    
        $result = $this->db->query("SHOW COLUMNS FROM `" . DB_PREFIX  . SELF::DB_GENERATOR_TABLE . "` LIKE '" . SELF::DB_GENERATOR_C_LAST_CREATION_DATE. "'");
        if($result->num_rows == 0) {
          $this->db->query("ALTER TABLE `". DB_PREFIX  . SELF::DB_GENERATOR_TABLE."` ADD `" . SELF::DB_GENERATOR_C_LAST_CREATION_DATE . "` DATETIME NULL");
        }
        
    }

    public function updateV1_2() {

        /** new tables */
        $this->db->query("CREATE TABLE IF NOT EXISTS `" . DB_PREFIX  . SELF::DB_NEWS_TABLE. "` (
          `news_id` INT(11) NOT NULL AUTO_INCREMENT,
          `guid` INT(11) NOT NULL,
          `lang` VARCHAR(3) NOT NULL,
          `title` VARCHAR(255) NOT NULL,
          `content` TEXT NOT NULL,
          `category` VARCHAR(255) NOT NULL,
          `pubdate` DATETIME NOT NULL,
          `url` VARCHAR(255) NOT NULL,
          PRIMARY KEY (`news_id`)
        ) ENGINE=MyISAM DEFAULT COLLATE=utf8_general_ci");

        $result = $this->db->query("SHOW COLUMNS FROM `" . DB_PREFIX  . SELF::DB_NEWS_TABLE . "` LIKE '" . SELF::DB_NEWS_C_URL . "'");
        if($result->num_rows == 0) {
          $this->db->query("ALTER TABLE `". DB_PREFIX  . SELF::DB_NEWS_TABLE ."` ADD `" . SELF::DB_NEWS_C_URL . "` VARCHAR(255) NOT NULL");
        }
    }

    public function setDbVersion() {
      $this->load->model('setting/setting');
      $this->model_setting_setting->editSettingValue($this->extension_fullname , $this->extension_fullname . '_version', $this->version);
    }

    public function setInstallDate() {
        $this->load->model('setting/setting');
        $this->model_setting_setting->editSettingValue($this->extension_fullname , $this->extension_fullname . '_install_date', time());
    }
  

}
