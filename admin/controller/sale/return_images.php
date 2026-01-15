<?php
class ControllerSaleReturnImages extends Controller {
    public function index() {
        $this->load->language('sale/return');
        $this->document->setTitle($this->language->get('heading_title'));

        $this->load->model('sale/return');
        $this->load->model('tool/image');

        $return_id = isset($this->request->get['return_id']) ? (int)$this->request->get['return_id'] : 0;
        $data['returns'] = array();

        if ($return_id) {
            $results = $this->model_sale_return->getReturnsImage($return_id);

            foreach ($results as $result) {
                $image_path = (!empty($result['image']) && file_exists(DIR_IMAGE . $result['image'])) 
                    ? HTTPS_CATALOG . 'image/' . $result['image'] 
                    : HTTPS_CATALOG . 'image/no_image.png';

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

        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');

        $this->response->setOutput($this->load->view('sale/return_images', $data));
    }
}
?>
