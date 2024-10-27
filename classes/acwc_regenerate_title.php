<?php
namespace ACWC;
if ( ! defined( 'ABSPATH' ) ) exit;
if(!class_exists('\\ACWC\\ACWC_Regenerate_Title')) {
    class ACWC_Regenerate_Title
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
            add_filter('post_row_actions',[$this,'acwc_regenerate_action'],10,2);
            add_filter('page_row_actions',[$this,'acwc_regenerate_action'],10,2);
            add_action('admin_footer',[$this,'acwc_regenerate_footer']);
            add_action('wp_ajax_acwc_regenerate_title',[$this,'acwc_regenerate_title']);
            add_action('wp_ajax_acwc_regenerate_save',[$this,'acwc_regenerate_save']);
        }

        public function acwc_regenerate_save()
        {
            $acwc_result = array('status' => 'error', 'msg' => 'Something went wrong');
            if ( ! wp_verify_nonce( $_POST['nonce'], 'acwc-ajax-nonce' ) ) {
                $acwc_result['status'] = 'error';
                $acwc_result['msg'] = WP_OPENAI_CG_NONCE_ERROR;
                wp_send_json($acwc_result);
            }
            if(isset($_POST['title']) && !empty($_POST['title']) && isset($_POST['id']) && !empty($_POST['id'])){
                $id = sanitize_text_field($_POST['id']);
                $title = sanitize_text_field($_POST['title']);
                $check = wp_update_post(array(
                    'ID' => $id,
                    'post_title' => $title
                ));
                if(is_wp_error($check)){
                    $acwc_result['msg'] = $check->get_error_message();
                }
                else{
                    $acwc_result['status'] = 'success';
                }
            }
            wp_send_json($acwc_result);
        }

        public function acwc_regenerate_title()
        {
            $acwc_result = array('status' => 'error', 'msg' => 'Something went wrong');
            if ( ! wp_verify_nonce( $_POST['nonce'], 'acwc-ajax-nonce' ) ) {
                $acwc_result['status'] = 'error';
                $acwc_result['msg'] = WP_OPENAI_CG_NONCE_ERROR;
                wp_send_json($acwc_result);
            }
            if(isset($_POST['title']) && !empty($_POST['title'])){
                $title = sanitize_text_field($_POST['title']);
                $open_ai = ACWCGPT::get_instance()->acwc();
                if(!$open_ai){
                    $acwc_result['error'] = 'Missing API Setting';
                }
                else{
                    $temperature = floatval( $open_ai->temperature );
                    $max_tokens = intval( $open_ai->max_tokens );
                    $top_p = floatval( $open_ai->top_p );
                    $best_of = intval( $open_ai->best_of );
                    $frequency_penalty = floatval( $open_ai->frequency_penalty );
                    $presence_penalty = floatval( $open_ai->presence_penalty );
                    $wpai_language = sanitize_text_field( $open_ai->wpai_language );
                    if ( empty($wpai_language) ) {
                        $wpai_language = "en";
                    }
                    $acwc_language_file = plugin_dir_path( dirname( __FILE__ ) ) . 'backend/languages/' . $wpai_language . '.json';
                    if ( !file_exists( $acwc_language_file ) ) {
                        $acwc_language_file = plugin_dir_path( dirname( __FILE__ ) ) . 'backend/languages/en.json';
                    }
                    $acwc_language_json = file_get_contents( $acwc_language_file );
                    $acwc_languages = json_decode( $acwc_language_json, true );
                    $prompt = isset($acwc_languages['regenerate_prompt']) && !empty($acwc_languages['regenerate_prompt']) ? $acwc_languages['regenerate_prompt'] : 'Suggest me 5 different title for: %s.';
                    $prompt = sprintf($prompt, $title);
                    $acwc_ai_model = get_option('acwc_ai_model','text-davinci-003');
                    $acwc_generator = ACWC_Generator::get_instance();
                    $acwc_generator->acwc($open_ai);
                    if($acwc_ai_model == 'gpt-3.5-turbo' || $acwc_ai_model == 'gpt-4' || $acwc_ai_model == 'gpt-4-32k'){
                        $prompt = $acwc_languages['fixed_prompt_turbo'].' '.$prompt;
                    }
                    $complete = $acwc_generator->acwc_request( [
                        'model'             => $acwc_ai_model,
                        'prompt'            => $prompt,
                        'temperature'       => $temperature,
                        'max_tokens'        => $max_tokens,
                        'frequency_penalty' => $frequency_penalty,
                        'presence_penalty'  => $presence_penalty,
                        'top_p'             => $top_p,
                        'best_of'           => $best_of,
                        'stop' => '6.'
                    ] );
                    $acwc_result['prompt'] = $prompt;
                    if($complete['status'] == 'error'){
                        $acwc_result['msg'] = $complete['msg'];
                    }
                    else{
                        $complete = $complete['data'];
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
                        }
                        else{
                            $acwc_result['msg'] = 'No title generated';
                        }
                    }
                }
            }
            wp_send_json($acwc_result);
        }

        public function acwc_regenerate_action($actions, $post)
        {
            if(current_user_can('acwc_suggester')) {
                $actions['acwc_regenerate'] = '<a class="acwc_regenerate_title" data-title="' . esc_html($post->post_title) . '" data-id="' . esc_attr($post->ID) . '" href="javascript:void(0)">' . esc_html(__('Suggest Title', 'ai-content-generator-chatgpt')) . '</a>';
            }
            return $actions;
        }

        public function acwc_regenerate_footer()
        {
            ?>
            <script>
                jQuery(document).ready(function ($){
                    var acwcRegenerateRunning = false;
                    $('.acwc_modal_close').click(function (){
                        $('.acwc_modal_content').empty();
                        $('.acwc_modal_close').closest('.acwc_modal').hide();
                        $('.acwc_modal_close').closest('.acwc_modal').removeClass('acwc-small-modal');
                        $('.acwc-overlay').hide();
                        if(acwcRegenerateRunning){
                            acwcRegenerateRunning.abort();
                        }
                    })
                    function acwcLoading(btn){
                        btn.attr('disabled','disabled');
                        if(!btn.find('spinner').length){
                            btn.append('<span class="spinner"></span>');
                        }
                        btn.find('.spinner').css('visibility','unset');
                    }
                    function acwcRmLoading(btn){
                        btn.removeAttr('disabled');
                        btn.find('.spinner').remove();
                    }
                    $(document).on('click','.acwc_regenerate_save', function (e){
                        var btn = $(e.currentTarget);
                        var title = btn.parent().find('input').val();
                        var id = btn.attr('data-id');
                        if(title === ''){
                            alert('Please insert title');
                        }
                        else{
                            acwcRegenerateRunning = $.ajax({
                                url: '<?php echo admin_url('admin-ajax.php')?>',
                                data: {action: 'acwc_regenerate_save',title: title, id: id,'nonce': '<?php echo wp_create_nonce('acwc-ajax-nonce')?>'},
                                dataType: 'JSON',
                                type: 'POST',
                                beforeSend: function (){
                                    $('.acwc_regenerate_save').attr('disabled','disabled');
                                    acwcLoading(btn);
                                },
                                success: function(res){
                                    if(res.status === 'success'){
                                        $('#post-'+id+' .row-title').text(title);
                                        $('.acwc_modal_close').click();
                                    }
                                    else{
                                        acwcRmLoading(btn);
                                        alert(res.msg);
                                    }
                                },
                                error: function (){
                                    acwcRmLoading(btn);
                                    alert('Something went wrong');
                                    $('.acwc_regenerate_save').removeAttr('disabled');
                                }
                            })
                        }
                    })
                    $(document).on('click','.acwc_regenerate_title', function (e){
                        var btn = $(e.currentTarget);
                        var id = btn.attr('data-id');
                        var title = btn.attr('data-title');
                        if(title === ''){
                            alert('Please update title first');
                        }
                        else{
                            if(acwcRegenerateRunning){
                                acwcRegenerateRunning.abort();
                            }
                            $('.acwc_modal_content').empty();
                            $('.acwc-overlay').show();
                            $('.acwc_modal').show();
                            $('.acwc_modal_title').html('AI Content Writer - ChatGPT - Title Suggestion Tool');
                            $('.acwc_modal_content').html('<p style="font-style: italic;margin-top: 5px;text-align: center;">Preparing suggestions...</p>');
                            acwcRegenerateRunning = $.ajax({
                                url: '<?php echo admin_url('admin-ajax.php')?>',
                                data: {action: 'acwc_regenerate_title',title: title,'nonce': '<?php echo wp_create_nonce('acwc-ajax-nonce')?>'},
                                dataType: 'JSON',
                                type: 'POST',
                                success: function (res){
                                    if(res.status === 'success'){
                                        var html = '';
                                        if(res.data.length){
                                            $.each(res.data, function (idx, item){
                                                html += '<div class="acwc-regenerate-title"><input type="text" value="'+item+'"><button data-id="'+id+'" class="button  acwc_regenerate_save">Use</button></div>';
                                            })
                                            $('.acwc_modal_content').html(html);
                                        }
                                        else{
                                            $('.acwc_modal_content').html('<p style="color: #f00;margin-top: 5px;text-align: center;">No result</p>');
                                        }
                                    }
                                    else{
                                        $('.acwc_modal_content').html('<p style="color: #f00;margin-top: 5px;text-align: center;">'+res.msg+'</p>');
                                    }
                                },
                                error: function (){
                                    $('.acwc_modal_content').html('<p style="color: #f00;margin-top: 5px;text-align: center;">Something went wrong</p>');
                                }
                            })
                        }
                    })
                })
            </script>
            <?php
        }
    }
    ACWC_Regenerate_Title::get_instance();
}
