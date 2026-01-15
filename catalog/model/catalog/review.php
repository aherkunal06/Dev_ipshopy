<?php
class ModelCatalogReview extends Model {
public function addReview($product_id, $data, $images = [], $customer_id = 0) {
   

	// Changes By Sheetal

  $image1 = isset($images[0]) && $images[0] !== '' ? "'" . $this->db->escape($images[0]) . "'" : 'NULL';
    $image2 = isset($images[1]) && $images[1] !== '' ? "'" . $this->db->escape($images[1]) . "'" : 'NULL';
    $image3 = isset($images[2]) && $images[2] !== '' ? "'" . $this->db->escape($images[2]) . "'" : 'NULL';
    $image4 = isset($images[3]) && $images[3] !== '' ? "'" . $this->db->escape($images[3]) . "'" : 'NULL';
    $image5 = isset($images[4]) && $images[4] !== '' ? "'" . $this->db->escape($images[4]) . "'" : 'NULL';


	// Determine the customer_id
$customer_id = ($this->customer->isLogged()) ? (int)$this->customer->getId() : 0;



    $this->db->query("INSERT INTO " . DB_PREFIX . "review SET 
        author = '" . $this->db->escape($data['name']) . "', 
        product_id = '" . (int)$product_id . "', 
		  customer_id = '" . $customer_id . "', 
        text = '" . $this->db->escape($data['text']) . "', 
        rating = '" . (int)$data['rating'] . "', 
        image1 = $image1, 
        image2 = $image2, 
        image3 = $image3, 
        image4 = $image4, 
        image5 = $image5, 
        status = '1', 
        date_added = NOW()");

		$review_id = $this->db->getLastId();




    // Optional: save images to a separate table or in same row
    foreach ($images as $code) {
        $this->db->query("INSERT INTO " . DB_PREFIX . "review_image SET review_id = '" . (int)$review_id . "', upload_code = '" . $this->db->escape($code) . "'");
    }
		if (in_array('review', (array)$this->config->get('config_mail_alert'))) {
			$this->load->language('mail/review');
			$this->load->model('catalog/product');
			
			$product_info = $this->model_catalog_product->getProduct($product_id);

			$subject = sprintf($this->language->get('text_subject'), html_entity_decode($this->config->get('config_name'), ENT_QUOTES, 'UTF-8'));

			$message  = $this->language->get('text_waiting') . "\n";
			$message .= sprintf($this->language->get('text_product'), html_entity_decode($product_info['name'], ENT_QUOTES, 'UTF-8')) . "\n";
			$message .= sprintf($this->language->get('text_reviewer'), html_entity_decode($data['name'], ENT_QUOTES, 'UTF-8')) . "\n";
			$message .= sprintf($this->language->get('text_rating'), $data['rating']) . "\n";
			$message .= $this->language->get('text_review') . "\n";
			$message .= html_entity_decode($data['text'], ENT_QUOTES, 'UTF-8') . "\n\n";

			$mail = new Mail($this->config->get('config_mail_engine'));
			$mail->parameter = $this->config->get('config_mail_parameter');
			$mail->smtp_hostname = $this->config->get('config_mail_smtp_hostname');
			$mail->smtp_username = $this->config->get('config_mail_smtp_username');
			$mail->smtp_password = html_entity_decode($this->config->get('config_mail_smtp_password'), ENT_QUOTES, 'UTF-8');
			$mail->smtp_port = $this->config->get('config_mail_smtp_port');
			$mail->smtp_timeout = $this->config->get('config_mail_smtp_timeout');

			$mail->setTo($this->config->get('config_email'));
			$mail->setFrom($this->config->get('config_email'));
			$mail->setSender(html_entity_decode($this->config->get('config_name'), ENT_QUOTES, 'UTF-8'));
			$mail->setSubject($subject);
			$mail->setText($message);
			$mail->send();

			// Send to additional alert emails
			$emails = explode(',', $this->config->get('config_mail_alert_email'));

			foreach ($emails as $email) {
				if ($email && filter_var($email, FILTER_VALIDATE_EMAIL)) {
					$mail->setTo($email);
					$mail->send();
				}
			}
		}
	}






// Changes By Sheetal

	public function getReviewsByProductId($product_id, $start = 0, $limit = 20) {
		if ($start < 0) {
			$start = 0;
		}

		if ($limit < 1) {
			$limit = 20;
		}

		$query = $this->db->query("SELECT r.review_id, r.author, r.rating, r.text,  r.image1, r.image2, r.image3, r.image4, r.image5, p.product_id, pd.name, p.price, p.image, r.date_added FROM " . DB_PREFIX . "review r LEFT JOIN " . DB_PREFIX . "product p ON (r.product_id = p.product_id) LEFT JOIN " . DB_PREFIX . "product_description pd ON (p.product_id = pd.product_id) WHERE p.product_id = '" . (int)$product_id . "' AND p.date_available <= NOW() AND p.status = '1' AND r.status = '1' AND pd.language_id = '" . (int)$this->config->get('config_language_id') . "' ORDER BY r.date_added DESC LIMIT " . (int)$start . "," . (int)$limit);
		

		return $query->rows;
	}



	// Changes By Sheetal

	public function getTotalReviewsByProductId($product_id) {
		$query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "review r LEFT JOIN " . DB_PREFIX . "product p ON (r.product_id = p.product_id) LEFT JOIN " . DB_PREFIX . "product_description pd ON (p.product_id = pd.product_id) WHERE p.product_id = '" . (int)$product_id . "' AND p.date_available <= NOW() AND p.status = '1' AND r.status = '1' AND pd.language_id = '" . (int)$this->config->get('config_language_id') . "'");

		return $query->row['total'];
	}





	// New Changes Added


    public function getReviewByCustomerAndProduct($customer_id, $product_id) {
        $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "review WHERE customer_id = '" . (int)$customer_id . "' AND product_id = '" . (int)$product_id . "'");
        return $query->row;
    }

    public function updateReview($review_id, $data, $images = []) {
        $image1 = isset($images[0]) && $images[0] !== '' ? "'" . $this->db->escape($images[0]) . "'" : 'NULL';
        $image2 = isset($images[1]) && $images[1] !== '' ? "'" . $this->db->escape($images[1]) . "'" : 'NULL';
        $image3 = isset($images[2]) && $images[2] !== '' ? "'" . $this->db->escape($images[2]) . "'" : 'NULL';
        $image4 = isset($images[3]) && $images[3] !== '' ? "'" . $this->db->escape($images[3]) . "'" : 'NULL';
        $image5 = isset($images[4]) && $images[4] !== '' ? "'" . $this->db->escape($images[4]) . "'" : 'NULL';

        $this->db->query("UPDATE " . DB_PREFIX . "review SET 
            author = '" . $this->db->escape($data['name']) . "', 
            text = '" . $this->db->escape($data['text']) . "', 
            rating = '" . (int)$data['rating'] . "', 
            image1 = $image1, 
            image2 = $image2, 
            image3 = $image3, 
            image4 = $image4, 
            image5 = $image5, 
            date_added = NOW() 
            WHERE review_id = '" . (int)$review_id . "'");

        // Optional: update images in separate table
        $this->db->query("DELETE FROM " . DB_PREFIX . "review_image WHERE review_id = '" . (int)$review_id . "'");
        foreach ($images as $code) {
            $this->db->query("INSERT INTO " . DB_PREFIX . "review_image SET review_id = '" . (int)$review_id . "', upload_code = '" . $this->db->escape($code) . "'");
        }
    }



	// --------------------------------------------------------------
	
}