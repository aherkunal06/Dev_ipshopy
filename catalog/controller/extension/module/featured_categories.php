<?php
class ControllerExtensionModuleFeaturedCategories extends Controller
{
    public function index()
    {
        $this->load->model('catalog/category');
        $this->load->model('catalog/product'); // Needed for getTotalProducts
        $this->load->model('tool/image');

        $data['categories'] = [];

        $featured_categories = [20, 28, 34, 18, 25]; // Your category IDs

        foreach ($featured_categories as $category_id) {
            $category = $this->model_catalog_category->getCategory($category_id);
            if ($category) {
                $image = $category['image'] ?
                    $this->model_tool_image->resize($category['image'], 200, 100) :
                    'placeholder.png';

                $data['categories'][] = [
                    'name'  => $category['name'],
                    'image' => $image,
                    'total' => $this->model_catalog_product->getTotalProducts(['filter_category_id' => $category_id]),
                    'href'  => $this->url->link('product/category', 'path=' . $category_id)
                ];
            }
        }

        $data['view_all_url'] = $this->url->link('product/search');

        return $this->load->view('product/featured_category', $data);
    }
}
