<?php
function getPopUp()
{ ?>
    <div id="acwc_contant_set_get_custombox" class="acwc_contant_set_get_custombox" style="display: none;">
		<div class="acwc_prompt_container clearfix">
		<span class="acwc_contant_popup_close">x</span>
			<label for="prompt-input" class="acwc_enter_prompt_label">Enter Title</label>
			<input id="acwc-prompt-input" type="text" name="acwc-prompt" class="acwc_enter_prompt clearfix" placeholder="" required>
            <input type="hidden" name="acwcActionTyp" id="acwcActionTyp" value="1">
            <?php 
                $langSelected = (!empty(get_option('acwc_set_org_language'))) ? get_option('acwc_set_org_language') : 'en';
            ?>
			<a href="admin.php?page=acwc" id="aiwa-advanced-settings-btn" class="" data-settings="advanced-settings" target="_blank">API Settings</a>
			<div id="acwc-response-data" class="acwc_response_data_container">
				
			</div>
			<div class="acwc_contant_footer_buttons clearfix">				
				<button id="acwc_content_generate" class="acwc_content_generate button" role="button"><span class="title">Generate</span><span class="acwc_post_contant_spinner acwc_post_contant_spinner_hide"></span></button>				
			</div>
		</div>
	</div>
<?php 
}
add_action('admin_footer',  'getPopUp');
