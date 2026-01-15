<?php
class ControllerVendorClaimClaimList extends Controller
{
  public function index()
  {
    if (!$this->vendor->isLogged()) {
      $this->response->redirect($this->url->link('vendor/login', '', true));
    }

    $vendor_id = $this->vendor->getId();


    // $this->load->language('vendor/claim/vendor_claim_list');
    $this->document->setTitle($this->language->get('heading_title'));

    $data = array();
    $this->load->model('vendor/claim/returnClaim');
    // $this->load->model('vendor/claim/vendor_claim');
    // $data['newclaimss'] = $this->load->controller('vendor/claim/vendor_claims');
    // $data['newClaims'] = $this->load->controller('vendor/claim/vendor_claims');

    // Collect all the data in one place

    $data['new_claims'] = $this->url->link('vendor/claim/return_list');

    // Pass $data by reference to both functions
    $this->getClaims($data);
    $this->getList($data);
    $this->getReturnOrders($data, $vendor_id);

    // Output the final view
    $this->response->setOutput($this->load->view('vendor/claim/claim_list', $data));
  }

  // Pass $data by reference to modify it in the function
  public function getList(&$data)
  {
    // Load the header, column_left, and footer into $data
    $data['header'] = $this->load->controller('vendor/header');
    $data['column_left'] = $this->load->controller('vendor/column_left');
    $data['footer'] = $this->load->controller('vendor/footer');
  }

  // Pass $data by reference to modify it in the function
  public function getClaims(&$data)
  {
    $vendor_id = $this->vendor->getId();

    // Fetch claims from the model
    // $result = $this->model_vendor_claim_returnClaim->getSellerClaimlist($vendor_id);

    // Loop through the results and populate $data['claims']

    // foreach ($result as $results) {
        
    //   $product_name_words = implode(' ', array_slice(explode(' ', $results['name']), 0, 6));

    //   $data['claims'][] = array(
    //     'claim_id' => $results['claim_id'],
    //     'order_id' => $results['order_id'],
    //     'order_item_id' => $results['product_id'],
    //     'product_name' => $product_name_words,
    //     'applied_date' =>$results['date_added'],
    //     'status_name' => $results['status_name'],
    //     'status' => $results['claim_status_id'],
    //     'modified_date' => date($this->language->get('date_format_short'), strtotime($results['date_modified'])),
    //     'view_url' => '#', // Set an actual URL if required
    //   );
    // }
    
        // Get status filter from URL
        $filter_status = isset($this->request->get['status']) ? $this->request->get['status'] : '';

        // Load filtered claims
        $data['claims'] = [];
        $results = $this->model_vendor_claim_returnClaim->getAllClaims($vendor_id, $filter_status);
// var_dump($results,'------------------------------------------------------------------------');
    //   var_dump($result['percentage']);
        foreach ($results as $result) {
            $product_name_words = implode(' ', array_slice(explode(' ', $result['name']), 0, 4));
            if($result['percentage']){
                
        //   $approve_amount = $result['claim_amount'] * (1 - ($result['percentage'] / 100));
            $approve_amount = $result['claim_amount'] * ($result['percentage'] / 100);

            }
            else{
                $approve_amount=0;
            }

            $data['claims'][] = array(
                'claim_id'        => $result['claim_id'],
                'order_id'        => $result['order_id'],
                'order_item_id'   => $result['product_id'],
                'amount'   => $result['claim_amount'],
                'product_name'    => $product_name_words,
                'status_name' => $result['status_name'] ,
                'status' => $result['claim_status_id'],
                // 'status'          => $result['status_name'] ?? 'In Processing',
               'applied_date' =>$result['date_added'],
                'modified_date'   => date($this->language->get('date_format_short'), strtotime($result['date_modified'])),
                'percentage'      =>  round($result['percentage'] ),
                'approve_amount'      =>  round($approve_amount), 
                'view_url'        => '#',
            );
        }

        // URL for filter buttons
         $data['filter_url'] = $this->url->link('vendor/claim/claim_list', '', true);
         $data['claim_counts'] = $this->model_vendor_claim_returnClaim->getClaimCounts($vendor_id);

// var_dump($data['claims']);
  }
//   update claim start 
   public function fetch_claim_product()
    {
        $this->load->model('vendor/claim/returnClaim');
        $this->load->model('tool/image');

        if (!isset($this->request->post['claim_id'])) {
            $this->response->addHeader('Content-Type: application/json');
            $this->response->setOutput(json_encode(['error' => 'Invalid parameters']));
            return;
        }

        $claim_id = (int)$this->request->post['claim_id'];
        $product_data = $this->model_vendor_claim_returnClaim->getClaimProduct($claim_id);

        if (!$product_data) {
            $this->response->addHeader('Content-Type: application/json');
            $this->response->setOutput(json_encode(['error' => 'No product data found for this claim']));
            return;
        }

        // Prepare image URLs
        if (!empty($product_data['product_image']) && file_exists(DIR_IMAGE . $product_data['product_image'])) {
            $image = $this->model_tool_image->resize($product_data['product_image'], 100, 100);
        } else {
            $image = $this->model_tool_image->resize('no_image.png', 100, 100);
        }

        $label_data = $this->model_vendor_claim_returnClaim->getClaimLabelImage($claim_id);
        $image_urls = ['image1_url' => ''];
        if (!empty($label_data['image'])) {
            $basename = basename($label_data['image']);
            $final_path = 'claims/' . $basename;
            $image_urls['image1_url'] = file_exists(DIR_IMAGE . $final_path) ? 'image/' . $final_path : '';
        }

        $claim_images = $this->model_vendor_claim_returnClaim->getClaimImages($claim_id);
        for ($i = 0; $i < 4; $i++) {
            $key = 'image' . ($i + 2) . '_url';
            if (isset($claim_images[$i])) {
                $basename = basename($claim_images[$i]);
                $final_path = 'claims/' . $basename;
                $image_urls[$key] = file_exists(DIR_IMAGE . $final_path) ? 'image/' . $final_path : '';
            } else {
                $image_urls[$key] = '';
            }
        }

        $issue_areas = $this->model_vendor_claim_returnClaim->getAllIssueAreas();
        $issue_types = $this->model_vendor_claim_returnClaim->getAllIssueTypes();

        $response = [
            'product' => [
                'image' => $image,
                'name' => $product_data['product'],
                'model' => $product_data['model'],
                'total' => $product_data['product_total'],
                'order_id' => $product_data['order_id'],
                'return_id' => $product_data['return_id'],
                'product_id' => $product_data['product_id'],
                'return_date' => $product_data['return_date'],
                'description' => $product_data['description'] ?? '',

            ],
            'issue_areas' => $issue_areas,
            'issue_types' => $issue_types,
            'selected_issue_area' => $product_data['claim_issue_area_id'] ?? '',
            'selected_issue_type' => $product_data['claim_issue_type_id'] ?? '',
            'image_urls' => $image_urls,
        ];

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($response));
    }
    
 public function updateClaim()
    {
        $json = [];

        $this->load->model('vendor/claim/returnClaim');

        if ($this->request->server['REQUEST_METHOD'] == 'POST') {
            if (empty($this->request->post['claim_id'])) {
                $json['error'] = 'Error: Claim ID is required.';
                $this->response->addHeader('Content-Type: application/json');
                $this->response->setOutput(json_encode($json));
                return;
            }

            $claim_id = (int)$this->request->post['claim_id'];

            // Initialize data array
            $data = [
                'claim_id'              => $claim_id,
                'claim_issue_type_id' => (int)($this->request->post['claim_issue_type_id'] ?? 0),
                'claim_issue_area_id' => (int)($this->request->post['claim_issue_area_id'] ?? 0),
                'description'         => trim($this->request->post['description'] ?? ''),
                'shipping_label'      => '', // Will be updated below
                'images'              => []  // Will be populated below
            ];

            // Define the base upload directory.
            $upload_dir = DIR_IMAGE . 'claims/';

            // Ensure the upload directory exists.
            if (!is_dir($upload_dir) && !mkdir($upload_dir, 0755, true) && !is_dir($upload_dir)) {
                $json['error'] = 'Error: Failed to create upload directory.';
                $this->response->addHeader('Content-Type: application/json');
                $this->response->setOutput(json_encode($json));
                return;
            }

            // Function to handle image uploads to avoid repetition.
            $uploadImage = function($file_field, $prefix) use ($upload_dir) {
                if (isset($_FILES[$file_field]) && $_FILES[$file_field]['error'] == UPLOAD_ERR_OK) {
                    $ext = pathinfo($_FILES[$file_field]['name'], PATHINFO_EXTENSION);
                    $filename = $prefix . '_' . md5(uniqid(rand(), true)) . '.' . $ext;
                    $target_file = $upload_dir . $filename;

                    if (move_uploaded_file($_FILES[$file_field]['tmp_name'], $target_file)) {
                        return 'claims/' . $filename; // Return path relative to 'image/' folder
                    } else {
                        error_log("Failed to move uploaded file: " . $_FILES[$file_field]['tmp_name'] . " to " . $target_file);
                    }
                }
                return null; // Return null if no file uploaded or an error occurred
            };

            // --- Logic for Shipping Label (firstImage) ---
            $new_shipping_label_path = $uploadImage('firstImage', 'label');
            if ($new_shipping_label_path) {
                // A new label was uploaded, use it.
                $data['shipping_label'] = $new_shipping_label_path;
            } elseif (isset($this->request->post['existing_shipping_label']) && !empty($this->request->post['existing_shipping_label'])) {
                // No new label uploaded, but an existing one was passed via hidden field.
                $data['shipping_label'] = $this->request->post['existing_shipping_label'];
            } else {
                // No new label, and no existing label was carried over (e.g., deleted or never existed)
                $data['shipping_label'] = '';
            }

            // --- Logic for Other Claim Images (secondImage to fifthImage) ---
            $image_fields = ['secondImage', 'thirdImage', 'fourthImage', 'fifthImage'];
            $final_claim_images_for_model = []; // This will be the array sent to the model

            // Populate current existing images from POST data (hidden fields)
            // This assumes your hidden fields are named 'existing_images[]' in the form
            $existing_images_from_post = $this->request->post['existing_images'] ?? [];

            for ($i = 0; $i < count($image_fields); $i++) {
                $field_name = $image_fields[$i];
                $new_image_path = $uploadImage($field_name, 'claim');

                if ($new_image_path) {
                    // A new image was uploaded for this slot
                    $final_claim_images_for_model[] = $new_image_path;
                } elseif (isset($existing_images_from_post[$i]) && !empty($existing_images_from_post[$i])) {
                    // No new image, but an existing image path was passed for this slot
                    $final_claim_images_for_model[] = $existing_images_from_post[$i];
                } else {
                    // This slot is explicitly empty (no new upload, no existing path)
                    $final_claim_images_for_model[] = '';
                }
            }
            $data['images'] = $final_claim_images_for_model; // Assign the prepared array to $data['images']

            // --- End of Image Logic ---

            try {
                $this->model_vendor_claim_returnClaim->updateClaim($data);
                $json['success'] = 'Claim updated successfully.';
            } catch (Exception $e) {
                error_log('Error updating claim: ' . $e->getMessage() . ' - ' . $e->getFile() . ' on line ' . $e->getLine());
                $json['error'] = 'Error: There was a problem updating the claim. Please try again later.';
            }
        } else {
            $json['error'] = 'Error: Invalid request method.';
        }

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }
    // upadte claim end 
  public function getClaimsIssue($data)
  {
    // claim issue details 
    $claim_issue_details = $this->model_vendor_claim_returnClaim->getClaimIssueDetails();

    // var_dump($claim_issue_details);
    $data['issue_details'] = $claim_issue_details;
  }
  public function getReturnOrders($data, $vendor_id)
  {
    $result = $this->model_vendor_claim_returnClaim->getReturnOrders($vendor_id);
  }
}
