<?php
class ControllerProductVisualSearch extends Controller {
    
    // public function index() {
        
    //     if ($this->request->server['REQUEST_METHOD'] == 'POST') {
    //         header('Content-Type: application/json');
    //         $json = [
    //             'success' => true,
    //             'message' => 'POST received in OpenCart controller!',
    //             'session_id' => uniqid(),
    //             'result_count' => 0
    //         ];
    //         echo json_encode($json);
    //         exit;
    //     }
        
    //     $this->load->language('product/search');
    //     $this->load->model('catalog/category');
    //     $this->load->model('catalog/product');
    //     $this->load->model('product/visual_search');
        
    //     $data['breadcrumbs'] = array();
        
    //     $data['breadcrumbs'][] = array(
    //         'text' => $this->language->get('text_home'),
    //         'href' => $this->url->link('common/home')
    //     );
        
    //     $data['breadcrumbs'][] = array(
    //         'text' => 'Visual Search',
    //         'href' => $this->url->link('product/visual_search')
    //     );
        
    //     $this->document->setTitle('Visual Search Results');
        
    //     $data['column_left'] = $this->load->controller('common/column_left');
    //     $data['column_right'] = $this->load->controller('common/column_right');
    //     $data['content_top'] = $this->load->controller('common/content_top');
    //     $data['content_bottom'] = $this->load->controller('common/content_bottom');
    //     $data['footer'] = $this->load->controller('common/footer');
    //     $data['header'] = $this->load->controller('common/header');
        
        
    //     $this->response->setOutput($this->load->view('product/visual_search_results', $data));
    // }
    public function index() {
    // Handle POST separately
    if ($this->request->server['REQUEST_METHOD'] == 'POST') {
        header('Content-Type: application/json');
        $json = [
            'success' => true,
            'message' => 'POST received in OpenCart controller!',
            'session_id' => uniqid(),
            'result_count' => 0
        ];
        echo json_encode($json);
        exit;
    }

    // Load language, models, etc.
    $this->load->language('product/search');
    $this->load->model('catalog/category');
    $this->load->model('catalog/product');
    $this->load->model('product/visual_search');

    $data['breadcrumbs'] = [];

    $data['breadcrumbs'][] = [
        'text' => $this->language->get('text_home'),
        'href' => $this->url->link('common/home')
    ];

    $data['breadcrumbs'][] = [
        'text' => 'Visual Search',
        'href' => $this->url->link('product/visual_search')
    ];

    $this->document->setTitle('Visual Search Results');

    // 💡 Handle session_id from GET param
    $session_id = isset($this->request->get['session_id']) ? $this->request->get['session_id'] : '';

    $data['results'] = [];

    if ($session_id) {
        // Fetch from external API
        $api_url = "http://181.224.131.247:5004/get_search_results/" . $session_id;

        $ch = curl_init($api_url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        $api_response = curl_exec($ch);

        if (!curl_errno($ch)) {
            $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            if ($http_code === 200 && $api_response) {
                $decoded = json_decode($api_response, true);
                if ($decoded && is_array($decoded)) {
                    $data['results'] = $decoded;
                }
            }
        }
        curl_close($ch);
    }

    // Render layout
    $data['column_left'] = $this->load->controller('common/column_left');
    $data['column_right'] = $this->load->controller('common/column_right');
    $data['content_top'] = $this->load->controller('common/content_top');
    $data['content_bottom'] = $this->load->controller('common/content_bottom');
    $data['footer'] = $this->load->controller('common/footer');
    $data['header'] = $this->load->controller('common/header');
    // var_dump($data['results']);

   $limit = 30;
$page = isset($this->request->get['page']) ? (int)$this->request->get['page'] : 1;

// All results from your source
$all_results = $data['results']['results'];
$total_results = count($all_results);

// Slice current page's results
$offset = ($page - 1) * $limit;
$paginated_results = array_slice($all_results, $offset, $limit);

// Add product_url
foreach ($paginated_results as &$result) {
    $result['product_url'] = $this->url->link(
        'product/product',
        'path=' . ($this->request->get['path'] ?? '') . '&product_id=' . $result['product_id']
    );
}

$data['results']['results'] = $paginated_results;

// Pagination
$this->load->language('product/search'); // Load your language file for pagination labels

$pagination = new Pagination();
$pagination->total = $total_results;
$pagination->page = $page;
$pagination->limit = $limit;
$pagination->url = $this->url->link(
    $this->request->get['route'],
    http_build_query(array_merge($this->request->get, ['page' => '{page}']))
);

$data['pagination'] = $pagination->render();
$data['results_total'] = sprintf($this->language->get('text_pagination'), 
    ($total_results) ? (($page - 1) * $limit) + 1 : 0,
    min($page * $limit, $total_results),
    $total_results,
    ceil($total_results / $limit)
);



    $this->response->setOutput($this->load->view('product/visual_search_results', $data));
}

    
    /**
     * Handle image upload for visual search
     */
     
    public function upload() {
        $json = array(
            'success' => false,
            'error' => ''
        );
        
        if (isset($this->request->files['file']) && is_uploaded_file($this->request->files['file']['tmp_name'])) {
            $this->load->model('product/visual_search');
            
            // Get the OpenCart base URL from the request
            $opencart_base_url = isset($this->request->post['opencart_base_url']) ? $this->request->post['opencart_base_url'] : '';
            
            // Process the uploaded image
            $result = $this->model_product_visual_search->uploadImage($this->request->files['file'], $opencart_base_url);
            
            if (isset($result['success']) && $result['success']) {
                $json = $result;
            } else {
                $json['error'] = isset($result['error']) ? $result['error'] : 'Failed to process the image';
            }
        } else {
            $json['error'] = 'No file uploaded';
        }
        
        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
        //   $this->response->setOutput($this->load->view('product/visual_search_results', $data));
    }
    
    /**
     * Handle image URL for visual search
     */
    public function url() {
        $json = array(
            'success' => false,
            'error' => ''
        );
        
        if (isset($this->request->post['image_url']) && !empty($this->request->post['image_url'])) {
            $this->load->model('product/visual_search');
            
            // Get the OpenCart base URL from the request
            $opencart_base_url = isset($this->request->post['opencart_base_url']) ? $this->request->post['opencart_base_url'] : '';
            
            // Process the image URL
            $result = $this->model_product_visual_search->searchByUrl($this->request->post['image_url'], $opencart_base_url);
            
            if (isset($result['success']) && $result['success']) {
                $json = $result;
            } else {
                $json['error'] = isset($result['error']) ? $result['error'] : 'Failed to process the image URL';
            }
        } else {
            $json['error'] = 'No image URL provided';
        }
        
        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }
    
    /**
     * Get search results using session ID
     */
    public function results() {
        $json = array(
            'success' => false,
            'error' => '',
            'results' => array()
        );
        
        if (isset($this->request->get['session_id']) && !empty($this->request->get['session_id'])) {
            $this->load->model('product/visual_search');
            
            // Get results using session ID
            $result = $this->model_product_visual_search->getResults($this->request->get['session_id']);
            
            if (isset($result['success']) && $result['success']) {
                $json = $result;
            } else {
                $json['error'] = isset($result['error']) ? $result['error'] : 'Failed to get search results';
            }
        } else {
            $json['error'] = 'No session ID provided';
        }
        
        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }
}