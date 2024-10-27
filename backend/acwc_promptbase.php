<?php
if ( ! defined( 'ABSPATH' ) ) exit;
$acwc_action = isset($_GET['action']) && !empty($_GET['action']) ? sanitize_text_field($_GET['action']) : '';
$checkRole = \ACWC\acwc_roles()->user_can('acwc_promptbase',empty($acwc_action) ? 'promptbase' : $acwc_action);
if($checkRole){
    echo '<script>window.location.href="'.$checkRole.'"</script>';
    exit;
}
?>
<div style="padding:10px;">
    <div class="sub-menu-container">
        <?php
        if(empty($acwc_action)){
            $acwc_action = 'promptbase';
        };
        \ACWC\acwc_util_core()->acwc_tabs('acwc_promptbase',array(
            'promptbase' => 'Prompt List',
            'logs' => 'Recently Prompt Respose'
        ),$acwc_action);
        if(!$acwc_action || $acwc_action == 'promptbase'){
            $acwc_action = '';
        }
        ?>
    </div>
    <div id="">
        <div id="">
            <?php
            if(empty($acwc_action)){
                include __DIR__.'/acwc_promptbase_index.php';
            }
            if($acwc_action == 'logs'){
                include __DIR__.'/acwc_promptbase_log.php';
            }	
            if($acwc_action == 'settings'){
                include __DIR__.'/acwc_promptbase_settings.php';
            }
            ?>
        </div>
    </div>
</div>
