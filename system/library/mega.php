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
use mega\helper;
class mega
{
	const VERSION = '1.2.7';
	const IMG_URL = 'view/image/mega/';
	const CSS_URL = 'view/stylesheet/mega_feed_pro.css';
	const JS_URL = 'view/javascript/mega_feed_pro.js';
	const TOGGLE_BUTTON_CSS_URL = 'view/stylesheet/bootstrap-toggle.min.css';
	const TOGGLE_BUTTON_JS_URL = 'view/javascript/bootstrap-toggle.min.js';
	const LOGO_URL = 'view/image/mega/newcart_oc_24_dark.png';
	const LOGO_PACK_URL = 'view/image/mega/newcart_oc_24_dark.png';
	const INFOGRAPHICS_URL = 'view/image/mega/info';
	const PLUGIN_MARKETPLACE_URL ='https://www.newcms.hu/opencart3/mega-feed-pro-opencart-extensione';
	const SIDEBAR_URL = 'https://www.newcms.hu/banner/sidebar.html';
	const BOTTOM_BANNER_URL = 'https://www.newcms.hu/banner/wide.html';
	const NEWS_WIDGET_COOKIE_LIFETIME = 60 * 60 * 24 * 30;
	const RATING_WIDGET_COOKIE_LIFETIME = 60 * 60 * 24 * 30;
	private $registry;
	private $modules = array(
		'dbmodel',
		'news',
		'glami',
		'facebook',
		'google',
		'heureka',
		'seznam',
		'ga',
		'gcr');
	private $storeid = 0;
	private $default_store_currency;
	private $settings_snippets = array();
	private $header_snippets = array();
	private $body_snippets = array();
	private $footer_snippets = array();
	private $success_snippets = array();
	private $product_snippets = array();
	private $category_snippets = array();
	private $add_to_cart_snippets = array();
	public $extension_path = 'feed/';
	public $extension_name = 'mega_feed_pro';
	public $extension_fullname = 'feed_mega_feed_pro';
	private $extension_type = 'feed';
	public function __construct($registry)
	{
		$this->registry = $registry;
		$this->storeid = $this->config->get('config_store_id');
		$this->default_store_currency = $this->config->get('config_currency');
		//set version
		if (!defined('MEGA_VERSION'))
		{
			define('MEGA_VERSION', self::VERSION);
		}
		//set logger
		$logs = in_array($this->config->get($this->extension_fullname . '_logs'), array
			("on", "1")) ? 1 : 0;
		$debug_mode = in_array($this->config->get($this->extension_fullname .
			'_debug_mode'), array("on", "1")) ? 1 : 0;
		if (!defined('MEGA_LOGGER_ENABLED'))
		{
			define('MEGA_LOGGER_ENABLED', $logs);
		}
		if (!defined('MEGA_DEBUG_MODE'))
		{
			define('MEGA_DEBUG_MODE', $debug_mode);
		}
		foreach ($this->modules as $module)
		{
			$class = '\mega\\' . ucfirst($module);
			if (in_array($module, array('dbmodel', 'news')))
			{
				$this->{$module} = new $class($registry, $this->extension_fullname, self::VERSION,
					$this->isDBCompatible());
			} else
			{
				$this->{$module} = new $class($registry, $this->extension_fullname, $this->storeid,
					$this->default_store_currency, $this->extension_path, $this->extension_name, $this->extension_type);
			}
		}
		$this->setSettingsCodeSnippets();
		$this->setHeaderCodeSnippets();
		$this->setBodyCodeSnippets();
		$this->setFooterCodeSnippets();
	}
	public function __get($name)
	{
		return $this->registry->get($name);
	}
	public function setSettingsCodeSnippets()
	{
		$this->settings_snippets[] = "<!-- Mega Pack Settings -->";
		$this->settings_snippets[] = $this->heureka->heurekaCustomerHeaderSettings();
		$this->settings_snippets[] = "<!-- ./Mega Pack Settings -->";
	}
	public function setHeaderCodeSnippets()
	{
		$this->header_snippets[] = "<!-- Mega Pack Header -->";
		$this->header_snippets[] = $this->facebook->pixelHeaderCodeSnippet();
		$this->header_snippets[] = $this->google->gtmHeaderCodeSnippet();
		$this->header_snippets[] = $this->glami->pixelHeaderCodeSnippet();
		$this->header_snippets[] = $this->google->googleAdsGlobalTagHeaderSnippet();
		$this->header_snippets[] = "<!-- ./Mega Pack Header -->";
	}
	public function setBodyCodeSnippets()
	{
		$this->body_snippets[] = "<!-- Mega Pack Body -->";
		$this->body_snippets[] = $this->google->gtmBodyCodeSnippet();
		$this->body_snippets[] = "<!-- ./Mega Pack Body -->";
	}
	public function setFooterCodeSnippets()
	{
		$this->footer_snippets[] = "<!-- Mega Pack Footer -->";
		$this->footer_snippets[] = $this->heureka->heurekaCustomerWidget();
		$this->footer_snippets[] = $this->seznam->sklikFooterCodeSnippet();
		$this->footer_snippets[] = "<!-- ./Mega Pack Footer -->";
	}
	public function setSuccessCodeSnippets($data = array())
	{
		$this->success_snippets[] = "<!-- Mega Pack Success -->";
		$this->success_snippets[] = $this->facebook->pixelSuccessCodeSnippet($data);
		$this->success_snippets[] = $this->glami->pixelSuccessCodeSnippet($data);
		$this->success_snippets[] = $this->google->gtmSuccessCodeSnippet($data);
		$this->success_snippets[] = $this->glami->reviewsSuccessCodeSnippet($data);
		$this->success_snippets[] = $this->google->googleAdsConversionSuccessCodeSnippet($data);
		$this->success_snippets[] = $this->heureka->heurekaConversionSuccessCodeSnippet($data);
		$this->success_snippets[] = $this->seznam->sklikConversionSuccessCodeSnippet($data);
		$this->success_snippets[] = $this->seznam->zboziConversionSuccessCodeSnippet($data);
		$this->success_snippets[] = "<!-- ./Mega Pack Success -->";
	}
	public function setProductCodeSnippets($data = array())
	{
		$this->product_snippets[] = "<!-- Mega Pack Product -->";
		$this->product_snippets[] = $this->facebook->pixelProductCodeSnippet($data);
		$this->product_snippets[] = $this->google->googleAdsRemarketingProductCodeSnippet($data);
		$this->product_snippets[] = $this->seznam->sklikProductCodeSnippet($data);
		$this->product_snippets[] = "<!-- ./Mega Pack Product -->";
	}
	public function setCategoryCodeSnippets($data = array())
	{
		$this->category_snippets[] = "<!-- Mega Pack Category -->";
		$this->category_snippets[] = $this->facebook->pixelCategoryCodeSnippet($data);
		$this->category_snippets[] = $this->google->googleAdsRemarketingCategoryCodeSnippet($data);
		$this->category_snippets[] = $this->seznam->sklikCategoryCodeSnippet($data);
		$this->category_snippets[] = "<!-- ./Mega Pack Category -->";
	}
	public function setAddToCartCodeSnippets($data = array())
	{
		$this->add_to_cart_snippets[] = "<!-- Mega Pack Add To Cart -->";
		$this->add_to_cart_snippets[] = $this->facebook->pixelAddToCartCodeSnippet($data);
		$this->add_to_cart_snippets[] = $this->glami->pixelAddToCartCodeSnippet($data);
		$this->add_to_cart_snippets[] = $this->google->googleAdsRemarketingAddToCartCodeSnippet($data);
		$this->add_to_cart_snippets[] = "<!-- ./Mega Pack Add To Cart -->";
	}
	public function getSettingsCodeSnippets()
	{
		return $this->settings_snippets;
	}
	public function getHeaderCodeSnippets()
	{
		return $this->header_snippets;
	}
	public function getBodyCodeSnippets()
	{
		return $this->body_snippets;
	}
	public function getFooterCodeSnippets()
	{
		return $this->footer_snippets;
	}
	public function getSuccessCodeSnippets()
	{
		return $this->success_snippets;
	}
	public function getProductCodeSnippets()
	{
		return $this->product_snippets;
	}
	public function getCategoryCodeSnippets()
	{
		return $this->category_snippets;
	}
	public function getCartCodeSnippets()
	{
		return $this->add_to_cart_snippets;
	}
	public function isDBCompatible()
	{
		$version_in_db = $this->getVersionInt($this->config->get($this->extension_fullname .
			'_version'));
		$ext_version = $this->getVersionInt(self::VERSION);
		if ((int)$version_in_db == -1 || ((int)$ext_version > (int)$version_in_db))
		{
			return false;
		} else
		{
			return true;
		}
	}
	public function getVersionInt($version_str)
	{
		return is_null($version_str) || $version_str == '' ? -1 : (int)str_replace('.',
			'', $version_str);
	}
	public function isActive()
	{
		$status = in_array($this->config->get($this->extension_fullname . '_status'),
			array("on", "1")) ? 1 : 0;
		return $status;
	}
	public static function getOptionData($product_id, $name, $options)
	{
		return Helper::getOptionData($product_id, $name, $options);
	}
	public static function transformPostDataToOptions($post_data, $product_options)
	{
		return Helper::transformPostDataToOptions($post_data, $product_options);
	}
}
