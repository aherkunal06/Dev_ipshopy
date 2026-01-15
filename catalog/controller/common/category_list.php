<?php
class ControllerCommonCategoryList extends Controller {
    public function index() {
              $this->load->model('tool/image');
         $data['categories'] = [
    [
        'link' => $this->url->link('product/category', 'path=77' ),
        'src' => $this->model_tool_image->resize('catalog/A - SAGAR/monitor.jpg', 100, 100),
        'title' => 'Monitor'
        
    ],
    [
        'link' => $this->url->link('product/category', 'path=608' ),
        'src' => $this->model_tool_image->resize('catalog/A - SAGAR/keyboard_mouse.jpg', 100, 100),
        'title' => 'Keyboards Mouse'
    ],
    [
        'link' => $this->url->link('product/category', 'path=80' ),
        'src' => $this->model_tool_image->resize('catalog/banners/category/headphone_green.jpg', 100, 100),
        'title' => 'Headphone'
    ],
    [
        'link' => $this->url->link('product/category', 'path=388' ),
        'src' => $this->model_tool_image->resize('catalog/Bajaj-Energos26-1200mm-Silent-BLDC-Ceiling-Fan5-StarRated-Energy-Efficie/Bajaj-Energos26-1200mm-Silent-BLDC-Ceiling-Fan5-StarRated-Energy-Efficient-Ceili.jpg', 100, 100),
        'title' => 'Fan'
        
    ],
    [
        'link' => $this->url->link('product/category', 'path=371' ),
        'src' => $this->model_tool_image->resize('catalog/Havells-Dzire-1000-watt-Dry-Iron-With-American-Heritage-Sole-Plate-Aerod/Havells-Dzire-1000-watt-Dry-Iron-With-American-Heritage-Sole-Plate-Aerodynamic-D.jpg', 100, 100),
        'title' => 'Iron'
    ],
    [
        'link' => $this->url->link('product/category', 'path=224' ),
        'src' => $this->model_tool_image->resize('catalog/A - SAGAR/induction.jpg', 100, 100),
        'title' => 'Induction'
    ],
    [
        'link' => $this->url->link('product/category', 'path=140' ),
        'src' => $this->model_tool_image->resize('catalog/A-Satyashil/Home/cello-cassrole.png', 100, 100),
        'title' => 'Casserole'
        
    ],
    [
        'link' => $this->url->link('product/category', 'path=213' ),
        'src' => $this->model_tool_image->resize('catalog/A - SAGAR/rotimaker.jpg', 100, 100),
        'title' => 'Roti Maker'
    ],
    [
        'link' => $this->url->link('product/category', 'path=109' ),
        'src' => $this->model_tool_image->resize('catalog/banners/category/Hair_Dryer_Blue.jpg', 100, 100),
        'title' => 'Dryer'
    ],
    [
        'link' => $this->url->link('product/category', 'path=107' ),
        'src' => $this->model_tool_image->resize('catalog/A - SAGAR/Straightener.jpg', 100, 100),
        'title' => 'Straightener'
    ],
    ];
        
        // $this->response->setOutput($this->load->view('common/category_list', $data));
        return $this->load->view('common/category_list', $data);
    }
    
}
