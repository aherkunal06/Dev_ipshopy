<?php 
/**
 * TMD(http://opencartextensions.in/)
 *
 * Copyright (c) 2006 - 2019 TMD
 * This package is Copyright so please us only one domain 
 * 
 */
 
set_time_limit(0);
ini_set('memory_limit','999M');
error_reporting(-1);
require_once(DIR_SYSTEM.'/library/tmdimportexport/PHPExcel.php');

class Controllervendorimport extends Controller { 
	private $error = array();
	
	public function index() {	
		
		$totalnewproduct=0;
		$totalupdateproduct=0;
		$this->language->load('vendor/import');
		
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
		$cfileds=$data['cfiled'];
		
		
		////////////// Custome filed //
		
		/////////// Vendor
		$data['entry_vendors'] = $this->language->get('entry_vendors');
		$data['text_all_vendors'] = $this->language->get('text_all_vendors');
		$this->load->model('vendor/import');
		$data['vendors']= $this->model_vendor_import->getVendors(array());
		/////////// Vendor
		
		$this->document->setTitle($this->language->get('heading_title1'));
		
		$this->load->model('vendor/import');
				
		if ($this->request->server['REQUEST_METHOD'] == 'POST' && $this->user->hasPermission('modify', 'vendor/import')) {
		
			$language_id=$this->request->post['language_id'];
			$store_id=$this->request->post['store_id'];
			$importby=$this->request->post['importby'];
			$vendor_id=$this->request->post['vendor'];
			
			if (is_uploaded_file($this->request->files['import']['tmp_name'])) {
				$content = file_get_contents($this->request->files['import']['tmp_name']);
			} else {
				$content = false;
			}
			
			
			if ($content)
			{
				////////////////////////// Started Import work  //////////////
				try {
					$objPHPExcel = PHPExcel_IOFactory::load($this->request->files['import']['tmp_name']);
				} catch(Exception $e) {
					die('Error loading file "'.pathinfo($this->path.$files,PATHINFO_BASENAME).'": '.$e->getMessage());
				}
				/*	@ get a file data into $sheetDatas variable */
				$sheetDatas = $objPHPExcel->getActiveSheet()->toArray(null,true,true,true);
				/*	@ $i variable for getting data. in first iteration of loop we get size and color name of product */
				$i=0;
				/*
				@ arranging the data according to our need
				*/
				foreach($sheetDatas as $sheetData){
				if($i!=0)
				{
					$product_id=$sheetData['A'];
					$model=$sheetData['E'];
					if($importby==1)
					{
						$product_id=$sheetData['A'];
					}
					else
					{
						$model=$sheetData['E'];
						$product_id=$this->model_vendor_import->getproductbymodel($model,$vendor_id);
					}
				
				if(isset($product_id) || isset($model))
				{
				
				/* stroers */
				$product_stores=array();
				$product_stores[]=0;
				$product_stornew=explode(';',$sheetData['D']);
				if(isset($product_stornew))
				{
					foreach($product_stornew as $product_store)
					{
					if(!empty($product_store))
						{
							$product_stores[]=$product_store;
						}
					}
				}
				
				$product_stornew=explode(';',$sheetData['C']);
				if(isset($product_stornew))
				{
					foreach($product_stornew as $product_store)
					{
					if(!empty($product_store))
						{
							 $product_store=$this->model_vendor_import->getstorebyname($product_store);
							$product_stores[]=$product_store;
						}
					}
				}
				$product_stores=array_unique($product_stores);
			
				/*  END Stores */
				
				/* Step Get category ids  */
				$catgoryname=$sheetData['AL'];
				$catgorynames=explode(';',$catgoryname);
				$category_id=array();
				if(isset($catgorynames))
				{
					foreach($catgorynames as $category)
					{
						$category=trim($category);
						if(!empty($category))
						{
							$category_id[]=$this->model_vendor_import->category($category,0,$store_id,$language_id);
						}
					}
				}
				
				$catgoryname=$sheetData['AK'];
				$catgorynames=explode(';',$catgoryname);
				
				if(isset($catgorynames))
				{
					foreach($catgorynames as $category)
					{
						$category=trim($category);
						if(!empty($category))
						{
							$category_id[]=$category;
						}
					}
				}
				
				$category_id=array_unique($category_id);
				/* Step Get category ids  */
				
				/* Step Get Barnd  ids  */
				$brandname=$sheetData['AJ'];
				$brandnames=explode(';',$brandname);
				$brand_id='';
				if(isset($brandnames))
				{
					foreach($brandnames as $brand)
					{
						$brand=trim($brand);
						if(!empty($brand))
						{
							$brand_id=$this->model_vendor_import->barnd($brand,$store_id,$vendor_id);
						}
					}
				}
				
				$brandname=$sheetData['AI'];
				if(isset($brandname))
				{
						$brand_id=$brandname;
				}
				
				/* Step Get Barnd  ids  */
				
				/* Step Get Options and value insert  */
				$options=$sheetData['AO'];
				$options=explode(';',$options);
				
				if(isset($options))
				{
					foreach($options as $option)
					{
						$option=trim($option);
						if(!empty($option))
						{
							$this->model_vendor_import->option($option,$language_id,$vendor_id);
						}
					}
				}
				
				$options=$sheetData['AP'];
				$options=explode(';',$options);
				$productoptions=array();
				if(!empty($options))
				{
					foreach($options as $option)
					{
						$option=trim($option);
						if(!empty($option))
						{
							$productoptions[]=$this->model_vendor_import->optionvalue($option,$language_id,$vendor_id);
						}
					}
				}
				/* Step End Get Options and value insert  */
				
			
				/* Step Product Main Image  */
				$image=$sheetData['Y'];
				if(!empty($image))
				{
				$mainimage=$this->model_vendor_import->imagesave($image,$vendor_id);
				}
				else
				{
				$mainimage='';
				}
				
				/* Step End Product Main Image  */
				
				/* Step Start Product Filter Group  entry */
				if(isset($sheetData['AT']))
				{
				$filtergroup=$sheetData['AT'];
				if(!empty($filtergroup))
				{
					$filtergroups=explode(';',$filtergroup);
					if(!empty($filtergroups))
					{
						foreach($filtergroups as $filtergroup)
						{
							$filtergroup=trim($filtergroup);
							if(!empty($filtergroup))
							{
								$this->model_vendor_import->filtergroup($filtergroup,$language_id,$vendor_id);
							}
						}
					}
				}
				}
				/* Step Start Product Filter Group  entry */
				/* Step Start Product Filter name  entry */
				$fillterids=array();
				if(isset($sheetData['AU']))
				{
				$filternames=$sheetData['AU'];
				if(!empty($filternames))
				{
					$filternames=explode(';',$filternames);
					if(!empty($filternames))
					{
						
						foreach($filternames as $filtername)
						{
							$filtername=trim($filtername);
							if(!empty($filtername))
							{
							$fillterids[]=$this->model_vendor_import->filtername($filtername,$language_id,$vendor_id);
							}
						}
					}
				}
				}
				
			
				
				/* Start Attribute work */
				$attributes=array();
				if(isset($sheetData['AV']))
				{
				$attributesname=$sheetData['AV'];
				if(!empty($attributesname))
				{
					$attributess=explode(';',$attributesname);
					if(!empty($attributess))
					{
						
						foreach($attributess as $attribute)
						{
							if(!empty($attribute))
							{
							$attinfo=$this->model_vendor_import->atributeallinfo($attribute,$language_id,$vendor_id);
							$attributes[]=array(
							'attribute_id'=>$attinfo['attribute_id'],
							'text'=>$attinfo['text']
							);
							}
						}
					}
				}
				
				}
				
				/* End Attribute work */
				$discounts=array();
				if(isset($sheetData['AW']))
				{
					$discountinfo=$sheetData['AW'];
					$discountinfos=explode(';',$discountinfo);
					if(!empty($discountinfos))
					{
						foreach($discountinfos as $discount)
						{
							if(!empty($discount))
							{
							$info=explode(':',$discount);
							$discounts[]=array(
							'customer_group_id'=>$info[0],
							'quantity'=>$info[1],
							'priority'=>$info[2],
							'price'=>$info[3],
							'date_start'=>$info[4],
							'date_end'=>$info[5]
							);
							}
						}
						
					}
				}
				/* Start Discount work */
				
				/* END Discount work */
				
				/* Reward point */
				$point='';
				if(!empty($sheetData['AX']))
				{
				$point=$sheetData['AX'];
				}
				
				/* Step Product Images  */
				$productimages=array();
				$images=$sheetData['AQ'];
				$images=explode(';',$images);
				
				if(isset($images))
				{
					foreach($images as $image)
					{
					
						if(!empty($image))
						{
						$productimages[]=$this->model_vendor_import->imagesave($image,$vendor_id);
						}
					}
				}
				
				/* Step End Product Images  */
				
				/* Step Product Speical price  */
				 $specialpricenew=$sheetData['AR'];
				
				$specialprice=array();
				if(!empty($specialpricenew))
				{
				$specialpriceset=explode(';',$specialpricenew);
				
				foreach($specialpriceset as $set)
					{
					if(!empty($set))
						{
						list($customer_group_id,$startdate,$enddate,$price)=explode(':',$set);
						$specialprice[]=array(
								'price'=>$price,
								'priority'=>1,
								'customer_group_id'=>$customer_group_id,
								'date_start'=>$startdate,
								'date_end'=>$enddate
						);
					}
					}
				}
				
				/* Step End Product Speical price  */
				
				/* Step related products */
				 $relatedprodctinfo=$sheetData['AN'];
				
				$product_related=array();
				if(!empty($relatedprodctinfo))
				{
				$relatedprodctinfos=explode(';',$relatedprodctinfo);
				
				foreach($relatedprodctinfos as $relatedprodctinfo)
					{
					if(!empty($relatedprodctinfo))
						{
						$product_related[]=$this->model_vendor_import->getproductbymodel($relatedprodctinfo,$vendor_id);
					}
					}
				}
				$relatedprodctinfo=$sheetData['AM'];
				if(!empty($relatedprodctinfo))
				{
				$relatedprodctinfos=explode(';',$relatedprodctinfo);
				
				foreach($relatedprodctinfos as $relatedprodctinfo)
					{
					if(!empty($relatedprodctinfo))
						{
						$product_related[]=$relatedprodctinfo;
					}
					}
				}
				$product_related=array_unique($product_related);
				/* Step End Product related  */
				
				
				/* Start Product Download */
				$productdownloads=array();
				$downloadids=trim($sheetData['BA']);
				if(!empty($downloadids))
				{
					$downloadids=explode(';',$downloadids);
					foreach($downloadids as $downloadid)
					{
						if(isset($downloadid))
						{
						$productdownloads[]=$downloadid;
						}
					}
				}
				/* end Product Download */
				
				/* Step Product other  info collect */
				$model=$sheetData['E'];
				$sku=$sheetData['F'];
				$upc=$sheetData['G'];
				$ean=$sheetData['H'];
				$jan=$sheetData['I'];
				$isbn=$sheetData['J'];
				$mpn=$sheetData['K'];
				$location=$sheetData['L'];
				$productname=$sheetData['M'];
				$metadescription=$sheetData['N'];
				$metatag=$sheetData['O'];
				$description=$sheetData['P'];
				$tags=$sheetData['Q'];
				$price=$sheetData['R'];
				$quantity=$sheetData['S'];
				$mquantity=$sheetData['T'];
				$subtractstock=$sheetData['U'];
				$stockstatus=$sheetData['V'];
				$shipping=$sheetData['W'];
				$keyword=$sheetData['X'];
				$available=$sheetData['Z'];
				$lengthclass=$sheetData['AA'];
				$length=$sheetData['AB'];
				$width=$sheetData['AC'];
				$height=$sheetData['AD'];
				$weight=$sheetData['AE'];
				$weightclass=$sheetData['AF'];
				$status=$sheetData['AG'];
				$sort_order=$sheetData['AH'];
				$viewed=$sheetData['AZ'];
				if(isset($sheetData['AY']))
				{
					$meta_title=$sheetData['AY'];
				}
				else
				{
					$meta_title=$sheetData['M'];
				}
				if(isset($sheetData['AS']))
				{
				$tax_class_id=$sheetData['AS'];
				}
				else
				{
				$tax_class_id='';
				}
				
				$extra=array();
				if(isset($cfileds))
				{
					foreach($cfileds as $cfiled)
					{
						if(!in_array($cfiled,$this->request->post))
						{	if(!empty($sheetData[$this->request->post[$cfiled]]))
							{
							$extra[$cfiled]=$sheetData[$this->request->post[$cfiled]];
							}
						}
					}
				}
				
				$data='';
				$data=array(
				'product_category'=>$category_id,
				'manufacturer_id'=>$brand_id,
				'productoptions'=>$productoptions,
				'product_special'=>$specialprice,
				'image'=>$mainimage,
				'product_image'=>$productimages,
				'model'=>$model,
				'sku'=>$sku,
				'upc'=>$upc,
				'ean'=>$ean,
				'jan'=>$jan,
				'isbn'=>$isbn,
				'available'=>$available,
				'mpn'=>$mpn,
				'location'=>$location,
				'name'=>$productname,
				'meta_keyword'=>$metatag,
				'meta_description'=>$metadescription,
				'description'=>$description,
				'tag'=>$tags,
				'price'=>$price,
				'product_filter'=>$fillterids,
				'quantity'=>$quantity,
				'minimum'=>$mquantity,
				'subtract'=>$subtractstock,
				'tax_class_id'=>$tax_class_id,
				'stock_status_id'=>$stockstatus,
				'shipping'=>$shipping,
				'keyword'=>$keyword,
				'length_class_id'=>$lengthclass,
				'length'=>$length,
				'width'=>$width,
				'height'=>$height,
				'weight'=>$weight,
				'weight_class_id'=>$weightclass,
				'status'=>$status,
				'sort_order'=>$sort_order,
				'attributes'=>$attributes,
				'discounts'=>$discounts,
				'point'=>$point,
				'product_store'=>$product_stores,
				'product_related'=>$product_related,
				'meta_title'=>$meta_title,
				'viewed'=>$viewed,
				'productdownloads'=>$productdownloads,
				);
				
				
				if(empty($product_id))
				{
				$this->model_vendor_import->addproduct($data,$language_id,$extra,$vendor_id);
				$totalnewproduct++;
				}
				else
				{
					$product_id=$this->model_vendor_import->getproductbyid($product_id);
					if(!empty($product_id))
					{
						$this->model_vendor_import->editproduct($data,$product_id,$language_id,$extra,$vendor_id);
						$totalupdateproduct++;
					}
				}
				/* Step Product other  info collect */
				//print_r($sheetData);
				
				}
				}
				$i++;
				}
				 $this->session->data['success']='Total product update : ' .$totalupdateproduct. ' <br /> Total New product added :'.$totalnewproduct;
				
				////////////////////////// Started Import work  //////////////
				$this->response->redirect($this->url->link('vendor/import', 'user_token=' . $this->session->data['user_token'], true));
			} 
			 else {
				$this->error['warning'] = $this->language->get('error_empty');
			}
			
			
			
		}

		$data['heading_title'] = $this->language->get('heading_title');
		$data['button_import'] = $this->language->get('button_import');
		$data['entry_stores'] = $this->language->get('entry_stores');
		$data['entry_stores'] = $this->language->get('entry_stores');
		$data['entry_language'] = $this->language->get('entry_language');
		$data['entry_import'] = $this->language->get('entry_import');
		$data['text_all_stores'] = $this->language->get('text_all_stores');
		$data['entry_importby'] = $this->language->get('entry_importby');
		$data['entry_productid'] = $this->language->get('entry_productid');
		$data['entry_type'] = $this->language->get('entry_type');
		$data['entry_model'] = $this->language->get('entry_model');
		$data['entry_extrafiled'] = $this->language->get('entry_extrafiled');
			/////////// Stores
		$this->load->model('setting/store');
		$data['stores'] = $this->model_setting_store->getStores();
		/////////// Stores
		/////////// Stock status
		$this->load->model('localisation/language');
		$data['languages'] = $this->model_localisation_language->getLanguages();
		/////////// Stores
		
		if (isset($this->session->data['error'])) {
    		$data['error_warning'] = $this->session->data['error'];
    
			unset($this->session->data['error']);
 		} elseif (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
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
			'href'      => $this->url->link('common/home', 'user_token=' . $this->session->data['user_token'], true)     		
      		
   		);

   		$data['breadcrumbs'][] = array(
       		'text'      => $this->language->get('heading_title'),
			'href'      => $this->url->link('vendor/import', 'user_token=' . $this->session->data['user_token'], true)
      		
   		);
		
		$data['import'] = $this->url->link('vendor/import', 'user_token=' . $this->session->data['user_token'], true);

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');
				
		$this->response->setOutput($this->load->view('vendor/import', $data));
	}
	
	
}
?>