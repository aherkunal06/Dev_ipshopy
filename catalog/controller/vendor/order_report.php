<?php
require_once(DIR_SYSTEM . 'library/tcpdf/tcpdf.php');
class ControllerVendorOrderReport  extends Controller {

	private $shipway_api_url = "https://app.shipway.com/api/v2orders";
	private $shipway_username = "ipshopy1@gmail.com";
	private $shipway_key = "96V1f01z291K02U1jg35s5Sb93gB4QmY";

	private $error = array();
	public function index()
	{
		if (!$this->vendor->isLogged()) {
			$this->response->redirect($this->url->link("vendor/login", "", true));
		}
		$this->load->language("vendor/order_report");
		$this->document->setTitle($this->language->get("heading_title"));
		$this->load->model("vendor/order_report");
    // 		added on 12_02_2024 by sagar 
        $this->load->model("vendor/vendor");
		$this->load->model('tool/image');
		$this->getList();
	}

	public function getList()
	{
		$this->load->model("vendor/vendor");
		$this->load->model("vendor/order_report");
		$pagination_limit = 10;
		if (isset($this->request->get["filter_order_id"])) {
			$filter_order_id = $this->request->get["filter_order_id"];
		} else {
			$filter_order_id = "";
		}

		if (isset($this->request->get["filter_customer"])) {
			$filter_customer = $this->request->get["filter_customer"];
		} else {
			$filter_customer = "";
		}


		if (isset($this->request->get["sort"])) {
			$sort = $this->request->get["sort"];
		} else {
			$sort = "o.order_id";
		}

		if (isset($this->request->get["order"])) {
			$order = $this->request->get["order"];
		} else {
			$order = "DESC";
		}

		if (isset($this->request->get["page"])) {
			$page = $this->request->get["page"];
		} else {
			$page = 1;
		}
		
		// for rtd pagination-------- 19-05-2025-------------------
		$page_rtd = isset($this->request->get['page_rtd']) ? (int)$this->request->get['page_rtd'] : 1;
		$page_breached = isset($this->request->get['page_breached']) ? (int)$this->request->get['page_breached'] : 1;
		$page_cancel = isset($this->request->get['page_cancel']) ? (int)$this->request->get['page_cancel'] : 1;
		$page_track = isset($this->request->get['page_track']) ? (int)$this->request->get['page_track'] : 1;
		$page_manifest = isset($this->request->get['page_manifest']) ? (int)$this->request->get['page_manifest'] : 1;


		// ---====-------------------------------------------
		
		$data['cancel'] = $this->url->link('vendor/dashboard');

		$url = "";

		if (isset($this->request->get["filter_order_id"])) {
			$url .= "&filter_order_id=" . $this->request->get["filter_order_id"];
		}

		if (isset($this->request->get["filter_customer"])) {
			$url .= "&filter_customer=" . $this->request->get["filter_customer"];
		}

		if (isset($this->request->get["sort"])) {
			$url .= "&sort=" . $this->request->get["sort"];
		}

		if (isset($this->request->get["order"])) {
			$url .= "&order=" . $this->request->get["order"];
		}

		if (isset($this->request->get["page"])) {
			$url .= "&page=" . $this->request->get["page"];
		}
		
        //----------------- added on the 19-05-2025-----------------
        if (isset($this->request->get["page_rtd"])) {
			$url .= "&page_rtd=" . (int)$this->request->get["page_rtd"];
		}

		if (isset($this->request->get["page_breached"])) {
			$url .= "&page_breached=" . (int)$this->request->get["page_breached"];
		}

		if (isset($this->request->get["page_cancel"])) {
			$url .= "&page_cancel=" . (int)$this->request->get["page_cancel"];
		}

		if (isset($this->request->get["page_track"])) {
			$url .= "&page_track=" . (int)$this->request->get["page_track"];
		}

		if (isset($this->request->get["page_manifest"])) {
			$url .= "&page_manifest=" . (int)$this->request->get["page_manifest"];
		}
        // ----------------------------------------------------------

		$data["breadcrumbs"] = array();

		$data["breadcrumbs"][] = array(
			"text" => $this->language->get("text_home"),
// 			"href" => $this->url->link("common/home", "", true)added changes on 19-05-2025
            'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
            // -----------------------------------------------------------------------
		);

		$data["breadcrumbs"][] = array(
			"text" => $this->language->get("heading_title"),
// 			"href" => $this->url->link("vendor/order_report", "", true) added changes on 19-05-2025
            'href' => $this->url->link('vendor/order_report', 'user_token=' . $this->session->data['user_token'] . $url, true)
            // -----------------------------------------------------------------------
		);
		//print_r($this->request->post); die();

		$data["reports"] = array();

        $common_filter = [
        		"filter_order_id"  => $filter_order_id,
        		"filter_customer"  => $filter_customer,
        		"sort"             => $sort,
        		"order"            => $order
        	];
        	
        $report_track = [];
		$report_total_track = 0;

        // Status types and their page variables on 19-05-2025
    	$status_pages = [
    		'Processing'    => $page,
    		'Label Generated'    => $page_rtd,
    		'Cancelled' => $page_cancel,
    		'Manifested'                 => $page_manifest,
            'Breached'                 => $page_breached,
    
    	];
    	
	   // ---------------------------------------------------------------------
	   

	    // added logic to get the all statuses dynamically
        $track_statuses = $this->model_vendor_order_report->getTrackableStatuses();
                    
        $page_track = isset($this->request->get['page_track']) && (int)$this->request->get['page_track'] > 0 ? (int)$this->request->get['page_track'] : 1;
        // $start = ($page_track - 1) * $pagination_limit;
        
        $filter_data_track = array_merge($common_filter, [
            'filter_order_statuses' => $track_statuses,
            'start'                 => ($page_track - 1) * $pagination_limit,
            'limit'                 => $pagination_limit
        ]);
        
        $reports_track = $this->model_vendor_order_report->getReports_n($filter_data_track);
        
        // ✅ Do not pass start/limit for total count
        $total_data_track = array_merge($common_filter, [
            'filter_order_statuses' => $track_statuses
        ]);
        $report_total_track = $this->model_vendor_order_report->getTotalTrackOrders($total_data_track);
        
        
        if (!empty($reports_track)) {
            foreach ($reports_track as $report) {
                $product_total = $this->model_vendor_vendor->getTotalOrderProductsByOrderId($report["order_id"], $report["vendor_id"]);
                $vorder_total = $this->model_vendor_vendor->getvendorOrdertotal($report["vendor_id"], $report["order_id"]);
                $label = $this->model_vendor_order_report->getShippingLabel($report["order_id"]);
                $image = $this->model_vendor_vendor->getOrderProducts($report["order_id"]);
                $image2 = (!empty($image) && isset($image[0]['image']) && is_file(DIR_IMAGE . $image[0]['image']))
                    ? $this->model_tool_image->resize($image[0]['image'], 50, 50)
                    : 'no image';
        
                $status_info = $this->model_vendor_order_report->getOrderStatus($report["order_status_id"]);
                $statusname = isset($status_info["name"]) ? $status_info["name"] : "";
        
                $data["reports_track"][] = array(
                    "order_product_id" => $report["order_product_id"],
                    "order_status_id" => $report["order_status_id"],
                    "order_id"      => $report["order_id"],
                    "firstname"     => $report["firstname"] . " " . $report["lastname"],
                    "name"     		=> $report["name"],
                    "total" 	    => $this->currency->format($vorder_total["total"], $this->config->get("config_currency")),
                    "noofproduct"   => $product_total,
                    "statusname"    => $statusname,
                    "date_added"	=> date($this->language->get("date_format_short"), strtotime($report["date_added"])),
                    "view"          => $this->url->link("vendor/latestorder/letestview", "&order_id=" . $report["order_id"]),
                    "shipping_code" => $report["shipping_code"],
                    "shipping_label" => $label,
                    'shipping_manifest' =>  $report["shipping_manifest"],
                    'product_image' =>  $image2
                );
            }
        }
        // ✅ Pagination for Track Orders
        $paginationTrack = new Pagination();
        $paginationTrack->total = $report_total_track;
        $paginationTrack->page = $page_track;
        $paginationTrack->limit = $pagination_limit;
        $paginationTrack->url = $this->url->link(
            'vendor/order_report',
            'user_token=' . $this->session->data['user_token'] . $url . '&page_track={page}#tab-track',
            true
        );
        
        $data['paginationTrack'] = $paginationTrack->render();
         
        // ✅ Pagination Summary Text for Track Orders
        $start_result = ($report_total_track) ? (($page_track - 1) * $pagination_limit) + 1 : 0;
        $end_result = ($start_result + count($reports_track) - 1);
        
        $data['resultsTrack'] = sprintf(
            $this->language->get('text_pagination'),
            $start_result,
            $end_result,
            $report_total_track,
            ceil($report_total_track / $pagination_limit)
        );
	   
	   foreach ($status_pages as $status => $status_page) {
		$filter_data = array_merge($common_filter, [
			'filter_order_status' => $status,
			'start'               => ($status_page - 1) * $pagination_limit,
			'limit'               => $pagination_limit
		]);

		$reports = $this->model_vendor_order_report->getReports($filter_data);
		$data["reports_" . $status] = [];
		
		//    added by shubham for pagination
		$report_total_pending = $this->model_vendor_order_report->getTotalProcessingOrders($filter_data);
	
    	// -===============-------------

    	// for rtd total pagination
		$report_total_rtd = $this->model_vendor_order_report->getTotalRtdOrders($filter_data);
	
    	// -------==========
    	// -===========for cancel pagination
		$report_total_cancel = $this->model_vendor_order_report->getTotalCancelledOrders($filter_data);
	
    	// ---------==============
    	// for complete===== pagination
		$report_total_track = $this->model_vendor_order_report->getTotalCompleteOrders($filter_data);
	
    	// --====
    	// for menifesto----===== pagination
		$report_total_manifest = $this->model_vendor_order_report->getTotalManifestOrders($filter_data);
	
    	// ---=====

    	// for brached====== pagination
		$report_total_breached = $this->model_vendor_order_report->getTotalBreachedOrders($filter_data);
    	// --========================
	   //-----------------------------------------------------------------------------------------------------------
	   //--- commented the code added new one 19-05-2025 -------------------------
        // 		$report_total = $this->model_vendor_order_report->getTotalReport($filter_data);
        // 		$reports = $this->model_vendor_order_report->getReports($filter_data);

		if (isset($reports)) {
			foreach ($reports as $report) {

                // added the code related to the estimated charges calculation 22-04-2025 --------------------
                $order_date = strtotime($report["date_added"]); // Assuming date_added contains the order date
				$today = strtotime(date('2025-04-11')); // Start of today
             
				$estimatedCourierCharges = 0;
				$netSettlement = 0;
			   // ------------------------------------------
				$product_total = $this->model_vendor_vendor->getTotalOrderProductsByOrderId($report["order_id"], $report["vendor_id"]);
				$vorder_total = $this->model_vendor_vendor->getvendorOrdertotal($report["vendor_id"], $report["order_id"]);
				$label = $this->model_vendor_order_report->getShippingLabel($report["order_id"]);

				$sellers = $this->model_vendor_vendor->getVendor($report["vendor_id"]);
				$status_info = $this->model_vendor_order_report->getOrderStatus($report["order_status_id"]);
				if (isset($status_info["name"])) {
					$statusname = $status_info["name"];
				} else {
					$statusname = "";
				}
				
				// added on 12_02_2024 by sagar 
				$image = $this->model_vendor_vendor->getOrderProducts($report["order_id"]);
				$image2 = (!empty($image) && isset($image[0]['image']) && is_file(DIR_IMAGE . $image[0]['image']))
					? $this->model_tool_image->resize($image[0]['image'], 50, 50) // Resize to 100x100px
					: 'no image';
					
				// added the code related to the estimated charges calculation on 22-04-2025--------------	
                if ($order_date >= $today &&
                    (empty($report['estimated_courier_charges']) || $report['estimated_courier_charges'] == 0.00) &&
                    (empty($report['net_settlement']) || $report['net_settlement'] == 0.00)) {
                        
						$calculated_charges = $this->calculateEstimatedCharges($report['order_id']);
				        // var_dump("$calculated_charges",$calculated_charges);
						$estimatedCourierCharges = $calculated_charges['estimated_courier_charges'];
						$netSettlement = $calculated_charges['net_settlement'];
					
					$this->model_vendor_order_report->saveOrderCharges($report['order_id'], $estimatedCourierCharges, $netSettlement);
				} 
				else {
					$estimatedCourierCharges = $report['estimated_courier_charges'];
					$netSettlement = $report['net_settlement'];
				}


				// ----------------------------------------------------------------------------------
				
				$data["reports"][] = array(
					"order_product_id" => $report["order_product_id"],
					/* 27 04 2020 add code*/
					"order_status_id" => $report["order_status_id"],
					/* 27 04 2020 add code */
					"order_id"      => $report["order_id"],
					"firstname"     => $report["firstname"] . " " . $report["lastname"],
					"name"     		=> $report["name"],
					"total" 	    => $this->currency->format($vorder_total["total"], $this->config->get("config_currency")),
					"noofproduct"   => $product_total,
					"statusname"    => $statusname,
					"date_added"	=> date($this->language->get("date_format_short"), strtotime($report["date_added"])),
					"view"      => $this->url->link("vendor/latestorder/letestview", "&order_id=" . $report["order_id"]),
					/* 18-02-2020 */
					"shipping_code" => $report["shipping_code"],
					/* 18-02-2020 */
					"shipping_label" => $label,
					'product_image' =>  $image2,
				// 	added the following variables on 22-04-2025
					'estimated_courier_charges' => $estimatedCourierCharges,
					'net_settlement' => $netSettlement,
					//added code here
					'shipping_manifest' =>  $report["shipping_manifest"]
				// 	------------------------------------------
				);
			}
		}
		
// 		for multi warehosue 19-06-2025
		// ✅ Add here: warehouse list for dropdown
		$this->load->controller('vendor/warehouse');
		$data['warehouses'] = $this->load->controller('vendor/warehouse/getWarehousesForDropdown');

		$data["heading_title"]          = $this->language->get("heading_title");
		$data["text_list"]           	= $this->language->get("text_list");
		$data["text_no_results"] 		= $this->language->get("text_no_results");
		$data["text_confirm"]			= $this->language->get("text_confirm");
		$data["text_none"] 				= $this->language->get("text_none");
		$data["text_enable"]            = $this->language->get("text_enable");
		$data["text_disable"]           = $this->language->get("text_disable");
		$data["text_select"]            = $this->language->get("text_select");
		$data["text_missing"]           = $this->language->get("text_missing");
		$data["column_order_id"]	    = $this->language->get("column_order_id");
		$data["column_customer"]		= $this->language->get("column_customer");
		$data["column_product"]			= $this->language->get("column_product");
		$data["column_total"]			= $this->language->get("column_total");
		$data["column_status"]			= $this->language->get("column_status");
		$data["column_date"]			= $this->language->get("column_date");
		$data["column_action"]			= $this->language->get("column_action");
		$data["entry_order_id"]			= $this->language->get("entry_order_id");
		$data["entry_customer"]			= $this->language->get("entry_customer");
		$data["entry_seller"]			= $this->language->get("entry_seller");
		$data["entry_status"]			= $this->language->get("entry_status");
		$data["entry_date"]			    = $this->language->get("entry_date");
		$data["button_remove"]          = $this->language->get("button_remove");
		$data["button_delete"]          = $this->language->get("button_delete");
		$data["button_filter"]          = $this->language->get("button_filter");
		$data["button_view"]            = $this->language->get("button_view");
		$data["text_confirm"]           = $this->language->get("text_confirm");
		$data["name"]                   = $this->language->get("name");
		/* 03 10 2019 */
		$data["column_noofproduct"]   = $this->language->get("column_noofproduct");
		/* 03 10 2019 */
		if (isset($this->error["warning"])) {
			$data["error_warning"] = $this->error["warning"];
		} else {
			$data["error_warning"] = "";
		}

		if (isset($this->session->data["success"])) {
			$data["success"] = $this->session->data["success"];
			unset($this->session->data["success"]);
		} else {
			$data["success"] = "";
		}

		if (isset($this->request->post["selected"])) {
			$data["selected"] = (array)$this->request->post["selected"];
		} else {
			$data["selected"] = array();
		}

		$url = "";

		if (isset($this->request->get["filter_order_id"])) {
			$url .= "&filter_order_id=" . $this->request->get["filter_order_id"];
		}

		if (isset($this->request->get["filter_customer"])) {
			$url .= "&filter_customer=" . $this->request->get["filter_customer"];
		}

		if (isset($this->request->get["filter_seller"])) {
			$url .= "&filter_seller=" . $this->request->get["filter_seller"];
		}

		if (isset($this->request->get["filter_status"])) {
			$url .= "&filter_status=" . $this->request->get["filter_status"];
		}

		if (isset($this->request->get["filter_date"])) {
			$url .= "&filter_date=" . $this->request->get["filter_date"];
		}

		if ($order == "ASC") {
			$url .= "&order=DESC";
		} else {
			$url .= "&order=ASC";
		}

		if (isset($this->request->get["page"])) {
			$url .= "&page=" . $this->request->get["page"];
		}

		$data["sort_order_id"]  = $this->url->link("vendor/order_report", "" . "&sort=o.order_id" . $url, true);

		$data["sort_customer"]  = $this->url->link("vendor/order_report", "" . "&sort=o.customer" . $url, true);

		$data["sort_status"]  	= $this->url->link("vendor/order_report", "" . "&sort=o.status" . $url, true);
		$data["sort_date"]  	= $this->url->link("vendor/order_report", "" . "&sort=vop.date" . $url, true);

		$url = "";

		if (isset($this->request->get["filter_order_id"])) {
			$url .= "&filter_order_id=" . $this->request->get["filter_order_id"];
		}

		if (isset($this->request->get["filter_customer"])) {
			$url .= "&filter_customer=" . $this->request->get["filter_customer"];
		}

		if (isset($this->request->get["sort"])) {
			$url .= "&sort=" . $this->request->get["sort"];
		}
		if (isset($this->request->get["order"])) {
			$url .= "&order=" . $this->request->get["order"];
		}

        // commented and added new changes on 19-05-2025 ---------------------------------------------------------------------
        // 		$pagination 		= new Pagination();
        // 		$pagination->total 	= $report_total;
        // 		$pagination->page  	= $page;
        // 		$pagination->limit 	= $this->config->get("config_limit_admin");
        // 		$pagination->url   	= $this->url->link("vendor/order_report", "" . $url . "&page={page}", true);
        // 		$data["pagination"] = $pagination->render();
        // 		$data["results"] = sprintf($this->language->get("text_pagination"), ($report_total) ? (($page - 1) * $this->config->get("config_limit_admin")) + 1 : 0, ((($page - 1) * $this->config->get("config_limit_admin")) > ($report_total - $this->config->get("config_limit_admin"))) ? $report_total : ((($page - 1) * $this->config->get("config_limit_admin")) + $this->config->get("config_limit_admin")), $report_total, ceil($report_total / $this->config->get("config_limit_admin")));
        
        $pagination_pending = new Pagination();
		$pagination_pending->total  = $report_total_pending;
		$pagination_pending->page   = $page;
		$pagination_pending->limit  = $pagination_limit;
		$pagination_pending->url    = $this->url->link("vendor/order_report", $url . "&page={page}#tab-new_orders", true);
		$data["pagination_pending"] = $pagination_pending->render();

		$data["results_pending"] = sprintf(
			$this->language->get("text_pagination"),
			($report_total_pending) ? (($page - 1) * $pagination_limit) + 1 : 0,
			((($page - 1) * $pagination_limit) > ($report_total_pending - $pagination_limit))
				? $report_total_pending
				: ((($page - 1) * $pagination_limit) + $pagination_limit),
			$report_total_pending,
			ceil($report_total_pending / $pagination_limit)
		);

		// // RTD Pagination
		$paginationRtd = new Pagination();
		$paginationRtd->total = $report_total_rtd;
		$paginationRtd->page = $page_rtd;
		$paginationRtd->limit = $pagination_limit;
		$paginationRtd->url = $this->url->link('vendor/order_report_rtd', 'user_token=' . $this->session->data['user_token'] . $url . '&page_rtd={page}#tab-rtd', true);
		$data['paginationRtd'] = $paginationRtd->render();

		$data['resultsRtd'] = sprintf(
			$this->language->get('text_pagination'),
			($report_total_rtd) ? (($page_rtd - 1) * $pagination_limit) + 1 : 0,
			((($page_rtd - 1) * $pagination_limit) > ($report_total_rtd - $pagination_limit)) ? $report_total_rtd : ((($page_rtd - 1) * $pagination_limit) + $pagination_limit),
			$report_total_rtd,
			ceil($report_total_rtd / $pagination_limit)
		);

		// Manifest Pagination
		$paginationManifest = new Pagination();
		$paginationManifest->total = $report_total_manifest;
		$paginationManifest->page = $page_manifest;
		$paginationManifest->limit = $pagination_limit;
		$paginationManifest->url = $this->url->link('vendor/order_report', 'user_token=' . $this->session->data['user_token'] . $url . '&page_manifest={page}#tab-manifest', true);
		$data['paginationManifest'] = $paginationManifest->render();

		$data['resultsManifest'] = sprintf(
			$this->language->get('text_pagination'),
			($report_total_manifest) ? (($page_manifest - 1) * $pagination_limit) + 1 : 0,
			((($page_manifest - 1) * $pagination_limit) > ($report_total_manifest - $pagination_limit)) ? $report_total_manifest : ((($page_manifest - 1) * $pagination_limit) + $pagination_limit),
			$report_total_manifest,
			ceil($report_total_manifest / $pagination_limit)
		);

		// Track Pagination
// 		$paginationTrack = new Pagination();
// 		$paginationTrack->total = $report_total_track;
// 		$paginationTrack->page = $page_track;
// 		$paginationTrack->limit = $pagination_limit;
// 		$paginationTrack->url = $this->url->link('vendor/order_report', 'user_token=' . $this->session->data['user_token'] . $url . '&page_track={page}#tab-track', true);
// 		$data['paginationTrack'] = $paginationTrack->render();

// 		$data['resultsTrack'] = sprintf(
// 			$this->language->get('text_pagination'),
// 			($report_total_track) ? (($page_track - 1) * $pagination_limit) + 1 : 0,
// 			((($page_track - 1) * $pagination_limit) > ($report_total_track - $pagination_limit)) ? $report_total_track : ((($page_track - 1) * $pagination_limit) + $pagination_limit),
// 			$report_total_track,
// 			ceil($report_total_track / $pagination_limit)
// 		);

		// Cancel Pagination
		$paginationCancel = new Pagination();
		$paginationCancel->total = $report_total_cancel;
		$paginationCancel->page = $page_cancel;
		$paginationCancel->limit = $pagination_limit;
		$paginationCancel->url = $this->url->link('vendor/order_report', 'user_token=' . $this->session->data['user_token'] . $url . '&page_cancel={page}#tab-cancel', true);
		$data['paginationCancel'] = $paginationCancel->render();

		$data['resultsCancel'] = sprintf(
			$this->language->get('text_pagination'),
			($report_total_cancel) ? (($page_cancel - 1) * $pagination_limit) + 1 : 0,
			((($page_cancel - 1) * $pagination_limit) > ($report_total_cancel - $pagination_limit)) ? $report_total_cancel : ((($page_cancel - 1) * $pagination_limit) + $pagination_limit),
			$report_total_cancel,
			ceil($report_total_cancel / $pagination_limit)
		);

		// Breached Pagination
		$paginationBreached = new Pagination();
		$paginationBreached->total = $report_total_breached;
		$paginationBreached->page = $page_breached;
		$paginationBreached->limit = $pagination_limit;
		$paginationBreached->url = $this->url->link('vendor/order_report', 'user_token=' . $this->session->data['user_token'] . $url . '&page_breached={page}#tab-breach', true);
		$data['paginationBreached'] = $paginationBreached->render();

		$data['resultsBreached'] = sprintf(
			$this->language->get('text_pagination'),
			($report_total_breached) ? (($page_breached - 1) * $pagination_limit) + 1 : 0,
			((($page_breached - 1) * $pagination_limit) > ($report_total_breached - $pagination_limit)) ? $report_total_breached : ((($page_breached - 1) * $pagination_limit) + $pagination_limit),
			$report_total_breached,
			ceil($report_total_breached / $pagination_limit)
		);
        //-end here-----------------------------------------------------------------------------------------------------------------
		$data["filter_order_id"]	= $filter_order_id;
		$data["filter_customer"]	= $filter_customer;

		$data["sort"]		= $sort;
		$data["order"]		= $order;

		$this->load->model("localisation/order_status");
		$data["order_statuses"] = $this->model_localisation_order_status->getOrderStatuses();

		$data["header"]      = $this->load->controller("vendor/header");
		$data["column_left"] = $this->load->controller("vendor/column_left");
		$data["footer"]      = $this->load->controller("vendor/footer");
$data['manifest_video']= $this->load->controller('common/video_popup', ['video_url' => 'https://www.youtube.com/embed/iZr7FDV3UhY']);
		$this->response->setOutput($this->load->view("vendor/order_report", $data));
    	}
    }
    
	public function invoice()
	{
		$this->load->language("vendor/order_report");

		$data["title"] = $this->language->get("text_invoice");

		if ($this->request->server["HTTPS"]) {
			$data["base"] = HTTPS_SERVER;
		} else {
			$data["base"] = HTTP_SERVER;
		}

		$data["direction"] = $this->language->get("direction");
		$data["lang"] = $this->language->get("code");

		$this->load->model("vendor/order_report");

		$this->load->model("setting/setting");

		$data["orders"] = array();

		$orders = array();

		if (isset($this->request->post["selected"])) {
			$orders = $this->request->post["selected"];
		} elseif (isset($this->request->get["order_id"])) {
			$orders[] = $this->request->get["order_id"];
		}

		foreach ($orders as $order_id) {
			$order_info = $this->model_vendor_order_report->getOrder($order_id);

			if ($order_info) {

				if ($order_info["invoice_no"]) {
					$invoice_no = $order_info["invoice_prefix"] . $order_info["invoice_no"];
				} else {
					$invoice_no = "";
				}

				if ($order_info["payment_address_format"]) {
					$format = $order_info["payment_address_format"];
				} else {
					$format = "{firstname} {lastname}" . "\n" . "{company}" . "\n" . "{address_1}" . "\n" . "{address_2}" . "\n" . "{city} {postcode}" . "\n" . "{zone}" . "\n" . "{country}";
				}

				$find = array(
					"{firstname}",
					"{lastname}",
					"{company}",
					"{address_1}",
					"{address_2}",
					"{city}",
					"{postcode}",
					"{zone}",
					"{zone_code}",
					"{country}"
				);

				$replace = array(
					"firstname" => $order_info["payment_firstname"],
					"lastname"  => $order_info["payment_lastname"],
					"company"   => $order_info["payment_company"],
					"address_1" => $order_info["payment_address_1"],
					"address_2" => $order_info["payment_address_2"],
					"city"      => $order_info["payment_city"],
					"postcode"  => $order_info["payment_postcode"],
					"zone"      => $order_info["payment_zone"],
					"zone_code" => $order_info["payment_zone_code"],
					"country"   => $order_info["payment_country"]
				);

				$payment_address = str_replace(array("\r\n", "\r", "\n"), "<br />", preg_replace(array("/\s\s+/", "/\r\r+/", "/\n\n+/"), "<br />", trim(str_replace($find, $replace, $format))));

				if ($order_info["shipping_address_format"]) {
					$format = $order_info["shipping_address_format"];
				} else {
					$format = "{firstname} {lastname}" . "\n" . "{company}" . "\n" . "{address_1}" . "\n" . "{address_2}" . "\n" . "{city} {postcode}" . "\n" . "{zone}" . "\n" . "{country}";
				}

				$find = array(
					"{firstname}",
					"{lastname}",
					"{company}",
					"{address_1}",
					"{address_2}",
					"{city}",
					"{postcode}",
					"{zone}",
					"{zone_code}",
					"{country}"
				);

				$replace = array(
					"firstname" => $order_info["shipping_firstname"],
					"lastname"  => $order_info["shipping_lastname"],
					"company"   => $order_info["shipping_company"],
					"address_1" => $order_info["shipping_address_1"],
					"address_2" => $order_info["shipping_address_2"],
					"city"      => $order_info["shipping_city"],
					"postcode"  => $order_info["shipping_postcode"],
					"zone"      => $order_info["shipping_zone"],
					"zone_code" => $order_info["shipping_zone_code"],
					"country"   => $order_info["shipping_country"]
				);

				$shipping_address = str_replace(array("\r\n", "\r", "\n"), "<br />", preg_replace(array("/\s\s+/", "/\r\r+/", "/\n\n+/"), "<br />", trim(str_replace($find, $replace, $format))));

				$this->load->model("tool/upload");

				$product_data = array();

				$products = $this->model_sale_order->getOrderProducts($order_id);

				foreach ($products as $product) {
					$option_data = array();

					$options = $this->model_sale_order->getOrderOptions($order_id, $product["order_product_id"]);

					foreach ($options as $option) {
						if ($option["type"] != "file") {
							$value = $option["value"];
						} else {
							$upload_info = $this->model_tool_upload->getUploadByCode($option["value"]);

							if ($upload_info) {
								$value = $upload_info["name"];
							} else {
								$value = "";
							}
						}

						$option_data[] = array(
							"name"  => $option["name"],
							"value" => $value
						);
					}

					$product_data[] = array(
						"name"     => $product["name"],
						"model"    => $product["model"],
						"option"   => $option_data,
						"quantity" => $product["quantity"],
						"price"    => $this->currency->format($product["price"] + ($this->config->get("config_tax") ? $product["tax"] : 0), $order_info["currency_code"], $order_info["currency_value"]),
						"total"    => $this->currency->format($product["total"] + ($this->config->get("config_tax") ? ($product["tax"] * $product["quantity"]) : 0), $order_info["currency_code"], $order_info["currency_value"])
					);
				}

				$voucher_data = array();

				$vouchers = $this->model_sale_order->getOrderVouchers($order_id);

				foreach ($vouchers as $voucher) {
					$voucher_data[] = array(
						"description" => $voucher["description"],
						"amount"      => $this->currency->format($voucher["amount"], $order_info["currency_code"], $order_info["currency_value"])
					);
				}

				$total_data = array();

				$totals = $this->model_sale_order->getOrderTotals($order_id);

				foreach ($totals as $total) {
					$total_data[] = array(
						"title" => $total["title"],
						"text"  => $this->currency->format($total["value"], $order_info["currency_code"], $order_info["currency_value"])
					);
				}

				$data["orders"][] = array(
					"order_id"	       => $order_id,
					"invoice_no"       => $invoice_no,
					"date_added"       => date($this->language->get("date_format_short"), strtotime($order_info["date_added"])),
					"store_name"       => $order_info["store_name"],
					"store_url"        => rtrim($order_info["store_url"], "/"),
					"store_address"    => nl2br($store_address),
					"store_email"      => $store_email,
					"store_telephone"  => $store_telephone,
					"store_fax"        => $store_fax,
					"email"            => $order_info["email"],
					"telephone"        => $order_info["telephone"],
					"shipping_address" => $shipping_address,
					"shipping_method"  => $order_info["shipping_method"],
					"payment_address"  => $payment_address,
					"payment_method"   => $order_info["payment_method"],
					"product"          => $product_data,
					"voucher"          => $voucher_data,
					"total"            => $total_data,
					"comment"          => nl2br($order_info["comment"])
				);
			}
		}

		$this->response->setOutput($this->load->view("sale/order_invoice", $data));
	}

	public function shipping()
	{
		$this->load->language("sale/order");

		$data["title"] = $this->language->get("text_shipping");

		if ($this->request->server["HTTPS"]) {
			$data["base"] = HTTPS_SERVER;
		} else {
			$data["base"] = HTTP_SERVER;
		}

		$data["direction"] = $this->language->get("direction");
		$data["lang"] = $this->language->get("code");

		$this->load->model("sale/order");

		$this->load->model("catalog/product");

		$this->load->model("setting/setting");

		$data["orders"] = array();

		$orders = array();

		if (isset($this->request->post["selected"])) {
			$orders = $this->request->post["selected"];
		} elseif (isset($this->request->get["order_id"])) {
			$orders[] = $this->request->get["order_id"];
		}

		foreach ($orders as $order_id) {
			$order_info = $this->model_sale_order->getOrder($order_id);

			// Make sure there is a shipping method
			if ($order_info && $order_info["shipping_code"]) {
				$store_info = $this->model_setting_setting->getSetting("config", $order_info["store_id"]);

				if ($store_info) {
					$store_address = $store_info["config_address"];
					$store_email = $store_info["config_email"];
					$store_telephone = $store_info["config_telephone"];
				} else {
					$store_address = $this->config->get("config_address");
					$store_email = $this->config->get("config_email");
					$store_telephone = $this->config->get("config_telephone");
				}

				if ($order_info["invoice_no"]) {
					$invoice_no = $order_info["invoice_prefix"] . $order_info["invoice_no"];
				} else {
					$invoice_no = "";
				}

				if ($order_info["shipping_address_format"]) {
					$format = $order_info["shipping_address_format"];
				} else {
					$format = "{firstname} {lastname}" . "\n" . "{company}" . "\n" . "{address_1}" . "\n" . "{address_2}" . "\n" . "{city} {postcode}" . "\n" . "{zone}" . "\n" . "{country}";
				}

				$find = array(
					"{firstname}",
					"{lastname}",
					"{company}",
					"{address_1}",
					"{address_2}",
					"{city}",
					"{postcode}",
					"{zone}",
					"{zone_code}",
					"{country}"
				);

				$replace = array(
					"firstname" => $order_info["shipping_firstname"],
					"lastname"  => $order_info["shipping_lastname"],
					"company"   => $order_info["shipping_company"],
					"address_1" => $order_info["shipping_address_1"],
					"address_2" => $order_info["shipping_address_2"],
					"city"      => $order_info["shipping_city"],
					"postcode"  => $order_info["shipping_postcode"],
					"zone"      => $order_info["shipping_zone"],
					"zone_code" => $order_info["shipping_zone_code"],
					"country"   => $order_info["shipping_country"]
				);

				$shipping_address = str_replace(array("\r\n", "\r", "\n"), "<br />", preg_replace(array("/\s\s+/", "/\r\r+/", "/\n\n+/"), "<br />", trim(str_replace($find, $replace, $format))));

				$this->load->model("tool/upload");

				$product_data = array();

				$products = $this->model_sale_order->getOrderProducts($order_id);

				foreach ($products as $product) {
					$option_weight = "";

					$product_info = $this->model_catalog_product->getProduct($product["product_id"]);

					if ($product_info) {
						$option_data = array();

						$options = $this->model_sale_order->getOrderOptions($order_id, $product["order_product_id"]);

						foreach ($options as $option) {
							if ($option["type"] != "file") {
								$value = $option["value"];
							} else {
								$upload_info = $this->model_tool_upload->getUploadByCode($option["value"]);

								if ($upload_info) {
									$value = $upload_info["name"];
								} else {
									$value = "";
								}
							}

							$option_data[] = array(
								"name"  => $option["name"],
								"value" => $value
							);

							$product_option_value_info = $this->model_catalog_product->getProductOptionValue($product["product_id"], $option["product_option_value_id"]);

							if ($product_option_value_info) {
								if ($product_option_value_info["weight_prefix"] == "+") {
									$option_weight += $product_option_value_info["weight"];
								} elseif ($product_option_value_info["weight_prefix"] == "-") {
									$option_weight -= $product_option_value_info["weight"];
								}
							}
						}

						$product_data[] = array(
							"name"     => $product_info["name"],
							"model"    => $product_info["model"],
							"option"   => $option_data,
							"quantity" => $product["quantity"],
							"location" => $product_info["location"],
							"sku"      => $product_info["sku"],
							"upc"      => $product_info["upc"],
							"ean"      => $product_info["ean"],
							"jan"      => $product_info["jan"],
							"isbn"     => $product_info["isbn"],
							"mpn"      => $product_info["mpn"],
							"weight"   => $this->weight->format(($product_info["weight"] + (float)$option_weight) * $product["quantity"], $product_info["weight_class_id"], $this->language->get("decimal_point"), $this->language->get("thousand_point"))
						);
					}
				}

				$data["orders"][] = array(
					"order_id"	       => $order_id,
					"invoice_no"       => $invoice_no,
					"date_added"       => date($this->language->get("date_format_short"), strtotime($order_info["date_added"])),
					"store_name"       => $order_info["store_name"],
					"store_url"        => rtrim($order_info["store_url"], "/"),
					"store_address"    => nl2br($store_address),
					"store_email"      => $store_email,
					"store_telephone"  => $store_telephone,
					"email"            => $order_info["email"],
					"telephone"        => $order_info["telephone"],
					"shipping_address" => $shipping_address,
					"shipping_method"  => $order_info["shipping_method"],
					"product"          => $product_data,
					"comment"          => nl2br($order_info["comment"])
				);
			}
		}

		$this->response->setOutput($this->load->view("sale/order_shipping", $data));
	}

	private function getProductNames($order_id)
	{
		$this->load->model("vendor/order");
		$products = $this->model_vendor_order->getOrderProducts($order_id);

		$productNames = [];
		foreach ($products as $product) {
			$productNames[] = $product["name"];
		}
		return implode(", ", $productNames);
	}

	public function fetchOrderData()
	{
		header("Content-Type: application/json");

		if (!isset($this->request->get["order_id"]) || empty($this->request->get["order_id"])) {
			echo json_encode(["error" => "Please select at least one order."]);
			return;
		}

		$order_id = (int)$this->request->get["order_id"];
		$this->load->model("vendor/order");

		$order = $this->model_vendor_order->getOrderDetails($order_id);
		$products = $this->model_vendor_order->getOrderProducts($order_id);
		$warehouse_id   = $this->model_vendor_order->getWarehouseId($order_id);

		$box_length = $products[0]["box_length"] ?? "";
		$box_breadth = $products[0]["box_breadth"] ?? "";
		$box_height = $products[0]["box_height"] ?? "";
// 		$order_weight = $products[0]["order_weight"] ?? "";

        // added on 08-04-2025 ----- related to the higher weight from both actual and volumetric weight
        $order_actual_weight = (float)($products[0]["order_weight"] ?? 0);
		$order_volumetric_weight = (float)($products[0]["order_volumetric_weight"] ?? 0);

        $order_weight = max($order_actual_weight,$order_volumetric_weight);

		$orderData = [
			"order_id" => $order["order_id"] ?? "",
			"carrier_id" => "", // Empty for now
			"warehouse_id" => $warehouse_id, // Empty for now
			"return_warehouse_id" => $warehouse_id, // Empty for now
			"ewaybill" => "", // Empty for now
			"products" => array_map(function ($product) {
				return [
					"product" => $product["name"],
					"price" => $product["price"],
					"product_code" => $product["model"],
					"product_quantity" => $product["quantity"],
					"discount" => "",
					"tax_rate" => "5",
					"tax_title" => "IGST"
				];
			}, $products),
			"discount" => "",
			"shipping" => "0",
			"order_total" => $order["total"] ?? "",
			"gift_card_amt" => "",
			"taxes" => "",
			"payment_type" => $order["payment_status"] ?? "",
			"email" => $order["email"] ?? "",
			"billing_address" => $order["payment_address_1"] ?? "",
			"billing_address2" => $order["payment_address_2"] ?? "",
			"billing_city" => $order["payment_city"] ?? "",
			"billing_state" => $order["payment_zone"] ?? "",
			"billing_country" => $order["payment_country"] ?? "",
			"billing_firstname" => $order["firstname"] ?? "",
			"billing_lastname" => $order["lastname"] ?? "",
			"billing_phone" => $order["telephone"] ?? "",
			"billing_zipcode" => $order["payment_postcode"] ?? "",
			"shipping_address" => $order["shipping_address_1"] ?? "",
			"shipping_address2" => $order["shipping_address_2"] ?? "",
			"shipping_city" => $order["shipping_city"] ?? "",
			"shipping_state" => $order["shipping_zone"] ?? "",
			"shipping_country" => $order["shipping_country"] ?? "",
			"shipping_firstname" => $order["shipping_firstname"] ?? "",
			"shipping_lastname" => $order["shipping_lastname"] ?? "",
			"shipping_phone" => $order["telephone"] ?? "",
			"shipping_zipcode" => $order["shipping_postcode"] ?? "",
			"shipping_latitude" => "",
			"shipping_longitude" => "",
			"box_length" => $box_length,
			"box_breadth" => $box_breadth,
			"box_height" => $box_height,
			"order_weight" => $order_weight,
			"order_date" => $order["date_added"] ?? date("Y-m-d H:i:s")
		];

		echo json_encode($orderData);
	}

	public function sendToShipway()
	{
		header("Content-Type: application/json");

		if ($_SERVER["REQUEST_METHOD"] !== "POST") {
			echo json_encode(["error" => "Invalid request method. Use POST."]);
			return;
		}

		$orderData = json_decode(file_get_contents("php://input"), true);

		if (!$orderData || empty($orderData["order_id"])) {
			echo json_encode(["error" => "Invalid order data."]);
			return;
		}

		$order_id = (int)$orderData["order_id"];
		$this->load->model("vendor/order");

		// Fetch order details from DB
		$order = $this->model_vendor_order->getOrderDetails($order_id);
		$products = $this->model_vendor_order->getOrderProducts($order_id);
		$warehouse_id   = $this->model_vendor_order->getWarehouseId($order_id);

		$box_length = $products[0]["box_length"] ?? "";
		$box_breadth = $products[0]["box_breadth"] ?? "";
		$box_height = $products[0]["box_height"] ?? "";
// 		$order_weight = $products[0]["order_weight"] ?? "";

        // added on 08-04-2025 ----- related to the higher weight from both actual and volumetric weight
        $order_actual_weight = (float)($products[0]["order_weight"] ?? 0);
		$order_volumetric_weight = (float)($products[0]["order_volumetric_weight"] ?? 0);

        $order_weight = max($order_actual_weight,$order_volumetric_weight);

		// Prepare the data for Shipway API
		$payload = [
			"username" => $this->shipway_username,
			"license_key" => $this->shipway_key,
			"order_id" => $order["order_id"] ?? "",
			"carrier_id" => $order["carrier_id"] ?? "",
			"warehouse_id" => $warehouse_id,                                   // $order["warehouse_id"] ??
			"return_warehouse_id" =>  $warehouse_id,                                // $order["return_warehouse_id"] ??
			"ewaybill" =>  "",                                                 // $order["ewaybill"] ??
			"products" => array_map(function ($product) {
				return [
					"product" => $product["name"],
					"price" => $product["price"],
					"product_code" => $product["model"],
					"product_quantity" => $product["quantity"],
					"discount" => "0", // Assuming no discount applied, update if necessary
					"tax_rate" => "5", // Example tax rate, update if available in DB
					"tax_title" => "IGST"
				];
			}, $products),
			"discount" => $order["discount"] ?? "0",
			"shipping" => $order["shipping_cost"] ?? "0",
			"order_total" => $order["total"] ?? "0",
			"gift_card_amt" => $order["gift_card_amt"] ?? "0",
			"taxes" => $order["tax"] ?? "0",
			"payment_type" =>  $order["payment_status"] ?? "",
			"email" => $order["email"] ?? "",
			"billing_address" => $order["payment_address_1"] ?? "",
			"billing_address2" => $order["payment_address_2"] ?? "",
			"billing_city" => $order["payment_city"] ?? "",
			"billing_state" => $order["payment_zone"] ?? "",
			"billing_country" => $order["payment_country"] ?? "",
			"billing_firstname" => $order["firstname"] ?? "",
			"billing_lastname" => $order["lastname"] ?? "",
			"billing_phone" => $order["telephone"] ?? "",
			"billing_zipcode" => $order["payment_postcode"] ?? "",
			"billing_latitude" => $order["billing_latitude"] ?? "",
			"billing_longitude" => $order["billing_longitude"] ?? "",
			"shipping_address" => $order["shipping_address_1"] ?? "",
			"shipping_address2" => $order["shipping_address_2"] ?? "",
			"shipping_city" => $order["shipping_city"] ?? "",
			"shipping_state" => $order["shipping_zone"] ?? "",
			"shipping_country" => $order["shipping_country"] ?? "",
			"shipping_firstname" => $order["shipping_firstname"] ?? "",
			"shipping_lastname" => $order["shipping_lastname"] ?? "",
			"shipping_phone" =>  $order["telephone"] ?? "",
			"shipping_zipcode" => $order["shipping_postcode"] ?? "",
			"shipping_latitude" => $order["shipping_latitude"] ?? "",
			"shipping_longitude" => $order["shipping_longitude"] ?? "",
			"box_length" => $box_length,
			"box_breadth" => $box_breadth,
			"box_height" => $box_height,
			"order_weight" => $order_weight,
			"order_date" => $order["date_added"] ?? date("Y-m-d H:i:s")
		];

		// Convert payload to JSON
		$json_data = json_encode($payload);

		$shipway_username = "ipshopy1@gmail.com";
		$shipway_key = "96V1f01z291K02U1jg35s5Sb93gB4QmY";

		$credentials = base64_encode("$shipway_username:$shipway_key");

		// Send request to Shipway API
		$ch = curl_init($this->shipway_api_url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt(
			$ch,
			CURLOPT_HTTPHEADER,
			[
				"Content-Type: application/json",
				"Authorization: Basic " . $credentials
			]
		);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $json_data);

		$response = curl_exec($ch);

		$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		curl_close($ch);

		// Handle response and download label if successful
		$shipwayResponse = json_decode($response, true);
		
// 		var_dump($shipwayResponse);

		if ($http_code == 200 || (
			(isset($shipwayResponse["success"]) && $shipwayResponse["success"] == 1)
		)) {
			$awb_no = isset($shipwayResponse["awb_response"]["AWB"]) ? $shipwayResponse["awb_response"]["AWB"] : null;
			$label_url = isset($shipwayResponse["awb_response"]["shipping_url"]) ? $shipwayResponse["awb_response"]["shipping_url"] : null;
			$carrier_id = isset($shipwayResponse["awb_response"]["carrier_id"]) ? $shipwayResponse["awb_response"]["carrier_id"] : null;

			if (!$awb_no || !$label_url || !$carrier_id) {
				echo json_encode([
					"error" => "Order booked, but no label available!"
				]);
			} else {
				echo json_encode([
					"success" => true,
					"message" => $shipwayResponse["message"] ?? "Ipshopy Order booked successfully!",
					"awb_no" => $awb_no,
					"label_url" => $label_url,
					"carrier_id" => $carrier_id
				]);
			}
		} else {
			echo json_encode([
				"error" => $shipwayResponse["message"] ?? "Unknown error occurred!",
				// $shipwayResponse["awb_response"]["AWB"],
				// $shipwayResponse["awb_response"]["shipping_url"],
				// $shipwayResponse["awb_response"]["carrier_id"]
			]);
		}
	}

	public function updateShippingLabel()
	{
		$this->load->model('vendor/order');

		$order_id = $this->request->post['order_id'];
		$label_url = $this->request->post['label_url'];
		$awb_no = $this->request->post['awbno'];
		$carrier_id = $this->request->post['carrier_id'];

		if ($order_id && $label_url) {
			$this->model_vendor_order->saveShippingLabel($order_id, $label_url, $awb_no, $carrier_id);
			$json['success'] = 'Shipping label saved successfully.';
		} else {
			$json['error'] = 'Missing order ID or label URL.';
		}

		$this->response->setOutput(json_encode($json));
	}


	public function getUpdatedOrders()
	{
		$json = array();

		$query = $this->db->query("SELECT order_id, order_status_id FROM `" . DB_PREFIX . "order` WHERE order_status_id = '8'");

		if ($query->num_rows) {
			$json = $query->rows;
		} else {
			$json['error'] = "No orders found with status 8.";
		}

		$this->response->setOutput(json_encode($json));
	}


	public function getRTDOrders()
	{
		$json = array();

		// Ensure a valid status ID is set (default to 8 for Label Generated)
		$status_id = isset($this->request->get['status_id']) ? (int)$this->request->get['status_id'] : 8;

		// SQL Query to fetch orders with status_id = 8 (Label Generated)
		$query = $this->db->query("
			SELECT 
				o.order_id,
				SUM(op.quantity) AS total_quantity,
				CONCAT(o.firstname, ' ', o.lastname) AS customer_name,
				o.total,
				o.date_added,
				o.order_status_id AS status
			FROM " . DB_PREFIX . "order o
			JOIN " . DB_PREFIX . "order_product op ON o.order_id = op.order_id
			WHERE o.order_status_id = '8'
			GROUP BY o.order_id, o.firstname, o.lastname, o.total, o.date_added, o.order_status_id
		");

		if ($query->num_rows) {
			$json['orders'] = $query->rows;
		} else {
			$json['error'] = "No orders found with status 8.";
		}

		$this->response->setOutput(json_encode($json));
	}

	public function updateOrderStatusAndSwitch()
	{
		$json = array();

		if (isset($this->request->post['order_id'])) {
			$order_id = (int)$this->request->post['order_id'];
			$order_status_id = 8; // Default Status ID for "Label Generated"
			$notify = 1; // Notify customer and vendor (1 = Yes, 0 = No)

			// 🔹 Status Comments Mapping
			$status_comments = [
				8  => "Label Generated Successfully",
				13 => "Order Ready to Dispatch",
				16 => "Manifest Generated Successfully",
				1  => "Order in Pending State",
				7  => "Order Canceled Successfully",
				17  => "Order Breach After 72 hours"
			];

			// Get the comment based on status ID
			$comment = isset($status_comments[$order_status_id]) ? $status_comments[$order_status_id] : "Order Status Updated";

			try {
				// 1️⃣ Update the order status in oc_order
				$this->db->query("UPDATE " . DB_PREFIX . "order 
							SET order_status_id = '" . (int)$order_status_id . "', 
								date_modified = NOW() 
							WHERE order_id = '" . (int)$order_id . "'");


				// 2️⃣ Insert into oc_order_history so that the update is visible in Admin & Vendor Panel
				$this->db->query(
					"INSERT INTO " . DB_PREFIX . "order_history SET 
					order_id = '" . (int)$order_id . "', 
					order_status_id = '" . (int)$order_status_id . "', 
					notify = '" . (int)$notify . "', 
					comment = '" . $this->db->escape($comment) . "', 
					date_added = NOW()"
				);

				// 3️⃣ Update the status in oc_vendor_order_product (Vendor Order Tracking)
				$this->db->query("UPDATE " . DB_PREFIX . "vendor_order_product 
								SET order_status_id = '" . (int)$order_status_id . "', 
									date_modified = NOW() 
								WHERE order_id = '" . (int)$order_id . "'");

				$vendorQuery = $this->db->query("SELECT vendor_id FROM " . DB_PREFIX . "vendor_order_product WHERE order_id = '" . (int)$order_id . "'");
				$vendorId = $vendorQuery->num_rows ? (int)$vendorQuery->row['vendor_id'] : 0;

				// 4️⃣ Insert into oc_order_vendorhistory (Vendor Order History)
				$this->db->query(
					"INSERT INTO " . DB_PREFIX . "order_vendorhistory SET 
					order_id = '" . (int)$order_id . "', 
					order_status_id = '" . (int)$order_status_id . "', 
					vendor_id = '" . $vendorId . "',
					comment = '" . $this->db->escape($comment) . "', 
					date_added = NOW()"
				);

				// 5️⃣ Check if the update was successful (Prevent Error)
				$query = $this->db->query("SELECT order_status_id FROM " . DB_PREFIX . "order WHERE order_id = '" . (int)$order_id . "'");

				if (is_object($query) && $query->num_rows && (int)$query->row['order_status_id'] === $order_status_id) {
					$json['success'] = true;
					$json['message'] = "Order status updated successfully: " . $comment;
				} else {
					$json['error'] = "Failed to update order status.";
				}
			} catch (Exception $e) {
				$json['error'] = "SQL Error: " . $e->getMessage();
			}
		} else {
			$json['error'] = "Invalid order ID.";
		}

		// Ensure proper JSON output even on error
		header('Content-Type: application/json');
		echo json_encode($json);
		exit();
	}

	//    for caurier rates 
	public function getCourierRates()
	{
		$json = array();

		try {
			if (!isset($this->request->get['order_id'])) {
				throw new Exception("Missing Order ID.");
			}

			$order_id = (int)$this->request->get['order_id'];

			// Load the model
			$this->load->model('vendor/order');

			// Fetch Shipment Data & Courier Rates
			$shipment_data = $this->model_vendor_order->getShipmentData($order_id);
			$courier_rates = $this->model_vendor_order->getShipwayRates($shipment_data);
			$vendorId = isset($shipment_data['vendor_id']) ? (int)$shipment_data['vendor_id'] : 0;

			if ($shipment_data) {
				$json['success'] = true;
				$json['shipment_data'] = $shipment_data;
				$json['courier_rates'] = $courier_rates;

				// Include the generated API request URL for debugging
				$json['api_request_url'] = isset($this->model_vendor_order->last_api_url) ? $this->model_vendor_order->last_api_url : "URL not available";
				// Calculate fowardCharge if rates are available
				if (!empty($courier_rates['rate_card'])) {
					$matchedCarrier = null;

					foreach ($courier_rates['rate_card'] as $rate) {
						if ($rate['carrier_id'] == $this->model_vendor_order->getCourierIdByOrderId($order_id)) {
							$matchedCarrier = $rate;
							break;
						}
					}

					if ($matchedCarrier) {
						$codCharges = (float)$matchedCarrier['cod_charges'];
						$deliveryCharge = (float)$matchedCarrier['delivery_charge'];
						$rtoCharges = (float)$matchedCarrier['rto_charge'];         // added on 20-03-2025
						$orderTotal = (float)$shipment_data['cumulativePrice'];     
						$courierName = $matchedCarrier['courier_name'];            // added on 20-03-2025
						$fowardCharge = ($orderTotal * 0.05) + ($codCharges + $deliveryCharge + (0.18 * ($codCharges + $deliveryCharge)) + ($orderTotal * 1.18 / 100));

						// Save fowardCharge in the database
						$carrier_id = $matchedCarrier['carrier_id'];

                        //  saveCourierRates updated on 20-03-2025

						$this->model_vendor_order->saveCourierRate($order_id, $vendorId, $fowardCharge, $carrier_id, $courierName , $rtoCharges);

						$json['total_charge'] = number_format($fowardCharge, 2);
					} else {
						$json['error'] = "No matching carrier found.";
					}
				}
			} else {
				throw new Exception("No data found for this Order ID.");
			}
		} catch (Exception $e) {
			$json['success'] = false;
			$json['error'] = $e->getMessage();
		}

		// Ensure JSON output
		header('Content-Type: application/json');
		echo json_encode($json);
		exit();
	}

	public function getCourierId()
	{
		$json = array();

		try {
			if (!isset($this->request->get['order_id'])) {
				throw new Exception("Missing Order ID.");
			}

			$order_id = (int)$this->request->get['order_id'];

			// Load the model
			$this->load->model('vendor/order');

			// Fetch courier_id from the model
			$courier_id = $this->model_vendor_order->getCourierIdByOrderId($order_id);

			if ($courier_id !== null) {
				$json['courier_id'] = $courier_id;
			} else {
				throw new Exception("Courier ID not found for this Order ID.");
			}
		} catch (Exception $e) {
			$json['error'] = $e->getMessage();
		}

		// Ensure JSON output
		header('Content-Type: application/json');
		echo json_encode($json);
		exit();
	}

	// for manifest

	# The createManifest function

// 	public function createManifest()
// 	{
// 		// Ensure this is a POST request
// 		if ($this->request->server['REQUEST_METHOD'] !== 'POST') {
// 			$this->response->addHeader('Content-Type: application/json');
// 			$this->response->setOutput(json_encode([
// 				'status' => false,
// 				'message' => 'Invalid request method. Use POST.'
// 			]));
// 			return;
// 		}

// 		// Get raw POST input (order IDs)
// 		$json = file_get_contents('php://input');
// 		$data = json_decode($json, true);

// 		// Check if order IDs are provided
// 		if (!isset($data['order_ids']) || !is_array($data['order_ids'])) {
// 			$this->response->addHeader('Content-Type: application/json');
// 			$this->response->setOutput(json_encode([
// 				'status' => false,
// 				'message' => 'Missing or invalid order IDs.'
// 			]));
// 			return;
// 		}

// 		$order_ids = $data['order_ids'];   // add 06-02-2024


// 		// API credentials
// 		$api_url = 'https://app.shipway.com/api/Createmanifest/';
// 		$username = 'ipshopy1@gmail.com';
// 		$license_key = '96V1f01z291K02U1jg35s5Sb93gB4QmY';

// 		// Prepare API request payload
// 		$payload = json_encode(["order_ids" => $data['order_ids']]);

// 		// Initialize cURL
// 		$ch = curl_init();
// 		curl_setopt($ch, CURLOPT_URL, $api_url);
// 		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
// 		curl_setopt($ch, CURLOPT_HTTPHEADER, [
// 			'Authorization: Basic ' . base64_encode($username . ':' . $license_key),
// 			'Content-Type: application/json'
// 		]);
// 		curl_setopt($ch, CURLOPT_POST, true);
// 		curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);

// 		// Execute API request
// 		$response = curl_exec($ch);
// 		$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
// 		curl_close($ch);

// 		// Parse response
// 		$response_data = json_decode($response, true);

// 		// Debugging logs
// 		file_put_contents(DIR_LOGS . 'shipway_manifest_log.txt', date('Y-m-d H:i:s') . " - Shipway API Response: " . print_r($response_data, true) . "\n", FILE_APPEND);

// 		// Ensure valid JSON response
// 		$this->response->addHeader('Content-Type: application/json');

// 		if ($http_code == 200 && isset($response_data['status']) && $response_data['status'] === true) {
// 			// ✅ FIX: Correct key "manifest_ids" instead of "manifest ids"
// 			$manifest_id = isset($response_data['manifest_ids']) ? $response_data['manifest_ids'] : 'N/A';

// 			// ✅ Update database with manifest ID & set status to "Manifested (ID: 16)"
// 			$this->load->model('vendor/order_report');
// 			foreach ($order_ids as $order_id) {
// 				$this->model_vendor_order_report->assignOrderToManifest($order_id, $manifest_id);
// 				$this->model_vendor_order_report->changeOrderStatus($order_id, 16); // Status 16 = Manifested
// 			}


// 			$this->response->setOutput(json_encode([
// 				'status' => true,
// 				'message' => 'Manifest created successfully.',
// 				'manifest_id' => $manifest_id, // Corrected
// 				'error_response' => $response_data['error_response'] ?? []
// 			]));
// 		} else {
// 			$this->response->setOutput(json_encode([
// 				'status' => false,
// 				'message' => 'Failed to create manifest.',
// 				'error_response' => $response_data['error_respo nse'] ?? 'Unknown error'
// 			]));
// 		}
// 	}


    public function createManifest() {
        // Only allow POST requests
        if ($this->request->server['REQUEST_METHOD'] !== 'POST') {
            $this->response->addHeader('Content-Type: application/json');
            $this->response->setOutput(json_encode([
                'status'  => false,
                'message' => 'Invalid request method. Use POST.'
            ]));
            return;
        }
    
        // Decode incoming JSON
        $data = json_decode(file_get_contents('php://input'), true);
        if (empty($data['order_ids']) || !is_array($data['order_ids'])) {
            $this->response->addHeader('Content-Type: application/json');
            $this->response->setOutput(json_encode([
                'status'  => false,
                'message' => 'Missing or invalid order IDs.'
            ]));
            return;
        }
    
        $order_ids = $data['order_ids'];
    
        // Step 1: Insert a new record into `oc_clickpost_order` table
        $this->db->query("
            INSERT INTO `" . DB_PREFIX . "order` (manifest_date)
            VALUES (NOW())
        ");
        
        // Step 2: Fetch the generated manifest_id
        $manifest_id = $this->db->getLastId();
    
        if (!$manifest_id) {
            // If no manifest_id is generated, return an error
            $this->response->addHeader('Content-Type: application/json');
            $this->response->setOutput(json_encode([
                'status'  => false,
                'message' => 'Error generating manifest ID.'
            ]));
            return;
        }
    
        // Step 3: Map the orders to the generated manifest_id and update the order status to "Manifested" (Status ID: 16)
        $this->load->model('vendor/order_report');
        foreach ($order_ids as $order_id) {
            // Associate each order with the newly generated manifest_id
            $this->model_vendor_order_report->assignOrderToManifest($order_id, $manifest_id);
    
            // Change order status to "Manifested (16)"
            $this->model_vendor_order_report->changeOrderStatus($order_id, 16);
        }
    
        // Return success message with the generated manifest_id
        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode([
            'status'      => true,
            'message'     => 'Manifest created successfully.',
            'manifest_id' => $manifest_id
        ]));
    }

	

	// Include TCPDF

    // updated downloadManifest on 16-02-2025
    
    public function downloadManifest()
	{
		if (!isset($this->request->get['manifest_id'])) {
			die('Manifest ID is required.');
		}

		$manifest_id = (int)$this->request->get['manifest_id'];
		$this->load->model('vendor/order_report');

		// Fetch all orders for the given manifest ID
		$orders = $this->model_vendor_order_report->getManifestOrders($manifest_id);

		if (empty($orders)) {
			die('No orders found for this manifest.');
		}

		// Create a new PDF instance
		$pdf = new TCPDF();
		$pdf->SetCreator(PDF_CREATOR);
		$pdf->SetAuthor('IP Supershoppee Private Limited');
		$pdf->SetTitle('Order Manifest');
		$pdf->SetHeaderData('', 0, 'Order Manifest', 'Manifest ID: ' . $manifest_id);
		$pdf->setHeaderFont(['helvetica', '', 10]);
		$pdf->setFooterFont(['helvetica', '', 8]);
		$pdf->SetDefaultMonospacedFont('courier');
		$pdf->SetMargins(10, 10, 10);
		$pdf->SetAutoPageBreak(TRUE, 15);  // ✅ Ensure page breaks happen correctly
		$pdf->SetFont('helvetica', '', 10);
		$pdf->AddPage(); // ✅ Add the first page

		// ✅ Table Header
		$currentDate = date('M j, Y');
		$html = '</br>';
		$i = 1;
		foreach ($orders as $order) {
			$html .= '<h3 style="text-align: center; margin-bottom: 20px; margin-top: 20px;">' . $order['courier_name'] . ' Order Manifest</h3>';
			$html .= '<p style="font-size: 8px margin:0px; padding:0px;">IP SUPERSHOPPEE PRIVATE LIMITED 
			<span style="margin-left: 2px;">  (Merchant ID: 56871)</span> 
			<span style="margin-left: 10px;">	 (Manifest Id : ' . $manifest_id . ')</span>
			<span style="margin-left: 5px;">  Manifest Date : ' . $currentDate . '</span> 
			<span style="margin-left: 5px;">  Payment Type : ' . $order['payment_code'] . '</span> 
			</p>';
			$html .= '<table border="1" cellpadding="5" cellspacing="0" width="770px" >
		<thead>
			<tr style="background-color:#ddd;">
				<th style="width:18px;">#</th>
				<th style="width:100px;">Customer Info</th>
				<th style="width:185px;">Product Name & SKU (Qty)</th>
				<th style="width:30px;">T. Qty</th>
				<th style="width:55px;">Amount</th>
				<th style="width:60px;">Order Info</th>
				<th style="width:90px;">AWB Barcode</th>
			</tr>
		</thead>
		<tbody style="font-size: 14px;">';


			$html .= '<tr>
						<td style="width:18px; vertical-align: middle; text-align:center;">' . $i++ . '</td>
						<td style="width:100px">' . $order['customer_name'] . '<br>' . $order['customer_address'] . '<br>' . $order['email'] . '</td>
						<td style="width:185px">' . $order['product_name'] . ' (' . $order['quantity'] . ')</td>
						<td style="width:30px; vertical-align: middle; text-align:center; ">' . $order['quantity'] . '</td>
						<td style="width:55px">Rs ' . number_format($order['amount'], 2) . '</td>
						<td style="width:60px">' . $order['order_id'] . '<br>' . $order['order_date'] . '</td>
						 <td style="width:90px">' . $order['awbno'] . '</td>
					  </tr>';
			$html .= '</tbody></table>';
			$html .= '
  	    <div style="display:block; margin-bottom:10px; margin-top:5px ">
        <table>
            <tr >
                <td style="width: 280px; text-align: left;">Merchant Signature:</td>
                <td style="width: 280px; text-align: left;">Courier Signature:</td>
            </tr>
            <tr>
                <td style="width: 280px; text-align: left;">Merchant SPOC Name:</td>
                <td style="width: 280px; text-align: left;">Courier SPOC Name:</td>
            </tr>
        </table>
  	    </div>';
		}
		// ✅ Write the complete HTML to the PDF
		$pdf->writeHTML($html, true, false, true, false, '');

		// ✅ File Naming & Storage
		$filename = 'manifest_' . $manifest_id . '_' . date('Y-m-d_H-i-s') . '.pdf';
		$file_path = DIR_DOWNLOAD . $filename;

		// ✅ Save the Manifest File Securely
		$pdf->Output($file_path, 'F');

		// ✅ Generate a Secure Download URL
		$secure_url = HTTP_SERVER . 'index.php?route=vendor/order_report/secureDownload&manifest_id=' . $manifest_id;

		// ✅ Save URL in the database
		foreach ($orders as $order) {
			$this->model_vendor_order_report->saveManifest($order['order_id'], $manifest_id, $secure_url);
		}

		// ✅ Return JSON Response with Secure Download Link
		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode([
			'status' => true,
			'file_url' => $secure_url
		]));
	}
	
	public function secureDownload()
	{
		if (!isset($this->request->get['manifest_id'])) {
			die('Manifest ID is required.');
		}

		$manifest_id = (int)$this->request->get['manifest_id'];
		$this->load->model('vendor/order_report');

		// ✅ Ensure only logged-in vendors or admins can access
		if (!isset($this->session->data['vendor_id']) && !isset($this->session->data['user_id'])) {
			die('Access Denied: Please log in.');
		}

		// ✅ Get the Manifest File
		$filename = 'manifest_' . $manifest_id . '_';
		$files = glob(DIR_DOWNLOAD . $filename . "*.pdf");

		if (!$files) {
			die('Error: Manifest file not found.');
		}

		$file_path = $files[0]; // Get the first matching file

		// ✅ Force Secure File Download
		header('Content-Description: File Transfer');
		header('Content-Type: application/pdf');
		header('Content-Disposition: attachment; filename="' . basename($file_path) . '"');
		header('Expires: 0');
		header('Cache-Control: must-revalidate');
		header('Pragma: public');
		header('Content-Length: ' . filesize($file_path));
		readfile($file_path);
		exit;
	}
	
	
    // updateBreachedOrders updated on 15-02-2025 
	public function updateBreachedOrders()
	{
	    $vendor_id = $this->vendor->getId();
		$this->load->model('vendor/order_report');
		$affected_orders = $this->model_vendor_order_report->updateBreachedOrders($vendor_id);
		$this->response->setOutput(json_encode(["updated_orders" => $affected_orders]));
	}

	public function getBreachedOrders()
	{
		$this->load->model('vendor/order_report');
		$orders = $this->model_vendor_order_report->getBreachedOrders();
		$this->response->setOutput(json_encode($orders));
	}

	public function getTrackOrders()
	{
		$json = array();

		// Ensure a valid status ID is set (default to 8 for Label Generated)
		$status_id = isset($this->request->get['status_id']) ? (int)$this->request->get['status_id'] : 8;

		// SQL Query to fetch orders with status_id = 8 (Label Generated)
		$query = $this->db->query("
			SELECT 
				o.order_id,
				SUM(op.quantity) AS total_quantity,
				CONCAT(o.firstname, ' ', o.lastname) AS customer_name,
				o.total,
				o.date_added,
				o.order_status_id AS status
			FROM " . DB_PREFIX . "order o
			JOIN " . DB_PREFIX . "order_product op ON o.order_id = op.order_id
			WHERE o.order_status_id >= '16' and o.order_status_id !='17'
			GROUP BY o.order_id, o.firstname, o.lastname, o.total, o.date_added, o.order_status_id
		");

		if ($query->num_rows) {
			$json['orders'] = $query->rows;
		} else {
			$json['error'] = "No orders found.";
		}

		$this->response->setOutput(json_encode($json));
	}

	//Shipway status tracking
	public function triggerShipwayUpdate()
	{
// 		$vendor_id = $this->vendor->getId();
		$this->load->model('vendor/order_report');
		$result = $this->model_vendor_order_report->updateOrderStatusFromShipway();

		// Return JSON response to JavaScript
		$this->response->addHeader('Content-Type: application/json');
		echo json_encode(["message" => $result]);
	}


	//Update the order Status Complete after 192 hours(8 days) on 17-02-2025
	public function updateOrderStatusAfter192Hours() {
		$db = $this->db; // OpenCart database connection
	
		// Get 'Complete' status ID
		$completeStatusQuery = $db->query("SELECT order_status_id FROM " . DB_PREFIX . "order_status WHERE name = 'Complete' LIMIT 1");
		$completeStatusId = (int)$completeStatusQuery->row['order_status_id'];
		
		$ordersQuery = $db->query("
		SELECT o.order_id, o.date_modified, v.vendor_id
		FROM " . DB_PREFIX . "order o
		LEFT JOIN " . DB_PREFIX . "vendor_order_product v ON o.order_id = v.order_id
		WHERE o.order_status_id = 18
		AND TIMESTAMPDIFF(HOUR, o.date_modified, NOW()) >= 192
		");

		if ($ordersQuery->num_rows > 0) {
			foreach ($ordersQuery->rows as $order) {
				$orderId = (int)$order['order_id'];
				$vendorId = isset($order['vendor_id']) ? (int)$order['vendor_id'] : 0;

				// Update order status to 'Complete' in `order` table
				$db->query("
					UPDATE " . DB_PREFIX . "order 
					SET order_status_id = $completeStatusId, date_modified = NOW() 
					WHERE order_id = $orderId
				");
	
				// Add history log for order status update in `order_history`
				$db->query("
					INSERT INTO " . DB_PREFIX . "order_history (order_id, order_status_id, notify, comment, date_added)
					VALUES ($orderId, $completeStatusId, 1, 'Order auto-updated to Complete after 192 hours', NOW())
				");
	
				// 1️⃣ Update `vendor_order_product` with 'Complete' status and date_modified
				$db->query("
					UPDATE " . DB_PREFIX . "vendor_order_product 
					SET order_status_id = $completeStatusId, date_modified = NOW() 
					WHERE order_id = $orderId AND vendor_id = $vendorId
				");
	
				// 2️⃣ Insert a new record into `order_vendorhistory`
				$db->query("
					INSERT INTO " . DB_PREFIX . "order_vendorhistory (order_id, order_status_id, vendor_id, comment, date_added)
					VALUES ($orderId, $completeStatusId, $vendorId, 'Order auto-updated to Complete after 192 hours', NOW())
				");
			}
		}
	}


	public function getManifestData()
	{
		$this->load->model('vendor/order_report');

		$json = [];
		$vendor_id = $this->vendor->getId();

		// Fetch manifest data from the model
		$manifest_data = $this->model_vendor_order_report->fetchManifestData($vendor_id);

		foreach ($manifest_data as $manifest) {
			$json[] = [
				'manifest_id'   => $manifest['manifest_id'],
				'manifest_date' => $manifest['manifest_date'],
				'order_count'   => $manifest['order_count'],
				'manifest_url'  => $manifest['shipping_manifest']
			];
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}
	
    // 	added 17-02-2025 for cancel order after breach 
	public function updateCanceledOrders($vendor_id) {
		$vendor_id = $this->vendor->getId();
        $this->load->model('vendor/order_report');
        $affected_orders = $this->model_vendor_order_report->updateCanceledOrders($vendor_id);
        $this->response->setOutput(json_encode(["updated_orders" => $affected_orders]));
    }
    
    
    // added function to calculate the estimated charges on 22-04-2025---------
   public function calculateEstimatedCharges($order_id)
	{
		$this->load->model("vendor/order");

		$shipment_data = $this->model_vendor_order->getShipmentData($order_id);
		if (!$shipment_data || !isset($shipment_data['TotalWeight'])) {
			return ['error' => 'Invalid shipment data for Order ID: ' . $order_id];
		}
		// var_dump($shipment_data);

		$total_weight = $shipment_data['TotalWeight'];
		$weight_in_kg = $total_weight / 1000;  // Convert weight to kg

		$carrier_data = $this->model_vendor_order->getShipwayRates($shipment_data);

		if (!isset($carrier_data['rate_card']) || !is_array($carrier_data['rate_card'])) {
			return ['error' => 'Rate card not found or invalid for Order ID: ' . $order_id];
		}

		$average_delivery = 0;
		$average_cod_charge = 0;
		$count = 0;
		$order_total = $shipment_data['cumulativePrice'] ?? 0;

		$weight_slabs = [
			[0.001, 0.5],
			[0.501, 1.0],
			[1.001, 2.0],
			[2.001, 3.0],
			[3.001, 5.0],
			[5.001, 10.0],
			[10.001,15.0]
		];

		// var_dump()
		foreach ($carrier_data['rate_card'] as $rate) {
			foreach ($weight_slabs as $slab) {
				if ($weight_in_kg >= $slab[0] && $weight_in_kg <= $slab[1]) {
					if ($rate['charged_weight'] >= $slab[0] && $rate['charged_weight'] <= $slab[1]) {
						$count++;
						$average_delivery += $rate['delivery_charge'];
						$average_cod_charge += $rate['cod_charges'];
					}
				}
			}
		}


		if ($count > 0) {
			$average_delivery /= $count;
			$average_cod_charge /= $count;
		} else {
			$average_delivery = 0;
			$average_cod_charge = 0;
		}

		$average_delivery += $average_delivery * 0.18;
		$average_cod_charge += $average_cod_charge * 0.18;

		$order_total_percentage = $order_total * 0.05;

		$estimated_courier_charges = $average_delivery + $average_cod_charge + $order_total_percentage;

		$net_settlement = $order_total - $estimated_courier_charges;

		
       // nikita added 21-04-2025 10:45
		return [
			'estimated_courier_charges' =>$estimated_courier_charges,
			'net_settlement' =>$net_settlement
		  ];
// 		$this->response->setOutput($this->load->view("vendor/order_report", $data));
	}

    // --------------------------------------------------------------------
}
