<?php
class ControllerExtensionModulePopularBrands extends Controller
{
    public function index()
    {
        // $this->load->model('catalog/manufacturer');

        // $brands_data = $this->model_catalog_manufacturer->getManufacturers();
        $data['brands'] = [];

        // foreach ($brands_data as $brand) {
        // if ($brand['image']) {
        // $data['brands'][] = [
        //     'name'  => $brand['name'],
        //     'image' => $this->model_tool_image->resize($brand['image'], 100, 100)
        // ];
        $this->load->model('tool/image');
        $data['categories'] = [
            [
                'link' => $this->url->link('product/category', 'path=77'),
                'image' => $this->model_tool_image->resize('catalog/A - SAGAR/monitor.jpg', 100, 100),
                'name' => 'Monitor'

            ],
            [
                'link' => $this->url->link('product/category', 'path=608'),
                'image' => $this->model_tool_image->resize('catalog/A - SAGAR/keyboard_mouse.jpg', 100, 100),
                'name' => 'Keyboards Mouse'
            ],
            [
                'link' => $this->url->link('product/category', 'path=80'),
                'image' => $this->model_tool_image->resize('catalog/banners/category/headphone_green.jpg', 100, 100),
                'name' => 'Headphone'
            ],
            [
                'link' => $this->url->link('product/category', 'path=388'),
                'image' => $this->model_tool_image->resize('catalog/Bajaj-Energos26-1200mm-Silent-BLDC-Ceiling-Fan5-StarRated-Energy-Efficie/Bajaj-Energos26-1200mm-Silent-BLDC-Ceiling-Fan5-StarRated-Energy-Efficient-Ceili.jpg', 100, 100),
                'name' => 'Fan'

            ],
            [
                'link' => $this->url->link('product/category', 'path=371'),
                'image' => $this->model_tool_image->resize('catalog/Havells-Dzire-1000-watt-Dry-Iron-With-American-Heritage-Sole-Plate-Aerod/Havells-Dzire-1000-watt-Dry-Iron-With-American-Heritage-Sole-Plate-Aerodynamic-D.jpg', 100, 100),
                'name' => 'Iron'
            ],
            [
                'link' => $this->url->link('product/category', 'path=224'),
                'image' => $this->model_tool_image->resize('catalog/A - SAGAR/induction.jpg', 100, 100),
                'name' => 'Induction'
            ],
            [
                'link' => $this->url->link('product/category', 'path=140'),
                'image' => $this->model_tool_image->resize('catalog/A-Satyashil/Home/cello-cassrole.png', 100, 100),
                'name' => 'Casserole'

            ],
            [
                'link' => $this->url->link('product/category', 'path=213'),
                'image' => $this->model_tool_image->resize('catalog/A - SAGAR/rotimaker.jpg', 100, 100),
                'name' => 'Roti Maker'
            ],
            [
                'link' => $this->url->link('product/category', 'path=109'),
                'image' => $this->model_tool_image->resize('catalog/banners/category/Hair_Dryer_Blue.jpg', 100, 100),
                'name' => 'Dryer'
            ],
            [
                'link' => $this->url->link('product/category', 'path=107'),
                'image' => $this->model_tool_image->resize('catalog/A - SAGAR/Straightener.jpg', 100, 100),
                'name' => 'Straightener'
            ],
        ];
        //     }
        // }

        return $this->load->view('product/popular_brands', $data);
    }
}
