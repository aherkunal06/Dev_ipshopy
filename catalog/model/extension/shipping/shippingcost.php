<?php
class ModelExtensionShippingShippingcost extends Model {
	function getQuote($address) {

		$this->load->language('extension/shipping/shippingcost');

		$status = true;
		
		if(!empty($address['country_id'])){
			$country_id = $address['country_id'];
		} else {
			$country_id = '';
		}

		if(!empty($address['postcode'])){
			$postcode = $address['postcode'];
		} else {
			$postcode = '';
		}

		$this->load->model('vendor/vendor');
		$this->load->model('catalog/product');
		
		$allprice = 0;
		$products = $this->cart->getProducts();
		$shippingcost=array();
		
		foreach ($products as $product) {
			if($product['shipping']==1){
		
			if(isset($product['sellerdisplay'])){
				$sellerdisplay = $product['sellerdisplay'];
			} else {
				$sellerdisplay = '';
			}
			
			if(isset($product['vendor_ids'])){
				$vendor_ids = $product['vendor_ids'];
			} else {
				$vendor_ids = '';
			}
			
			if(isset($product['weight'])){
				$weight = $product['weight'];
			} else {
				$weight = 0;
			}

			$data['products'][] = array(
				'cart_id'   => $product['cart_id'],
				'name'      => $product['name'],
				'model'     => $product['model'],
			);

			
			$queryshippingcost = $this->db->query("SELECT * FROM " . DB_PREFIX . "shipping WHERE country_id = '" . $country_id . "' AND zip_from = '". $postcode ."'  AND weight_from <= '". $weight ."' AND weight_to >= '". $weight ."' AND vendor_id = '" . $vendor_ids . "' limit 0,1");
			
			
			if(!empty($queryshippingcost->row)){
				foreach($queryshippingcost->rows as $result){
					$allprice += $result['price'];
				    $shippingcost[$product['product_id']]=$this->tax->calculate($result['price'], $this->config->get('shipping_shippingcost_tax_class_id'), $this->config->get('config_tax'));
				}	
			} else {
				$this->load->model('vendor/vendor');
				$seller_info = $this->model_vendor_vendor->getSellerInfo($vendor_ids);
				
				if(!empty($seller_info['shipping_charge'])){
				$allprice += $seller_info['shipping_charge'];
					$shippingcost[$product['product_id']]=$this->tax->calculate($seller_info['shipping_charge'], $this->config->get('shipping_shippingcost_tax_class_id'), $this->config->get('config_tax'));
				}
			}				
		}
		}

		$this->session->data['tmdshippingcost']=$shippingcost;	
		
		$method_data = array();

		if ($status && $allprice!=0) {
			$quote_data = array();

			$shipping_shippingcost = $this->config->get('shipping_shippingcost');

			 if(!empty($shipping_shippingcost[$this->config->get('config_language_id')]['title'])){
	            $shippingcost = $shipping_shippingcost[$this->config->get('config_language_id')]['title'];
	        } else {
	            $shippingcost = $this->language->get('text_description');
	        }

			$quote_data['shippingcost'] = array(
				'code'         => 'shippingcost.shippingcost',
				'title'        => $shippingcost,
				'cost'         => $this->tax->calculate($allprice, $this->config->get('shipping_shippingcost_tax_class_id'), $this->config->get('config_tax')),
				'tax_class_id' =>0,
				'text'         => $this->currency->format($this->tax->calculate($allprice, $this->config->get('shipping_shippingcost_tax_class_id'), $this->config->get('config_tax')), $this->session->data['currency'])
			);

			$method_data = array(
				'code'       => 'shippingcost',
				'title'      => $this->language->get('text_title'),
				'quote'      => $quote_data,
				'sort_order' => $this->config->get('shipping_shippingcost_sort_order'),
				'error'      => false
			);
		}

		return $method_data;
	}
}