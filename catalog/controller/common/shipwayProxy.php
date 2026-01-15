<?php
class ControllerCommonShipwayProxy extends Controller {
    public function pushOrders() {
        // Load configuration
        $config = require_once(DIR_CONFIG . 'config_custom.php');
        
        // Shipway credentials from config
        $email = $config['SHIPWAY_EMAIL'];
        $licenseKey = $config['SHIPWAY_LICENSE_KEY'];

        if (!$email || !$licenseKey) {
            echo json_encode(['status' => false, 'message' => 'Missing Shipway credentials']);
            exit;
        }

        // Read input data
        $inputData = file_get_contents('php://input');
        $orderData = json_decode($inputData, true);

        if (!$orderData) {
            echo json_encode(['status' => false, 'message' => 'Invalid JSON data']);
            exit;
        }

        // Prepare authentication
        $authHeader = "Authorization: Basic " . base64_encode("$email:$licenseKey");

        // Shipway API endpoint
        $apiUrl = "https://app.shipway.com/api/v2orders";

        // Initialize cURL request
        $ch = curl_init($apiUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            $authHeader
        ]);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($orderData));

        // Execute request and handle response
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        if (curl_errno($ch)) {
            echo json_encode(['status' => false, 'message' => 'cURL Error: ' . curl_error($ch)]);
            curl_close($ch);
            exit;
        }

        curl_close($ch);

        $responseData = json_decode($response, true);
        
        if ($httpCode == 200 && isset($responseData['status']) && $responseData['status'] === true) {
            echo json_encode(['status' => true, 'message' => 'Orders pushed successfully']);
        } else {
            echo json_encode(['status' => false, 'message' => $responseData['message'] ?? 'Failed to push orders']);
        }
    }
}
?>