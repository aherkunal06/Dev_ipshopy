<?php
class ModelVendorStore extends Model {
			
	public function getViewStore($vendor_id){
		$sql = "SELECT * FROM " . DB_PREFIX . "vendor v LEFT JOIN " . DB_PREFIX . "vendor_description vd ON (v.vendor_id = vd.vendor_id) WHERE vd.language_id = '" . (int)$this->config->get('config_language_id') . "' and v.vendor_id='".(int)$vendor_id."'";
		$query=$this->db->query($sql);
		return $query->row;
	}
	
	public function deleteVendor($vendor_id) {
		$sql="delete  from " . DB_PREFIX . "vendor_description where vendor_id='".(int)$vendor_id."'";
		$query=$this->db->query($sql);
	}
	
	public function getStores($data=array()){
		$sql = "SELECT * FROM " . DB_PREFIX . "vendor v LEFT JOIN " . DB_PREFIX . "vendor_description vd ON (v.vendor_id = vd.vendor_id) WHERE vd.language_id = '" . (int)$this->config->get('config_language_id') . "'";
		
		if(isset($data['vendor_id'])){
			$sql .= " and v.vendor_id='". (int)$data['vendor_id']."'";
		}
		$sql .= " GROUP BY v.vendor_id";
		
		$sort_data = array(
			'vd.name'
		);
		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
		 	$sql .= " ORDER BY " . $data['sort'];
		} else {
		 	$sql .= " ORDER BY vd.name";
		}
		if (isset($data['order']) && ($data['order'] == 'DESC')) {
		 	$sql .= " DESC";
		} else {
		 	$sql .= " ASC";
		}
		if (isset($data['start']) || isset($data['limit'])) {
			if ($data['start'] < 0) {
				$data['start'] = 0;
			}
			if ($data['limit'] < 1) {
				$data['limit'] = 20;
			}
			$sql .= " LIMIT " . (int)$data['start'] . "," . (int)$data['limit'];
		}
		$query = $this->db->query($sql);
		return $query->rows;	
 	}
	
	public function getTotalVendorStore($data) {
		$sql = "SELECT COUNT(*) AS total FROM " . DB_PREFIX . "vendor v LEFT JOIN " . DB_PREFIX . "vendor_description vd ON (v.vendor_id = vd.vendor_id) WHERE vd.language_id = '" . (int)$this->config->get('config_language_id') . "' and v.vendor_id<>0";
		
		if(isset($data['vendor_id'])){
			$sql .= " and v.vendor_id='".(int)$data['vendor_id']."'";
		}
		
		$query = $this->db->query($sql);
		return $query->row['total'];
	}
	
	public function getProducts($data = array()) {
		$sql = "SELECT p.product_id, (SELECT AVG(rating) AS total FROM " . DB_PREFIX . "review r1 WHERE r1.product_id = p.product_id AND r1.status = '1' GROUP BY r1.product_id) AS rating, (SELECT price FROM " . DB_PREFIX . "product_discount pd2 WHERE pd2.product_id = p.product_id AND pd2.customer_group_id = '" . (int)$this->config->get('config_customer_group_id') . "' AND pd2.quantity = '1' AND ((pd2.date_start = '0000-00-00' OR pd2.date_start < NOW()) AND (pd2.date_end = '0000-00-00' OR pd2.date_end > NOW())) ORDER BY pd2.priority ASC, pd2.price ASC LIMIT 1) AS discount, (SELECT price FROM " . DB_PREFIX . "product_special ps WHERE ps.product_id = p.product_id AND ps.customer_group_id = '" . (int)$this->config->get('config_customer_group_id') . "' AND ((ps.date_start = '0000-00-00' OR ps.date_start < NOW()) AND (ps.date_end = '0000-00-00' OR ps.date_end > NOW())) ORDER BY ps.priority ASC, ps.price ASC LIMIT 1) AS special";

		if (!empty($data['filter_category_id'])) {
			if (!empty($data['filter_sub_category'])) {
				$sql .= " FROM " . DB_PREFIX . "category_path cp LEFT JOIN " . DB_PREFIX . "product_to_category p2c ON (cp.category_id = p2c.category_id)";
			} else {
				$sql .= " FROM " . DB_PREFIX . "product_to_category p2c";
			}

			if (!empty($data['filter_filter'])) {
				$sql .= " LEFT JOIN " . DB_PREFIX . "product_filter pf ON (p2c.product_id = pf.product_id) LEFT JOIN " . DB_PREFIX . "product p ON (pf.product_id = p.product_id)";
			} else {
				$sql .= " LEFT JOIN " . DB_PREFIX . "product p ON (p2c.product_id = p.product_id)";
			}
		} else {
			$sql .= " FROM " . DB_PREFIX . "product p";
		}

		$sql .= " LEFT JOIN " . DB_PREFIX . "product_description pd ON (p.product_id = pd.product_id) LEFT JOIN " . DB_PREFIX . "product_to_store p2s ON (p.product_id = p2s.product_id) LEFT JOIN " . DB_PREFIX . "vendor_to_product v2p ON (p.product_id = v2p.product_id)  WHERE pd.language_id = '" . (int)$this->config->get('config_language_id') . "' AND p.status = '1' AND p.date_available <= NOW() AND p2s.store_id = '" . (int)$this->config->get('config_store_id') . "' ";

		if (!empty($data['filter_category_id'])) {
			if (!empty($data['filter_sub_category'])) {
				$sql .= " AND cp.path_id = '" . (int)$data['filter_category_id'] . "'";
			} else {
				$sql .= " AND p2c.category_id = '" . (int)$data['filter_category_id'] . "'";
			}

			if (!empty($data['filter_filter'])) {
				$implode = array();

				$filters = explode(',', $data['filter_filter']);

				foreach ($filters as $filter_id) {
					$implode[] = (int)$filter_id;
				}

				$sql .= " AND pf.filter_id IN (" . implode(',', $implode) . ")";
			}
		}

		if (!empty($data['filter_name']) || !empty($data['filter_tag'])) {
			$sql .= " AND (";

			if (!empty($data['filter_name'])) {
				$implode = array();

				$words = explode(' ', trim(preg_replace('/\s+/', ' ', $data['filter_name'])));

				foreach ($words as $word) {
					$implode[] = "pd.name LIKE '%" . $this->db->escape($word) . "%'";
				}

				if ($implode) {
					$sql .= " " . implode(" AND ", $implode) . "";
				}

				if (!empty($data['filter_description'])) {
					$sql .= " OR pd.description LIKE '%" . $this->db->escape($data['filter_name']) . "%'";
				}
			}

			if (!empty($data['filter_name']) && !empty($data['filter_tag'])) {
				$sql .= " OR ";
			}

			if (!empty($data['filter_tag'])) {
				$sql .= "pd.tag LIKE '%" . $this->db->escape($data['filter_tag']) . "%'";
			}

			if (!empty($data['filter_name'])) {
				$sql .= " OR LCASE(p.model) = '" . $this->db->escape(utf8_strtolower($data['filter_name'])) . "'";
				$sql .= " OR LCASE(p.sku) = '" . $this->db->escape(utf8_strtolower($data['filter_name'])) . "'";
				$sql .= " OR LCASE(p.upc) = '" . $this->db->escape(utf8_strtolower($data['filter_name'])) . "'";
				$sql .= " OR LCASE(p.ean) = '" . $this->db->escape(utf8_strtolower($data['filter_name'])) . "'";
				$sql .= " OR LCASE(p.jan) = '" . $this->db->escape(utf8_strtolower($data['filter_name'])) . "'";
				$sql .= " OR LCASE(p.isbn) = '" . $this->db->escape(utf8_strtolower($data['filter_name'])) . "'";
				$sql .= " OR LCASE(p.mpn) = '" . $this->db->escape(utf8_strtolower($data['filter_name'])) . "'";
			}

			$sql .= ")";
		}

		if (!empty($data['filter_manufacturer_id'])) {
			$sql .= " AND p.manufacturer_id = '" . (int)$data['filter_manufacturer_id'] . "'";
		}
		
		
		if(isset($data['vendor_id'])){
			$sql .= " AND v2p.vendor_id='".(int)$data['vendor_id']."'";
		}
	
		$sql .= " GROUP BY p.product_id";

		$sort_data = array(
			'pd.name',
			'p.model',
			'p.quantity',
			'p.price',
			'rating',
			'p.sort_order',
			'p.date_added'
		);

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			if ($data['sort'] == 'pd.name' || $data['sort'] == 'p.model') {
				$sql .= " ORDER BY LCASE(" . $data['sort'] . ")";
			} elseif ($data['sort'] == 'p.price') {
				$sql .= " ORDER BY (CASE WHEN special IS NOT NULL THEN special WHEN discount IS NOT NULL THEN discount ELSE p.price END)";
			} else {
				$sql .= " ORDER BY " . $data['sort'];
			}
		} else {
			$sql .= " ORDER BY p.sort_order";
		}

		if (isset($data['order']) && ($data['order'] == 'DESC')) {
			$sql .= " DESC, LCASE(pd.name) DESC";
		} else {
			$sql .= " ASC, LCASE(pd.name) ASC";
		}

		if (isset($data['start']) || isset($data['limit'])) {
			if ($data['start'] < 0) {
				$data['start'] = 0;
			}

			if ($data['limit'] < 1) {
				$data['limit'] = 20;
			}

			$sql .= " LIMIT " . (int)$data['start'] . "," . (int)$data['limit'];
		}

		$product_data = array();

		$query = $this->db->query($sql);

		foreach ($query->rows as $result) {
			$this->load->model('catalog/product');
			$product_data[$result['product_id']] = $this->model_catalog_product->getProduct($result['product_id']);
		}

		return $product_data;
		
		
	}
	
	public function getTotalProducts($data = array()) {
		$sql = "SELECT COUNT(DISTINCT p.product_id) AS total";

		if (!empty($data['filter_category_id'])) {
			if (!empty($data['filter_sub_category'])) {
				$sql .= " FROM " . DB_PREFIX . "category_path cp LEFT JOIN " . DB_PREFIX . "product_to_category p2c ON (cp.category_id = p2c.category_id)";
			} else {
				$sql .= " FROM " . DB_PREFIX . "product_to_category p2c";
			}

			if (!empty($data['filter_filter'])) {
				$sql .= " LEFT JOIN " . DB_PREFIX . "product_filter pf ON (p2c.product_id = pf.product_id) LEFT JOIN " . DB_PREFIX . "product p ON (pf.product_id = p.product_id)";
			} else {
				$sql .= " LEFT JOIN " . DB_PREFIX . "product p ON (p2c.product_id = p.product_id)";
			}
		} else {
			$sql .= " FROM " . DB_PREFIX . "product p";
		}

		$sql .= " LEFT JOIN " . DB_PREFIX . "product_description pd ON (p.product_id = pd.product_id) LEFT JOIN " . DB_PREFIX . "product_to_store p2s ON (p.product_id = p2s.product_id) LEFT JOIN " . DB_PREFIX . "vendor_to_product v2p ON (p.product_id = v2p.product_id)  WHERE pd.language_id = '" . (int)$this->config->get('config_language_id') . "' AND p.status = '1' AND p.date_available <= NOW() AND p2s.store_id = '" . (int)$this->config->get('config_store_id') . "'";

		if (!empty($data['filter_category_id'])) {
			if (!empty($data['filter_sub_category'])) {
				$sql .= " AND cp.path_id = '" . (int)$data['filter_category_id'] . "'";
			} else {
				$sql .= " AND p2c.category_id = '" . (int)$data['filter_category_id'] . "'";
			}

			if (!empty($data['filter_filter'])) {
				$implode = array();

				$filters = explode(',', $data['filter_filter']);

				foreach ($filters as $filter_id) {
					$implode[] = (int)$filter_id;
				}

				$sql .= " AND pf.filter_id IN (" . implode(',', $implode) . ")";
			}
		}

		if (!empty($data['filter_name']) || !empty($data['filter_tag'])) {
			$sql .= " AND (";

			if (!empty($data['filter_name'])) {
				$implode = array();

				$words = explode(' ', trim(preg_replace('/\s+/', ' ', $data['filter_name'])));

				foreach ($words as $word) {
					$implode[] = "pd.name LIKE '%" . $this->db->escape($word) . "%'";
				}

				if ($implode) {
					$sql .= " " . implode(" AND ", $implode) . "";
				}

				if (!empty($data['filter_description'])) {
					$sql .= " OR pd.description LIKE '%" . $this->db->escape($data['filter_name']) . "%'";
				}
			}

			if (!empty($data['filter_name']) && !empty($data['filter_tag'])) {
				$sql .= " OR ";
			}

			if (!empty($data['filter_tag'])) {
				$sql .= "pd.tag LIKE '%" . $this->db->escape(utf8_strtolower($data['filter_tag'])) . "%'";
			}

			if (!empty($data['filter_name'])) {
				$sql .= " OR LCASE(p.model) = '" . $this->db->escape(utf8_strtolower($data['filter_name'])) . "'";
				$sql .= " OR LCASE(p.sku) = '" . $this->db->escape(utf8_strtolower($data['filter_name'])) . "'";
				$sql .= " OR LCASE(p.upc) = '" . $this->db->escape(utf8_strtolower($data['filter_name'])) . "'";
				$sql .= " OR LCASE(p.ean) = '" . $this->db->escape(utf8_strtolower($data['filter_name'])) . "'";
				$sql .= " OR LCASE(p.jan) = '" . $this->db->escape(utf8_strtolower($data['filter_name'])) . "'";
				$sql .= " OR LCASE(p.isbn) = '" . $this->db->escape(utf8_strtolower($data['filter_name'])) . "'";
				$sql .= " OR LCASE(p.mpn) = '" . $this->db->escape(utf8_strtolower($data['filter_name'])) . "'";
			}

			$sql .= ")";
		}
		if(isset($data['vendor_id'])){
			$sql .= " and v2p.vendor_id='". (int)$data['vendor_id']."'";
		}
		
		if (!empty($data['filter_manufacturer_id'])) {
			$sql .= " AND p.manufacturer_id = '" . (int)$data['filter_manufacturer_id'] . "'";
		}

		$query = $this->db->query($sql);

		return $query->row['total'];
	}
	
	public function getReviewField($review_id){
		$sql="select * from " . DB_PREFIX . "vendor_review_field_submit where review_id='".(int)$review_id."'";
		$query=$this->db->query($sql);
		return $query->row;
	}
	
	public function getReviews($data){
		$sql="select * from " . DB_PREFIX . "vendor_review where review_id<>0";
			
		if(isset($data['vendor_id'])){
			$sql .= " and vendor_id='".(int)$data['vendor_id']."'";
		}
		
		//$sql .= " ORDER BY  date_added DESC";
		
		if (isset($data['start']) || isset($data['limit'])) {
			if ($data['start'] < 0) {
				$data['start'] = 0;
			}

			if ($data['limit'] < 1) {
				$data['limit'] = 20;
			}

			$sql .= " LIMIT " . (int)$data['start'] . "," . (int)$data['limit'];
		}
		
		$query=$this->db->query($sql);
		return $query->rows;
	}
	
	public function getTotalSellerReview($data) {
		$sql = "SELECT COUNT(*) AS total FROM " . DB_PREFIX . "vendor_review where vendor_id<>0";
		if(isset($data['vendor_id'])){
			$sql .= " and vendor_id='".(int)$data['vendor_id']."'";
		}
		$query = $this->db->query($sql);
		return $query->row['total'];
	}
	
	public function getProductReviewStatus($product_id){
		$sql="select *, status as rstatus from " . DB_PREFIX . "review where product_id='".(int)$product_id."'";
		$query=$this->db->query($sql);
		
		return $query->row;
	}
	
	
	//   Best Selling Products
	public function getBestSellingProducts($data = array()) {
		if (!isset($data['vendor_id'])) {
			return array();
		}

		$language_id = (int)$this->config->get('config_language_id');
		$customer_group_id = (int)$this->config->get('config_customer_group_id');
		$today = date('Y-m-d');
		$limit = isset($data['limit']) ? (int)$data['limit'] : 10;
		$vendor_id = (int)$data['vendor_id'];
		$period_condition = isset($data['period']) && (int)$data['period'] > 0 ? " AND o.date_added >= DATE_SUB(NOW(), INTERVAL {$data['period']} DAY)" : "";

    	$sql = "SELECT 
                p.product_id, pd.name, p.image, p.price AS mrp, ps.price AS special_price, 
                SUM(op.quantity) AS total_ordered
            FROM " . DB_PREFIX . "product p
            JOIN " . DB_PREFIX . "product_description pd ON p.product_id = pd.product_id AND pd.language_id = '" . $language_id . "'
            LEFT JOIN " . DB_PREFIX . "product_special ps ON ps.product_id = p.product_id
                AND ps.customer_group_id = '" . $customer_group_id . "' 
                AND ps.date_start <= '" . $today . "'
                AND (ps.date_end = '0000-00-00' OR ps.date_end >= '" . $today . "')
            JOIN " . DB_PREFIX . "order_product op ON p.product_id = op.product_id
            JOIN " . DB_PREFIX . "order o ON op.order_id = o.order_id AND o.order_status_id > 0
            INNER JOIN " . DB_PREFIX . "vendor_to_product vp ON p.product_id = vp.product_id -- Join with vendor_to_product table
            WHERE p.status = 1 
              AND vp.vendor_id = '" . (int)$vendor_id . "' -- Filter by current vendor
              $period_condition
            GROUP BY p.product_id
            ORDER BY total_ordered DESC
            LIMIT " . $limit;

		$result = $this->db->query($sql);
		return $result->rows;
	}


	   // latest products //
	public function getLatestProducts($data = array()) {
		if (!isset($data['vendor_id'])) {
			return array();
		}

		$language_id = (int)$this->config->get('config_language_id');
		$today = date('Y-m-d');
		$limit = isset($data['limit']) ? (int)$data['limit'] : 10; 
		$vendor_id = (int)$data['vendor_id']; 

		$sql = "SELECT 
					p.product_id, p.image, p.price AS mrp, ps.price AS special_price, pd.name, p.date_added
				FROM " . DB_PREFIX . "product p
				LEFT JOIN " . DB_PREFIX . "product_description pd ON (p.product_id = pd.product_id)
				INNER JOIN " . DB_PREFIX . "vendor_to_product vp ON (p.product_id = vp.product_id)
				LEFT JOIN " . DB_PREFIX . "product_special ps 
					ON (p.product_id = ps.product_id 
						AND ps.customer_group_id = '" . $customer_group_id . "' 
						AND ps.date_start <= '" . $today . "' 
						AND (ps.date_end = '0000-00-00' OR ps.date_end >= '" . $today . "')
					)
				WHERE p.date_added >= DATE_SUB(NOW(), INTERVAL 10 DAY) -- Filter for last 10 days
				AND pd.language_id = '" . $language_id . "'
				AND p.status = 1
				AND vp.vendor_id = '" . (int)$vendor_id . "' -- Filter by current vendor
				GROUP BY p.product_id
				ORDER BY p.date_added DESC
				LIMIT " . $limit;

		$result = $this->db->query($sql);

		return $result->rows;
	}


	// most discounted products //
	public function getMostDiscountedProducts($data = array()) {
		if (!isset($data['vendor_id'])) {
			return array();
		}

		$language_id = (int)$this->config->get('config_language_id');
		$customer_group_id = (int)$this->config->get('config_customer_group_id');
		$today = date('Y-m-d');
		$limit = isset($data['limit']) ? (int)$data['limit'] : 10;
		$vendor_id = (int)$data['vendor_id'];

		$sql = "SELECT 
					p.product_id, p.image, p.price AS mrp, ps.price AS selling_price, pd.name,
					AVG(r.rating) AS rating,
					((p.price - ps.price) / p.price * 100) AS discount_percent
				FROM " . DB_PREFIX . "product p
				LEFT JOIN " . DB_PREFIX . "product_description pd ON (p.product_id = pd.product_id)
				LEFT JOIN " . DB_PREFIX . "review r ON (p.product_id = r.product_id AND r.status = 1)
				LEFT JOIN " . DB_PREFIX . "product_special ps 
					ON (p.product_id = ps.product_id 
						AND ps.customer_group_id = '" . $customer_group_id . "' 
						AND ps.date_start <= '" . $today . "' 
						AND (ps.date_end = '0000-00-00' OR ps.date_end >= '" . $today . "')
					)
				INNER JOIN " . DB_PREFIX . "vendor_to_product vp ON (p.product_id = vp.product_id) -- Join with vendor_to_product table
				WHERE pd.language_id = '" . $language_id . "'
				AND vp.vendor_id = '" . (int)$vendor_id . "' -- Filter by current vendor
				AND ps.price IS NOT NULL 
				AND ps.price < p.price
				AND ((p.price - ps.price) / p.price * 100) >= 30  -- Filter for 30% discount or more
				GROUP BY p.product_id
				ORDER BY discount_percent DESC
				LIMIT " . $limit;

		$query = $this->db->query($sql);

		return $query->rows;
	}
	
	
}
