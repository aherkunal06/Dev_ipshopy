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
class ModelExtensionFeedMegaFeedProLogger extends Model
{
	const DB_LOG_TABLE = 'mega_feed_pro_log';
	public function log($label, $log, $store_id = 0, $lang = '', $currency = '')
	{
		if (MEGA_LOGGER_ENABLED == 0)
		{
			return;
		}
		if (is_array($log))
		{ //convert array to json string
			if (!empty($log))
			{
				$log = json_encode($log);
			} else
			{
				$log = '';
			}
		} else
		{
			$log = '{"msg": ' . (is_numeric($log) ? $log : '"' . $log . '"') . '}';
		}
		$this->db->query("INSERT INTO " . DB_PREFIX . self::DB_LOG_TABLE .
			" (`log_label`, `log_msg`, `log_date`, `store_id`, `currency`, `lang_code`) VALUES ('" .
			$label . "', '" . $this->db->escape($log) . "', NOW(), {$store_id}, '" . $currency .
			"', '" . $lang . "') ");
	}
	public function getLogs($filter = array(), $limit = '', $orderby = 'ASC')
	{
		$where = "";
		if (!empty($filter))
		{
			$where = " WHERE";
			foreach ($filter as $key => $value)
			{
				if ($value['type'] == "int")
				{
					$where .= " " . $key . "=" . $value["value"];
				} elseif ($value['type'] == "string")
				{
					$where .= " " . $key . "='" . $value["value"] . "'";
				}
				if (isset($value['relation']))
				{
					$where .= ' ' . strtoupper($value['relation']) . ' ';
				}
			}
		}
		if ($limit != '')
		{
			$limit = ' LIMIT ' . $limit;
		}
		$order = ' ORDER BY log_id ' . $orderby;
		$result = $this->db->query("SELECT * FROM " . DB_PREFIX . self::DB_LOG_TABLE . $where .
			$order . $limit);
		return $result->rows;
	}
}
