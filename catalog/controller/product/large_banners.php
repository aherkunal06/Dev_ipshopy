<?php
class ControllerProductLargeBanners extends Controller {
    public function index($setting = []) {
        $this->load->model('tool/image');

        $banner_files = [
            ['image' => 'catalog/banners/large_banners/shoes.png', 'title' => 'shoes', 'path' => '2115'],
            ['image' => 'catalog/banners/large_banners/bags.png', 'title' => 'bags', 'path' => '3296'],
            ['image' => 'catalog/banners/large_banners/lipsticks.png', 'title' => 'lipsticks', 'path' => '3340'],
            ['image' => 'catalog/banners/large_banners/jewels.png', 'title' => 'jewels', 'path' => '2945'],
            ['image' => 'catalog/banners/large_banners/shirt.jpg', 'title' => 'fashion', 'path' => '2102'],
            
        ];

        $data['large_banners'] = [];

        foreach ($banner_files as $banner) {
        if (is_file(DIR_IMAGE . $banner['image'])) {
            $data['large_banners'][] = [
                'src'   => $this->model_tool_image->resize($banner['image'], 1280, 260), // Set width explicitly
                'link'  => $this->url->link('product/category', 'path=' . $banner['path']),
                'title' => $banner['title']
            ];
        }
    }


        return $this->load->view('product/large_banners', $data);
    }
}
