<?php
class ControllerPromptChatGpt extends Controller {
    public function index() {
        $output = '';
        $input_text = '';

        if ($this->request->server['REQUEST_METHOD'] === 'POST') {
            $input_text = $this->request->post['prompt'] ?? '';
            $input_text = trim($input_text);

            if (empty($input_text)) {
                $output = 'Prompt cannot be empty.';
            } else {
                $api_key = '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';
                $url = 'https://api.openai.com/v1/chat/completions';

                $postData = [
                    'model' => 'gpt-4-1106-preview',
                    'messages' => [
                        ['role' => 'system', 'content' => 'You are a strict JSON generator. Return ONLY JSON, no explanations, no Markdown, no labels, no extra formatting. Start with { and end with } only.'],
                        ['role' => 'user', 'content' => $input_text]
                    ],
                    'temperature' => 0.7,
                    'stop' => ['\n\n', 'Product Name:', 'Meta title:'] // Optional, can prevent markdown style
                ];

                $headers = [
                    'Content-Type: application/json',
                    'Authorization: Bearer ' . $api_key
                ];

                $ch = curl_init($url);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($postData));
                curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

                $response = curl_exec($ch);

                file_put_contents(DIR_LOGS . 'prompt_debug.log', "==== RAW RESPONSE ====\n" . $response . "\n", FILE_APPEND);

                if (curl_errno($ch)) {
                    $output = json_encode(['error' => 'cURL Error: ' . curl_error($ch)]);
                } else {
                    $result = json_decode($response, true);

                    if (isset($result['choices'][0]['message']['content'])) {
                        $jsonContent = trim($result['choices'][0]['message']['content']);

                        // Clean possible markdown or warnings
                        $jsonContent = strip_tags($jsonContent);
                        $jsonContent = preg_replace('/^.*?(?=\{)/s', '', $jsonContent); // Remove anything before first {
                        $jsonContent = preg_replace('/```json|```/i', '', $jsonContent);
                        $jsonContent = trim($jsonContent);

                        file_put_contents(DIR_LOGS . 'prompt_cleaned.log', "==== CLEANED CONTENT ====\n" . $jsonContent . "\n", FILE_APPEND);

                        $decoded = json_decode($jsonContent, true);

                        if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                            $output = json_encode($decoded);
                        } else {
                            $output = json_encode([
                                'error' => 'Invalid JSON from GPT',
                                'raw'   => $jsonContent
                            ]);
                        }
                    } elseif (isset($result['error']['message'])) {
                        $output = json_encode(['error' => 'API Error: ' . $result['error']['message']]);
                    } else {
                        $output = json_encode(['error' => 'Unexpected API response', 'raw' => $response]);
                    }
                }

                curl_close($ch);
            }

            // AJAX response
            if (!empty($this->request->server['HTTP_X_REQUESTED_WITH']) &&
                strtolower($this->request->server['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {

                $this->response->addHeader('Content-Type: application/json');

                $decoded = json_decode($output, true);

                if (!is_array($decoded)) {
                    $this->response->setOutput(json_encode(['error' => 'Invalid JSON output structure', 'raw' => $output]));
                    return;
                }

                // Sanitize SEO URL if present
                if (isset($decoded['seo_url'])) {
                    $seo_url = trim(strtolower($decoded['seo_url']));
                    $seo_url = preg_replace('/^(buy|shop|get|order)-/i', '', $seo_url);
                    $seo_url = preg_replace('/[^a-z0-9-]+/', '-', $seo_url);
                    $seo_url = trim($seo_url, '-');
                    $random_suffix = substr(str_shuffle('abcdefghijklmnopqrstuvwxyz0123456789'), 0, 4);
                    $decoded['seo_url'] = $seo_url . '-' . $random_suffix;
                }

                file_put_contents(DIR_LOGS . 'prompt_output.log', "==== FINAL OUTPUT ====\n" . print_r($decoded, true) . "\n", FILE_APPEND);
                $this->response->setOutput(json_encode($decoded));
                return;
            }
        }

        // Fallback manual UI access (optional for GET)
        $data['title'] = 'Chat with GPT';
        $data['action'] = $this->url->link('prompt/chatgpt', '', true);
        $data['input'] = $input_text;
        $data['output'] = $output;

        $this->response->setOutput($this->load->view('prompt/chatgpt', $data));
    }
    
}
