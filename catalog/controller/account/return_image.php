<?php
class ControllerAccountReturnImage extends Controller {
    public function index() {
        $this->load->model('account/return');
        $this->load->model('tool/image');

        $return_id = isset($this->request->get['return_id']) ? (int)$this->request->get['return_id'] : 0;
        $data['returns'] = array();

        if ($return_id) {
            $results = $this->model_account_return->getReturnImages($return_id);

            // Get return info (order id, product, etc.)
            $return_info = $this->model_account_return->getReturn($return_id);

            if ($return_info) {
                $data['returns'][$return_id] = array(
                    'return_id'  => $return_info['return_id'],
                    'order_id'   => $return_info['order_id'],
                    'product'    => $return_info['product'],
                    'images'     => array()
                );
            }

            foreach ($results as $result) {
                $image_path = (!empty($result['image']) && file_exists(DIR_IMAGE . $result['image']))
                    ? 'image/' . $result['image']
                    : 'image/no_image.png';

                $data['returns'][$return_id]['images'][] = $image_path;
            }
        }
        
        $data['cancel'] = $this->url->link('account/return');

        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');

        $this->response->setOutput($this->load->view('account/return_image', $data));
    }
}
?>