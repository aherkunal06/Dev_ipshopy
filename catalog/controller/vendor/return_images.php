<?php
class ControllerVendorReturnImages extends Controller {

    public function index() {
        // Load language and set the document title
        $this->load->language('vendor/return_orders');
        $this->document->setTitle($this->language->get('return_image_heading_title'));
        

        $data['cancel'] = $this->url->link('vendor/return_orders', '', true);
        

        // Load models for returns and images
        $this->load->model('vendor/return_orders') ;
        $this->load->model('tool/image');
        if (isset($this->request->get['return_ids'])) {
            $return_ids = $this->request->get['return_ids'];
        } else {
            $return_ids = null;  // or handle the case where the parameter is not provided
        }
    
        // Get the return_id and vendor_id from the request
          $vendor_id = $this->vendor->getId();
  

      
        $data['returns'] = array();

        if ($return_ids || $vendor_id) {
            $results = $this->model_vendor_return_orders->getReturnsImage($return_ids, $vendor_id);  // Fix this line
            
            foreach ($results as $result) {
                $image_path = (!empty($result['image']) && file_exists(DIR_IMAGE . $result['image'])) 
                ? HTTPS_SERVER . 'image/' . $result['image'] 
                : HTTPS_SERVER . 'image/no_image.png';
                // $image_path = (!empty($result['image']) && file_exists(DIR_IMAGE . $result['image'])) 
                //     ? HTTPS_CATALOG . 'image/' . $result['image'] 
                //     : HTTPS_CATALOG . 'image/no_image.png';

                // Group images under each return_id
                if (!isset($data['returns'][$result['return_id']])) {
                    $data['returns'][$result['return_id']] = array(
                        'return_id'  => $result['return_id'],
                        'order_id'   => $result['order_id'],
                        'product'    => $result['product'],
                        'images'     => array()
                    );
                }

                $data['returns'][$result['return_id']]['images'][] = $image_path;
            }
        }




        // Load header, footer, and left column controllers
        $data['header'] = $this->load->controller('vendor/header');
        $data['column_left'] = $this->load->controller('vendor/column_left');
        $data['footer'] = $this->load->controller('vendor/footer');

        // Set the output view with the loaded data
        $this->response->setOutput($this->load->view('vendor/return_images', $data));
        
    }

    
}
?>
