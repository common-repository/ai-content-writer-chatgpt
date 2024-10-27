<?php
if ( ! defined( 'ABSPATH' ) ) exit;
$roles = wp_roles();
$success = false;
if(isset($_POST['acwc_role_save'])){
    check_admin_referer('acwc_role_manager');
    foreach($roles->get_names() as $role => $name){
        if($role !== 'administrator') {
            delete_option('acwc_role_' . $role . '_modules');
            $user_role = get_role($role);
            foreach ($this->acwc_roles as $key => $acwc_role) {
                if(!empty($acwc_role['hide'])){
                    $user_role->remove_cap('acwc_' . $acwc_role['hide']);
                }
                if (isset($acwc_role['roles']) && count($acwc_role['roles'])) {
                    foreach ($acwc_role['roles'] as $key_role => $role_name) {
                        $user_role->remove_cap('acwc_' . $key . '_' . $key_role);
                    }
                } else {
                    $user_role->remove_cap('acwc_' . $key);
                }
            }
        }
    }
    if(isset($_POST['acwcroles'])){
        $acwc_roles = \ACWC\acwc_util_core()->sanitize_text_or_array_field($_POST['acwcroles']);
        foreach($acwc_roles as $role=>$permissions){
            if(is_array($permissions) && count($permissions)){
                $user_role = get_role($role);
                update_option('acwc_role_'.$role.'_modules',$permissions);
                foreach($permissions as $permission){
                    $user_role->add_cap($permission);
                }
            }
        }
    }
    $success = true;
}
?>
<style>
    .acwc-list-roles{
        margin-bottom: 15px;
    }
    .acwc-role-item label{
        display: block;
        padding: 6px 12px;
        background: #fff;
        border-radius: 3px;
        border: 1px solid #dfdfdf;
        margin-bottom: 10px;
    }
    .acwc-grid-three{
        grid-row-gap: 0;
    }
    .acwc-role-title > div > span{
        font-weight: bold;
        font-size: 15px;
        margin-right: 10px;
    }
    .acwc-role-title > div{
        display: flex;
        align-items: center;
    }
    .acwc-role-title{
        display: flex;
        justify-content: space-between;
        align-items: center;
        background: #dfdfdf;
        border-top-left-radius: 3px;
        border-top-right-radius: 3px;
        padding: 5px 12px;
    }
    .acwc_role{
        margin-bottom: 10px;
    }
    .acwc_content_toggle{
        color: #007cba;
        cursor: pointer;
    }
    .acwc_role_content.acwc_expand{
        display: block;
    }
    .acwc_role_content{
        display: none;
        padding: 10px;
        border: 1px solid #ccc;
        background: #f7f7f7;
        border-bottom-left-radius: 3px;
        border-bottom-right-radius: 3px;
    }
</style>
<h3>Role Manager</h3>
<p>Control which user has access to which options of AI Content Writer - ChatGPT</p>
<?php
if($success){
    ?>
    <strong style="color: #00aa00">Record updated successfully</strong>
    <?php
}
?>
<form action="" method="post" style="max-width: 1000px">
<?php
wp_nonce_field('acwc_role_manager');
$keyx = 0;
foreach($roles->get_names() as $role => $name){
    if($role !== 'administrator'){
        $role_modules = get_option('acwc_role_'.$role.'_modules',[]);
    ?>
        <div class="acwc_role">
            <div class="acwc-role-title">
                <div>
                    <span><?php echo esc_html($name)?></span>
                    <button style="opacity:0" class="button button-small acwc_toggle_role" data-target="<?php echo esc_html($role)?>" type="button">Toggle All</button>
                </div>
                <span class="acwc_content_toggle<?php echo $keyx == 1 ? ' acwc_expand':''?>">
                    <?php
                    if($keyx == 1){
                        echo 'Collapse';
                    }
                    else{
                        echo 'Expand';
                    }
                    ?>
                </span>
            </div>
            <div class="acwc_role_content<?php echo $keyx == 1? ' acwc_expand':''?>">
                <?php
                foreach($this->acwc_roles as $key=>$acwc_role){
                    ?>
                    <p style="margin-bottom: 5px">
                        <strong><?php echo esc_html($acwc_role['name'])?></strong>
                    </p>
                    <div class="acwc-grid-three acwc-list-roles">
                    <?php
                    if(isset($acwc_role['roles']) && count($acwc_role['roles'])){
                        $has_checked = false;
                        foreach($acwc_role['roles'] as $key_role => $role_name){
                            if(!$has_checked && in_array('acwc_'.$key.'_'.$key_role, $role_modules)){
                                $has_checked = true;
                            }
                            if(!\ACWC\acwc_util_core()->acwc_is_pro() && ($key_role == 'google-sheets' || $key_role == 'rss')){
                                ?>
                                <div class="acwc-grid-1">
                                    <div class="acwc-role-item">
                                        <label><input type="checkbox" disabled>&nbsp;<?php echo esc_html($role_name['name'])?><span style="font-size: 12px;display: inline-block;padding: 0 4px;background: #ffb30a;border-radius: 2px;margin-left: 5px;font-weight: bold;">Pro</span></label>
                                    </div>
                                </div>
                                <?php
                            }
                            else{
                            ?>
                            <div class="acwc-grid-1">
                                <div class="acwc-role-item">
                                    <label><input<?php echo in_array('acwc_'.$key.'_'.$key_role, $role_modules) ? ' checked':''?> name="acwcroles[<?php echo esc_html($role)?>][]" value="acwc_<?php echo esc_html($key)?>_<?php echo esc_html($key_role)?>" class="acwc_role_<?php echo esc_html($role)?> acwc_role_multi" type="checkbox">&nbsp;<?php echo esc_html($role_name['name'])?></label>
                                </div>
                            </div>
                            <?php
                            }
                        }
                        if(isset($acwc_role['hide']) && !empty($acwc_role['hide'])){
                            ?>
                            <input<?php echo $has_checked ? '':' disabled'?> type="hidden" name="acwcroles[<?php echo esc_html($role)?>][]" class="acwc_role_hide" value="acwc_<?php echo esc_html($acwc_role['hide'])?>">
                            <?php
                        }
                    }
                    else{
                        ?>
                        <div class="acwc-grid-1">
                            <div class="acwc-role-item">
                                <label><input<?php echo in_array('acwc_'.$key, $role_modules) ? ' checked':''?> name="acwcroles[<?php echo esc_html($role)?>][]" value="acwc_<?php echo esc_html($key)?>" class="acwc_role_<?php echo esc_html($role)?>" type="checkbox">&nbsp;<?php echo esc_html($acwc_role['name'])?></label>
                            </div>
                        </div>
                        <?php
                    }
                    ?>
                    </div>
                    <?php
                }
                ?>
            </div>
        </div>
    <?php
    }
}
?>
    <button name="acwc_role_save" class="button ">Save</button>
</form>
<script>
    jQuery(document).ready(function ($){
        $('.acwc_toggle_role').click(function (){
            let target = $(this).attr('data-target');
            if($(this).hasClass('acwc_toggled')){
                $('.acwc_role_'+target).prop('checked', false);
            }
            else{
                $('.acwc_role_'+target).prop('checked', true);
            }
            $(this).toggleClass('acwc_toggled');
        });
        $('.acwc_content_toggle').click(function (){
            if($(this).hasClass('acwc_expand')){
                $(this).html('Expand');
                $(this).closest('.acwc_role').find('.acwc_role_content').removeClass('acwc_expand');
                $(this).closest('.acwc_role').find('.acwc_toggle_role').css('opacity',0);
            }
            else{
                $(this).html('Collapse');
                $(this).closest('.acwc_role').find('.acwc_toggle_role').css('opacity',1);
                $(this).closest('.acwc_role').find('.acwc_role_content').addClass('acwc_expand');
            }
            $(this).toggleClass('acwc_expand');
        });
        $('.acwc_role_multi').click(function (){
            let list_roles = $(this).closest('.acwc-list-roles');
            let activeOneRole = false;
            list_roles.find('input[type=checkbox]').each(function (idx, item){
                if($(item).prop('checked')){
                    activeOneRole =true;
                }
            });
            if(activeOneRole){
                list_roles.find('.acwc_role_hide').removeAttr('disabled');
            }
            else{
                list_roles.find('.acwc_role_hide').attr('disabled','disabled');
            }
        })
    })
</script>
