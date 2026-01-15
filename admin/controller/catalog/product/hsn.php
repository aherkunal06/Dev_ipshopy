<?php

class ControllerCatalogProductHsn extends Controller {
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
}

?>