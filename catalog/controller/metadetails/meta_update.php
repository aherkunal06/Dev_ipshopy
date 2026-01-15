<?php
class ControllerMetadetailsMetaUpdate extends Controller {

    public function index() {
        set_time_limit(0);

        $this->load->model('catalog/product');
        $this->load->model('metadetails/meta_update');
        
        $log_file = DIR_LOGS . 'meta_update_log.txt';
        file_put_contents($log_file, "\n\n=== Starting Meta Update Batch at " . date('Y-m-d H:i:s') . " ===\n", FILE_APPEND);

        $limit = 20;
        $offset_file = DIR_LOGS . 'last_offset.txt';
        $start = file_exists($offset_file) ? (int)file_get_contents($offset_file) : 0;

        $total_updated = 0;
        $errors = [];
        $grand_input_tokens = 0;
        $grand_output_tokens = 0;

        while (true) {
            file_put_contents($log_file, "\n🟡 Starting batch at offset: $start\n", FILE_APPEND);

            $products = $this->model_catalog_product->getProducts([
                'start' => $start,
                'limit' => $limit,
                'filter_status' => 1
            ]);

            file_put_contents($log_file, "📊 Products found: " . count($products) . "\n", FILE_APPEND);

            if (empty($products)) {
                file_put_contents($log_file, "❌ No products returned. Ending batch.\n", FILE_APPEND);
                break;
            }

            $prompts = [];
            $product_map = [];
            $batch_updated = 0;
            $batch_errors = [];
            $consecutive_failures = 0; // 💥 Counter to stop after 10 failures

            foreach ($products as $product) {
                $product_id = $product['product_id'];
                $product_info = $this->model_catalog_product->getProduct($product_id);
                $language_id = (int)($product_info['language_id'] ?? 1);

                if (!$product_info || empty($product_info['name'])) {
                    $batch_errors[] = "Missing name for product ID: $product_id";
                    continue;
                }

                $sanitized_name = trim(htmlspecialchars($product_info['name']));
                $prompt = $this->buildPrompt($sanitized_name);
                $prompts[] = $prompt;
                $product_map[] = [
                    'product_id' => $product_id,
                    'language_id' => $language_id
                ];
            }

            list($responses, $input_tokens, $output_tokens) = $this->callChatGPTBatch($prompts);
            $grand_input_tokens += $input_tokens;
            $grand_output_tokens += $output_tokens;

            foreach ($responses as $i => $response) {
                $product_id = $product_map[$i]['product_id'];
                $language_id = $product_map[$i]['language_id'];

                if (!$response) {
                    $batch_errors[] = "No response from ChatGPT for product ID: $product_id";
                    $consecutive_failures++;
                    
                    if ($consecutive_failures >= 5) {
                        file_put_contents($log_file, "❌ Exceeded 5 consecutive failures. Stopping early to avoid credit loss.\n", FILE_APPEND);
                        break 2; // Exit both foreach and while
                    }
                    
                    continue;
                }

                // Clean up response in case of markdown or code blocks
                $clean = trim($response);
                
                // Remove ```json or ``` if present
                $clean = preg_replace('/^```(json)?/i', '', $clean);
                $clean = preg_replace('/```$/', '', $clean);
                $clean = trim($clean);
                
                // Now decode
                $meta = json_decode($clean, true);
                if (json_last_error() !== JSON_ERROR_NONE || !is_array($meta)) {
                    $batch_errors[] = "Invalid JSON response for product ID: $product_id";
    
                    // ⛔️ Mark as failed
                    $this->safeQuery("UPDATE " . DB_PREFIX . "product_description 
                        SET updated_status = 'Not Updated'
                        WHERE product_id = '" . (int)$product_id . "' 
                        AND language_id = '" . (int)$language_id . "'");
                        
                    $consecutive_failures++;
                    
                    continue;
                }

                $this->model_metadetails_meta_update->updateProductMeta(
                    $product_id,
                    $meta['meta_title'] ?? '',
                    $meta['meta_description'] ?? '',
                    $meta['meta_keywords'] ?? '',
                    $meta['tags'] ?? '',
                    $language_id
                );
                
                $this->safeQuery("UPDATE " . DB_PREFIX . "product_description 
                    SET updated_status = 'Updated', 
                    updated_date = NOW() 
                    WHERE product_id = '" . (int)$product_id . "' 
                    AND language_id = '" . (int)$language_id . "'");

                $batch_updated++;
                $consecutive_failures = 0;
                
                file_put_contents($log_file, date('Y-m-d H:i:s') . " - Updated product ID: $product_id\n", FILE_APPEND);
            }

            $total_updated += $batch_updated;
            $errors = array_merge($errors, $batch_errors);

            $estimated_cost = round(($input_tokens / 1000000 * 2.00) + ($output_tokens / 1000000 * 8.00), 4);

            file_put_contents($log_file, "✅ Batch done at offset $start: {$batch_updated} updated, " . count($batch_errors) . " errors\n📊 Tokens: Input=$input_tokens, Output=$output_tokens, Cost=~\${$estimated_cost}\n\n", FILE_APPEND);

            $start += $limit;
            file_put_contents($offset_file, $start);

             // 💡 Smart rate-limit backoff (throttle every batch)
            sleep(5); // Prevent TPM breach, adjust if using larger batches
        }

        $final_cost = round(($grand_input_tokens / 1000000 * 2.00) + ($grand_output_tokens / 1000000 * 8.00), 4);

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode([
            'status' => 'All product meta updated successfully',
            'products_updated' => $total_updated,
            'input_tokens' => $grand_input_tokens,
            'output_tokens' => $grand_output_tokens,
            'estimated_cost_usd' => $final_cost,
            'errors' => $errors
        ]));
    }
    
    
    public function resetOffset() {
        $offset_file = DIR_LOGS . 'last_offset.txt';
        file_put_contents($offset_file, '0');
        
        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode(['status' => 'Offset reset to 0']));
    }
    
    
    // public function index() {
    //      $log_file = '/home/ipshopy2/ipshopy.com/meta_update_log.txt';
    //     file_put_contents($log_file, "✅ index() function triggered by cron at " . date('Y-m-d H:i:s') . "\n", FILE_APPEND);
    //     $this->processProducts(10);
    // }

    // public function test50() {
    //     $this->processProducts(50); // for testing only 50 products
    // }

    // private function processProducts($limit = 50) {
    //     set_time_limit(0);
        
    //     $log_file = '/home/ipshopy2/ipshopy.com/meta_update_log.txt';
    //     file_put_contents($log_file, "🛠 processProducts() started with limit=$limit\n", FILE_APPEND);

    //     $this->load->model('catalog/product');
    //     $this->load->model('metadetails/meta_update');

    //     $start = 21;
    //     $total_updated = 0;
    //     $errors = [];
    //     $grand_input_tokens = 0;
    //     $grand_output_tokens = 0;

    //     while (true) {
    //         file_put_contents($log_file, "📦 Fetching products: start=$start, limit=$limit\n", FILE_APPEND);

    //         $products = $this->model_catalog_product->getProducts([
    //             'start' => $start,
    //             'limit' => $limit,
    //             'filter_status' => 1 // ✅ only active products
    //         ]);
            
    //         file_put_contents($log_file, "📊 Products found: " . count($products) . "\n", FILE_APPEND);

    //         if (empty($products)) {
    //             file_put_contents($log_file, "❌ No products returned. Ending batch.\n", FILE_APPEND);
    //             break;
    //         }

    //         $prompts = [];
    //         $product_map = [];
    //         $batch_updated = 0;
    //         $batch_errors = [];

    //         foreach ($products as $product) {
    //             $product_id = $product['product_id'];
    //             $product_info = $this->model_catalog_product->getProduct($product_id);
    //             $language_id = (int)($product_info['language_id'] ?? 1);

    //             if (!$product_info || empty($product_info['name'])) {
    //                 $batch_errors[] = "Missing name for product ID: $product_id";
    //                 continue;
    //             }
                
    //             file_put_contents($log_file, "📝 Preparing prompt for product ID: $product_id\n", FILE_APPEND);

    //             $sanitized_name = trim(htmlspecialchars($product_info['name']));
    //             $prompt = $this->buildPrompt($sanitized_name);
    //             $prompts[] = $prompt;
    //             $product_map[] = [
    //                 'product_id' => $product_id,
    //                 'language_id' => $language_id
    //             ];
    //         }

    //         list($responses, $input_tokens, $output_tokens) = $this->callChatGPTBatch($prompts);
    //         $grand_input_tokens += $input_tokens;
    //         $grand_output_tokens += $output_tokens;

    //         foreach ($responses as $i => $response) {
    //             $product_id = $product_map[$i]['product_id'];
    //             $language_id = $product_map[$i]['language_id'];

    //             if (!$response) {
    //                 $errors[] = "No response from ChatGPT for product ID: $product_id";
    //                 continue;
    //             }

    //             // Clean up response in case of markdown or code blocks
    //             $clean = trim($response);
                
    //             // Remove ```json or ``` if present
    //             $clean = preg_replace('/^```(json)?/i', '', $clean);
    //             $clean = preg_replace('/```$/', '', $clean);
    //             $clean = trim($clean);
                
    //             // Now decode
    //             $meta = json_decode($clean, true);

    //             if (json_last_error() !== JSON_ERROR_NONE || !is_array($meta)) {
    //                 $batch_errors[] = "Invalid JSON response for product ID: $product_id";
                    
    //                 // ⛔️ Mark as failed
    //                 $this->db->query("UPDATE " . DB_PREFIX . "product_description 
    //                     SET updated_status = 'Not Updated'
    //                     WHERE product_id = '" . (int)$product_id . "' 
    //                     AND language_id = '" . (int)$language_id . "'");
                        
    //                 continue;
    //             }

    //             $this->model_metadetails_meta_update->updateProductMeta(
    //                 $product_id,
    //                 $meta['meta_title'] ?? '',
    //                 $meta['meta_description'] ?? '',
    //                 $meta['meta_keywords'] ?? '',
    //                 $meta['tags'] ?? '',
    //                 $language_id
    //             );

    //             $batch_updated++;
    //             file_put_contents($log_file, date('Y-m-d H:i:s') . " - Updated product ID: $product_id\n", FILE_APPEND);
    //         }

    //         $total_updated += $batch_updated;
    //         $errors = array_merge($errors, $batch_errors);

    //         $estimated_cost = round(($input_tokens / 1000000 * 2.00) + ($output_tokens / 1000000 * 8.00), 4);

    //         file_put_contents($log_file, "✅ Batch done at offset $start: {$batch_updated} updated, " . count($batch_errors) . " errors\n📊 Tokens: Input=$input_tokens, Output=$output_tokens, Cost=~\${$estimated_cost}\n\n", FILE_APPEND);

    //         break; // Only run once for this limited test batch
    //     }

    //     $final_cost = round(($grand_input_tokens / 1000000 * 2.00) + ($grand_output_tokens / 1000000 * 8.00), 4);

    //     $this->response->addHeader('Content-Type: application/json');
    //     $this->response->setOutput(json_encode([
    //         'status' => 'Limited product meta update complete.',
    //         'products_updated' => $total_updated,
    //         'input_tokens' => $grand_input_tokens,
    //         'output_tokens' => $grand_output_tokens,
    //         'estimated_cost_usd' => $final_cost,
    //         'errors' => $errors
    //     ]));
    // }


    private function buildPrompt($productName) {
        return '
        You are an expert SEO specialist.
        Based on the product name "' . $productName . '", perform the following tasks:

        Step 1: SEO Keyword Research
        - Provide Top 15 most searched keywords related to the product.
        - Provide Top 5 long-tail keywords related to the product (high purchase intent).

        Step 2: Meta Tags Creation
        - Meta Tag Title (under 60 characters) using primary keywords and important long-tail keywords.
        - Meta Tag Description (under 160 characters) using primary and long-tail keywords. Do not start with "Shop" or "Discover."
        - Meta Tag Keywords: comma-separated list of all 15+5 keywords.

        Step 3: Product Tags
        - Provide a comma-separated list of relevant 20 product tags based on the above keywords.

        Important: 
        Return only a valid JSON in this structure:
        {
          "top_keywords": "keyword1, keyword2, keyword3, ...",
          "long_tail_keywords": "long-tail1, long-tail2, ...",
          "meta_title": "Your Meta Title here",
          "meta_description": "Your Meta Description here",
          "meta_keywords": "keyword1, keyword2, keyword3, long-tail1, long-tail2, ...",
          "tags": "tag1, tag2, tag3, ...",
        }';
    }


    // private function estimateTokens($text) {
    //     return ceil(strlen($text) / 4); // Rough estimate: 4 characters ≈ 1 token
    // }
    

    private function callChatGPTBatch($prompts) {

        $api_key = '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';
        
        $mh = curl_multi_init();
        $handles = [];
        $results = [];
        $input_tokens = 0;
        $output_tokens = 0;
        
        $global_failures = 0;
        $max_global_failures = 10; // Prevent full burn

        foreach ($prompts as $index => $prompt) {
            $ch = curl_init();
            curl_setopt_array($ch, [
                CURLOPT_URL => 'https://api.openai.com/v1/chat/completions',
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_POST => true,
                CURLOPT_HTTPHEADER => [
                    'Content-Type: application/json',
                    'Authorization: Bearer ' . $api_key,
                ],
                CURLOPT_POSTFIELDS => json_encode([
                    'model' => 'gpt-4-1106-preview',
                    'messages' => [
                        ['role' => 'system', 'content' => 'You are an SEO expert. Return strict JSON only.'],
                        ['role' => 'user', 'content' => $prompt],
                    ],
                    'temperature' => 0.7
                ])
            ]);
            curl_multi_add_handle($mh, $ch);
            $handles[$index] = $ch;
        }
        do {
            $status = curl_multi_exec($mh, $running);
            if ($running) {
                usleep(100000); // 100ms sleep to reduce load
                curl_multi_select($mh);
            }
        } while ($running && $status == CURLM_OK);

        foreach ($handles as $index => $ch) {
            $response = curl_multi_getcontent($ch);
            $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

            if ($http_code === 200 && $response) {
                $decoded = json_decode($response, true);
                $content = $decoded['choices'][0]['message']['content'] ?? null;
                $input_tokens += $decoded['usage']['prompt_tokens'] ?? 0;
                $output_tokens += $decoded['usage']['completion_tokens'] ?? 0;
                $results[$index] = $content;
            } elseif ($http_code === 429) {
                $global_failures++;
                file_put_contents($log_file, "⚠️ Batch request $index got 429. Attempting retry...\n", FILE_APPEND);
                
                if ($global_failures >= $max_global_failures) {
                    file_put_contents($log_file, "❌ Too many 429 rate limits. Aborting batch early.\n", FILE_APPEND);
                    break;
                }
                
                sleep(10);
                $results[$index] = $this->retryChatGPTPrompt($prompts[$index], 1); // Reduce retries
            } else {
                $results[$index] = $this->retryChatGPTPrompt($prompts[$index]);
            }
            curl_multi_remove_handle($mh, $ch);
            curl_close($ch);
        }

        curl_multi_close($mh);
        return [$results, $input_tokens, $output_tokens];
    }
    

    private function retryChatGPTPrompt($prompt, $attempts = 1) {
        
        $log_file = DIR_LOGS . 'meta_update_log.txt';

        $api_key = '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';

        $url = 'https://api.openai.com/v1/chat/completions';
    
        $delay = 10;
        for ($i = 1; $i <= $attempts; $i++) {
            $ch = curl_init();
            curl_setopt_array($ch, [
                CURLOPT_URL => $url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_POST => true,
                CURLOPT_TIMEOUT => 30,
                CURLOPT_HTTPHEADER => [
                    'Content-Type: application/json',
                    'Authorization: Bearer ' . $api_key,
                ],
                CURLOPT_POSTFIELDS => json_encode([
                    'model' => 'gpt-4-1106-preview',
                    'messages' => [
                        ['role' => 'system', 'content' => 'You are an SEO expert. Return strict JSON only.'],
                        ['role' => 'user', 'content' => $prompt],
                    ],
                    'temperature' => 0.7
                ])
            ]);

            $response = curl_exec($ch);
            $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($http_code === 200 && $response) {
                $json = json_decode($response, true);
                return $json['choices'][0]['message']['content'] ?? null;
            }
            
            // 💡 Rate Limit Handling
            if ($http_code === 429) {
                file_put_contents($log_file, "⏳ Retry $i failed due to rate limit (429). Sleeping for {$delay}s...\n", FILE_APPEND);
                sleep($delay);
                $delay *= 2; // exponential backoff
                continue;
            }

            file_put_contents($log_file, "🔁 Retry $i failed for prompt.\n", FILE_APPEND);
            sleep(3);
        }

        return null;
    }
    
    
    // public function testCron() {
    //     $log_file = '/home/ipshopy2/ipshopy.com/cron_test_output.txt';
    
    //     $timestamp = date('Y-m-d H:i:s');
    //     $message = "✅ testCron() ran at {$timestamp}\n";
    
    //     file_put_contents($log_file, $message, FILE_APPEND);
    
    //     $this->response->addHeader('Content-Type: application/json');
    //     $this->response->setOutput(json_encode(['status' => 'Cron test executed', 'time' => $timestamp]));
    // }
    
    
    private function safeQuery($sql) {
        $log_file = DIR_LOGS . 'meta_update_log.txt';
        
        try {
            return $this->db->query($sql);
        } catch (Exception $e) {
            if (strpos($e->getMessage(), 'MySQL server has gone away') !== false) {
                file_put_contents($log_file, date('Y-m-d H:i:s') . " - ⚠️ MySQL disconnected. Reconnecting...\n", FILE_APPEND);
                $this->registry->set('db', new \DB(DB_DRIVER, DB_HOSTNAME, DB_USERNAME, DB_PASSWORD, DB_DATABASE));
                return $this->db->query($sql); // retry once
            } else {
                throw $e;
            }
        }
    }

}
