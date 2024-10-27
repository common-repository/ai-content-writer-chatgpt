<?php

namespace ACWC;
if ( ! defined( 'ABSPATH' ) ) exit;
if(!class_exists('\\ACWC\\ACWC_Forms')) {
    class ACWC_Forms
    {
        private static $instance = null;
        public $acwc_engine = 'gpt-3.5-turbo';
        public $acwc_max_tokens = 2000;
        public $acwc_temperature = 0;
        public $acwc_top_p = 1;
        public $acwc_best_of = 1;
        public $acwc_frequency_penalty = 0;
        public $acwc_presence_penalty = 0;
        public $acwc_stop = [];

        public static function get_instance()
        {
            if (is_null(self::$instance)) {
                self::$instance = new self();
            }
            return self::$instance;
        }

        public function __construct()
        {
            add_action('wp_ajax_acwc_update_template',[$this,'acwc_update_template']);
            add_action('wp_ajax_acwc_template_delete',[$this,'acwc_template_delete']);
            add_shortcode('acwc_form',[$this,'acwc_form_shortcode']);
            add_action( 'admin_menu', array( $this, 'acwc_menu' ) );
            add_action('wp_enqueue_scripts',[$this,'enqueue_scripts']);
            add_action('wp_ajax_acwc_form_log', [$this,'acwc_form_log']);
            add_action('wp_ajax_nopriv_acwc_form_log', [$this,'acwc_form_log']);
            if ( ! wp_next_scheduled( 'acwc_remove_forms_tokens_limited' ) ) {
                wp_schedule_event( time(), 'hourly', 'acwc_remove_forms_tokens_limited' );
            }
            add_action( 'acwc_remove_forms_tokens_limited', array( $this, 'acwc_remove_tokens_limit' ) );
            $this->create_table_logs();
        }

        public function acwc_remove_tokens_limit()
        {
            global $wpdb;
            $acwc_settings = get_option('acwc_limit_tokens_form',[]);
            $widget_reset_limit = isset($acwc_settings['reset_limit']) && !empty($acwc_settings['reset_limit']) ? $acwc_settings['reset_limit'] : 0;
            if($widget_reset_limit > 0) {
                $widget_time = time() - ($widget_reset_limit * 86400);
                $wpdb->query($wpdb->prepare("DELETE FROM " . $wpdb->prefix . "acwc_formtokens WHERE created_at < %s",$widget_time));
            }
        }

        public function acwc_form_log()
        {
            global $wpdb;
            $acwc_result = array('status' => 'success');
            $acwc_nonce = sanitize_text_field($_REQUEST['_wpnonce']);
            if ( !wp_verify_nonce( $acwc_nonce, 'acwc-formlog' ) ) {
                $acwc_result['msg'] = WP_OPENAI_CG_NONCE_ERROR;
                wp_send_json($acwc_result);
                exit;
            }
            if(
                isset($_REQUEST['prompt_id'])
                && !empty($_REQUEST['prompt_id'])
                && isset($_REQUEST['prompt_name'])
                && !empty($_REQUEST['prompt_name'])
                && isset($_REQUEST['prompt_response'])
                && !empty($_REQUEST['prompt_response'])
                && isset($_REQUEST['engine'])
                && !empty($_REQUEST['engine'])
                && isset($_REQUEST['title'])
                && !empty($_REQUEST['title'])
            ){
                $log = array(
                    'prompt' => sanitize_text_field($_REQUEST['title']),
                    'data' => wp_kses_post($_REQUEST['prompt_response']),
                    'prompt_id' => sanitize_text_field($_REQUEST['prompt_id']),
                    'name' => sanitize_text_field($_REQUEST['prompt_name']),
                    'model' => sanitize_text_field($_REQUEST['engine']),
                    'duration' => sanitize_text_field($_REQUEST['duration']),
                    'created_at' => time()
                );
                if(isset($_REQUEST['source_id']) && !empty($_REQUEST['source_id'])){
                    $log['source'] = sanitize_text_field($_REQUEST['source_id']);
                }
                $acwc_generator = ACWC_Generator::get_instance();
                $log['tokens'] = ceil($acwc_generator->acwc_count_words($log['data'])*1000/750);
                $wpdb->insert($wpdb->prefix.'acwc_form_logs', $log);
                $acwc_playground = ACWC_Playground::get_instance();
                $acwc_prompt_token_data = array(
                    'tokens' => $log['tokens'],
                    'created_at' => time()
                );
                if(is_user_logged_in()){
                    $acwc_prompt_token_data['user_id'] = get_current_user_id();
                }
                else{
                    $acwc_prompt_token_data['session_id'] = $acwc_tokens_handling['client_id'];
                }
            }
            wp_send_json($acwc_result);
        }

        public function create_table_logs()
        {
            global $wpdb;
            if(is_admin()) {
                $acwcLogTable = $wpdb->prefix . 'acwc_form_logs';
                if ($wpdb->get_var($wpdb->prepare("SHOW TABLES LIKE %s",$acwcLogTable)) != $acwcLogTable) {
                    $charset_collate = $wpdb->get_charset_collate();
                    $sql = "CREATE TABLE " . $acwcLogTable . " (
    `id` mediumint(11) NOT NULL AUTO_INCREMENT,
    `prompt` TEXT NOT NULL,
    `source` INT NOT NULL DEFAULT '0',
    `data` LONGTEXT NOT NULL,
    `prompt_id` VARCHAR(255) DEFAULT NULL,
    `name` VARCHAR(255) DEFAULT NULL,
    `model` VARCHAR(255) DEFAULT NULL,
    `duration` VARCHAR(255) DEFAULT NULL,
    `tokens` VARCHAR(255) DEFAULT NULL,
    `created_at` VARCHAR(255) NOT NULL,
    PRIMARY KEY  (id)
    ) $charset_collate";
                    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
                    $wpdb->query($sql);
                }
            }
        }

        public function enqueue_scripts()
        {
            wp_enqueue_script('acwc-gpt-form',ACWC_PLUGIN_URL.'assests/js/acwc-form-shortcode.js',array(),null,true);
        }

        public function acwc_menu()
        {
            add_submenu_page(
                'acwc',
                'AI Forms',
                'AI Forms',
                'manage_options',
                'acwc_forms',
                array( $this, 'acwc_forms' ),
                5
            );
        }

        public function acwc_form_shortcode($atts)
        {
            ob_start();
            include ACWC_PLUGIN_DIR . 'backend/acwc_form_shortcode.php';
            return ob_get_clean();
        }

        public function acwc_template_delete()
        {
            $acwc_result = array('status' => 'success');
            if ( ! wp_verify_nonce( $_POST['nonce'], 'acwc-ajax-nonce' ) ) {
                $acwc_result['msg'] = WP_OPENAI_CG_NONCE_ERROR;
                wp_send_json($acwc_result);
            }
            if(isset($_POST['id']) && !empty($_POST['id'])){
                wp_delete_post(sanitize_text_field($_POST['id']));
            }
            wp_send_json($acwc_result);
        }

        public function acwc_update_template()
        {
            $acwc_result = array('status' => 'error', 'msg' => 'Something went wrong');
            if ( ! wp_verify_nonce( $_POST['_wpnonce'], 'acwc_formai_save' ) ) {
                $acwc_result['msg'] = WP_OPENAI_CG_NONCE_ERROR;
                wp_send_json($acwc_result);
            }
            if(
                isset($_POST['title'])
                && !empty($_POST['title'])
                && isset($_POST['description'])
                && !empty($_POST['description'])
                && isset($_POST['prompt'])
                && !empty($_POST['prompt'])
            ){
                $title = sanitize_text_field($_POST['title']);
                $description = sanitize_text_field($_POST['description']);
                if(isset($_POST['id']) && !empty($_POST['id'])){
                    $acwc_prompt_id = sanitize_text_field($_POST['id']);
                    wp_update_post(array(
                        'ID' => $acwc_prompt_id,
                        'post_title' => $title,
                        'post_content' => $description
                    ));
                }
                else{
                    $acwc_prompt_id = wp_insert_post(array(
                        'post_title' => $title,
                        'post_type' => 'acwc_form',
                        'post_content' => $description,
                        'post_status' => 'publish'
                    ));
                }
                $template_fields = array('prompt','fields','response','category','engine','max_tokens','temperature','top_p','best_of','frequency_penalty','presence_penalty','stop','color','icon','editor','bgcolor','header','dans','ddraft','dclear','dnotice','generate_text','noanswer_text','draft_text','clear_text','stop_text','cnotice_text');
                foreach($template_fields as $template_field){
                    if(isset($_POST[$template_field]) && !empty($_POST[$template_field])){
                        $value = acwc_util_core()->sanitize_text_or_array_field($_POST[$template_field]);
                        $key = sanitize_text_field($template_field);
                        if($key == 'fields'){
                            $value = json_encode($value,JSON_UNESCAPED_UNICODE );
                        }
                        update_post_meta($acwc_prompt_id, 'acwc_form_'.$key, $value);
                    }
                    elseif(in_array($template_field,array('bgcolor','header','dans','ddraft','dclear','dnotice')) && (!isset($_POST[$template_field]) || empty($_POST[$template_field]))){
                        delete_post_meta($acwc_prompt_id, 'acwc_form_'.$template_field);
                    }
                }
                $acwc_result['status'] = 'success';
                $acwc_result['id'] = $acwc_prompt_id;
            }
            wp_send_json($acwc_result);
        }

        public function acwc_forms()
        {
            include ACWC_PLUGIN_DIR . 'backend/acwc_forms.php';
        }
    }
    ACWC_Forms::get_instance();
}
