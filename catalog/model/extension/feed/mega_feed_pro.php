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
class ModelExtensionFeedMegaFeedPro extends Model
{
	const DB_WEIGHT_CLASS_DESC_TABLE = 'weight_class_description';
	const DB_LENGTH_CLASS_DESC_TABLE = 'length_class_description';
	const DB_STOCK_STATUS_TABLE = 'stock_status';
	const DB_PRODUCT_TABLE = 'product';
	const DB_PRODUCT_TO_CATEGORY_TABLE = 'product_to_category';
	const DB_PRODUCT_TO_STORE_TABLE = 'product_to_store';
	const DB_PRODUCT_SPECIAL_TABLE = 'product_special';
	const DB_PRODUCT_OPTION_VALUE_TABLE = 'product_option_value';
	const DB_CATEGORY_TABLE = 'category';
	const DB_CATEGORY_DESCRIPTION_TABLE = 'category_description';
	const DB_CATEGORY_TO_STORE_TABLE = 'category_to_store';
	public function getMegaToken()
	{
		return substr(sha1($_SERVER['SERVER_NAME'] . '$@/digital-wolf-645787'), 0, 30);
	}
	public function getWeightClass($class_id, $lang_id)
	{
		$result = $this->db->query("SELECT * FROM " . DB_PREFIX . self::DB_WEIGHT_CLASS_DESC_TABLE .
			" WHERE weight_class_id={$class_id} AND language_id={$lang_id}");
		return empty($result->rows) ? "" : $result->rows[0]['unit'];
	}
	public function getLengthClass($class_id, $lang_id)
	{
		$result = $this->db->query("SELECT * FROM " . DB_PREFIX . self::DB_LENGTH_CLASS_DESC_TABLE .
			" WHERE length_class_id={$class_id} AND language_id={$lang_id}");
		return empty($result->rows) ? "" : $result->rows[0]['unit'];
	}
	public function getStockStatus($status, $lang_id)
	{
		$res = $this->db->query("SELECT * FROM " . DB_PREFIX . self::DB_STOCK_STATUS_TABLE .
			" WHERE name='{$status}' LIMIT 1");
		$status_id = $res->rows[0]['stock_status_id'];
		$result = $this->db->query("SELECT * FROM " . DB_PREFIX . self::DB_STOCK_STATUS_TABLE .
			" WHERE stock_status_id={$status_id} AND language_id={$lang_id}");
		return empty($result->rows) ? null : $result->rows[0];
	}
	public function getAllCategories($lang_id, $store_id = 0, $filter = array())
	{
		$limit = !empty($filter) ? ' LIMIT ' . $filter['start'] . ', ' . $filter['limit'] :
			'';
		$result = $this->db->query("SELECT " . DB_PREFIX . self::DB_CATEGORY_TABLE .
			".category_id as category_id, name, description FROM " . DB_PREFIX . self::DB_CATEGORY_TABLE .
			" LEFT JOIN " . DB_PREFIX . self::DB_CATEGORY_DESCRIPTION_TABLE . " ON " .
			DB_PREFIX . self::DB_CATEGORY_DESCRIPTION_TABLE . ".category_id=" . DB_PREFIX .
			self::DB_CATEGORY_TABLE . ".category_id AND " . DB_PREFIX . self::DB_CATEGORY_DESCRIPTION_TABLE .
			".language_id={$lang_id}" . " LEFT JOIN " . DB_PREFIX . self::DB_CATEGORY_TO_STORE_TABLE .
			" ON " . DB_PREFIX . self::DB_CATEGORY_TO_STORE_TABLE . ".category_id=" .
			DB_PREFIX . self::DB_CATEGORY_TABLE . ".category_id AND " . DB_PREFIX . self::DB_CATEGORY_TO_STORE_TABLE .
			".store_id={$store_id}" . " WHERE status=1 AND " . DB_PREFIX . self::DB_CATEGORY_TO_STORE_TABLE .
			".store_id IS NOT NULL ORDER BY " . DB_PREFIX . self::DB_CATEGORY_TABLE .
			".category_id ASC {$limit}");
		return $result->rows;
	}
	public function getItemQuantityInCategories($category_id, $store_id = 0)
	{
		//get main and all child categories
		$result = $this->db->query("SELECT " . DB_PREFIX . self::DB_CATEGORY_TABLE .
			".category_id FROM " . DB_PREFIX . self::DB_CATEGORY_TABLE . " LEFT JOIN " .
			DB_PREFIX . self::DB_CATEGORY_TO_STORE_TABLE . " ON " . DB_PREFIX . self::DB_CATEGORY_TO_STORE_TABLE .
			".category_id=" . DB_PREFIX . self::DB_CATEGORY_TABLE . ".category_id AND " .
			DB_PREFIX . self::DB_CATEGORY_TO_STORE_TABLE . ".store_id={$store_id}" .
			" WHERE status=1 AND " . DB_PREFIX . self::DB_CATEGORY_TO_STORE_TABLE .
			".store_id IS NOT NULL AND (" . DB_PREFIX . self::DB_CATEGORY_TABLE .
			".category_id={$category_id} OR parent_id={$category_id})");
		$quantity = 0;
		if ($result && count($result->rows) > 0)
		{
			//implode category_ids
			foreach ($result->rows as $r)
			{
				$categories[] = $r['category_id'];
			}
			$categories = implode(",", $categories);
			//sum quantity column in product in main and child categories
			$result = $this->db->query("SELECT SUM( " . DB_PREFIX . self::DB_PRODUCT_TABLE .
				".quantity ) as quantity FROM " . DB_PREFIX . self::DB_PRODUCT_TO_CATEGORY_TABLE .
				" LEFT JOIN " . DB_PREFIX . self::DB_PRODUCT_TABLE . " ON " . DB_PREFIX . self::DB_PRODUCT_TABLE .
				".product_id=" . DB_PREFIX . self::DB_PRODUCT_TO_CATEGORY_TABLE .
				".product_id and status=1" . " LEFT JOIN " . DB_PREFIX . self::DB_PRODUCT_TO_STORE_TABLE .
				" ON " . DB_PREFIX . self::DB_PRODUCT_TO_STORE_TABLE . ".product_id=" .
				DB_PREFIX . self::DB_PRODUCT_TO_CATEGORY_TABLE . ".product_id AND " . DB_PREFIX .
				self::DB_PRODUCT_TO_STORE_TABLE . ".store_id={$store_id}" . " WHERE " .
				DB_PREFIX . self::DB_PRODUCT_TO_STORE_TABLE . ".store_id IS NOT NULL AND " .
				DB_PREFIX . self::DB_PRODUCT_TO_CATEGORY_TABLE . ".category_id in ({$categories})");
			$quantity = $result && $result->rows[0]['quantity'] != '' ? $result->rows[0]['quantity'] :
				0;
		}
		return $quantity;
	}
	public function getItemMinMaxPriceInCategory($category_id, $store_id = 0, $type =
		'MIN')
	{
		$price = $type == 'MAX' ? 0 : PHP_INT_MAX;
		$tax_class_id = -1;
		//get main and all child categories
		$result = $this->db->query("SELECT " . DB_PREFIX . self::DB_CATEGORY_TABLE .
			".category_id FROM " . DB_PREFIX . self::DB_CATEGORY_TABLE . " LEFT JOIN " .
			DB_PREFIX . self::DB_CATEGORY_TO_STORE_TABLE . " ON " . DB_PREFIX . self::DB_CATEGORY_TO_STORE_TABLE .
			".category_id=" . DB_PREFIX . self::DB_CATEGORY_TABLE . ".category_id AND " .
			DB_PREFIX . self::DB_CATEGORY_TO_STORE_TABLE . ".store_id={$store_id}" .
			" WHERE status=1 AND " . DB_PREFIX . self::DB_CATEGORY_TO_STORE_TABLE .
			".store_id IS NOT NULL AND (" . DB_PREFIX . self::DB_CATEGORY_TABLE .
			".category_id={$category_id} OR parent_id={$category_id})");
		if ($result && count($result->rows) > 0)
		{
			//implode category_ids
			foreach ($result->rows as $r)
			{
				$categories[] = $r['category_id'];
			}
			$categories = implode(",", $categories);
			//products in main and child categories
			$result = $this->db->query("SELECT " . DB_PREFIX . self::DB_PRODUCT_TABLE .
				".price, " . DB_PREFIX . self::DB_PRODUCT_TABLE . ".product_id, tax_class_id, " .
				DB_PREFIX . self::DB_PRODUCT_SPECIAL_TABLE . ".price as special  FROM " .
				DB_PREFIX . self::DB_PRODUCT_TO_CATEGORY_TABLE . " LEFT JOIN " . DB_PREFIX .
				self::DB_PRODUCT_TABLE . " ON " . DB_PREFIX . self::DB_PRODUCT_TABLE .
				".product_id=" . DB_PREFIX . self::DB_PRODUCT_TO_CATEGORY_TABLE .
				".product_id and status=1" . " LEFT JOIN " . DB_PREFIX . self::DB_PRODUCT_SPECIAL_TABLE .
				" ON " . DB_PREFIX . self::DB_PRODUCT_SPECIAL_TABLE . ".product_id=" . DB_PREFIX .
				self::DB_PRODUCT_TO_CATEGORY_TABLE . ".product_id and status=1" . " LEFT JOIN " .
				DB_PREFIX . self::DB_PRODUCT_TO_STORE_TABLE . " ON " . DB_PREFIX . self::DB_PRODUCT_TO_STORE_TABLE .
				".product_id=" . DB_PREFIX . self::DB_PRODUCT_TO_CATEGORY_TABLE .
				".product_id AND " . DB_PREFIX . self::DB_PRODUCT_TO_STORE_TABLE . ".store_id={$store_id}" .
				" WHERE " . DB_PREFIX . self::DB_PRODUCT_TO_STORE_TABLE .
				".store_id IS NOT NULL AND " . DB_PREFIX . self::DB_PRODUCT_TO_CATEGORY_TABLE .
				".category_id in ({$categories})");
			foreach ($result->rows as $p)
			{
				if (!isset($p['product_id']) || empty($p['product_id']))
				{
					break;
				}
				if ($type == 'MAX')
				{ //max
					$options = $this->db->query("SELECT IFNULL(MAX(price),0) as price FROM " .
						DB_PREFIX . self::DB_PRODUCT_OPTION_VALUE_TABLE . " WHERE product_id={$p['product_id']} AND price > 0 AND quantity > 0 AND price_prefix='+'");
					$option_price = 0;
					if (!empty($options->rows) && is_numeric($options->rows[0]['price']))
					{
						$option_price = $p['price'] + $options->rows[0]['price'];
					}
					if ($price <= $option_price && $p['price'] <= $option_price)
					{
						$price = $option_price;
						$tax_class_id = $p['tax_class_id'];
					} elseif ($price < $p['price'])
					{
						$price = $p['price'];
						$tax_class_id = $p['tax_class_id'];
					}
				} else
				{ //min
					$options = $this->db->query("SELECT IFNULL(MAX(price),0) as price FROM " .
						DB_PREFIX . self::DB_PRODUCT_OPTION_VALUE_TABLE . " WHERE product_id={$p['product_id']} AND price > 0 AND quantity > 0 AND price_prefix='-'");
					$special_price = PHP_INT_MAX;
					$option_price = PHP_INT_MAX;
					if (is_numeric($p['special']))
					{
						$special_price = $p['special'];
					}
					if (!empty($options->rows) && is_numeric($options->rows[0]['price']))
					{
						$option_price = $p['price'] - $options->rows[0]['price'];
					}
					if ($price >= $option_price && $p['price'] >= $option_price && $special_price >=
						$option_price)
					{
						$price = $option_price;
						$tax_class_id = $p['tax_class_id'];
					} elseif ($price >= $special_price && $p['price'] >= $special_price)
					{
						$price = $special_price;
						$tax_class_id = $p['tax_class_id'];
					} elseif ($price >= $p['price'])
					{
						$price = $p['price'];
						$tax_class_id = $p['tax_class_id'];
					}
				}
			}
		}
		return array('price' => $price == PHP_INT_MAX ? 0 : $price, 'tax_class_id' => $tax_class_id);
	}
	public function getLanguages()
	{ //fix for bug https://github.com/opencart/issues/4825
		$language_data = array();
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX .
			"language WHERE status = '1' ORDER BY sort_order, name");
		foreach ($query->rows as $result)
		{
			$language_data[$result['code']] = array(
				'language_id' => $result['language_id'],
				'name' => $result['name'],
				'code' => $result['code'],
				'locale' => $result['locale'],
				'image' => $result['image'],
				'directory' => $result['directory'],
				'sort_order' => $result['sort_order'],
				'status' => $result['status']);
		}
		return $language_data;
	}
}
