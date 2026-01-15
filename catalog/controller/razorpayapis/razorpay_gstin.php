<?php
class ControllerRazorpayapisRazorpaygstin extends Controller
{

    public function index()
    {
        $this->load->language('razorpayapis/razorpay_gstin');
        $this->load->model('razorpayapis/razorpay_gstin');

        $data = [];

        if (isset($this->request->get['gstin_no'])) {
            $gstin_no = $this->request->get['gstin_no'];
            $gstin_info = $this->model_razorpayapis_razorpay_gstin->getGstinDetails($gstin_no);

            $data['gstin_info'] = $gstin_info ?: ['error' => 'Invalid GSTIN or no data found.'];

            // Always return JSON response if 'gstin_no' is provided
            $this->response->addHeader('Content-Type: application/json');
            $this->response->setOutput(json_encode($data, JSON_PRETTY_PRINT));
            return;
        }

        // If no 'gstin_no' is provided, load the default view
        $this->response->setOutput($this->load->view('vendor/vendor', $data));
    }
}
?>