<?php
class Controllervendorsellerpagesfooter extends Controller {
	public function index() {

        $data['link_register_guide'] = $this->url->link('vendor/seller_pages/registration_guide', '', true);
        $data['link_fees'] = $this->url->link('vendor/seller_pages/fees', '', true);
        $data['link_launch'] = $this->url->link('vendor/seller_pages/launch', '', true);
        $data['link_program'] = $this->url->link('vendor/seller_pages/program', '', true);
        $data['link_how_to_grow'] = $this->url->link('vendor/seller_pages/how_to_grow', '', true);
        $data['link_blog'] = $this->url->link('vendor/seller_pages/business_hub_blog', '', true);
        $data['link_success_stories'] = $this->url->link('vendor/seller_pages/success_stories', '', true);
        $data['link_faq'] = $this->url->link('vendor/seller_pages/seller_faq', '', true);
        $data['link_terms'] = $this->url->link('vendor/seller_pages/terms_conditions', '', true);
        $data['link_privacy'] = $this->url->link('vendor/seller_pages/privacy_policy', '', true);
        $data['link_cookies'] = $this->url->link('vendor/seller_pages/cookies_policy', '', true);
        $data['link_ads'] = $this->url->link('vendor/seller_pages/ads_policy', '', true);

        
        return $this->load->view('vendor/seller_pages/footer', $data);
		
	}
}