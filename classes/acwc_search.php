<?php
namespace ACWC;
if ( ! defined( 'ABSPATH' ) ) exit;
if(!class_exists('\\ACWC\\ACWC_Search')) {
    class ACWC_Search
    {
        private static $instance = null;

        public static function get_instance()
        {
            if (is_null(self::$instance)) {
                self::$instance = new self();
            }
            return self::$instance;
        }

        public function __construct()
        {
            add_shortcode('acwc_search',[$this,'acwc_search']);
            add_action('wp_ajax_acwc_search_data',[$this,'acwc_search_data']);
            add_action('wp_ajax_nopriv_acwc_search_data',[$this,'acwc_search_data']);
        }

        public function acwc_search_data()
        {
            $acwc_result = array('status' => 'error', 'msg' => 'Something went wrong');
            $open_ai = ACWCGPT::get_instance()->acwc();
            if (!$open_ai) {
                $acwc_result['msg'] = 'Missing API Setting';
                wp_send_json($acwc_result);
                exit;
            }
            $acwc_nonce = sanitize_text_field($_REQUEST['_wpnonce']);
            if ( !wp_verify_nonce( $acwc_nonce, 'acwc-chatbox' ) ) {
                $acwc_result['msg'] = WP_OPENAI_CG_NONCE_ERROR;
                wp_send_json($acwc_result);
                exit;
            }
            $acwc_search = isset( $_REQUEST['search'] ) && !empty($_REQUEST['search']) ? sanitize_text_field( $_REQUEST['search'] ) : '';
            if(empty($acwc_search)){
                $acwc_result['msg'] = esc_html(__('Nothing to search','gpt3-ai-content-generator'));
                wp_send_json($acwc_result);
                exit;
            }
            $acwc_pinecone_api = get_option('acwc_pinecone_api','');
            $acwc_pinecone_environment = get_option('acwc_pinecone_environment','');
            $acwc_search_no_result = get_option('acwc_search_no_result','5');
            $acwc_embeddings_result = $this->acwc_embeddings_result($open_ai,$acwc_pinecone_api, $acwc_pinecone_environment, $acwc_search, $acwc_search_no_result);
            $acwc_result['status'] = $acwc_embeddings_result['status'];
            if($acwc_embeddings_result['status'] == 'error'){
                $acwc_result['msg'] = $acwc_embeddings_result['data'];
            }
            else if(is_array($acwc_embeddings_result['data'])){
                $ids = $acwc_embeddings_result['data'];
                $acwc_result['data'] = array();
                $acwc_result['source'] = array();
                foreach ($ids as $key=>$post_id){
                    $acwc_key = $key+1;
                    $embedding = get_post($post_id);
                    if($embedding){
                        ob_start();
                        include ACWC_PLUGIN_DIR.'backend/views/search/item.php';
                        $acwc_result['data'][] = ob_get_clean();
                    }
                }
            }
            else{
                $acwc_result['msg'] = esc_html(__('No result found','gpt3-ai-content-generator'));
            }
            wp_send_json($acwc_result);
        }

        public function acwc_embeddings_result($open_ai,$acwc_pinecone_api,$acwc_pinecone_environment,$acwc_message, $acwc_chat_embedding_top)
        {
            $result = array('status' => 'error','data' => '');
            if(!empty($acwc_pinecone_api) && !empty($acwc_pinecone_environment) ) {
                $response = $open_ai->embeddings([
                    'input' => $acwc_message,
                    'model' => 'text-embedding-ada-002'
                ]);
                $response = json_decode($response, true);
                if (isset($response['error']) && !empty($response['error'])) {
                    $result['data'] = $response['error']['message'];
                } else {
                    $result['data'] = esc_html(__('No result found','gpt3-ai-content-generator'));
                    $embedding = $response['data'][0]['embedding'];
                    if (!empty($embedding)) {
                        $headers = array(
                            'Content-Type' => 'application/json',
                            'Api-Key' => $acwc_pinecone_api
                        );
                        $response = wp_remote_post('https://' . $acwc_pinecone_environment . '/query', array(
                            'headers' => $headers,
                            'body' => json_encode(array(
                                'vector' => $embedding,
                                'topK' => $acwc_chat_embedding_top
                            ))
                        ));
                        if (is_wp_error($response)) {
                            $result['data'] = esc_html($response->get_error_message());
                        } else {
                            $body = json_decode($response['body'], true);
                            if ($body) {
                                if (isset($body['matches']) && is_array($body['matches']) && count($body['matches'])) {
                                    $result['data'] = array();
                                    foreach($body['matches'] as $match){
                                        $result['data'][] = $match['id'];
                                    }
                                    $result['status'] = 'success';
                                }
                            }
                        }
                    }
                }
            }
            else{
                $result['data'] = 'Missing PineCone Settings';
            }
            return $result;
        }

        public function acwc_search()
        {
            ob_start();
            include ACWC_PLUGIN_DIR.'backend/views/search/shortcode.php';
            return ob_get_clean();
        }
    }
    ACWC_Search::get_instance();
}
