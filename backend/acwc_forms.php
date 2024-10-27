<?php
if ( ! defined( 'ABSPATH' ) ) exit;
$acwc_action = isset($_GET['action']) && !empty($_GET['action']) ? sanitize_text_field($_GET['action']) : '';
$checkRole = \ACWC\acwc_roles()->user_can('acwc_forms',empty($acwc_action) ? 'forms' : $acwc_action);
if($checkRole){
    echo '<script>window.location.href="'.$checkRole.'"</script>';
    exit;
}
?>
<div class="">
    <div class="sub-menu-container">
        <?php
        if(empty($acwc_action)){
            $acwc_action = 'forms';
        }
        \ACWC\acwc_util_core()->acwc_tabs('acwc_forms', array(
            'forms'=>'Forms List',
            'logs' => 'Recently Response List'
        ), $acwc_action);
        if(!$acwc_action || $acwc_action == 'forms'){
            $acwc_action = '';
        }
        ?>
    </div>
    <div id="">
        <div id="">
            <?php
            if(empty($acwc_action)){
                include __DIR__.'/acwc_form_index.php';
            }
            if($acwc_action == 'logs'){
                include __DIR__.'/acwc_form_log.php';
            }
            if($acwc_action == 'settings'){
                include __DIR__.'/acwc_form_settings.php';
            }
            ?>
        </div>
    </div>
</div>
