<?php
class ControllerVendorWarehouse extends Controller
{
    public function index()
    {
        $this->load->language('vendor/warehouse');
        $this->document->setTitle('My Warehouses');
        $this->load->model('vendor/warehouse');

        $vendor_id = $this->vendor->getId();
        $data['warehouses'] = $this->model_vendor_warehouse->getWarehousesByVendorId($vendor_id);
        $data['max_reached'] = (count($data['warehouses']) >= 2);

        $data['add_url'] = $this->url->link('vendor/warehouse/add', '', true);
        $data['back'] = $this->url->link('vendor/dashboard', '', true);
        $data['warehouse'] = $this->url->link('vendor/warehouse', '', true);

        $data['header'] = $this->load->controller('vendor/header');
        $data['column_left'] = $this->load->controller('vendor/column_left');
        $data['footer'] = $this->load->controller('vendor/footer');

        $this->response->setOutput($this->load->view('vendor/warehouse_list', $data));
    }

    public function add()
    {
        $this->load->language('vendor/warehouse');
        $this->document->setTitle('Add Warehouse');
        $this->load->model('vendor/warehouse');
        $this->load->model('vendor/vendor');
        $this->load->model('localisation/country');

        $vendor_id = $this->vendor->getId();
        $vendor_info = $this->model_vendor_vendor->getVendor($vendor_id); // vendor table

        $warehouses = $this->model_vendor_warehouse->getWarehousesByVendorId($vendor_id);
        if (count($warehouses) >= 2) {
            $this->session->data['error'] = 'Maximum 2 warehouse addresses allowed.';
            $this->response->redirect($this->url->link('vendor/warehouse', '', true));
        }

        if ($this->request->server['REQUEST_METHOD'] == 'POST') {
            $this->request->post['firstname'] = $vendor_info['firstname'];
            $this->request->post['lastname'] = $vendor_info['lastname'];
            $this->request->post['display_name'] = $vendor_info['firstname'] . ' ' . $vendor_info['lastname'];
            $this->request->post['email'] = $vendor_info['email'];

            $this->model_vendor_warehouse->addWarehouse($this->request->post, $vendor_id);

            $this->session->data['success'] = 'Warehouse added successfully!';
            $this->response->redirect($this->url->link('vendor/warehouse', '', true));
        }

        $data['action'] = $this->url->link('vendor/warehouse/add', '', true);
        $data['back'] = $this->url->link('vendor/warehouse', '', true);

        if ($this->request->server['REQUEST_METHOD'] != 'POST') {
            $data['warehouse'] = [
                'firstname'     => $vendor_info['firstname'],
                'lastname'      => $vendor_info['lastname'],
                'display_name'  => $vendor_info['firstname'] . ' ' . $vendor_info['lastname'],
                'email'         => $vendor_info['email'],
                'telephone'     => '',
                'address_1'     => '',
                'address_2'     => '',
                'city'          => '',
                'zone_id'       => '',
                'country_id'    => $this->config->get('config_country_id'),
                'postcode'      => ''
            ];
        } else {
            $data['warehouse'] = $this->request->post;
            $data['warehouse']['display_name'] = $this->request->post['firstname'] . ' ' . $this->request->post['lastname'];
            $data['warehouse']['email'] = $vendor_info['email'];
            $data['warehouse']['telephone'] = $vendor_info['telephone'] ?? '';
        }

        $data['countries'] = $this->model_localisation_country->getCountries();
        $data['country_id'] = $data['warehouse']['country_id'] ?? $this->config->get('config_country_id');
        $data['zone_id'] = $data['warehouse']['zone_id'] ?? '';

        $data['header'] = $this->load->controller('vendor/header');
        $data['column_left'] = $this->load->controller('vendor/column_left');
        $data['footer'] = $this->load->controller('vendor/footer');

        $this->response->setOutput($this->load->view('vendor/warehouse_form', $data));
    }

    public function zone()
    {
        $json = [];
        $this->load->model('localisation/zone');

        if (isset($this->request->get['country_id'])) {
            $zones = $this->model_localisation_zone->getZonesByCountryId($this->request->get['country_id']);
            foreach ($zones as $zone) {
                $json[] = [
                    'zone_id' => $zone['zone_id'],
                    'name' => $zone['name']
                ];
            }
        }

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }

    public function getWarehousesForDropdown()
    {
        $this->load->model('vendor/warehouse');

        $vendor_id = $this->vendor->getId();
        $warehouses = $this->model_vendor_warehouse->getWarehousesByVendorId($vendor_id);

        return $warehouses;
    }

}
