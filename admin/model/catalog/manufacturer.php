<?php
class ModelCatalogManufacturer extends Model {
    // public function addManufacturer($data) {
    // 		$this->db->query("INSERT INTO " . DB_PREFIX . "manufacturer SET name = '" . $this->db->escape($data['name']) . "', sort_order = '" . (int)$data['sort_order'] . "'");
    
    // 		$manufacturer_id = $this->db->getLastId();
    
    // 		if (isset($data['image'])) {
    // 			$this->db->query("UPDATE " . DB_PREFIX . "manufacturer SET image = '" . $this->db->escape($data['image']) . "' WHERE manufacturer_id = '" . (int)$manufacturer_id . "'");
    // 		}
    
    // 		if (isset($data['manufacturer_store'])) {
    // 			foreach ($data['manufacturer_store'] as $store_id) {
    // 				$this->db->query("INSERT INTO " . DB_PREFIX . "manufacturer_to_store SET manufacturer_id = '" . (int)$manufacturer_id . "', store_id = '" . (int)$store_id . "'");
    // 			}
    // 		}
    				
    // 		// SEO URL
    // 		if (isset($data['manufacturer_seo_url'])) {
    // 			foreach ($data['manufacturer_seo_url'] as $store_id => $language) {
    // 				foreach ($language as $language_id => $keyword) {
    // 					if (!empty($keyword)) {
    // 						$this->db->query("INSERT INTO " . DB_PREFIX . "seo_url SET store_id = '" . (int)$store_id . "', language_id = '" . (int)$language_id . "', query = 'manufacturer_id=" . (int)$manufacturer_id . "', keyword = '" . $this->db->escape($keyword) . "'");
    // 					}
    // 				}
    // 			}
    // 		}
    		
    // 		$this->cache->delete('manufacturer');
    
    // 		return $manufacturer_id;
    // 	}

    //Adding new one code on 13/07/2025 --------------------------
    public function addManufacturer($data) {
        $name = $this->db->escape($data['name']);

        // Check duplicate by name
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

        // ✅ Create entry in vendor_to_manufacturer
        if (!empty($data['vendor_id'])) {
            $vendor_id = (int)$data['vendor_id'];
            $declaration = isset($data['declaration_form']) ? $this->db->escape($data['declaration_form']) : '';

            $check = $this->db->query("SELECT * FROM " . DB_PREFIX . "vendor_to_manufacturer 
                WHERE manufacturer_id = '" . (int)$manufacturer_id . "' 
                AND vendor_id = '" . $vendor_id . "'");

            if ($check->num_rows == 0) {
                $this->db->query("INSERT INTO " . DB_PREFIX . "vendor_to_manufacturer SET 
                    manufacturer_id = '" . (int)$manufacturer_id . "', 
                    vendor_id = '" . $vendor_id . "', 
                    status = '" . (int)$data['status'] . "' , 
                    declaration = '" . $declaration . "', 
                    date_added = NOW(), 
                    date_modified = NOW()");
            }
        }

        $this->cache->delete('manufacturer');
        return $manufacturer_id;
    }
    //-----------end here------------------------------------------

    //public function editManufacturer($manufacturer_id, $data) {
    // 	   // updated following changes for the status edit option 26-04-2025---------
    // 		$this->db->query("UPDATE " . DB_PREFIX . "manufacturer SET name = '" . $this->db->escape($data['name']) . "', sort_order = '" . (int)$data['sort_order'] . "' , status = '" . (int)$data['status'] . "' WHERE manufacturer_id = '" . (int)$manufacturer_id . "'");
    
    // 		if (isset($data['image'])) {
    // 			$this->db->query("UPDATE " . DB_PREFIX . "manufacturer SET image = '" . $this->db->escape($data['image']) . "' WHERE manufacturer_id = '" . (int)$manufacturer_id . "'");
    // 		}
    
    // 		$this->db->query("DELETE FROM " . DB_PREFIX . "manufacturer_to_store WHERE manufacturer_id = '" . (int)$manufacturer_id . "'");
    
    // 		if (isset($data['manufacturer_store'])) {
    // 			foreach ($data['manufacturer_store'] as $store_id) {
    // 				$this->db->query("INSERT INTO " . DB_PREFIX . "manufacturer_to_store SET manufacturer_id = '" . (int)$manufacturer_id . "', store_id = '" . (int)$store_id . "'");
    // 			}
    // 		}
    
    // 		$this->db->query("DELETE FROM `" . DB_PREFIX . "seo_url` WHERE query = 'manufacturer_id=" . (int)$manufacturer_id . "'");
    
    // 		if (isset($data['manufacturer_seo_url'])) {
    // 			foreach ($data['manufacturer_seo_url'] as $store_id => $language) {
    // 				foreach ($language as $language_id => $keyword) {
    // 					if (!empty($keyword)) {
    // 						$this->db->query("INSERT INTO `" . DB_PREFIX . "seo_url` SET store_id = '" . (int)$store_id . "', language_id = '" . (int)$language_id . "', query = 'manufacturer_id=" . (int)$manufacturer_id . "', keyword = '" . $this->db->escape($keyword) . "'");
    // 					}
    // 				}
    // 			}
    // 		}
    
    // 		$this->cache->delete('manufacturer');
    // 	}
    
    //Adding new code on 13/07/2025 -------------------------
    public function editManufacturer($manufacturer_id, $data) {
        // Update manufacturer base fields
        $this->db->query("UPDATE " . DB_PREFIX . "manufacturer SET 
            name = '" . $this->db->escape($data['name']) . "', 
            sort_order = '" . (int)$data['sort_order'] . "', 
            status = '" . (int)$data['status'] . "' 
            WHERE manufacturer_id = '" . (int)$manufacturer_id . "'");

        // Optional image
        if (isset($data['image'])) {
            $this->db->query("UPDATE " . DB_PREFIX . "manufacturer SET 
                image = '" . $this->db->escape($data['image']) . "' 
                WHERE manufacturer_id = '" . (int)$manufacturer_id . "'");
        }

        // Store mapping
        $this->db->query("DELETE FROM " . DB_PREFIX . "manufacturer_to_store WHERE manufacturer_id = '" . (int)$manufacturer_id . "'");

        if (isset($data['manufacturer_store'])) {
            foreach ($data['manufacturer_store'] as $store_id) {
                $this->db->query("INSERT INTO " . DB_PREFIX . "manufacturer_to_store SET 
                    manufacturer_id = '" . (int)$manufacturer_id . "', 
                    store_id = '" . (int)$store_id . "'");
            }
        }

        // Handle declaration form and vendor-to-manufacturer relation
        if (!empty($data['vendor_id'])) {
            $vendor_id = (int)$data['vendor_id'];
            $declaration_form = '';

            if (!empty($data['declaration_form'])) {
                $declaration_form = $data['declaration_form'];
            } else {
                // Fallback to previous declaration (if exists)
                $query = $this->db->query("SELECT declaration FROM " . DB_PREFIX . "vendor_to_manufacturer 
                    WHERE manufacturer_id = '" . (int)$manufacturer_id . "' 
                    AND vendor_id = '" . $vendor_id . "'");

                if ($query->num_rows && !empty($query->row['declaration'])) {
                    $declaration_form = $query->row['declaration'];
                }
            }

            // Remove old record and insert new one

           

            // $this->db->query("DELETE FROM " . DB_PREFIX . "vendor_to_manufacturer 
            //     WHERE manufacturer_id = '" . (int)$manufacturer_id . "' 
            //     AND vendor_id = '" . $vendor_id . "'");

            // $this->db->query("INSERT INTO " . DB_PREFIX . "vendor_to_manufacturer SET 
            // manufacturer_id = '" . (int)$manufacturer_id . "', 
            // vendor_id = '" . $vendor_id . "', 
            // status = '" . (int)$data['status'] . "' , 
            // declaration = '" . $this->db->escape($declaration_form) . "', 
            // date_added = NOW(), 
            // date_modified = NOW()");

           
                
               $this->db->query("UPDATE " . DB_PREFIX . "vendor_to_manufacturer SET 
                manufacturer_id = '" . (int)$manufacturer_id . "', 
                vendor_id = '" . (int)$vendor_id . "', 
                status = '" . (int)$data['status'] . "', 
                declaration = '" . $this->db->escape($declaration_form) . "', 
                date_modified = NOW() 
                WHERE manufacturer_id = '" . (int)$manufacturer_id . "' 
                AND vendor_id = '" . (int)$vendor_id. "'");

        }

        // SEO URL update
        $this->db->query("DELETE FROM `" . DB_PREFIX . "seo_url` WHERE query = 'manufacturer_id=" . (int)$manufacturer_id . "'");

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
    //------- end here---------------------------------------

	public function deleteManufacturer($manufacturer_id) {
		$this->db->query("DELETE FROM `" . DB_PREFIX . "manufacturer` WHERE manufacturer_id = '" . (int)$manufacturer_id . "'");
		$this->db->query("DELETE FROM `" . DB_PREFIX . "manufacturer_to_store` WHERE manufacturer_id = '" . (int)$manufacturer_id . "'");
		$this->db->query("DELETE FROM `" . DB_PREFIX . "seo_url` WHERE query = 'manufacturer_id=" . (int)$manufacturer_id . "'");

		$this->cache->delete('manufacturer');
	}

    // Updated the query on the 13/07/2025
	public function getManufacturer($manufacturer_id,$vendor_id =0) {
        // 		$query = $this->db->query("SELECT DISTINCT * FROM " . DB_PREFIX . "manufacturer WHERE manufacturer_id = '" . (int)$manufacturer_id . "'");
        $query = $this->db->query("
            SELECT  m.*, 
                vtm.status, 
                vtm.declaration, 
                vtm.date_added, 
                vtm.date_modified,
                CONCAT(v.firstname, ' ', v.lastname) AS seller_name
            FROM " . DB_PREFIX . "manufacturer m
            JOIN " . DB_PREFIX . "vendor_to_manufacturer vtm ON vtm.manufacturer_id = m.manufacturer_id
            JOIN " . DB_PREFIX . "vendor v ON v.vendor_id = vtm.vendor_id
            WHERE vtm.manufacturer_id = '" . (int)$manufacturer_id . "'
            AND vtm.vendor_id = '" . (int)$vendor_id . "'
        ");
        
		return $query->row;
	}



	public function getManufacturerStores($manufacturer_id) {
		$manufacturer_store_data = array();

		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "manufacturer_to_store WHERE manufacturer_id = '" . (int)$manufacturer_id . "'");

		foreach ($query->rows as $result) {
			$manufacturer_store_data[] = $result['store_id'];
		}

		return $manufacturer_store_data;
	}
	
	public function getManufacturerSeoUrls($manufacturer_id) {
		$manufacturer_seo_url_data = array();
		
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "seo_url WHERE query = 'manufacturer_id=" . (int)$manufacturer_id . "'");

		foreach ($query->rows as $result) {
			$manufacturer_seo_url_data[$result['store_id']][$result['language_id']] = $result['keyword'];
		}

		return $manufacturer_seo_url_data;
	}
	
	public function getTotalManufacturers() {
		$query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "manufacturer");

		return $query->row['total'];
	}
	
	public function DisStatus($manufacturer_id) {
		$this->db->query("UPDATE " . DB_PREFIX . "manufacturer SET status = '0' WHERE manufacturer_id = '" . (int)$manufacturer_id . "'");
	}
	// -----------------------------------------------------------------------------------------------------
	
	// added changes for manufacturer 25-04-2025 -----------------------------

	public function getLatestAdminCommentForManufacturer($manufacturer_id) {
		$query = $this->db->query("SELECT comment FROM " . DB_PREFIX . "manufacturer_approval_comments WHERE manufacturer_id = '" . (int)$manufacturer_id . "' AND comment_by = 'admin' ORDER BY date_added DESC LIMIT 1");
		return ($query->num_rows > 0) ? $query->row['comment'] : '';
	}
	
	public function getManufacturerCommentThread($manufacturer_id) {
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "manufacturer_approval_comments WHERE manufacturer_id = '" . (int)$manufacturer_id . "' ORDER BY date_added ASC");
		return $query->rows;
	}
	
	public function saveManufacturerAdminComment($manufacturer_id, $comment) {
		$this->db->query("INSERT INTO " . DB_PREFIX . "manufacturer_approval_comments SET manufacturer_id = '" . (int)$manufacturer_id . "', comment_by = 'admin', comment = '" . $this->db->escape($comment) . "', date_added = NOW()");
	}
	
	public function getAllManufacturerComments($manufacturer_id) {
		$query = $this->db->query("SELECT comment_by, comment, date_added, media FROM " . DB_PREFIX . "manufacturer_approval_comments WHERE manufacturer_id = '" . (int)$manufacturer_id . "' ORDER BY date_added ASC");
	
		$comments = $query->rows;
		foreach ($comments as &$comment) {
			$comment['media'] = !empty($comment['media']) ? explode(',', $comment['media']) : [];
		}
	
		return $comments;
	}
	
	public function submitManufacturerAdminComment($manufacturer_id, $comment) {
		$this->db->query("INSERT INTO " . DB_PREFIX . "manufacturer_approval_comments SET manufacturer_id = '" . (int)$manufacturer_id . "', comment_by = 'admin', comment = '" . $this->db->escape($comment) . "', date_added = NOW()");
	}
	
	public function submitVendorReply($manufacturer_id, $comment, $media = '', $vendor_id = 0) {
		$this->db->query("INSERT INTO " . DB_PREFIX . "manufacturer_approval_comments SET
			manufacturer_id = '" . (int)$manufacturer_id . "',
			comment_by = 'admin',
			vendor_id = '" . (int)$vendor_id . "',
			comment = '" . $this->db->escape($comment) . "',
			media = '" . $this->db->escape($media) . "',
			date_added = NOW()");
	}
    // --------------------------------------------------------------------------------------------------------------------	
    
    // public function getManufacturersByVendor($vendor) {
    //     if (empty($vendor)) {
    //         return [];
    //     }
    
    //     $query = $this->db->query("SELECT m.* FROM " . DB_PREFIX . "manufacturer m 
    //         INNER JOIN " . DB_PREFIX . "vendor_to_manufacturer v2m ON m.manufacturer_id = v2m.manufacturer_id
    //         WHERE v2m.vendor_id = '" . (int)$vendor . "'
    //         ORDER BY m.name ASC");
    
    //     return $query->rows;
    // }
    
    // new one added on 15/07/2025 for autocomplete 
    public function getManufacturersByVendor($data = []) {
        $sql = "SELECT m.manufacturer_id, m.name 
                FROM " . DB_PREFIX . "manufacturer m
                LEFT JOIN " . DB_PREFIX . "vendor_to_manufacturer v2m 
                ON m.manufacturer_id = v2m.manufacturer_id 
                WHERE v2m.vendor_id = '" . (int)$data['vendor_id'] . "'";
    
        if (!empty($data['filter_name'])) {
            $sql .= " AND m.name LIKE '%" . $this->db->escape($data['filter_name']) . "%'";
        }
    
        $sql .= " ORDER BY m.name ASC";
    
        if (isset($data['start']) && isset($data['limit'])) {
            $sql .= " LIMIT " . (int)$data['start'] . "," . (int)$data['limit'];
        }
    
        $query = $this->db->query($sql);
        return $query->rows;
    }
    //-------- end here-----------------------------------------------------------

    public function getManufacturers($data = array()) {
    $sql = "SELECT m.*, vtm.status, vtm.declaration, vtm.vendor_id, vtm.date_added, vtm.date_modified, 
                   vd.name AS vendorstorename 
            FROM " . DB_PREFIX . "vendor_to_manufacturer vtm 
            LEFT JOIN " . DB_PREFIX . "manufacturer m ON (vtm.manufacturer_id = m.manufacturer_id) 
            LEFT JOIN " . DB_PREFIX . "vendor_description vd ON (vtm.vendor_id = vd.vendor_id) 
            WHERE vd.language_id = '" . (int)$this->config->get('config_language_id') . "' 
              AND vtm.vendor_id <> 0";

    // ðŸ”Ž Filter by vendor_id
    if (!empty($data['filter_vendor1'])) {
        $sql .= " AND vtm.vendor_id LIKE '" . $this->db->escape($data['filter_vendor1']) . "'";
    }

    // ðŸ”Ž Filter by manufacturer name
    if (!empty($data['filter_name'])) {
        $sql .= " AND m.name LIKE '" . $this->db->escape($data['filter_name']) . "%'";
    }

    // âœ… Filter by vendor-specific status
    if (isset($data['filter_status']) && $data['filter_status'] !== '') {
        $sql .= " AND vtm.status = '" . (int)$data['filter_status'] . "'";
    }

    // ðŸ”Ž Filter by approval status from oc_manufacturer (if still applicable)
    if (isset($data['filter_approval_status']) && $data['filter_approval_status'] !== '') {
        $sql .= " AND m.approval_status = '" . $this->db->escape($data['filter_approval_status']) . "'";
    }

    // ðŸ”Ž Filter by store/vendor name
    if (!empty($data['filter_store_name'])) {
        $sql .= " AND vd.name LIKE '" . $this->db->escape($data['filter_store_name']) . "%'";
    }

    $sql .= " GROUP BY m.manufacturer_id, vtm.vendor_id";

    // ðŸ“Š Sorting support
    $sort_data = array(
        'm.name',
        'm.sort_order',
        'vtm.status',
        'm.approval_status',
        'vd.name',
        'vtm.vendor_id'
    );

    if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
        $sql .= " ORDER BY " . $data['sort'];
    } else {
        $sql .= " ORDER BY m.name";
    }

    if (isset($data['order']) && $data['order'] == 'DESC') {
        $sql .= " DESC";
    } else {
        $sql .= " ASC";
    }

    // ðŸ”¢ Pagination
    if (isset($data['start']) || isset($data['limit'])) {
        if ($data['start'] < 0) $data['start'] = 0;
        if ($data['limit'] < 1) $data['limit'] = 20;
        $sql .= " LIMIT " . (int)$data['start'] . "," . (int)$data['limit'];
    }

    $query = $this->db->query($sql);

    return $query->rows;
}

    public function status($manufacturer_id, $vendor_id) {
    	$query = $this->db->query("SELECT status FROM " . DB_PREFIX . "vendor_to_manufacturer 
    		WHERE manufacturer_id = '" . (int)$manufacturer_id . "' 
    		AND vendor_id = '" . (int)$vendor_id . "'");
    
    	if ($query->num_rows) {
    		$current = (int)$query->row['status'];
    		$new_status = ($current == 1) ? 0 : 1;
    
    		$this->db->query("UPDATE " . DB_PREFIX . "vendor_to_manufacturer 
    			SET status = '" . $new_status . "' 
    			WHERE manufacturer_id = '" . (int)$manufacturer_id . "' 
    			AND vendor_id = '" . (int)$vendor_id . "'");
    	}
    }
    
    public function getVendorsByManufacturer($manufacturer_id) {
    	$query = $this->db->query("SELECT 
    		vtm.vendor_id,
    		v.display_name,
    		vtm.declaration,
    		vtm.status,
    		vtm.date_added,
    		vtm.date_modified,
    		m.name
    	FROM " . DB_PREFIX . "vendor_to_manufacturer vtm
    	LEFT JOIN " . DB_PREFIX . "vendor v ON vtm.vendor_id = v.vendor_id
    	LEFT JOIN " . DB_PREFIX . "manufacturer v ON vtm.manufacturer_id = m.manufacturer_id
    	WHERE vtm.manufacturer_id = '" . (int)$manufacturer_id . "'");
    
    	return $query->rows;
    }
    
    //Added code to get the vendor name on 13/07/2025
    public function getVendorsByName($filter_name) {
        $sql = "SELECT vendor_id, CONCAT(firstname, ' ', lastname) AS name 
                FROM " . DB_PREFIX . "vendor 
                WHERE CONCAT(firstname, ' ', lastname) LIKE '%" . $this->db->escape($filter_name) . "%' 
                ORDER BY name ASC 
                LIMIT 20";
    
        $query = $this->db->query($sql);
        return $query->rows;
    }
    
    public function updateVendorDeclaration($manufacturer_id, $vendor_id, $finalRelativePath) {
        // Escape the finalRelativePath to prevent SQL injection
        $escapedPath = $this->db->escape($finalRelativePath);

        // Prepare the SQL query to update the declaration
        $sql = "UPDATE " . DB_PREFIX . "vendor_to_manufacturer SET declaration = '" . $escapedPath . "' WHERE manufacturer_id = '" . (int)$manufacturer_id . "' AND vendor_id = '" . (int)$vendor_id . "'";

        // Execute the query
        $this->db->query($sql);
    }
    
    // -------========- end here ------------------
}
