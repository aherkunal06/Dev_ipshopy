<?php
/**
 * Short description for opencartapi.php
 *
 * @package opencartapi
 * @author tim <tim@tim-PC>
 * @version 0.1
 * @copyright (C) 2018 tim <tim@tim-PC>
 * @license MIT
 */

class opencartapi{
    private  $baseUrl = '';
    private $api_token='';
    private $log;
    private $error;
    public function __construct($baseUrl, $log) {
        $this->baseUrl = $baseUrl;
        $this->log = $log;
    }
    public function login($username,$key){
        $this->error = null;
        $url = $this->buildUrl('api/login');
        $data=[
            'username'=>$username,
            'key'=>$key,
        ];
        $response = $this->post($url, $data);
        $render = true;
        if(!empty($response['error'])){
            $this->error = $response['error'];
            $render = false;
        }elseif(!empty($response['api_token'])){
            $this->api_token=$response['api_token'];
        }
        return $render;
        
    }

    public function addOrderHistory($order_id, $order_status_id, $notify, $override, $comment){
        $this->error = null;
        $url = $this->buildUrl('api/order/history',['order_id'=>$order_id]);
        $data=[
            'order_status_id'=>$order_status_id,
            'notify'=>$notify,
            'override'=>$override,
            'comment'=>$comment,
        ];
        $response = $this->post($url, $data);
        $render = true;
        if(!empty($response['error'])){
            $this->error = $response['error'];
            $render = false;
        }
        return $render;
    }
    /**
     * 获取返回的错误信息
     */
    public function getError(){
        return $this->error;
    }
    protected function buildUrl($route, $getData=[]){
        $getData['api_token'] = $this->api_token;
        return $this->baseUrl .'index.php?route='.$route.'&'.http_build_query($getData);
    }
    protected function post($url, $requestData) {
        $curl = curl_init();

        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        $params =  http_build_query($requestData);

        curl_setopt($curl, CURLOPT_POSTFIELDS, $params);
        $this->log->write("opencartapi-request::url:".$url.",params:".json_encode($params,true));

        $res = curl_exec($curl);
        $this->log->write("opencartapi-response::url:".$url.",params:".json_encode($res));
        curl_close($curl);
        $render = json_decode($res,true);

        return $render;
    }


}
