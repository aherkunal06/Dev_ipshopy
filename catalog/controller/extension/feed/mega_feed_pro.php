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
//ini_set("display_errors", 1);
//error_reporting (E_ALL);
class ControllerExtensionFeedMegaFeedPro extends Controller
{
	const MIN_IMAGE_HEIGHT = 50;
	const MIN_IMAGE_WIDTH = 50;
	const MAX_IMAGE_HEIGHT = 4096;
	const MAX_IMAGE_WIDTH = 4096;
	const MAX_IMAGE_FILESIZE = 2000000; // in Bytes
	private $extension_path = 'feed/';
	private $extension_name = 'mega_feed_pro';
	private $extension_fullname = 'feed_mega_feed_pro';
	private $extension_type = 'feed';
	private $user_currency;
	private $default_store_currency;
	private $zone_id;
	private $country_id;
	private $base_url;
	private $is_https;
	private $has_seo_url;
	private $stores = array();
	private $model = array();
	private $attrs = array();
	private $languages = array();
	private $time = 0;
	private $extension_status;
	private $feed_hash;
	private $limit;
	private $offset;
	private $items_count;
	private $action;
	public function index()
	{
		$this->time = new DateTime('now');
		//load mega libraries
		$this->registry->set('mega', new Mega($this->registry));
		$mega = $this->mega;
		//load models
		$this->loadModels();
		//auto update of extension db tables
		$this->mega->dbmodel->needUpdate();
		//GET atributes
		$this->attrs = array();
		foreach ($_GET as $key => $value)
		{
			$this->attrs[$key] = htmlspecialchars($value);
		}
		//extension status
		$this->extension_status = $this->mega->isActive();
		//load global settings
		$this->is_https = $this->config->get('config_secure');
		$this->has_seo_url = $this->config->get('config_seo_url');
		$this->base_url = $this->is_https ? HTTPS_SERVER : HTTP_SERVER;
		$this->default_store_currency = $this->config->get('config_currency');
		//load stores
		$this->getStores();
		//validate security token
		$this->validateSecurityToken();
		try
		{
			// choose action based on URL parameter "a" (action)
			if (isset($this->attrs['a']) && $this->attrs['a'] == 'log')
			{ // log
				$this->showLog(isset($this->attrs['s']) ? $this->attrs['s'] : '');
			} elseif ($this->extension_status && isset($this->attrs['a']))
			{
				$this->setFeedHash();
				$this->validateFeedParams();
				if ($this->attrs['a'] == 'pf')
				{ // product feed
					$this->action = 'product_generator';
					$this->createProductFeed();
				} elseif ($this->attrs['a'] == 'cf')
				{ // category feed
					$this->action = 'category_generator';
					$this->createCategoryFeed();
				} elseif ($this->attrs['a'] == 'haf')
				{ // heureka availability feed
					$this->action = 'heureka_availability_generator';
					$this->createHeurekaAvailabilityFeed();
					exit;
				} elseif ($this->attrs['a'] == 'af')
				{ // analytics feed
					$this->action = 'analytics_generator';
					// Not supported in current version
					$this->model['logger']->log('Error: Analytics feed is not supported in this version. parameter a ==',
						$this->attrs['a']);
					exit;
				}
			} else
			{
				if ($this->extension_status)
				{
					// missing or incorrect params
					$this->model['logger']->log('error', 'missing_or_incorrect_params');
				} else
				{
					$this->model['logger']->log('warning', 'extension_is_disabled');
				}
				echo - 1;
				exit;
			}
		}
		catch (exception $e)
		{
			$this->model['logger']->log('exception', $e->getMessage());
		}
	}
	private function createProductFeed()
	{
		// Start timer
		$this->model['logger']->log($this->action . '_start', array(), $this->attrs['s'],
			$this->attrs['l'], $this->attrs['c']);
		//product limit
		$this->limit = $this->config->get($this->extension_fullname .
			'_product_feed_limit');
		$state = $this->model['generator']->getCurrentState($this->feed_hash);
		//product items count
		$this->items_count = !empty($state) ? $state['items_count'] : 0;
		//product offset
		$this->offset = !empty($state) && $state['current_offset'] != $this->items_count ?
			$state['current_offset'] : 0;
		//set filter
		$filter = array();
		if (is_numeric($this->limit))
		{
			$filter = array('start' => $this->offset, 'limit' => $this->limit);
		}
		$this->items_count = $this->countAllProducts();
		$products = $this->model['product']->getProducts($filter);
		$output = "";
		if (!is_numeric($this->limit) || $this->offset == 0)
		{
			$output .= '<?xml version="1.0" encoding="utf-8"?>';
			$output .= '<CHANNEL xmlns="http://www.newcms.hu/ns/1.8">';
			$output .= '<GENERATOR>mega.feed.pro.opencart.v.1.2.7.' . str_replace('.', '_',
				MEGA_VERSION) . '</GENERATOR>';
			$output .= '<LINK>' . $this->getStoreUrl() . '</LINK>';
		}
		foreach ($products as $product)
		{
			// If special price is set, use it instead of regular price:
			$options_data = $this->generate_options_data($product['product_id'], $product['name']);
			foreach ($options_data as $option_data)
			{
				$output .= '<ITEM>';
				$output .= '<ITEM_ID>' . $option_data['id'] . '</ITEM_ID>';
				$output .= '<NAME_EXACT><![CDATA[' . $option_data['name'] . ']]></NAME_EXACT>';
				$output .= '<DESCRIPTION><![CDATA[' . $this->plainText($product['description']) .
					']]></DESCRIPTION>';
				$output .= '<URL><![CDATA[' . $this->replaceStoreURL($this->createProductURL($product['product_id'],
					"")) . ']]></URL>';
				//main image
				if ($product['image'])
				{
					$output .= '<IMAGE><![CDATA[' . $this->getImageURL($product['image']) .
						']]></IMAGE>';
				} else
				{
					$output .= '<IMAGE></IMAGE>';
				}
				//alternative images
				$images = $this->model['product']->getProductImages($product['product_id']);
				foreach ($images as $img)
				{
					$output .= '<IMAGE_ALTERNATIVE><![CDATA[' . $this->getImageURL($img['image']) .
						']]></IMAGE_ALTERNATIVE>';
				}
				//categories
				$categories = $this->model['product']->getCategories($product['product_id']);
				foreach ($categories as $cat)
				{
					$output .= '<CATEGORY><![CDATA[' . $this->getFullCategory($cat['category_id']) .
						']]></CATEGORY>';
				}
				$output .= '<PRICE>' . $this->getPrice($product['price'], $product['special'], $option_data['delta_price']) .
					'</PRICE>';
				$output .= '<PRICE_VAT>' . $this->getPrice($product['price'], $product['special'],
					$option_data['delta_price'], $product['tax_class_id']) . '</PRICE_VAT>';
				$output .= '<CURRENCY>' . $this->user_currency . '</CURRENCY>';
				$output .= '<VAT>' . $this->getTaxRate($product['tax_class_id']) . '</VAT>';
				$output .= '<EAN>' . $product['ean'] . '</EAN>';
				$output .= '<ISBN>' . $product['isbn'] . '</ISBN>';
				$output .= '<PRODUCTNO><![CDATA[' . $product['model'] . ']]></PRODUCTNO>';
				$output .= '<AVAILABILITY>' . $this->getAvailability($product['quantity'], $product['stock_status']) .
					'</AVAILABILITY>';
				$output .= '<DELIVERY_DAYS>' . $this->getDeliveryDays($product['quantity'], $product['stock_status'],
					$product['date_available']) . '</DELIVERY_DAYS>';
				$output .= '<SHIPPING_SIZE><![CDATA[' . $product['length'] . ' x ' . $product['width'] .
					' x ' . $product['height'] . ' ' . $this->model['main']->getLengthClass($product['length_class_id'],
					$this->languages[$this->attrs['l']]['language_id']) . ']]></SHIPPING_SIZE>';
				$output .= '<SHIPPING_WEIGHT><![CDATA[' . ($product['weight'] + $option_data['delta_weight']) .
					' ' . $this->model['main']->getWeightClass($product['weight_class_id'], $this->languages[$this->attrs['l']]['language_id']) .
					']]></SHIPPING_WEIGHT>';
				$output .= '<PRODUCER><![CDATA[' . $product['manufacturer'] . ']]></PRODUCER>';
				$output .= '<ITEMGROUP_ID>' . $product['product_id'] . '</ITEMGROUP_ID>';
				if (!empty($option_data['property']))
				{
					for ($i = 0; $i < count($option_data['property']); $i++)
					{
						$output .= '<PARAM>';
						$output .= '<NAME><![CDATA[' . $option_data['property'][$i] . ']]></NAME>';
						$output .= '<VALUE><![CDATA[' . $option_data['property_value'][$i] .
							']]></VALUE>';
						$output .= '</PARAM>';
					}
				}
				$output .= '<STOCK_QUANTITY>' . $this->stockQuantity($product['quantity'], $option_data['quantity']) .
					'</STOCK_QUANTITY>';
				$output .= '</ITEM>';
			}
		}
		if (!is_numeric($this->limit) || (($this->limit + $this->offset) >= $this->items_count))
		{
			$output .= '</CHANNEL>';
		}
		$filename = 'pf_' . $this->feed_hash;
		$this->createFeed($filename, $output);
		exit;
	}
	private function createCategoryFeed()
	{
		// Start timer
		$this->model['logger']->log($this->action . '_start', array(), $this->attrs['s'],
			$this->attrs['l'], $this->attrs['c']);
		//category limit
		$this->limit = $this->config->get($this->extension_fullname .
			'_category_feed_limit');
		$state = $this->model['generator']->getCurrentState($this->feed_hash);
		//category items count
		$this->items_count = !empty($state) ? $state['items_count'] : 0;
		//category offset
		$this->offset = !empty($state) && $state['current_offset'] != $this->items_count ?
			$state['current_offset'] : 0;
		//set filter
		$filter = array();
		if (is_numeric($this->limit))
		{
			$filter = array('start' => $this->offset, 'limit' => $this->limit);
		}
		$this->items_count = count($this->model['main']->getAllCategories($this->languages[$this->attrs['l']]['language_id']),
			$this->attrs['s']);
		$categories = $this->model['main']->getAllCategories($this->languages[$this->attrs['l']]['language_id'],
			$this->attrs['s'], $filter);
		$output = "";
		if (!is_numeric($this->limit) || $this->offset == 0)
		{
			$output .= '<?xml version="1.0" encoding="utf-8"?>';
			$output .= '<CHANNEL xmlns="http://www.newcms.hu/ns/category/1.2.7">';
			$output .= '<GENERATOR>mega.feed.pro.opencart.v.1.2.7.' . str_replace('.', '_',
				MEGA_VERSION) . '</GENERATOR>';
			$output .= '<LINK>' . $this->getStoreUrl() . '</LINK>';
		}
		foreach ($categories as $category)
		{
			$output .= '<ITEM>';
			$output .= '<CATEGORY_ID>' . $category['category_id'] . '</CATEGORY_ID>';
			$output .= '<CATEGORY_NAME><![CDATA[' . $category['name'] .
				']]></CATEGORY_NAME>';
			$output .= '<CATEGORY><![CDATA[' . $this->getFullCategory($category['category_id'],
				' | ') . ']]></CATEGORY>';
			$output .= '<CATEGORY_URL><![CDATA[' . $this->replaceStoreURL($this->createCategoryURL
				($this->getFullCategoryPath($category['category_id']))) . ']]></CATEGORY_URL>';
			$output .= '<CATEGORY_QUANTITY>' . $this->getItemQuantityInCategory($category['category_id']) .
				'</CATEGORY_QUANTITY>';
			$output .= '<CATEGORY_DESCRIPTION><![CDATA[' . $this->plainText($category['description']) .
				']]></CATEGORY_DESCRIPTION>';
			$output .= '<CATEGORY_MIN_PRICE_VAT>' . $this->getItemMinPriceInCategory($category['category_id']) .
				'</CATEGORY_MIN_PRICE_VAT>';
			$output .= '<CATEGORY_MAX_PRICE_VAT>' . $this->getItemMaxPriceInCategory($category['category_id']) .
				'</CATEGORY_MAX_PRICE_VAT>';
			$output .= '</ITEM>';
		}
		if (!is_numeric($this->limit) || (($this->limit + $this->offset) >= $this->items_count))
		{
			$output .= '</CHANNEL>';
		}
		$filename = 'cf_' . $this->feed_hash;
		$this->createFeed($filename, $output);
		exit;
	}
	private function createHeurekaAvailabilityFeed()
	{
		// Start timer
		$this->model['logger']->log($this->action . '_start', array(), $this->attrs['s']);
		//heureka availability limit
		$this->limit = $this->config->get($this->extension_fullname .
			'_heureka_availability_feed_limit');
		$state = $this->model['generator']->getCurrentState($this->feed_hash);
		//heureka availability items count
		$this->items_count = !empty($state) ? $state['items_count'] : 0;
		//heureka availability offset
		$this->offset = !empty($state) && $state['current_offset'] != $this->items_count ?
			$state['current_offset'] : 0;
		//set filter
		$filter = array();
		if (is_numeric($this->limit))
		{
			$filter = array('start' => $this->offset, 'limit' => $this->limit);
		}
		$this->items_count = $this->countAllProducts();
		$products = $this->model['product']->getProducts($filter);
		$output = "";
		if (!is_numeric($this->limit) || $this->offset == 0)
		{
			$output .= '<?xml version="1.0" encoding="utf-8"?>';
			$output .= '<item_list>';
		}
		foreach ($products as $product)
		{
			// If special price is set, use it instead of regular price:
			$options_data = $this->generate_options_data($product['product_id'], $product['name']);
			foreach ($options_data as $option_data)
			{
				$quantity = $this->stockQuantity($product['quantity'], $option_data['quantity']);
				if ($quantity > 0)
				{
					$output .= '<item id="' . $option_data['id'] . '">';
					$output .= '<stock_quantity>' . $quantity . '</stock_quantity>';
					$output .= '</item>';
				}
			}
		}
		if (!is_numeric($this->limit) || (($this->limit + $this->offset) >= $this->items_count))
		{
			$output .= '</item_list>';
		}
		$filename = 'haf_' . $this->feed_hash;
		$this->createFeed($filename, $output);
		exit;
	}
	private function loadModels()
	{
		$this->load->model('setting/setting');
		$this->load->model('catalog/product');
		$this->load->model('catalog/category');
		$this->load->model('tool/image');
		$this->load->model('localisation/zone');
		$this->load->model('localisation/country');
		$this->load->model('localisation/currency');
		$this->load->model('localisation/language');
		$this->load->model('setting/store');
		$this->load->model('extension/' . $this->extension_path . $this->extension_name);
		$this->load->model('extension/' . $this->extension_path . $this->extension_name .
			'_logger');
		$this->load->model('extension/' . $this->extension_path . $this->extension_name .
			'_generator');
		//custom models
		$model_name = "model_extension_" . $this->extension_type . "_" . $this->extension_name;
		$model_logger = "model_extension_" . $this->extension_type . "_" . $this->extension_name .
			'_logger';
		$model_generator = "model_extension_" . $this->extension_type . "_" . $this->extension_name .
			'_generator';
		$this->model['settings'] = $this->model_setting_setting;
		$this->model['product'] = $this->model_catalog_product;
		$this->model['category'] = $this->model_catalog_category;
		$this->model['image'] = $this->model_tool_image;
		$this->model['zone'] = $this->model_localisation_zone;
		$this->model['country'] = $this->model_localisation_country;
		$this->model['currency'] = $this->model_localisation_currency;
		$this->model['language'] = $this->model_localisation_language;
		$this->model['store'] = $this->model_setting_store;
		$this->model['main'] = $this->{$model_name};
		$this->model['logger'] = $this->{$model_logger};
		$this->model['generator'] = $this->{$model_generator};
	}
	private function validateSecurityToken()
	{
		//check security token
		if (!isset($this->attrs['h']))
		{
			$this->model['logger']->log('invalid_token', array('token' => $this->attrs['h']),
				$this->attrs['s'], $this->attrs['l'], $this->attrs['c']);
			echo - 2;
			exit;
		} elseif (isset($this->attrs['a']) && isset($this->attrs['s']) && isset($this->attrs['l']) &&
		isset($this->attrs['c']))
		{
			if ($this->attrs['a'] == 'pf' && md5($this->model['main']->getMegaToken() .
				'_pf_' . $this->attrs['s'] . '_' . $this->attrs['l'] . '_' . $this->attrs['c']) !=
				$this->attrs['h'])
			{ //check product feed hash token
				echo - 2;
				exit;
			} elseif ($this->attrs['a'] == 'cf' && md5($this->model['main']->getMegaToken() .
			'_cf_' . $this->attrs['s'] . '_' . $this->attrs['l'] . '_' . $this->attrs['c']) !=
				$this->attrs['h'])
			{ //check category feed hash token
				echo - 2;
				exit;
			}
		} elseif (isset($this->attrs['a']) && isset($this->attrs['s']))
		{ //check store log hash token
			if ($this->attrs['a'] == 'log' && md5($this->model['main']->getMegaToken() .
				'_log_' . $this->attrs['s']) != $this->attrs['h'])
			{
				echo - 2;
				exit;
			}
		} elseif (isset($this->attrs['a']))
		{ //check full log hash token
			if ($this->attrs['a'] == 'log' && $this->model['main']->getMegaToken() != $this->attrs['h'])
			{
				echo - 2;
				exit;
			}
		}
	}
	private function validateFeedParams()
	{
		// check if store id passed through parameter is valid:
		if (isset($this->attrs['s']))
		{
			if ($this->attrs['s'] != 0)
			{
				// Check if the store_id is valid (we do not need to check store_id=0, since that is default one):
				$store_id_found = false;
				if (array_key_exists($this->attrs['s'], $this->stores))
				{
					$store_id_found = true;
				}
				if (!$store_id_found)
				{
					$this->model['logger']->log('catalog/controller - Error: Invalid store_id: ', $this->attrs['s'],
						$this->attrs['l'], $this->attrs['c']);
					exit;
				}
			}
		} else
		{
			$this->model['logger']->log('catalog/controller - Error: Failed reading of store_id from URL.',
				'', $this->attrs['s'], $this->attrs['l'], $this->attrs['c']);
			exit;
		}
		if (isset($this->attrs['a']) && !in_array($this->attrs['a'], array('haf')))
		{
			// read parameter c (currency) to find out the desired currency of export:
			if (isset($this->attrs['c']))
			{
				$this->user_currency = strtoupper($this->attrs['c']);
				// TODO: Add a check if the currency code supplied via URL is valid
			} else
			{
				$this->model['logger']->log('Error: Failed reading of currency from URL.',
					'Parameter: c', $this->attrs['s'], $this->attrs['l']);
				exit;
			}
			// Change the language config so that model returns product data in correct language:
			if (isset($this->attrs['l']))
			{
				$this->languages = $this->model['main']->getLanguages();
				if (isset($this->languages[$this->attrs['l']]))
				{
					$this->config->set('config_language_id', $this->languages[$this->attrs['l']]['language_id']);
				}
			} else
			{
				$this->model['logger']->log('Error: Failed reading of language from URL.',
					'Parameter: l', $this->attrs['s'], $this->attrs['l'], $this->attrs['c']);
				exit;
			}
		}
	}
	private function setFeedHash()
	{
		$this->feed_hash = md5((isset($this->attrs['a']) ? $this->attrs['a'] : '') . '_' .
			(isset($this->attrs['s']) ? $this->attrs['s'] : '') . (isset($this->attrs['l']) ?
			'_' . $this->attrs['l'] : '') . (isset($this->attrs['c']) ? '_' . $this->attrs['c'] :
			''));
	}
	private function createFeed($feed_name, $data)
	{
		$root_dir = DIR_APPLICATION . '../mega/';
		$tmp_dir = DIR_APPLICATION . '../mega/tmp/';
		//create root dir
		if (!file_exists($root_dir))
		{
			if (!mkdir($root_dir, 0755, true))
			{
				$this->model['logger']->log('error', 'failed to create mega folder');
			} else
			{
				chmod($root_dir, 0755);
			}
		}
		//create temp dir
		if (is_numeric($this->limit))
		{
			if (!file_exists($tmp_dir))
			{
				if (!mkdir($tmp_dir, 0755, true))
				{
					$this->model['logger']->log('error', 'failed to create mega temp folder');
				} else
				{
					chmod($tmp_dir, 0755);
				}
			}
		}
		$is_final_feed = false;
		$is_feed_chunk = false;
		if (!is_numeric($this->limit))
		{
			$feed_path = $root_dir . $feed_name . '.xml';
			@file_put_contents($feed_path, $data);
			if (file_exists($feed_path))
			{
				chmod($feed_path, 0755);
				$is_final_feed = true;
			}
		} else
		{
			//create temporary feed chunk
			$temp_feed_path = $tmp_dir . $this->offset . '_' . $feed_name . '.mer';
			@file_put_contents($temp_feed_path, $data);
			if (file_exists($temp_feed_path))
			{
				chmod($temp_feed_path, 0755);
				$is_feed_chunk = true;
			}
			// echo "limit: " . $this->limit . "<br/>";
			// echo "offset: " . $this->offset . "<br/>";
			// echo "count: " . $this->items_count . "<br/>";
			//if all feed chunk files were generated
			if (($this->limit + $this->offset) >= $this->items_count)
			{
				//load all feed chunk files
				$tmp_files = glob($tmp_dir . '*_' . $feed_name . '.mer');
				// print_r($tmp_files);
				if (!empty($tmp_files))
				{
					$files = array();
					//sort chunks by name prefix
					foreach ($tmp_files as $f)
					{
						$filename_parts = explode("/", $f);
						$name = explode("_", end($filename_parts));
						$files[$name[0]] = $f;
					}
					ksort($files, SORT_NUMERIC);
					// print_r($files);
					//merge chunks to final feed file
					$feed_path = $root_dir . $feed_name . '.xml';
					// echo "dest: " . $feed_path . "<br/>";
					$dest_file = fopen($feed_path, "w");
					foreach ($files as $f)
					{
						// echo "chunk: " . $f . "<br/>";
						$chunk_file = fopen($f, "r");
						$data = fgets($chunk_file);
						while ($data !== false)
						{
							fputs($dest_file, $data);
							$data = fgets($chunk_file);
						}
						fclose($chunk_file);
					}
					fclose($dest_file);
					chmod($feed_path, 0755);
					$is_final_feed = true;
					//remove temp files
					foreach ($files as $f)
					{
						if (file_exists($f))
						{
							unlink($f);
						}
					}
				}
			}
		}
		$final_time = new DateTime('now');
		if (!$is_final_feed && !$is_feed_chunk)
		{ //error during xml/chunk creation
			$this->model['logger']->log($this->action . '_finished', array(
				'limit' => $this->limit,
				'offset' => $this->offset,
				'items_count' => $this->items_count,
				'status' => 'ERR',
				'duration' => $final_time->diff($this->time)->s), $this->attrs['s'], isset($this->attrs['l']) ?
				$this->attrs['l'] : null, isset($this->attrs['c']) ? $this->attrs['c'] : null);
			$args = array(
				'change_date' => date('Y-m-d H:i:s', $final_time->getTimestamp()),
				'last_creation_date' => null,
				'current_offset' => !is_numeric($this->limit) || ($this->limit + $this->offset) >
					$this->items_count ? $this->items_count : ($this->limit + $this->offset),
				'items_count' => $this->items_count,
				'status' => -1);
			$this->model['generator']->update($this->feed_hash, $args);
			echo - 1;
		} else
		{
			$this->model['logger']->log($this->action . '_finished', array(
				'limit' => $this->limit,
				'offset' => $this->offset,
				'items_count' => $this->items_count,
				'status' => 'OK',
				'duration' => $final_time->diff($this->time)->s), $this->attrs['s'], isset($this->attrs['l']) ?
				$this->attrs['l'] : null, isset($this->attrs['c']) ? $this->attrs['c'] : null);
			$args = array(
				'change_date' => date('Y-m-d H:i:s', $final_time->getTimestamp()),
				'last_creation_date' => $is_final_feed ? date('Y-m-d H:i:s', $final_time->getTimestamp
					()) : null,
				'current_offset' => !is_numeric($this->limit) || ($this->limit + $this->offset) >
					$this->items_count ? $this->items_count : ($this->limit + $this->offset),
				'items_count' => $this->items_count,
				'status' => 1);
			$this->model['generator']->update($this->feed_hash, $args);
			echo 1;
		}
		exit;
	}
	public function showLog($store_id = '')
	{
		header('Content-Type: text/plain');
		$params = array();
		if ($store_id != '')
		{
			$params = array('store_id' => array('value' => $store_id, 'type' => 'int'));
		}
		$logs = $this->model['logger']->getLogs($params);
		$output = '';
		foreach ($logs as $log)
		{
			$output .= '[' . $log['log_date'] . '][store id: ' . $log['store_id'] . ($log['lang_code'] !=
				'' ? ', lang: ' . $log['lang_code'] : '') . ($log['currency'] != '' ?
				', currency: ' . $log['currency'] : '') . '] ' . $log['log_label'] . ' ' . $log['log_msg'] .
				PHP_EOL;
		}
		echo $output;
		exit;
	}
	/******* STORE HELPERS *********/
	private function replaceStoreURL($orig_url)
	{
		if (MEGA_DEBUG_MODE)
		{
			$this->model['logger']->log('debug', 'replace_url - input: url => ' . $orig_url .
				', store => ' . $this->attrs['s']);
		}
		// For not default stores replace domain with what is stored in store url for that store,
		// as it is not done automatically in older versions of OpenCart
		if ($this->attrs['s'] != 0)
		{
			// Check if the store exists in $stores
			foreach ($this->stores as $index => $store)
			{
				if ($this->attrs['s'] == $store['store_id'])
				{
					break;
				}
			}
			// parse and replace domain part of orig_url for store url or store's ssl url:
			$elements = explode('/', $orig_url);
			$new_url = str_replace($elements[0] . '//' . $elements[2] . '/', $this->stores[$index]['url'],
				$orig_url);
			if (MEGA_DEBUG_MODE)
			{
				$this->model['logger']->log('debug', 'replace_url - output: ' . $new_url);
			}
			return $new_url;
		} else
		{
			if (MEGA_DEBUG_MODE)
			{
				$this->model['logger']->log('debug', 'replace_url - output: ' . $orig_url);
			}
			return $orig_url;
		}
	}
	private function getStoreUrl()
	{
		foreach ($this->stores as $index => $store)
		{
			if (in_array($this->attrs['s'], $store))
			{
				break;
			}
		}
		return !empty($this->stores) && isset($this->attrs['s']) && $this->attrs['s'] !=
			0 ? $this->stores[$index]['url'] : $this->base_url;
	}
	private function getStores()
	{
		//get stores
		$stores = $this->model['store']->getStores();
		$this->stores[] = array( //default store
			'store_id' => 0,
			'name' => $this->config->get('config_name'),
			'url' => isset($this->request->server['HTTPS']) && (($this->request->server['HTTPS'] ==
				'on') || ($this->request->server['HTTPS'] == '1')) ? HTTPS_SERVER : HTTP_SERVER,
			'ssl' => isset($this->request->server['HTTPS']) && (($this->request->server['HTTPS'] ==
				'on') || ($this->request->server['HTTPS'] == '1')) ? 1 : 0);
		foreach ($stores as $store)
		{
			$this->stores[] = array(
				'store_id' => $store['store_id'],
				'name' => $store['name'],
				'url' => !empty($store['ssl']) ? $store['ssl'] : $store['url'],
				'ssl' => empty($store['ssl']) ? 0 : 1);
		}
		if (MEGA_DEBUG_MODE)
		{
			$this->model['logger']->log('debug - store list', $this->stores);
		}
	}
	/******* PRODUCT HELPERS *********/
	private function countAllProducts()
	{
		$products = $this->model['product']->getProducts();
		$count = 0;
		foreach ($products as $p)
		{
			$options = $this->model['product']->getProductOptions($p['product_id']);
			if (!empty($options))
			{
				$values = array();
				foreach ($options as $o)
				{
					if (count($o['product_option_value']) > 0)
					{
						$values[] = count($o['product_option_value']);
					}
				}
				$count += array_product($values);
			} else
			{
				$count += 1;
			}
		}
		return $count;
	}
	private function generate_options_data($product_id, $name)
	{
		$options = $this->model['product']->getProductOptions($product_id);
		$output_array = array();
		if (!empty($options))
		{
			$input_array = array();
			$input_array[] = array(
				"id" => $product_id,
				"name" => $name,
				"delta_price" => 0,
				"delta_weight" => 0,
				'property' => array(),
				'property_value' => array(),
				'quantity' => array());
			foreach ($options as $option)
			{
				if (!empty($option['product_option_value']))
				{
					$output_array = array();
					//add product without options, if option isn't required
					if (isset($option['required']) && $option['required'] == 0)
					{
						$output_array[] = array(
							"id" => $product_id,
							"name" => $name,
							"delta_price" => 0,
							"delta_weight" => 0,
							'property' => array(),
							'property_value' => array(),
							'quantity' => array());
					}
					foreach ($input_array as $input_array_member)
					{
						if ($option['type'] == 'checkbox')
						{ //TO DO all variants for checkbox
							/*$start_array = $input_array_member;

							* foreach ($option['product_option_value'] as $product_option_value_iterator1) {

							* $this->generate_option($option, $product_option_value_iterator1, $input_array_member, $output_array);

							* foreach ($option['product_option_value'] as $product_option_value_iterator2) {

							* if($product_option_value_iterator1['product_option_value_id'] != $product_option_value_iterator2['product_option_value_id'] ) {

							* $this->generate_option($option, $product_option_value_iterator2, $input_array_member, $output_array);

							* $input_array_member = end($output_array);

							* }

							* }

							* $input_array_member = $start_array;

							* }*/
							foreach ($option['product_option_value'] as $product_option_value_iterator)
							{
								$this->generate_option($option, $product_option_value_iterator, $input_array_member,
									$output_array);
							}
						} else
						{
							foreach ($option['product_option_value'] as $product_option_value_iterator)
							{
								$this->generate_option($option, $product_option_value_iterator, $input_array_member,
									$output_array);
							}
						}
					}
					$input_array = $output_array;
				}
			}
			if (empty($output_array))
			{
				// in case there are no options just copy data to output_array so that we can iterate just through that and keep the code common
				$output_array[] = array(
					"id" => $product_id,
					"name" => $name,
					"delta_price" => 0,
					"delta_weight" => 0,
					'property' => array(),
					'property_value' => array(),
					'quantity' => array());
			}
		} else
		{
			// in case there are no options just copy data to output_array so that we can iterate just through that and keep the code common
			$output_array[] = array(
				"id" => $product_id,
				"name" => $name,
				"delta_price" => 0,
				"delta_weight" => 0,
				'property' => array(),
				'property_value' => array(),
				'quantity' => array());
		}
		return $output_array;
	}
	private function generate_option($option, $product_option_value_iterator, $input_array_member,
		&$output_array)
	{
		$temp_id = $input_array_member['id'] . '-' . $option['product_option_id'] . '_' .
			$product_option_value_iterator['product_option_value_id'];
		$temp_name = $input_array_member['name'] . ' ' . $product_option_value_iterator['name'];
		$properties = $input_array_member['property'];
		$properties[] = $option['name'];
		$properties_value = $input_array_member['property_value'];
		$properties_value[] = $product_option_value_iterator['name'];
		$quantity = $input_array_member['quantity'];
		$quantity[] = $product_option_value_iterator['quantity'];
		if ($product_option_value_iterator['price_prefix'] == "+")
		{
			$temp_price = $input_array_member['delta_price'] + $product_option_value_iterator['price'];
		} else
		{
			$temp_price = $input_array_member['delta_price'] - $product_option_value_iterator['price'];
		}
		if ($product_option_value_iterator['weight_prefix'] == "+")
		{
			$temp_weight = $input_array_member['delta_weight'] + $product_option_value_iterator['weight'];
		} else
		{
			$temp_weight = $input_array_member['delta_weight'] - $product_option_value_iterator['weight'];
		}
		array_push($output_array, array(
			"id" => $temp_id,
			"name" => $temp_name,
			"delta_price" => $temp_price,
			"delta_weight" => $temp_weight,
			'property' => $properties,
			'property_value' => $properties_value,
			'quantity' => $quantity));
	}
	private function createProductURL($product_id, $url_param = "")
	{
		$url = $this->url->link('product/product', 'product_id=' . $product_id, $this->is_https);
		if ($this->has_seo_url)
		{
			return str_replace("&amp;", "&", $url) . ($url_param != "" ? "?" . str_replace("?",
				"", $url_param) : "");
		} else
		{
			return str_replace("&amp;", "&", $url) . ($url_param != "" ? "&" . str_replace("?",
				"", $url_param) : "");
		}
	}
	private function plainText($string)
	{
		return strip_tags(html_entity_decode($string));
	}
	private function hasImageValidSize($path)
	{
		if (file_exists($path))
		{
			list($width, $height) = @getimagesize($path);
			$filesize = filesize($path); // in bytes
			if ($width < self::MIN_IMAGE_WIDTH && $height < self::MIN_IMAGE_HEIGHT)
			{
				return - 1; // image smaller than minimum
			}
			if ($width > self::MAX_IMAGE_WIDTH && $height > self::MAX_IMAGE_HEIGHT)
			{
				return - 2; // image bigger than maximum
			}
			if ($filesize > self::MAX_IMAGE_FILESIZE)
			{
				return - 3; // image size on disk bigger than maximum
			}
		}
		return 1;
	}
	private function getImageURL($image_path)
	{
		//resize sa obrazkov sa da riesit na urovni Mergada, nie je potrebny uz viac v plugine
		//    $ret_val = $this->hasImageValidSize(DIR_IMAGE. $image_path);
		//    if($ret_val == 1){
		//      return $this->replaceStoreURL($this->base_url . 'image/' . $image_path);
		//    }elseif($ret_val == -1){ // image smaller than minimum
		//      return $this->replaceStoreURL($this->model['image']->resize( $image_path, self::MIN_IMAGE_WIDTH, self::MIN_IMAGE_HEIGHT));
		//    }elseif($ret_val == -2){ // image bigger than maximum
		//      return $this->replaceStoreURL($this->model['image']->resize( $image_path, self::MAX_IMAGE_WIDTH, self::MAX_IMAGE_HEIGHT));
		//    }else{ //should get where when $ret_val=3; image size on disk bigger than maximum
		//      return '';
		//    }
		return $this->replaceStoreURL($this->base_url . 'image/' . $image_path);
	}
	private function getPrice($price, $special, $delta_price = 0, $tax_class_id = null)
	{
		$this->getCountyId();
		$this->getZoneId();
		$this->tax->setStoreAddress($this->country_id, $this->zone_id);
		$this->tax->setPaymentAddress($this->country_id, $this->zone_id);
		$this->tax->setShippingAddress($this->country_id, $this->zone_id);
		if ((float)$special)
		{
			$computed_price = $this->currency->convert($special + $delta_price, $this->default_store_currency,
				$this->user_currency);
		} else
		{
			$computed_price = $this->currency->convert($price + $delta_price, $this->default_store_currency,
				$this->user_currency);
		}
		$decimal_places = $this->currency->getDecimalPlace($this->user_currency);
		if (!is_numeric($decimal_places))
		{
			$decimal_places = 2;
		}
		if (!is_null($tax_class_id))
		{
			return number_format((float)$this->tax->calculate($computed_price, $tax_class_id),
				(int)$decimal_places, '.', '');
		} else
		{
			return number_format((float)$computed_price, (int)$decimal_places, '.', '');
		}
	}
	private function getTaxRate($tax_class_id)
	{
		$rates = $this->tax->getRates(0, $tax_class_id);
		$vat = 0;
		foreach ($rates as $r)
		{
			if ($r['type'] == 'P')
			{
				$vat += $r['rate'];
			}
		}
		return $vat;
	}
	private function getAvailability($quantity, $order_status)
	{
		if ($quantity > 0)
		{
			return 'in stock';
		} else
		{
			//treba doriesit pripady pre stav pre-order
			return 'out of stock';
		}
	}
	private function getDeliveryDays($quantity, $order_status, $date_available)
	{
		if ($quantity > 0)
		{
			return 0;
		} else
		{
			$now = new DateTime('now');
			$dt_available = new DateTime($date_available);
			$interval = $now->diff($dt_available);
			//treba doriesit pripady pre stav pre-order
			if ($interval->d > 0)
			{
				return $interval->d;
			}
			return '';
		}
	}
	private function getCountyId()
	{
		$map_array = array('english' => 'en-gb', 'hungary' => 'hu-hu');
		if (strpos($this->attrs['l'], '-') !== false)
		{ //for cases like 'cs-cz', 'pl-pl'
			$lang_code_parts = explode('-', $this->attrs['l']);
			if (isset($lang_code_parts[1]))
			{
				$lang_code = $lang_code_parts[1];
			} else
			{
				$lang_code = $this->attrs['l'];
			}
		} else
		{ //for cases like 'czech', 'english', 'cz', 'sk' etc.
			$key = strtolower($this->attrs['l']);
			if (isset($map_array[$key]))
			{
				$lang_code_parts = explode('-', $map_array[$key]);
				if (isset($lang_code_parts[1]))
				{
					$lang_code = $lang_code_parts[1];
				} else
				{
					$lang_code = $this->attrs['l'];
				}
			} else
			{
				$lang_code = $this->attrs['l'];
			}
		}
		$countries = $this->model['country']->getCountries();
		if (!empty($countries))
		{
			foreach ($countries as $c)
			{
				if (strtolower($c['iso_code_2']) == $lang_code)
				{
					$this->country_id = $c['country_id'];
					return;
				}
			}
		}
	}
	private function getZoneId()
	{
		$zones = $this->model['zone']->getZonesByCountryId($this->country_id);
		if (!empty($zones))
		{
			foreach ($zones as $z)
			{
				$this->zone_id = $z['zone_id'];
				return;
			}
		}
	}
	private function stockQuantity($quantity, $options_quantity)
	{
		if (!empty($options_quantity))
		{ //if product have options get quantity from option with the lowest quantity
			$options_quantity[] = $quantity;
			return min($options_quantity);
		} else
		{
			return $quantity; //quantity from product card
		}
	}
	/******* CATEGORY HELPERS *********/
	private function createCategoryURL($category, $url_param = "")
	{
		$url = $this->url->link('product/category', 'path=' . $category, $this->is_https);
		if ($this->has_seo_url)
		{
			return str_replace("&amp;", "&", $url) . ($url_param != "" ? "?" . str_replace("?",
				"", $url_param) : "");
		} else
		{
			return str_replace("&amp;", "&", $url) . ($url_param != "" ? "&" . str_replace("?",
				"", $url_param) : "");
		}
	}
	private function getFullCategory($category_id, $delimiter = " / ")
	{
		$categories = array();
		$category_data = $this->model['category']->getCategory($category_id);
		if (empty($category_data))
		{
			return "";
		}
		$categories[] = $category_data['name'];
		while (isset($category_data['parent_id']) && $category_data['parent_id'] != 0)
		{
			$category_data = $this->model['category']->getCategory($category_data['parent_id']);
			if (isset($category_data['name']))
			{
				$categories[] = $category_data['name'];
			}
		}
		return implode($delimiter, array_reverse($categories));
	}
	private function getFullCategoryPath($category_id, $delimiter = "_")
	{
		$path = array($category_id);
		$category_data = $this->model['category']->getCategory($category_id);
		while (isset($category_data['parent_id']) && $category_data['parent_id'] != 0)
		{
			$category_data = $this->model['category']->getCategory($category_data['parent_id']);
			if (isset($category_data['category_id']))
			{
				$path[] = $category_data['category_id'];
			}
		}
		return implode($delimiter, array_reverse($path));
	}
	private function getItemQuantityInCategory($category_id)
	{
		return $this->model['main']->getItemQuantityInCategories($category_id, $this->attrs['s']);
	}
	private function getItemMaxPriceInCategory($category_id)
	{
		$price_data = $this->model['main']->getItemMinMaxPriceInCategory($category_id, $this->attrs['s'],
			'MAX');
		if ($price_data['tax_class_id'] != -1)
		{
			return $this->getPrice($price_data['price'], 0, 0, $price_data['tax_class_id']);
		} else
		{
			return $this->getPrice($price_data['price'], 0, 0);
		}
	}
	private function getItemMinPriceInCategory($category_id)
	{
		$price_data = $this->model['main']->getItemMinMaxPriceInCategory($category_id, $this->attrs['s'],
			'MIN');
		if ($price_data['tax_class_id'] != -1)
		{
			return $this->getPrice($price_data['price'], 0, 0, $price_data['tax_class_id']);
		} else
		{
			return $this->getPrice($price_data['price'], 0, 0);
		}
	}
}
