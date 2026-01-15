<?php
class ControllerToolBrokenlink extends Controller {

    public function index() {
        $this->load->language('tool/brokenlink');
        $this->document->setTitle('Broken Link Checker');

        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');

        $data['scan_action'] = $this->url->link('tool/brokenlink/scan', 'user_token=' . $this->session->data['user_token'], true);

        // Fetch previous results
        $query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "broken_links` ORDER BY checked_on DESC");
        $data['links'] = $query->rows;

        // Display error/success messages
        $data['error_warning'] = isset($this->session->data['error']) ? $this->session->data['error'] : '';
        unset($this->session->data['error']);

        $data['success'] = isset($this->session->data['success']) ? $this->session->data['success'] : '';
        unset($this->session->data['success']);

        $this->response->setOutput($this->load->view('tool/brokenlink', $data));
    }

    public function scan() {
        // Use 127.0.0.1 for localhost if needed
    //    $sitemap_url = str_replace('localhost', '127.0.0.1', HTTP_CATALOG) . 'index.php?route=extension/feed/simple_google_sitemap';
$sitemap_url = HTTP_CATALOG . 'sitemap.xml';

        // Fetch XML via cURL
        $ch = curl_init($sitemap_url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 15);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
//    echo '<pre>';
// var_dump($xmlString);
// echo '</pre>';
// die();
  $xmlString = curl_exec($ch); // ✅ add this
    curl_close($ch);


        if (!$xmlString) {
            $this->session->data['error'] = 'Failed to fetch sitemap XML!';
            $this->response->redirect($this->url->link('tool/brokenlink', 'user_token=' . $this->session->data['user_token'], true));
        }

        // Optional debug
        // echo '<pre>'; print_r($xmlString); echo '</pre>'; die();
echo '<pre>'; print_r($urls); die();
        // Parse XML
        libxml_use_internal_errors(true);
        $xml = simplexml_load_string($xmlString);

        if (!$xml) {
            $this->session->data['error'] = 'Failed to parse sitemap XML!';
            $this->response->redirect($this->url->link('tool/brokenlink', 'user_token=' . $this->session->data['user_token'], true));
        }

        // Extract URLs (handle namespace)
        $urls = [];
        $namespaces = $xml->getNamespaces(true);

        if (isset($namespaces[''])) {
            $xml->registerXPathNamespace('s', $namespaces['']);
            foreach ($xml->xpath('//s:url') as $url) {
                $urls[] = (string)$url->loc;
            }
        } else {
            foreach ($xml->url as $url) {
                $urls[] = (string)$url->loc;
            }
        }

        if (empty($urls)) {
            $this->session->data['error'] = 'No URLs found in sitemap!';
            $this->response->redirect($this->url->link('tool/brokenlink', 'user_token=' . $this->session->data['user_token'], true));
        }

        // Clear previous results
        $this->db->query("TRUNCATE TABLE `" . DB_PREFIX . "broken_links`");

        // Check each URL status and insert into DB
        foreach ($urls as $link) {
            $status = $this->getHttpStatus($link);
            $this->db->query("INSERT INTO `" . DB_PREFIX . "broken_links` SET url='" . $this->db->escape($link) . "', status_code='" . (int)$status . "', checked_on=NOW()");
        }

        $this->session->data['success'] = 'Scanning complete!';
        $this->response->redirect($this->url->link('tool/brokenlink', 'user_token=' . $this->session->data['user_token'], true));
    }

    // Private method to get HTTP status code
    private function getHttpStatus($url) {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_NOBODY, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_exec($ch);
        $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        return $status;
    }
}
