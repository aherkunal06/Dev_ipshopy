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
namespace mega;
class Ga
{ //Google Analytics
	private $registry;
	private $storeid = 0;
	private $default_store_currency;
	private $extension_fullname;
	private $extension_name;
	private $extension_path;
	private $extension_type;
	function __construct($registry, $extension_fullname, $storeid, $default_store_currency,
		$extension_path, $extension_name, $extension_type)
	{
		$this->registry = $registry;
		$this->extension_fullname = $extension_fullname;
		$this->storeid = $storeid;
		$this->default_store_currency = $default_store_currency;
		$this->extension_name = $extension_name;
		$this->extension_path = $extension_path;
		$this->extension_type = $extension_type;
	}
	public function __get($name)
	{
		return $this->registry->get($name);
	}
}
