
<?php
class ControllerApiHsn extends Controller {
    public function index() {
        $json = [];

        if (isset($this->request->get['hsn'])) {
            $this->load->model('catalog/hsn');
            $hsn_data = $this->model_catalog_hsn->getHsnData($this->request->get['hsn']);

            if ($hsn_data) {
                $json = [
                    'success' => true,
                    'data' => $hsn_data
                ];
            } else {
                
                $json = [
                    'success' => false,
                    'error' => 'HSN code not found.'
                ];
            }
        } else {
            $json = [
                'success' => false,
                'error' => 'HSN code is required.'
            ];
        }

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }
    
    public function check() {
        $json = [];
    
        if (isset($this->request->get['hsn_code'])) {
            $hsn_code = $this->request->get['hsn_code'];
    
            $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "hsn_data WHERE hsn_code = '" . $this->db->escape($hsn_code) . "'");
    
            if ($query->num_rows) {
                $json['exists'] = true;
                $json['message'] = 'This HSN code already exists.';
                $json['description'] = $query->row['description'];
                $json['gst_rate'] = $query->row['gst_rate'];
            } else {
                $json['exists'] = false;
            }
        }
    
        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }

}