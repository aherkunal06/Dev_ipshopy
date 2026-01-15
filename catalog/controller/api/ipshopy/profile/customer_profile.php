<?php
class ControllerApiIpshopyProfileCustomerProfile extends Controller {
    public function index() {
        try {
            switch ($_SERVER['REQUEST_METHOD']) {
                case 'GET':
                    return $this->getCustomer();
                case 'POST':
                    return $this->createCustomer();
                case 'PUT':
                    return $this->replaceCustomer();
                case 'PATCH':
                    return $this->updateCustomer();
                case 'DELETE':
                    return $this->deleteCustomer();
                default:
                    return $this->errorResponse('Method not allowed', 405);
            }
        } catch (Exception $e) {
            return $this->errorResponse('Internal server error: ' . $e->getMessage(), 500);
        }
    }

    private function getCustomer() {
        if (!$this->validateApiKey()) return $this->unauthorized();

        $customer_id = (int)($this->request->get['customer_id'] ?? 0);
        if (!$customer_id) return $this->errorResponse('Customer ID is required', 400);

        $this->load->model('account/customer');
        $info = $this->model_account_customer->getCustomer($customer_id);

        if (!$info) return $this->errorResponse('Customer not found', 404);

        return $this->successResponse([
            'customer_id' => $info['customer_id'],
            'firstname'   => $info['firstname'],
            'lastname'    => $info['lastname'],
            'email'       => $info['email'],
            'telephone'   => $info['telephone']
        ]);
    }

    private function createCustomer() {
        if (!$this->validateApiKey()) return $this->unauthorized();

        $input = json_decode(file_get_contents('php://input'), true);
        if (!$input) return $this->errorResponse('Invalid JSON input', 400);

        foreach (['firstname', 'lastname', 'email', 'password'] as $field) {
            if (empty($input[$field])) {
                return $this->errorResponse(ucfirst($field) . ' is required', 400);
            }
        }

        $this->load->model('account/customer');

        if ($this->model_account_customer->getTotalCustomersByEmail($input['email'])) {
            return $this->errorResponse('Email already exists', 409);
        }

        $customer_id = $this->model_account_customer->addCustomer([
            'firstname' => trim($input['firstname']),
            'lastname'  => trim($input['lastname']),
            'email'     => trim($input['email']),
            'telephone' => trim($input['telephone'] ?? ''),
            'password'  => $input['password']
        ]);

        return $this->successResponse(['customer_id' => $customer_id], 'Customer created successfully');
    }

    private function replaceCustomer() {
        if (!$this->validateApiKey()) return $this->unauthorized();

        $customer_id = (int)($this->request->get['customer_id'] ?? 0);
        if (!$customer_id) return $this->errorResponse('Customer ID is required', 400);

        $input = json_decode(file_get_contents('php://input'), true);
        foreach (['firstname', 'lastname', 'email', 'password'] as $field) {
            if (empty($input[$field])) {
                return $this->errorResponse("Field '$field' is required for PUT", 400);
            }
        }

        $this->db->query("UPDATE " . DB_PREFIX . "customer SET 
            firstname = '" . $this->db->escape($input['firstname']) . "',
            lastname = '" . $this->db->escape($input['lastname']) . "',
            email = '" . $this->db->escape($input['email']) . "',
            telephone = '" . $this->db->escape($input['telephone'] ?? '') . "',
            password = '" . $this->db->escape(sha1($input['password'])) . "'
            WHERE customer_id = '" . (int)$customer_id . "'");

        return $this->successResponse(null, 'Customer replaced successfully');
    }

    private function updateCustomer() {
        if (!$this->validateApiKey()) return $this->unauthorized();

        $customer_id = (int)($this->request->get['customer_id'] ?? 0);
        if (!$customer_id) return $this->errorResponse('Customer ID is required', 400);

        $input = json_decode(file_get_contents('php://input'), true);
        if (!$input) return $this->errorResponse('Invalid JSON input', 400);

        $set = [];
        foreach (['firstname', 'lastname', 'email', 'telephone', 'password'] as $key) {
            if (isset($input[$key])) {
                $value = ($key === 'password') ? sha1($input[$key]) : $input[$key];
                $set[] = "$key = '" . $this->db->escape($value) . "'";
            }
        }

        if (empty($set)) return $this->errorResponse('No fields to update', 400);

        $this->db->query("UPDATE " . DB_PREFIX . "customer SET " . implode(', ', $set) . " WHERE customer_id = '" . (int)$customer_id . "'");

        return $this->successResponse(null, 'Customer updated successfully');
    }

    private function deleteCustomer() {
        if (!$this->validateApiKey()) return $this->unauthorized();

        $customer_id = (int)($this->request->get['customer_id'] ?? 0);
        if (!$customer_id) return $this->errorResponse('Customer ID is required', 400);

        $this->db->query("DELETE FROM " . DB_PREFIX . "customer WHERE customer_id = '" . (int)$customer_id . "'");

        return $this->successResponse(null, 'Customer deleted successfully');
    }

    // ✅ API Key check
    private function validateApiKey() {
        $expected_key = 'n2h7tnpirooeFF8hPjumpyZdT45ydNGZawAmyx6ktIplZQ3cmXDKjbCQn1cgfLbobag5Kx6MWUeNXyqL40w2KYsowpxlSifgFB2twx2cDjWTBBVcOGdqebBfVAdu667IUhB1UcQtRby4Qil2P5sTIIPglvHjJwE0sATl8W6g2NSGTCAigGdzRfGqwTEr8TgWbGBHPUeoq1cwligMVEyOJdsuOwCi16BKPEzWulTSySZD2ZDB14J0FneHe7c2NN3d';
        $received_key = $this->request->get['api_key'] ?? '';
        return hash_equals($expected_key, $received_key);
    }

    // ✅ Success response
    private function successResponse($data = null, $message = 'Success') {
        $response = ['success' => true, 'message' => $message];
        if ($data !== null) {
            $response['data'] = $data;
        }
        return $this->sendResponse($response, 200);
    }

    // ✅ Error response
    private function errorResponse($message, $status = 400) {
        return $this->sendResponse([
            'success' => false,
            'error' => $message
        ], $status);
    }

    // ✅ 401 helper
    private function unauthorized() {
        return $this->errorResponse('Unauthorized access', 401);
    }

    // ✅ Final output function
    private function sendResponse($data, $statusCode = 200) {
        $this->response->addHeader('Content-Type: application/json');
        $this->response->addHeader('HTTP/1.1 ' . $statusCode);
        $this->response->setOutput(json_encode($data));
    }
}
