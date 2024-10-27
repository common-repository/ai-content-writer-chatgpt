<?php

namespace ACWC;
if ( ! defined( 'ABSPATH' ) ) exit;
if(!class_exists('\\ACWC\\ACWC_Frontend')) {
    class ACWC_Frontend
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

        }
    }
    ACWC_Frontend::get_instance();
}
