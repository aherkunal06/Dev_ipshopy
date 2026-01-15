<?php
class ControllerVendorAllOrder extends Controller
{
  private $error = array();

//   public function index()
//   {
//     if (!$this->vendor->isLogged()) {
//       $this->response->redirect($this->url->link('vendor/login', '', true));
//     }

//     $this->load->language('vendor/all_order');
//     $this->document->setTitle($this->language->get('heading_title'));
//     $this->load->model('vendor/all_order');

//     $this->getList();
//   }

//   protected function getList()
//   {
//     $this->load->model('tool/image');
//     $this->load->model('vendor/all_order');

//     $filter_order_id = isset($this->request->get['filter_order_id']) ? $this->request->get['filter_order_id'] : '';
//     $filter_product_name = isset($this->request->get['filter_product_name']) ? $this->request->get['filter_product_name'] : '';
//     $filter_date_added = isset($this->request->get['filter_date_added']) ? $this->request->get['filter_date_added'] : '';
//     $page = isset($this->request->get['page']) ? (int)$this->request->get['page'] : 1;
    
//     $data['cancel'] = $this->url->link('vendor/dashboard');

//     $url = '';
//     if ($filter_order_id) $url .= '&filter_order_id=' . urlencode($filter_order_id);
//     if ($filter_product_name) $url .= '&filter_product_name=' . urlencode($filter_product_name);
//     if ($filter_date_added) $url .= '&filter_date_added=' . urlencode($filter_date_added);
//     if ($page) $url .= '&page=' . $page;


//     $data['breadcrumbs'] = array(
//       array('text' => $this->language->get('text_home'), 'href' => $this->url->link('common/home', '', true)),
//       array('text' => $this->language->get('heading_title'), 'href' => $this->url->link('vendor/all_order', '', true))
//     );

//     $data['all_order'] = array();

//     $filter_data = array(
//       'filter_order_id' => $filter_order_id,
//       'filter_product_name' => $filter_product_name,
//       'filter_date_added' => $filter_date_added,
//       'start' => ($page - 1) * $this->config->get('config_limit_admin'),
//       'limit' => $this->config->get('config_limit_admin')
//     );

//     $vendor_id = $this->vendor->getId();
//     $order_total = $this->model_vendor_all_order->getTotalAllOrders($vendor_id, $filter_data);
//     $results = $this->model_vendor_all_order->getAllOrders($vendor_id, $filter_data);
    
//     $sr = 1;
//     foreach ($results as $result) {
//       $image = 'no_image.png';
//       if (!empty($result['image']) && is_file(DIR_IMAGE . $result['image'])) {
//         $image = $this->model_tool_image->resize($result['image'], 40, 40);
//       }
//       $data['all_order'][] = array(
//         'sr' => $sr++,
//         'order_id' => $result['order_id'],
//         'image' => $image,
//         'Product' => $result['name'],
//         'quantity' => $result['quantity'],
//         'total' => $this->currency->format($result['total'], $this->config->get('config_currency')),
//         'date_added' => date($this->language->get('date_format_short'), strtotime($result['date_added'])),
//         'status' => $result['status_name'],
//         'view' => $this->url->link('vendor/latestorder/letestview', 'order_id=' . $result['order_id'], true)
//       );
//     }

//     $pagination = new Pagination();
//     $pagination->total = $order_total;
//     $pagination->page = $page;
//     $pagination->limit = $this->config->get('config_limit_admin');
//     $pagination->url = $this->url->link('vendor/all_order', $url . '&page={page}', true);

//     $data['pagination'] = $pagination->render();
//     $data['results'] = sprintf($this->language->get('text_pagination'), ($order_total) ? (($page - 1) * $this->config->get('config_limit_admin')) + 1 : 0, ((($page - 1) * $this->config->get('config_limit_admin')) > ($order_total - $this->config->get('config_limit_admin'))) ? $order_total : ((($page - 1) * $this->config->get('config_limit_admin')) + $this->config->get('config_limit_admin')), $order_total, ceil($order_total / $this->config->get('config_limit_admin')));

//     $data['header'] = $this->load->controller('vendor/header');
//     $data['column_left'] = $this->load->controller('vendor/column_left');
//     $data['footer'] = $this->load->controller('vendor/footer');

//     $this->response->setOutput($this->load->view('vendor/all_order', $data));
//   }

  public function index() {
    if (!$this->vendor->isLogged()) {
      $this->response->redirect($this->url->link('vendor/login', '', true));
    }

    $this->load->language('vendor/all_order');
    $this->document->setTitle($this->language->get('heading_title'));
    $this->load->model('vendor/all_order');

    $this->getList();
  }

  protected function getList()
  {
    $this->load->model('tool/image');
    $this->load->model('vendor/all_order');

    $filter_order_id = isset($this->request->get['filter_order_id']) ? $this->request->get['filter_order_id'] : '';
    $filter_product_name = isset($this->request->get['filter_product_name']) ? $this->request->get['filter_product_name'] : '';
    $filter_date_added = isset($this->request->get['filter_date_added']) ? $this->request->get['filter_date_added'] : '';
    $page = isset($this->request->get['page']) ? (int)$this->request->get['page'] : 1;

    $data['cancel'] = $this->url->link('vendor/dashboard');

    $url = '';
    if ($filter_order_id) $url .= '&filter_order_id=' . urlencode($filter_order_id);
    if ($filter_product_name) $url .= '&filter_product_name=' . urlencode($filter_product_name);
    if ($filter_date_added) $url .= '&filter_date_added=' . urlencode($filter_date_added);
    if ($page) $url .= '&page=' . $page;


    $data['breadcrumbs'] = array(
      array('text' => $this->language->get('text_home'), 'href' => $this->url->link('common/home', '', true)),
      array('text' => $this->language->get('heading_title'), 'href' => $this->url->link('vendor/all_order', '', true))
    );

    $data['all_order'] = array();

    $filter_data = array(
      'filter_order_id' => $filter_order_id,
      'filter_product_name' => $filter_product_name,
      'filter_date_added' => $filter_date_added,
      'start' => ($page - 1) * $this->config->get('config_limit_admin'),
      'limit' => $this->config->get('config_limit_admin')
    );

    // $vendor_id = $this->vendor->getId();
    // $order_total = $this->model_vendor_all_order->getTotalAllOrders($vendor_id, $filter_data);
    // $results = $this->model_vendor_all_order->getAllOrders($vendor_id, $filter_data);

    // ...existing code...
    $vendor_id = $this->vendor->getId();
    $order_total = $this->model_vendor_all_order->getTotalAllOrders($vendor_id, $filter_data);
    $results = $this->model_vendor_all_order->getAllOrders($vendor_id, $filter_data);
    // ...existing code...
    $sr = 1;
    foreach ($results as $result) {
      $image = 'no_image.png';
      if (!empty($result['image']) && is_file(DIR_IMAGE . $result['image'])) {
        $image = $this->model_tool_image->resize($result['image'], 40, 40);
      }
      $data['all_order'][] = array(
        'sr' => $sr++,
        'order_id' => $result['order_id'],
        'image' => $image,
        'Product' => $result['name'],
        'quantity' => $result['quantity'],
        'total' => $this->currency->format($result['total'], $this->config->get('config_currency')),
        'date_added' => date($this->language->get('date_format_short'), strtotime($result['date_added'])),
        'status' => $result['status_name'],
        'view' => $this->url->link('vendor/latestorder/letestview', 'order_id=' . $result['order_id'], true)
      );
    }

    $pagination = new Pagination();
    $pagination->total = $order_total;
    $pagination->page = $page;
    $pagination->limit = $this->config->get('config_limit_admin');
    $pagination->url = $this->url->link('vendor/all_order', $url . '&page={page}', true);

    $data['pagination'] = $pagination->render();
    $data['results'] = sprintf($this->language->get('text_pagination'), ($order_total) ? (($page - 1) * $this->config->get('config_limit_admin')) + 1 : 0, ((($page - 1) * $this->config->get('config_limit_admin')) > ($order_total - $this->config->get('config_limit_admin'))) ? $order_total : ((($page - 1) * $this->config->get('config_limit_admin')) + $this->config->get('config_limit_admin')), $order_total, ceil($order_total / $this->config->get('config_limit_admin')));

    $data['header'] = $this->load->controller('vendor/header');
    $data['column_left'] = $this->load->controller('vendor/column_left');
    $data['footer'] = $this->load->controller('vendor/footer');

    $this->response->setOutput($this->load->view('vendor/all_order', $data));
  }
  
}
