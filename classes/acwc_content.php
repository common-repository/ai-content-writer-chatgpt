<?php

namespace ACWC;
if ( ! defined( 'ABSPATH' ) ) exit;

if ( !class_exists( '\\ACWC\\ACWC_Content' ) ) {
    final class ACWC_Content
    {
        private static  $instance = null ;
        public  $acwc_token_price = 0.02 / 1000 ;
        public  $acwc_limit_titles = 5 ;
        public  $acwc_extra_titles = 15 ;

        public static function get_instance()
        {
            if ( is_null( self::$instance ) ) {
                self::$instance = new self();
            }
            return self::$instance;
        }

        public function __construct()
        {
            add_action( 'wp_ajax_acwc_save_draft_post_extra', array( $this, 'acwc_save_draft_post' ) );
            add_action( 'wp_ajax_acwc_bulk_generator', array( $this, 'acwc_bulk_save' ) );
            add_action( 'wp_ajax_acwc_bulk_save_editor', array( $this, 'acwc_bulk_save_editor' ) );
            add_action( 'wp_ajax_acwc_bulk_cancel', array( $this, 'acwc_bulk_cancel' ) );
            add_action( 'wp_ajax_acwc_bulk_status', array( $this, 'acwc_bulk_status' ) );
            add_action( 'wp_ajax_acwc_read_csv', array( $this, 'acwc_read_csv' ) );
        }

        public function acwc_read_csv()
        {
            $acwc_result = array(
                'status' => 'error',
                'msg'    => 'Something went wrong',
            );
            if ( ! wp_verify_nonce( $_POST['nonce'], 'acwc-ajax-nonce' ) ) {
                $acwc_result['msg'] = WP_OPENAI_CG_NONCE_ERROR;
                wp_send_json($acwc_result);
            }
            if ( !empty($_FILES['file']) && empty($_FILES['file']['error']) ) {
                $acwc_file = $_FILES['file'];
                $acwc_csv_lines = array();

                if ( ($handle = fopen( $acwc_file['tmp_name'], 'r' )) !== false ) {
                    while ( ($data = fgetcsv( $handle, 100, ',' )) !== false ) {
                        if ( isset( $data[0] ) && !empty($data[0]) ) {
                            $acwc_csv_lines[] = $data[0];
                        }
                    }
                    fclose( $handle );
                }


                if ( count( $acwc_csv_lines ) ) {
                    if ( count( $acwc_csv_lines ) > $this->acwc_limit_titles ) {

                        if ( acwc_util_core()->acwc_is_pro() ) {
                            $acwc_result['notice'] = 'Your CSV was including more than ' . $this->acwc_limit_titles . ' lines so we are only processing first 10 lines';
                        } else {
                            $acwc_result['notice'] = 'Free users can only generate ' . $this->acwc_limit_titles . ' titles at a time. Please upgrade to the Pro plan to get access to more fields.';
                        }

                    }
                    $acwc_result['status'] = 'success';
                    $acwc_result['data'] = implode( '|', array_splice( $acwc_csv_lines, 0, $this->acwc_limit_titles ) );
                } else {
                    $acwc_result['msg'] = 'Your CSV file is empty';
                }

            }
            wp_send_json( $acwc_result );
        }

        public function acwc_bulk_cancel()
        {

            $acwc_result = array(
                'status' => 'error',
                'msg'    => 'Something went wrong',
            );
            if (!isset($_POST['acwc_nonce']) || !wp_verify_nonce($_POST['acwc_nonce'], 'acwc_nonce_action')) {
                $acwc_result['msg'] = WP_OPENAI_CG_NONCE_ERROR;
                wp_send_json($acwc_result);
            }
            if ( isset( $_POST['ids'] ) && !empty($_POST['ids']) ) {
                $acwc_ids = acwc_util_core()->sanitize_text_or_array_field($_POST['ids']);
                $acwc_bulks = get_posts( array(
                    'post_type'      => 'acwc_bulk',
                    'post_status'    => array(
                        'publish',
                        'pending',
                        'draft',
                        'trash'
                    ),
                    'post__in'       => $acwc_ids,
                    'posts_per_page' => -1,
                ) );

                if ( $acwc_bulks && is_array( $acwc_bulks ) && count( $acwc_bulks ) ) {
                    $acwc_bulk_id = false;
                    foreach ( $acwc_bulks as $acwc_bulk ) {
                        $acwc_bulk_id = $acwc_bulk->post_parent;
                        wp_update_post( array(
                            'ID'          => $acwc_bulk->ID,
                            'post_status' => 'inherit',
                        ) );
                    }
                    if ( $acwc_bulk_id && !empty($acwc_bulk_id) ) {
                        wp_update_post( array(
                            'ID'          => $acwc_bulk_id,
                            'post_status' => 'trash',
                        ) );
                    }
                }

            }

            wp_send_json( $acwc_result );
        }

        public function acwc_valid_date( $date, $format = 'Y-m-d H:i:s' )
        {
            $d = \DateTime::createFromFormat( $format, $date );
            return $d && $d->format( $format ) == $date;
        }

        public function acwc_bulk_save()
        {
            $acwc_result = array(
                'status' => 'error',
                'msg'    => 'Something went wrong',
            );
            if ( ! wp_verify_nonce( $_POST['nonce'], 'acwc-ajax-nonce' ) ) {
                $acwc_result['msg'] = WP_OPENAI_CG_NONCE_ERROR;
                wp_send_json($acwc_result);
            }
            if (isset($_POST['acwc_titles']) && !empty($_POST['acwc_titles'])) {
                $acwc_titles = acwc_util_core()->sanitize_text_or_array_field($_POST['acwc_titles']);
                $acwc_schedules = (isset($_POST['acwc_schedules']) && !empty($_POST['acwc_schedules']) ? acwc_util_core()->sanitize_text_or_array_field($_POST['acwc_schedules']) : array());
                $acwc_category = (isset($_POST['acwc_category']) && !empty($_POST['acwc_category']) ? acwc_util_core()->sanitize_text_or_array_field($_POST['acwc_category']) : array());

                if (is_array($acwc_titles)) {
                    $post_status = (isset($_POST['post_status']) && !empty($_POST['post_status']) ? sanitize_text_field($_POST['post_status']) : 'draft');
                    $acwc_track_title = '';
                    foreach ($acwc_titles as $acwc_title) {
                        if (!empty($acwc_title)) {
                            $acwc_track_title .= (empty($acwc_track_title) ? trim($acwc_title) : ', ' . $acwc_title);
                        }
                    }
                    $acwc_source = (isset($_POST['source']) && !empty($_POST['source']) ? sanitize_text_field($_POST['source']) : 'editor');

                    if (!empty($acwc_track_title)) {
                        $acwc_track_id = wp_insert_post(array(
                            'post_type' => 'acwc_tracking',
                            'post_title' => $acwc_track_title,
                            'post_status' => 'pending',
                            'post_mime_type' => $acwc_source,
                        ));

                        if (!is_wp_error($acwc_track_id)) {
                            foreach ($acwc_titles as $key => $acwc_title) {

                                if (!empty($acwc_title)) {
                                    $acwc_bulk_data = array(
                                        'post_type' => 'acwc_bulk',
                                        'post_title' => trim($acwc_title),
                                        'post_status' => 'pending',
                                        'post_parent' => $acwc_track_id,
                                        'post_password' => $post_status,
                                        'post_mime_type' => $acwc_source,
                                    );

                                    if (isset($acwc_schedules[$key]) && !empty($acwc_schedules[$key])) {
                                        $acwc_item_schedule = $acwc_schedules[$key] . ':00';
                                        if ($this->acwc_valid_date($acwc_item_schedule)) {
                                            $acwc_bulk_data['post_excerpt'] = $acwc_item_schedule;
                                        }
                                    }

                                    if (isset($acwc_category[$key]) && !empty($acwc_category[$key])) {
                                        $acwc_bulk_data['menu_order'] = sanitize_text_field($acwc_category[$key]);
                                    }

                                    wp_insert_post($acwc_bulk_data);
                                }

                            }
                            $acwc_result['id'] = $acwc_track_id;
                            $acwc_result['status'] = 'success';
                        }

                    }

                }

            }
            wp_send_json($acwc_result);
        }

        public function acwc_bulk_save_editor()
        {
            $acwc_result = array(
                'status' => 'error',
                'msg'    => 'Something went wrong',
            );
            if ( ! wp_verify_nonce( $_POST['_wpnonce'], 'acwc_bulk_save' ) ) {
                $acwc_result['msg'] = WP_OPENAI_CG_NONCE_ERROR;
                wp_send_json($acwc_result);
            }
            if(isset($_POST['bulk']) && is_array($_POST['bulk']) && count($_POST['bulk'])){
                $post_status = ( isset( $_POST['post_status'] ) && !empty($_POST['post_status']) ? sanitize_text_field( $_POST['post_status'] ) : 'draft' );
                $bulks = acwc_util_core()->sanitize_text_or_array_field($_POST['bulk']);
                $acwc_track_title = '';
                foreach($bulks as $bulk){
                    if (isset($bulk['title']) && !empty($bulk['title'])) {
                        $acwc_track_title .= ( empty($acwc_track_title) ? trim( $bulk['title'] ) : ', ' . $bulk['title'] );
                    }
                }
                $acwc_source = ( isset( $_POST['source'] ) && !empty($_POST['source']) ? sanitize_text_field( $_POST['source'] ) : 'editor' );
                if ( !empty($acwc_track_title) ) {
                    $acwc_track_id = wp_insert_post(array(
                        'post_type' => 'acwc_tracking',
                        'post_title' => $acwc_track_title,
                        'post_status' => 'pending',
                        'post_mime_type' => $acwc_source,
                    ));
                    if ( !is_wp_error( $acwc_track_id ) ) {
                        foreach ($bulks as $bulk) {
                            if (isset($bulk['title']) && !empty($bulk['title'])) {
                                $acwc_bulk_data = array(
                                    'post_type'      => 'acwc_bulk',
                                    'post_title'     => trim( $bulk['title'] ),
                                    'post_status'    => 'pending',
                                    'post_parent'    => $acwc_track_id,
                                    'post_password'  => $post_status,
                                    'post_mime_type' => $acwc_source,
                                );
                                if(isset($bulk['schedule']) && !empty($bulk['schedule'])){
                                    $acwc_item_schedule = $bulk['schedule'] . ':00';
                                    if ( $this->acwc_valid_date( $acwc_item_schedule ) ) {
                                        $acwc_bulk_data['post_excerpt'] = $acwc_item_schedule;
                                    }
                                }
                                if(isset($bulk['category']) && !empty($bulk['category'])){
                                    $acwc_bulk_data['menu_order'] = sanitize_text_field($bulk['category']);
                                }
                                if(isset($bulk['author']) && !empty($bulk['author'])){
                                    $acwc_bulk_data['post_author'] = sanitize_text_field($bulk['author']);
                                }
                                $acwc_bulk_id = wp_insert_post( $acwc_bulk_data );
                                if(isset($bulk['tags']) && !empty($bulk['tags'])){
                                    update_post_meta($acwc_bulk_id, '_acwc_tags', sanitize_text_field($bulk['tags']));
                                }
                                if(isset($bulk['keywords']) && !empty($bulk['keywords'])){
                                    update_post_meta($acwc_bulk_id, '_acwc_keywords', sanitize_text_field($bulk['keywords']));
                                }
                                if(isset($bulk['avoid']) && !empty($bulk['avoid'])){
                                    update_post_meta($acwc_bulk_id, '_acwc_avoid', sanitize_text_field($bulk['avoid']));
                                }
                                if(isset($bulk['anchor']) && !empty($bulk['anchor'])){
                                    update_post_meta($acwc_bulk_id, '_acwc_anchor', sanitize_text_field($bulk['anchor']));
                                }
                                if(isset($bulk['target']) && !empty($bulk['target'])){
                                    update_post_meta($acwc_bulk_id, '_acwc_target', sanitize_text_field($bulk['target']));
                                }
                                if(isset($bulk['cta']) && !empty($bulk['cta'])){
                                    update_post_meta($acwc_bulk_id, '_acwc_cta', sanitize_text_field($bulk['cta']));
                                }
                            }
                        }
                        $acwc_result['id'] = $acwc_track_id;
                        $acwc_result['status'] = 'success';
                    }
                }
            }
            wp_send_json( $acwc_result );
        }

        public function acwc_bulk_status()
        {
            $acwc_result = array(
                'status' => 'error',
                'msg'    => 'Something went wrong',
            );
            if (!isset($_POST['acwc_nonce']) || !wp_verify_nonce($_POST['acwc_nonce'], 'acwc_nonce_action')) {
                $acwc_result['msg'] = WP_OPENAI_CG_NONCE_ERROR;
                wp_send_json($acwc_result);
            }
            if ( isset( $_POST['ids'] ) && !empty($_POST['ids']) ) {
                $acwc_ids = acwc_util_core()->sanitize_text_or_array_field($_POST['ids']);
                $acwc_bulks = get_posts( array(
                    'post_type'      => 'acwc_bulk',
                    'post_status'    => array(
                        'publish',
                        'pending',
                        'draft',
                        'trash',
                        'inherit'
                    ),
                    'post__in'       => $acwc_ids,
                    'posts_per_page' => -1,
                ) );

                if ( $acwc_bulks && is_array( $acwc_bulks ) && count( $acwc_bulks ) ) {
                    $acwc_result['data'] = array();
                    $acwc_result['status'] = 'success';
                    foreach ( $acwc_bulks as $acwc_bulk ) {
                        $acwc_generator_run = get_post_meta( $acwc_bulk->ID, '_acwc_generator_run', true );
                        $acwc_generator_length = get_post_meta( $acwc_bulk->ID, '_acwc_generator_length', true );
                        $acwc_generator_token = get_post_meta( $acwc_bulk->ID, '_acwc_generator_token', true );
                        $acwc_generator_post_id = get_post_meta( $acwc_bulk->ID, '_acwc_generator_post', true );
                        $acwc_cost = 0;
                        $acwc_ai_model = get_post_meta($acwc_bulk->ID,'acwc_ai_model',true);
                        if(!empty($acwc_generator_token)) {
                            if ($acwc_ai_model == 'gpt-3.5-turbo') {
                                $acwc_cost = '$' . esc_html(number_format($acwc_generator_token * 0.002 / 1000, 5));
                            }
                            elseif ($acwc_ai_model == 'gpt-4') {
                                $acwc_cost = '$' . esc_html(number_format($acwc_generator_token * 0.06 / 1000, 5));
                            }
                            elseif ($acwc_ai_model == 'gpt-4-32k') {
                                $acwc_cost = '$' . esc_html(number_format($acwc_generator_token * 0.12 / 1000, 5));
                            } else {
                                $acwc_cost = '$' . esc_html(number_format($acwc_generator_token * $this->acwc_token_price, 5));
                            }
                        }
                        $acwc_result['data'][] = array(
                            'id'       => $acwc_bulk->ID,
                            'title'    => $acwc_bulk->post_title,
                            'status'   => $acwc_bulk->post_status,
                            'duration' => ( $acwc_generator_run ? $this->acwc_seconds_to_time( (int) $acwc_generator_run ) : '' ),
                            'word'     => $acwc_generator_length,
                            'token'    => $acwc_generator_token,
                            'cost'     => $acwc_cost,
                            'msg'      => get_post_meta( $acwc_bulk->ID, '_acwc_error', true ),
                            'url'      => ( empty($acwc_generator_post_id) ? '' : admin_url( 'post.php?post=' . $acwc_generator_post_id . '&action=edit' ) ),
                        );
                    }
                }

            }

            wp_send_json( $acwc_result );
        }

        public function acwc_save_description($post_id, $description)
        {
            global $wpdb;
            update_post_meta($post_id,'_acwc_meta_description',$description);
            $seo_option = get_option('_yoast_wpseo_metadesc',false);
            $seo_plugin_activated = acwc_util_core()->seo_plugin_activated();
            if($seo_plugin_activated == '_yoast_wpseo_metadesc' && $seo_option){
                update_post_meta($post_id,$seo_plugin_activated,$description);
            }
            $seo_option = get_option('_aioseo_description',false);
            if($seo_plugin_activated == '_aioseo_description' && $seo_option){
                update_post_meta($post_id,$seo_plugin_activated,$description);
                $check = $wpdb->get_row($wpdb->prepare("SELECT * FROM ".$wpdb->prefix."aioseo_posts WHERE post_id=%d",$post_id));
                if($check){
                    $wpdb->update($wpdb->prefix.'aioseo_posts',array(
                        'description' => $description
                    ), array(
                        'post_id' => $post_id
                    ));
                }
                else{
                    $wpdb->insert($wpdb->prefix.'aioseo_posts',array(
                        'post_id' => $post_id,
                        'description' => $description,
                        'created' => date('Y-m-d H:i:s'),
                        'updated' => date('Y-m-d H:i:s')
                    ));
                }
            }
            $seo_option = get_option('rank_math_description',false);
            if($seo_plugin_activated == 'rank_math_description' && $seo_option){
                update_post_meta($post_id,$seo_plugin_activated,$description);
            }
        }

        public function acwc_bulk_error_log($id, $msg)
        {
            update_post_meta( $id, '_acwc_error', $msg );
            wp_update_post( array(
                'ID'          => $id,
                'post_status' => 'trash',
            ) );
        }

        public function acwc_bulk_generator()
        {
            global  $wpdb ;
            $acwc_cron_added = get_option( '_acwc_cron_added', '' );

            if ( empty($acwc_cron_added) ) {
                update_option( '_acwc_cron_added', time() );
            } else {
                $sql = "SELECT * FROM " . $wpdb->posts . " WHERE post_type='acwc_bulk' AND post_status='pending' ORDER BY post_date ASC";
                $acwc_single = $wpdb->get_row( $sql );
                update_option( '_acwc_crojob_bulk_last_time', time() );
                $acwc_restart_queue = get_option('acwc_restart_queue','');
                $acwc_try_queue = get_option('acwc_try_queue','');
                if(!empty($acwc_restart_queue) && !empty($acwc_try_queue)) {
                    $acwc_fix_sql = $wpdb->prepare("SELECT p.ID,(SELECT m.meta_value FROM ".$wpdb->postmeta." m WHERE m.post_id=p.ID AND m.meta_key='acwc_try_queue_time') as try_time FROM ".$wpdb->posts." p WHERE (p.post_status='draft' OR p.post_status='trash') AND p.post_type='acwc_bulk' AND p.post_modified <  NOW() - INTERVAL %d MINUTE",$acwc_restart_queue);
                    $in_progress_posts = $wpdb->get_results($acwc_fix_sql);
                    if($in_progress_posts && is_array($in_progress_posts) && count($in_progress_posts)){
                        foreach($in_progress_posts as $in_progress_post){
                            if(!$in_progress_post->try_time || (int)$in_progress_post->try_time < $acwc_try_queue){
                                wp_update_post(array(
                                    'ID'          => $in_progress_post->ID,
                                    'post_status' => 'pending',
                                ));
                                wp_update_post(array(
                                    'ID'          => $in_progress_post->post_parent,
                                    'post_status' => 'pending',
                                ));
                                $next_time = (int)$in_progress_post->try_time + 1;
                                update_post_meta($in_progress_post->ID,'acwc_try_queue_time',$next_time);
                            }
                        }
                    }
                }
                
                if ( $acwc_single ) {
                    $acwc_generator_start = microtime( true );
                    $acwc_generator_tokens = 0;
                    $acwc_generator_text_length = 0;
                    try {
                        wp_update_post( array(
                            'ID'          => $acwc_single->ID,
                            'post_status' => 'draft',
                            'post_modified' => date('Y-m-d H:i:s')
                        ) );
                        $acwc_generator = ACWC_Generator::get_instance();
                        $acwc = ACWCGPT::get_instance()->acwc();
                        $acwc_generator_result = array();
                        if(!$acwc){
                            $this->acwc_bulk_error_log($acwc_single->ID, 'Missing API Setting');
                        }
                        else{
                            $steps = array('heading','content','intro','faq','conclusion','tagline','seo','addition','image','featuredimage');
                            $acwc_generator->init($acwc,$acwc_single->post_title,true,$acwc_single->ID);
                            $acwc_has_error = false;
                            $break_step = '';
                            foreach ($steps as $step){
                                $acwc_generator->acwc_generator($step);
                                if($acwc_generator->error_msg){
                                    $break_step = $step;
                                    $acwc_has_error = $acwc_generator->error_msg;
                                    break;
                                }
                            }
                            if($acwc_has_error){
                                $this->acwc_bulk_error_log($acwc_single->ID, $acwc_has_error.'. Break at step '.$break_step);
                                $acwc_running = ACWC_PLUGIN_DIR.'/acwc_running.txt';
                                if(file_exists($acwc_running)){
                                    unlink($acwc_running);
                                }
                            }
                            else{
                                $acwc_generator_result = $acwc_generator->acwcResult();
                                $acwc_generator_text_length = $acwc_generator_result['length'];
                                $acwc_generator_tokens = $acwc_generator_result['tokens'];
                                $acwc_allowed_html_content_post = wp_kses_allowed_html( 'post' );
                                $acwc_content = wp_kses( $acwc_generator_result['content'], $acwc_allowed_html_content_post );
                                $acwc_post_status = ( $acwc_single->post_password == 'draft' ? 'draft' : 'publish' );
                                $acwc_image_attachment_id = false;
                                if(isset($acwc_generator_result['img']) && !empty($acwc_generator_result['img'])){
                                    $acwc_image_url = sanitize_url($acwc_generator_result['img']);
                                    $acwc_image_attachment_id = $this->acwc_save_image($acwc_image_url,$acwc_single->post_title);
                                    if($acwc_image_attachment_id['status'] == 'success'){
                                        $acwc_image_attachment_url = wp_get_attachment_url($acwc_image_attachment_id['id']);
                                        $acwc_content = str_replace("__ACWC_IMAGE__", '<img src="'.$acwc_image_attachment_url.'" alt="'.$acwc_single->post_title.'" />', $acwc_content);
                                    }
                                }
                               
                                $acwc_content = str_replace("__ACWC_IMAGE__", '', $acwc_content);

                                $acwc_post_data = array(
                                    'post_title'   => $acwc_single->post_title,
                                    'post_author'  => $acwc_single->post_author,
                                    'post_content' => $acwc_content,
                                    'post_status'  => $acwc_post_status,
                                );
                                if($acwc_single->menu_order && $acwc_single->menu_order > 0){
                                    $acwc_post_data['post_category'] = array($acwc_single->menu_order);
                                }

                                if ( !empty($acwc_single->post_excerpt) ) {
                                    $acwc_post_data['post_status'] = 'future';
                                    $acwc_post_data['post_date'] = $acwc_single->post_excerpt;
                                    $acwc_post_data['post_date_gmt'] = $acwc_single->post_excerpt;
                                }

                                $acwc_post_id = wp_insert_post( $acwc_post_data );

                                if ( is_wp_error( $acwc_post_id ) ) {
                                    update_post_meta( $acwc_single->ID, '_acwc_error', $acwc_post_id->get_error_message() );
                                    wp_update_post( array(
                                        'ID'          => $acwc_single->ID,
                                        'post_status' => 'trash',
                                    ) );
                                } else {
                                    $acwc_ai_model = get_option('acwc_ai_model','text-davinci-003');
                                    add_post_meta($acwc_post_id,'acwc_ai_model',$acwc_ai_model);
                                    add_post_meta($acwc_single->ID,'acwc_ai_model',$acwc_ai_model);
                                    if(isset($acwc_generator_result['description']) && !empty($acwc_generator_result['description'])){
                                        $this->acwc_save_description($acwc_post_id,sanitize_text_field($acwc_generator_result['description']));
                                    }

                                    if(isset($acwc_generator_result['featured_img']) && !empty($acwc_generator_result['featured_img'])){
                                        $acwc_featured_image_url = sanitize_url($acwc_generator_result['featured_img']);
                                        $acwc_image_attachment_id = $this->acwc_save_image($acwc_featured_image_url,$acwc_single->post_title);
                                        if($acwc_image_attachment_id['status'] == 'success'){
                                            update_post_meta( $acwc_post_id, '_thumbnail_id', $acwc_image_attachment_id['id']);
                                        }
                                    }

                                    $acwc_tags = get_post_meta($acwc_single->ID, '_acwc_tags',true);
                                    if(!empty($acwc_tags)){
                                        $acwc_tags = array_map('trim', explode(',', $acwc_tags));
                                        if($acwc_tags && is_array($acwc_tags) && count($acwc_tags)){
                                            wp_set_post_tags($acwc_post_id,$acwc_tags);
                                        }
                                    }
                                    update_post_meta( $acwc_single->ID, '_acwc_generator_post', $acwc_post_id );
                                    wp_update_post( array(
                                        'ID'          => $acwc_single->ID,
                                        'post_status' => 'publish',
                                    ));
                                }
                            }
                        }
                    } catch ( \Exception $exception ) {
                    }
                    $acwc_bulks = get_posts( array(
                        'post_type'      => 'acwc_bulk',
                        'post_status'    => array(
                            'publish',
                            'pending',
                            'draft',
                            'trash',
                            'inherit'
                        ),
                        'post_parent'    => $acwc_single->post_parent,
                        'posts_per_page' => -1,
                    ) );
                    $acwc_bulk_completed = true;
                    $acwc_bulk_error = false;
                    foreach ( $acwc_bulks as $acwc_bulk ) {
                        if ( $acwc_bulk->post_status == 'pending' || $acwc_bulk->post_status == 'draft' ) {
                            $acwc_bulk_completed = false;
                        }

                        if ( $acwc_bulk->post_status == 'trash' ) {
                            $acwc_bulk_error = true;
                            $acwc_bulk_completed = false;
                        }

                    }
                    if ( $acwc_bulk_completed ) {
                        wp_update_post( array(
                            'ID'          => $acwc_single->post_parent,
                            'post_status' => 'publish',
                        ) );
                    }
                    if ( $acwc_bulk_error ) {
                        wp_update_post( array(
                            'ID'          => $acwc_single->post_parent,
                            'post_status' => 'draft',
                        ) );
                    }
                    $acwc_generator_end = microtime( true ) - $acwc_generator_start;
                    update_post_meta( $acwc_single->ID, '_acwc_generator_run', $acwc_generator_end );
                    update_post_meta( $acwc_single->ID, '_acwc_generator_length', $acwc_generator_text_length );
                    update_post_meta( $acwc_single->ID, '_acwc_generator_token', $acwc_generator_tokens );
                }

            }

        }

        public function acwc_seconds_to_time( $seconds )
        {
            $dtF = new \DateTime( '@0' );
            $dtT = new \DateTime( "@{$seconds}" );
            return $dtF->diff( $dtT )->format( '%h h, %i m and %s s' );
        }

        public function acwc_post_image($post_id, $acwc_title = '')
        {
            if(isset($_REQUEST['acwc_content_changed']) && !empty($_REQUEST['acwc_content_changed'])){
                $my_post = array(
                    'ID'          => $post_id,
                    'post_status' => 'draft',
                );
                if ( isset( $_REQUEST['_wporg_preview_title'] ) && $_REQUEST['_wporg_preview_title'] != '' ) {
                    $my_post['post_title'] = sanitize_text_field($_REQUEST['_wporg_preview_title']);
                }
                if ( isset( $_REQUEST['_wporg_generated_text'] ) && $_REQUEST['_wporg_generated_text'] != '' ) {
                    $my_post['post_content'] = wp_kses_post($_REQUEST['_wporg_generated_text']);
                }
                $acwc_content = $my_post['post_content'];
                $acwc_image_attachment_id = false;
                if(isset($_REQUEST['acwc_image_url']) && !empty($_REQUEST['acwc_image_url'])){
                    $acwc_image_url = sanitize_url($_REQUEST['acwc_image_url']);
                    $acwc_image_attachment_id = $this->acwc_save_image($acwc_image_url, $acwc_title);
                    if($acwc_image_attachment_id['status'] == 'success'){
                        $acwc_image_attachment_url = wp_get_attachment_url($acwc_image_attachment_id['id']);
                        $acwc_content = str_replace('<img />', '<img src="'.$acwc_image_attachment_url.'" alt="'.$acwc_title.'" />', $acwc_content);
                        $acwc_content = str_replace("<img src=\\'__ACWC_IMAGE__\\' alt=\\'".$acwc_title."\\' />", '<img src="'.$acwc_image_attachment_url.'" alt="'.$acwc_title.'" />', $acwc_content);
                        $acwc_content = str_replace("<img src=\'__ACWC_IMAGE__\' alt=\'".$acwc_title."\' />", '<img src="'.$acwc_image_attachment_url.'" alt="'.$acwc_title.'" />', $acwc_content);
                        $acwc_content = str_replace("__ACWC_IMAGE__", '<img src="'.$acwc_image_attachment_url.'" alt="'.$acwc_title.'" />', $acwc_content);
                    }
                }
                // Fix empty image
                $acwc_content = str_replace("__ACWC_IMAGE__", '', $acwc_content);
                $my_post['post_content'] = $acwc_content;
                if(isset($_REQUEST['acwc_featured_img_url']) && !empty($_REQUEST['acwc_featured_img_url'])){
                    $acwc_featured_img_url = sanitize_url($_REQUEST['acwc_featured_img_url']);
                    $acwc_image_attachment_id = $this->acwc_save_image($acwc_featured_img_url, $acwc_title);
                    if($acwc_image_attachment_id['status'] == 'success'){
                        update_post_meta( $post_id, '_thumbnail_id', $acwc_image_attachment_id['id']);
                    }
                }
                wp_update_post( $my_post );
            }
        }

        public function acwc_save_image($imageurl, $acwc_title = '')
        {
            global $wpdb;
            $result = array('status' => 'error', 'msg' => 'Can not save image to media');
            if(!function_exists('wp_generate_attachment_metadata')){
                include_once( ABSPATH . 'wp-admin/includes/image.php' );
            }
            if(!function_exists('download_url')){
                include_once( ABSPATH . 'wp-admin/includes/file.php' );
            }
            if(!function_exists('media_handle_sideload')){
                include_once( ABSPATH . 'wp-admin/includes/media.php' );
            }
            try {
                $array = explode('/', getimagesize($imageurl)['mime']);
                $imagetype = end($array);
                $uniq_name = md5($imageurl);
                $filename = $uniq_name . '.' . $imagetype;
                $checkExist = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->postmeta} WHERE meta_value LIKE %s",'%/'.$wpdb->esc_like($filename)));
                if($checkExist){
                    $result['status'] = 'success';
                    $result['id'] = $checkExist->post_id;
                }
                else{
                    $tmp = download_url($imageurl);
                    if ( is_wp_error( $tmp ) ){
                        $result['msg'] = $tmp->get_error_message();
                        return $result;
                    }
                    $args = array(
                        'name' => $filename,
                        'tmp_name' => $tmp,
                    );
                    $attachment_id = media_handle_sideload( $args, 0, '',array(
                        'post_title'     => $acwc_title,
                        'post_content'   => $acwc_title,
                        'post_excerpt'   => $acwc_title
                    ));
                    if(!is_wp_error($attachment_id)){
                        update_post_meta($attachment_id,'_wp_attachment_image_alt', $acwc_title);
                        $imagenew = get_post( $attachment_id );
                        $fullsizepath = get_attached_file( $imagenew->ID );
                        $attach_data = wp_generate_attachment_metadata( $attachment_id, $fullsizepath );
                        wp_update_attachment_metadata( $attachment_id, $attach_data );
                        $result['status'] = 'success';
                        $result['id'] = $attachment_id;
                    }
                    else{
                        $result['msg'] = $attachment_id->get_error_message();
                        return $result;
                    }
                }
            }
            catch (\Exception $exception){
                $result['msg'] = $exception->getMessage();
            }
            return $result;
        }

        public function acwc_save_draft_post()
        {
            ini_set('max_execution_time', 1000);
            $acwc_result = array(
                'status' => 'error',
                'msg'    => 'Something went wrong',
            );
            if ( ! wp_verify_nonce( $_POST['nonce'], 'acwc-ajax-nonce' ) ) {
                $acwc_result['msg'] = WP_OPENAI_CG_NONCE_ERROR;
                wp_send_json($acwc_result);
            }
            if ( isset( $_POST['title'] ) && !empty($_POST['title']) && isset( $_POST['content'] ) && !empty($_POST['content']) ) {
                $acwc_allowed_html_content_post = wp_kses_allowed_html( 'post' );
                $acwc_title = sanitize_text_field( $_POST['title'] );
                $acwc_content = wp_kses( $_POST['content'], $acwc_allowed_html_content_post );
                $acwc_content = str_replace("__ACWC_IMAGE__", '', $acwc_content);
                if(isset($_POST['post_id']) && !empty($_POST['post_id'])){
                    $acwc_post_id = sanitize_text_field($_POST['post_id']);
                    wp_update_post(array(
                        'ID' => $acwc_post_id,
                        'post_title' => $acwc_title,
                        'post_content' => $acwc_content,
                    ));
                }
                else {
                    $acwc_post_id = wp_insert_post(array(
                        'post_title' => $acwc_title,
                        'post_content' => $acwc_content,
                    ));
                }
                if ( !is_wp_error( $acwc_post_id ) ) {
                    if ( array_key_exists( 'acwc_settings', $_POST ) ) {
                        update_post_meta( $acwc_post_id, '_wporg_meta_key', acwc_util_core()->sanitize_text_or_array_field($_POST['acwc_settings']) );
                    }
                    if ( array_key_exists( '_wporg_language', $_POST ) ) {
                        update_post_meta( $acwc_post_id, '_wporg_language', sanitize_text_field($_POST['_wporg_language']) );
                    }
                    if ( array_key_exists( '_wporg_preview_title', $_POST ) ) {
                        update_post_meta( $acwc_post_id, '_wporg_preview_title', sanitize_text_field($_POST['_wporg_preview_title']) );
                    }
                    if ( array_key_exists( '_wporg_number_of_heading', $_POST ) ) {
                        update_post_meta( $acwc_post_id, '_wporg_number_of_heading', sanitize_text_field($_POST['_wporg_number_of_heading']) );
                    }
                    if ( array_key_exists( '_wporg_heading_tag', $_POST ) ) {
                        update_post_meta( $acwc_post_id, '_wporg_heading_tag', sanitize_text_field($_POST['_wporg_heading_tag']) );
                    }
                    if ( array_key_exists( '_wporg_writing_style', $_POST ) ) {
                        update_post_meta( $acwc_post_id, '_wporg_writing_style', sanitize_text_field($_POST['_wporg_writing_style']) );
                    }
                    if ( array_key_exists( '_wporg_writing_tone', $_POST ) ) {
                        update_post_meta( $acwc_post_id, '_wporg_writing_tone', sanitize_text_field($_POST['_wporg_writing_tone']) );
                    }
                    if ( array_key_exists( '_wporg_modify_headings', $_POST ) ) {
                        update_post_meta( $acwc_post_id, '_wporg_modify_headings', sanitize_text_field($_POST['_wporg_modify_headings']) );
                    }
                    if ( array_key_exists( 'acwc_image_source', $_POST ) ) {
                        update_post_meta( $acwc_post_id, 'acwc_image_source', sanitize_text_field($_POST['acwc_image_source']) );
                    }
                    if ( array_key_exists( 'acwc_featured_image_source', $_POST ) ) {
                        update_post_meta( $acwc_post_id, 'acwc_featured_image_source', sanitize_text_field($_POST['acwc_featured_image_source']) );
                    }
                    if ( array_key_exists( 'acwc_pexels_orientation', $_POST ) ) {
                        update_post_meta( $acwc_post_id, 'acwc_pexels_orientation', sanitize_text_field($_POST['acwc_pexels_orientation']) );
                    }
                    if ( array_key_exists( 'acwc_pexels_size', $_POST ) ) {
                        update_post_meta( $acwc_post_id, 'acwc_pexels_size', sanitize_text_field($_POST['acwc_pexels_size']) );
                    }
                    if ( array_key_exists( '_wporg_add_tagline', $_POST ) ) {
                        update_post_meta( $acwc_post_id, '_wporg_add_tagline', sanitize_text_field($_POST['_wporg_add_tagline']) );
                    }
                    if ( array_key_exists( '_wporg_add_intro', $_POST ) ) {
                        update_post_meta( $acwc_post_id, '_wporg_add_intro', sanitize_text_field($_POST['_wporg_add_intro']) );
                    }
                    if ( array_key_exists( '_wporg_add_conclusion', $_POST ) ) {
                        update_post_meta( $acwc_post_id, '_wporg_add_conclusion', sanitize_text_field($_POST['_wporg_add_conclusion']) );
                    }
                    if ( array_key_exists( '_wporg_anchor_text', $_POST ) ) {
                        update_post_meta( $acwc_post_id, '_wporg_anchor_text', sanitize_text_field($_POST['_wporg_anchor_text']) );
                    }
                    if ( array_key_exists( '_wporg_target_url', $_POST ) ) {
                        update_post_meta( $acwc_post_id, '_wporg_target_url', sanitize_text_field($_POST['_wporg_target_url']) );
                    }
                    if ( array_key_exists( '_wporg_generated_text', $_POST ) ) {
                        update_post_meta( $acwc_post_id, '_wporg_generated_text', sanitize_text_field($_POST['_wporg_generated_text']) );
                    }
                    // _wporg_cta_pos
                    if ( array_key_exists( '_wporg_cta_pos', $_POST ) ) {
                        update_post_meta( $acwc_post_id, '_wporg_cta_pos', sanitize_text_field($_POST['_wporg_cta_pos']) );
                    }
                    // _wporg_target_url_cta
                    if ( array_key_exists( '_wporg_target_url_cta', $_POST ) ) {
                        update_post_meta( $acwc_post_id, '_wporg_target_url_cta', sanitize_text_field($_POST['_wporg_target_url_cta']) );
                    }
                    if ( array_key_exists( '_wporg_img_size', $_POST ) ) {
                        update_post_meta( $acwc_post_id, '_wporg_img_size', sanitize_text_field($_POST['_wporg_img_size']) );
                    }
                    if ( array_key_exists( '_wporg_img_style', $_POST ) ) {
                        update_post_meta( $acwc_post_id, '_wporg_img_style', sanitize_text_field($_POST['_wporg_img_style']) );
                    }
                    if ( array_key_exists( 'acwc_seo_meta_desc', $_POST ) ) {
                        update_post_meta( $acwc_post_id, '_acwc_seo_meta_desc', 1 );
                    }
                    if ( array_key_exists( 'acwc_custom_image_settings', $_POST ) ) {
                        update_post_meta( $acwc_post_id, 'acwc_custom_image_settings', acwc_util_core()->sanitize_text_or_array_field($_POST['acwc_custom_image_settings']) );
                    }
                    if ( array_key_exists( 'acwc_custom_prompt_enable', $_POST ) ) {
                        update_post_meta( $acwc_post_id, 'acwc_custom_prompt_enable', sanitize_text_field($_POST['acwc_custom_prompt_enable']));
                    }
                    if ( array_key_exists( 'acwc_custom_prompt', $_POST ) && array_key_exists( 'acwc_custom_prompt_enable', $_POST ) && $_POST['acwc_custom_prompt_enable'] ) {
                        update_post_meta( $acwc_post_id, 'acwc_custom_prompt', wp_kses_post($_POST['acwc_custom_prompt']));
                    }
                    if ( array_key_exists( 'acwc_post_tags', $_POST ) ) {
                        update_post_meta( $acwc_post_id, '_acwc_post_tags', sanitize_text_field($_POST['acwc_post_tags']) );
                        if(!empty($_POST['acwc_post_tags'])){
                            $acwc_tags = array_map('trim', explode(',', sanitize_text_field($_POST['acwc_post_tags'])));
                            if($acwc_tags && is_array($acwc_tags) && count($acwc_tags)){
                                wp_set_post_tags($acwc_post_id,$acwc_tags);
                            }
                        }
                    }
                    if ( array_key_exists( '_acwc_meta_description', $_POST ) ) {
                        $this->acwc_save_description($acwc_post_id,sanitize_text_field($_POST['_acwc_meta_description']));
                    }
                    $this->acwc_post_image($acwc_post_id,$acwc_title);
                    $acwc_post = get_post($acwc_post_id);
                    $acwc_content = str_replace("__ACWC_IMAGE__", '', $acwc_post->post_content);
                    wp_update_post(array(
                        'ID' => $acwc_post_id,
                        'post_content' => $acwc_content
                    ));
                    $acwc_result['status'] = 'success';
                    $acwc_result['id'] = $acwc_post_id;
                    if(isset($_REQUEST['save_source']) && $_REQUEST['save_source'] == 'promptbase'){

                    }
                    else {
                        /*Save Single Content Log*/
                        $acwc_duration = isset($_REQUEST['duration']) && !empty($_REQUEST['duration']) ? sanitize_text_field($_REQUEST['duration']) : 0;
                        $acwc_usage_token = isset($_REQUEST['usage_token']) && !empty($_REQUEST['usage_token']) ? sanitize_text_field($_REQUEST['usage_token']) : 0;
                        $acwc_word_count = isset($_REQUEST['word_count']) && !empty($_REQUEST['word_count']) ? sanitize_text_field($_REQUEST['word_count']) : 0;
                        $acwc_log_id = wp_insert_post(array(
                            'post_title' => 'ACWCLOG:' . $acwc_title,
                            'post_type' => 'acwc_slog',
                            'post_status' => 'publish'
                        ));
                        $acwc_ai_model = get_option('acwc_ai_model', 'text-davinci-003');
                        if (isset($_REQUEST['model']) && !empty($_REQUEST['model'])) {
                            $acwc_ai_model = sanitize_text_field($_REQUEST['model']);
                        }
                        $source_log = 'writer';
                        if (isset($_REQUEST['source_log']) && !empty($_REQUEST['source_log'])) {
                            $source_log = sanitize_text_field($_REQUEST['source_log']);
                        }
                        add_post_meta($acwc_log_id, 'acwc_source_log', $source_log);
                        add_post_meta($acwc_log_id, 'acwc_ai_model', $acwc_ai_model);
                        add_post_meta($acwc_log_id, 'acwc_duration', $acwc_duration);
                        add_post_meta($acwc_log_id, 'acwc_usage_token', $acwc_usage_token);
                        add_post_meta($acwc_log_id, 'acwc_word_count', $acwc_word_count);
                        add_post_meta($acwc_log_id, 'acwc_post_id', $acwc_post_id);
                    }
                }

            }

            wp_send_json( $acwc_result );
        }
    }
    ACWC_Content::get_instance();
}
