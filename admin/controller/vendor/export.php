<?php 
set_time_limit(0);
ini_set('memory_limit','9999M');
error_reporting(-1);
class ControllervendorExport extends Controller { 
	private $error = array();
	
	public function index() {		
		$this->load->language('vendor/export');
		
		// Default opencart table field list ///
		$defaultfild=array();
		$defaultfild[]='product_id';
		$defaultfild[]='model';
		$defaultfild[]='sku';
		$defaultfild[]='upc';
		$defaultfild[]='ean';
		$defaultfild[]='jan';
		$defaultfild[]='isbn';
		$defaultfild[]='mpn';
		$defaultfild[]='location';
		$defaultfild[]='quantity';
		$defaultfild[]='stock_status_id';
		$defaultfild[]='image';
		$defaultfild[]='manufacturer_id';
		$defaultfild[]='shipping';
		$defaultfild[]='price';
		$defaultfild[]='points';
		$defaultfild[]='tax_class_id';
		$defaultfild[]='date_available';
		$defaultfild[]='weight';
		$defaultfild[]='weight_class_id';
		$defaultfild[]='length';
		$defaultfild[]='width';
		$defaultfild[]='height';
		$defaultfild[]='length_class_id';
		$defaultfild[]='subtract';
		$defaultfild[]='minimum';
		$defaultfild[]='sort_order';
		$defaultfild[]='status';
		$defaultfild[]='viewed';
		$defaultfild[]='date_added';
		$defaultfild[]='date_modified';
		
		// Default opencart table field list ///
		$this->document->setTitle($this->language->get('heading_title'));
		
		$data['heading_title'] = $this->language->get('heading_title');
		
		$data['button_export'] = $this->language->get('button_export');
		$data['button_exportoc'] = $this->language->get('button_exportoc');
		$data['entry_exportxls'] = $this->language->get('entry_exportxls');
		$data['entry_exportocxls'] = $this->language->get('entry_exportocxls');
		$data['entry_number'] = $this->language->get('entry_number');
		$data['help_number'] = $this->language->get('help_number');
		$data['entry_category'] = $this->language->get('entry_category');
		$data['entry_manufature'] = $this->language->get('entry_manufature');
		$data['entry_stores'] = $this->language->get('entry_stores');
		$data['entry_language'] = $this->language->get('entry_language');
		$data['entry_status'] = $this->language->get('entry_status');
		$data['entry_type'] = $this->language->get('entry_type');
		$data['entry_extrafiled'] = $this->language->get('entry_extrafiled');
		$data['entry_stock_status'] = $this->language->get('entry_stock_status');
		$data['entry_vendors'] = $this->language->get('entry_vendors');
		$data['text_all_vendors'] = $this->language->get('text_all_vendors');
		
		$data['text_enabled'] = $this->language->get('text_enabled');
		$data['text_disabled'] = $this->language->get('text_disabled');
		
		$data['text_yes'] = $this->language->get('text_yes');
		$data['text_no'] = $this->language->get('text_no');
		
		$data['entry_quantity'] = $this->language->get('entry_quantity');
		$data['entry_price'] = $this->language->get('entry_price');
		$data['entry_productname'] = $this->language->get('entry_productname');
		$data['entry_model'] = $this->language->get('entry_model');
		$data['entry_producturl'] = $this->language->get('entry_producturl');
		$data['entry_review'] = $this->language->get('entry_review');
		$data['user_token'] = $this->session->data['user_token'];
		
		
		$data['text_all_manufacturer'] = $this->language->get('text_all_manufacturer');
		$data['text_all_category'] = $this->language->get('text_all_category');
		$data['text_all_status'] = $this->language->get('text_all_status');
		$data['text_all_language'] = $this->language->get('text_all_language');
		$data['text_all_stores'] = $this->language->get('text_all_stores');
		$data['text_all_stockstatus'] = $this->language->get('text_all_stockstatus');
		
		if (isset($this->session->data['error'])) {
    		$data['error_warning'] = $this->session->data['error'];
    
			unset($this->session->data['error']);
 		} elseif (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
		}
		
		if (isset($this->request->get['number'])) {
			$data['number'] = $this->request->get['number'];
		} else {
			$data['number'] = '0';
		}
		
		
		$product_cont=$this->getTotalProducts();
		
		if (isset($this->request->get['end'])) {
			$data['end'] = $this->request->get['end'];
		}elseif (!empty($product_cont)) {
			$data['end'] = $product_cont;
			}
		else {
			$data['end'] = '';
		}
		
		if (isset($this->session->data['success'])) {
			$data['success'] = $this->session->data['success'];
		
			unset($this->session->data['success']);
		} else {
			$data['success'] = '';
		}
		
  		$data['breadcrumbs'] = array();

   		$data['breadcrumbs'][] = array(
       		'text'      => $this->language->get('text_home'),
			'href'      => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)    	
			
		);

   		$data['breadcrumbs'][] = array(
       		'text'      => $this->language->get('heading_title'),
			'href'      => $this->url->link('vendor/export', 'user_token=' . $this->session->data['user_token'], true)
   		);
		
		$data['restore'] = $this->url->link('vendor/export/export', 'user_token=' . $this->session->data['user_token'], true);
		
		$data['export'] = $this->url->link('vendor/export/export', 'user_token=' . $this->session->data['user_token'], true);
		$data['export1'] = $this->url->link('vendor/export/export1', 'user_token=' . $this->session->data['user_token'], true);

		$this->load->model('catalog/category');
			$data['categories'] = array();
			
		$data1 = array(
		);
		$results = $this->model_catalog_category->getCategories($data1);
	
		foreach ($results as $result) {
		
		$data['categories'][] = array(
				'category_id' => $result['category_id'],
				'name'        => $result['name'],
				'sort_order'  => $result['sort_order'],
				'selected'    => isset($this->request->post['selected']) && in_array($result['category_id'], $this->request->post['selected'])
				
			);
			
		}
		
		////////////// Custome filed //
		$query=$this->db->query("SHOW COLUMNS FROM " . DB_PREFIX . "product");
		$data['cfiled']=array();
		foreach($query->rows as $row)
		{
			if(!in_array($row['Field'],$defaultfild))
			{
			$data['cfiled'][]=$row['Field'];
			}
		}
		
		/////////// Vendor
		$this->load->model('vendor/import');
		$data['vendors']= $this->model_vendor_import->getVendors(array());
		/////////// Vendor
		
		
		////////////// Custome filed //
		/////////// Manufature
		$this->load->model('catalog/manufacturer');
		$data['product_manufacturers']= $this->model_catalog_manufacturer->getManufacturers();
		/////////// Manufature
		
		
		
		/////////// Stores
		$this->load->model('setting/store');
		$data['stores'] = $this->model_setting_store->getStores();
		/////////// Stores
		
		/////////// Stock status
		$this->load->model('localisation/stock_status');
		$data['stock_statuses'] = $this->model_localisation_stock_status->getStockStatuses();
		/////////// Stores
		
		/////////// Stock status
		$this->load->model('localisation/language');
		$data['languages'] = $this->model_localisation_language->getLanguages();
		/////////// Stores
		
		
		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');
				
		$this->response->setOutput($this->load->view('vendor/export', $data));
	}
	
	public function getTotalProducts() {
		$query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "product");

		return $query->row['total'];
	}	
	
	public function export() {
		
		require_once(DIR_SYSTEM.'/library/tmdimportexport/PHPExcel.php');
		//include  'PHPExcel.php';
		
		$data=array();
		$start=$this->request->post['number'];
		$end2=$this->request->post['end'];
		$productreview=$this->request->post['productreview'];
		if(!empty($this->request->post['category']))
		{
		$category=true;
		$categoryvalue=$this->request->post['category'];
		}
		else
		{
		$category=false;
		}
		
		if(!empty($this->request->post['manufacturer_id']))
		{
			$manufacturer_id=$this->request->post['manufacturer_id'];
		}
		else
		{
			$manufacturer_id=false;
		}
		
		if(!empty($this->request->post['stock_status_id']))
		{
			$stock_status_id=$this->request->post['stock_status_id'];
		}
		else
		{
			$stock_status_id=false;
		}
		
		if(!empty($this->request->post['status']))
		{
			$status=$this->request->post['status'];
		}
		else
		{
			$status=false;
		}
		
		if(!empty($this->request->post['language_id']))
		{
			$language_id=$this->request->post['language_id'];
		}
		else
		{
			$language_id=(int)$this->config->get('config_language_id');
		}
		
		if(!empty($this->request->post['productimage']))
		{
			$productimage=$this->request->post['productimage'];
		}
		else
		{
			$productimage=0;
		}
		
		
		$this->load->model('localisation/language');
		$languages = $this->model_localisation_language->getLanguage($language_id);
		$language_code=$languages['code'];
		
		
		
		if(!empty($this->request->post['productname']))
		{
			$productname=$this->request->post['productname'];
		}
		else
		{
			$productname=false;
		}
		
		if(!empty($this->request->post['model']))
		{
			$model=$this->request->post['model'];
		}
		else
		{
			$model=false;
		}
		
		if(!empty($this->request->post['price']))
		{
			$price=$this->request->post['price'];
		}
		else
		{
			$price=false;
		}
		if(!empty($this->request->post['price']))
		{
			$price=$this->request->post['price'];
		}
		else
		{
			$price=false;
		}
		if(!empty($this->request->post['vendor']))
		{
			$vendor=$this->request->post['vendor'];
		}
		else
		{
			$vendor=false;
		}
		if(!empty($this->request->post['price1']))
		{
			$price1=$this->request->post['price1'];
		}
		else
		{
			$price1=false;
		}
		if(!empty($this->request->post['quantity']))
		{
			$quantity=$this->request->post['quantity'];
		}
		else
		{
			$quantity=false;
		}
		
		if(!empty($this->request->post['store_id']))
		{
			$store_id=$this->request->post['store_id'];
		}
		else
		{
			$store_id=false;
		}
		$cfiled=array();
		if(isset($this->request->post['cfiled']))
		{
			$cfiled=$this->request->post['cfiled'];
		}
		$sql="SELECT * FROM `".DB_PREFIX."product` as p left join ".DB_PREFIX."product_description as pd on p.`product_id`= pd.`product_id` ";
		
		
		if($category)
			{
			$sql .=" left join ".DB_PREFIX."product_to_category as pc on pc.`product_id`= p.`product_id`   ";
			}
		if($store_id)
			{
			$sql .=" left join ".DB_PREFIX."product_to_store as pts on pts.product_id= p.product_id   ";
			}
			
		if($vendor)
			{
			$sql .=" left join ".DB_PREFIX."vendor_to_product as vtp on vtp.`product_id`= p.`product_id`   ";
		}
		
			
		
		$sql .=" where pd.language_id = '" . $language_id . "' ";
		
		if($category)
			{
			$sql .="  and  pc.category_id='".$categoryvalue."'";
			}
		
		if($manufacturer_id)
			{
			$sql .="  and  p.manufacturer_id='".$manufacturer_id."'";
			}
			
		if($stock_status_id)
			{
			$sql .="  and  p.stock_status_id='".$stock_status_id."'";
			}
			if($vendor)
			{
			$sql .="  and  vtp.vendor_id='".$vendor."'";
			}
			
		if($status)
			{
			if($status==2)
			{
			$status=0;
			}
			$sql .="  and  p.status='".$status."'";
			}
			
		if($status)
			{
			$sql .="  and  p.status='".$status."'";
			}
		if($productname)
			{
			$sql .="  and  pd.name like '".$productname."%'";
			}
		if($model)
			{
			$sql .="  and  p.model like '".$model."%'";
			}
		if($price)
			{
			$sql .="  and  p.price>='".$price."'";
			}
		if($price1)
			{
			$sql .="  and  p.price<='".$price1."'";
			}
		if($quantity)
			{
			$sql .="  and  p.quantity='".$quantity."'";
			}
		
		if($store_id)
			{
				$store_id1=$store_id;
			$sql .=" and pts.store_id='".$store_id."'";
			}




			
			
		if(isset($end2) && isset($start))
		{
			$sql .=" limit ".(int)$start.",".(int)$end2."";
			
		}
		
		
		$query=$this->db->query($sql);
		
		foreach($query->rows as $row){
		
		//////////////////////////// seo_keyword///
		$seo_keyword='';
		if(empty($store_id1))
		{
			$store_id1=0;
		}
		$query1 = $this->db->query("SELECT * FROM " . DB_PREFIX . "seo_url WHERE `query` = 'product_id=" . (int)$row['product_id'] . "' and store_id = '" . (int)$store_id1 . "' and  language_id = '" . (int)$language_id . "' limit 0,1");
		if($query1->row)
		{
		$seo_keyword=$query1->row['keyword'];
		}
		///////////////////////////////seo_keyword///////
		
		////////////////////////////////manufacturer///////////
		$manufacturer='';
		$manufacturerid='';
		$query1 = $this->db->query("SELECT * FROM " . DB_PREFIX . "manufacturer  where manufacturer_id = '" . (int)$row['manufacturer_id'] . "'");
		if($query1->row)
		{
		$manufacturerid=$query1->row['manufacturer_id'];		
		$manufacturer=$query1->row['name'];		
		}
		////////////////////////////////manufacturer///////////
		
		///////////////////////////////////// Category ////////////
		$categories='';
		$categoriesid='';
		$sq11=$this->db->query("SELECT * FROM " . DB_PREFIX . "product_to_category where product_id='".$row['product_id']."'");
		if($sq11->rows)
		{
		foreach($sq11->rows as $category_id)
		{
		$sql = "SELECT cp.category_id AS category_id, GROUP_CONCAT(cd1.name ORDER BY cp.level SEPARATOR ' &gt; ') AS name, c.parent_id, c.sort_order FROM " . DB_PREFIX . "category_path cp LEFT JOIN " . DB_PREFIX . "category c ON (cp.path_id = c.category_id) LEFT JOIN " . DB_PREFIX . "category_description cd1 ON (c.category_id = cd1.category_id) LEFT JOIN " . DB_PREFIX . "category_description cd2 ON (cp.category_id = cd2.category_id) WHERE cd1.language_id = '" . $language_id . "' AND cd2.language_id = '" . $language_id . "'";
		$sql .= " AND cd2.category_id = '" . $category_id['category_id'] . "'";
		$sql .= " GROUP BY cp.category_id ORDER BY name";
		$categoryqyery=$this->db->query($sql);
		if(isset($categoryqyery->row['name']))
		{
			$categories .=$categoryqyery->row['name'].';';
			$categoriesid .=$categoryqyery->row['category_id'].';';
		}
		}
		}
		///////////////////////////////////// Category ////////////
		
		///////////////////////////////////// Stores ////////////
		$stores='';
		$storeids='';
		$sq11=$this->db->query("SELECT * FROM " . DB_PREFIX . "product_to_store where product_id='".$row['product_id']."'");
		if($sq11->rows)
		{
		foreach($sq11->rows as $store_id)
        {
        if($store_id['store_id']==0)
        {
        $stores='default;';	
        $storeids .='0;';
        }

        $sq12=$this->db->query("SELECT * FROM " . DB_PREFIX . "store where store_id='".$store_id['store_id']."'");
        if($sq12->row)
        {
        $stores .=$sq12->row['name'].';';
        $storeids .=$store_id['store_id'].';';	
        }
        }
		}
		
		///////////////////////////////////// Stores ////////////
		
		
		///////////////////////////////////// images ////////////
		$images='';
		$sq11=$this->db->query("SELECT * FROM " . DB_PREFIX . "product_image where product_id='".$row['product_id']."'");
		if($sq11->rows)
		{
		foreach($sq11->rows as $image)
		{
			if(!empty($this->request->post['productimage']))
			{
				$images .=HTTP_CATALOG.'image/'.$image['image'].';';
			}
			else
			{
				$images .=$image['image'].';';
			}
		}
		}
		
		///////////////////////////////////// images ////////////
		
		///////////////////////////////////// Product Special ////////////
		$product_sp='';
		$sq11=$this->db->query("SELECT * FROM " . DB_PREFIX . "product_special where product_id='".$row['product_id']."' order by product_special_id DESC");
		if($sq11->rows)
		{
		foreach($sq11->rows as $sp)
		{
		$product_sp .=$sp['customer_group_id'].':'.$sp['date_start'].':'.$sp['date_end'].':'.$sp['price'].';';
		}
		}
		///////////////////////////////////// Product Special ////////////
		
		
		////////////////////////////////// Option Collection option:type
		$options='';
		$option_value_ids=array();
		$sq11=$this->db->query("SELECT * FROM " . DB_PREFIX . "product_option po left join " . DB_PREFIX . "option_description od on od.option_id=po.option_id  left join `" . DB_PREFIX . "option` o on o.option_id=po.option_id  where po.product_id='".$row['product_id']."' group by od.option_id");
		if($sq11->rows)
		{
		foreach($sq11->rows as $option)
		{
		$option['name']=str_replace('-','/',$option['name']);	
		$options .=str_replace('&amp;','&',$option['name']).':'.$option['type'].';';
		$option_value_ids[]=array('option_id'=>$option['option_id'],'name'=>$option['name']);
		}
		}
		
		
		
		////////////////////////////////// Option Collection
		////////////////////////////////// Option value collections 
		///////////////option:value1-qty-Subtract Stock-Price-Points-Weight;
		$optionvalue='';
		foreach($option_value_ids as $option)
		{
		
		$sq11=$this->db->query("SELECT * FROM " . DB_PREFIX . "product_option_value po left join " . DB_PREFIX . "option_value_description od on od.option_value_id=po.option_value_id  left join " . DB_PREFIX . "option_value ov on ov.option_value_id=po.option_value_id  where po.product_id='".$row['product_id']."' group by po.option_value_id");
		foreach($sq11->rows as $option_value)
		{
		$sq12=$this->db->query("SELECT * FROM " . DB_PREFIX . "product_option po left join " . DB_PREFIX . "option_description od on od.option_id=po.option_id  left join `" . DB_PREFIX . "option` o on o.option_id=po.option_id  where po.product_id='".$row['product_id']."' and po.option_id='".$option_value['option_id']."'");
		if(isset($option_value['name'])){
		$option['name']=str_replace('-','/',$sq12->row['name']);		
		$option_value['name']=str_replace('-','/',$option_value['name']);	
		$optionvalue .=str_replace('&amp;','&',$option['name']).':'.str_replace('&amp;','&',$option_value['name']).'-'.$option_value['quantity'].'-'.$option_value['subtract'].'-'.round($option_value['price'],2).'-'.$option_value['points'].'-'.round($option_value['weight'],2).'-'.$option_value['sort_order'].';';
		}
		}
		}
		////////////////////////////////// Option value collections
		
		////////////////////////////// Filter group name collection////////
		$filter_group='';
		$sq11=$this->db->query("SELECT * FROM " . DB_PREFIX . "product_filter po left join " . DB_PREFIX . "filter od on od.filter_id=po.filter_id left join " . DB_PREFIX . "filter_group_description fgd on fgd.filter_group_id=od.filter_group_id left join " . DB_PREFIX . "filter_group fg on fg.filter_group_id=od.filter_group_id where po.product_id='".$row['product_id']."' and fgd.language_id='".$language_id."'");
		if($sq11->rows)
		{
		foreach($sq11->rows as $filter_groups)
		{
			$filter_group .=$filter_groups['name'].':'.$filter_groups['sort_order'].';';
		}
		}
		////////////////////////////// Filter group name collection////////
		
		////////////////////////////// Filter group name collection////////
		$filter_name='';
		$sq11=$this->db->query("SELECT fgd.name as groupname,od.name as name,fgdn.sort_order FROM " . DB_PREFIX . "product_filter po left join " . DB_PREFIX . "filter_description od on od.filter_id=po.filter_id left join " . DB_PREFIX . "filter_group_description fgd on fgd.filter_group_id=od.filter_group_id left join " . DB_PREFIX . "filter fgdn on fgdn.filter_id=po.filter_id   where po.product_id='".$row['product_id']."' and fgd.language_id='".$language_id."' and od.language_id='".$language_id."'");
		if($sq11->rows)
		{
		foreach($sq11->rows as $filter_names)
		{
			$filter_name .=$filter_names['groupname'].'='.$filter_names['name'].':'.$filter_names['sort_order'].';';
		}
		}
		////////////////////////////// Filter group name collection////////
		
		////////////////////////////// Discount collection////////
		$discounts='';
		$sq11=$this->db->query("SELECT * FROM " . DB_PREFIX . "product_discount where product_id='".$row['product_id']."'");
		if($sq11->rows)
		{
		foreach($sq11->rows as $discount)
		{
			$discounts .=$discount['customer_group_id'].':'.$discount['quantity'].':'.$discount['priority'].':'.$discount['price'].':'.$discount['date_start'].':'.$discount['date_end'].';';
		}
		}
		////////////////////////////// Discount collection////////
		
		
		////////////////////////////// att collection////////
		$atts='';
		$sq11=$this->db->query("SELECT agd.name as groupname,ag.sort_order as groupsort,ad.name as attname,a.sort_order as attsort,pa.text as text from  " . DB_PREFIX . "product_attribute pa   left join " . DB_PREFIX . "attribute a on a.attribute_id=pa.attribute_id  left join " . DB_PREFIX . "attribute_description ad on ad.attribute_id=pa.attribute_id left join " . DB_PREFIX . "attribute_group ag on ag.attribute_group_id=a.attribute_group_id  left join " . DB_PREFIX . "attribute_group_description agd on agd.attribute_group_id=ag.attribute_group_id  where pa.product_id='".$row['product_id']."' and ad.language_id='".$language_id."' and agd.language_id='".$language_id."'  and ad.language_id='".$language_id."'");
		if($sq11->rows)
		{
		foreach($sq11->rows as $att)
		{
			$atts .=$att['groupname'].':'.$att['groupsort'].'='.$att['attname'].'-'.$att['text'].'-'.$att['attsort'].';';
		}
		}
		////////////////////////////// att collection////////
		
		/////////////////////////////// Related product//////
		$related='';
		$relatedid='';
		$sq11=$this->db->query("SELECT pn.model as model,pn.product_id FROM " . DB_PREFIX . "product_related  pr  left join " . DB_PREFIX . "product pn on pn.product_id=pr.related_id where pr.product_id='".$row['product_id']."'");
		if($sq11->rows)
		{
		foreach($sq11->rows as $rp)
		{
		$relatedid .=$rp['product_id'].';';
		$related .=$rp['model'].';';
		}
		}
		
		////////////////////////Main Image
			$productimage=$row['image'];
			if(!empty($this->request->post['productimage']))
			{
				$productimage=HTTP_CATALOG.'image/'.$row['image'];
			}
		////////////////
		/////////////////////////////// Related product//////
		
		
		
		////////////////////// Product Download //////////////
		$downloadids='';
		if(isset($downloadids))
		{
			$sq11=$this->db->query("SELECT download_id	 FROM " . DB_PREFIX . "product_to_download   where product_id='".$row['product_id']."'");
			if(isset($sq11->rows))
			{
				foreach($sq11->rows as $download)
				{
					
					$downloadids .=$download['download_id'].';';
					
				}
			}
		}
		$vendor_id=0;
		$vendor='';
		
		/// Check vendor information ////
		$sq11=$this->db->query("SELECT v.vendor_id,v.firstname,v.lastname FROM  " . DB_PREFIX . "vendor_to_product ptv left join  `" . DB_PREFIX . "vendor` v on v.vendor_id=ptv.vendor_id where ptv.product_id='".$row['product_id']."'");
			if(isset($sq11->rows))
			{
				foreach($sq11->rows as $vendorinfo)
				{
					
					$vendor_id=$vendorinfo['vendor_id'];
					$vendor=$vendorinfo['firstname'].' '.$vendorinfo['lastname'];
		
					
				}
			}
		/// Check vendor information ////
		
		
		////////////////////// Product Download //////////////
		
		$product= array( 
		'product_id'=>$row['product_id'],
		'language'=>$language_code,
		'stores'=>$stores,
		'storeids'=>$storeids,
		'model'=>$row['model'],
		'sku'=>$row['sku'],
		'upc'=>$row['upc'],
		'ean'=>$row['ean'],
		'jan'=>$row['jan'],
		'isbn'=>$row['isbn'],
		'mpn'=>$row['mpn'],
		'location'=>$row['location'],
		'name'=>$row['name'],
		'meta_tag_description'=>$row['meta_description'],
		'meta_tag_keywords'=>$row['meta_keyword'],
		'description'=>html_entity_decode($row['description']),
		'tag'=>$row['tag'],
		'price'=>$row['price'],
		'quantity'=>$row['quantity'],
		'minimum_quantity'=>$row['minimum'],
		'subtract_stock'=>$row['subtract'],
		'out_stockstat'=>$row['stock_status_id'],
		'require_shipping'=>$row['shipping'],
		'seo_keyword'=>$seo_keyword,
		'img_main'=>$productimage,
		'date_avail'=>$row['date_available'],
		'len_class'=>$row['length_class_id'],
		'length'=>$row['length'],
		'width'=>$row['width'],
		'height'=>$row['height'],
		'weight'=>$row['weight'],
		'weight_class'=>$row['weight_class_id'],
		'status'=>$row['status'],
		'sort_order'=>$row['sort_order'],
		'manufacturerid'=>$manufacturerid,
		'manufacturer'=>$manufacturer,
		'categoriesid'=>$categoriesid,
		'categories'=>$categories,
		'related'=>$related,
		'relatedid'=>$relatedid,
		'option'=>$options,
		'option_val'=>$optionvalue,
		'image1'=>$images,
		'product_sp'=>$product_sp,
		'tax_class'=>$row['tax_class_id'],
		'filter_group'=>$filter_group,
		'filter_name'=>$filter_name,
		'att'=>$atts,
		'discount'=>$discounts,
		'point'=>$row['points'],
		'meta_title'=>$row['meta_title'],
		'viewed'=>$row['viewed'],
		'downloadid'=>$downloadids,
		'vendor_id'=>$vendor_id,
		'vendor'=>$vendor
		);
		$productextrainfo=array();
		if(isset($cfiled))
					  {
						  foreach($cfiled as $cfile)
						  {
							   $cfile=trim($cfile);
							   $productextrainfo[$cfile]=$row[$cfile];
						  }  
        $data[]=array_merge($product,$productextrainfo)	;             
		}
		else
		{
			$data[]=$product;
		}
		
		
		
		}
		
		$objPHPExcel = new PHPExcel();

		// Set properties
		
		$objPHPExcel->getProperties()->setCreator("TMD Export");
		$objPHPExcel->getProperties()->setLastModifiedBy("TMD Export");
		$objPHPExcel->getProperties()->setTitle("Office Excel");
		$objPHPExcel->getProperties()->setSubject("Office Excel");
		$objPHPExcel->getProperties()->setDescription("Office Excel");
		$objPHPExcel->setActiveSheetIndex(0);
						$i=1;
					  $objPHPExcel->getActiveSheet()->SetCellValue('A'.$i, 'Product ID');
					  $objPHPExcel->getActiveSheet()->SetCellValue('B'.$i, 'Language');
					  $objPHPExcel->getActiveSheet()->SetCellValue('C'.$i, 'Stores');
					  $objPHPExcel->getActiveSheet()->SetCellValue('D'.$i, 'Stores id (0=Store;1=next if presemt) (1=2)');
					  $objPHPExcel->getActiveSheet()->SetCellValue('E'.$i, 'Model');
                      $objPHPExcel->getActiveSheet()->SetCellValue('F'.$i, 'SKU');
                      $objPHPExcel->getActiveSheet()->SetCellValue('G'.$i, 'UPC');
                      $objPHPExcel->getActiveSheet()->SetCellValue('H'.$i, 'EAN');
                      $objPHPExcel->getActiveSheet()->SetCellValue('I'.$i, 'JAN');
                      $objPHPExcel->getActiveSheet()->SetCellValue('J'.$i, 'ISBN');
                      $objPHPExcel->getActiveSheet()->SetCellValue('K'.$i, 'MPN');
                      $objPHPExcel->getActiveSheet()->SetCellValue('L'.$i, 'Location');
                      $objPHPExcel->getActiveSheet()->SetCellValue('M'.$i, 'Product Name');
                      $objPHPExcel->getActiveSheet()->SetCellValue('N'.$i, 'Meta Tag Description');
                      $objPHPExcel->getActiveSheet()->SetCellValue('O'.$i, 'Meta Tag Keywords');
                      $objPHPExcel->getActiveSheet()->SetCellValue('P'.$i, 'Description');
                      $objPHPExcel->getActiveSheet()->SetCellValue('Q'.$i, 'Product Tags');
                      $objPHPExcel->getActiveSheet()->SetCellValue('R'.$i, 'Price');
                      $objPHPExcel->getActiveSheet()->SetCellValue('S'.$i, 'Quantity');
                      $objPHPExcel->getActiveSheet()->SetCellValue('T'.$i, 'Minimum Quantity');
                      $objPHPExcel->getActiveSheet()->SetCellValue('U'.$i, 'Subtract Stock  (1=YES 0= NO)');
                      $objPHPExcel->getActiveSheet()->SetCellValue('V'.$i, 'Out Of Stock Status  (5=Out Of Stock , 8=Pre-Order , In Stock=7, 6=2 - 3 Days)');
                      $objPHPExcel->getActiveSheet()->SetCellValue('W'.$i, 'Requires Shipping (1=YES 0= NO)');
                      $objPHPExcel->getActiveSheet()->SetCellValue('X'.$i, 'SEO Keyword  (Must Unquie)');
                      $objPHPExcel->getActiveSheet()->SetCellValue('Y'.$i, 'Image(Main image)');
                      $objPHPExcel->getActiveSheet()->SetCellValue('Z'.$i, 'Date Available (Y-m-d)');
                      $objPHPExcel->getActiveSheet()->SetCellValue('AA'.$i, 'Length Class (1=Centimeter, 3=Inch, 2=Millimeter)');
                      $objPHPExcel->getActiveSheet()->SetCellValue('AB'.$i, 'Length');
                      $objPHPExcel->getActiveSheet()->SetCellValue('AC'.$i, 'Width');
                      $objPHPExcel->getActiveSheet()->SetCellValue('AD'.$i, 'height');
                      $objPHPExcel->getActiveSheet()->SetCellValue('AE'.$i, 'Weight');
                      $objPHPExcel->getActiveSheet()->SetCellValue('AF'.$i, 'Weight Class  (1=Kilogram,2=Gram,6=Ounce,Pound=5)');
                      $objPHPExcel->getActiveSheet()->SetCellValue('AG'.$i, 'Status (1=Enabled, 0= Disabled)');
                      $objPHPExcel->getActiveSheet()->SetCellValue('AH'.$i, 'Sort Order');
                      $objPHPExcel->getActiveSheet()->SetCellValue('AI'.$i, 'Manufacturer ID');
                      $objPHPExcel->getActiveSheet()->SetCellValue('AJ'.$i, 'Manufacturer');
                      $objPHPExcel->getActiveSheet()->SetCellValue('AK'.$i, 'Categories id');
                      $objPHPExcel->getActiveSheet()->SetCellValue('AL'.$i, 'Categories (category>subcategory; category1>subcategory1 )');
                     
                      $objPHPExcel->getActiveSheet()->SetCellValue('AM'.$i, 'Related Product ID(productid,productid)');
                      $objPHPExcel->getActiveSheet()->SetCellValue('AN'.$i, 'Related Product (model,model)');
                      $objPHPExcel->getActiveSheet()->SetCellValue('AO'.$i, 'Option (name and type) size:select;color:radio');
                      $objPHPExcel->getActiveSheet()->SetCellValue('AP'.$i, 'option:value1-qty-Subtract Stock-Price-Points-Weight;option:value1-qty-Subtract Stock-Price-Points-Weight');
                      $objPHPExcel->getActiveSheet()->SetCellValue('AQ'.$i, '(image1;image2;image3)');
                      $objPHPExcel->getActiveSheet()->SetCellValue('AR'.$i, 'Product Special price:(customer_group_id:start date:end date: special price )');
                      $objPHPExcel->getActiveSheet()->SetCellValue('AS'.$i, 'Tax Class (None=0,Taxable Goods=9,Downloadable Products=10) Rest you can make and put that ID');
                      $objPHPExcel->getActiveSheet()->SetCellValue('AT'.$i, 'Filter Group Name      (Group Name: Sort order;Group Name: Sort order)');
                      $objPHPExcel->getActiveSheet()->SetCellValue('AU'.$i, 'Filter names (group name=name:sort order;group name=name:sort order)');
                      $objPHPExcel->getActiveSheet()->SetCellValue('AV'.$i, 'Attributes (Attribute group name:sort order=atrribute name-value-sort order;Attribute group name:sort order=atrribute name-value-sort order;)');
                      $objPHPExcel->getActiveSheet()->SetCellValue('AW'.$i, 'Discount (customer_group_id:qty:Priority:Price-Date Start-Date End;customer_group_id:qty:Priority:Price-Date Start-Date End;)');
                      $objPHPExcel->getActiveSheet()->SetCellValue('AX'.$i, 'Reward Points');
                      $objPHPExcel->getActiveSheet()->SetCellValue('AY'.$i, 'Meta Title');
					  $objPHPExcel->getActiveSheet()->SetCellValue('AZ'.$i, 'Viewed');
                      $objPHPExcel->getActiveSheet()->SetCellValue('BA'.$i, 'Download id');
                      $objPHPExcel->getActiveSheet()->SetCellValue('BB'.$i, 'Vendor Id');
                      $objPHPExcel->getActiveSheet()->SetCellValue('BC'.$i, 'Vendor Name');
                    
					  
					  $al='BD';
					  if(isset($cfiled))
					  {
						  foreach($cfiled as $cfile)
						  {
							  $cfile=trim($cfile);
							   $objPHPExcel->getActiveSheet()->SetCellValue($al.$i, $cfile);
							   $al++;
                     
						  }
					  }
					  
					  $i=2;
					  

		 foreach($data as $product) {
						
                      $objPHPExcel->getActiveSheet()->SetCellValue('A'.$i, $product['product_id']);
                      $objPHPExcel->getActiveSheet()->SetCellValue('B'.$i, $product['language']);
                      $objPHPExcel->getActiveSheet()->SetCellValue('C'.$i, $product['stores']);
                      $objPHPExcel->getActiveSheet()->SetCellValue('D'.$i, $product['storeids']);
                      $objPHPExcel->getActiveSheet()->SetCellValue('E'.$i, $product['model']);
                      $objPHPExcel->getActiveSheet()->SetCellValue('F'.$i, $product['sku']);
                      $objPHPExcel->getActiveSheet()->SetCellValue('G'.$i, $product['upc']);
                      $objPHPExcel->getActiveSheet()->SetCellValue('H'.$i, $product['ean']);
                      $objPHPExcel->getActiveSheet()->SetCellValue('I'.$i, $product['jan']);
                      $objPHPExcel->getActiveSheet()->SetCellValue('J'.$i, $product['isbn']);
                      $objPHPExcel->getActiveSheet()->SetCellValue('K'.$i, $product['mpn']);
                      $objPHPExcel->getActiveSheet()->SetCellValue('L'.$i, $product['location']);
                      $objPHPExcel->getActiveSheet()->SetCellValue('M'.$i, $product['name']);
                      $objPHPExcel->getActiveSheet()->SetCellValue('N'.$i, $product['meta_tag_description']);
                      $objPHPExcel->getActiveSheet()->SetCellValue('O'.$i, $product['meta_tag_keywords']);
                      $objPHPExcel->getActiveSheet()->SetCellValue('P'.$i, $product['description']);
                      $objPHPExcel->getActiveSheet()->SetCellValue('Q'.$i, $product['tag']);
                      $objPHPExcel->getActiveSheet()->SetCellValue('R'.$i, $product['price']);
                      $objPHPExcel->getActiveSheet()->SetCellValue('S'.$i, $product['quantity']);
                      $objPHPExcel->getActiveSheet()->SetCellValue('T'.$i, $product['minimum_quantity']);
                      $objPHPExcel->getActiveSheet()->SetCellValue('U'.$i, $product['subtract_stock']);
                      $objPHPExcel->getActiveSheet()->SetCellValue('V'.$i, $product['out_stockstat']);
                      $objPHPExcel->getActiveSheet()->SetCellValue('W'.$i, $product['require_shipping']);
                      $objPHPExcel->getActiveSheet()->SetCellValue('X'.$i, $product['seo_keyword']);
                      $objPHPExcel->getActiveSheet()->SetCellValue('Y'.$i, $product['img_main']);
                      $objPHPExcel->getActiveSheet()->SetCellValue('Z'.$i, $product['date_avail']);
                      $objPHPExcel->getActiveSheet()->SetCellValue('AA'.$i, $product['len_class']);
                      $objPHPExcel->getActiveSheet()->SetCellValue('AB'.$i, $product['length']);
                      $objPHPExcel->getActiveSheet()->SetCellValue('AC'.$i, $product['width']);
                      $objPHPExcel->getActiveSheet()->SetCellValue('AD'.$i, $product['height']);
                      $objPHPExcel->getActiveSheet()->SetCellValue('AE'.$i, $product['weight']);
                      $objPHPExcel->getActiveSheet()->SetCellValue('AF'.$i, $product['weight_class']);
                      $objPHPExcel->getActiveSheet()->SetCellValue('AG'.$i, $product['status']);
                      $objPHPExcel->getActiveSheet()->SetCellValue('AH'.$i, $product['sort_order']);
                      $objPHPExcel->getActiveSheet()->SetCellValue('AI'.$i, $product['manufacturerid']);
                      $objPHPExcel->getActiveSheet()->SetCellValue('AJ'.$i, $product['manufacturer']);
                      $objPHPExcel->getActiveSheet()->SetCellValue('AK'.$i,$product['categoriesid']);
                      $objPHPExcel->getActiveSheet()->SetCellValue('AL'.$i,str_replace('&amp;','&', str_replace('&gt;','>',$product['categories'])));
                      $objPHPExcel->getActiveSheet()->SetCellValue('AM'.$i, $product['relatedid']);
                      $objPHPExcel->getActiveSheet()->SetCellValue('AN'.$i, $product['related']);
                      $objPHPExcel->getActiveSheet()->SetCellValue('AO'.$i, $product['option']);
                      $objPHPExcel->getActiveSheet()->SetCellValue('AP'.$i, $product['option_val']);
                      $objPHPExcel->getActiveSheet()->SetCellValue('AQ'.$i, $product['image1']);
                      $objPHPExcel->getActiveSheet()->SetCellValue('AR'.$i, $product['product_sp']);
                      $objPHPExcel->getActiveSheet()->SetCellValue('AS'.$i, $product['tax_class']);
                      $objPHPExcel->getActiveSheet()->SetCellValue('AT'.$i, $product['filter_group']);
                      $objPHPExcel->getActiveSheet()->SetCellValue('AU'.$i, $product['filter_name']);
                      $objPHPExcel->getActiveSheet()->SetCellValue('AV'.$i, $product['att']);
                      $objPHPExcel->getActiveSheet()->SetCellValue('AW'.$i, $product['discount']);
                      $objPHPExcel->getActiveSheet()->SetCellValue('AX'.$i, $product['point']);
                      $objPHPExcel->getActiveSheet()->SetCellValue('AY'.$i, $product['meta_title']);
					  $objPHPExcel->getActiveSheet()->SetCellValue('AZ'.$i, $product['viewed']);
                      $objPHPExcel->getActiveSheet()->SetCellValue('BA'.$i, $product['downloadid']);
                      $objPHPExcel->getActiveSheet()->SetCellValue('BB'.$i, $product['vendor_id']);
                      $objPHPExcel->getActiveSheet()->SetCellValue('BC'.$i, $product['vendor']);
                     
					   $al='BD';
					  if(isset($cfiled))
					  {
						  foreach($cfiled as $cfile)
						  {
							   $cfile=trim($cfile);
							   $objPHPExcel->getActiveSheet()->SetCellValue($al.$i, $product[$cfile]);
							   $al++;
                     
						  }
					  }
					  $i++;
               }
			   
			   
			   	/* color setup */
				for($col = 'A'; $col != $al; $col++) {
			   $objPHPExcel->getActiveSheet()->getColumnDimension($col)->setWidth(20);
			 	}
				
				$objPHPExcel->getActiveSheet()->getColumnDimension('P','AR')->setWidth(50);
				$objPHPExcel->getActiveSheet()->getColumnDimension('AQ')->setWidth(50);
				$objPHPExcel->getActiveSheet()->getColumnDimension('AR')->setWidth(50);
				$objPHPExcel->getActiveSheet()->getColumnDimension('BB')->setWidth(100);
				
				$objPHPExcel->getActiveSheet()->getRowDimension(1)->setRowHeight(50);
				
				$objPHPExcel->getActiveSheet()
				->getStyle('A1:'.$al.'1')
				->getFill()
				->setFillType(PHPExcel_Style_Fill::FILL_SOLID)
				->getStartColor()
				->setARGB('02057D');
				
				$styleArray = array(
					'font'  => array(
					'bold'  => true,
					'color' => array('rgb' => 'FFFFFF'),
					'size'  => 9,
					'name'  => 'Verdana'
				));
				
				$objPHPExcel->getActiveSheet()->getStyle('A1:'.$al.'1')->applyFromArray($styleArray);

				/* color setup */  
				$excel='Excel5';				
						 
		$filename = 'Product.xls';
		$objPHPExcel->getActiveSheet()->setTitle('All product');
		$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, $excel);
		$objWriter->save($filename );
		header('Content-type: application/vnd.ms-excel');
		header('Content-Disposition: attachment; filename="'.$filename.'"');
		$objWriter->save('php://output');
		unlink($filename);
		
               
             
	
	}
	
	
		public  function cleanData(&$str) {
               $str = preg_replace("/\t/", "\\t", $str);
               $str = preg_replace("/\r?\n/", "\\n", $str);
               if(strstr($str, '"'))
               $str = '"' . str_replace('"', '""', $str) . '"';
       }
	   
}
?>