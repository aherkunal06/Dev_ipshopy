<?php
/**
 * Short description for order_export_import.php
 *
 * @package order_export_import
 * @author tim <tim@tim-PC>
 * @version 0.1
 * @copyrigtim <tim@tim-PC>
 * @license MIT
 */


class ModelExtensionOrderExportImport extends Model {
    public function importOrderData($data) {
        $this->load->model('sale/order');
        $this->load->model('localisation/language');
        $this->load->model('localisation/order_status');
        $this->load->model('user/api');
        $api_id = $this->config->get('config_api_id');
        $api_info = $this->model_user_api->getApi($api_id);

        $api = new opencartapi(HTTP_CATALOG, $this->log);
        $render = [];
        do{

            if(!$api->login($api_info['username'], $api_info['key'])){
                $this->log->write("order_export_import::login error:".$api->getError());
                break;
            }

            if (empty($data['data'])) {
                break;
            }
            // 获取全部的订单状态数据
            $result = $this->model_localisation_order_status->getOrderStatuses();
            $orderStatusList = [];
            foreach($result as $row){
                $orderStatusList[$row['name']] = $row['order_status_id'];
            }
            // 获取全部的语言数据
            $languageList = $this->model_localisation_language->getLanguages();


            foreach ($data['data'] as $row) {
                $notify_customer = 0;
                if (empty($row['order_id'])) {
                    continue;
                }
                $order_id = $row['order_id'];
                $orderInfo = $this->model_sale_order->getOrder($order_id);
                if(!$orderInfo){
                    continue;
                }
                $order_status_id =$orderInfo['order_status_id'];
                if(isset($orderStatusList[$row['order_status']])){
                    $order_status_id = $orderStatusList[$row['order_status']];
                }
                $notify = 0;
                $override= 0;
                $comment= $row['comment'];

                if(strtolower($row['notify_customer'])=='yes' || $row['notify_customer']==1){
                    $notify=1;
                }
                if(strtolower($row['override'])=='yes' || $row['override']==1){
                    $override=1;
                }
                $res = [
                    'order_id'=>$order_id,
                    'order_status_id'=>$order_status_id,
                    'status'=>true,
                    'msg'=>'',
                ];
                if(!$api->addOrderHistory($order_id, $order_status_id, $notify, $override, $comment)){
                    $res['status'] = false;
                    $res['msg']= $api->getError();
                }
                $render[]=$res;
            }
        }while(false);
        return $render;
    }
    
}
