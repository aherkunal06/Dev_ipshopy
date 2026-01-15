<?php
/**
 * Short description for order_export_import.php
 *
 * @package order_export_import
 * @author tim <tim@atim.cn>
 * @version 0.1
 * @copyright (C) 2018 tim <tim@atim.cn>
 * @license MIT
 */

class ControllerExtensionOrderExportImport extends Controller {
	private $error = array();
    public function index(){
		$this->load->language('extension/order_export_import');
		$this->load->model('sale/order');

    }
    public function export(){
		$this->load->language('extension/order_export_import');
		$this->load->model('sale/order');
        $this->response->addheader('Pragma: public');
        $this->response->addheader('Expires: 0');
        $this->response->addHeader('Cache-Control: max-age=0');
        $this->response->addheader('Content-Description: File Transfer');
        $this->response->addheader('Content-Type: application/vnd.ms-excel');
        $this->response->addheader('Content-Disposition: attachment; filename=' . DB_DATABASE . '_' . date('Y-m-d_H-i-s', time()) . '_order.csv');
        $this->response->addheader('Content-Transfer-Encoding: binary');
        $data = $this->filterExport();

        $this->response->setOutput(Csv::array_to_csv($data));

    }
    public function import(){
		$this->load->language('extension/order_export_import');
		$this->load->model('extension/order_export_import');
		$this->load->model('sale/order');
        do{
            if(!$this->validateImport()) {
                $this->session->data['error'] = $this->error['warning'];
                break;
            }
            $import_data = Csv::csv_to_array($this->request->files['uploadfile']['tmp_name']);
            $this->model_extension_order_export_import->importOrderData($import_data);
            $this->session->data['success'] = $this->language->get('text_success');
        }while(false);

    }
    private function validateImport(){
        do{

            if (($this->request->server['REQUEST_METHOD'] != 'POST')) {
                $this->error['warning'] = $this->language->get('error_submit_method');
                break;
            }
            if (!$this->user->hasPermission('modify', 'extension/order_export_import')) {
                $this->error['warning'] = $this->language->get('error_permission');
                break;
            }
            if (!is_uploaded_file($this->request->files['uploadfile']['tmp_name'])) {
                $this->error['warning'] = $this->language->get('error_not_found_upload_file');
                break;
            }
            if (substr(strrchr(strtolower($this->request->files['uploadfile']['name']), '.'), 1) != 'csv') {
                $this->error['warning'] = $this->language->get('error_extension');
                break;
            }
        }while(false);
        return !$this->error;
    }
    private function  filterExport(){

        if (isset($this->request->get['filter_order_id'])) {
            $filter_order_id = $this->request->get['filter_order_id'];
        } else {
            $filter_order_id = '';
        }

        if (isset($this->request->get['filter_customer'])) {
            $filter_customer = $this->request->get['filter_customer'];
        } else {
            $filter_customer = '';
        }

        if (isset($this->request->get['filter_order_status'])) {
            $filter_order_status = $this->request->get['filter_order_status'];
        } else {
            $filter_order_status = '';
        }

        if (isset($this->request->get['filter_order_status_id'])) {
            $filter_order_status_id = $this->request->get['filter_order_status_id'];
        } else {
            $filter_order_status_id = '';
        }

        if (isset($this->request->get['filter_total'])) {
            $filter_total = $this->request->get['filter_total'];
        } else {
            $filter_total = '';
        }

        if (isset($this->request->get['filter_date_added'])) {
            $filter_date_added = $this->request->get['filter_date_added'];
        } else {
            $filter_date_added = '';
        }

        if (isset($this->request->get['filter_date_modified'])) {
            $filter_date_modified = $this->request->get['filter_date_modified'];
        } else {
            $filter_date_modified = '';
        }

        if (isset($this->request->get['sort'])) {
            $sort = $this->request->get['sort'];
        } else {
            $sort = 'o.order_id';
        }

        if (isset($this->request->get['order'])) {
            $order = $this->request->get['order'];
        } else {
            $order = 'DESC';
        }

        $page = 1;
        $filter_data = array(
            'filter_order_id'        => $filter_order_id,
            'filter_customer'	     => $filter_customer,
            'filter_order_status'    => $filter_order_status,
            'filter_order_status_id' => $filter_order_status_id,
            'filter_total'           => $filter_total,
            'filter_date_added'      => $filter_date_added,
            'filter_date_modified'   => $filter_date_modified,
            'sort'                   => $sort,
            'order'                  => $order,
            'start'                  => ($page - 1) * $this->config->get('config_limit_admin'),
            'limit'                  => $this->config->get('config_limit_admin')
        );
        $order_total = $this->model_sale_order->getTotalOrders($filter_data);
        $render = [];
        do{
            $results = $this->model_sale_order->getOrders($filter_data);
            foreach($results as $result){
                $row = $this->model_sale_order->getOrder($result['order_id']);
                $item = [];
                $item['order_id'] = $row['order_id'];
                $item['firstname'] = isset($row['firstname'])?$row['firstname']:'';
                $item['lastname'] = isset($row['lastname'])?$row['lastname']:'';
                $item['order_status'] = $row['order_status'];
                $item['total'] = $row['total'];
                $item['notify_customer'] ='';
                $item['override'] ='';
                $item['comment'] ='';
                $item['date_added'] =  date($this->language->get('date_format_short'), strtotime($row['date_added']));
                $productList = $this->model_sale_order->getOrderProducts($result['order_id']);
                $itemProductList = [];
                foreach($productList as $k=>$product){
                    $itemProduct = [];
                    foreach($item as $key=>$value){
                        if($k==0){
                            $itemProduct[$key]= $value;
                        }else{
                            $itemProduct[$key]= '';
                        }
                    }
                    $itemProduct['model'] = $product['model'];
                    $itemProduct['name'] = $product['name'];
                    $itemProduct['quantity'] = $product['quantity'];
                    $render[] = $itemProduct;
                }
            }
            $page++;
            $start = ($page-1) * $this->config->get('config_limit_admin');
            if($start>=$order_total){
                break;
            }

        }while(true);
        return $render;

    }
    public function test(){
        $this->load->model("user/api");
        $this->load->helper("opencartapi");
        //@todo 编写接口
        $api_info= $this->model_user_api->getApi(1);
        $api =new opencartapi( HTTP_CATALOG,$this->log);
        $api->login($api_info['username'], $api_info['key']);
        $api->addOrderHistory(1,3,0,0,"hi,".date("y-m-d H:i:s"));
        //删除token
    }

}
