<?php

require_once __DIR__ . '/../../../../system/library/razorpay/razorpay-sdk/Razorpay.php';
require_once __DIR__ . '/../../../../system/library/razorpay/razorpay-lib/createwebhook.php';

use Razorpay\Api\Api;
use Razorpay\Api\Errors;

class ControllerExtensionPaymentRazorpay extends Controller
{
    /**
     * Event constants
     */
    const PAYMENT_AUTHORIZED        = 'payment.authorized';
    const PAYMENT_FAILED            = 'payment.failed';
    const ORDER_PAID                = 'order.paid';
    const WEBHOOK_URL               = HTTPS_SERVER . 'index.php?route=extension/payment/razorpay/webhook';
    const SUBSCRIPTION_PAUSED       = 'subscription.paused';
    const SUBSCRIPTION_RESUMED      = 'subscription.resumed';
    const SUBSCRIPTION_CANCELLED    = 'subscription.cancelled';
    const SUBSCRIPTION_CHARGED      = 'subscription.charged';
    const WEBHOOK_WAIT_TIME         = 120;
    const HTTP_CONFLICT_STATUS      = 409;
    const CURRENCY_NOT_ALLOWED  = [
        'KWD',
        'OMR',
        'BHD',
    ];

    // Set RZP plugin version
    private $version = '5.1.7';

    private $api;

    public function __construct($registry)
    {
        parent::__construct($registry);
        $this->api = $this->getApiIntance();
    }

    /**
     * Initialize Razorpay popup
     * This method is called via AJAX from the checkout page
     */
    public function initializePopup()
    {
        // Set header first to ensure no output before JSON
        $this->response->addHeader('Content-Type: application/json');

        $this->load->model('checkout/order');
        $this->load->model('extension/payment/razorpay');

        $json = array();
        $parent_order_id = null;
        $razorpay_order_id = '';
        $order_id = null;
        $order_info = null;

        // Debug session data
        $this->log->write('Razorpay: Session data: ' . json_encode($this->session->data));

        // First check if we have a parent order ID
        if (isset($this->session->data['parent_order_id'])) {
            $parent_order_id = $this->session->data['parent_order_id'];
            $this->log->write('Razorpay: Found parent_order_id in session: ' . $parent_order_id);

            // Get parent order data
            $parent_order = $this->model_checkout_order->getOrderParent($parent_order_id);
            $this->log->write('Razorpay: Parent order data: ' . json_encode($parent_order));

            if ($parent_order && isset($parent_order['total'])) {
                $this->log->write('Razorpay: Using parent order ID: ' . $parent_order_id);

                // Get the first order ID from the parent order's order_ids
                $order_ids = json_decode($parent_order['order_ids'], true);
                if (!empty($order_ids) && is_array($order_ids)) {
                    $order_id = reset($order_ids);
                    $order_info = $this->model_checkout_order->getOrder($order_id);
                    $this->log->write('Razorpay: Using first child order ID: ' . $order_id);
                }

                // Check if we have a Razorpay order ID for this parent order
                $razorpay_order_id = isset($this->session->data["razorpay_parent_order_id_" . $parent_order_id]) ?
                    $this->session->data["razorpay_parent_order_id_" . $parent_order_id] : '';
                $this->log->write('Razorpay: Existing parent Razorpay order ID: ' . $razorpay_order_id);

                // If no Razorpay order ID exists for the parent order, create one
                if (empty($razorpay_order_id) && $order_info) {
                    // Create a Razorpay order for the parent order
                    $is_recurring = $this->cart->hasRecurringProducts() && $this->config->get('payment_razorpay_subscription_status');

                    // Format the amount properly
                    $formatted_amount = $this->currency->format($parent_order['total'], $order_info['currency_code'], $order_info['currency_value'], false);
                    $numeric_amount = (float)preg_replace('/[^0-9.]/', '', $formatted_amount);
                    $amount_in_smallest_unit = (int)round($numeric_amount * 100);
                    $this->log->write('Razorpay: Parent order amount: ' . $parent_order['total'] . ', formatted: ' . $formatted_amount . ', in smallest unit: ' . $amount_in_smallest_unit);

                    // Create the Razorpay order
                    $receipt = 'parent_' . $parent_order_id;
                    $razorpay_args = array(
                        'receipt'         => $receipt,
                        'amount'          => $amount_in_smallest_unit,
                        'currency'        => $order_info['currency_code'],
                        'payment_capture' => 1
                    );

                    try {
                        $api = $this->getRazorpayApiInstance();
                        $razorpay_order = $api->order->create($razorpay_args);
                        $razorpay_order_id = $razorpay_order['id'];

                        // Store the Razorpay order ID in the session
                        $this->session->data["razorpay_parent_order_id_" . $parent_order_id] = $razorpay_order_id;
                        $this->log->write("Created Razorpay order ID: " . $razorpay_order_id . " for parent order ID: " . $parent_order_id);
                    } catch (Exception $e) {
                        $this->log->write("Error creating Razorpay order for parent order: " . $e->getMessage());
                        $json['success'] = false;
                        $json['error'] = 'Error creating payment order. Please try again.';
                        $this->response->addHeader('Content-Type: application/json');
                        $this->response->setOutput(json_encode($json));
                        return;
                    }
                }

                if ($order_info) {
                    // Add necessary data for the popup using parent order total
                    $json['success'] = true;
                    $json['razorpay'] = true;
                    $json['key_id'] = $this->config->get('payment_razorpay_key_id');
                    $json['order_id'] = $razorpay_order_id;
                    $json['amount'] = round($this->currency->format($parent_order['total'], $order_info['currency_code'], $order_info['currency_value'], false) * 100);
                    $json['currency'] = $order_info['currency_code'];
                    // Use the store name from Razorpay dashboard instead of the hardcoded store name
                    $json['name'] = '';
                    $json['description'] = 'Parent Order #' . $parent_order_id;
                    $json['merchant_order_id'] = $parent_order_id; // Use parent_order_id instead of order_id
                    $json['is_parent_order'] = 'true';
                    $json['is_recurring'] = ($this->cart->hasRecurringProducts() && $this->config->get('payment_razorpay_subscription_status')) ? 'true' : 'false';
                    $json['return_url'] = $this->url->link('extension/payment/razorpay/callback', '', true);

                    // Add customer details if available
                    if (isset($order_info['email']) && isset($order_info['telephone'])) {
                        $json['prefill'] = array(
                            'name' => $order_info['firstname'] . ' ' . $order_info['lastname'],
                            'email' => $order_info['email'],
                            'contact' => $order_info['telephone']
                        );
                    }
                } else {
                    $json['success'] = false;
                    $json['error'] = 'Order information not found';
                    $this->log->write('Razorpay: Child order information not found for parent order ID: ' . $parent_order_id);
                }
            } else {
                $json['success'] = false;
                $json['error'] = 'Parent order information not found';
                $this->log->write('Razorpay: Parent order data not found or total missing for ID: ' . $parent_order_id);
            }
        }
        // Fallback to regular order processing if no parent order
        else if (isset($this->session->data['order_id'])) {
            $order_id = $this->session->data['order_id'];

            // Check if order_id is an array (split orders)
            if (is_array($order_id)) {
                // Use the first order ID from the array
                $order_id = reset($order_id);
                $this->log->write('Razorpay: Using first order ID from split orders: ' . $order_id);
            }

            $order_info = $this->model_checkout_order->getOrder($order_id);

            if ($order_info) {
                // Get Razorpay order ID directly instead of calling index()
                $is_recurring = $this->cart->hasRecurringProducts() && $this->config->get('payment_razorpay_subscription_status');

                if ($is_recurring) {
                    // Get subscription order ID
                    $razorpay_order_id = isset($this->session->data["razorpay_subscription_order_id_" . $order_id]) ?
                        $this->session->data["razorpay_subscription_order_id_" . $order_id] : '';
                } else {
                    // Get regular order ID
                    $razorpay_order_id = isset($this->session->data["razorpay_order_id_" . $order_id]) ?
                        $this->session->data["razorpay_order_id_" . $order_id] : '';
                }

                // Add necessary data for the popup
                $json['success'] = true;
                $json['razorpay'] = true;
                $json['key_id'] = $this->config->get('payment_razorpay_key_id');
                $json['order_id'] = $razorpay_order_id;
                $json['amount'] = round($this->currency->format($order_info['total'], $order_info['currency_code'], $order_info['currency_value'], false) * 100);
                $json['currency'] = $order_info['currency_code'];
                // Use the store name from Razorpay dashboard instead of the hardcoded store name
                $json['name'] = '';
                $json['description'] = 'Order #' . $order_id;
                $json['merchant_order_id'] = $order_id;
                $json['is_recurring'] = $is_recurring ? 'true' : 'false';
                $json['return_url'] = $this->url->link('extension/payment/razorpay/callback', '', true);

                // Customer information
                $json['prefill'] = array(
                    'name' => $order_info['payment_firstname'] . ' ' . $order_info['payment_lastname'],
                    'email' => $order_info['email'],
                    'contact' => $order_info['telephone']
                );
            } else {
                $json['success'] = false;
                $json['error'] = 'Order information not found';
            }
        } else {
            $json['success'] = false;
            $json['error'] = 'No order ID in session';
        }

        // Output the JSON response
        $this->response->setOutput(json_encode($json));
    }

    public function index()
    {
        $data['button_confirm'] = $this->language->get('button_confirm');
        $data['is_recurring'] = "false";

        $this->load->model('checkout/order');
        $this->load->model('extension/payment/razorpay');

        $order_info = $this->model_checkout_order->getOrder($this->session->data['order_id']);

        if (in_array($order_info['currency_code'],  self::CURRENCY_NOT_ALLOWED) === true) {
            $this->log->write("Order creation failed, because currency (" . $order_info['currency_code'] . ") not supported");
            echo "<div class='alert alert-danger alert-dismissible'>Order creation failed, because currency (" . $order_info['currency_code'] . ") not supported.</div>";
            exit;
        }

        // Add JavaScript to handle Razorpay popup initialization
        $this->document->addScript('https://checkout.razorpay.com/v1/checkout.js');

        try {
            if (
                $this->cart->hasRecurringProducts() and
                $this->config->get('payment_razorpay_subscription_status')
            ) {
                //validate for non-subscription product and if recurring is product for more than 1
                $this->validate_non_recurring_products();

                if ($this->cart->hasRecurringProducts() > 1) {
                    $this->log->write("Cart has more than 1 recurring product");
                    echo "<div class='alert alert-danger alert-dismissible'>We do not support payment of two different subscription products at once. Please remove one of the products from your cart to proceed.</div>";
                    exit;
                }

                $subscriptionData = $this->get_subscription_order_creation_data($this->session->data['order_id']);

                if (empty($this->session->data["razorpay_subscription_id_" . $this->session->data['order_id']]) === true) {
                    $subscription_order = $this->api->subscription->create($subscriptionData['subscriptionData'])->toArray();

                    // Save subscription details to DB
                    $this->model_extension_payment_razorpay->saveSubscriptionDetails($subscription_order, $subscriptionData["planData"], $subscriptionData['subscriptionData']['customer_id'], $this->session->data['order_id']);

                    $this->session->data["razorpay_subscription_order_id_" . $this->session->data['order_id']] = $subscription_order['id'];
                    $data['razorpay_order_id'] = $this->session->data["razorpay_subscription_order_id_" . $this->session->data['order_id']];
                    $data['is_recurring'] = "true";
                    $recurring_description = "Recurring order ";
                    $cartDetails = $this->cart->getProducts();

                    $recurringData = [
                        "order_id" => $this->session->data['order_id'],
                        "product_id" => $cartDetails[0]["product_id"],
                        "product_name" => $cartDetails[0]["name"],
                        "product_quantity" => $cartDetails[0]["quantity"],
                        "recurring_id" => $cartDetails[0]["recurring"]["recurring_id"],
                        "recurring_name" => $cartDetails[0]["recurring"]["name"],
                        "recurring_description" => $cartDetails[0]["recurring"]["frequency"] . "ly recurring with SubscriptionId " . $subscription_order['id'],
                        "recurring_frequency" => $cartDetails[0]["recurring"]["frequency"] . "ly",
                        "recurring_cycle" => $cartDetails[0]["recurring"]["cycle"],
                        "recurring_duration" => $cartDetails[0]["recurring"]["duration"],
                        "recurring_price" => $cartDetails[0]["recurring"]["price"],
                        "trial" => $cartDetails[0]["recurring"]["trial"],
                        "trial_frequency" => $cartDetails[0]["recurring"]["trial_frequency"],
                        "trial_cycle" => $cartDetails[0]["recurring"]["trial_cycle"],
                        "trial_duration" => $cartDetails[0]["recurring"]["trial_duration"],
                        "trial_price" => $cartDetails[0]["recurring"]["trial_price"],
                        "reference" => "Subscription Id " . $subscription_order['id']
                    ];

                    $this->model_extension_payment_razorpay->createOCRecurring($recurringData);

                    $this->log->write("RZP subscriptionID (:" . $subscription_order['id'] . ") created for Opencart OrderID (:" . $this->session->data['order_id'] . ")");
                }
            } else {
                // Orders API with payment autocapture
                $order_data = $this->get_order_creation_data($this->session->data['order_id']);

                if (isset($this->session->data["razorpay_order_amount"]) === false) {
                    $this->session->data["razorpay_order_amount"] = 0;
                }

                if ((isset($this->session->data["razorpay_order_id_" . $this->session->data['order_id']]) === false) or
                    ((isset($this->session->data["razorpay_order_id_" . $this->session->data['order_id']]) === true) and
                        (($this->session->data["razorpay_order_amount"] === 0) or
                            ($this->session->data["razorpay_order_amount"] !== $order_data["amount"])))
                ) {
                    $razorpay_order = $this->api->order->create($order_data);

                    $this->session->data["razorpay_order_amount"] = $order_data["amount"];
                    $this->session->data["razorpay_order_id_" . $this->session->data['order_id']] = $razorpay_order['id'];
                    $data['razorpay_order_id'] = $this->session->data["razorpay_order_id_" . $this->session->data['order_id']];
                    $this->model_extension_payment_razorpay->addOrderForWebhook($this->session->data['order_id'], $razorpay_order['id'], 0);

                    $this->log->write("RZP orderID (:" . $razorpay_order['id'] . ") created for Opencart OrderID (:" . $this->session->data['order_id'] . ")");
                }
            }
        } catch (\Razorpay\Api\Errors\Error $e) {
            $this->log->write($e->getMessage());
            $this->session->data['error'] = $e->getMessage();
            echo "<div class='alert alert-danger alert-dismissible'> Something went wrong. Unable to create Razorpay Order Id.</div>";
            exit;
        }

        try {
            $webhookUpdatedAt = $this->config->get('payment_razorpay_webhook_updated_at');

            if ($webhookUpdatedAt + 86400 < time()) {
                $createWebhook = new CreateWebhook(
                    $this->config->get('payment_razorpay_key_id'),
                    $this->config->get('payment_razorpay_key_secret'),
                    $this->config->get('payment_razorpay_webhook_secret'),
                    self::WEBHOOK_URL,
                    $this->config->get('payment_razorpay_subscription_status')
                );

                $webhookConfigData = $createWebhook->autoCreateWebhook();

                $this->load->model('extension/payment/razorpay');
                $this->model_extension_payment_razorpay->editSetting('payment_razorpay', $webhookConfigData);
            }
        } catch (\Razorpay\Api\Errors\Error $e) {
            $this->log->write('Unable to update webhook status');
            $this->log->write($e->getMessage());
        }

        $data['key_id'] = $this->config->get('payment_razorpay_key_id');
        $data['currency_code'] = $order_info['currency_code'];

        // Calculate and format the total amount properly
        $original_amount = $order_info['total'];
        $parent_order_id = null;

        // Always prioritize parent order data if available
        if (isset($this->session->data['parent_order_id'])) {
            $parent_order = $this->model_checkout_order->getOrderParent($this->session->data['parent_order_id']);

            if ($parent_order && isset($parent_order['total'])) {
                $original_amount = $parent_order['total'];
                $parent_order_id = $this->session->data['parent_order_id'];
                $this->log->write("Using parent order total: " . $original_amount . " for parent order ID: " . $parent_order_id . " (in template data)");
            }
        }

        $formatted_amount = $this->currency->format($original_amount, $order_info['currency_code'], $order_info['currency_value'], false);
        $numeric_amount = (float)preg_replace('/[^0-9.]/', '', $formatted_amount);

        // Convert to smallest currency unit (paise/cents) and ensure it's an integer
        $amount_in_smallest_unit = (int)round($numeric_amount * 100);

        // Enhanced logging for amount calculation
        $this->log->write("Razorpay template data - amount calculation: Original = " . $original_amount . ", Formatted = " . $formatted_amount . ", Numeric = " . $numeric_amount . ", In smallest unit = " . $amount_in_smallest_unit);

        // Ensure amount is at least 100 (1 INR in paise) for Razorpay minimum requirement
        if ($amount_in_smallest_unit < 100 && $order_info['currency_code'] == 'INR') {
            $this->log->write("Warning: Amount was below Razorpay minimum. Setting to minimum 100 paise (1 INR)");
            $amount_in_smallest_unit = 100; // Minimum 1 INR
        }

        // Store the amount in session for verification
        $this->session->data['razorpay_amount'] = $amount_in_smallest_unit;

        $data['total'] = $amount_in_smallest_unit;
        $data['merchant_order_id'] = $this->session->data['order_id'];
        $data['card_holder_name'] = $order_info['payment_firstname'] . ' ' . $order_info['payment_lastname'];
        $data['email'] = $order_info['email'];
        $data['phone'] = $order_info['telephone'];
        $data['name'] = $this->config->get('config_name');
        $data['lang'] = $this->session->data['language'];
        $data['return_url'] = $this->url->link('extension/payment/razorpay/callback', '', 'true');
        $data['version'] = $this->version;
        $data['oc_version'] = VERSION;

        //verify if 'hosted' checkout required and set related data
        $this->getMerchantPreferences($data);

        $data['api_url']    = $this->api->getBaseUrl();
        $data['cancel_url'] =  $this->url->link('checkout/checkout', '', 'true');

        header('Set-Cookie: ' . $this->config->get('session_name') . '=' . $this->session->getId() . '; HttpOnly; SameSite=None; Secure; HttpOnly;');

        if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/extension/payment/razorpay')) {
            return $this->load->view($this->config->get('config_template') . '/template/extension/payment/razorpay', $data);
        } else {
            return $this->load->view('extension/payment/razorpay', $data);
        }
    }

    private function get_order_creation_data($order_id)
    {
        $order = $this->model_checkout_order->getOrder($this->session->data['order_id']);

        // Ensure currency is not blank and properly formatted
        $currency_code = !empty($order['currency_code']) ? $order['currency_code'] : 'INR';

        // Always prioritize parent order data if available
        $original_amount = $order['total'];
        $parent_order_id = null;

        // Try to get the parent order total if available - ALWAYS use parent order total when available
        if (isset($this->session->data['parent_order_id'])) {
            $this->load->model('checkout/order');
            $parent_order = $this->model_checkout_order->getOrderParent($this->session->data['parent_order_id']);

            if ($parent_order && isset($parent_order['total'])) {
                // ALWAYS use the parent order total from the database, not from session
                $original_amount = $parent_order['total'];
                $parent_order_id = $this->session->data['parent_order_id'];
                $this->log->write("Using parent order total from database: " . $original_amount . " for parent order ID: " . $parent_order_id);
            } else {
                $this->log->write("Warning: Parent order ID exists in session but could not retrieve parent order data from database");
            }
        } else {
            $this->log->write("No parent order ID in session, using regular order total: " . $original_amount);
        }

        $formatted_amount = $this->currency->format($original_amount, $currency_code, $order['currency_value'], false);

        // Extract only the numeric value from the formatted amount
        $numeric_amount = (float)preg_replace('/[^0-9.]/', '', $formatted_amount);

        // Convert to smallest currency unit (paise/cents) and ensure it's an integer
        $amount_in_smallest_unit = (int)round($numeric_amount * 100);

        // Enhanced logging for amount calculation
        $this->log->write("Razorpay amount calculation: Original amount = " . $original_amount . ", Formatted amount = " . $formatted_amount . ", Numeric extracted = " . $numeric_amount . ", Amount in smallest unit = " . $amount_in_smallest_unit);

        // Check if amount is less than minimum required (100 paise = 1 INR)
        // Razorpay has a minimum transaction amount requirement
        if ($amount_in_smallest_unit < 100 && $currency_code == 'INR') {
            $this->log->write("Order amount is less than minimum amount allowed by Razorpay: " . ($amount_in_smallest_unit / 100) . " " . $currency_code . ". Setting to minimum 100 paise (1 INR).");
            $amount_in_smallest_unit = 100; // Minimum 1 INR in paise
        }

        // Use parent_order_id as receipt if available, otherwise use order_id
        $receipt = $parent_order_id ? 'parent_' . $parent_order_id : (string)$order_id;

        $data = [
            'receipt' => $receipt, // Use parent order ID if available
            'amount' => (int)$amount_in_smallest_unit, // Ensure amount is an integer
            'currency' => $currency_code,
            'payment_capture' => ($this->config->get('payment_razorpay_payment_action') === 'authorize') ? 0 : 1
        ];

        // Store the amount in session for verification
        $this->session->data['razorpay_amount'] = $amount_in_smallest_unit;

        // Log the order creation data for debugging
        $this->log->write("Razorpay order creation data: " . json_encode($data));

        return $data;
    }

    public function validate_non_recurring_products()
    {
        $nonRecurringProduct = array_filter($this->cart->getProducts(), function ($product) {
            return array_filter($product, function ($value, $key) {
                return $key == "recurring" and empty($value);
            }, ARRAY_FILTER_USE_BOTH);
        });

        if (!empty($nonRecurringProduct)) {
            $this->log->write("Cart has recurring product and non recurring product");
            echo "<div class='alert alert-danger alert-dismissible'>You have a one-time payment product and a subscription payment product in your cart. Please remove one of the products from the cart to proceed.</div>";
            exit;
        }
    }

    private function get_subscription_order_creation_data($order_id)
    {
        $this->load->model('extension/payment/razorpay');

        $order = $this->model_checkout_order->getOrder($order_id);
        $recurringPlanData = $this->cart->getProducts()[0]["recurring"];
        $productId = $this->cart->getProducts()[0]['product_id'];

        $planData = $this->model_extension_payment_razorpay->getPlanByRecurringIdAndFrequencyAndProductId($recurringPlanData['recurring_id'], $recurringPlanData['frequency'], $productId);

        $subscriptionData = [
            "customer_id" => $this->getRazorpayCustomerData($order),
            "plan_id" => $planData['plan_id'],
            "total_count" => $planData['plan_bill_cycle'],
            "quantity" => $this->cart->getProducts()[0]['quantity'],
            "customer_notify" => 0,
            "notes" => [
                "source" => "opencart-subscription",
                "merchant_order_id" => $order_id,
            ],
            "source" => "opencart-subscription",
        ];

        if ($planData['plan_trial']) {
            $subscriptionData["start_at"] = strtotime("+{$planData['plan_trial']} days");
        }

        if ($planData['plan_addons']) {
            $item["item"] = [
                "name" => "Addon amount",
                "amount" => (int)(number_format($planData["plan_addons"] * 100, 0, ".", "")),
                "currency" => $this->session->data['currency'],
                "description" => "Addon amount"
            ];
            $subscriptionData["addons"][] = $item;
        }

        return ["subscriptionData" => $subscriptionData, "planData" => $planData];
    }

    // public function callback()
    // {
    //     $this->load->model('checkout/order');
    //     $this->load->model('extension/payment/razorpay');
    //     // nikita added changes 
    //     if (isset($this->session->data['order_id']) && is_array($this->session->data['order_id'])) {
    //         $this->log->write('Order ID is array. Will extract first value.');
    //     }
    //     // nikita added changes end

    //     if (isset($this->request->request['razorpay_payment_id']) === true) {
    //         $razorpay_payment_id = $this->request->request['razorpay_payment_id'];
    //         $razorpay_signature = $this->request->request['razorpay_signature'];
    //         // $merchant_order_id = $this->session->data['order_id'];
    //         // nikita added changes
    //         $merchant_order_id = $this->session->data['order_id'];

    //         if (is_array($merchant_order_id)) {
    //             $merchant_order_id = reset($merchant_order_id); // Get first value
    //         }

    //         $isSubscriptionCallBack = false;

    //         if (array_key_exists("razorpay_subscription_order_id_" . $this->session->data['order_id'], $this->session->data)) {
    //             $razorpay_subscription_id = $this->session->data["razorpay_subscription_order_id_" . $this->session->data['order_id']];
    //             $isSubscriptionCallBack = true;

    //             $attributes = array(
    //                 'razorpay_subscription_id' => $razorpay_subscription_id,
    //                 'razorpay_payment_id' => $razorpay_payment_id,
    //                 'razorpay_signature' => $razorpay_signature
    //             );
    //         } else {
    //             $razorpay_order_id = $this->session->data["razorpay_order_id_" . $this->session->data['order_id']];
    //             $attributes = array(
    //                 'razorpay_order_id' => $razorpay_order_id,
    //                 'razorpay_payment_id' => $razorpay_payment_id,
    //                 'razorpay_signature' => $razorpay_signature
    //             );
    //         }

    //         $order_info = $this->model_checkout_order->getOrder($merchant_order_id);

    //         // Get the correct amount from parent order if available
    //         $original_amount = $order_info['total'];
    //         $is_parent_order = false;
    //         $parent_order_id = null;

    //         if (isset($this->session->data['parent_order_id'])) {
    //             $parent_order_id = $this->session->data['parent_order_id'];
    //             $parent_order = $this->model_checkout_order->getOrderParent($parent_order_id);

    //             if ($parent_order && isset($parent_order['total'])) {
    //                 $original_amount = $parent_order['total'];
    //                 $is_parent_order = true;
    //                 $this->log->write("Callback: Using parent order total: " . $original_amount . " for parent order ID: " . $parent_order_id);

    //                 // Check if we're using a Razorpay order ID for the parent order
    //                 if (isset($this->session->data["razorpay_parent_order_id_" . $parent_order_id])) {
    //                     $razorpay_order_id = $this->session->data["razorpay_parent_order_id_" . $parent_order_id];
    //                     $this->log->write("Callback: Using Razorpay order ID for parent order: " . $razorpay_order_id);
    //                 }
    //             }
    //         }

    //         $amount = $this->currency->format($original_amount, $order_info['currency_code'], $order_info['currency_value'], false) * 100;

    //         //validate Rzp signature
    //         try {
    //             // Log the attributes being sent for signature verification
    //             $this->log->write("Razorpay signature verification attributes: " . json_encode($attributes));

    //             // Log the request parameters
    //             $this->log->write("Razorpay callback request parameters: " . json_encode($_POST));

    //             // Log the session data related to Razorpay
    //             $session_data = [];
    //             foreach ($this->session->data as $key => $value) {
    //                 if (strpos($key, 'razorpay') !== false) {
    //                     $session_data[$key] = $value;
    //                 }
    //             }
    //             $this->log->write("Razorpay session data: " . json_encode($session_data));


    //             // nikita added
    //             $order_ids = [];

    //             if (isset($this->session->data['order_ids'])) {
    //                 $order_ids = json_decode($this->session->data['order_ids'], true);
    //                 $this->log->write("✅ Loaded order_ids from session: " . json_encode($order_ids));
    //             }

    //             // Fallback to DB
    //             if (empty($order_ids) && isset($this->session->data['parent_order_id'])) {
    //                 $this->load->model('checkout/order');
    //                 $parent = $this->model_checkout_order->getOrderParent($this->session->data['parent_order_id']);
    //                 if (!empty($parent['order_ids'])) {
    //                     $order_ids = json_decode($parent['order_ids'], true);
    //                     $this->log->write("✅ Loaded order_ids from DB (oc_order_parent): " . json_encode($order_ids));
    //                 }
    //             }

    //             // nikita ended

    //             // Log the secret key being used (masked for security)
    //             $key_id = $this->config->get('payment_razorpay_key_id');
    //             $key_secret = $this->config->get('payment_razorpay_key_secret');
    //             $this->log->write("Razorpay API Key ID: " . $key_id);
    //             $this->log->write("Razorpay API Key Secret (first 4 chars): " . substr($key_secret, 0, 4) . '****');

    //             // Attempt to verify the signature
    //             $this->api->utility->verifyPaymentSignature($attributes);
    //             $this->log->write("Razorpay signature verification successful");
    //             if ($isSubscriptionCallBack) {
    //                 $subscriptionData = $this->api->subscription->fetch($razorpay_subscription_id)->toArray();

    //                 $planData = $this->model_extension_payment_razorpay->fetchRZPPlanById($subscriptionData['plan_id']);
    //                 $this->model_extension_payment_razorpay->updateSubscription($subscriptionData, $razorpay_subscription_id);

    //                 // Update oC recurring table and OC recurring transaction
    //                 $this->model_extension_payment_razorpay->updateOCRecurringStatus($this->session->data['order_id'], 1);

    //                 // Creating OC Recurring Transaction
    //                 $ocRecurringData = $this->model_extension_payment_razorpay->getOCRecurringStatus($this->session->data['order_id']);
    //                 $this->model_extension_payment_razorpay->addOCRecurringTransaction($ocRecurringData['order_recurring_id'], $razorpay_subscription_id, $planData['plan_bill_amount'], "success");
    //             }

    //             if ($order_info['payment_code'] === 'razorpay') {
    //                 // Create a transaction record for the payment
    //                 $transaction_data = array(
    //                     'razorpay_payment_id' => $razorpay_payment_id,
    //                     'razorpay_order_id' => $razorpay_order_id,
    //                     'razorpay_signature' => $razorpay_signature,
    //                     'merchant_order_id' => $merchant_order_id,
    //                     'amount' => $amount / 100, // Convert back from smallest unit
    //                     'currency' => $order_info['currency_code'],
    //                     'status' => 'completed',
    //                     'date_added' => date('Y-m-d H:i:s')
    //                 );

    //                 // If this is a parent order, add parent order details to transaction
    //                 if ($is_parent_order && $parent_order_id) {
    //                     $transaction_data['parent_order_id'] = $parent_order_id;
    //                     $transaction_data['is_parent_order'] = 1;

    //                     // Save to oc_razorpay_transactions using model method
    //                     $this->load->model('extension/payment/razorpay');
    //                     $transaction_id = $this->model_extension_payment_razorpay->updatePayment($transaction_data);

    //                     $this->log->write("Created transaction record for parent order ID: " . $parent_order_id . ", Transaction ID: " . $transaction_id);
    //                 } else {
    //                     // Save to oc_razorpay_transactions using model method
    //                     $this->load->model('extension/payment/razorpay');
    //                     $transaction_id = $this->model_extension_payment_razorpay->updatePayment($transaction_data);

    //                     $this->log->write("Created transaction record for order ID: " . $merchant_order_id . ", Transaction ID: " . $transaction_id);
    //                 }

    //                 // Get the success status ID from config
    //                 $success_status_id = $this->config->get('payment_razorpay_order_status_id');

    //                 $this->log->write("Direct database update executed for order ID: " . $merchant_order_id . " to status: " . $success_status_id);


    //                 // Debug the success status ID
    //                 $this->log->write("Success status ID from config: " . $success_status_id);

    //                 // Check current order status
    //                 $current_order = $this->model_checkout_order->getOrder($merchant_order_id);
    //                 $this->log->write("Current order status before update: " . $current_order['order_status_id']);

    //                 // Force update the order status directly in the database first
    //                 $this->db->query("UPDATE `" . DB_PREFIX . "order` SET order_status_id = '" . (int)$success_status_id . "', date_modified = NOW() WHERE order_id = '" . (int)$merchant_order_id . "'");
    //                 $this->log->write("Direct database update executed for order ID: " . $merchant_order_id . " to status: " . $success_status_id);

    //                 // Then use the standard method to add order history
    //                 $this->model_checkout_order->addOrderHistory($merchant_order_id, $success_status_id, 'Payment Successful. Razorpay Payment Id:' . $razorpay_payment_id, true);
    //                 //   nikita added 19/06/2025
    //                 $debug_order = $this->model_checkout_order->getOrder($merchant_order_id);
    //                 $this->log->write("🕵️ Final order status after addOrderHistory: " . $debug_order['order_status_id']);


    //                 $this->model_extension_payment_razorpay->updateOrderForWebhook($merchant_order_id, $razorpay_order_id, $success_status_id);

    //                 // Verify the update was successful
    //                 $updated_order = $this->model_checkout_order->getOrder($merchant_order_id);
    //                 $this->log->write("Order status after update: " . $updated_order['order_status_id']);


    //                 // If this is a parent order, update all child orders with the same status
    //                 if ($is_parent_order && isset($this->session->data['order_ids'])) {
    //                     $order_ids = json_decode($this->session->data['order_ids'], true);

    //                     if (empty($order_ids) && isset($parent_order['order_ids'])) {
    //                         // Try to get order IDs from the parent order record
    //                         $order_ids = json_decode($parent_order['order_ids'], true);
    //                     }

    //                     $this->log->write("Parent Order ID: $parent_order_id");
    //                     $this->log->write("Order IDs from session/DB: " . json_encode($order_ids));

    //                     if (is_array($order_ids)) {
    //                         foreach ($order_ids as $child_order_id) {
    //                             if ($child_order_id != $merchant_order_id) { // Skip if it's the same as the main order
    //                                 $child_order_info = $this->model_checkout_order->getOrder($child_order_id);
    //                                 $this->log->write("Trying to update child order ID: " . $child_order_id);
    //                                 $this->log->write("🔁 Updating child order ID: $child_order_id via API fallback");


    //                                 if ($child_order_info) {
    //                                     // Force update child order status directly in the database first
    //                                     $this->db->query("UPDATE `" . DB_PREFIX . "order` SET order_status_id = '" . (int)$success_status_id . "', date_modified = NOW() WHERE order_id = '" . (int)$child_order_id . "'");
    //                                     $this->log->write("Direct database update executed for child order ID: " . $child_order_id . " to status: " . $success_status_id);

    //                                     // Then use the standard method to add order history
    //                                     $this->model_checkout_order->addOrderHistory($child_order_id, $success_status_id, 'Payment Successful via Parent Order. Razorpay Payment Id:' . $razorpay_payment_id, false);

    //                                     $this->log->write("Updated child order ID: " . $child_order_id . " status to: " . $success_status_id);
    //                                 } else {
    //                                     $this->log->write("❌ Could not find child order ID: $child_order_id in DB");
    //                                 }
    //                             }
    //                         }
    //                     }
    //                 }
    //             }
    //             $this->response->redirect($this->url->link('checkout/success', '', true));
    //         } catch (\Razorpay\Api\Errors\SignatureVerificationError $e) {
    //             // Log the signature verification error
    //             $this->log->write("Razorpay signature verification failed: " . $e->getMessage());

    //             // Try to verify the payment directly with Razorpay API as a fallback
    //             try {
    //                 $this->log->write("Attempting direct API verification for payment ID: " . $razorpay_payment_id);
    //                 $payment = $this->api->payment->fetch($razorpay_payment_id);

    //                 // Check if payment exists and is captured/authorized
    //                 if ($payment && ($payment->status === 'captured' || $payment->status === 'authorized')) {
    //                     // Verify the order ID if available
    //                     $payment_order_id = isset($payment->order_id) ? $payment->order_id : null;

    //                     if (!$payment_order_id || $payment_order_id === $razorpay_order_id) {
    //                         // Payment is valid despite signature verification failure
    //                         $this->log->write("Direct API verification successful for payment ID: " . $razorpay_payment_id);
    //                         $this->log->write("Payment status from API: " . $payment->status);

    //                         // Process the payment as successful
    //                         if ($isSubscriptionCallBack) {
    //                             $subscriptionData = $this->api->subscription->fetch($razorpay_subscription_id)->toArray();
    //                             $planData = $this->model_extension_payment_razorpay->fetchRZPPlanById($subscriptionData['plan_id']);
    //                             $this->model_extension_payment_razorpay->updateSubscription($subscriptionData, $razorpay_subscription_id);
    //                             $this->model_extension_payment_razorpay->updateOCRecurringStatus($this->session->data['order_id'], 1);
    //                             $ocRecurringData = $this->model_extension_payment_razorpay->getOCRecurringStatus($this->session->data['order_id']);
    //                             $this->model_extension_payment_razorpay->addOCRecurringTransaction($ocRecurringData['order_recurring_id'], $razorpay_subscription_id, $planData['plan_bill_amount'], "success");
    //                         }

    //                         // Make sure the model is loaded
    //                         if (!isset($this->model_extension_payment_razorpay)) {
    //                             $this->load->model('extension/payment/razorpay');
    //                         }

    //                         // Create a transaction record for the successful payment
    //                         $transaction_data = array(
    //                             'razorpay_payment_id' => $razorpay_payment_id,
    //                             'razorpay_order_id' => $razorpay_order_id,
    //                             'razorpay_signature' => $razorpay_signature,
    //                             'merchant_order_id' => $merchant_order_id,
    //                             'amount' => $amount / 100,
    //                             'currency' => $order_info['currency_code'],
    //                             'status' => 'completed',
    //                             'date_added' => date('Y-m-d H:i:s')
    //                         );

    //                         // If this is a parent order, add parent order details to transaction
    //                         if ($is_parent_order && $parent_order_id) {
    //                             $transaction_data['parent_order_id'] = $parent_order_id;
    //                             $transaction_data['is_parent_order'] = 1;
    //                             $transaction_id = $this->model_extension_payment_razorpay->updatePayment($transaction_data);
    //                             $this->log->write("Created transaction record for parent order ID: " . $parent_order_id . ", Transaction ID: " . $transaction_id);
    //                         } else {
    //                             $transaction_id = $this->model_extension_payment_razorpay->updatePayment($transaction_data);
    //                             $this->log->write("Created transaction record for order ID: " . $merchant_order_id . ", Transaction ID: " . $transaction_id);
    //                         }

    //                         // Get the success status ID from config
    //                         $success_status_id = $this->config->get('payment_razorpay_order_status_id');

    //                         // Debug the success status ID
    //                         $this->log->write("Success status ID from config: " . $success_status_id);

    //                         // Check current order status
    //                         $current_order = $this->model_checkout_order->getOrder($merchant_order_id);
    //                         $this->log->write("Current order status before update: " . $current_order['order_status_id']);

    //                         // Force update the order status directly in the database first
    //                         $this->db->query("UPDATE `" . DB_PREFIX . "order` SET order_status_id = '" . (int)$success_status_id . "', date_modified = NOW() WHERE order_id = '" . (int)$merchant_order_id . "'");
    //                         $this->log->write("Direct database update executed for order ID: " . $merchant_order_id . " to status: " . $success_status_id);

    //                         // Then use the standard method to add order history
    //                         $this->log->write("Updating order ID: " . $merchant_order_id . " status to: " . $success_status_id);
    //                         $this->model_checkout_order->addOrderHistory($merchant_order_id, $success_status_id, 'Payment Successful via API verification. Razorpay Payment Id:' . $razorpay_payment_id, true);
    //                         $this->model_extension_payment_razorpay->updateOrderForWebhook($merchant_order_id, $razorpay_order_id, $success_status_id);
    //                         $this->log->write("Updated order ID: " . $merchant_order_id . " status to: " . $success_status_id);

    //                         // Verify the update was successful
    //                         $updated_order = $this->model_checkout_order->getOrder($merchant_order_id);
    //                         $this->log->write("Order status after update: " . $updated_order['order_status_id']);

    //                         // If this is a parent order, update all child orders with the same status
    //                         if ($is_parent_order && isset($this->session->data['order_ids'])) {
    //                             $order_ids = json_decode($this->session->data['order_ids'], true);

    //                             if (empty($order_ids) && isset($parent_order['order_ids'])) {
    //                                 $order_ids = json_decode($parent_order['order_ids'], true);
    //                             }

    //                             if (is_array($order_ids)) {
    //                                 foreach ($order_ids as $child_order_id) {
    //                                     if ($child_order_id != $merchant_order_id) {
    //                                         $child_order_info = $this->model_checkout_order->getOrder($child_order_id);

    //                                         // Always update child order status regardless of current status
    //                                         if ($child_order_info) {
    //                                             $this->log->write("Updating child order ID: " . $child_order_id . " status to: " . $success_status_id);
    //                                             $this->model_checkout_order->addOrderHistory($child_order_id, $success_status_id, 'Payment Successful via API verification. Razorpay Payment Id:' . $razorpay_payment_id, false);
    //                                             $this->log->write("Updated child order ID: " . $child_order_id . " status to: " . $success_status_id);
    //                                         }
    //                                     }
    //                                 }
    //                             }
    //                         }

    //                         $this->response->redirect($this->url->link('checkout/success', '', true));
    //                         return;
    //                     } else {
    //                         $this->log->write("Order ID mismatch in API verification. Expected: " . $razorpay_order_id . ", Got: " . $payment_order_id);
    //                     }
    //                 } else {
    //                     $payment_status = $payment ? $payment->status : 'not found';
    //                     $this->log->write("Payment verification failed via API. Payment status: " . $payment_status);
    //                 }
    //             } catch (\Exception $api_e) {
    //                 $this->log->write("API verification failed with error: " . $api_e->getMessage());
    //             }

    //             // If we reach here, both signature verification and API verification failed
    //             if ($isSubscriptionCallBack) {
    //                 // Update oC recurring table for failed payment
    //                 $this->model_extension_payment_razorpay->updateOCRecurringStatus($this->session->data['order_id'], 4);
    //             }

    //             // Create a transaction record for the failed payment
    //             $transaction_data = array(
    //                 'razorpay_payment_id' => $razorpay_payment_id,
    //                 'razorpay_order_id' => $razorpay_order_id,
    //                 'razorpay_signature' => $razorpay_signature,
    //                 'merchant_order_id' => $merchant_order_id,
    //                 'amount' => $amount / 100, // Convert back from smallest unit
    //                 'currency' => $order_info['currency_code'],
    //                 'status' => 'failed',
    //                 'date_added' => date('Y-m-d H:i:s')
    //             );

    //             // Save to oc_razorpay_transactions using model method
    //             $this->load->model('extension/payment/razorpay');

    //             // If this is a parent order, add parent order details to transaction
    //             if ($is_parent_order && $parent_order_id) {
    //                 $transaction_data['parent_order_id'] = $parent_order_id;
    //                 $transaction_data['is_parent_order'] = 1;

    //                 // Save failed transaction record for parent order
    //                 $transaction_id = $this->model_extension_payment_razorpay->updatePayment($transaction_data);
    //                 $this->log->write("Created failed transaction record for parent order ID: " . $parent_order_id . ", Transaction ID: " . $transaction_id);
    //             } else {
    //                 // Save failed transaction record for regular order
    //                 $transaction_id = $this->model_extension_payment_razorpay->updatePayment($transaction_data);
    //                 $this->log->write("Created failed transaction record for order ID: " . $merchant_order_id . ", Transaction ID: " . $transaction_id);
    //             }

    //             // Get the failed status ID
    //             $failed_status_id = 10; // Default failed status ID

    //             // Debug the failed status ID
    //             $this->log->write("Failed status ID: " . $failed_status_id);

    //             // Check current order status
    //             $current_order = $this->model_checkout_order->getOrder($merchant_order_id);
    //             $this->log->write("Current order status before update: " . $current_order['order_status_id']);

    //             // Force update the order status directly in the database first
    //             $this->db->query("UPDATE `" . DB_PREFIX . "order` SET order_status_id = '" . (int)$failed_status_id . "', date_modified = NOW() WHERE order_id = '" . (int)$merchant_order_id . "'");
    //             $this->log->write("Direct database update executed for order ID: " . $merchant_order_id . " to status: " . $failed_status_id);

    //             // Then use the standard method to add order history
    //             $this->model_checkout_order->addOrderHistory($merchant_order_id, $failed_status_id, $e->getMessage() . ' Payment Failed! Check Razorpay dashboard for details of Payment Id:' . $razorpay_payment_id);

    //             // Verify the update was successful
    //             $updated_order = $this->model_checkout_order->getOrder($merchant_order_id);
    //             $this->log->write("Order status after update: " . $updated_order['order_status_id']);

    //             // If this is a parent order, update all child orders with failed status
    //             if ($is_parent_order) {
    //                 $order_ids = isset($this->session->data['order_ids']) ? json_decode($this->session->data['order_ids'], true) : null;

    //                 if (empty($order_ids) && isset($parent_order['order_ids'])) {
    //                     // Try to get order IDs from the parent order record
    //                     $order_ids = json_decode($parent_order['order_ids'], true);
    //                 }

    //                 if (is_array($order_ids)) {
    //                     foreach ($order_ids as $child_order_id) {
    //                         if ($child_order_id != $merchant_order_id) { // Skip if it's the same as the main order
    //                             $child_order_info = $this->model_checkout_order->getOrder($child_order_id);

    //                             if ($child_order_info) {
    //                                 // Force update child order status directly in the database first
    //                                 $this->db->query("UPDATE `" . DB_PREFIX . "order` SET order_status_id = '" . (int)$failed_status_id . "', date_modified = NOW() WHERE order_id = '" . (int)$child_order_id . "'");
    //                                 $this->log->write("Direct database update executed for child order ID: " . $child_order_id . " to status: " . $failed_status_id);

    //                                 // Then use the standard method to add order history
    //                                 $this->log->write("Updating child order ID: " . $child_order_id . " status to failed");
    //                                 $this->model_checkout_order->addOrderHistory($child_order_id, $failed_status_id, 'Payment Failed via Parent Order. ' . $e->getMessage(), false);
    //                                 $this->log->write("Updated child order ID: " . $child_order_id . " status to failed");
    //                             }
    //                         }
    //                     }
    //                 }
    //             }

    //             $this->session->data['error'] = $e->getMessage() . ' Payment Failed! Check Razorpay dashboard for details of Payment Id:' . $razorpay_payment_id;
    //             $this->response->redirect($this->url->link('checkout/checkout', '', true));
    //         }
    //     } else {
    //         if (isset($_POST['error']) === true) {
    //             $error = $_POST['error'];

    //             $message = 'An error occured. Description : ' . $error['description'] . '. Code : ' . $error['code'];

    //             if (isset($error['field']) === true) {
    //                 $message .= 'Field : ' . $error['field'];
    //             }
    //         } else {
    //             $message = 'An error occured. Please contact administrator for assistance';
    //         }

    //         $this->session->data['error'] = $message;
    //         $this->response->redirect($this->url->link('checkout/checkout', '', true));
    //     }
    // }
    public function callback()
    {
        $this->load->model('checkout/order');
        $this->load->model('extension/payment/razorpay');

        // Handle case where order_id is an array (split orders)
        if (isset($this->session->data['order_id']) && is_array($this->session->data['order_id'])) {
            $this->log->write('Order ID is array. Will extract first value.');
            $merchant_order_id = reset($this->session->data['order_id']); // Get first order ID
        } else {
            $merchant_order_id = $this->session->data['order_id'];
        }

        if (isset($this->request->post['razorpay_payment_id'])) {
            $razorpay_payment_id = $this->request->post['razorpay_payment_id'];
            $razorpay_signature = $this->request->post['razorpay_signature'];

            $isSubscriptionCallBack = false;
            $razorpay_order_id = '';

            // Determine if this is a subscription callback
            if (array_key_exists("razorpay_subscription_order_id_" . $merchant_order_id, $this->session->data)) {
                $razorpay_subscription_id = $this->session->data["razorpay_subscription_order_id_" . $merchant_order_id];
                $isSubscriptionCallBack = true;
                $attributes = array(
                    'razorpay_subscription_id' => $razorpay_subscription_id,
                    'razorpay_payment_id' => $razorpay_payment_id,
                    'razorpay_signature' => $razorpay_signature
                );
            } else {
                $razorpay_order_id = $this->session->data["razorpay_order_id_" . $merchant_order_id];
                $attributes = array(
                    'razorpay_order_id' => $razorpay_order_id,
                    'razorpay_payment_id' => $razorpay_payment_id,
                    'razorpay_signature' => $razorpay_signature
                );
            }

            $order_info = $this->model_checkout_order->getOrder($merchant_order_id);

            // Get the correct amount from parent order if available
            $original_amount = $order_info['total'];
            $is_parent_order = false;
            $parent_order_id = null;

            if (isset($this->session->data['parent_order_id'])) {
                $parent_order_id = $this->session->data['parent_order_id'];
                $parent_order = $this->model_checkout_order->getOrderParent($parent_order_id);

                if ($parent_order && isset($parent_order['total'])) {
                    $original_amount = $parent_order['total'];
                    $is_parent_order = true;
                    $this->log->write("Callback: Using parent order total: " . $original_amount . " for parent order ID: " . $parent_order_id);

                    // Check if we're using a Razorpay order ID for the parent order
                    if (isset($this->session->data["razorpay_parent_order_id_" . $parent_order_id])) {
                        $razorpay_order_id = $this->session->data["razorpay_parent_order_id_" . $parent_order_id];
                        $this->log->write("Callback: Using Razorpay order ID for parent order: " . $razorpay_order_id);
                        $attributes['razorpay_order_id'] = $razorpay_order_id; // Update attributes for signature verification
                    }
                }
            }

            $amount = $this->currency->format($original_amount, $order_info['currency_code'], $order_info['currency_value'], false) * 100;

            // Load order_ids for parent order, including merchant_order_id
            $order_ids = [];
            if ($is_parent_order && isset($this->session->data['order_ids'])) {
                $order_ids = json_decode($this->session->data['order_ids'], true);
                $this->log->write("✅ Loaded order_ids from session: " . json_encode($order_ids));
            }

            // Fallback to database if order_ids not found in session
            if (empty($order_ids) && $is_parent_order && isset($parent_order['order_ids'])) {
                $order_ids = json_decode($parent_order['order_ids'], true);
                $this->log->write("✅ Loaded order_ids from DB (oc_order_parent): " . json_encode($order_ids));
            }

            // Ensure order_ids includes merchant_order_id and is an array
            if ($is_parent_order) {
                if (!is_array($order_ids)) {
                    $order_ids = [];
                    $this->log->write("Warning: order_ids is not an array, initializing with merchant_order_id");
                }
                // Add merchant_order_id to order_ids if not already included
                if (!in_array($merchant_order_id, $order_ids)) {
                    $order_ids[] = $merchant_order_id;
                }
            } else {
                // Non-parent order: only merchant_order_id
                $order_ids = [$merchant_order_id];
            }

            $this->log->write("Processing orders: " . json_encode($order_ids));

            // Validate Razorpay signature
            try {
                // Log verification details
                $this->log->write("Razorpay signature verification attributes: " . json_encode($attributes));
                $this->log->write("Razorpay callback request parameters: " . json_encode($_POST));
                $session_data = [];
                foreach ($this->session->data as $key => $value) {
                    if (strpos($key, 'razorpay') !== false) {
                        $session_data[$key] = $value;
                    }
                }
                $this->log->write("Razorpay session data: " . json_encode($session_data));
                $this->log->write("Razorpay API Key ID: " . $this->config->get('payment_razorpay_key_id'));
                $this->log->write("Razorpay API Key Secret (first 4 chars): " . substr($this->config->get('payment_razorpay_key_secret'), 0, 4) . '****');

                // Verify signature
                $this->api->utility->verifyPaymentSignature($attributes);
                $this->log->write("Razorpay signature verification successful");

                // Handle subscription callback
                if ($isSubscriptionCallBack) {
                    $subscriptionData = $this->api->subscription->fetch($razorpay_subscription_id)->toArray();
                    $planData = $this->model_extension_payment_razorpay->fetchRZPPlanById($subscriptionData['plan_id']);
                    $this->model_extension_payment_razorpay->updateSubscription($subscriptionData, $razorpay_subscription_id);
                    $this->model_extension_payment_razorpay->updateOCRecurringStatus($merchant_order_id, 1);
                    $ocRecurringData = $this->model_extension_payment_razorpay->getOCRecurringStatus($merchant_order_id);
                    $this->model_extension_payment_razorpay->addOCRecurringTransaction($ocRecurringData['order_recurring_id'], $razorpay_subscription_id, $planData['plan_bill_amount'], "success");
                }

                // Create transaction record
                $transaction_data = array(
                    'razorpay_payment_id' => $razorpay_payment_id,
                    'razorpay_order_id' => $razorpay_order_id,
                    'razorpay_signature' => $razorpay_signature,
                    'merchant_order_id' => $merchant_order_id,
                    'amount' => $amount / 100,
                    'currency' => $order_info['currency_code'],
                    'status' => 'completed',
                    'date_added' => date('Y-m-d H:i:s')
                );

                if ($is_parent_order && $parent_order_id) {
                    $transaction_data['parent_order_id'] = $parent_order_id;
                    $transaction_data['is_parent_order'] = 1;
                    $transaction_id = $this->model_extension_payment_razorpay->updatePayment($transaction_data);
                    $this->log->write("Created transaction record for parent order ID: " . $parent_order_id . ", Transaction ID: " . $transaction_id);
                } else {
                    $transaction_id = $this->model_extension_payment_razorpay->updatePayment($transaction_data);
                    $this->log->write("Created transaction record for order ID: " . $merchant_order_id . ", Transaction ID: " . $transaction_id);
                }

                $success_status_id = $this->config->get('payment_razorpay_order_status_id');
                $this->log->write("Success status ID from config: " . $success_status_id);

                // Update all orders (parent and child) in one loop
                $this->log->write("Processing all orders: " . json_encode($order_ids));
                foreach ($order_ids as $order_id) {
                    $this->log->write("🔁 Updating order ID: " . $order_id);
                    $current_order = $this->model_checkout_order->getOrder($order_id);
                    if ($current_order) {
                        $this->log->write("Current order status before update: " . $current_order['order_status_id']);
                        $this->db->query("UPDATE `" . DB_PREFIX . "order` SET order_status_id = '" . (int)$success_status_id . "', date_modified = NOW() WHERE order_id = '" . (int)$order_id . "'");
                        $this->log->write("Direct database update executed for order ID: " . $order_id . " to status: " . $success_status_id);
                        $comment = $is_parent_order && $order_id != $merchant_order_id ?
                            'Payment Successful via Parent Order. Razorpay Payment Id: ' . $razorpay_payment_id :
                            'Payment Successful. Razorpay Payment Id: ' . $razorpay_payment_id;
                        $this->model_checkout_order->addOrderHistory($order_id, $success_status_id, $comment, $order_id == $merchant_order_id);
                        $this->model_extension_payment_razorpay->updateOrderForWebhook($order_id, $razorpay_order_id, $success_status_id);
                        $updated_order = $this->model_checkout_order->getOrder($order_id);
                        $this->log->write("✅ Updated order ID: " . $order_id . " status to: " . $updated_order['order_status_id']);
                    } else {
                        $this->log->write("❌ Could not find order ID: " . $order_id . " in DB");
                    }
                }

                $this->response->redirect($this->url->link('checkout/success', '', true));
            } catch (\Razorpay\Api\Errors\SignatureVerificationError $e) {
                $this->log->write("Razorpay signature verification failed: " . $e->getMessage());

                // Fallback to direct API verification
                try {
                    $this->log->write("Attempting direct API verification for payment ID: " . $razorpay_payment_id);
                    $payment = $this->api->payment->fetch($razorpay_payment_id);

                    if ($payment && ($payment->status === 'captured' || $payment->status === 'authorized')) {
                        $payment_order_id = isset($payment->order_id) ? $payment->order_id : null;
                        if (!$payment_order_id || $payment_order_id === $razorpay_order_id) {
                            $this->log->write("Direct API verification successful for payment ID: " . $razorpay_payment_id . ", Status: " . $payment->status);

                            if ($isSubscriptionCallBack) {
                                $subscriptionData = $this->api->subscription->fetch($razorpay_subscription_id)->toArray();
                                $planData = $this->model_extension_payment_razorpay->fetchRZPPlanById($subscriptionData['plan_id']);
                                $this->model_extension_payment_razorpay->updateSubscription($subscriptionData, $razorpay_subscription_id);
                                $this->model_extension_payment_razorpay->updateOCRecurringStatus($merchant_order_id, 1);
                                $ocRecurringData = $this->model_extension_payment_razorpay->getOCRecurringStatus($merchant_order_id);
                                $this->model_extension_payment_razorpay->addOCRecurringTransaction($ocRecurringData['order_recurring_id'], $razorpay_subscription_id, $planData['plan_bill_amount'], "success");
                            }

                            $transaction_data = array(
                                'razorpay_payment_id' => $razorpay_payment_id,
                                'razorpay_order_id' => $razorpay_order_id,
                                'razorpay_signature' => $razorpay_signature,
                                'merchant_order_id' => $merchant_order_id,
                                'amount' => $amount / 100,
                                'currency' => $order_info['currency_code'],
                                'status' => 'completed',
                                'date_added' => date('Y-m-d H:i:s')
                            );

                            if ($is_parent_order && $parent_order_id) {
                                $transaction_data['parent_order_id'] = $parent_order_id;
                                $transaction_data['is_parent_order'] = 1;
                                $transaction_id = $this->model_extension_payment_razorpay->updatePayment($transaction_data);
                                $this->log->write("Created transaction record for parent order ID: " . $parent_order_id . ", Transaction ID: " . $transaction_id);
                            } else {
                                $transaction_id = $this->model_extension_payment_razorpay->updatePayment($transaction_data);
                                $this->log->write("Created transaction record for order ID: " . $merchant_order_id . ", Transaction ID: " . $transaction_id);
                            }

                            $success_status_id = $this->config->get('payment_razorpay_order_status_id');
                            $this->log->write("Success status ID from config: " . $success_status_id);

                            // Update all orders (parent and child) in one loop
                            $this->log->write("Processing all orders: " . json_encode($order_ids));
                            foreach ($order_ids as $order_id) {
                                $this->log->write("🔁 Updating order ID: " . $order_id);
                                $current_order = $this->model_checkout_order->getOrder($order_id);
                                if ($current_order) {
                                    $this->log->write("Current order status before update: " . $current_order['order_status_id']);
                                    $this->db->query("UPDATE `" . DB_PREFIX . "order` SET order_status_id = '" . (int)$success_status_id . "', date_modified = NOW() WHERE order_id = '" . (int)$order_id . "'");
                                    $this->log->write("Direct database update executed for order ID: " . $order_id . " to status: " . $success_status_id);
                                    $comment = $is_parent_order && $order_id != $merchant_order_id ?
                                        'Payment Successful via Parent Order. Razorpay Payment Id: ' . $razorpay_payment_id :
                                        'Payment Successful via API verification. Razorpay Payment Id: ' . $razorpay_payment_id;
                                    $this->model_checkout_order->addOrderHistory($order_id, $success_status_id, $comment, $order_id == $merchant_order_id);
                                    $this->model_extension_payment_razorpay->updateOrderForWebhook($order_id, $razorpay_order_id, $success_status_id);
                                    $updated_order = $this->model_checkout_order->getOrder($order_id);
                                    $this->log->write("✅ Updated order ID: " . $order_id . " status to: " . $updated_order['order_status_id']);
                                } else {
                                    $this->log->write("❌ Could not find order ID: " . $order_id . " in DB");
                                }
                            }

                            $this->response->redirect($this->url->link('checkout/success', '', true));
                            return;
                        } else {
                            $this->log->write("Order ID mismatch in API verification. Expected: " . $razorpay_order_id . ", Got: " . $payment_order_id);
                            $this->response->redirect($this->url->link('checkout/failure', '', true));
                        }
                    } else {
                        $this->log->write("Direct API verification failed. Payment status: " . ($payment ? $payment->status : 'unknown'));
                        $this->response->redirect($this->url->link('checkout/failure', '', true));
                    }
                } catch (\Exception $e) {
                    $this->log->write("Direct API verification failed: " . $e->getMessage());
                    $this->response->redirect($this->url->link('checkout/failure', '', true));
                }
            } catch (\Exception $e) {
                $this->log->write("Unexpected error during signature verification: " . $e->getMessage());
                $this->response->redirect($this->url->link('checkout/failure', '', true));
            }
        } else {
            $this->log->write("Razorpay payment ID not found in request.");
            $this->response->redirect($this->url->link('checkout/failure', '', true));
        }
    }

    public function webhook()
    {
        $post = file_get_contents('php://input');
        $data = json_decode($post, true);

        if (json_last_error() !== 0) {
            return;
        }

        $this->load->model('checkout/order');
        $this->load->model('extension/payment/razorpay');
        $enabled = $this->config->get('payment_razorpay_webhook_status');

        if (($enabled === '1') and
            (empty($data['event']) === false)
        ) {

            if (isset($_SERVER['HTTP_X_RAZORPAY_SIGNATURE']) === true) {
                try {
                    $this->validateSignature($post, $_SERVER['HTTP_X_RAZORPAY_SIGNATURE']);
                } catch (\Razorpay\Api\Errors\SignatureVerificationError $e) {
                    $this->log->write($e->getMessage());
                    return;
                }

                if (in_array($data['event'], [self::ORDER_PAID, self::PAYMENT_AUTHORIZED]) === true) {
                    $webhookFilteredData = [
                        "id"                => $data['payload']['payment']['entity']['id'],
                        "event"             => $data['event'],
                        "opencart_order_id" => $data['payload']['payment']['entity']['notes']['opencart_order_id']
                    ];

                    if ($data['event'] === self::ORDER_PAID) {
                        $webhookFilteredData['invoice_id'] = $data['payload']['payment']['entity']['invoice_id'];
                        sleep(3);
                    }
                    $this->model_extension_payment_razorpay->addWebhookEvent(
                        $data['payload']['payment']['entity']['notes']['opencart_order_id'],
                        $data['payload']['payment']['entity']['order_id'],
                        $webhookFilteredData
                    );
                } else {
                    switch ($data['event']) {
                        case self::PAYMENT_FAILED:
                            return $this->paymentFailed($data);
                        case self::SUBSCRIPTION_PAUSED:
                        case self::SUBSCRIPTION_RESUMED:
                        case self::SUBSCRIPTION_CANCELLED:
                            return $this->updateOcSubscriptionStatus($data);
                        case self::SUBSCRIPTION_CHARGED:
                            return $this->processSubscriptionCharged($data);
                        default:
                            return;
                    }
                }
            }
        }
    }

    /**
     * Handling order.paid event
     * @param array $data Webook Data
     */
    protected function orderPaid(array $data)
    {
        // Do not process if order is subscription type
        if (isset($data['invoice_id']) === true) {
            $rzpInvoiceId = $data['invoice_id'];
            $invoice = $this->api->invoice->fetch($rzpInvoiceId);
            if (isset($invoice->subscription_id)) {
                return;
            }
        }

        // reference_no (opencart_order_id) should be passed in payload
        $merchant_order_id = $data['opencart_order_id'];
        $razorpay_payment_id = $data['id'];

        if (isset($merchant_order_id) === true) {
            $this->log->write("Processing webhook order.paid event for order ID: " . $merchant_order_id);

            $order_info = $this->model_checkout_order->getOrder($merchant_order_id);
            $this->log->write("Current order status: " . $order_info['order_status_id']);

            // Get the success status ID from config
            $success_status_id = $this->config->get('payment_razorpay_order_status_id');
            if (!$success_status_id) {
                $success_status_id = 2; // Default to Processing if not configured
            }
            $this->log->write("Success status ID from config: " . $success_status_id);

            if ($order_info['payment_code'] === 'razorpay') {
                // Force update the order status directly in the database first
                $this->db->query("UPDATE `" . DB_PREFIX . "order` SET order_status_id = '" . (int)$success_status_id . "', date_modified = NOW() WHERE order_id = '" . (int)$merchant_order_id . "'");
                $this->log->write("Direct database update executed for order ID: " . $merchant_order_id . " to status: " . $success_status_id);

                // Then use the standard method to add order history
                $this->model_checkout_order->addOrderHistory($merchant_order_id, $success_status_id, 'Payment Successful via Webhook. Razorpay Payment Id:' . $razorpay_payment_id);
                $this->log->write("order:$merchant_order_id updated by razorpay order.paid event");

                // Verify the update was successful
                $updated_order = $this->model_checkout_order->getOrder($merchant_order_id);
                $this->log->write("Order status after webhook update: " . $updated_order['order_status_id']);
            }
        }
    }

    /**
     * Handling payment.failed event
     * @param array $data Webook Data
     */
    protected function paymentFailed(array $data)
    {
        // reference_no (opencart_order_id) should be passed in payload
        $merchant_order_id = $data['opencart_order_id'];
        $razorpay_payment_id = $data['id'];

        $this->log->write("Processing webhook payment.failed event for order ID: " . $merchant_order_id);

        if (isset($merchant_order_id) === true) {
            $order_info = $this->model_checkout_order->getOrder($merchant_order_id);
            $this->log->write("Current order status: " . $order_info['order_status_id']);

            if ($order_info['payment_code'] === 'razorpay') {
                // Get the failed status ID
                $failed_status_id = 10; // Default failed status ID

                $this->log->write("Failed status ID: " . $failed_status_id);

                // Force update the order status directly in the database
                $this->db->query("UPDATE `" . DB_PREFIX . "order` SET order_status_id = '" . (int)$failed_status_id . "', date_modified = NOW() WHERE order_id = '" . (int)$merchant_order_id . "'");
                $this->log->write("Direct database update executed for order ID: " . $merchant_order_id . " to status: " . $failed_status_id);

                // Then use the standard method to add order history
                $this->model_checkout_order->addOrderHistory($merchant_order_id, $failed_status_id, 'Payment Failed via Webhook. Razorpay Payment Id: ' . $razorpay_payment_id);
                $this->log->write("Order history added for order ID: " . $merchant_order_id);

                // Verify the update was successful
                $updated_order = $this->model_checkout_order->getOrder($merchant_order_id);
                $this->log->write("Order status after webhook update: " . $updated_order['order_status_id']);

                // Check if this is a parent order with child orders
                $query = $this->db->query("SELECT order_ids FROM `" . DB_PREFIX . "order` WHERE order_id = '" . (int)$merchant_order_id . "'");
                $parent_order = $query->row;

                if (!empty($parent_order['order_ids'])) {
                    $order_ids = json_decode($parent_order['order_ids'], true);

                    if (is_array($order_ids)) {
                        foreach ($order_ids as $child_order_id) {
                            if ($child_order_id != $merchant_order_id) // Skip if it's the same as the main order
                            {
                                // Force update child order status directly in the database
                                $this->db->query("UPDATE `" . DB_PREFIX . "order` SET order_status_id = '" . (int)$failed_status_id . "', date_modified = NOW() WHERE order_id = '" . (int)$child_order_id . "'");
                                $this->log->write("Direct database update executed for child order ID: " . $child_order_id . " to status: " . $failed_status_id);

                                // Add order history for child order
                                $this->model_checkout_order->addOrderHistory($child_order_id, $failed_status_id, 'Payment Failed via Parent Order Webhook. Razorpay Payment Id: ' . $razorpay_payment_id, false);
                                $this->log->write("Order history added for child order ID: " . $child_order_id);
                            }
                        }
                    }
                }

                // Create a transaction record for the failed payment
                $transaction_data = array(
                    'razorpay_payment_id' => $razorpay_payment_id,
                    'merchant_order_id' => $merchant_order_id,
                    'amount' => $order_info['total'],
                    'currency' => $order_info['currency_code'],
                    'status' => 'failed',
                    'date_added' => date('Y-m-d H:i:s')
                );

                // Save to oc_razorpay_transactions using model method
                $transaction_id = $this->model_extension_payment_razorpay->updatePayment($transaction_data);
                $this->log->write("Created failed transaction record for order ID: " . $merchant_order_id . ", Transaction ID: " . $transaction_id);
            }
        }
    }

    /**
     * Handling payment.authorized event
     * @param array $data Webook Data
     */
    protected function paymentAuthorized(array $data)
    {
        if ($this->config->get('payment_razorpay_payment_action') === "capture") {
            return;
        }

        // reference_no (opencart_order_id) should be passed in payload
        $merchant_order_id = $data['opencart_order_id'];
        $razorpay_payment_id = $data['id'];

        $this->log->write("Processing webhook payment.authorized event for order ID: " . $merchant_order_id);

        //update the order
        if (isset($merchant_order_id) === true) {
            $order_info = $this->model_checkout_order->getOrder($merchant_order_id);
            $this->log->write("Current order status: " . $order_info['order_status_id']);

            // Get the success status ID from config
            $success_status_id = $this->config->get('payment_razorpay_order_status_id');
            if (!$success_status_id) {
                $success_status_id = 2; // Default to Processing if not configured
            }
            $this->log->write("Success status ID from config: " . $success_status_id);

            if ($order_info['payment_code'] === 'razorpay') {
                try {
                    $capture_amount = $this->currency->format($order_info['total'], $order_info['currency_code'], $order_info['currency_value'], false) * 100;

                    //fetch the payment
                    $payment = $this->api->payment->fetch($razorpay_payment_id);

                    //capture only if payment status is 'authorized'
                    if (
                        $payment->status === 'authorized'
                        and $this->config->get('payment_razorpay_payment_action') === 'capture'
                    ) {
                        $payment->capture(
                            array(
                                'amount' => $capture_amount,
                                'currency' => $order_info['currency_code']
                            )
                        );
                    }
                    //update the order status in store - first directly in the database
                    $this->db->query("UPDATE `" . DB_PREFIX . "order` SET order_status_id = '" . (int)$success_status_id . "', date_modified = NOW() WHERE order_id = '" . (int)$merchant_order_id . "'");
                    $this->log->write("Direct database update executed for order ID: " . $merchant_order_id . " to status: " . $success_status_id);

                    // Then use the standard method to add order history
                    $this->model_checkout_order->addOrderHistory($merchant_order_id, $success_status_id, 'Payment Successful via Webhook (Authorized). Razorpay Payment Id:' . $razorpay_payment_id);
                    $this->log->write("order:$merchant_order_id updated by razorpay payment.authorized event");

                    // Verify the update was successful
                    $updated_order = $this->model_checkout_order->getOrder($merchant_order_id);
                    $this->log->write("Order status after webhook update: " . $updated_order['order_status_id']);
                } catch (\Razorpay\Api\Errors\Error $e) {
                    $this->log->write("Razorpay API Error in payment.authorized webhook for order ID: " . $merchant_order_id . " - " . $e->getMessage());
                    return;
                } catch (Exception $e) {
                    $this->log->write("General Exception in payment.authorized webhook for order ID: " . $merchant_order_id . " - " . $e->getMessage());
                    return;
                }
            }
        }
    }


    /**
     * @param $payloadRawData
     * @param $actualSignature
     */
    public function validateSignature($payloadRawData, $actualSignature)
    {
        $webhookSecret = $this->config->get('payment_razorpay_webhook_secret');

        if (empty($webhookSecret) === false) {
            $this->api->utility->verifyWebhookSignature($payloadRawData, $actualSignature, $webhookSecret);
        }
    }

    public function getMerchantPreferences(array &$preferences)
    {
        try {
            $response = Requests::get($this->api->getBaseUrl() . 'preferences?key_id=' . $this->api->getKey());
        } catch (Exception $e) {
            $this->log->write($e->getMessage());
            throw new Exception($e->getMessage(), $e->getHttpCode());
        }

        $preferences['is_hosted'] = false;

        if ($response->status_code === 200) {

            $jsonResponse = json_decode($response->body, true);

            $preferences['image'] = $jsonResponse['options']['image'];

            if (empty($jsonResponse['options']['redirect']) === false) {
                $preferences['is_hosted'] = $jsonResponse['options']['redirect'];
            }
        }
    }

    protected function getApiIntance()
    {
        return new Api($this->config->get('payment_razorpay_key_id'), $this->config->get('payment_razorpay_key_secret'));
    }

    protected function getRazorpayApiInstance()
    {
        return $this->getApiIntance();
    }

    /**
     * This line of code tells api that if a customer is already created,
     * return the created customer instead of throwing an exception
     * https://docs.razorpay.com/v1/page/customers-api
     * @param $order
     * @return void
     */
    protected function getRazorpayCustomerData($order)
    {
        try {
            $customerData = [
                'email' => $order['email'],
                'name' => $order['firstname'] . " " . $order['lastname'],
                'contact' => $order['telephone'],
                'fail_existing' => 0
            ];

            $customerResponse = $this->api->customer->create($customerData);

            return $customerResponse->id;
        } catch (\Exception $e) {
            $this->log->write("Razopray exception Customer: {$e->getMessage()}");
            $this->session->data['error'] = $e->getMessage();
            echo "<div class='alert alert-danger alert-dismissible'> Something went wrong</div>";

            return;
        }
    }

    /**
     * Fetch subscription list
     */
    public function subscriptions()
    {
        if (!$this->customer->isLogged()) {
            $this->session->data['redirect'] = $this->url->link('extension/payment/razorpay/subscriptions', '', true);

            $this->response->redirect($this->url->link('account/login', '', true));
        }

        $this->load->language('extension/payment/razorpay');
        $this->document->setTitle($this->language->get('heading_title'));

        $url = '';

        if (isset($this->request->get['page'])) {
            $url .= '&page=' . $this->request->get['page'];
        }

        $data['breadcrumbs'] = array();

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/home')
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_account'),
            'href' => $this->url->link('account/account', '', true)
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('heading_title'),
            'href' => $this->url->link('extension/payment/razorpay/subscriptions', $url, true)
        );

        if (isset($this->request->get['page'])) {
            $page = (int)$this->request->get['page'];
        } else {
            $page = 1;
        }

        $this->load->model('extension/payment/razorpay');
        $recurring_total = $this->model_extension_payment_razorpay->getTotalOrderRecurring();
        $results = $this->model_extension_payment_razorpay->getSubscriptionByUserId(($page - 1) * 10, 10);

        foreach ($results as $result) {
            $data['subscriptions'][] = [
                'id' => $result['entity_id'],
                'subscription_id' => $result['subscription_id'],
                'productName' => $result['productName'],
                'status' => ucfirst($result["status"]),
                'total_count' => $result["total_count"],
                'paid_count' => $result["paid_count"],
                'remaining_count' => $result["remaining_count"],
                'start_at' => isset($result['start_at']) ? date($this->language->get('date_format_short'), strtotime($result['start_at'])) : "",
                'end_at' => isset($result['start_at']) ? date($this->language->get('date_format_short'), strtotime($result['end_at'])) : "",
                'subscription_created_at' => isset($result['subscription_created_at']) ? date($this->language->get('date_format_short'), strtotime($result['subscription_created_at'])) : "",
                'next_charge_at' => isset($result['next_charge_at']) ? date($this->language->get('date_format_short'), strtotime($result['next_charge_at'])) : "",
                'view' => $this->url->link('extension/payment/razorpay/info', "subscription_id={$result['subscription_id']}", true),
            ];
        }

        $pagination = new Pagination();
        $pagination->total = $recurring_total;
        $pagination->page = $page;
        $pagination->limit = 10;
        $pagination->text = $this->language->get('text_pagination');
        $pagination->url = $this->url->link('extension/payment/razorpay/subscriptions', 'page={page}', true);
        $data['pagination'] = $pagination->render();

        $data['continue'] = $this->url->link('account/account', '', true);
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['column_right'] = $this->load->controller('common/column_right');
        $data['content_top'] = $this->load->controller('common/content_top');
        $data['content_bottom'] = $this->load->controller('common/content_bottom');
        $data['footer'] = $this->load->controller('common/footer');
        $data['header'] = $this->load->controller('common/header');

        return $this->response->setOutput($this->load->view('extension/payment/razorpay_subscription/razorpay_subscription', $data));
    }

    /**
     * Subscription details
     * @return mixed
     */
    public function info()
    {
        if (!$this->customer->isLogged()) {
            $this->session->data['redirect'] = $this->url->link('extension/payment/razorpay/subscriptions', '', true);

            $this->response->redirect($this->url->link('account/login', '', true));
        }
        $this->load->language('extension/payment/razorpay');

        if (!empty($this->request->get['subscription_id'])) {
            $subscription_id = $this->request->get['subscription_id'];
        } else {
            $subscription_id = 0;
        }

        $this->load->model('extension/payment/razorpay');
        $recurring_info = $this->model_extension_payment_razorpay->getSubscriptionDetails($subscription_id);

        if (isset($this->session->data['error'])) {
            $data['error'] = $this->session->data['error'];
            unset($this->session->data['error']);
        }

        if (isset($this->session->data['success'])) {
            $data['success'] = $this->session->data['success'];
            unset($this->session->data['success']);
        }

        if (!empty($recurring_info)) {
            $this->document->setTitle($this->language->get('text_heading_title_subscription'));

            $url = '';

            if (isset($this->request->get['page'])) {
                $url .= '&page=' . $this->request->get['page'];
            }

            $data['breadcrumbs'] = array();

            $data['breadcrumbs'][] = array(
                'text' => $this->language->get('text_home'),
                'href' => $this->url->link('common/home'),
            );

            $data['breadcrumbs'][] = array(
                'text' => $this->language->get('text_account'),
                'href' => $this->url->link('account/account', '', true),
            );

            $data['breadcrumbs'][] = array(
                'text' => $this->language->get('heading_title'),
                'href' => $this->url->link('extension/payment/razorpay/subscriptions', $url, true),
            );

            $data['breadcrumbs'][] = array(
                'text' => $this->language->get('text_heading_title_subscription'),
                'href' => $this->url->link('extension/payment/razorpay/info', 'subscription_id=' . $subscription_id . $url, true),
            );
            $data['subscription_details'] = $recurring_info;

            $subscriptionInvoice = $this->api->invoice->all(['subscription_id' => $subscription_id])->toArray();
            $data["items"] = $subscriptionInvoice["items"];

            if ($recurring_info["status"] == "active") {
                $data['pauseurl'] = $this->url->link('extension/payment/razorpay/pause', 'subscription_id=' . $subscription_id, true);
            } else if ($recurring_info["status"] == "paused") {
                $data['resumeurl'] = $this->url->link('extension/payment/razorpay/resume', 'subscription_id=' . $subscription_id, true);
            }

            $data['cancelurl'] = $this->url->link('extension/payment/razorpay/cancel', 'subscription_id=' . $subscription_id, true);

            $data["plan_data"] = $this->model_extension_payment_razorpay->getProductBasedPlans($recurring_info["product_id"]);
            $data["updateUrl"] = $this->url->link('extension/payment/razorpay/update');


            $data['column_left'] = $this->load->controller('common/column_left');
            $data['column_right'] = $this->load->controller('common/column_right');
            $data['content_top'] = $this->load->controller('common/content_top');
            $data['content_bottom'] = $this->load->controller('common/content_bottom');
            $data['footer'] = $this->load->controller('common/footer');
            $data['header'] = $this->load->controller('common/header');

            return $this->response->setOutput($this->load->view('extension/payment/razorpay_subscription/razorpay_subscription_info', $data));
        } else {
            $this->document->setTitle($this->language->get('text_heading_title_subscription'));

            $data['breadcrumbs'] = array();

            $data['breadcrumbs'][] = array(
                'text' => $this->language->get('text_home'),
                'href' => $this->url->link('common/home')
            );

            $data['breadcrumbs'][] = array(
                'text' => $this->language->get('text_account'),
                'href' => $this->url->link('account/account', '', true)
            );

            $data['breadcrumbs'][] = array(
                'text' => $this->language->get('heading_title'),
                'href' => $this->url->link('extension/payment/razorpay/subscriptions', '', true)
            );

            $data['breadcrumbs'][] = array(
                'text' => $this->language->get('text_heading_title_subscription'),
                'href' => $this->url->link('extension/payment/razorpay/subscriptions/info', 'subscription_id=' . $subscription_id, true)
            );

            $data['continue'] = $this->url->link('extension/payment/razorpay/subscriptions', '', true);

            $data['column_left'] = $this->load->controller('common/column_left');
            $data['column_right'] = $this->load->controller('common/column_right');
            $data['content_top'] = $this->load->controller('common/content_top');
            $data['content_bottom'] = $this->load->controller('common/content_bottom');
            $data['footer'] = $this->load->controller('common/footer');
            $data['header'] = $this->load->controller('common/header');

            return $this->response->setOutput($this->load->view('error/not_found', $data));
        }
    }

    /**
     * Resume subscription
     */
    public function resume()
    {
        if (!$this->customer->isLogged()) {
            $this->session->data['redirect'] = $this->url->link('extension/payment/razorpay/subscriptions', '', true);

            $this->response->redirect($this->url->link('account/login', '', true));
        }
        $this->load->language('extension/payment/razorpay');

        if (!empty($this->request->get['subscription_id'])) {
            $subscription_id = $this->request->get['subscription_id'];
        } else {
            $subscription_id = 0;
        }

        try {
            $subscriptionData = $this->api->subscription->fetch($subscription_id)->resume(array('pause_at' => 'now'));
            $this->load->model('extension/payment/razorpay');

            $this->model_extension_payment_razorpay->updateSubscriptionStatus($this->request->get['subscription_id'], $subscriptionData->status);

            $subscriptionData = $this->model_extension_payment_razorpay->getSubscriptionById($subscription_id);
            $this->model_extension_payment_razorpay->updateOCRecurringStatus($subscriptionData['order_id'], 1);

            $this->session->data['success'] = $this->language->get('subscription_resumed_message');

            return $this->response->redirect($this->url->link('extension/payment/razorpay/info', 'subscription_id=' . $subscription_id, true));
        } catch (\Razorpay\Api\Errors\Error $e) {
            $this->log->write($e->getMessage());
            $this->session->data['error'] = ucfirst($e->getMessage());

            return  $this->response->redirect($this->url->link('extension/payment/razorpay/info', 'subscription_id=' . $this->request->get['subscription_id'], true));
        }
    }

    /**
     * Pause subscription
     */
    public function pause()
    {
        if (!$this->customer->isLogged()) {
            $this->session->data['redirect'] = $this->url->link('extension/payment/razorpay/subscriptions', '', true);

            $this->response->redirect($this->url->link('account/login', '', true));
        }
        $this->load->language('extension/payment/razorpay');

        if (!empty($this->request->get['subscription_id'])) {
            $subscription_id = $this->request->get['subscription_id'];
        } else {
            $subscription_id = 0;
        }

        try {
            $subscriptionData = $this->api->subscription->fetch($subscription_id)->pause(array('pause_at' => 'now'));
            $this->load->model('extension/payment/razorpay');

            $this->model_extension_payment_razorpay->updateSubscriptionStatus($subscription_id, $subscriptionData->status);

            $subscriptionData = $this->model_extension_payment_razorpay->getSubscriptionById($subscription_id);
            $this->model_extension_payment_razorpay->updateOCRecurringStatus($subscriptionData['order_id'], 2);

            $this->session->data['success'] = $this->language->get('subscription_paused_message');

            return $this->response->redirect($this->url->link('extension/payment/razorpay/info', 'subscription_id=' . $subscription_id, true));
        } catch (\Razorpay\Api\Errors\Error $e) {
            $this->log->write($e->getMessage());
            $this->session->data['error'] = ucfirst($e->getMessage());
            return  $this->response->redirect($this->url->link('extension/payment/razorpay/info', 'subscription_id=' . $this->request->get['subscription_id'], true));
        }
    }

    /**
     * Cancel Subscription
     */
    public function cancel()
    {
        if (!$this->customer->isLogged()) {
            $this->session->data['redirect'] = $this->url->link('extension/payment/razorpay/subscriptions', '', true);

            $this->response->redirect($this->url->link('account/login', '', true));
        }
        $this->load->language('extension/payment/razorpay');

        if (!empty($this->request->get['subscription_id'])) {
            $subscription_id = $this->request->get['subscription_id'];
        } else {
            $subscription_id = 0;
        }
        try {
            $subscriptionData = $this->api->subscription->fetch($subscription_id)->cancel(array('cancel_at_cycle_end' => 0));
            $this->load->model('extension/payment/razorpay');

            $this->model_extension_payment_razorpay->updateSubscriptionStatus($subscription_id, $subscriptionData->status, "user");

            $subscriptionData = $this->model_extension_payment_razorpay->getSubscriptionById($subscription_id);
            $this->model_extension_payment_razorpay->updateOCRecurringStatus($subscriptionData['order_id'], 3);

            $this->session->data['success'] = $this->language->get('subscription_cancelled_message');

            return $this->response->redirect($this->url->link('extension/payment/razorpay/info', 'subscription_id=' . $subscription_id, true));
        } catch (\Razorpay\Api\Errors\Error $e) {
            $this->log->write($e->getMessage());
            $this->session->data['error'] = ucfirst($e->getMessage());

            return  $this->response->redirect($this->url->link('extension/payment/razorpay/info', 'subscription_id=' . $this->request->get['subscription_id'], true));
        }
    }

    /**
     * Update subscription
     */
    public function update()
    {
        try {
            $postData = $this->request->post;

            $this->load->language('extension/payment/razorpay');
            $this->load->model('extension/payment/razorpay');
            $planData = $this->model_extension_payment_razorpay->fetchPlanByEntityId($postData["plan_entity_id"]);

            $planUpdateData['plan_id'] = $planData['plan_id'];

            if ($postData['qty']) {
                $planUpdateData['quantity'] = $postData['qty'];
            }

            $this->api->subscription->fetch($postData["subscriptionId"])->update($planUpdateData)->toArray();

            //Update plan in razorpay subscription table
            $this->model_extension_payment_razorpay->updateSubscriptionPlan($postData);

            $this->session->data['success'] = $this->language->get('subscription_updated_message');

            return $this->response->redirect($this->url->link('extension/payment/razorpay/info', 'subscription_id=' . $postData['subscriptionId'], true));
        } catch (\Razorpay\Api\Errors\Error $e) {
            $this->log->write($e->getMessage());
            $this->session->data['error'] = ucfirst($e->getMessage());
            return  $this->response->redirect($this->url->link('extension/payment/razorpay/info', 'subscription_id=' . $postData['subscriptionId'], true));
        }
    }

    /**
     * Handling subscription.paused, subscription.resumed, subscription.cancelled events
     * @param array $data Webook Data
     */
    protected function updateOcSubscriptionStatus($data)
    {
        $subscriptionId = $data['payload']['subscription']['entity']['id'];

        if (empty($subscriptionId) === false) {
            $merchant_order_id = $data['payload']['subscription']['entity']['notes']['merchant_order_id'];

            if (isset($merchant_order_id) === true) {
                switch ($data['event']) {
                    case 'subscription.paused':
                        $status = 'paused';
                        $oc_status = 2;
                        break;

                    case 'subscription.resumed':
                        $status = 'active';
                        $oc_status = 1;
                        break;

                    case 'subscription.cancelled':
                        $status = 'cancelled';
                        $oc_status = 3;
                        break;
                }

                $this->load->model('extension/payment/razorpay');
                $rzpSubscription = $this->model_extension_payment_razorpay->getSubscriptionById($subscriptionId);

                if ($rzpSubscription['status'] != $status) {
                    $this->model_extension_payment_razorpay->updateSubscriptionStatus($subscriptionId, $status, "Webhook");
                    $this->model_extension_payment_razorpay->updateOCRecurringStatus($merchant_order_id, $oc_status);
                    $this->log->write("Subscription " . $status . " webhook event processed for Opencart OrderID (:" . $merchant_order_id . ")");
                }

                return;
            }
        }
    }

    /**
     * Handling subscription.charged event
     * @param array $data Webook Data
     */
    protected function processSubscriptionCharged($data)
    {
        $paymentId = $data['payload']['payment']['entity']['id'];
        $subscriptionId = $data['payload']['subscription']['entity']['id'];
        $merchant_order_id = $data['payload']['subscription']['entity']['notes']['merchant_order_id'];
        $webhookSource = $data['payload']['subscription']['entity']['source'];
        $amount = number_format($data['payload']['payment']['entity']['amount'] / 100, 4, ".", "");

        $this->load->model('extension/payment/razorpay');

        // Process only if its from opencart subscription source
        if ($webhookSource == "opencart-subscription") {
            $subscription = $this->api->subscription->fetch($subscriptionId)->toArray();
            $rzpSubscription = $this->model_extension_payment_razorpay->getSubscriptionById($subscriptionId);

            if ($subscription['paid_count'] == 1) {
                if (
                    in_array($rzpSubscription['status'], ['created', 'authenticated']) and
                    $rzpSubscription['paid_count'] == 0
                ) {
                    $this->model_extension_payment_razorpay->updateSubscription($subscription, $subscriptionId);
                    $this->model_extension_payment_razorpay->updateOCRecurringStatus($merchant_order_id, 1);

                    $this->model_checkout_order->addOrderHistory($merchant_order_id, $this->config->get('payment_razorpay_order_status_id'), trim("Subscription charged Successfully. Razorpay Payment Id:" . $paymentId));
                }

                return;
            } else {
                $this->log->write("Subscription charged webhook event initiated for Opencart OrderID (:" . $merchant_order_id . ")");

                // Creating OC Recurring Transaction
                $ocRecurringData = $this->model_extension_payment_razorpay->getOCRecurringStatus($merchant_order_id);
                $this->model_extension_payment_razorpay->addOCRecurringTransaction($ocRecurringData['order_recurring_id'], $subscriptionId, $amount, "success");

                // Update RZP Subscription and OC subscription
                $this->model_extension_payment_razorpay->updateSubscription($subscription, $subscriptionId);
                $this->model_extension_payment_razorpay->updateOCRecurringStatus($merchant_order_id, 1);

                $this->model_checkout_order->addOrderHistory($merchant_order_id, $this->config->get('payment_razorpay_order_status_id'), trim("Subscription charged Successfully. Razorpay Payment Id:" . $paymentId));
                $this->log->write("Subscription charged webhook event finished for Opencart OrderID (:" . $merchant_order_id . ")");

                return;
            }
        }
    }

    /**
     * executing payment.authorized and order.paid webhook event using cron
     */
    public function rzpWebhookCron()
    {
        $this->load->model('checkout/order');
        $this->load->model('extension/payment/razorpay');

        $webhookEvents = $this->model_extension_payment_razorpay->getWebhookEvents(self::WEBHOOK_WAIT_TIME);
        foreach ($webhookEvents as $row) {
            $events = json_decode($row['rzp_webhook_data']);
            $rzpOrderId = $row['rzp_order_id'];
            foreach ($events as $event) {
                $event = (array) $event;
                switch ($event['event']) {
                    case self::PAYMENT_AUTHORIZED:
                        $this->paymentAuthorized($event);
                        break;
                    case self::ORDER_PAID:
                        $this->orderPaid($event);
                        break;
                    default:
                        return;
                }
                $this->model_extension_payment_razorpay->updateOrderForWebhook($event['opencart_order_id'], $rzpOrderId, 2);
            }
        }
    }

    public function confirm()
    {
        $json = array();

        if ($this->session->data['payment_method']['code'] == 'razorpay') {
            $this->load->model('checkout/order');

            // For multiple orders (split orders)
            if (is_array($this->session->data['order_id'])) {
                // We don't immediately update order status for Razorpay
                // as payment is not yet complete
                $json['success'] = true;
                $json['razorpay'] = true;

                // Get the first order ID for Razorpay reference
                // (payment will be processed through callback)
                $order_id = reset($this->session->data['order_id']);
                $json['order_id'] = $order_id;
            } else {
                // Single order
                $json['success'] = true;
                $json['razorpay'] = true;
                $json['order_id'] = $this->session->data['order_id'];
            }

            // No redirect here - the frontend JS will handle opening the Razorpay popup
        } else {
            $json['error'] = 'Payment method not valid';
        }

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }
}
