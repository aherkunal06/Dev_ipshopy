<?php
// File: catalog/model/clickpost/clickpost.php
class ModelClickpostPincodeServiceability extends Model {

    private $clickpost_api_url = 'https://api.clickpost.in/tracking-api/v2/get-tracking-details/';
    private $access_token = 'your_access_token_here'; // Replace with real token




    public function getTrackingDetails($awb_number) {
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => $this->clickpost_api_url . $awb_number,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_HTTPHEADER => array(
                "Content-Type: application/json",
                "Authorization: Token token={$this->access_token}"
            ),
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);
        curl_close($curl);

        if ($err) {
            return ['error' => $err];
        } else {
            return json_decode($response, true);
        }
    }

    public function postOrderData($order_data) {
        // Placeholder if Clickpost requires order push API
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://api.clickpost.in/some-order-api/',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => json_encode($order_data),
            CURLOPT_HTTPHEADER => array(
                "Content-Type: application/json",
                "Authorization: Token token={$this->access_token}"
            ),
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);
        curl_close($curl);

        if ($err) {
            return ['error' => $err];
        } else {
            return json_decode($response, true);
        }
    }



     public function checkServiceability($drop_pincode) {
        $pickup_pincode = $this->config->get('config_postcode') ?? '400065';
        $url = 'https://serviceability.clickpost.in/api/v3/serviceability_api/?username=ipshopy-test&key=6cb47441-af83-4d3f-bc49-cbbece04a4c0';

        $payload = json_encode([[
            'pickup_pincode' => $pickup_pincode,
            'drop_pincode' => $drop_pincode,
            'order_type' => 'COD',
            'service_type' => 'FORWARD'
        ]]);

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);

        $response = curl_exec($ch);
        curl_close($ch);

        $data = json_decode($response, true);

        if ($data['meta']['success']) {
            return ['success' => true, 'result' => $data['result']];
        } else {
            return ['success' => false, 'error' => $data['meta']['message']];
        }
    }


    // public function checkServiceability() {
    //     $this->load->model('clickpost/clickpost');
    //     $pickup_pincode = $this->config->get('config_postcode') ?? '400065';
    //     $drop_pincode = $this->request->post['drop_pincode'] ?? '431001';

    //     // Call the function from the model
    //     $result = $this->model_extension_module_clickpost_serviceability->checkClickpostServiceability($pickup_pincode, $drop_pincode);

    //     $this->response->addHeader('Content-Type: application/json');
    //     $this->response->setOutput(json_encode($result));
    // }
}


