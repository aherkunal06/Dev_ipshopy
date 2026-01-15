<?php
class ControllerVendorClaimReturnList extends Controller
{
  public function index()
  {
    $data = array();

    $this->load->model('vendor/claim/returnClaim');

    // Pass $data by reference
    $this->getList($data);
    $this->getReturnOrders($data);

    // Example usage of the function:
    $claim_issue_details = $this->model_vendor_claim_returnClaim->getClaimIssueDetails();

    $data['issue_details'] = $claim_issue_details;
    $this->response->setOutput($this->load->view('vendor/claim/return_list', $data));
  }

  public function getList(&$data)
  {
    // Load the header, column_left, and footer into $data
    $data['header'] = $this->load->controller('vendor/header');
    $data['column_left'] = $this->load->controller('vendor/column_left');
    $data['footer'] = $this->load->controller('vendor/footer');
  }
  public function getReturnOrders(&$data)
  {
    $vendor_id = $this->vendor->getId();

    // Fetch claims from the model
    $results = $this->model_vendor_claim_returnClaim->getReturnOrders($vendor_id);


    // Loop through the results and populate $data['claims']
    $data['return_orders'] = array();

    $sr = 1;
    foreach ($results as $result) {
      $image = 'no_image.png';
      if (!empty($result['image']) && is_file(DIR_IMAGE . $result['image'])) {
          $this->load->model('tool/image');
        $image = $this->model_tool_image->resize($result['image'], 40, 40);
      }
      $product_name_words = implode(' ', array_slice(explode(' ', $result['name']), 0, 6));

      $data['return_orders'][] = array(
        'sr' => $sr++,
        'order_id' => $result['order_id'],
        // 'image' => $image,
        'Product' => $product_name_words,
        'return_id' => $result['return_id'],
        'product_id' => $result['product_id'],
        // 'return_ids' = $result['return_id'],
        'customer_id' => $result['customer_id'],
        'return_date' => date($this->language->get('date_format_short'), strtotime($result['date_modified'])),
        // 'status' => $result['status_name'],
        'view' => $this->url->link('vendor/latestorder/letestview', 'order_id=' . $result['order_id'], true),
        // 'viewImages' => $this->url->link('vendor/return_images', 'return_ids=' . $result['return_id'], true),
      );
    }
    // var_dump($data['return_orders']);
    // var_dump($data);
  }




  public function fetch_return_product()
  {
    // Load the model
    $this->load->model('vendor/claim/returnClaim');

    // Check if return_id and vendor_id are sent via POST request  
    if (isset($this->request->post['return_id'])) {
      $return_id = (int)$this->request->post['return_id'];
      // $vendor_id = (int)$this->request->post['vendor_id'];
      $vendor_id = $this->vendor->getId();

      // Call the model method to get the return product data
      $product_data = $this->model_vendor_claim_returnClaim->getReturnProduct($return_id);

 if (!empty($product_data['product_image']) && is_file(DIR_IMAGE . $product_data['product_image'])) {
     $this->load->model('tool/image');
        $image = $this->model_tool_image->resize($product_data['product_image'], 80, 80);
      }
    //   var_dump($image,'product image');
      if ($product_data) {
        $hidden_inputs = '
        <input type="hidden" name="return_id" value="' . $product_data['return_id'] . '">
        <input type="hidden" name="order_id" value="' . $product_data['order_id'] . '">
        <input type="hidden" name="product_id" value="' . $product_data['product_id'] . '">
        <input type="hidden" name="product" value="' . $product_data['product'] . '">
        <input type="hidden" name="return_date" value="' . $product_data['return_date'] . '">
        <input type="hidden" name="customer_id" value="' . $product_data['customer_id'] . '">
        <input type="hidden" name="claim_amount" value="' . $product_data['product_total'] . '">
        <input type="hidden" name="product_model" value="' . $product_data['model'] . '">
    ';

        $output = "
        <div id='productDetails'>
				<div class='title'>
					<img src='".$image."' alt='product image'>
					<div>
						<h4>" . $product_data['product'] . "</h4>
						<p>". $product_data['model'] ."</p>
					</div>
				</div>
				<div class='contents' id='product-info'>
					<div>
						<span>Price</span>
						<span>₹ ". $product_data['product_total'] ."</span>
					</div>
					<div>
						<span>Return Type</span>
						<span>Courier return</span>
					</div>
					<div>
						<span>Order Id</span>
						<span>" . $product_data['order_id'] . "</span>
					</div>
					<div>
						<span>return Id</span>
						<span>" . $product_data['return_id'] . "</span>
					</div>
					<div>
						<span>Order Item Id</span>
						<span>" . $product_data['product_id'] . "</span>
					</div>
					<div>
						<span>Return On</span>
						<span>" . $product_data['return_date'] . "</span>
					</div>
				</div>
			</div>" . $hidden_inputs;

        $this->response->setOutput($output);
      } else {
        $this->response->setOutput('No product data found for this return.');
      }
    } else {
      $this->response->setOutput('Invalid parameters.');
    }
  }
  public function create_claim()
  {
    if ($this->request->server['REQUEST_METHOD'] == 'POST') {
      // Retrieve form data
      
      $return_id = (int)$this->request->post['return_id'];
      $order_id = (int)$this->request->post['order_id'];
      $product_id = (int)$this->request->post['product_id'];
      $claim_amount = (int)$this->request->post['claim_amount'];
      $model = $this->request->post['product_model'];
      $product = isset($this->request->post['product']) ? $this->request->post['product'] : '';
      $return_date = isset($this->request->post['return_date']) ? $this->request->post['return_date'] : '';
      $description = isset($this->request->post['description']) ? $this->request->post['description'] : '';
      // Or print it in a more readable format
    //   print_r($this->request->post);

      // Optionally, log it into the error log
      error_log(print_r($this->request->post, true));

      $claim_issue_area_id = isset($this->request->post['claim_issue_area_id']) ? (int)$this->request->post['claim_issue_area_id'] : 0;
      $claim_issue_type_id = isset($this->request->post['claim_issue_type_id']) ? (int)$this->request->post['claim_issue_type_id'] : 0;
      // var_dump($claim_issue_type_id);
      // Vendor ID
      $vendor_id = $this->vendor->getId();

      // Process the uploaded images (you can use the same logic as before)

      $image_paths = [];

      // Handle file uploads
      $upload_dir = DIR_IMAGE . 'claims/'; // Example upload directory

      // Process the shipping label image (firstImage)
      if (isset($_FILES['firstImage']) && $_FILES['firstImage']['tmp_name']) {
        $file = $_FILES['firstImage'];
        $filename = $vendor_id . '_claim_' . time() . '_shipping_label_' . basename($file['name']);
        $destination = $upload_dir . $filename;

        // Move the uploaded shipping label file to the server
        if (move_uploaded_file($file['tmp_name'], $destination)) {
          // Add the shipping label image path to the array
          $image_paths['shipping_label'] = 'catalog/claims/' . $filename; // Relative path for database
        } else {
          // Handle the error if upload failed
          $this->error['warning'] = 'Shipping label upload failed!';
        }
      }

      // Process the other images
      $images = ['secondImage', 'thirdImage', 'fourthImage', 'fifthImage'];
      foreach ($images as $image_key) {
        if (isset($_FILES[$image_key]) && $_FILES[$image_key]['tmp_name']) {
          $file = $_FILES[$image_key];
          $filename = $vendor_id . '_claim_' . time() . '_' . basename($file['name']);
          $destination = $upload_dir . $filename;

          // Move the uploaded file to the server
          if (move_uploaded_file($file['tmp_name'], $destination)) {
            // Add the image path to the array
            $image_paths[$image_key] = 'catalog/claims/' . $filename; // Relative path for database
          } else {
            // Handle the error if upload failed
            $this->error['warning'] = $image_key . ' upload failed!';
          }
        }
      }



      // Prepare claim data for the table
      $claim_data = [
        'vendor_id'      => $vendor_id,
        'return_id'      => $return_id,
        'order_id'       => $order_id,
        'product_id'     => $product_id,
        'product'        => $product,
        'return_date'     => $return_date,
        'description'    => $description,
        'claim_amount'    => $claim_amount,
        'model'    => $model,
        'claim_issue_area_id' => $claim_issue_area_id,   // Add claim_issue_area_id
        'claim_issue_type_id' => $claim_issue_type_id,   // Add claim_issue_type_id

        'shipping_label' => isset($image_paths['shipping_label']) ? $image_paths['shipping_label'] : '', // First image is shipping label
        'images'         => isset($image_paths['secondImage']) ? implode(',', array_slice($image_paths, 1)) : '', // Remaining images
        'date_added'     => date('Y-m-d H:i:s')
      ];
      // var_dump($this->request->post);
      // var_dump('-------------------------------------------------------------------------');
      // var_dump($claim_data);
      // Call the model method to save the claim data
      $this->load->model('vendor/claim/returnClaim');
      $this->model_vendor_claim_returnClaim->createClaim($claim_data);

        $data['success']='application send successfully';
      // Redirect or show success message
       $this->response->redirect($this->url->link('vendor/claim/claim_list',$data));
    }
  }
}
