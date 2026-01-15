<?php
class ControllerProductMediumBanners extends Controller {
  public function index($args = []) {
      $this->load->model('tool/image');
         $data['medium_banners'] = [
    [
        'link' => $this->url->link('product/category', 'path=274' ),
        'src' => $this->model_tool_image->resize('catalog/banners/medium_banners/mixer1.jpg', 451, 242),
        'title' => 'mixer'
    ],
    [
        'link' => $this->url->link('product/category', 'path=2131' ),
        'src' => $this->model_tool_image->resize('catalog/banners/medium_banners/sliper.jpg', 451, 242),
        'title' => 'sliper'
    ],
    [
        'link' => $this->url->link('product/category', 'path=2115' ),
        'src' => $this->model_tool_image->resize('catalog/banners/medium_banners/shoes.jpg', 451, 242),
        'title' => 'shoes'
    ],
    [
        'link' => $this->url->link('product/category', 'path=371' ),
        'src' => $this->model_tool_image->resize('catalog/banners/medium_banners/iron1.jpg', 451, 242),
        'title' => 'iron'
    ],
    [
        'link' => $this->url->link('product/category', 'path=89' ),
        'src' => $this->model_tool_image->resize('catalog/banners/medium_banners/earbuds1.jpg', 451, 242),
        'title' => 'earbuds'
    ],
    [
        'link' => $this->url->link('product/category', 'path=3296' ),
        'src' => $this->model_tool_image->resize('catalog/banners/medium_banners/bag.jpg', 451, 242),
        'title' => 'bags'
    ]
];

        return $this->load->view('product/medium_banners', $data);
        // $this->response->setOutput($this->load->view('product/medium_banners', $data));
    }
}
