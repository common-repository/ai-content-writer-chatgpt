<?php
namespace ACWC;
if ( ! defined( 'ABSPATH' ) ) exit;
if(!class_exists('\\ACWC\\ACWC_Hook')) {
    class ACWC_Hook
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
            add_action( 'admin_menu', array( $this, 'acwc_change_menu_name' ) );
            add_action( 'admin_head', array( $this, 'acwc_hooks_admin_header' ) );
            add_action('wp_footer',[$this,'acwc_footer'],1);
            add_action('wp_head',[$this,'acwc_head_seo'],1);
            add_action( 'admin_enqueue_scripts', 'wp_enqueue_media' );
            add_action('admin_footer',array($this,'acwc_admin_footer'));
            add_editor_style(ACWC_PLUGIN_URL.'backend/css/editor.css');
            add_action( 'admin_enqueue_scripts', [$this,'acwc_enqueue_scripts'] );
        }


        public function acwc_enqueue_scripts()
        {
            wp_enqueue_script('acwc-jquery-datepicker',ACWC_PLUGIN_URL.'backend/js/jquery.datetimepicker.full.min.js',array(),null);
            wp_enqueue_style('acwc-extra-css',ACWC_PLUGIN_URL.'backend/css/acwc_extra.css',array(),null);
            wp_enqueue_style('acwc-jquery-datepicker-css',ACWC_PLUGIN_URL.'backend/css/jquery.datetimepicker.min.css',array(),null);
        }

        public function acwc_admin_footer()
        {
            ?>
            <div class="acwc_out_overlay" style="display: none">
                <div class="acwc_model">
                    <div class="acwc_model_head">
                        <span class="acwc_model_title">GPT3 Modal</span>
                        <span class="acwc_model_close">&times;</span>
                    </div>
                    <div class="acwc_model_content"></div>
                </div>
            </div>
            <div class="acwc_out_overlay-second" style="display: none">
                <div class="acwc_model_second">
                    <div class="acwc_model_head_second">
                        <span class="acwc_model_title_second">GPT3 Modal</span>
                        <span class="acwc_model_close_second">&times;</span>
                    </div>
                    <div class="acwc_model_content_second"></div>
                </div>
            </div>
            <div class="wpcgai_lds-ellipsis" style="display: none">
                <div class="acwc-generating-title">Generating content..</div>
                <div class="acwc-generating-process"></div>
                <div class="acwc-timer"></div>
            </div>
            <script>
                let acwc_ajax_url = '<?php echo admin_url('admin-ajax.php')?>';
            </script>
            <?php
        }

        public function acwc_head_seo()
        {
            global $wpdb;
            $acwc_chat_widget = get_option('acwc_chat_widget',[]);
            $current_context_ID = get_the_ID();
            $acwc_bot_content = $wpdb->get_row($wpdb->prepare("SELECT * FROM ".$wpdb->postmeta." WHERE meta_key=%s",'acwc_widget_page_'.$current_context_ID));
            if($acwc_bot_content && isset($acwc_bot_content->post_id)){
                $acwc_bot = get_post($acwc_bot_content->post_id);
                if($acwc_bot) {
                    if(strpos($acwc_bot->post_content,'\"') !== false) {
                        $acwc_bot->post_content = str_replace('\"', '&quot;', $acwc_bot->post_content);
                    }
                    if(strpos($acwc_bot->post_content,"\'") !== false) {
                        $acwc_bot->post_content = str_replace('\\', '', $acwc_bot->post_content);
                    }
                    $acwc_chat_widget = json_decode($acwc_bot->post_content, true);
                }
            }
            
            $acwc_chat_icon = isset($acwc_chat_widget['icon']) && !empty($acwc_chat_widget['icon']) ? $acwc_chat_widget['icon'] : 'default';
            $acwc_chat_icon_url = isset($acwc_chat_widget['icon_url']) && !empty($acwc_chat_widget['icon_url']) ? $acwc_chat_widget['icon_url'] : '';
            $acwc_chat_status = isset($acwc_chat_widget['status']) && !empty($acwc_chat_widget['status']) ? $acwc_chat_widget['status'] : '';
            $acwc_chat_fontsize = isset($acwc_chat_widget['fontsize']) && !empty($acwc_chat_widget['fontsize']) ? $acwc_chat_widget['fontsize'] : '13';
            $acwc_chat_fontcolor = isset($acwc_chat_widget['fontcolor']) && !empty($acwc_chat_widget['fontcolor']) ? $acwc_chat_widget['fontcolor'] : '#90EE90';
            $acwc_chat_bgcolor = isset($acwc_chat_widget['bgcolor']) && !empty($acwc_chat_widget['bgcolor']) ? $acwc_chat_widget['bgcolor'] : '#222222';
            $acwc_bg_text_field = isset($acwc_chat_widget['bg_text_field']) && !empty($acwc_chat_widget['bg_text_field']) ? $acwc_chat_widget['bg_text_field'] : '#fff';
            $acwc_send_color = isset($acwc_chat_widget['send_color']) && !empty($acwc_chat_widget['send_color']) ? $acwc_chat_widget['send_color'] : '#fff';
            $acwc_border_text_field = isset($acwc_chat_widget['border_text_field']) && !empty($acwc_chat_widget['border_text_field']) ? $acwc_chat_widget['border_text_field'] : '#ccc';
            $acwc_chat_width = isset($acwc_chat_widget['width']) && !empty($acwc_chat_widget['width']) ? $acwc_chat_widget['width'] : '350';
            $acwc_chat_height = isset($acwc_chat_widget['height']) && !empty($acwc_chat_widget['height']) ? $acwc_chat_widget['height'] : '400';
            $acwc_chat_position = isset($acwc_chat_widget['position']) && !empty($acwc_chat_widget['position']) ? $acwc_chat_widget['position'] : 'left';
            $acwc_chat_tone = isset($acwc_chat_widget['tone']) && !empty($acwc_chat_widget['tone']) ? $acwc_chat_widget['tone'] : 'friendly';
            $acwc_chat_proffesion = isset($acwc_chat_widget['proffesion']) && !empty($acwc_chat_widget['proffesion']) ? $acwc_chat_widget['proffesion'] : 'none';
            $acwc_chat_remember_conversation = isset($acwc_chat_widget['remember_conversation']) && !empty($acwc_chat_widget['remember_conversation']) ? $acwc_chat_widget['remember_conversation'] : 'yes';
            $acwc_chat_content_aware = isset($acwc_chat_widget['content_aware']) && !empty($acwc_chat_widget['content_aware']) ? $acwc_chat_widget['content_aware'] : 'yes';
            $acwc_include_footer = (isset($acwc_chat_widget['footer_text']) && !empty($acwc_chat_widget['footer_text'])) ? 5 : 0;
            ?>
            <style>
                .acwc_toc h2{
                    margin-bottom: 20px;
                }
                .acwc_toc{
                    list-style: none;
                    margin: 0 0 30px 0!important;
                    padding: 0!important;
                }
                .acwc_toc li{}
                .acwc_toc li ul{
                    list-style: decimal;
                }
                .acwc_toc a{}
                .acwc_chat_widget{
                    position: fixed;
                }
                .acwc_widget_left{
                    bottom: 15px;
                    left: 15px;
                }
                .acwc_widget_right{
                    bottom: 15px;
                    right: 15px;
                }
                .acwc_widget_right .acwc_chat_widget_content{
                    right: 0;
                }
                .acwc_widget_left .acwc_chat_widget_content{
                    left: 0;
                }
                .acwc_chat_widget_content .acwc-chatbox{
                    height: 100%;
                    background-color: <?php echo esc_html($acwc_chat_bgcolor)?>;
                    border-radius: 5px;
                }
                .acwc_widget_open .acwc_chat_widget_content{
                    height: <?php echo esc_html($acwc_chat_height)?>px;
                }
                .acwc_chat_widget_content{
                    position: absolute;
                    bottom: calc(100% + 15px);
                    width: <?php echo esc_html($acwc_chat_width)?>px;
                    overflow: hidden;

                }
                .acwc_widget_open .acwc_chat_widget_content .acwc-chatbox{
                    top: 0;
                }
                .acwc_chat_widget_content .acwc-chatbox{
                    position: absolute;
                    top: 100%;
                    left: 0;
                    width: <?php echo esc_html($acwc_chat_width)?>px;
                    height: <?php echo esc_html($acwc_chat_height)?>px;
                    transition: top 300ms cubic-bezier(0.17, 0.04, 0.03, 0.94);
                }
                .acwc_chat_widget_content .acwc-chatbox-content{
                    height: <?php echo esc_html($acwc_chat_height)- ($acwc_include_footer ? 58 : 44)?>px;
                }
                .acwc_chat_widget_content .acwc-chatbox-content ul{
                    box-sizing: border-box;
                    height: <?php echo esc_html($acwc_chat_height)- ($acwc_include_footer ? 58 : 44) -24?>px;
                    background: <?php echo esc_html($acwc_chat_bgcolor)?>;
                }
                .acwc_chat_widget_content .acwc-chatbox-content ul li{
                    color: <?php echo esc_html($acwc_chat_fontcolor)?>;
                    font-size: <?php echo esc_html($acwc_chat_fontsize)?>px;
                }
                .acwc_chat_widget_content .acwc-bot-thinking{
                    color: <?php echo esc_html($acwc_chat_fontcolor)?>;
                }
                .acwc_chat_widget_content .acwc-chatbox-type{
                    <?php
                    if($acwc_include_footer):
                    ?>
                    padding: 5px 5px 0 5px;
                    <?php
                    endif;
                    ?>
                    border-top: 0;
                    background: rgb(0 0 0 / 19%);
                }
                .acwc_chat_widget_content .acwc-chat-message{
                    color: <?php echo esc_html($acwc_chat_fontcolor)?>;
                }
                .acwc_chat_widget_content input.acwc-chatbox-typing{
                    background-color: <?php echo esc_html($acwc_bg_text_field)?>;
                    border-color: <?php echo esc_html($acwc_border_text_field)?>;
                }
                .acwc_chat_widget_content .acwc-chatbox-send{
                    color: <?php echo esc_html($acwc_send_color)?>;
                }
                .acwc-chatbox-footer{
                    height: 18px;
                    font-size: 11px;
                    padding: 0 5px;
                    color: <?php echo esc_html($acwc_send_color)?>;
                    background: rgb(0 0 0 / 19%);
                    margin-top:2px;
                    margin-bottom: 2px;
                }
                .acwc_chat_widget_content input.acwc-chatbox-typing:focus{
                    outline: none;
                }
                .acwc_chat_widget .acwc_toggle{
                    cursor: pointer;
                }
                .acwc_chat_widget .acwc_toggle img{
                    width: 75px;
                    height: 75px;
                }
                .acwc-chat-shortcode-type,.acwc-chatbox-type{
                    position: relative;
                }
                .acwc-mic-icon{
                    display: flex;
                    cursor: pointer;
                    position: absolute;
                    right: 47px;
                }
                .acwc-mic-icon svg{
                    width: 16px;
                    height: 16px;
                    fill: currentColor;
                }
                .acwc-chat-message code{
                    padding: 3px 5px 2px;
                    background: rgb(0 0 0 / 20%);
                    font-size: 13px;
                    font-family: Consolas,Monaco,monospace;
                    direction: ltr;
                    unicode-bidi: embed;
                    display: block;
                    margin: 5px 0px;
                    border-radius: 4px;
                    white-space: pre-wrap;
                }
            </style>
            <script>
                let acwc_ajax_url = '<?php echo admin_url('admin-ajax.php')?>';
            </script>
            <?php
            if(is_single()){
               /*  $acwc_meta_description = get_post_meta(get_the_ID(),'_acwc_meta_description',true);
                $_acwc_seo_meta_tag = get_option('_acwc_seo_meta_tag',false);
                $acwc_seo_option = false;
                $acwc_seo_plugin = acwc_util_core()->seo_plugin_activated();
                if($acwc_seo_plugin) {
                    $acwc_seo_option = get_option($acwc_seo_plugin, false);
                }
                if(!empty($acwc_meta_description) && $_acwc_seo_meta_tag && !$acwc_seo_option){
                    ?>
                    
                    <meta name="description" content="<?php echo esc_html($acwc_meta_description)?>">
                    <meta name="og:description" content="<?php echo esc_html($acwc_meta_description)?>">
                    <?php
                } */
            }
        }

        public function acwc_footer()
        {
            ?>
            <script>
                var acwcUserLoggedIn = <?php echo is_user_logged_in() ? 'true' : 'false';?>;
            </script>
            <?php
        }

        public function acwc_hooks_admin_header()
        {
            ?>
            <style>               
                .acwc_out_overlay-second {
                    position: fixed;
                    width: 100%;
                    height: 100%;
                    z-index: 99999;
                    background: rgb(0 0 0 / 20%);
                    top: 0;
                    direction: ltr;
                    display: flex;
                    justify-content: center;
                    align-items: center;
                }
                .acwc_model_second {
                    width: 400px;
                    min-height: 100px;
                    background: #fff;
                    border-radius: 5px;
                }
                .acwc_model_head_second {
                    min-height: 30px;
                    border-bottom: 1px solid #ccc;
                    display: flex;
                    align-items: center;
                    padding: 6px 12px;
                    position: relative;
                }
                .acwc_model_content_second {
                    max-height: calc(100% - 103px);
                    overflow-y: auto;
                }
                .acwc_model_title_second {
                    font-size: 18px;
                }
                .acwc_model_close_second {
                    position: absolute;
                    top: 10px;
                    right: 10px;
                    font-size: 30px;
                    font-weight: bold;
                    cursor: pointer;
                }                
            </style>
            <?php
        }

        public function acwc_change_menu_name()
        {
            global  $menu ;
            global  $submenu ;
            if($submenu['acwc'][0][2] == 'acwc'){
                $submenu['acwc'][0][0] = 'Settings';
            }
        }
    }
    ACWC_Hook::get_instance();
}
