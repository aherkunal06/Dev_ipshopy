<?php
/**
 * OpenCart 3.x Backend Ecommerce Feed  application
 * 
 * @package Mega Feed Pro
 * @author Andras Kato <developer@newcms.hu>
 * @link https://www.newcms.hu/
 * @copyright NewCart and NewCMS Software LTD. 2004-2021 (https://www.newcms.hu)
 * @license https://www.www.newcms.hu/oc3/license/
 * @version 1.2.7 Pro
 * 
 * Attention!
 * In case you want to use a nulled software version, we will detect it due to the built-in protection and you will be prosecuted!
 * DO NOT USE NULLED SOFTWARE BETTER USE ORIGINAL LICENSED SOFTWARE! 
 * 
 */
class ModelExtensionFeedMegaFeedProGenerator extends Model
{
	const DB_GENERATOR_TABLE = 'mega_feed_pro_generator';
	public function update($hash_code, $data = array())
	{
		$result = $this->db->query("SELECT * FROM " . DB_PREFIX . self::DB_GENERATOR_TABLE .
			" WHERE hash_code='{$hash_code}'");
		$creation_date = is_null($data['last_creation_date']) ? '0000-00-00 00:00:00' :
			$data['last_creation_date'];
		if (empty($result->rows))
		{
			$this->db->query("INSERT INTO " . DB_PREFIX . self::DB_GENERATOR_TABLE .
				" (`hash_code`, `change_date`, `last_creation_date`, `current_offset` , `items_count`, `status`) VALUES ('" .
				$hash_code . "', '" . $data['change_date'] . "','" . $creation_date . "' ,'" . $data['current_offset'] .
				"' ,'" . $data['items_count'] . "' ,'" . $data['status'] . "' ) ");
		} else
		{
			$atrributes = !is_null($data['last_creation_date']) ? ", last_creation_date = '" .
				$data['last_creation_date'] . "'" : "";
			$this->db->query("UPDATE " . DB_PREFIX . self::DB_GENERATOR_TABLE .
				" SET change_date = '" . $data['change_date'] . "', current_offset = '" . $data['current_offset'] .
				"', items_count = '" . $data['items_count'] . "', status = '" . $data['status'] .
				"' {$atrributes} WHERE hash_code='{$hash_code}' ");
		}
	}
	public function getCurrentState($hash_code)
	{
		$result = $this->db->query("SELECT current_offset, items_count FROM " .
			DB_PREFIX . self::DB_GENERATOR_TABLE . " WHERE hash_code='{$hash_code}'");
		if (!empty($result->rows))
		{
			return $result->rows[0];
		} else
		{
			return array();
		}
	}
}
