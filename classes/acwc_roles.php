<?php

namespace ACWC;
if ( ! defined( 'ABSPATH' ) ) exit;
if(!class_exists('\\ACWC\\ACWC_Roles')) {
    class ACWC_Roles
    {
        private static $instance = null;

        public $acwc_roles = array();


        public static function get_instance()
        {
            if (is_null(self::$instance)) {
                self::$instance = new self();
            }
            return self::$instance;
        }

        public function __construct()
        {
            add_action('init', [$this,'register_roles_admin'],1);
        }

        public function acwc_roles()
        {
            include ACWC_PLUGIN_DIR.'backend/views/roles/index.php';
        }

        public function register_roles_admin()
        {
            $add_role_to_administrator = get_option('acwc_role_to_administrator',false);
            if(!$add_role_to_administrator) {
                $user_role = get_role('administrator');
                foreach ($this->acwc_roles as $key => $acwc_role) {
                    if(isset($acwc_role['hide']) && !empty($acwc_role['hide'])){
                        $user_role->add_cap('acwc_'.$acwc_role['hide']);
                    }
                    if (isset($acwc_role['roles']) && count($acwc_role['roles'])) {
                        foreach ($acwc_role['roles'] as $key_role => $role_name) {
                            $user_role->add_cap('acwc_' . $key . '_' . $key_role);
                        }
                    } else {
                        $user_role->add_cap('acwc_' . $key);
                    }
                }
                update_option('acwc_role_to_administrator','yes');
            }
        }

        public function user_can($module, $tool = false, $action = 'action')
        {
            if(in_array('administrator',(array)wp_get_current_user()->roles)){
                return false;
            }
            $capability = $module;
            if($tool){
                $capability .= '_'.$tool;
            }
            if(current_user_can($capability)){
                return false;
            }
            else{
                $role_granted = '';
                $keyName = str_replace('acwc_','',$module);
                foreach($this->acwc_roles[$keyName]['roles'] as $key=>$role){
                    if(current_user_can($module.'_'.$key)){
                        $role_granted = $key;
                        break;
                    }
                }
                return admin_url('admin.php?page='.$module.'&'.$action.'='.$role_granted);
            }
        }
    }

    ACWC_Roles::get_instance();
}
if(!function_exists(__NAMESPACE__.'\acwc_roles')){
    function acwc_roles(){
        return ACWC_Roles::get_instance();
    }
}
