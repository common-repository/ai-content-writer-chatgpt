<?php
if ( ! defined( 'ABSPATH' ) ) exit;

global $wpdb;
if (isset($_GET['acwc_nonce']) && !wp_verify_nonce($_GET['acwc_nonce'], 'acwc_formlog_search_nonce')) {
    die(WP_OPENAI_CG_NONCE_ERROR);
}
$acwc_log_page = isset($_GET['wpage']) && !empty($_GET['wpage']) ? sanitize_text_field($_GET['wpage']) : 1;
$search = isset($_GET['search']) && !empty($_GET['search']) ? sanitize_text_field($_GET['search']) : '';
$where = '';
if(!empty($search)) {
    $where .= $wpdb->prepare(" AND `data` LIKE %s", '%' . $wpdb->esc_like($search) . '%');
    $where .= $wpdb->prepare(" OR `prompt` LIKE %s", '%' . $wpdb->esc_like($search) . '%');
}
$query = "SELECT * FROM ".$wpdb->prefix."acwc_form_logs WHERE 1=1".$where;
$total_query = "SELECT COUNT(1) FROM (${query}) AS combined_table";
$total = $wpdb->get_var( $total_query );
$items_per_page = 20;
$offset = ( $acwc_log_page * $items_per_page ) - $items_per_page;
$acwc_logs = $wpdb->get_results( $query . " ORDER BY created_at DESC LIMIT ${offset}, ${items_per_page}" );
$totalPage         = ceil($total / $items_per_page);
?>
<form action="" method="get" style="margin-top:50px;">
    <?php wp_nonce_field('acwc_formlog_search_nonce', 'acwc_nonce'); ?>
    <input type="hidden" name="page" value="acwc_forms">
    <input type="hidden" name="action" value="logs">
    <div class="d_flex mb_5">
        <input style="width: 100%" value="<?php echo esc_html($search)?>" class="" name="search" type="text" placeholder="Type for search">
        <button class="button ">Search</button>
    </div>
</form>
<table class="wp-list-table widefat fixed striped table-view-list posts" style="margin-top:10px;">
    <thead>
    <tr>
        <th style="width:30px;">ID</th>
		<th>Form Title</th>
        <th>Prompt</th>
        <th>Used Model</th>
        <th>Duration</th>
        <th>Token</th>
        <th>Estimated</th>
        <th>Created At</th>
    </tr>
    </thead>
    <tbody class="acwc-builder-list">
    <?php
    if($acwc_logs && is_array($acwc_logs) && count($acwc_logs)){
        foreach ($acwc_logs as $acwc_log) {
            $source = '';
            $acwc_ai_model = $acwc_log->model;
            $acwc_usage_token = $acwc_log->tokens;
            if($acwc_log->source > 0){
                $source = get_the_title($acwc_log->source);
            }
            if($acwc_ai_model === 'gpt-3.5-turbo') {
                $acwc_estimated = 0.002 * $acwc_usage_token / 1000;
            }
            if($acwc_ai_model === 'gpt-4') {
                $acwc_estimated = 0.06 * $acwc_usage_token / 1000;
            }
            if($acwc_ai_model === 'gpt-4-32k') {
                $acwc_estimated = 0.12 * $acwc_usage_token / 1000;
            }
            else{
                $acwc_estimated = 0.02 * $acwc_usage_token / 1000;
            }
            ?>
            <tr>
                <td><?php echo esc_html($acwc_log->prompt_id)?></td>
				<td><?php echo esc_html($acwc_log->name)?></td>
                <td><a class="acwc_log_view" href="javascript:void(0)" data-content="<?php echo esc_html($acwc_log->data)?>" data-prompt="<?php echo esc_html($acwc_log->prompt)?>"><?php echo esc_html(substr($acwc_log->prompt,0,100))?>..</a></td>
                <td><?php echo esc_html($acwc_ai_model)?></td>
                <td><?php echo esc_html(ACWC\ACWC_Content::get_instance()->acwc_seconds_to_time((int)$acwc_log->duration))?></td>
                <td><?php echo esc_html($acwc_usage_token)?></td>
                <td>$<?php echo esc_html($acwc_estimated)?></td>
                <td><?php echo esc_html(date('d.m.Y H:i',$acwc_log->created_at))?></td>
            </tr>
            <?php
        }
    }
    ?>
    </tbody>
</table>
<div class="acwc_paginate">
    <?php
    if($totalPage > 1){
        echo paginate_links( array(
            'base'         => admin_url('admin.php?page=acwc_forms&action=logs&wpage=%#%'),
            'total'        => $totalPage,
            'current'      => $acwc_log_page,
            'format'       => '?wpage=%#%',
            'show_all'     => false,
            'prev_next'    => false,
            'add_args'     => false,
        ));
    }
    ?>
</div>
<script>
    jQuery(document).ready(function ($){
        $('.acwc_model_close').click(function (){
            $('.acwc_model_close').closest('.acwc_model').hide();
            $('.acwc_out_overlay').hide();
        });
        $('.acwc_log_view').click(function (){
            let html = '';
            let content = $(this).attr('data-content');
            content = content.trim();
            content = content.replace(/\n/g, "<br />");
            content = content.replace(/\\/g,'');
            $('.acwc_model_title').html('Response');
            html += '<p><strong>Prompt:</strong> '+$(this).attr('data-prompt')+'</p>';
            html += '<strong>Response</strong>';
            html += '<div>';
            html += content.trim();
            html += '</div>';
            $('.acwc_model_content').html(html);
            $('.acwc_out_overlay').show();
            $('.acwc_model').show();

        })
    })
</script>
