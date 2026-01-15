<?php 
set_time_limit(0);
ini_set('memory_limit','9999M');
error_reporting(-1);
require_once(DIR_SYSTEM.'/library/tmd/PHPExcel.php');
//lib
require_once(DIR_SYSTEM.'library/tmd/system.php');
//lib
class ControllerExtensionReviewimportexport extends Controller { 
	private $error = array();
	
	public function index() {	
	$this->registry->set('tmd', new TMD($this->registry));
	$keydata=array(
	'code'=>'tmdkey_reviewimportexport',
	'eid'=>'MjU5NzU=',
	'route'=>'extension/reviewimportexport',
	);
	$reviewimportexport=$this->tmd->getkey($keydata['code']);
	$data['getkeyform']=$this->tmd->loadkeyform($keydata);	
	$this->language->load('extension/reviewimportexport');
	$this->document->setTitle($this->language->get('heading_title'));
	$data['user_token']=$this->session->data['user_token'];
	$data['heading_title'] = $this->language->get('heading_title');
	$data['button_export'] = $this->language->get('button_export');
	$data['button_import'] = $this->language->get('button_import');
	$data['entry_name'] = $this->language->get('entry_name');
	$data['entry_customer'] = $this->language->get('entry_customer');
	$data['entry_author'] = $this->language->get('entry_author');
	$data['entry_rating'] = $this->language->get('entry_rating');
	$data['entry_daterange'] = $this->language->get('entry_daterange');
	$data['entry_limit'] = $this->language->get('entry_limit');
	$data['entry_import'] = $this->language->get('entry_import');
	$data['entry_importby'] = $this->language->get('entry_importby');
	$data['entry_newreview'] = $this->language->get('entry_newreview');
	$data['entry_updatereview'] = $this->language->get('entry_updatereview');
	$data['text_from'] = $this->language->get('text_from');
	$data['text_to'] = $this->language->get('text_to');
	
	if (isset($this->session->data['warning'])) {
			$data['error_warning'] = $this->session->data['warning'];
			unset($this->session->data['warning']);
		} else {
			$data['error_warning'] = '';
		}
		if (isset($this->request->get['number'])) {
			$data['number'] = $this->request->get['number'];
		} else {
			$data['number'] = '0';
		}
		
		$totalreview=$this->getTotalreview();
		
		if (isset($this->request->get['end'])) {
			$data['end'] = $this->request->get['end'];
		}elseif (!empty($totalreview)) {
			$data['end'] = $totalreview;
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
			'href'      => $this->url->link('common/home', 'user_token=' . $this->session->data['user_token'], true),     		
      		'separator' => false
   		);

   		$data['breadcrumbs'][] = array(
       		'text'      => $this->language->get('heading_title'),
			'href'      => $this->url->link('extension/reviewimportexport', 'user_token=' . $this->session->data['user_token'], true),
      		'separator' => ' :: '
   		);
		
		$data['exportlink'] = $this->url->link('extension/reviewimportexport/export', 'user_token=' . $this->session->data['user_token'], true);
		$data['importlink'] = $this->url->link('extension/reviewimportexport/import', 'user_token=' . $this->session->data['user_token'], true);
		$this->load->model('catalog/category');
			$data['categories'] = array();
			
		$data1 = array(
		);
		
		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');
				
		$this->response->setOutput($this->load->view('extension/reviewimportexport', $data));
	}
	
	public function getTotalreview() {
		$query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "review");

		return $query->row['total'];
	}	
	
	public function export() {
		$data=array();
		$objPHPExcel = new PHPExcel();

		// Set properties
		
		$objPHPExcel->getProperties()->setCreator("TMD Export");
		$objPHPExcel->getProperties()->setLastModifiedBy("TMD Export");
		$objPHPExcel->getProperties()->setTitle("Office Excel");
		$objPHPExcel->getProperties()->setSubject("Office Excel");
		$objPHPExcel->getProperties()->setDescription("Office Excel");
		$objPHPExcel->setActiveSheetIndex(0);
						$i=1;
					  $objPHPExcel->getActiveSheet()->SetCellValue('A'.$i, 'Review ID');
					  $objPHPExcel->getActiveSheet()->SetCellValue('B'.$i, 'Product ID');
					  $objPHPExcel->getActiveSheet()->SetCellValue('C'.$i, 'Product Model');
					  $objPHPExcel->getActiveSheet()->SetCellValue('D'.$i, 'Customer_id	');
					  $objPHPExcel->getActiveSheet()->SetCellValue('E'.$i, 'Customer email');
					  $objPHPExcel->getActiveSheet()->SetCellValue('F'.$i, 'Author');
					  $objPHPExcel->getActiveSheet()->SetCellValue('G'.$i, 'Text');
					  $objPHPExcel->getActiveSheet()->SetCellValue('H'.$i, 'Rating');
					  $objPHPExcel->getActiveSheet()->SetCellValue('I'.$i, 'Status');
					  $objPHPExcel->getActiveSheet()->SetCellValue('J'.$i, 'Date Added (YYYY-DD-MM)');
					  $objPHPExcel->getActiveSheet()->SetCellValue('K'.$i, 'Date Modified (YYYY-DD-MM)');
					 
					  $i=2;
					  
		$start=$this->request->post['number'];
		$end2=$this->request->post['end'];
		
		$sql="SELECT *,r.date_added as reviewdateadded, r.date_modified as reviewdatemodified,r.status as rstatus FROM `".DB_PREFIX."review` as r left join ".DB_PREFIX."customer as c on r.`customer_id`= c.`customer_id` where r.review_id!=0 ";
		
		if(!empty($this->request->post['product_id']))
		{
			$sql .=" and product_id='".$this->request->post['product_id']."'";
		}
		if(!empty($this->request->post['ratingto']))
		{
			$sql .=" and rating>='".$this->request->post['ratingto']."'";
		}
		
		if(!empty($this->request->post['ratingfrom']))
		{
			$sql .=" and rating<='".$this->request->post['ratingfrom']."'";
		}
		
		if(!empty($this->request->post['dateto']))
		{
			$sql .=" and r.date_added >='".$this->request->post['dateto']."'";
		}
		if(!empty($this->request->post['datefrom']))
		{
			$sql .=" and r.date_added <='".$this->request->post['datefrom']."'";
		}
		
		if(isset($end2) && isset($start))
		{
			$sql .=" limit ".(int)$start.",".(int)$end2."";
			
		}
		
		$reviewimportexport=$this->config->get('tmdkey_reviewimportexport');
		if (empty(trim($reviewimportexport))) {			
		$this->session->data['warning'] ='Module will Work after add License key!';
		$this->response->redirect($this->url->link('extension/reviewimportexport', 'user_token=' . $this->session->data['user_token'], true));
		}
		
		$query=$this->db->query($sql);
		
		foreach($query->rows as $row){
		
		$query1=$this->db->query("SELECT model FROM `".DB_PREFIX."product` where product_id='".$row['product_id']."'");
						
                      $objPHPExcel->getActiveSheet()->SetCellValue('A'.$i, $row['review_id']);
					  $objPHPExcel->getActiveSheet()->SetCellValue('B'.$i, $row['product_id']);
					  $objPHPExcel->getActiveSheet()->SetCellValue('C'.$i, $query1->row['model']);
					  $objPHPExcel->getActiveSheet()->SetCellValue('D'.$i, $row['customer_id']);
					  $objPHPExcel->getActiveSheet()->SetCellValue('E'.$i, $row['email']);
					  $objPHPExcel->getActiveSheet()->SetCellValue('F'.$i, $row['author']);
					  $objPHPExcel->getActiveSheet()->SetCellValue('G'.$i, $row['text']);
					  $objPHPExcel->getActiveSheet()->SetCellValue('H'.$i, $row['rating']);
						$objPHPExcel->getActiveSheet()->SetCellValue('I'.$i, $row['rstatus']);
					  $objPHPExcel->getActiveSheet()->SetCellValue('J'.$i, $row['reviewdateadded']);
					  $objPHPExcel->getActiveSheet()->SetCellValue('K'.$i, $row['reviewdatemodified']);
                      
					  
					  $i++;
               }
			   
			   
		$filename = 'Review.xls';
		$objPHPExcel->getActiveSheet()->setTitle('All product');
		$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
		$objWriter->save($filename );
		header('Content-type: application/vnd.ms-excel');
		header('Content-Disposition: attachment; filename="'.$filename.'"');
		$objWriter->save('php://output');
		unlink($filename);
	}
	
	public function import()
	{
		$this->load->model('extension/reviewimportexport');
		$this->language->load('extension/reviewimportexport');
		$totalupdateproduct=0;
		$totalnewproduct=0;
		if ($this->request->server['REQUEST_METHOD'] == 'POST' && $this->user->hasPermission('modify', 'extension/reviewimportexport')) {
			
		$reviewimportexport=$this->config->get('tmdkey_reviewimportexport');
		if (empty(trim($reviewimportexport))) {			
		$this->session->data['warning'] ='Module will Work after add License key!';
		$this->response->redirect($this->url->link('extension/reviewimportexport', 'user_token=' . $this->session->data['user_token'], true));
		}
			
			if (is_uploaded_file($this->request->files['import']['tmp_name'])) {
				$content = file_get_contents($this->request->files['import']['tmp_name']);
			} else {
				$content = false;
			}
			
			if ($content) {
				////////////////////////// Started Import work  //////////////
				try {
					$objPHPExcel = PHPExcel_IOFactory::load($this->request->files['import']['tmp_name']);
				} catch(Exception $e) {
					die('Error loading file "'.pathinfo($this->path.$files,PATHINFO_BASENAME).'": '.$e->getMessage());
				}
				
				$i=0;
				$sheetDatas = $objPHPExcel->getActiveSheet()->toArray(null,true,true,true);
				$importby=$this->request->post['importby'];
				foreach($sheetDatas as $sheetData){
				if($i!=0)
				{
					$product_id=$sheetData['B'];
					if(empty($product_id))
					{
						$product_id=$this->model_extension_reviewimportexport->getproductidbymodel($sheetData['C']);
					}
					
					$customer_id=$sheetData['D'];
					if(empty($customer_id))
					{
						$customer_id=$this->model_extension_reviewimportexport->getcustomeridbyemail($sheetData['E']);
					}
					
					$review=array(
						'review_id'=>$sheetData['A'],
						'product_id'=>$product_id,
						'customer_id'=>$customer_id,
						'author'=>$sheetData['F'],
						'text'=>$sheetData['G'],
						'rating'=>$sheetData['H'],
						'status'=>$sheetData['I'],
						'date_added'=>$sheetData['J'],
						'date_modified'=>$sheetData['K'],
					);
					
					if($importby==2)
					{
							$this->model_extension_reviewimportexport->updatereview($review);	
							 $totalupdateproduct++;
					}
					else{
							$this->model_extension_reviewimportexport->addreview($review);
						   $totalnewproduct++;						
					}
				}
				$i++;
				}
				 $this->session->data['success']='Total Review update ' .$totalupdateproduct. ' <br /> Total New Review added '.$totalnewproduct;
				
				////////////////////////// Started Import work  //////////////
				$this->response->redirect($this->url->link('extension/reviewimportexport', 'user_token=' . $this->session->data['user_token'], true));
			}
		}
	}
	
	public function keysubmit() {
		$json = array(); 
		
      	if (($this->request->server['REQUEST_METHOD'] == 'POST')) {
			$keydata=array(
			'code'=>'tmdkey_reviewimportexport',
			'eid'=>'MjU5NzU=',
			'route'=>'extension/reviewimportexport',
			'moduledata_key'=>$this->request->post['moduledata_key'],
			);
			$this->registry->set('tmd', new TMD($this->registry));
            $json=$this->tmd->matchkey($keydata);       
		} 
		
		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}
	
	
	   
}
?>