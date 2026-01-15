<?php
class ControllerTrackingProductApprovalHistory extends Controller {
    private $error = array();

    public function index(): void {
        $this->load->language('tracking/product_approval_history');
        $this->document->setTitle($this->language->get('heading_title'));
        $this->load->model('tracking/product_approval_history');

        $this->getList();
    }

    // protected function getList(): void {
    //     $user_token = $this->session->data['user_token'];

    //     $url = '';
    //     if (isset($this->request->get['approved_by'])) {
    //         $url .= '&approved_by=' . urlencode($this->request->get['approved_by']);
    //     }

    //     // Breadcrumbs
    //     $data['breadcrumbs'] = [
    //         [
    //             'text' => $this->language->get('text_home'),
    //             'href' => $this->url->link('common/dashboard', 'user_token=' . $user_token, true)
    //         ],
    //         [
    //             'text' => $this->language->get('heading_title_back'),
    //             'href' => $this->url->link('tracking/product_approval', 'user_token=' . $user_token . $url, true)
    //         ]
    //     ];

    //     // Filter
    //     $approved_by = $this->request->get['approved_by'] ?? null;
    //     $data['approved_by_name'] = $approved_by;

    //     // Get grouped approval records
    //     $approval_rows = $this->model_tracking_product_approval_history->getProductGroupedByApprovedByAndDate($approved_by);

    //     $data['products'] = [];

    //     foreach ($approval_rows as $row) {
    //         $data['products'][] = [
    //             'approved_by'       => $row['approved_by'],
    //             'approved_date'     => $row['approved_date'],
    //             'approved_count'    => $row['approved'],
    //             'disapproved_count' => $row['disapproved'],
    //             'pending_count'     => $row['pending'],
    //             'view'              => $this->url->link(
    //                 'tracking/product_approval_list',
    //                 'user_token=' . $this->session->data['user_token'] .
    //                 '&approved_by=' . urlencode($row['approved_by']) .
    //                 '&approved_date=' . urlencode($row['approved_date']),
    //                 true
    //             )
    //         ];

    //     }

    //     $data['user_token'] = $user_token;

    //     // Load common parts
    //     $data['header'] = $this->load->controller('common/header');
    //     $data['column_left'] = $this->load->controller('common/column_left');
    //     $data['footer'] = $this->load->controller('common/footer');

    //     $this->response->setOutput($this->load->view('tracking/product_approval_history', $data));
    // }
    
    protected function getList(): void {
        $user_token = $this->session->data['user_token'];

        // Get approved_by from URL filter or default to logged-in user
        $approved_by = $this->request->get['approved_by'] ?? null;

        if (!$approved_by) {
            // Optionally default to logged-in username (adjust if your OpenCart version supports this)
            if (isset($this->user)) {
                $approved_by = $this->user->getUserName();
            }
        }

        $url='';

        // Get approved date filter if set
        $approved_date = $this->request->get['filter_date_approved'] ?? null;

        $data['approved_by_name'] = $approved_by;
        $data['filter_date_approved'] = $approved_date;

       // Breadcrumbs
        $data['breadcrumbs'] = [
            [
                'text' => $this->language->get('text_home'),
                'href' => $this->url->link('common/dashboard', 'user_token=' . $user_token, true)
            ],
            [
                'text' => $this->language->get('heading_title_back'),
                'href' => $this->url->link('tracking/product_approval', 'user_token=' . $user_token . $url, true)
            ]
        ];

        // Fetch grouped approval data filtered by approved_by and optionally date
        $approval_rows = $this->model_tracking_product_approval_history->getProductGroupedByApprovedByAndDate($approved_by, $approved_date);

        $data['products'] = [];

        foreach ($approval_rows as $row) {
            $data['products'][] = [
                'approved_by'       => $row['approved_by'],
                'approved_date'     => $row['approved_date'],
                'approved_count'    => $row['approved'],
                'disapproved_count' => $row['disapproved'],
                'pending_count'     => $row['pending'],
                'view'              => $this->url->link(
                    'tracking/product_approval_list',
                    'user_token=' . $user_token .
                    '&approved_by=' . urlencode($row['approved_by']) .
                    '&approved_date=' . urlencode($row['approved_date']),
                    true
                )
            ];
        }

        $data['user_token'] = $user_token;

        // Load common controllers
        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');

        $this->response->setOutput($this->load->view('tracking/product_approval_history', $data));
    }
    
}
