<?php
class ModelProductVisualSearch extends Model {
    
    /**
     * Upload and process an image for visual search
     * 
     * @param array $file The uploaded file information
     * @param string $opencart_base_url The base URL of the OpenCart installation
     * @return array Response from the backend with search results
     */
    public function uploadImage($file, $opencart_base_url) {
        $response = array(
            'success' => false,
            'error' => ''
        );
        
        // Validate file type
        $allowed_types = array('image/jpeg', 'image/png', 'image/gif', 'image/webp');
        if (!in_array($file['type'], $allowed_types)) {
            $response['error'] = 'Invalid file type. Only JPEG, PNG, GIF, and WEBP images are allowed.';
            return $response;
        }
        
        // Call the Python backend API
        $backend_url = 'http://181.224.131.247:5004/search_by_image';
        
        // Prepare the multipart form data
        $boundary = uniqid();
        $headers = array(
            'Content-Type: multipart/form-data; boundary=' . $boundary
        );
        
        // Build the multipart form data
        $data = '';
        $data .= '--' . $boundary . "\r\n";
        $data .= 'Content-Disposition: form-data; name="file"; filename="' . basename($file['name']) . '"' . "\r\n";
        $data .= 'Content-Type: ' . $file['type'] . "\r\n\r\n";
        $data .= file_get_contents($file['tmp_name']) . "\r\n";
        
        // Add the opencart_base_url to the form data
        $data .= '--' . $boundary . "\r\n";
        $data .= 'Content-Disposition: form-data; name="opencart_base_url"' . "\r\n\r\n";
        $data .= $opencart_base_url . "\r\n";
        
        // Add the base_url to the form data
        $data .= '--' . $boundary . "\r\n";
        $data .= 'Content-Disposition: form-data; name="base_url"' . "\r\n\r\n";
        $data .= 'http://181.224.131.247:5004/' . "\r\n";
        
        $data .= '--' . $boundary . '--' . "\r\n";
        
        // Initialize cURL
        $ch = curl_init($backend_url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        
        // Execute the request
        $result = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        
        if (curl_errno($ch)) {
            $response['error'] = 'cURL Error: ' . curl_error($ch);
        } else if ($http_code != 200) {
            $response['error'] = 'HTTP Error: ' . $http_code;
        } else {
            $json_result = json_decode($result, true);
            
            if ($json_result && isset($json_result['success']) && $json_result['success']) {
                // Add the OpenCart base URL to the response
                $json_result['opencart_base_url'] = $opencart_base_url;
                return $json_result;
            } else {
                $response['error'] = isset($json_result['error']) ? $json_result['error'] : 'Unknown error from backend';
            }
        }
        
        curl_close($ch);
        return $response;
    }
    
    /**
     * Search by image URL
     * 
     * @param string $image_url The URL of the image to search for
     * @param string $opencart_base_url The base URL of the OpenCart installation
     * @return array Response from the backend with search results
     */
    public function searchByUrl($image_url, $opencart_base_url) {
        $response = array(
            'success' => false,
            'error' => ''
        );
        
        // Get the image content
        $image_content = $this->fetchImageFromUrl($image_url);
        if (!$image_content) {
            $response['error'] = 'Failed to fetch image from URL';
            return $response;
        }
        
        // Call the Python backend API
        $backend_url = 'http://181.224.131.247:5004/search_by_image';
        
        // Prepare the multipart form data
        $boundary = uniqid();
        $headers = array(
            'Content-Type: multipart/form-data; boundary=' . $boundary
        );
        
        // Build the multipart form data
        $data = '';
        $data .= '--' . $boundary . "\r\n";
        $data .= 'Content-Disposition: form-data; name="file"; filename="image_from_url.jpg"' . "\r\n";
        $data .= 'Content-Type: image/jpeg' . "\r\n\r\n";
        $data .= $image_content . "\r\n";
        
        // Add the opencart_base_url to the form data
        $data .= '--' . $boundary . "\r\n";
        $data .= 'Content-Disposition: form-data; name="opencart_base_url"' . "\r\n\r\n";
        $data .= $opencart_base_url . "\r\n";
        
        // Add the base_url to the form data
        $data .= '--' . $boundary . "\r\n";
        $data .= 'Content-Disposition: form-data; name="base_url"' . "\r\n\r\n";
        $data .= 'http://181.224.131.247:5004/' . "\r\n";
        
        $data .= '--' . $boundary . '--' . "\r\n";
        
        // Initialize cURL
        $ch = curl_init($backend_url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        
        // Execute the request
        $result = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        
        if (curl_errno($ch)) {
            $response['error'] = 'cURL Error: ' . curl_error($ch);
        } else if ($http_code != 200) {
            $response['error'] = 'HTTP Error: ' . $http_code;
        } else {
            $json_result = json_decode($result, true);
            
            if ($json_result && isset($json_result['success']) && $json_result['success']) {
                // Add the OpenCart base URL to the response
                $json_result['opencart_base_url'] = $opencart_base_url;
                return $json_result;
            } else {
                $response['error'] = isset($json_result['error']) ? $json_result['error'] : 'Unknown error from backend';
            }
        }
        
        curl_close($ch);
        return $response;
    }
    
    /**
     * Get search results from the backend using a session ID
     * 
     * @param string $session_id The session ID from a previous search
     * @return array Search results from the backend
     */
    public function getResults($session_id) {
        $response = array(
            'success' => false,
            'error' => '',
            'results' => array()
        );
        
        // Call the Python backend API to get results
        $backend_url = 'http://181.224.131.247:5004/get_results?session_id=' . urlencode($session_id);
        
        // Initialize cURL
        $ch = curl_init($backend_url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        
        // Execute the request
        $result = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        
        if (curl_errno($ch)) {
            $response['error'] = 'cURL Error: ' . curl_error($ch);
        } else if ($http_code != 200) {
            $response['error'] = 'HTTP Error: ' . $http_code;
        } else {
            $json_result = json_decode($result, true);
            
            if ($json_result) {
                return $json_result;
            } else {
                $response['error'] = 'Invalid response from backend';
            }
        }
        
        curl_close($ch);
        return $response;
    }
    
    /**
     * Fetch image content from a URL
     * 
     * @param string $url The URL to fetch the image from
     * @return string|false The image content or false on failure
     */
    private function fetchImageFromUrl($url) {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        
        $result = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        
        curl_close($ch);
        
        if ($http_code == 200) {
            return $result;
        }
        
        return false;
    }
}
