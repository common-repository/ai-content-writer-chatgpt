<?php
namespace ACWC;
if ( ! defined( 'ABSPATH' ) ) exit;
if(!class_exists('\\ACWC\\ACWC_Generator')) {
    class ACWC_Generator{
        private static $instance = null;
        public $acwc_engine = 'text-davinci-003';
        public $acwc_max_tokens = 2000;
        public $acwc_temperature = 0;
        public $acwc_top_p = 1;
        public $acwc_best_of = 1;
        public $acwc_frequency_penalty = 0;
        public $acwc_presence_penalty = 0;
        public $acwc_stop = [];
        public $acwc_allowed_html_content_post;
        public $acwc_image_style;
        public $acwc_number_of_heading;
        public $acwc_preview_title;
        public $acwc_opts = array();
        public $acwc_prompt = '';
        public $acwc_intro = '';
        public $acwc_conclusion = '';
        public $acwc_tagline = '';
        public $acwc_cta = '';
        public $acwc_image_source;
        public $acwc_featured_image_source;
        public $acwc_language;
        public $acwc_add_intro;
        public $acwc_add_conclusion;
        public $acwc_writing_style;
        public $acwc_writing_tone;
        public $acwc_keywords;
        public $acwc_add_keywords_bold;
        public $acwc_heading_tag;
        public $acwc_words_to_avoid;
        public $acwc_add_tagline;
        public $acwc_add_faq;
        public $acwc_target_url;
        public $acwc_anchor_text;
        public $acwc_cta_pos;
        public $acwc_target_url_cta;
        public $acwc_modify_headings;
        public $acwc_toc;
        public $acwc_toc_title;
        public $acwc_toc_title_tag;
        public $acwc_intro_title_tag;
        public $acwc_conclusion_title_tag;
        public $acwc_pexels_api;
        public $acwc_pexels_orientation;
        public $acwc_pexels_size;
        public $acwc_img_size;
        public $acwc_seo_meta_desc;
        public $acwc_img_style;
        public $acwc_toc_list = array();
        public $generate_continue = false;
        public $acwc_content = '';
        public $acwc_languages;
        public $writing_style;
        public $tone_text;
        public $conclusion_text;
        public $intro_text;
        public $tagline_text;
        public $faq_heading;
        public $introduction;
        public $faq_text;
        public $conclusion;
        public $style_text;
        public $error_msg = false;
        public $acwc_custom_image_settings = array(
            'artist' => 'None',
            'photography_style' => 'None',
            'lighting' => 'Ambient',
            'subject' => 'None',
            'camera_settings' => 'Aperture',
            'composition' => 'Rule of Thirds',
            'resolution' => '4K (3840x2160)',
            'color' => 'RGB',
            'special_effects' => 'Cinemagraph'
        );
        public $acwc_headings = array();
        public $acwc_result = array(
            'status'    => 'error',
            'msg'       => 'Something went wrong',
            'tokens' => 0,
            'length' => 0,
            'data' => '',
            'error' => '',
            'content' => '',
            'next_step' => 'content',
            'img' => '',
            'description' => '',
            'featured_img'       => '',
            'tocs' => ''
        );
        public $acwc;
        public $acwc_sleep = 8;

        public static function get_instance()
        {
            if (is_null(self::$instance)) {
                self::$instance = new self();
            }
            return self::$instance;
        }

        public function __construct()
        {
            add_action('wp_ajax_acwc_content_generator',[$this,'acwc_content_generator']);
        }

        public function acwc_content_generator()
        {
            $step = isset( $_REQUEST['step'] ) && !empty($_REQUEST['step']) ? sanitize_text_field( $_REQUEST['step'] ) : 'heading';
            $acwc = ACWCGPT::get_instance()->acwc();
            if ( ! wp_verify_nonce( $_POST['nonce'], 'acwc-ajax-nonce' ) ) {
                $this->acwc_result['msg'] = ACWC_NONCE_ERROR;
                $this->acwc_result['status'] = 'error';
                wp_send_json($this->acwcResult());
            }
            if(!$acwc){
                $this->acwc_result['msg'] = 'Missing API Setting';
                $this->acwc_result['status'] = 'error';
            }
            else{
                $this->init($acwc);
                $this->acwc_generator($step);
            }
            wp_send_json($this->acwcResult());
        }

        public function init($open_ai, $acwc_preview_title = false, $cronjob = false, $post_id = false)
        {
            $this->acwc = $open_ai;
            $img_size = $open_ai->img_size;
            $this->acwc_image_style = get_option('_acwc_image_style', '');
            $this->acwc_temperature = floatval( $open_ai->temperature );
            $this->acwc_max_tokens = intval( $open_ai->max_tokens );
            $this->acwc_top_p = floatval( $open_ai->top_p );
            $this->acwc_best_of = intval( $open_ai->best_of );
            $this->acwc_frequency_penalty = floatval( $open_ai->frequency_penalty );
            $this->acwc_presence_penalty = floatval( $open_ai->presence_penalty );
            $this->acwc_engine = get_option('acwc_ai_model','text-davinci-003');
            $this->acwc_allowed_html_content_post = wp_kses_allowed_html( 'post' );
            if($cronjob){
                $this->acwc_preview_title = $acwc_preview_title;
                $this->acwc_number_of_heading = $open_ai->wpai_number_of_heading;
                $this->acwc_image_source = get_option('acwc_image_source','');
                $this->acwc_featured_image_source = get_option('acwc_featured_image_source','');
                $this->acwc_language = sanitize_text_field( $open_ai->wpai_language );
                $this->acwc_add_intro = intval( $open_ai->wpai_add_intro );
                $this->acwc_add_conclusion = intval( $open_ai->wpai_add_conclusion );
                $this->acwc_writing_style = sanitize_text_field( $open_ai->wpai_writing_style );
                $this->acwc_writing_tone = sanitize_text_field( $open_ai->wpai_writing_tone );
                $this->acwc_keywords = get_post_meta($post_id, '_acwc_keywords', true);
                $this->acwc_add_keywords_bold = intval($open_ai->wpai_add_keywords_bold);
                $this->acwc_heading_tag = sanitize_text_field( $open_ai->wpai_heading_tag );
                $this->acwc_words_to_avoid = get_post_meta($post_id,'_acwc_avoid',true);
                $this->acwc_add_tagline = intval( $open_ai->wpai_add_tagline );
                $this->acwc_add_faq = intval( $open_ai->wpai_add_faq );
                $this->acwc_seo_meta_desc = get_option('_acwc_seo_meta_desc',false);
                $this->acwc_target_url = get_post_meta($post_id,'_acwc_target',true);
                $this->acwc_anchor_text = get_post_meta($post_id,'_acwc_anchor',true);
                $this->acwc_cta_pos = sanitize_text_field( $open_ai->wpai_cta_pos );
                $this->acwc_target_url_cta = get_post_meta($post_id,'_acwc_cta',true);
                $this->acwc_modify_headings = false;
                $this->acwc_toc = get_option('acwc_toc',false);
                $this->acwc_toc_title = get_option('acwc_toc_title','Table of Contents');
                $this->acwc_toc_title_tag = get_option('acwc_toc_title_tag','h2');
                $this->acwc_intro_title_tag = get_option('acwc_intro_title_tag','h2');
                $this->acwc_conclusion_title_tag = get_option('acwc_conclusion_title_tag','h2');
                $this->acwc_pexels_api = get_option('acwc_pexels_api','');
                $this->acwc_pexels_orientation = get_option('acwc_pexels_orientation','');
                $this->acwc_pexels_size = get_option('acwc_pexels_size','');
                $this->acwc_img_size = $img_size;
                $this->acwc_img_style = get_option('_acwc_image_style', '');
                $this->acwc_toc_list = array();
                $this->generate_continue = false;
                $this->acwc_result['content'] = '';
                $acwc_custom_image_settings = get_option('acwc_custom_image_settings',[]);
                $this->acwc_custom_image_settings = wp_parse_args($acwc_custom_image_settings, $this->acwc_custom_image_settings);
            }
            else{
                $this->acwc_number_of_heading = sanitize_text_field( $_REQUEST["wpai_number_of_heading"] );
                $this->acwc_image_source = sanitize_text_field($_REQUEST['acwc_image_source']);
                $this->acwc_featured_image_source = sanitize_text_field($_REQUEST['acwc_featured_image_source']);
                $this->acwc_language = sanitize_text_field( $_REQUEST["wpai_language"] );
                $this->acwc_add_intro = intval( sanitize_text_field($_REQUEST["wpai_add_intro"] ));
                $this->acwc_add_conclusion = intval( sanitize_text_field($_REQUEST["wpai_add_conclusion"] ));
                $this->acwc_writing_style = sanitize_text_field( $_REQUEST["wpai_writing_style"] );
                $this->acwc_writing_tone = sanitize_text_field( $_REQUEST["wpai_writing_tone"] );
                $this->acwc_keywords = isset($_REQUEST["wpai_keywords"]) ? sanitize_text_field( $_REQUEST["wpai_keywords"] ) : '';
                $this->acwc_add_keywords_bold = intval( sanitize_text_field($_REQUEST["wpai_add_keywords_bold"] ));
                $this->acwc_heading_tag = sanitize_text_field( $_REQUEST["wpai_heading_tag"] );
                $this->acwc_words_to_avoid = isset($_REQUEST['wpai_words_to_avoid']) ? sanitize_text_field( $_REQUEST["wpai_words_to_avoid"] ): '';
                $this->acwc_add_tagline = intval( sanitize_text_field($_REQUEST["wpai_add_tagline"] ));
                $this->acwc_add_faq = intval( sanitize_text_field($_REQUEST["wpai_add_faq"] ));
                $this->acwc_seo_meta_desc = isset($_REQUEST["acwc_seo_meta_desc"]) ? intval( sanitize_text_field($_REQUEST["acwc_seo_meta_desc"] )) : false;
                $this->acwc_target_url = sanitize_text_field( $_REQUEST["wpai_target_url"] );
                $this->acwc_anchor_text = sanitize_text_field( $_REQUEST["wpai_anchor_text"] );
                $this->acwc_cta_pos = sanitize_text_field( $_REQUEST["wpai_cta_pos"] );
                $this->acwc_target_url_cta = sanitize_text_field( $_REQUEST["wpai_target_url_cta"] );
                $this->acwc_img_size = sanitize_text_field( $_REQUEST["wpai_img_size"] );
                $this->acwc_img_size = ( empty($this->acwc_img_size) ? $img_size : $this->acwc_img_size );
                $this->acwc_img_style = sanitize_text_field( $_REQUEST["wpai_img_style"] );
                $this->acwc_img_style = ( empty($this->acwc_img_style) ? $this->acwc_image_style : $this->acwc_img_style );
                $this->acwc_modify_headings = intval( sanitize_text_field($_REQUEST["wpai_modify_headings"] ));
                $this->acwc_toc = intval(sanitize_text_field($_REQUEST['acwc_toc']));
                $this->acwc_toc_title = sanitize_text_field($_REQUEST['acwc_toc_title']);
                $this->acwc_toc_title = empty($this->acwc_toc_title) ? 'Table of Contents' : $this->acwc_toc_title;
                $this->acwc_toc_title_tag = sanitize_text_field($_REQUEST['acwc_toc_title_tag']);
                $this->acwc_toc_title_tag = empty($this->acwc_toc_title_tag) ? 'h2' : $this->acwc_toc_title_tag;
                $this->acwc_intro_title_tag = sanitize_text_field($_REQUEST['acwc_intro_title_tag']);
                $this->acwc_intro_title_tag = empty($this->acwc_intro_title_tag) ? 'h2' : $this->acwc_intro_title_tag;
                $this->acwc_conclusion_title_tag = sanitize_text_field($_REQUEST['acwc_conclusion_title_tag']);
                $this->acwc_conclusion_title_tag = empty($this->acwc_conclusion_title_tag) ? 'h2' : $this->acwc_conclusion_title_tag;
                $this->acwc_toc_list = isset($_REQUEST['acwc_toc_list']) && !empty($_REQUEST['acwc_toc_list']) ? explode('||',sanitize_text_field($_REQUEST['acwc_toc_list'])) : array();
                $this->acwc_pexels_orientation = isset($_REQUEST['acwc_pexels_orientation']) && !empty($_REQUEST['acwc_pexels_orientation']) ? sanitize_text_field($_REQUEST['acwc_pexels_orientation']) : '';
                $this->acwc_pexels_size = isset($_REQUEST['acwc_pexels_size']) && !empty($_REQUEST['acwc_pexels_size']) ? sanitize_text_field($_REQUEST['acwc_pexels_size']) : '';
                $this->acwc_pexels_api = get_option('acwc_pexels_api','');
                $this->generate_continue = intval( sanitize_text_field($_REQUEST["is_generate_continue"] ));
                $this->acwc_result['tokens'] = isset($_REQUEST['tokens']) ? sanitize_text_field($_REQUEST['tokens']) : 0;
                $this->acwc_result['length'] = isset($_REQUEST['length']) ? sanitize_text_field($_REQUEST['length']) : 0;
                $this->acwc_result['content'] = ( isset( $_REQUEST['content'] ) ? wp_kses( $_REQUEST['content'], $this->acwc_allowed_html_content_post ) : '' );
                $this->acwc_preview_title = sanitize_text_field( $_REQUEST["wpai_preview_title"] );
                $hfHeadings = sanitize_text_field( $_REQUEST["hfHeadings"] );
                $this->acwc_headings = explode( "||", $hfHeadings );
                if(isset($_REQUEST['acwc_custom_image_settings']) && is_array($_REQUEST['acwc_custom_image_settings']) && count($_REQUEST['acwc_custom_image_settings'])){
                    $acwc_custom_image_settings = acwc_util_core()->sanitize_text_or_array_field($_REQUEST['acwc_custom_image_settings']);
                }
                else{
                    $acwc_custom_image_settings = get_option('acwc_custom_image_settings',[]);
                }
                $this->acwc_custom_image_settings = wp_parse_args($acwc_custom_image_settings, $this->acwc_custom_image_settings);
            }
            $this->acwc_opts = [
                'model'             => $this->acwc_engine,
                'temperature'       => $this->acwc_temperature,
                'max_tokens'        => $this->acwc_max_tokens,
                'frequency_penalty' => $this->acwc_frequency_penalty,
                'presence_penalty'  => $this->acwc_presence_penalty,
                'top_p'             => $this->acwc_top_p,
                'best_of'           => $this->acwc_best_of
            ];
            $this->acwc_sleep = get_option('acwc_sleep_time',8);
            if(empty($this->acwc_language)){
                $this->acwc_language = 'en';
            }
            if ( empty($this->acwc_number_of_heading) ) {
                $this->acwc_number_of_heading = 5;
            }
            if ( empty($this->acwc_writing_style) ) {
                $this->acwc_writing_style = "infor";
            }
            if ( empty($this->acwc_writing_tone) ) {
                $this->acwc_writing_tone = "formal";
            }
            // if heading tag is not set, set it to h2
            if ( empty($this->acwc_heading_tag) ) {
                $this->acwc_heading_tag = "h2";
            }
            $acwc_language_file = plugin_dir_path( dirname( __FILE__ ) ) . 'admin/languages/' . $this->acwc_language . '.json';
            if ( !file_exists( $acwc_language_file ) ) {
                $acwc_language_file = plugin_dir_path( dirname( __FILE__ ) ) . 'admin/languages/en.json';
            }

            $acwc_language_json = file_get_contents( $acwc_language_file );
            $this->acwc_languages = json_decode( $acwc_language_json, true );
            $this->writing_style = ( isset( $this->acwc_languages['writing_style'][$this->acwc_writing_style] ) ? $this->acwc_languages['writing_style'][$this->acwc_writing_style] : 'infor' );
            $this->tone_text = ( isset( $this->acwc_languages['writing_tone'][$this->acwc_writing_tone] ) ? $this->acwc_languages['writing_tone'][$this->acwc_writing_tone] : 'formal' );
            if ( $this->acwc_number_of_heading == 1 ) {
                $prompt_text = ( isset( $this->acwc_languages['prompt_text_1'] ) ? $this->acwc_languages['prompt_text_1'] : '' );
            } else {
                $prompt_text = ( isset( $this->acwc_languages['prompt_text'] ) ? $this->acwc_languages['prompt_text'] : '' );
            }
            $this->intro_text = ( isset( $this->acwc_languages['intro_text'] ) ? $this->acwc_languages['intro_text'] : '' );
            $this->conclusion_text = ( isset( $this->acwc_languages['conclusion_text'] ) ? $this->acwc_languages['conclusion_text'] : '' );
            $this->tagline_text = ( isset( $this->acwc_languages['tagline_text'] ) ? $this->acwc_languages['tagline_text'] : '' );
            $this->introduction = ( isset( $this->acwc_languages['introduction'] ) ? $this->acwc_languages['introduction'] : '' );
            $this->conclusion = ( isset( $this->acwc_languages['conclusion'] ) ? $this->acwc_languages['conclusion'] : '' );
            if ( $this->acwc_language == 'hi' || $this->acwc_language == 'tr' || $this->acwc_language == 'ja' || $this->acwc_language == 'zh' || $this->acwc_language == 'ko' ) {
                $this->faq_text = ( isset( $this->acwc_languages['faq_text'] ) ? sprintf( $this->acwc_languages['faq_text'], $this->acwc_preview_title, strval( $this->acwc_number_of_heading ) ) : '' );
            } else {
                $this->faq_text = ( isset( $this->acwc_languages['faq_text'] ) ? sprintf( $this->acwc_languages['faq_text'], strval( $this->acwc_number_of_heading ), $this->acwc_preview_title ) : '' );
            }

            $this->faq_heading = ( isset( $this->acwc_languages['faq_heading'] ) ? $this->acwc_languages['faq_heading'] : '' );
            $this->style_text = ( isset( $this->acwc_languages['style_text'] ) ? sprintf( $this->acwc_languages['style_text'], $this->writing_style ) : '' );
            $of_text = ( isset( $this->acwc_languages['of_text'] ) ? $this->acwc_languages['of_text'] : '' );
            $prompt_last = ( isset( $this->acwc_languages['prompt_last'] ) ? $this->acwc_languages['prompt_last'] : '' );
            $piece_text = ( isset( $this->acwc_languages['piece_text'] ) ? $this->acwc_languages['piece_text'] : '' );

            if ( $this->acwc_language == 'ru' || $this->acwc_language == 'ko' ) {

                if ( empty($this->acwc_keywords) ) {
                    $this->acwc_prompt = $prompt_text . strval( $this->acwc_number_of_heading ) . $prompt_last . $this->acwc_preview_title . ".";
                } else {
                    $keyword_text = ( isset( $this->acwc_languages['keyword_text'] ) ? sprintf( $this->acwc_languages['keyword_text'], $this->acwc_keywords ) : '' );
                    $this->acwc_prompt = $prompt_text . strval( $this->acwc_number_of_heading ) . $prompt_last . $this->acwc_preview_title . $keyword_text;
                }

            } elseif ( $this->acwc_language == 'zh' ) {

                if ( empty($this->acwc_keywords) ) {
                    $this->acwc_prompt = $prompt_text . $this->acwc_preview_title . $of_text . strval( $this->acwc_number_of_heading ) . $piece_text . ".";
                } else {
                    $keyword_text = ( isset( $this->acwc_languages['keyword_text'] ) ? sprintf( $this->acwc_languages['keyword_text'], $this->acwc_keywords ) : '' );
                    $this->acwc_prompt = $prompt_text . $this->acwc_preview_title . $of_text . strval( $this->acwc_number_of_heading ) . $piece_text . $keyword_text;
                }

            } elseif ( $this->acwc_language == 'ja' || $this->acwc_language == 'hi' || $this->acwc_language == 'tr' ) {

                if ( empty($this->acwc_keywords) ) {
                    $this->acwc_prompt = $this->acwc_preview_title . $prompt_text . strval( $this->acwc_number_of_heading ) . $prompt_last . ".";
                } else {
                    $keyword_text = ( isset( $this->acwc_languages['keyword_text'] ) ? sprintf( $this->acwc_languages['keyword_text'], $this->acwc_keywords ) : '' );
                    $this->acwc_prompt = $this->acwc_preview_title . $prompt_text . strval( $this->acwc_number_of_heading ) . $prompt_last . $keyword_text;
                }

            } else {

                if ( empty($this->acwc_keywords) ) {
                    $this->acwc_prompt = strval( $this->acwc_number_of_heading ) . $prompt_text . $this->acwc_preview_title . ".";
                } else {
                    $keyword_text = ( isset( $this->acwc_languages['keyword_text'] ) ? sprintf( $this->acwc_languages['keyword_text'], $this->acwc_keywords ) : '' );
                    $this->acwc_prompt = strval( $this->acwc_number_of_heading ) . $prompt_text . $this->acwc_preview_title . $keyword_text;
                }

            }


            if ( !empty($this->acwc_words_to_avoid) ) {
                $this->avoid_text = ( isset( $this->acwc_languages['avoid_text'] ) ? sprintf( $this->acwc_languages['avoid_text'], $this->acwc_words_to_avoid ) : '' );
                $this->acwc_prompt = $this->acwc_prompt . $this->avoid_text;
            }


            if ( $this->acwc_language == 'ja' || $this->acwc_language == 'tr' ) {
                $this->acwc_intro = $this->acwc_preview_title . $this->intro_text;
                $this->acwc_conclusion = $this->acwc_preview_title . $this->conclusion_text;
                $this->acwc_tagline = $this->acwc_preview_title . $this->tagline_text;
            } else {

                if ( $this->acwc_language == 'ko' || $this->acwc_language == 'hi' || $this->acwc_language == 'ar' ) {
                    $this->acwc_intro = $this->intro_text . $this->acwc_preview_title;
                    $this->acwc_conclusion = $this->conclusion_text . $this->acwc_preview_title;
                    $this->acwc_tagline = $this->acwc_preview_title . $this->tagline_text;
                } else {
                    $this->acwc_intro = $this->intro_text . $this->acwc_preview_title;
                    $this->acwc_conclusion = $this->conclusion_text . $this->acwc_preview_title;
                    $this->acwc_tagline = $this->tagline_text . $this->acwc_preview_title;
                }
            }
            $this->acwc_cta = ( isset( $this->acwc_languages['mycta'] ) ? sprintf( $this->acwc_languages['mycta'], $this->acwc_preview_title, $this->acwc_target_url_cta ) : '' );
        }

        public function sleep_request()
        {
            if($this->acwc_engine == 'gpt-3.5-turbo' || $this->acwc_engine == 'gpt-4' || $this->acwc_engine == 'gpt-4-32k'){
                sleep($this->acwc_sleep);
            }
        }

        public function acwc_generator($step = 'heading')
        {
            /*Generate Heading*/
            if($step == 'heading'){
                $this->sleep_request();
                if($this->acwc_modify_headings && $this->generate_continue){
                    $this->acwc_headings = sanitize_text_field( $_REQUEST["hfHeadings"] );
                    $this->acwc_result['next_step'] = 'content';
                    $this->acwc_result['data'] = $this->acwc_headings;
                    $this->acwc_result['status'] = 'success';
                }
                else{
                    if($this->acwc_engine == 'gpt-3.5-turbo' || $this->acwc_engine == 'gpt-4' || $this->acwc_engine == 'gpt-4-32k'){
                        $this->acwc_opts['prompt'] = $this->acwc_languages['heading_prompt_turbo'].' '.$this->acwc_prompt;
                    }
                    else{
                        $this->acwc_opts['prompt'] = $this->acwc_prompt;
                    }
                    $acwc_request = $this->acwc_request($this->acwc_opts);
                    if($acwc_request['status'] == 'error'){
                        $this->acwc_result['status'] = 'error';
                        $this->acwc_result['msg'] = $acwc_request['msg'];
                        $this->error_msg = $acwc_request['msg'];
                    }
                    else{
                        $acwc_response = $acwc_request['data'];
                        $acwc_response = preg_replace('/\n$/', '', preg_replace('/^\n/', '', preg_replace('/[\r\n]+/', "\n", $acwc_response)));
                        $acwc_response = preg_split("/\r\n|\n|\r/", $acwc_response);
                        $acwc_response = preg_replace('/^\\d+\\.\\s/', '', $acwc_response);
                        $acwc_response = preg_replace('/\\.$/', '', $acwc_response);
                        $acwc_response = array_splice($acwc_response, 0, strval($this->acwc_number_of_heading));
                        $headings = array();
                        foreach($acwc_response as $item){
                            $headings[] = str_replace('"','', $item);
                        }
                        $this->acwc_headings = $headings;
                        $this->acwc_result['next_step'] = 'content';
                        $this->acwc_result['data'] = implode('||', $headings);
                        $this->acwc_result['status'] = 'success';
                        if ($this->acwc_modify_headings && !$this->generate_continue) {
                            $this->acwc_result['next_step'] = 'modify_heading';
                        }
                        $this->acwc_result['tokens'] += $acwc_request['tokens'];
                        $this->acwc_result['length'] += $acwc_request['length'];
                    }
                }
            }
            
            if($step == 'content'){
                foreach ( $this->acwc_headings as $key => $value ) {
                    $this->sleep_request();
                    $withstyle = $value . '. ' . $this->style_text . ', ' . $this->tone_text . '.';
                    if ( !empty(${$this->acwc_words_to_avoid}) ) {
                        $withstyle = $value . '. ' . $this->style_text . ', ' . $this->tone_text . ', ' . $this->avoid_text . '.';
                    }
                    if($this->acwc_engine == 'gpt-3.5-turbo' || $this->acwc_engine == 'gpt-4' || $this->acwc_engine == 'gpt-4-32k') {
                        $this->acwc_opts['prompt'] = sprintf($this->acwc_languages['content_prompt_turbo'],$this->acwc_preview_title).' '.$withstyle;
                    }
                    else{
                        $this->acwc_opts['prompt'] = $withstyle;
                    }
                    $acwc_request = $this->acwc_request($this->acwc_opts);
                    if($acwc_request['status'] == 'error'){
                        $this->acwc_result['status'] = 'error';
                        $this->acwc_result['msg'] = $acwc_request['msg'];
                        $this->error_msg = $acwc_request['msg'];
                    }
                    else{
                        $acwc_response = $acwc_request['data'];
                        $value = str_replace( '\\/', '', $value );
                        $value = str_replace( '\\', '', $value );
                        $value = trim( $value );
                        // we will add h tag if the user wants to
                        $acwc_heading_id = 'acwc-'.sanitize_title($value);
                        $this->acwc_toc_list[] = $value;
                        if ( $this->acwc_heading_tag == "h1" ) {
                            $result = "<h1 id=\"$acwc_heading_id\">" . $value . "</h1>" . $acwc_response;
                        } elseif ( $this->acwc_heading_tag == "h2" ) {
                            $result = "<h2 id=\"$acwc_heading_id\">" . $value . "</h2>" . $acwc_response;
                        } elseif ( $this->acwc_heading_tag == "h3" ) {
                            $result = "<h3 id=\"$acwc_heading_id\">" . $value . "</h3>" . $acwc_response;
                        } elseif ( $this->acwc_heading_tag == "h4" ) {
                            $result = "<h4 id=\"$acwc_heading_id\">" . $value . "</h4>" . $acwc_response;
                        } elseif ( $this->acwc_heading_tag == "h5" ) {
                            $result = "<h5 id=\"$acwc_heading_id\">" . $value . "</h5>" . $acwc_response;
                        } elseif ( $this->acwc_heading_tag == "h6" ) {
                            $result = "<h6 id=\"$acwc_heading_id\">" . $value . "</h6>" . $acwc_response;
                        } else {
                            $result = "<h2 id=\"$acwc_heading_id\">" . $value . "</h2>" . $acwc_response;
                        }
                        $this->acwc_result['content'] = $this->acwc_result['content'].$result;
                        $this->acwc_result['status'] = 'success';
                        $this->acwc_result['next_step'] = 'intro';
                        $this->acwc_result['tokens'] += $acwc_request['tokens'];
                        $this->acwc_result['length'] += $acwc_request['length'];
                    }
                }
            }
            
            if($step == 'intro'){
                if($this->acwc_add_intro){
                    $this->sleep_request();
                    if($this->acwc_engine == 'gpt-3.5-turbo' || $this->acwc_engine == 'gpt-4' || $this->acwc_engine == 'gpt-4-32k') {
                        $this->acwc_opts['prompt'] = $this->acwc_languages['fixed_prompt_turbo'].' '.$this->acwc_intro;
                    }
                    else{
                        $this->acwc_opts['prompt'] = $this->acwc_intro;
                    }
                    $acwc_request = $this->acwc_request($this->acwc_opts);
                    if($acwc_request['status'] == 'error'){
                        $this->acwc_result['status'] = 'error';
                        $this->acwc_result['msg'] = $acwc_request['msg'];
                        $this->error_msg = $acwc_request['msg'];
                    }
                    else{
                        $acwc_response = $acwc_request['data'];
                        $acwc_toc_list_new = array($this->introduction);
                        foreach($this->acwc_toc_list as $acwc_toc_item){
                            $acwc_toc_list_new[] = $acwc_toc_item;
                        }
                        $this->acwc_toc_list = $acwc_toc_list_new;
                        $acwc_introduction_id = 'acwc-'.sanitize_title($this->introduction);
                        $acwc_response = '<'.$this->acwc_intro_title_tag.' id="'.$acwc_introduction_id.'">'.$this->introduction.'</'.$this->acwc_intro_title_tag.'>'.$acwc_response;
                        $this->acwc_result['content'] = $acwc_response . $this->acwc_result['content'];
                        $this->acwc_result['status'] = 'success';
                        $this->acwc_result['next_step'] = 'faq';
                        $this->acwc_result['tokens'] += $acwc_request['tokens'];
                        $this->acwc_result['length'] += $acwc_request['length'];
                    }
                }
                else{
                    $this->acwc_result['status'] = 'success';
                    $this->acwc_result['next_step'] = 'faq';
                }
            }
            
            if($step == 'faq'){
                if($this->acwc_add_faq){
                    $this->sleep_request();
                    if($this->acwc_engine == 'gpt-3.5-turbo' || $this->acwc_engine == 'gpt-4' || $this->acwc_engine == 'gpt-4-32k') {
                        $this->acwc_opts['prompt'] = $this->acwc_languages['fixed_prompt_turbo'].' '.$this->faq_text;
                    }
                    else{
                        $this->acwc_opts['prompt'] = $this->faq_text;
                    }
                    $acwc_request = $this->acwc_request($this->acwc_opts);
                    if($acwc_request['status'] == 'error'){
                        $this->acwc_result['status'] = 'error';
                        $this->acwc_result['msg'] = $acwc_request['msg'];
                        $this->error_msg = $acwc_request['msg'];
                    }
                    else{
                        $acwc_response = $acwc_request['data'];
                        $this->acwc_toc_list[] = $this->faq_heading;
                        $acwc_faq_id = 'acwc-'.sanitize_title($this->faq_heading);
                        $acwc_response = "<h2 id=\"$acwc_faq_id\">" . $this->faq_heading . "</h2>" . $acwc_response;
                        $this->acwc_result['content'] = $this->acwc_result['content'].$acwc_response;
                        $this->acwc_result['status'] = 'success';
                        $this->acwc_result['next_step'] = 'conclusion';
                        $this->acwc_result['tokens'] += $acwc_request['tokens'];
                        $this->acwc_result['length'] += $acwc_request['length'];
                    }
                }
                else{
                    $this->acwc_result['status'] = 'success';
                    $this->acwc_result['next_step'] = 'conclusion';
                }
            }
            
            if($step == 'conclusion'){
                if($this->acwc_add_conclusion){
                    $this->sleep_request();
                    if($this->acwc_engine == 'gpt-3.5-turbo' || $this->acwc_engine == 'gpt-4' || $this->acwc_engine == 'gpt-4-32k') {
                        $this->acwc_opts['prompt'] = $this->acwc_languages['fixed_prompt_turbo'].' '.$this->acwc_conclusion;
                    }
                    else{
                        $this->acwc_opts['prompt'] = $this->acwc_conclusion;
                    }
                    $acwc_request = $this->acwc_request($this->acwc_opts);
                    if($acwc_request['status'] == 'error'){
                        $this->acwc_result['status'] = 'error';
                        $this->acwc_result['msg'] = $acwc_request['msg'];
                        $this->error_msg = $acwc_request['msg'];
                    }
                    else{
                        $acwc_response = $acwc_request['data'];
                        $this->acwc_toc_list[] = $this->conclusion;
                        $acwc_conclusion_id = 'acwc-'.sanitize_title($this->conclusion);
                        $acwc_response = '<'.$this->acwc_conclusion_title_tag.' id="'.$acwc_conclusion_id.'">'.$this->conclusion.'</'.$this->acwc_conclusion_title_tag.'>'.$acwc_response;
                        $this->acwc_result['content'] = $this->acwc_result['content'].$acwc_response;
                        $this->acwc_result['status'] = 'success';
                        $this->acwc_result['next_step'] = 'tagline';
                        $this->acwc_result['tokens'] += $acwc_request['tokens'];
                        $this->acwc_result['length'] += $acwc_request['length'];
                    }
                }
                else{
                    $this->acwc_result['status'] = 'success';
                    $this->acwc_result['next_step'] = 'tagline';
                }
            }
            
            if($step == 'tagline'){
                if($this->acwc_add_tagline){
                    $this->sleep_request();
                    if($this->acwc_engine == 'gpt-3.5-turbo' || $this->acwc_engine == 'gpt-4' || $this->acwc_engine == 'gpt-4-32k') {
                        $this->acwc_opts['prompt'] = $this->acwc_languages['fixed_prompt_turbo'].' '.$this->acwc_tagline;
                    }
                    else{
                        $this->acwc_opts['prompt'] = $this->acwc_tagline;
                    }
                    $acwc_request = $this->acwc_request($this->acwc_opts);
                    if($acwc_request['status'] == 'error'){
                        $this->acwc_result['status'] = 'error';
                        $this->acwc_result['msg'] = $acwc_request['msg'];
                        $this->error_msg = $acwc_request['msg'];
                    }
                    else{
                        $this->acwc_result['status'] = 'success';
                        $acwc_response = $acwc_request['data'];
                        $acwc_response = "<p>" . $acwc_response . "</p>";
                        $this->acwc_result['content'] = $acwc_response.$this->acwc_result['content'];
                        $this->acwc_result['tokens'] += $acwc_request['tokens'];
                        $this->acwc_result['length'] += $acwc_request['length'];
                    }
                }
                else{
                    $this->acwc_result['status'] = 'success';
                }
                if($this->acwc_seo_meta_desc){
                    $this->acwc_result['next_step'] = 'seo';
                }
                else{
                    $this->acwc_result['next_step'] = 'addition';
                }
            }
            
            if($step == 'seo'){
                $this->acwc_result['next_step'] = 'addition';
                if($this->acwc_seo_meta_desc){
                    $this->sleep_request();
                    $meta_desc_prompt = ( isset( $this->acwc_languages['meta_desc_prompt'] ) && !empty($this->acwc_languages['meta_desc_prompt']) ? sprintf( $this->acwc_languages['meta_desc_prompt'], $this->acwc_preview_title ) : 'Write a meta description about: ' . $this->acwc_preview_title .'. Max: 155 characters');
                    if($this->acwc_engine == 'gpt-3.5-turbo' || $this->acwc_engine == 'gpt-4' || $this->acwc_engine == 'gpt-4-32k') {
                        $this->acwc_opts['prompt'] = $this->acwc_languages['fixed_prompt_turbo'].' '.$meta_desc_prompt;
                    }
                    else{
                        $this->acwc_opts['prompt'] = $meta_desc_prompt;
                    }
                    $acwc_request = $this->acwc_request($this->acwc_opts);
                    if($acwc_request['status'] == 'error'){
                        $this->acwc_result['status'] = 'error';
                        $this->acwc_result['msg'] = $acwc_request['msg'];
                        $this->error_msg = $acwc_request['msg'];
                    }
                    else{
                        $acwc_response = $acwc_request['data'];
                        $this->acwc_result['status'] = 'success';
                        $this->acwc_result['description'] = $acwc_response;
                        $this->acwc_result['tokens'] += $acwc_request['tokens'];
                        $this->acwc_result['length'] += $acwc_request['length'];
                    }
                }
                else{
                    $this->acwc_result['status'] = 'success';
                }
            }
            
            if($step == 'addition'){
                if($this->acwc_add_keywords_bold){
                    if($this->acwc_keywords != ''){
                        if ( strpos( $this->acwc_keywords, ',' ) !== false ) {
                            $keywords = explode( ",", $this->acwc_keywords );
                        } else {
                            $keywords = array( $this->acwc_keywords );
                        }

                        
                        foreach ( $keywords as $keyword ) {
                            $keyword = trim( $keyword );
                            
                            $this->acwc_result['content'] = preg_replace(
                                '/(?<!<h[1-6]><a href=")(?<!<a href=")(?<!<h[1-6]>)(?<!<h[1-6]><strong>)(?<!<strong>)(?<!<h[1-6]><em>)(?<!<em>)(?<!<h[1-6]><strong><em>)(?<!<strong><em>)(?<!<h[1-6]><em><strong>)(?<!<em><strong>)\\b' . $keyword . '\\b(?![^<]*<\\/a>)(?![^<]*<\\/h[1-6]>)(?![^<]*<\\/strong>)(?![^<]*<\\/em>)(?![^<]*<\\/strong><\\/em>)(?![^<]*<\\/em><\\/strong>)/i',
                                '<strong>'.$keyword.'</strong>',
                                $this->acwc_result['content']
                            );
                        }
                    }
                }
                if($this->acwc_target_url != '' && $this->acwc_anchor_text != ''){
                    $this->acwc_result['content'] = preg_replace(
                        '/(?<!<h[1-6]><a href=")(?<!<a href=")(?<!<h[1-6]>)(?<!<h[1-6]><strong>)(?<!<strong>)(?<!<h[1-6]><em>)(?<!<em>)(?<!<h[1-6]><strong><em>)(?<!<strong><em>)(?<!<h[1-6]><em><strong>)(?<!<em><strong>)\\b' . $this->acwc_anchor_text . '\\b(?![^<]*<\\/a>)(?![^<]*<\\/h[1-6]>)(?![^<]*<\\/strong>)(?![^<]*<\\/em>)(?![^<]*<\\/strong><\\/em>)(?![^<]*<\\/em><\\/strong>)/i',
                        '<a href="' . $this->acwc_target_url . '">' . $this->acwc_anchor_text . '</a>',
                        $this->acwc_result['content'],
                        1
                    );
                }
                $this->acwc_result['status'] = 'success';
                if($this->acwc_target_url_cta !== ''){
                    $this->sleep_request();
                    if($this->acwc_engine == 'gpt-3.5-turbo' || $this->acwc_engine == 'gpt-4' || $this->acwc_engine == 'gpt-4-32k') {
                        $this->acwc_opts['prompt'] = $this->acwc_languages['fixed_prompt_turbo'].' '.$this->acwc_cta;
                    }
                    else{
                        $this->acwc_opts['prompt'] = $this->acwc_cta;
                    }
                    $acwc_request = $this->acwc_request($this->acwc_opts);
                    if($acwc_request['status'] == 'error'){
                        $this->acwc_result['status'] = 'error';
                        $this->acwc_result['msg'] = $acwc_request['msg'];
                        $this->error_msg = $acwc_request['msg'];
                    }
                    else{
                        $acwc_response = $acwc_request['data'];
                        $acwc_response = "<p>" . $acwc_response . "</p>";
                        if ( $this->acwc_cta_pos == "beg" ) {
                            $this->acwc_result['content'] = preg_replace(
                                '/(<h[1-6]>)/',
                                $acwc_response . ' $1',
                                $this->acwc_result['content'],
                                1
                            );
                        } else {
                            $this->acwc_result['content'] = $this->acwc_result['content'] . $acwc_response;
                        }
                        $this->acwc_result['tokens'] += $acwc_request['tokens'];
                        $this->acwc_result['length'] += $acwc_request['length'];
                    }
                }
                if($this->acwc_toc && is_array($this->acwc_toc_list) && count($this->acwc_toc_list)){
                    $acwc_table_content = '<ul class="acwc_toc"><li>';
                    if($this->acwc_toc_title !== ''){
                        $acwc_table_content .= '<'.$this->acwc_toc_title_tag.'>'.$this->acwc_toc_title.'</'.$this->acwc_toc_title_tag.'>';
                    }
                    $acwc_table_content .= '<ul>';
                    foreach($this->acwc_toc_list as $acwc_toc_item){
                        $acwc_toc_item_id = 'acwc-'.sanitize_title($acwc_toc_item);
                        $acwc_table_content .= '<li><a href="#'.$acwc_toc_item_id.'">'.$acwc_toc_item.'</a></li>';
                    }
                    $acwc_table_content .= '</ul>';
                    $acwc_table_content .= '</li></ul>';
                    $this->acwc_result['content'] = $acwc_table_content.$this->acwc_result['content'];
                }
                $this->acwc_result['next_step'] = 'image';
            }
            
            if($step == 'image'){
                $this->acwc_result['status'] = 'success';
                $this->acwc_result['next_step'] = 'featuredimage';
                if(!empty($this->acwc_image_source)){
                    if($this->acwc_image_source == 'dalle') {
                        $this->sleep_request();
                        $_acwc_image_style = '';
                        $_acwc_art_style = '';
                        if(!empty($this->acwc_img_style)){
                            $_acwc_art_style = (isset($this->acwc_languages['art_style']) && !empty($this->acwc_languages['art_style']) ? ' ' . $this->acwc_languages['art_style'] : '');
                            $_acwc_image_style = (isset($this->acwc_languages['img_styles'][$this->acwc_img_style]) && !empty($this->acwc_languages['img_styles'][$this->acwc_img_style]) ? ' ' . $this->acwc_languages['img_styles'][$this->acwc_img_style] : '');
                        }
                        $prompt_image = $this->acwc_preview_title . $_acwc_art_style . $_acwc_image_style;
                        if($this->acwc_custom_image_settings && is_array($this->acwc_custom_image_settings) && count($this->acwc_custom_image_settings)) {
                            $prompt_elements = array(
                                'artist' => 'Painter',
                                'photography_style' => 'Photography Style',
                                'composition' => 'Composition',
                                'resolution' => 'Resolution',
                                'color' => 'Color',
                                'special_effects' => 'Special Effects',
                                'lighting' => 'Lighting',
                                'subject' => 'Subject',
                                'camera_settings' => 'Camera Settings',
                            );
                            foreach ($this->acwc_custom_image_settings as $key => $value) {
                                if ($value != "None") {
                                    $prompt_image = $prompt_image . ". " . $prompt_elements[$key] . ": " . $value;
                                }
                            }
                        }
                        $acwc_request = $this->acwc_image([
                            "prompt" => $prompt_image,
                            "n" => 1,
                            "size" => $this->acwc_img_size,
                            "response_format" => "url",
                        ]);
                        if($acwc_request['status'] == 'error'){
                            $this->acwc_result['status'] = 'no_image';
                            $this->acwc_result['msg'] = $acwc_request['msg'];
                        }
                        else{
                            $this->acwc_result['img'] = trim($acwc_request['url']);
                        }
                    }
                    if($this->acwc_image_source == 'pexels'){
                        $acwc_pexels_response = $this->acwc_pexels_generator();
                        if(isset($acwc_pexels_response['pexels_response']) && !empty($acwc_pexels_response['pexels_response'])){
                            $this->acwc_result['img'] = trim($acwc_pexels_response['pexels_response']);
                        }
                    }
                    if(!empty($this->acwc_result['img'])){
                        $imgresult = "__ACWC_IMAGE__";
                        $half = intval($this->acwc_number_of_heading) / 2;
                        $half = round($half);
                        $half = $half - 1;
                        $acwc_heading_tag_default = $this->acwc_heading_tag;
                        if(isset($_REQUEST['acwc_heading_tag_modify']) && !empty($_REQUEST['acwc_heading_tag_modify'])){
                            $acwc_heading_tag_default = sanitize_text_field($_REQUEST['acwc_heading_tag_modify']);
                            $half = 0;
                        }
                        $acwc_content = explode("</" . $acwc_heading_tag_default . ">", $this->acwc_result['content']);
                        if(count($acwc_content) >= 2){
                            $acwc_content[$half+1] = $imgresult.'<br />'.$acwc_content[$half+1];
                        }
                        else{
                            $acwc_content[$half] = $acwc_content[$half] . $imgresult;
                        }
                        $this->acwc_result['content'] = implode("</" . $acwc_heading_tag_default . ">", $acwc_content);
                    }
                }
            }
            
            if($step == 'featuredimage'){
                $this->acwc_result['status'] = 'success';
                $this->acwc_result['next_step'] = 'DONE';
                if(!empty($this->acwc_featured_image_source)){
                    if($this->acwc_featured_image_source == 'dalle') {
                        $this->sleep_request();
                        $_acwc_image_style = '';
                        $_acwc_art_style = '';
                        if(!empty($this->acwc_img_style)){
                            $_acwc_art_style = (isset($this->acwc_languages['art_style']) && !empty($this->acwc_languages['art_style']) ? ' ' . $this->acwc_languages['art_style'] : '');
                            $_acwc_image_style = (isset($this->acwc_languages['img_styles'][$this->acwc_img_style]) && !empty($this->acwc_languages['img_styles'][$this->acwc_img_style]) ? ' ' . $this->acwc_languages['img_styles'][$this->acwc_img_style] : '');
                        }
                        $prompt_image = $this->acwc_preview_title . $_acwc_art_style . $_acwc_image_style;
                        if($this->acwc_custom_image_settings && is_array($this->acwc_custom_image_settings) && count($this->acwc_custom_image_settings)) {
                            $prompt_elements = array(
                                'artist' => 'Painter',
                                'photography_style' => 'Photography Style',
                                'composition' => 'Composition',
                                'resolution' => 'Resolution',
                                'color' => 'Color',
                                'special_effects' => 'Special Effects',
                                'lighting' => 'Lighting',
                                'subject' => 'Subject',
                                'camera_settings' => 'Camera Settings',
                            );
                            foreach ($this->acwc_custom_image_settings as $key => $value) {
                                if ($value != "None") {
                                    $prompt_image = $prompt_image . ". " . $prompt_elements[$key] . ": " . $value;
                                }
                            }
                        }
                        $acwc_request = $this->acwc_image([
                            "prompt" => $prompt_image,
                            "n" => 1,
                            "size" => $this->acwc_img_size,
                            "response_format" => "url",
                        ]);
                        if($acwc_request['status'] == 'error'){
                            $this->acwc_result['status'] = 'no_image';
                            $this->acwc_result['msg'] = $acwc_request['msg'];
                        }
                        else{
                            $this->acwc_result['featured_img'] = trim($acwc_request['url']);
                        }
                    }
                    if($this->acwc_featured_image_source == 'pexels'){
                        $acwc_pexels_response = $this->acwc_pexels_generator();
                        if(isset($acwc_pexels_response['pexels_response']) && !empty($acwc_pexels_response['pexels_response'])){
                            $this->acwc_result['featured_img'] = trim($acwc_pexels_response['pexels_response']);
                        }
                    }
                }
            }
            $this->acwc_result['tocs'] = implode('||', $this->acwc_toc_list);
        }

        public function acwcResult()
        {
            return $this->acwc_result;
        }

        public function acwc_pexels_generator()
        {
            $acwc_result = array('status' => 'success');
            if(!empty($this->acwc_pexels_api)) {
                $acwc_pexels_url = 'https://api.pexels.com/v1/search?query='.$this->acwc_preview_title.'&per_page=1';
                if(!empty($this->acwc_pexels_orientation)){
                    $acwc_pexels_orientation = strtolower($this->acwc_pexels_orientation);
                    $acwc_pexels_url .= '&orientation='.$acwc_pexels_orientation;
                }
                $response = wp_remote_get($acwc_pexels_url,array(
                    'headers' => array(
                        'Authorization' => $this->acwc_pexels_api
                    ),
                    'timeout' => 100
                ));
                if(is_wp_error($response)){
                    $acwc_result['status'] = 'success';
                    $acwc_result['msg'] = $response->get_error_message();
                }
                else{
                    $body = json_decode($response['body'],true);
                    if($body && is_array($body) && isset($body['photos']) && is_array($body['photos']) && count($body['photos'])){
                        $acwc_pexels_key = 'medium';
                        if(!empty($this->acwc_pexels_size)){
                            $acwc_pexels_size = strtolower($this->acwc_pexels_size);
                            if(in_array($acwc_pexels_size,array('large','medium','small'))){
                                $acwc_pexels_key = $acwc_pexels_size;
                            }
                        }
                        if(isset($body['photos'][0]['src'][$acwc_pexels_key]) && !empty($body['photos'][0]['src'][$acwc_pexels_key])){
                            $acwc_result['pexels_response'] = trim($body['photos'][0]['src'][$acwc_pexels_key]);

                        }
                        else{
                            $acwc_result['status'] = 'no_image';
                            $acwc_result['msg'] = 'No image generated';
                        }
                    }
                    else{
                        $acwc_result['status'] = 'no_image';
                        $acwc_result['msg'] = 'No image generated';
                    }
                }

            }
            else{
                $acwc_result['status'] = 'error';
                $acwc_result['msg'] = 'Missing Pexels API Setting';
            }
            return $acwc_result;
        }
        public function acwc_count_words($text)
        {
            $text = trim(strip_tags(html_entity_decode($text,ENT_QUOTES)));
            $text = preg_replace("/[\n]+/", " ", $text);
            $text = preg_replace("/[\s]+/", "@SEPARATOR@", $text);
            $text_array = explode('@SEPARATOR@', $text);
            $count = count($text_array);
            $last_key = end($text_array);
            if (empty($last_key)) {
                $count--;
            }
            return $count;
        }

        public function acwc_image($opts)
        {
            $result = array('status' => 'error');
            $imgresult = $this->acwc->image($opts);
            $imgresult = json_decode($imgresult);
            if (isset($imgresult->error)) {
                $result['msg'] = esc_html($imgresult->error->message);
            } else {
                $result['status'] = 'success';
                $result['url'] = $imgresult->data[0]->url;
            }
            return $result;
        }

        public function acwc($acwc)
        {
            $this->acwc = $acwc;
        }

        public function acwc_request($opts)
        {
            $result = array('status' => 'error','tokens' => 0, 'length' => 0);
            if(!isset($opts['model']) || empty($opts['model'])){
                $opts['model'] = $this->acwc_engine;
            }
            $chat_model = false;
            if($opts['model'] == 'gpt-3.5-turbo' || $opts['model'] == 'gpt-4' || $opts['model'] == 'gpt-4-32k'){
                $chat_model = true;
                unset($opts['best_of']);
                $opts['messages'] = array(
                    array('role' => 'user', 'content' => $opts['prompt'])
                );
                unset($opts['prompt']);
                unset($opts['best_of']);
                $complete = $this->acwc->chat($opts);
            }
            else{
                $complete = $this->acwc->completion($opts);
            }
            $complete = json_decode( $complete );
            if ( isset( $complete->error ) ) {
                $result['msg'] = trim($complete->error->message);
                if(strpos($result['msg'],'exceeded your current quota') !== false){
                    $result['msg'] .= ' Please note that this message is coming from OpenAI and it is not related to our plugin. It means that you do not have enough credit from OpenAI. You can check your usage here: https://platform.openai.com/account/usage';
                }
            }
            else{
                if(isset($complete->choices) && is_array($complete->choices)) {
                    $result['status'] = 'success';
                    if($chat_model) {
                        $result['tokens'] = $complete->usage->total_tokens;
                        $result['data'] = isset($complete->choices[0]->message->content) ? trim($complete->choices[0]->message->content) : '';
                    }
                    else{
                        $result['tokens'] = $complete->usage->total_tokens;
                        $result['data'] = trim($complete->choices[0]->text);
                    }
                    if(empty($result['data'])){
                        $result['status'] = 'error';
                        $result['msg'] = 'The model predicted a completion that begins with a stop sequence, resulting in no output. Consider adjusting your prompt or stop sequences.';
                    }
                    else{
                        $result['length'] = $this->acwc_count_words($result['data']);
                    }
                }
                else $result['msg'] = 'The model predicted a completion that begins with a stop sequence, resulting in no output. Consider adjusting your prompt or stop sequences.';
            }
            return $result;
        }
    }
    ACWC_Generator::get_instance();
}
