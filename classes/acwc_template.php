<?php
namespace ACWC;
if ( ! defined( 'ABSPATH' ) ) exit;
if(!class_exists('\\ACWC\\ACWC_Template')) {
    class ACWC_Template
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
            add_action('wp_ajax_acwc_template_generator', [$this,'acwc_template_generator']);
            add_action('wp_ajax_acwc_template_post', [$this,'acwc_template_post']);
            add_action('wp_ajax_acwc_save_template', [$this,'acwc_save_template']);
            add_action('wp_ajax_acwc_template_delete', [$this,'acwc_template_delete']);
        }

        public function acwc_template_delete()
        {
            $acwc_result = array('status' => 'error', 'msg'=>'Missing request');
            if ( ! wp_verify_nonce( $_POST['nonce'], 'acwc-ajax-nonce' ) ) {
                $acwc_result['msg'] = WP_OPENAI_CG_NONCE_ERROR;
                wp_send_json($acwc_result);
            }
            if(
                isset($_REQUEST['id'])
                && !empty($_REQUEST['id'])
            ){
                wp_delete_post(sanitize_text_field($_REQUEST['id']));
                $acwc_result['status'] = 'success';
            }
            wp_send_json($acwc_result);
        }

        public function acwc_save_template()
        {
            $acwc_result = array('status' => 'error', 'msg'=>'Missing request');
            if ( ! wp_verify_nonce( $_POST['_wpnonce'], 'acwc_custom_mode_generator' ) ) {
                $acwc_result['msg'] = WP_OPENAI_CG_NONCE_ERROR;
                wp_send_json($acwc_result);
            }
            if(
                isset($_REQUEST['title'])
                && !empty($_REQUEST['title'])
                && isset($_REQUEST['template'])
                && is_array($_REQUEST['template'])
                && count($_REQUEST['template'])
            ){
                $template = acwc_util_core()->sanitize_text_or_array_field($_REQUEST['template']);
                $template['title'] = sanitize_text_field($_REQUEST['title']);
                $template_id = false;
                if(isset($_REQUEST['id']) && !empty($_REQUEST['id'])){
                    $template_id = $_REQUEST['id'];
                }
                if(isset($_REQUEST['title_count']) && !empty($_REQUEST['title_count'])){
                    $template['title_count'] = sanitize_text_field($_REQUEST['title_count']);
                }
                if(isset($_REQUEST['section_count']) && !empty($_REQUEST['section_count'])){
                    $template['section_count'] = sanitize_text_field($_REQUEST['section_count']);
                }
                if(isset($_REQUEST['paragraph_count']) && !empty($_REQUEST['paragraph_count'])){
                    $template['paragraph_count'] = sanitize_text_field($_REQUEST['paragraph_count']);
                }
                if($template_id){
                    wp_update_post(array(
                        'ID' => $template_id,
                        'post_title' => $template['title'],
                        'post_content' => serialize($template)
                    ));
                }
                else{
                    $template_id = wp_insert_post(array(
                        'post_status' => 'publish',
                        'post_type' => 'acwc_mtemplate',
                        'post_title' => $template['title'],
                        'post_content' => serialize($template)
                    ));
                }
                $selected_template = $template_id;
                ob_start();
                include ACWC_PLUGIN_DIR.'backend/acwc_custom_model_template.php';
                $acwc_result['setting'] = ob_get_clean();
                $acwc_result['status'] = 'success';
            }
            wp_send_json($acwc_result);
        }

        public function acwc_template_post()
        {
            $acwc_result = array('status' => 'error', 'msg'=>'Missing request');
            if ( ! wp_verify_nonce( $_POST['nonce'], 'acwc-ajax-nonce' ) ) {
                $acwc_result['msg'] = WP_OPENAI_CG_NONCE_ERROR;
                wp_send_json($acwc_result);
            }
            if(
                isset($_REQUEST['title'])
                && !empty($_REQUEST['title'])
                && isset($_REQUEST['content'])
                && !empty($_REQUEST['content'])
            ){
                $title = sanitize_text_field($_REQUEST['title']);
                $content = wp_kses_post($_REQUEST['content']);
                $new_content = array();
                $exs = array_map('trim', explode("\n", $content));
                foreach($exs as $ex){
                    if(strpos($ex, '##') !== false){
                        $new_content[] = '<h2>'.trim(str_replace('##','',$ex)).'</h2>';
                    }
                    else $new_content[] = $ex;
                }
                $new_content = implode("\n",$new_content);
                $post_type = 'post';
                if(isset($_REQUEST['post_type']) && !empty($_REQUEST['post_type'])){
                    $post_type = sanitize_text_field($_REQUEST['post_type']);
                }
                $acwc_data = array(
                    'post_title' => $title,
                    'post_content' => $new_content,
                    'post_status' => 'draft',
                    'post_type' => $post_type
                );
                if(isset($_REQUEST['excerpt']) && !empty($_REQUEST['excerpt'])){
                    $acwc_data['post_excerpt'] = sanitize_text_field($_REQUEST['excerpt']);
                }
                $acwc_post_id = wp_insert_post($acwc_data);
                if(is_wp_error($acwc_post_id)){
                    $acwc_result['msg'] = $acwc_post_id->get_error_message();
                    wp_send_json($acwc_result);
                }
                else{
                    $content_class = ACWC_Content::get_instance();
                    if(isset($_REQUEST['description']) && !empty($_REQUEST['description'])){
                        $content_class->acwc_save_description($acwc_post_id, sanitize_text_field($_REQUEST['description']));
                    }
                    $acwc_duration = isset($_REQUEST['duration']) && !empty($_REQUEST['duration']) ? sanitize_text_field($_REQUEST['duration']) : 0;
                    $acwc_usage_token = isset($_REQUEST['tokens']) && !empty($_REQUEST['tokens']) ? sanitize_text_field($_REQUEST['tokens']) : 0;
                    $acwc_word_count = isset($_REQUEST['words']) && !empty($_REQUEST['words']) ? sanitize_text_field($_REQUEST['words']) : 0;
                    $acwc_log_id = wp_insert_post(array(
                        'post_title' => 'ACWCLOG:' . $title,
                        'post_type' => 'acwc_slog',
                        'post_status' => 'publish'
                    ));
                    $acwc_ai_model = get_option('acwc_ai_model', 'text-davinci-003');
                    if (isset($_REQUEST['model']) && !empty($_REQUEST['model'])) {
                        $acwc_ai_model = sanitize_text_field($_REQUEST['model']);
                    }
                    $source_log = 'custom';
                    add_post_meta($acwc_log_id, 'acwc_source_log', $source_log);
                    add_post_meta($acwc_log_id, 'acwc_ai_model', $acwc_ai_model);
                    add_post_meta($acwc_log_id, 'acwc_duration', $acwc_duration);
                    add_post_meta($acwc_log_id, 'acwc_usage_token', $acwc_usage_token);
                    add_post_meta($acwc_log_id, 'acwc_word_count', $acwc_word_count);
                    add_post_meta($acwc_log_id, 'acwc_post_id', $acwc_post_id);
                    $acwc_result['status'] = 'success';
                    $acwc_result['id'] = $acwc_post_id;
                }


            }
            wp_send_json($acwc_result);
        }

        public function acwc_template_generator()
        {
            $acwc_result = array('status' => 'error', 'msg'=>'Missing request');
            if ( ! wp_verify_nonce( $_POST['_wpnonce'], 'acwc_custom_mode_generator' ) ) {
                $acwc_result['msg'] = WP_OPENAI_CG_NONCE_ERROR;
                wp_send_json($acwc_result);
            }
            if($_REQUEST['template'] && is_array($_REQUEST['template']) && count($_REQUEST['template']) && isset($_REQUEST['step']) && !empty($_REQUEST['step'])){
                $step = sanitize_text_field($_REQUEST['step']);
                $template = acwc_util_core()->sanitize_text_or_array_field($_REQUEST['template']);
                $prompt = '';
                $title_count = (int)sanitize_text_field($_REQUEST['title_count']);
                $section_count = (int)sanitize_text_field($_REQUEST['section_count']);
                $paragraph_count = sanitize_text_field($_REQUEST['paragraph_count']);
                $post_title = isset($_REQUEST['post_title']) && !empty($_REQUEST['post_title']) ? sanitize_text_field($_REQUEST['post_title']) : '';
                $sections = isset($_REQUEST['sections']) && !empty($_REQUEST['sections']) ? sanitize_text_field($_REQUEST['sections']) : '';
                $list_sections = array();
                if($step == 'titles'){
                    $topic = sanitize_text_field($_REQUEST['topic']);
                    $prompt = $template['prompt_title'];
                    $prompt = str_replace('[count]',$title_count,$prompt);
                    $prompt = str_replace('[topic]',$topic,$prompt);
                }
                if($step == 'sections'){
                    if(empty($post_title)){
                        $acwc_result['msg'] = 'Please generate title first';
                        wp_send_json($acwc_result);
                    }
                    $prompt = $template['prompt_section'];
                    $prompt = str_replace('[count]',$section_count,$prompt);
                    $prompt = str_replace('[title]',$post_title,$prompt);
                }
                if($step == 'excerpt'){
                    if(empty($post_title)){
                        $acwc_result['msg'] = 'Please generate title first';
                        wp_send_json($acwc_result);
                    }
                    $prompt = $template['prompt_excerpt'];
                    $prompt = str_replace('[title]',$post_title,$prompt);
                }
                if($step == 'meta'){
                    if(empty($post_title)){
                        $acwc_result['msg'] = 'Please generate title first';
                        wp_send_json($acwc_result);
                    }
                    $prompt = $template['prompt_meta'];
                    $prompt = str_replace('[title]',$post_title,$prompt);
                }
                if($step == 'content'){
                    if(empty($post_title)){
                        $acwc_result['msg'] = 'Please generate title first';
                        wp_send_json($acwc_result);
                    }
                    if(empty($sections)){
                        $acwc_result['msg'] = 'Please generate sections title';
                        wp_send_json($acwc_result);
                    }
                    $exs = array_map('trim', explode("##", $sections));
                    foreach($exs as $key=> $ex){
                        $section = trim(str_replace("\n",'',$ex));
                        if(!empty($section)) {
                            $list_sections[] = $section;
                        }
                    }
                    $new_sections = implode("\n",$list_sections);
                    $prompt = $template['prompt_content'];
                    $prompt = str_replace('[count]',$paragraph_count,$prompt);
                    $prompt = str_replace('[title]',$post_title,$prompt);
                    $prompt = str_replace('[sections]',$new_sections,$prompt);
                }
                $acwc = ACWCGPT::get_instance()->acwc();
                $generator = ACWC_Generator::get_instance();
                $generator->acwc($acwc);
                $data_request = array(
                    'prompt' => $prompt,
                    'model' => $template['model'],
                    'temperature' => (float)$template['temperature'],
                    'max_tokens' => (float)$template['max_tokens'],
                    'top_p' => (float)$template['top_p'],
                    'best_of' => (float)$template['best_of'],
                    'frequency_penalty' => (float)$template['frequency_penalty'],
                    'presence_penalty' => (float)$template['presence_penalty'],
                );
                if($step == 'sections'){
                    $data_request['stop'] = ($section_count+1).'.';
                }
                if($step == 'titles'){
                    $data_request['stop'] = ($title_count+1).'.';
                }
                $result = $generator->acwc_request($data_request);
                if($result['status'] == 'error'){
                    $acwc_result['msg'] = $result['msg'];
                }
                else{

                    if($step == 'titles' || $step == 'sections'){
                        $complete = $result['data'];
                        $words_count = $generator->acwc_count_words($complete);
                        $complete = trim( $complete );
                        $complete=preg_replace('/\n$/','',preg_replace('/^\n/','',preg_replace('/[\r\n]+/',"\n",$complete)));
                        $mylist = preg_split( "/\r\n|\n|\r/", $complete );
                        $mylist = preg_replace( '/^\\d+\\.\\s/', '', $mylist );
                        $mylist = preg_replace( '/\\.$/', '', $mylist );
                        if($mylist && is_array($mylist) && count($mylist)){
                            $newlist = array();
                            foreach($mylist as $item){
                                $newlist[] = str_replace('"','',$item);
                            }
                            $acwc_result['data'] = $newlist;
                            $acwc_result['status'] = 'success';
                            $acwc_result['tokens'] = $result['tokens'];
                            $acwc_result['words'] = $words_count;
                        }
                        else{
                            $acwc_result['msg'] = 'No data generated';
                        }
                    }
                    if($step == 'content'){
                        $content = $result['data'];
                        $acwc_result['content'] = $content;
                        $words_count = $generator->acwc_count_words($content);
                        foreach($list_sections as $list_section){
                            $list_section = str_replace('\\','',$list_section);
                            if(strpos($content,$list_section.':') !== false){
                                $content = str_replace($list_section.':',$list_section,$content);
                            }
                            if(strpos($content,$list_section."\n") === false){
                                $content = str_replace($list_section,$list_section."\n",$content);
                            }
                            $content = str_replace($list_section,'## '.$list_section, $content);
                        }
                        $acwc_result['data'] = $content;
                        $acwc_result['status'] = 'success';
                        $acwc_result['tokens'] = $result['tokens'];
                        $acwc_result['words'] = $words_count;
                    }
                    if($step == 'meta' || $step == 'excerpt'){
                        $content = $result['data'];
                        $words_count = $generator->acwc_count_words($content);
                        $acwc_result['data'] = $content;
                        $acwc_result['status'] = 'success';
                        $acwc_result['tokens'] = $result['tokens'];
                        $acwc_result['words'] = $words_count;
                    }
                    $acwc_result['prompt'] = $prompt;

                }
            }
            wp_send_json($acwc_result);
        }
    }
    ACWC_Template::get_instance();
}
