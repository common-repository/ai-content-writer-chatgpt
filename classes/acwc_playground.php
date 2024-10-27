<?php
namespace ACWC;
if ( ! defined( 'ABSPATH' ) ) exit;
if(!class_exists('\\ACWC\\ACWC_Playground')) {
    class ACWC_Playground
    {
        private static  $instance = null ;

        public static function get_instance()
        {
            if ( is_null( self::$instance ) ) {
                self::$instance = new self();
            }
            return self::$instance;
        }

        public function __construct()
        {
            add_action('init',[$this,'acwc_stream'],1);
            add_action( 'wp_ajax_acwc_comparison', array( $this, 'acwc_comparison' ) );
        }

        public function acwc_comparison()
        {
            $acwc_result = array('status' => 'error');
            if ( ! wp_verify_nonce( $_POST['_wpnonce'], 'acwc_comparison_generator' ) ) {
                $acwc_result['msg'] = WP_OPENAI_CG_NONCE_ERROR;
                wp_send_json($acwc_result);
            }
            $open_ai = ACWCGPT::get_instance()->acwc();
            if(!$open_ai){
                $acwc_result['msg'] = 'Missing API Setting';
                wp_send_json($acwc_result);
                exit;
            }
            $acwc_generator = ACWC_Generator::get_instance();
            $acwc_generator->acwc($open_ai);
            $model = sanitize_text_field($_REQUEST['model']);
            $prompt = sanitize_text_field($_REQUEST['prompt']);
            $temperature = sanitize_text_field($_REQUEST['temperature']);
            $max_tokens = sanitize_text_field($_REQUEST['max_tokens']);
            $top_p = sanitize_text_field($_REQUEST['top_p']);
            $frequency_penalty = sanitize_text_field($_REQUEST['frequency_penalty']);
            $presence_penalty = sanitize_text_field($_REQUEST['presence_penalty']);
            $complete = $acwc_generator->acwc_request([
                'model' => $model,
                'prompt' => $prompt,
                'temperature' => (float)$temperature,
                'max_tokens' => (float)$max_tokens,
                'frequency_penalty' => (float)$frequency_penalty,
                'presence_penalty' => (float)$presence_penalty,
                'top_p' => (float)$top_p
            ]);
            if($complete['status'] == 'error'){
                $acwc_result['msg'] = $complete['msg'];
            }
            else{
                $acwc_estimated = 0;
                $acwc_result['text'] = $complete['data'];
                $acwc_result['text'] = str_replace("\\",'',$acwc_result['text']);
                $acwc_result['tokens'] = $complete['tokens'];
                $acwc_result['words'] = $complete['length'];
                if($model === 'gpt-3.5-turbo') {
                    $acwc_estimated = 0.002 * $acwc_result['tokens'] / 1000;
                }
                if($model === 'gpt-4') {
                    $acwc_estimated = 0.06 * $acwc_result['tokens'] / 1000;
                }
                if($model === 'gpt-4-32k') {
                    $acwc_estimated = 0.12 * $acwc_result['tokens'] / 1000;
                }
                else{
                    $acwc_estimated = 0.02 * $acwc_result['tokens'] / 1000;
                }
                $acwc_result['cost'] = $acwc_estimated;
                $acwc_result['status'] = 'success';
            }
            wp_send_json($acwc_result);
        }

        public function acwc_stream()
        {
            if(isset($_GET['acwc_stream']) && sanitize_text_field($_GET['acwc_stream']) == 'yes'){
			
                global $wpdb;
                header('Content-type: text/event-stream');
                header('Cache-Control: no-cache');
                if ( ! wp_verify_nonce( $_REQUEST['nonce'], 'acwc-ajax-nonce' ) ) {
                    $words = explode(' ', WP_OPENAI_CG_NONCE_ERROR);
                    $words[count($words) + 1] = '[LIMITED]';
                    foreach ($words as $key => $word) {
                        echo "event: message\n";
                        if ($key == 0) {
                            echo 'data: {"choices":[{"delta":{"content":"' . $word . '"}}]}';
                        } else {
                            if ($word == '[LIMITED]') {
                                echo 'data: [LIMITED]';
                            } else {
                                echo 'data: {"choices":[{"delta":{"content":" ' . $word . '"}}]}';
                            }
                        }
                        echo "\n\n";
                        ob_end_flush();
                        flush();
                    }
                }
                else {
                    if (isset($_REQUEST['title']) && !empty($_REQUEST['title'])) {
                        $acwc_prompt = sanitize_text_field($_REQUEST['title']);
                        $acwc = \ACWC\ACWCGPT::get_instance()->acwc();
                        if ($acwc) {
                            $acwc_limited_tokens = false;
                            $acwc_args = array(
                                'prompt' => $acwc_prompt,
                                'temperature' => (float)$acwc->acwc_temperature,
                                "max_tokens" => (float)$acwc->acwc_max_token,
                                "frequency_penalty" => (float)$acwc->acwc_frequency_penalty,
                                "presence_penalty" => (float)$acwc->acwc_presence_penalty,
                                "stream" => true
                            );
                            if (isset($_REQUEST['temperature']) && !empty($_REQUEST['temperature'])) {
                                $acwc_args['temperature'] = (float)sanitize_text_field($_REQUEST['temperature']);
                            }
                            if (isset($_REQUEST['engine']) && !empty($_REQUEST['engine'])) {
                                $acwc_args['model'] = sanitize_text_field($_REQUEST['engine']);
                            } else {
                                $acwc_args['model'] = 'gpt-3.5-turbo';
                            }
                            if (isset($_REQUEST['max_tokens']) && !empty($_REQUEST['max_tokens'])) {
                                $acwc_args['max_tokens'] = (float)sanitize_text_field($_REQUEST['max_tokens']);
                            }
                            if (isset($_REQUEST['frequency_penalty']) && !empty($_REQUEST['frequency_penalty'])) {
                                $acwc_args['frequency_penalty'] = (float)sanitize_text_field($_REQUEST['frequency_penalty']);
                            }
                            if (isset($_REQUEST['presence_penalty']) && !empty($_REQUEST['presence_penalty'])) {
                                $acwc_args['presence_penalty'] = (float)sanitize_text_field($_REQUEST['presence_penalty']);
                            }
                            if (isset($_REQUEST['top_p']) && !empty($_REQUEST['top_p'])) {
                                $acwc_args['top_p'] = (float)sanitize_text_field($_REQUEST['top_p']);
                            }
                            if (isset($_REQUEST['best_of']) && !empty($_REQUEST['best_of'])) {
                                $acwc_args['best_of'] = (float)sanitize_text_field($_REQUEST['best_of']);
                            }
                            if (isset($_REQUEST['stop']) && !empty($_REQUEST['stop'])) {
                                $acwc_args['stop'] = explode(',', sanitize_text_field($_REQUEST['stop']));
                            }
                            $has_limited = false;

                            if (!$has_limited) {
								
                                if ($acwc_args['model'] == 'gpt-3.5-turbo' || $acwc_args['model'] == 'gpt-4' || $acwc_args['model'] == 'gpt-4-32k') {
                                    unset($acwc_args['best_of']);
                                    $acwc_args['messages'] = array(
                                        array('role' => 'user', 'content' => $acwc_args['prompt'])
                                    );
                                    unset($acwc_args['prompt']);
                                    try {
                                        $acwc->chat($acwc_args, function ($curl_info, $data) {
                                            echo _wp_specialchars($data, ENT_NOQUOTES, 'UTF-8', true);
                                            echo PHP_EOL;
                                            ob_flush();
                                            flush();
                                            return strlen($data);
                                        });
                                    }
                                    catch (\Exception $exception){
                                        $message = $exception->getMessage();
                                        $this->acwc_event_message($message);
                                    }
                                } else {
                                    try {
                                        $acwc->completion($acwc_args, function ($curl_info, $data) {
                                            echo _wp_specialchars($data, ENT_NOQUOTES, 'UTF-8', true);
                                            echo PHP_EOL;
                                            ob_flush();
                                            flush();
                                            return strlen($data);
                                        });
                                    }
                                    catch (\Exception $exception){
                                        $message = $exception->getMessage();
                                        $this->acwc_event_message($message);
                                    }
                                }
                            }
                        }
                    }
                }
                exit;
            }
        }

        public function acwc_event_message($words)
        {
            $words = explode(' ', $words);
            $words[count($words) + 1] = '[LIMITED]';
            foreach ($words as $key => $word) {
                echo "event: message\n";
                if ($key == 0) {
                    echo 'data: {"choices":[{"delta":{"content":"' . $word . '"}}]}';
                } else {
                    if ($word == '[LIMITED]') {
                        echo 'data: [LIMITED]';
                    } else {
                        echo 'data: {"choices":[{"delta":{"content":" ' . $word . '"}}]}';
                    }
                }
                echo "\n\n";
                ob_end_flush();
                flush();
            }
        }

        public function acwc_get_cookie_id($source_stream)
        {
            if(!function_exists('PasswordHash')){
                require_once ABSPATH . 'wp-includes/class-phpass.php';
            }
            if(isset($_COOKIE['acwc_'.$source_stream.'_client_id']) && !empty($_COOKIE['acwc_'.$source_stream.'_client_id'])){
                return $_COOKIE['acwc_'.$source_stream.'_client_id'];
            }
            else{
                $hasher      = new \PasswordHash( 8, false );
                $cookie_id = 't_' . substr( md5( $hasher->get_random_bytes( 32 ) ), 2 );
                setcookie('acwc_'.$source_stream.'_client_id', $cookie_id, time() + 604800, COOKIEPATH, COOKIE_DOMAIN);
                return $cookie_id;
            }
        }

    }
    ACWC_Playground::get_instance();
}
