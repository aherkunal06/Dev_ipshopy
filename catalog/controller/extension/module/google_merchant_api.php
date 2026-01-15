<?php


class ControllerExtensionModuleGoogleMerchantApi extends Controller

{


    private $systemLibrary = DIR_SYSTEM . 'library/google_merchant_api/';
    private $redirectUri = HTTPS_SERVER . 'index.php?route=extension/module/google_merchant_api';

    public function index()
    {


        include_once $this->systemLibrary . "vendor/autoload.php";

        $code =     $this->request->get;

        if (isset($code['code'])) {

            $client = new \Google_Client();

            $client->setAuthConfig($this->systemLibrary . "client_secret.json");

            $client->setRedirectUri($this->redirectUri);


            // $ip = $_SERVER['REMOTE_ADDR'];


            $config_code["module_content_api_code"] = $code['code'];

            $this->editSetting("module_content_api_code", $config_code);


            $authCode = $code['code'];
            // Exchange authorization code for an access token.
            $accessToken = $client->fetchAccessTokenWithAuthCode($authCode);

            // if (!file_exists(dirname($this->systemLibrary . "token.json"))) {
            //     mkdir(dirname($this->systemLibrary . "token.json"), 0700, true);
            // }

            file_put_contents($this->systemLibrary . "token.json", json_encode($accessToken));

            header('Location: ' .        HTTP_SERVER . 'admin/index.php?route=extension/module/google_merchant_api' . '&user_token=' . $this->session->data['user_token']);
        } else {

            exit('No code found');
        }
    }

    public function editSetting(string $code, array $data, int $store_id = 0): void
    {
        $this->db->query("DELETE FROM `" . DB_PREFIX . "setting` WHERE `store_id` = '" . (int)$store_id . "' AND `code` = '" . $this->db->escape($code) . "'");

        foreach ($data as $key => $value) {
            if (substr($key, 0, strlen($code)) == $code) {
                if (!is_array($value)) {
                    $this->db->query("INSERT INTO `" . DB_PREFIX . "setting` SET `store_id` = '" . (int)$store_id . "', `code` = '" . $this->db->escape($code) . "', `key` = '" . $this->db->escape($key) . "', `value` = '" . $this->db->escape($value) . "'");
                } else {
                    $this->db->query("INSERT INTO `" . DB_PREFIX . "setting` SET `store_id` = '" . (int)$store_id . "', `code` = '" . $this->db->escape($code) . "', `key` = '" . $this->db->escape($key) . "', `value` = '" . $this->db->escape(json_encode($value)) . "', `serialized` = '1'");
                }
            }
        }
    }
}
