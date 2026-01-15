<?php
class ControllerExtensionFeedPSGoogleSitemap extends Controller
{
    /**
     * @var string The support email address.
     */
    const EXTENSION_EMAIL = 'support@playfulsparkle.com';

    /**
     * @var string The documentation URL for the extension.
     */
    const EXTENSION_DOC = 'https://github.com/playfulsparkle/oc3_google_sitemap.git';

    private $error = array();

    /**
     * Displays the Google Sitemap settings page.
     *
     * This method loads the necessary language file, sets the title of the page,
     * and prepares the data for the view. It also generates the breadcrumbs for
     * navigation and retrieves configuration settings for the sitemap.
     *
     * @return void
     */
    public function index()
    {
        $this->load->language('extension/feed/ps_google_sitemap');

        $this->document->setTitle($this->language->get('heading_title'));

        $this->load->model('setting/setting');

        if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
            $this->model_setting_setting->editSetting('feed_ps_google_sitemap', $this->request->post, $this->request->get['store_id']);

            $this->session->data['success'] = $this->language->get('text_success');

            $this->response->redirect($this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=feed', true));
        }

        if (isset($this->error['warning'])) {
            $data['error_warning'] = $this->error['warning'];
        } else {
            $data['error_warning'] = '';
        }

        if (isset($this->error['input_max_product_images'])) {
            $data['error_input_max_product_images'] = $this->error['input_max_product_images'];
        } else {
            $data['error_input_max_product_images'] = '';
        }


        if (isset($this->request->get['store_id'])) {
            $store_id = (int) $this->request->get['store_id'];
        } else {
            $store_id = 0;
        }


        $data['breadcrumbs'] = array();

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_extension'),
            'href' => $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=feed', true)
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('heading_title'),
            'href' => $this->url->link('extension/feed/ps_google_sitemap', 'user_token=' . $this->session->data['user_token'] . '&store_id=' . $store_id, true)
        );

        $data['action'] = $this->url->link('extension/feed/ps_google_sitemap', 'user_token=' . $this->session->data['user_token'] . '&store_id=' . $store_id, true);

        $data['cancel'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=feed', true);

        $data['user_token'] = $this->session->data['user_token'];

        if (isset($this->request->post['feed_ps_google_sitemap_status'])) {
            $data['feed_ps_google_sitemap_status'] = (bool) $this->request->post['feed_ps_google_sitemap_status'];
        } else {
            $data['feed_ps_google_sitemap_status'] = (bool) $this->model_setting_setting->getSettingValue('feed_ps_google_sitemap_status', $store_id);
        }

        if (isset($this->request->post['feed_ps_google_sitemap_product'])) {
            $data['feed_ps_google_sitemap_product'] = (bool) $this->request->post['feed_ps_google_sitemap_product'];
        } else {
            $data['feed_ps_google_sitemap_product'] = (bool) $this->model_setting_setting->getSettingValue('feed_ps_google_sitemap_product', $store_id);
        }

        if (isset($this->request->post['feed_ps_google_sitemap_product_images'])) {
            $data['feed_ps_google_sitemap_product_images'] = (bool) $this->request->post['feed_ps_google_sitemap_product_images'];
        } else {
            $data['feed_ps_google_sitemap_product_images'] = (bool) $this->model_setting_setting->getSettingValue('feed_ps_google_sitemap_product_images', $store_id);
        }

        if (isset($this->request->post['feed_ps_google_sitemap_max_product_images'])) {
            $data['feed_ps_google_sitemap_max_product_images'] = (int) $this->request->post['feed_ps_google_sitemap_max_product_images'];
        } else {
            $data['feed_ps_google_sitemap_max_product_images'] = (int) $this->model_setting_setting->getSettingValue('feed_ps_google_sitemap_max_product_images', $store_id);
        }

        if (isset($this->request->post['feed_ps_google_sitemap_category'])) {
            $data['feed_ps_google_sitemap_category'] = (bool) $this->request->post['feed_ps_google_sitemap_category'];
        } else {
            $data['feed_ps_google_sitemap_category'] = (bool) $this->model_setting_setting->getSettingValue('feed_ps_google_sitemap_category', $store_id);
        }

        if (isset($this->request->post['feed_ps_google_sitemap_category_images'])) {
            $data['feed_ps_google_sitemap_category_images'] = (bool) $this->request->post['feed_ps_google_sitemap_category_images'];
        } else {
            $data['feed_ps_google_sitemap_category_images'] = (bool) $this->model_setting_setting->getSettingValue('feed_ps_google_sitemap_category_images', $store_id);
        }

        if (isset($this->request->post['feed_ps_google_sitemap_manufacturer'])) {
            $data['feed_ps_google_sitemap_manufacturer'] = (bool) $this->request->post['feed_ps_google_sitemap_manufacturer'];
        } else {
            $data['feed_ps_google_sitemap_manufacturer'] = (bool) $this->model_setting_setting->getSettingValue('feed_ps_google_sitemap_manufacturer', $store_id);
        }

        if (isset($this->request->post['feed_ps_google_sitemap_manufacturer_images'])) {
            $data['feed_ps_google_sitemap_manufacturer_images'] = (bool) $this->request->post['feed_ps_google_sitemap_manufacturer_images'];
        } else {
            $data['feed_ps_google_sitemap_manufacturer_images'] = (bool) $this->model_setting_setting->getSettingValue('feed_ps_google_sitemap_manufacturer_images', $store_id);
        }

        if (isset($this->request->post['feed_ps_google_sitemap_information'])) {
            $data['feed_ps_google_sitemap_information'] = (bool) $this->request->post['feed_ps_google_sitemap_information'];
        } else {
            $data['feed_ps_google_sitemap_information'] = (bool) $this->model_setting_setting->getSettingValue('feed_ps_google_sitemap_information', $store_id);
        }

        $this->load->model('localisation/language');

        $languages = $this->model_localisation_language->getLanguages();

        $data['languages'] = $languages;

        $data['store_id'] = $store_id;

        $data['stores'] = array();

        $data['stores'][] = array(
            'store_id' => 0,
            'name' => $this->config->get('config_name') . '&nbsp;' . $this->language->get('text_default'),
            'href' => $this->url->link('extension/feed/ps_google_sitemap', 'user_token=' . $this->session->data['user_token'] . '&store_id=0'),
        );

        $this->load->model('setting/store');

        $stores = $this->model_setting_store->getStores();

        $store_url = HTTP_CATALOG;

        foreach ($stores as $store) {
            $data['stores'][] = array(
                'store_id' => $store['store_id'],
                'name' => $store['name'],
                'href' => $this->url->link('extension/feed/ps_google_sitemap', 'user_token=' . $this->session->data['user_token'] . '&store_id=' . $store['store_id']),
            );

            if ((int) $store['store_id'] === $store_id) {
                $store_url = $store['url'];
            }
        }

        $data['data_feed_seo_urls'] = array();
        $data['data_feed_urls'] = array();

        $htaccess_mod = array();

        foreach ($languages as $language) {
            $feed_seo_url = rtrim($store_url, '/') . '/' . $language['code'] . '/sitemap.xml';
            $feed_url = rtrim($store_url, '/') . '/index.php?route=extension/feed/ps_google_sitemap&language=' . $language['code'];

            $data['data_feed_seo_urls'][$language['language_id']] = $feed_seo_url;
            $data['data_feed_urls'][$language['language_id']] = $feed_url;

            $htaccess_mod[] = 'RewriteRule ^' . $language['code'] . '/sitemap.xml$ index.php?route=extension/feed/ps_google_sitemap&language=' . $language['code'] . ' [L]';
        }

        $data['htaccess_mod'] = implode(PHP_EOL, $htaccess_mod);

        $data['user_agents'] = array(
            '*' => $this->language->get('text_user_agent_any'),
            'Googlebot' => 'Googlebot',
            'Googlebot-Image' => 'Googlebot Image',
            'Googlebot-Video' => 'Googlebot Video',
            'Googlebot-News' => 'Googlebot News',
            'Bingbot' => 'Bingbot',
            'msnbot' => 'MSNBot',
            'YandexBot' => 'YandexBot',
            'YandexImages' => 'YandexImages',
            'YandexVideo' => 'YandexVideo',
            'YandexMedia' => 'YandexMedia',
            'Baiduspider' => 'BaiduSpider',
            'SeznamBot' => 'SeznamBot',
        );

        $data['text_contact'] = sprintf($this->language->get('text_contact'), self::EXTENSION_EMAIL, self::EXTENSION_EMAIL, self::EXTENSION_DOC);

        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');

        $this->response->setOutput($this->load->view('extension/feed/ps_google_sitemap', $data));
    }

    protected function validate()
    {
        if (!$this->user->hasPermission('modify', 'extension/feed/ps_google_sitemap')) {
            $this->error['warning'] = $this->language->get('error_permission');
        }

        if (!$this->error && !isset($this->request->post['store_id'])) {
            $this->error['warning'] = $this->language->get('error_store_id');
        }

        if (!$this->error && $this->request->post['feed_ps_google_sitemap_max_product_images'] < 0) {
            $this->error['input_max_product_images'] = $this->language->get('error_max_product_images_min');
        }

        return !$this->error;
    }

    public function install()
    {
        $this->load->model('setting/setting');

        $data = array(
            'feed_ps_google_sitemap_max_product_images' => 1,
        );

        $this->model_setting_setting->editSetting('feed_ps_google_sitemap', $data);
    }

    public function uninstall()
    {

    }
    private function _validateRobotsTxt($testUserAgent, $urls)
    {
        $results = [];

        // Path to robots.txt
        $robotsTxt = dirname(DIR_SYSTEM) . '/robots.txt';

        // Read the robots.txt file lines
        $lines = file($robotsTxt);

        // If the file is not readable, assume no URLs are blocked
        if (false === $lines) {
            foreach ($urls as $url) {
                $results[$url] = false; // No blocking when no robots.txt is found
            }
            return $results;
        }

        // Iterate through each URL to check
        foreach ($urls as $url) {
            $parsedUrl = parse_url($url);
            $path = $parsedUrl['path'] . (isset($parsedUrl['query']) ? '?' . $parsedUrl['query'] : '');

            // Variables to track user-agent and blocking status
            $userAgent = null;
            $isBlocked = false;
            $disallowedPaths = [];

            // Check each line in robots.txt
            foreach ($lines as $line) {
                $line = trim($line);

                // Skip empty lines or comments
                if (empty($line) || $line[0] == '#') {
                    continue;
                }

                // Check if it's a User-agent directive
                if (strpos($line, 'User-agent:') === 0) {
                    $userAgent = trim(substr($line, 11)); // Extract user-agent
                    continue; // Move to the next line
                }

                // If user-agent is test user-agent or wildcard '*', process the Disallow
                if ($userAgent === $testUserAgent || $userAgent === '*' || $userAgent === null) {
                    if (strpos($line, 'Disallow:') === 0) {
                        $disallowedPath = trim(substr($line, 9)); // Extract disallowed path
                        $disallowedPaths[] = $disallowedPath; // Store disallowed paths
                    }
                }
            }

            // Check if any of the disallowed paths match the current URL
            foreach ($disallowedPaths as $disallowedPath) {
                $regexPattern = $this->convertToRegex($disallowedPath);
                if (preg_match($regexPattern, $path)) {
                    $isBlocked = true;
                    break; // Stop checking if the URL is already blocked
                }
            }

            // Store the result for this URL
            $results[$url] = $isBlocked ? 'text_disallowed' : 'text_allowed';
        }

        return $results; // Return the array of results for each URL
    }

    /**
     * Converts a Disallow pattern to a regular expression
     * This function handles basic wildcard conversion like * and $
     *
     * @param string $disallowedPath
     * @return string
     */
    private function convertToRegex($disallowedPath)
    {
        // Escape any regular expression special characters
        $disallowedPath = preg_quote($disallowedPath, '/');

        // Replace wildcard '*' with '.*' to match any number of characters
        $disallowedPath = str_replace('\*', '.*', $disallowedPath);

        // Replace '$' with '\z' to match the end of the string
        $disallowedPath = str_replace('\$', '\z', $disallowedPath);

        // Make sure the regular expression matches the entire path (not just a part of it)
        return '/^' . $disallowedPath . '/';
    }

    public function validaterobotstxt()
    {
        $this->load->language('extension/feed/ps_google_sitemap');

        $json = [];

        if (!$this->user->hasPermission('modify', 'extension/feed/ps_google_sitemap')) {
            $json['error'] = $this->language->get('error_permission');
        }

        $this->load->model('localisation/language');

        if (isset($this->request->get['store_id'])) {
            $store_id = (int) $this->request->get['store_id'];
        } else {
            $store_id = 0;
        }

        if (isset($this->request->get['user_agent'])) {
            $user_agent = $this->request->get['user_agent'];
        } else {
            $user_agent = '*';
        }

        $store_url = HTTP_CATALOG;

        if (!$json) {

            $feed_seo_urls = array();

            $languages = $this->model_localisation_language->getLanguages();

            foreach ($languages as $language) {
                $feed_seo_urls[] = rtrim($store_url, '/') . '/index.php?route=extension/feed/ps_google_sitemap&language=' . $language['code'];
            }

            foreach ($languages as $language) {
                $feed_seo_urls[] = rtrim($store_url, '/') . '/' . $language['code'] . '/sitemap.xml';
            }

            $results = array();

            $validationResults = $this->_validateRobotsTxt($user_agent, $feed_seo_urls);

            foreach ($validationResults as $feed_seo_url => $translation) {
                $results[] = sprintf($this->language->get($translation), $feed_seo_url);
            }

            $json['results'] = implode(PHP_EOL, $results);
        } else {
            $json['results'] = '';
        }

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }

    private function _patchHtaccess()
    {
        $htaccess_filename = dirname(DIR_SYSTEM) . '/.htaccess';

        if (false === $lines = file($htaccess_filename)) {
            return false;
        }

        $this->load->model('localisation/language');

        $languages = $this->model_localisation_language->getLanguages();

        $rules = [];

        foreach ($languages as $language) {
            $canAddRule = true;

            $rule = 'RewriteRule ^' . $language['code'] . '/sitemap.xml$ index.php?route=extension/feed/ps_google_sitemap&language=' . $language['code'] . ' [L]';

            foreach ($lines as $line) {
                if (strpos($line, $rule) !== false) {
                    $canAddRule = false;
                }
            }

            if ($canAddRule) {
                $rules[] = $rule;
            }
        }

        $new_content = '';
        $foundRewriteEngine = false;

        foreach ($lines as $line) {
            $new_content .= $line;

            if (trim($line) === 'RewriteEngine On' && !$foundRewriteEngine) {
                $foundRewriteEngine = true;

                foreach ($rules as $rule) {
                    $new_content .= $rule . PHP_EOL;
                }
            }
        }

        if ($rules && !empty($new_content)) {
            return file_put_contents($htaccess_filename, $new_content) !== false;
        }

        return true;
    }

    public function patchhtaccess()
    {
        $this->load->language('extension/feed/ps_google_sitemap');

        $json = array();

        if (!$this->user->hasPermission('modify', 'extension/feed/ps_google_sitemap')) {
            $json['error'] = $this->language->get('error_permission');
        }

        if (!$json) {
            if (!$this->_patchHtaccess()) {
                $json['error'] = $this->language->get('error_htaccess_update');
            } else {
                $json['success'] = $this->language->get('text_htaccess_update_success');
            }
        }

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }

}
