
<?php
class ModelVendorManufacturer extends Model
{

	public function addManufacturer($data) {
    $name = $this->db->escape($data['name']);

    // Normalize to lowercase for safer duplicate check
    $query = $this->db->query("SELECT manufacturer_id FROM " . DB_PREFIX . "manufacturer WHERE LOWER(name) = '" . strtolower($name) . "'");

    if ($query->num_rows > 0) {
        $manufacturer_id = (int)$query->row['manufacturer_id'];
    } else {
        $this->db->query("INSERT INTO " . DB_PREFIX . "manufacturer SET 
            name = '" . $name . "',
            sort_order = '" . (int)$data['sort_order'] . "'");

        $manufacturer_id = $this->db->getLastId();

        if (isset($data['image'])) {
            $this->db->query("UPDATE " . DB_PREFIX . "manufacturer SET 
                image = '" . $this->db->escape($data['image']) . "' 
                WHERE manufacturer_id = '" . (int)$manufacturer_id . "'");
        }

        if (isset($data['manufacturer_store'])) {
            foreach ($data['manufacturer_store'] as $store_id) {
                $this->db->query("INSERT INTO " . DB_PREFIX . "manufacturer_to_store SET 
                    manufacturer_id = '" . (int)$manufacturer_id . "', 
                    store_id = '" . (int)$store_id . "'");
            }
        }

        // SEO URLs
        if (isset($data['manufacturer_seo_url'])) {
            foreach ($data['manufacturer_seo_url'] as $store_id => $language) {
                foreach ($language as $language_id => $keyword) {
                    if (!empty($keyword)) {
                        $this->db->query("INSERT INTO " . DB_PREFIX . "seo_url SET 
                            store_id = '" . (int)$store_id . "',
                            language_id = '" . (int)$language_id . "',
                            query = 'manufacturer_id=" . (int)$manufacturer_id . "',
                            keyword = '" . $this->db->escape($keyword) . "'");
                    }
                }
            }
        }
    }

    // Vendor link (with declaration and status)
    $vendor_id = (int)$this->vendor->getId();
    $check = $this->db->query("SELECT * FROM " . DB_PREFIX . "vendor_to_manufacturer 
        WHERE manufacturer_id = '" . (int)$manufacturer_id . "' 
        AND vendor_id = '" . $vendor_id . "'");

    if ($check->num_rows == 0) {
        $this->db->query("INSERT INTO " . DB_PREFIX . "vendor_to_manufacturer SET 
            manufacturer_id = '" . (int)$manufacturer_id . "',
            vendor_id = '" . $vendor_id . "',
            status = 2,
            declaration = '" . $this->db->escape($data['declaration_form']) . "',
            date_added = NOW(),
            date_modified = NOW()");
    }

    $this->cache->delete('manufacturer');
    return $manufacturer_id;
}


	public function editManufacturer($manufacturer_id, $data, $vendor_id = 0)
	{
		// Update manufacturer table
		$this->db->query("UPDATE " . DB_PREFIX . "manufacturer SET 
		name = '" . $this->db->escape($data['name']) . "', 
		sort_order = '" . (int)$data['sort_order'] . "', 
		status = 2 
		WHERE manufacturer_id = '" . (int)$manufacturer_id . "'");

		// Optional image
		if (isset($data['image'])) {
			$this->db->query("UPDATE " . DB_PREFIX . "manufacturer SET 
			image = '" . $this->db->escape($data['image']) . "' 
			WHERE manufacturer_id = '" . (int)$manufacturer_id . "'");
		}

		// Update manufacturer to store
		$this->db->query("DELETE FROM " . DB_PREFIX . "manufacturer_to_store WHERE manufacturer_id = '" . (int)$manufacturer_id . "'");

		if (isset($data['manufacturer_store'])) {
			foreach ($data['manufacturer_store'] as $store_id) {
				$this->db->query("INSERT INTO " . DB_PREFIX . "manufacturer_to_store SET 
				manufacturer_id = '" . (int)$manufacturer_id . "', 
				store_id = '" . (int)$store_id . "'");
			}
		}

		// Handle declaration form
		$declaration_form = '';
		if (!empty($data['declaration_form'])) {
			$declaration_form = $data['declaration_form'];
		} else {
			// Fetch previous declaration form (if it exists)
			$query = $this->db->query("SELECT declaration FROM " . DB_PREFIX . "vendor_to_manufacturer 
			WHERE manufacturer_id = '" . (int)$manufacturer_id . "' 
			AND vendor_id = '" . (int)$vendor_id . "'");

			if ($query->num_rows && !empty($query->row['declaration'])) {
				$declaration_form = $query->row['declaration'];
			}
		}

		// Update vendor_to_manufacturer record
		$this->db->query("DELETE FROM " . DB_PREFIX . "vendor_to_manufacturer 
		WHERE manufacturer_id = '" . (int)$manufacturer_id . "' 
		AND vendor_id = '" . (int)$vendor_id . "'");

		$this->db->query("INSERT INTO " . DB_PREFIX . "vendor_to_manufacturer SET 
		manufacturer_id = '" . (int)$manufacturer_id . "', 
		vendor_id = '" . (int)$vendor_id . "', 
		status = 2,
		declaration = '" . $this->db->escape($declaration_form) . "'");

		// Handle SEO URLs
		$this->db->query("DELETE FROM `" . DB_PREFIX . "seo_url` 
		WHERE query = 'manufacturer_id=" . (int)$manufacturer_id . "'");

		if (isset($data['manufacturer_seo_url'])) {
			foreach ($data['manufacturer_seo_url'] as $store_id => $language) {
				foreach ($language as $language_id => $keyword) {
					if (!empty($keyword)) {
						$this->db->query("INSERT INTO `" . DB_PREFIX . "seo_url` SET 
						store_id = '" . (int)$store_id . "', 
						language_id = '" . (int)$language_id . "', 
						query = 'manufacturer_id=" . (int)$manufacturer_id . "', 
						keyword = '" . $this->db->escape($keyword) . "'");
					}
				}
			}
		}

		// Clear cache
		$this->cache->delete('manufacturer');
	}



	public function deleteManufacturer($manufacturer_id, $vendor_id)
	{
		$this->db->query("DELETE FROM " . DB_PREFIX . "vendor_to_manufacturer WHERE manufacturer_id = '" . (int)$manufacturer_id . "' and vendor_id='" . (int)$vendor_id . "'");

		// $this->db->query("DELETE FROM " . DB_PREFIX . "manufacturer_to_store WHERE manufacturer_id = '" . (int)$manufacturer_id . "'");

		$this->cache->delete('manufacturer');
	}

	public function getManufacturer($manufacturer_id=0, $vendor_id=0)
	{
    	$query = $this->db->query("
            SELECT DISTINCT *
            FROM " . DB_PREFIX . "manufacturer m
            JOIN " . DB_PREFIX . "vendor_to_manufacturer vtm ON vtm.manufacturer_id = m.manufacturer_id
            WHERE vtm.manufacturer_id = '" . (int)$manufacturer_id . "'
            AND vtm.vendor_id = '" . (int)$vendor_id . "'
        ");

		return $query->row;
	}

	public function getManufacturers($data = array())
	{
		$sql = "SELECT * FROM " . DB_PREFIX . "manufacturer m LEFT JOIN " . DB_PREFIX . "vendor_to_manufacturer vm ON (m.manufacturer_id = vm.manufacturer_id) where vm.vendor_id<>0";

		if (isset($data['vendor_id'])) {
			$sql .= " and vm.vendor_id='" . (int)$data['vendor_id'] . "'";
		}

		if (!empty($data['filter_name'])) {
			$sql .= " AND m.name LIKE '" . $this->db->escape($data['filter_name']) . "%'";
		}

		if (isset($data['filter_status']) && $data['filter_status'] !== '') {
			$sql .= " AND vm.status = '" . (int)$data['filter_status'] . "'";
		}

		$sort_data = array(
			'm.name',
			'sort_order'
		);

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sql .= " ORDER BY " . $data['sort'];
		} else {
			$sql .= " ORDER BY m.name";
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

	public function getManufacturerStores($manufacturer_id=0)
	{
		$manufacturer_store_data = array();

		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "manufacturer_to_store WHERE manufacturer_id = '" . (int)$manufacturer_id . "'");

		foreach ($query->rows as $result) {
			$manufacturer_store_data[] = $result['store_id'];
		}

		return $manufacturer_store_data;
	}

	public function getManufacturerSeoUrls($manufacturer_id=0)
	{
		$manufacturer_seo_url_data = array();

		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "seo_url WHERE query = 'manufacturer_id=" . (int)$manufacturer_id . "'");

		foreach ($query->rows as $result) {
			$manufacturer_seo_url_data[$result['store_id']][$result['language_id']] = $result['keyword'];
		}

		return $manufacturer_seo_url_data;
	}

	public function getTotalManufacturers($data)
	{

		$sql = "SELECT COUNT(*) AS total FROM " . DB_PREFIX . "manufacturer m LEFT JOIN " . DB_PREFIX . "vendor_to_manufacturer vm ON (m.manufacturer_id = vm.manufacturer_id) where vm.vendor_id<>0";
		if (isset($data['vendor_id'])) {
			$sql .= " and vm.vendor_id='" . (int)$data['vendor_id'] . "'";
		}

		$query = $this->db->query($sql);
		return $query->row['total'];
	}

	// added changes for the manufacturer 25-04-2025
	public function QuickStatus($status, $manufacturer_id, $vendor_id = 0)
	{
		$this->db->query("UPDATE " . DB_PREFIX . "vendor_to_manufacturer SET status = '" . (int)$status . "' WHERE manufacturer_id = '" . (int)$manufacturer_id . "' and vendor_id= '" . (int)$vendor_id . "'");
	}

	// added changes for the manufacturer comment on 25-04-2025------------
	public function getLatestAdminComment($manufacturer_id, $vendor_id = 0)
	{
		$query = $this->db->query("SELECT comment FROM " . DB_PREFIX . "manufacturer_approval_comments WHERE manufacturer_id = '" . (int)$manufacturer_id . "' AND comment_by = 'admin' and vendor_id='" . (int)$vendor_id . "' ORDER BY date_added DESC LIMIT 1");
		return ($query->num_rows > 0) ? $query->row['comment'] : '';
	}

	public function submitVendorReply($manufacturer_id, $comment, $media_files = [], $vendor_id = 0)
	{
		$this->db->query("INSERT INTO " . DB_PREFIX . "manufacturer_approval_comments SET 
    		manufacturer_id = '" . (int)$manufacturer_id . "', 
    		comment_by = 'vendor', 
    		comment = '" . $this->db->escape($comment) . "', 
    		vendor_id = '" . (int)$vendor_id . "', 
    		date_added = NOW()");

		$comment_id = $this->db->getLastId();

		if (!empty($media_files)) {
			foreach ($media_files as $file) {
				$this->db->query("INSERT INTO " . DB_PREFIX . "manufacturer_comment_media SET comment_id = '" . (int)$comment_id . "', file = '" . $this->db->escape($file) . "'");
			}
		}
	}



	public function getAllManufacturerComments($manufacturer_id, $vendor_id)
	{
		$query = $this->db->query("SELECT comment_by, comment, date_added, media FROM " . DB_PREFIX . "manufacturer_approval_comments WHERE manufacturer_id = '" . (int)$manufacturer_id . "' and vendor_id = '" . $vendor_id . "'  ORDER BY date_added ASC");

		$comments = [];
		foreach ($query->rows as $row) {
			$row['media'] = $row['media'] ? explode(',', $row['media']) : [];
			$comments[] = $row;
		}

		return $comments;
	}
	// --------------------------------------------------------------------------------------------------
	public function getManufacturersByName($data = []) {
    $sql = "SELECT * FROM " . DB_PREFIX . "manufacturer WHERE 1";

    if (!empty($data['filter_name'])) {
        $sql .= " AND name LIKE '" . $this->db->escape($data['filter_name']) . "%'";
    }

    $sql .= " ORDER BY name ASC";

    if (isset($data['start']) || isset($data['limit'])) {
        $start = $data['start'] ?? 0;
        $limit = $data['limit'] ?? 20;
        $sql .= " LIMIT " . (int)$start . "," . (int)$limit;
    }

    $query = $this->db->query($sql);
    return $query->rows;
}
}

