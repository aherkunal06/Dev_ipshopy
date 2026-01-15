<?php
class ControllerClickpostLabelGeneration extends Controller
{

    public function generateLabels()
    {
        // $this->load->model('checkout/order');
        $this->load->model('clickpost/label_generation');
        $json = [];

        $input = json_decode(file_get_contents('php://input'), true);

        if (!isset($input['order_ids']) || !is_array($input['order_ids'])) {
            $json['success'] = false;
            $json['error'] = 'No order IDs received';
            $this->response->addHeader('Content-Type: application/json');
            $this->response->setOutput(json_encode($json));
            return;
        }

        // Store pickup time in session if provided
        // if (isset($input['pickup_time']) && !empty($input['pickup_time'])) {
        //     $this->session->data['pickup_date'] = $input['pickup_time'];
        // }

        $username = "ipshopy";
        $key = "e24653d6-275f-425f-900a-0247bbd0f24b";
   
        $api_url = "https://www.clickpost.in/api/v3/create-order/?username=$username&key=$key";

        foreach ($input['order_ids'] as $order_id) {
            $order_data = $this->model_clickpost_label_generation->getPickupInfo($order_id);
            if (!$order_data) continue;

            $pickupInfo   = $order_data['pickup_query']->row;
            $dropInfo     = $order_data['drop_query']->row;
            $shipmentRows = $order_data['shipment_query']->rows;
            

            // $pickup_datetime = (isset($this->session->data['pickup_date']) && !empty($this->session->data['pickup_date']))
            //     ? (new DateTime($this->session->data['pickup_date'], new DateTimeZone(date_default_timezone_get())))->format('c')
            //     : (new DateTime('now', new DateTimeZone(date_default_timezone_get())))->format('c');


            // === Call Recommendation API ===
            $recommendation_payload = $this->model_clickpost_label_generation->recommendation($order_id);
            $cp_id = '';
            $account_code = '';
            if ($recommendation_payload) {
                
                $username = 'ipshopy';
                $key = 'e24653d6-275f-425f-900a-0247bbd0f24b';
        
                $rec_url = "https://www.clickpost.in/api/v1/recommendation_api/?username=$username&key=$key";
                
                $ch = curl_init($rec_url);
                curl_setopt_array($ch, [
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_POST => true,
                    CURLOPT_POSTFIELDS => json_encode($recommendation_payload),
                    CURLOPT_HTTPHEADER => ['Content-Type: application/json']
                ]);
                $rec_response = curl_exec($ch);
                curl_close($ch);

                $rec_result = json_decode($rec_response, true);
                if (!empty($rec_result['result'][0]['preference_array'])) {
                    foreach ($rec_result['result'][0]['preference_array'] as $option) {
                        if (!empty($option['cp_id']) && !empty($option['account_code'])) {
                            $cp_id = (int)$option['cp_id'];
                            $account_code = $option['account_code'];
                            $shipping_charge = $option['shipping_charge'];
                            break;
                        }
                    }
                }
            }
            
            // var_dump($recommendation_payload);
            // var_dump($rec_result); // Check the structure of the response



            if (!$cp_id || !$account_code) {
                $json['success'] = false;
                $json['error'] = 'No valid courier partner found for order ' . $order_id;
                $this->response->addHeader('Content-Type: application/json');
                $this->response->setOutput(json_encode($json));
                return;
            }


            foreach ($shipmentRows as $shipment_details) {
                $payload = [
                    "pickup_info" => [
                        "pickup_state"   => $pickupInfo["zone_name"],
                        "pickup_address" => $pickupInfo["address_1"],
                        "email"          => $pickupInfo["email"],
                        "pickup_time"    => date("c"),
                        "pickup_pincode" => $pickupInfo["postcode"],
                        "pickup_city"    => $pickupInfo["city"],
                        "tin"            => $pickupInfo["gstin"],
                        "pickup_name"    => $pickupInfo["firstname"] . " " . $pickupInfo["lastname"],
                        "pickup_country" => "IN",
                        "pickup_phone"   => $pickupInfo["telephone"],
                        "pickup_lat"     => "",
                        "pickup_long"    => ""
                    ],
                    "drop_info" => [
                        "drop_address"  => $dropInfo["payment_address_1"],
                        "drop_phone"    => $dropInfo["telephone"],
                        "drop_country"  => "IN",
                        "drop_state"    => $dropInfo["payment_zone"],
                        "drop_pincode"  => $dropInfo["payment_postcode"],
                        "drop_city"     => $dropInfo["payment_city"],
                        "drop_name"     => $dropInfo["firstname"] . " " . $dropInfo["lastname"],
                        "drop_email"    => $dropInfo["email"],
                        "drop_lat" => "",
                        "drop_long" => ""
                    ],
                    "shipment_details" => [
                        "height" => (int)round($shipment_details["height"], 2),
                        "order_type" => strtolower($dropInfo["payment_code"]) === 'cod' ? 'COD' : 'PREPAID',
                        "invoice_value" => round($dropInfo["total"], 2),
                        "invoice_number" => "#" . $dropInfo["invoice_no"],
                        "invoice_date" => date('Y-m-d', strtotime($dropInfo["date_added"])),
                        "reference_number" => "Order-" . $dropInfo["order_id"],
                        "length" => (int)round($shipment_details["length"], 2),
                        "breadth" => (int)round($shipment_details["width"], 2),
                        "weight" => (int)round($shipment_details["weight"], 2),
                        "items" => [
                            [
                                "product_url" => "",
                                "price" => $shipment_details["order_price"],
                                "description" => $shipment_details["order_name"],
                                "quantity" => $shipment_details["order_quantity"],
                                "sku" => $shipment_details["sku"],
                                "additional" => [
                                    "length" => (int)round($shipment_details["length"], 2),
                                    "height" => (int)round($shipment_details["height"], 2),
                                    "breadth" => (int)round($shipment_details["width"], 2),
                                    "weight" => (int)round($shipment_details["weight"], 2),
                                    "images" => "https://www.ipshopy.com/image/" . $shipment_details["image"],
                                    "return_days" => ""
                                ]
                            ]
                        ],
                        // "cod_value" => round($dropInfo["total"], 2) , // updated the changes on 02-07-2025
                        "cod_value" => strtolower($dropInfo["payment_code"]) === 'cod' ? round($dropInfo["total"], 2) : 0, // updated the changes on 03-07-2025
                        "courier_partner" => $cp_id
                    ],
                    "gst_info" => [
                        "seller_gstin" => $pickupInfo["gstin"],
                        "taxable_value" => "",
                        "ewaybill_serial_number" => "",
                        "is_seller_registered_under_gst" => false,
                        "sgst_tax_rate" => "",
                        "place_of_supply" => "",
                        "gst_discount" => "",
                        "hsn_code" => $shipment_details["hsn_code"],
                        "sgst_amount" => "",
                        "enterprise_gstin" => "",
                        "gst_total_tax" => "",
                        "igst_amount" => "",
                        "cgst_amount" => "",
                        "gst_tax_base" => "",
                        "consignee_gstin" => "",
                        "igst_tax_rate" => "",
                        "invoice_reference" => "",
                        "cgst_tax_rate" => ""
                    ],
                    "additional" => [
                        "label" => true,
                        "return_info" => [
                            "pincode" => $pickupInfo["postcode"],
                            "address" => $pickupInfo["address_1"],
                            "state" => $pickupInfo["zone_name"],
                            "phone" => $pickupInfo["telephone"],
                            "name" => $pickupInfo["firstname"] . " " . $pickupInfo["lastname"],
                            "city" => $pickupInfo["city"],
                            "country" => "IN",
                            "return_lng" => "",
                            "return_lat" => ""
                        ],
                        "reseller_info" => [
                            "name" => $pickupInfo["firstname"] . " " . $pickupInfo["lastname"],
                            "phone" => $pickupInfo["telephone"]
                        ],
                        "delivery_type" => "FORWARD",
                        "async" => false,
                        "gst_number" => $pickupInfo["gstin"],
                        "account_code" => $account_code,
                        "from_wh" => "",
                        "to_wh" => "",
                        "channel_name" => "",
                        "order_date" => $dropInfo["date_added"],
                        "enable_whatsapp" => true,
                        "is_fragile" => true,
                        "is_dangerous" => true,
                        "order_id" => $dropInfo["order_id"]
                    ]
                ];
                
                // var_dump($payload);
                
                // Send to ClickPost
                $curl = curl_init();
                curl_setopt_array($curl, [
                    CURLOPT_URL => $api_url,
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_CUSTOMREQUEST => "POST",
                    CURLOPT_POSTFIELDS => json_encode($payload),
                    CURLOPT_HTTPHEADER => [
                        "Content-Type: application/json"
                    ],
                ]);

                $response = curl_exec($curl);
                $http_status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
                curl_close($curl);

                $result = json_decode($response, true);


                if ($http_status !== 200 || empty($result['meta']['success'])) {
                    $json['success'] = false;
                    $json['error'] = 'API call failed for order ' . $order_id;
                    $json['response'] = $response; // optional debug info
                    $this->response->addHeader('Content-Type: application/json');
                    $this->response->setOutput(json_encode($json));
                    return;
                }
                


                $commercial_invoice_url    = $result['result']['commercial_invoice_url'] ?? '';
                $waybill        = $result['result']['waybill'] ?? '';
                $reference_number = $result['result']['reference_number'] ?? '';
                $label_url      = $result['result']['label'] ?? '';
                $courier_partner_id = $result['result']['courier_partner_id'] ?? '';
                $courier_name   = $result['result']['courier_name'] ?? '';
                $ipshopy_order_id       = $result['result']['order_id'] ?? '';
                $tracking_id    = $result['result']['tracking_id'] ?? '';


                $this->model_clickpost_label_generation->saveClickpostLabelData(
                    $ipshopy_order_id,
                    $commercial_invoice_url,
                    $waybill,
                    $reference_number,
                    $shipping_charge,
                    $label_url,
                    $courier_partner_id,
                    $courier_name,
                    $tracking_id,
                    $pickup_datetime
                );
                
                $this->model_clickpost_label_generation->saveClickpostToOrder($ipshopy_order_id, $waybill, $label_url, $courier_partner_id, $courier_name);
                
                 $results[] = [
                    'order_id' => $ipshopy_order_id,
                    'label_url' => $label_url,
                    'waybill' => $waybill,
                    'reference_number' => $reference_number,
                    'courier_partner_id' => $courier_partner_id,
                    'courier_name' => $courier_name,
                    'tracking_id' => $tracking_id
                ];
            }
        }

        $json['success'] = true;
        $json['message'] = 'Labels generated successfully.';
        $json['results'] = $results ?? [];
        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }
    
    
//     public function createManifest() {
//         // Only allow POST requests
//         if ($this->request->server['REQUEST_METHOD'] !== 'POST') {
//             $this->response->addHeader('Content-Type: application/json');
//             $this->response->setOutput(json_encode([
//                 'status'  => false,
//                 'message' => 'Invalid request method. Use POST.'
//             ]));
//             return;
//         }
    
//         // Decode incoming JSON
//         $data = json_decode(file_get_contents('php://input'), true);
//         if (empty($data['order_ids']) || !is_array($data['order_ids'])) {
//             $this->response->addHeader('Content-Type: application/json');
//             $this->response->setOutput(json_encode([
//                 'status'  => false,
//                 'message' => 'Missing or invalid order IDs.'
//             ]));
//             return;
//         }
    
//         $order_ids = $data['order_ids'];
    
//         // Step 1: Insert a new record into `oc_clickpost_order` table
//         $this->db->query("
//             INSERT INTO `" . DB_PREFIX . "clickpost_order` (manifest_date)
//             VALUES (NOW())
//         ");
        
//         // Step 2: Fetch the generated manifest_id
//         $manifest_id = $this->db->getLastId();
    
//         if (!$manifest_id) {
//             // If no manifest_id is generated, return an error
//             $this->response->addHeader('Content-Type: application/json');
//             $this->response->setOutput(json_encode([
//                 'status'  => false,
//                 'message' => 'Error generating manifest ID.'
//             ]));
//             return;
//         }
    
//         // Step 3: Map the orders to the generated manifest_id and update the order status to "Manifested" (Status ID: 16)
//         $this->load->model('vendor/order_report');
//         foreach ($order_ids as $order_id) {
//             // Associate each order with the newly generated manifest_id
//             $this->model_clickpost_label_generation->assignOrderToManifest($order_id, $manifest_id);
    
//             // Change order status to "Manifested (16)"
//             $this->model_vendor_order_report->changeOrderStatus($order_id, 16);
//         }
    
//         // Return success message with the generated manifest_id
//         $this->response->addHeader('Content-Type: application/json');
//         $this->response->setOutput(json_encode([
//             'status'      => true,
//             'message'     => 'Manifest created successfully.',
//             'manifest_id' => $manifest_id
//         ]));
//     }


//      public function downloadManifest()
// 	{
// 		if (!isset($this->request->get['manifest_id'])) {
// 			die('Manifest ID is required.');
// 		}

// 		$manifest_id = (int)$this->request->get['manifest_id'];
// 		$this->load->model('clickpost/label_generation');

// 		// Fetch all orders for the given manifest ID
// 		$orders = $this->model_clickpost_label_generation->getManifestData($manifest_id);

// 		if (empty($orders)) {
// 			die('No orders found for this manifest.');
// 		}

// 		// Create a new PDF instance
// 		$pdf = new TCPDF();
// 		$pdf->SetCreator(PDF_CREATOR);
// 		$pdf->SetAuthor('IP Supershoppee Private Limited');
// 		$pdf->SetTitle('Order Manifest');
// 		$pdf->SetHeaderData('', 0, 'Order Manifest', 'Manifest ID: ' . $manifest_id);
// 		$pdf->setHeaderFont(['helvetica', '', 10]);
// 		$pdf->setFooterFont(['helvetica', '', 8]);
// 		$pdf->SetDefaultMonospacedFont('courier');
// 		$pdf->SetMargins(10, 10, 10);
// 		$pdf->SetAutoPageBreak(TRUE, 15);  // ✅ Ensure page breaks happen correctly
// 		$pdf->SetFont('helvetica', '', 10);
// 		$pdf->AddPage(); // ✅ Add the first page

// 		// ✅ Table Header
// 		$currentDate = date('M j, Y');
// 		$html = '</br>';
// 		$i = 1;
// 		foreach ($orders as $order) {
// 			$html .= '<h3 style="text-align: center; margin-bottom: 20px; margin-top: 20px;">' . $order['courier_name'] . ' Order Manifest</h3>';
// 			$html .= '<p style="font-size: 8px margin:0px; padding:0px;">IP SUPERSHOPPEE PRIVATE LIMITED 
// 			<span style="margin-left: 2px;">  (Merchant ID: 56871)</span> 
// 			<span style="margin-left: 10px;">	 (Manifest Id : ' . $manifest_id . ')</span>
// 			<span style="margin-left: 5px;">  Manifest Date : ' . $currentDate . '</span> 
// 			<span style="margin-left: 5px;">  Payment Type : ' . $order['payment_code'] . '</span> 
// 			</p>';
// 			$html .= '<table border="1" cellpadding="5" cellspacing="0" width="770px" >
// 		<thead>
// 			<tr style="background-color:#ddd;">
// 				<th style="width:18px;">#</th>
// 				<th style="width:100px;">Customer Info</th>
// 				<th style="width:185px;">Product Name & SKU (Qty)</th>
// 				<th style="width:30px;">T. Qty</th>
// 				<th style="width:55px;">Amount</th>
// 				<th style="width:60px;">Order Info</th>
// 				<th style="width:90px;">AWB Barcode</th>
// 			</tr>
// 		</thead>
// 		<tbody style="font-size: 14px;">';


// 			$html .= '<tr>
// 						<td style="width:18px; vertical-align: middle; text-align:center;">' . $i++ . '</td>
// 						<td style="width:100px">' . $order['customer_name'] . '<br>' . $order['customer_address'] . '<br>' . $order['email'] . '</td>
// 						<td style="width:185px">' . $order['product_name'] . ' (' . $order['quantity'] . ')</td>
// 						<td style="width:30px; vertical-align: middle; text-align:center; ">' . $order['quantity'] . '</td>
// 						<td style="width:55px">Rs ' . number_format($order['amount'], 2) . '</td>
// 						<td style="width:60px">' . $order['order_id'] . '<br>' . $order['order_date'] . '</td>
// 						 <td style="width:90px">' . $order['awbno'] . '</td>
// 					  </tr>';
// 			$html .= '</tbody></table>';
// 			$html .= '
//   	    <div style="display:block; margin-bottom:10px; margin-top:5px ">
//         <table>
//             <tr >
//                 <td style="width: 280px; text-align: left;">Merchant Signature:</td>
//                 <td style="width: 280px; text-align: left;">Courier Signature:</td>
//             </tr>
//             <tr>
//                 <td style="width: 280px; text-align: left;">Merchant SPOC Name:</td>
//                 <td style="width: 280px; text-align: left;">Courier SPOC Name:</td>
//             </tr>
//         </table>
//   	    </div>';
// 		}
// 		// ✅ Write the complete HTML to the PDF
// 		$pdf->writeHTML($html, true, false, true, false, '');

// 		// ✅ File Naming & Storage
// 		$filename = 'manifest_' . $manifest_id . '_' . date('Y-m-d_H-i-s') . '.pdf';
// 		$file_path = DIR_DOWNLOAD . $filename;

// 		// ✅ Save the Manifest File Securely
// 		$pdf->Output($file_path, 'F');

// 		// ✅ Generate a Secure Download URL
// 		$secure_url = HTTP_SERVER . 'index.php?route=vendor/order_report/secureDownload&manifest_id=' . $manifest_id;

// 		// ✅ Save URL in the database
// 		foreach ($orders as $order) {
// 			$this->model_vendor_order_report->saveManifest($order['order_id'], $manifest_id, $secure_url);
// 		}

// 		// ✅ Return JSON Response with Secure Download Link
// 		$this->response->addHeader('Content-Type: application/json');
// 		$this->response->setOutput(json_encode([
// 			'status' => true,
// 			'file_url' => $secure_url
// 		]));
// 	}
	
// 	public function secureDownload()
// 	{
// 		if (!isset($this->request->get['manifest_id'])) {
// 			die('Manifest ID is required.');
// 		}

// 		$manifest_id = (int)$this->request->get['manifest_id'];
// 		$this->load->model('vendor/order_report');

// 		// ✅ Ensure only logged-in vendors or admins can access
// 		if (!isset($this->session->data['vendor_id']) && !isset($this->session->data['user_id'])) {
// 			die('Access Denied: Please log in.');
// 		}

// 		// ✅ Get the Manifest File
// 		$filename = 'manifest_' . $manifest_id . '_';
// 		$files = glob(DIR_DOWNLOAD . $filename . "*.pdf");

// 		if (!$files) {
// 			die('Error: Manifest file not found.');
// 		}

// 		$file_path = $files[0]; // Get the first matching file

// 		// ✅ Force Secure File Download
// 		header('Content-Description: File Transfer');
// 		header('Content-Type: application/pdf');
// 		header('Content-Disposition: attachment; filename="' . basename($file_path) . '"');
// 		header('Expires: 0');
// 		header('Cache-Control: must-revalidate');
// 		header('Pragma: public');
// 		header('Content-Length: ' . filesize($file_path));
// 		readfile($file_path);
// 		exit;
// 	}


}
