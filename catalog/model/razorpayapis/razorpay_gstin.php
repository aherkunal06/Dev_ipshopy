<?php
class ModelRazorpayapisRazorpayGstin  extends Model {
    public function getGstinDetails($gstin_no) {
        $url = "https://razorpay.com/api/gstin/" . $gstin_no;

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_USERPWD, "rzp_live_AbNLCMukluJ6tQ:bef5QaTgd9oO1m9WlnlT0bST");
        curl_setopt($curl, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json'
        ]);

        $response = curl_exec($curl);
        $http_status = curl_getinfo($curl, CURLINFO_HTTP_CODE);

        if (curl_errno($curl)) {
            return ['error' => 'Curl Error: ' . curl_error($curl)];
        }

        if ($http_status !== 200) {
            // return ['error' => 'API Request failed with status code ' . $http_status];
            return ['error' => 'Enter Valid GSTIN Number'];
        }

        curl_close($curl);

        $result = json_decode($response, true);

        if (isset($result['enrichment_details']['online_provider']['details'])) {
            return $result['enrichment_details']['online_provider']['details'];
        } else {
            return ['error' => 'No data found for the provided GSTIN.'];
        }
    }
}
?>
