<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class Ai_Content_Generator_Chatgpt_Admin
{
    private  $plugin_name ;
    
    private  $version ;
    
    public function __construct( $plugin_name, $version )
    {
        $this->plugin_name = $plugin_name;
        $this->version = $version;
    }
    
    public function enqueue_styles()
    {
       
        $screen = get_current_screen();
     
        wp_enqueue_style(
            'font-awesome',
            plugin_dir_url( __FILE__ ) . 'css/font-awesome.min.css',
            array(),
            $this->version,
            'all'
        );
    }

    public function enqueue_scripts()
    {
        wp_enqueue_script( 'jquery' );
        wp_enqueue_script( 'jquery-ui-sortable' );
        wp_enqueue_script( 'jquery-ui-tabs' );
        wp_enqueue_script( 'jquery-ui-accordion' );
    }

    function acwc_load_db_vaule_js()
    {
        global  $post ;
    }
       
    public static function add_wp_ai_metabox()
    {
        $screens = [ 'post', 'page', 'wporg_cpt' ];
        if(current_user_can('acwc_meta_box')) {
            foreach ($screens as $screen) {
                add_meta_box(
                    'acwc_preview',
                    __('GPT-3 AI Content Writer & Generator', 'wwu-api'),
                    [self::class, 'html'],
                    $screen,
                    'advanced',
                    'default'
                );
            }
        }
    }

    public function acwc_set_post_content_()
    {
        wp_send_json( 'success' );
        die;
    }

    public static function save( int $post_id )
    {
        $acwc_keys = array(
            'acwc_settings',
            '_wporg_language',
            '_wporg_preview_title',
            '_wporg_number_of_heading',
            '_wporg_heading_tag',
            '_wporg_writing_style',
            '_wporg_writing_tone',
            '_wporg_modify_headings',
            '_wporg_add_img',
            'acwc_image_featured',
            '_wporg_add_tagline',
            '_wporg_add_intro',
            '_wporg_add_conclusion',
            '_wporg_anchor_text',
            '_wporg_target_url',
            '_wporg_generated_text',
            '_wporg_cta_pos',
            '_wporg_target_url_cta',
            'acwc_toc',
            'acwc_toc_title',
            'acwc_toc_title_tag',
            'acwc_intro_title_tag',
            'acwc_conclusion_title_tag'
        );
        foreach($acwc_keys as $acwc_key){
            if ( array_key_exists( $acwc_key, $_POST ) ) {
                update_post_meta($post_id,$acwc_key, \ACWC\acwc_util_core()->sanitize_text_or_array_field($_POST[$acwc_key]));
            }
            else{
                delete_post_meta($post_id,$acwc_key);
            }
        }
    }

    public static function html( $post )
    {
    }

}
add_action( 'add_meta_boxes', [ 'Ai_Content_Generator_Chatgpt_Admin', 'add_wp_ai_metabox' ] );
add_action( 'save_post', [ 'Ai_Content_Generator_Chatgpt_Admin', 'save' ] );
