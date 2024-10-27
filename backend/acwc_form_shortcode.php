<?php
if ( ! defined( 'ABSPATH' ) ) exit;
$acwc_items = array();
$acwc_models = array();
if(file_exists(ACWC_PLUGIN_DIR.'backend/json/gptcategories.json')){
    $acwc_file_content = file_get_contents(ACWC_PLUGIN_DIR.'backend/json/gptcategories.json');
    $acwc_file_content = json_decode($acwc_file_content, true);
    if($acwc_file_content && is_array($acwc_file_content) && count($acwc_file_content)){
        foreach($acwc_file_content as $key=>$item){
            $acwc_categories[$key] = trim($item);
        }
    }
}
if(file_exists(ACWC_PLUGIN_DIR.'backend/json/gptforms.json')){
    $acwc_file_content = file_get_contents(ACWC_PLUGIN_DIR.'backend/json/gptforms.json');
    $acwc_file_content = json_decode($acwc_file_content, true);
    if($acwc_file_content && is_array($acwc_file_content) && count($acwc_file_content)){
        foreach($acwc_file_content as $item){
            $acwc_items[] = $item;
        }
    }
}
if(file_exists(ACWC_PLUGIN_DIR.'backend/json/models.json')){
    $acwc_file_content = file_get_contents(ACWC_PLUGIN_DIR.'backend/json/models.json');
    $acwc_file_content = json_decode($acwc_file_content, true);
    if($acwc_file_content && is_array($acwc_file_content) && isset($acwc_file_content['models']) && is_array($acwc_file_content['models']) && count($acwc_file_content['models'])){
        foreach($acwc_file_content['models'] as $item){
            $acwc_models[] = $item['name'];
        }
    }
}
global $wpdb;
if(isset($atts) && is_array($atts) && isset($atts['id']) && !empty($atts['id'])){
    $acwc_item_id = sanitize_text_field($atts['id']);
    $acwc_item = false;
    $acwc_custom = isset($atts['custom']) && $atts['custom'] == 'yes' ? true : false;
    if(count($acwc_items) && !$acwc_custom){
        foreach ($acwc_items as $acwc_prompt){
            if(isset($acwc_prompt['id']) && $acwc_prompt['id'] == $acwc_item_id){
                $acwc_item = $acwc_prompt;
                $acwc_item['type'] = 'json';
            }
        }
    }
    if($acwc_custom){
        $sql = "SELECT p.ID as id,p.post_title as title, p.post_content as description";
        $acwc_meta_keys = array('prompt','editor','fields','response','category','engine','max_tokens','temperature','top_p','best_of','frequency_penalty','presence_penalty','stop','color','bgcolor','header','ddraft','dclear','dnotice','generate_text','noanswer_text','draft_text','clear_text','stop_text','cnotice_text');
        foreach($acwc_meta_keys as $acwc_meta_key){
            $sql .= ", (".$wpdb->prepare("SELECT %i.%i FROM %i %i WHERE %i.%i=%s AND p.ID=%i.%i LIMIT 1",$acwc_meta_key,'meta_value',
                    $wpdb->postmeta,
                    $acwc_meta_key,
                    $acwc_meta_key,
                    'meta_key',
                    'acwc_form_'.$acwc_meta_key,
                    $acwc_meta_key,
                    'post_id'
                ).") as ".$acwc_meta_key;
        }
        $sql .= $wpdb->prepare(" FROM %i p WHERE p.post_type = 'acwc_form' AND p.post_status='publish' AND p.ID=%d ORDER BY p.post_date DESC",$wpdb->posts,$acwc_item_id);
        $acwc_item = $wpdb->get_row($sql, ARRAY_A);
        if($acwc_item){
            $acwc_item['type'] = 'custom';
        }
    }
    if($acwc_item){
        $acwc_item_categories = array();
        $acwc_item_categories_name = array();
        if(isset($acwc_item['category']) && !empty($acwc_item['category'])){
            $acwc_item_categories = array_map('trim', explode(',', $acwc_item['category']));
        }        
       
        $acwc_engine = isset($acwc_item['engine']) && !empty($acwc_item['engine']) ? $acwc_item['engine'] : $this->acwc_engine;
        $acwc_max_tokens = isset($acwc_item['max_tokens']) && !empty($acwc_item['max_tokens']) ? $acwc_item['max_tokens'] : $this->acwc_max_tokens;
        $acwc_temperature = isset($acwc_item['temperature']) && !empty($acwc_item['temperature']) ? $acwc_item['temperature'] : $this->acwc_temperature;
        $acwc_top_p = isset($acwc_item['top_p']) && !empty($acwc_item['top_p']) ? $acwc_item['top_p'] : $this->acwc_top_p;
        $acwc_best_of = isset($acwc_item['best_of']) && !empty($acwc_item['best_of']) ? $acwc_item['best_of'] : $this->acwc_best_of;
        $acwc_frequency_penalty = isset($acwc_item['frequency_penalty']) && !empty($acwc_item['frequency_penalty']) ? $acwc_item['frequency_penalty'] : $this->acwc_frequency_penalty;
        $acwc_presence_penalty = isset($acwc_item['presence_penalty']) && !empty($acwc_item['presence_penalty']) ? $acwc_item['presence_penalty'] : $this->acwc_presence_penalty;
        $acwc_stop = isset($acwc_item['stop']) && !empty($acwc_item['stop']) ? $acwc_item['stop'] : $this->acwc_stop;
        $acwc_generate_text = isset($acwc_item['generate_text']) && !empty($acwc_item['generate_text']) ? $acwc_item['generate_text'] : 'Generate';
        $acwc_draft_text = isset($acwc_item['draft_text']) && !empty($acwc_item['draft_text']) ? $acwc_item['draft_text'] : 'Save Draft';
        $acwc_noanswer_text = isset($acwc_item['noanswer_text']) && !empty($acwc_item['noanswer_text']) ? $acwc_item['noanswer_text'] : 'Number of Answers';
        $acwc_clear_text = isset($acwc_item['clear_text']) && !empty($acwc_item['clear_text']) ? $acwc_item['clear_text'] : 'Clear';
        $acwc_stop_text = isset($acwc_item['stop_text']) && !empty($acwc_item['stop_text']) ? $acwc_item['stop_text'] : 'Stop';
        $acwc_cnotice_text = isset($acwc_item['cnotice_text']) && !empty($acwc_item['cnotice_text']) ? $acwc_item['cnotice_text'] : 'Please register to save your result';
        $acwc_stop_lists = '';
        if(is_array($acwc_stop) && count($acwc_stop)){
            foreach($acwc_stop as $item_stop){
                if($item_stop === "\n"){
                    $item_stop = '\n';
                }
                $acwc_stop_lists = empty($acwc_stop_lists) ? $item_stop : ','.$item_stop;
            }
        }
        if(count($acwc_item_categories)){
            foreach($acwc_item_categories as $acwc_item_category){
                if(isset($acwc_categories[$acwc_item_category]) && !empty($acwc_categories[$acwc_item_category])){
                    $acwc_item_categories_name[] = $acwc_categories[$acwc_item_category];
                }
            }
        }
        if(is_user_logged_in()){
            wp_enqueue_editor();
        }
        $acwc_show_setting = false;
        if(isset($atts['settings']) && $atts['settings'] == 'yes'){
            $acwc_show_setting = true;
        }
        ?>
        <style>
            .acwc_prompt_item{

            }
            .acwc_prompt_head{
                display: flex;
                align-items: center;
                padding-bottom: 10px;
                border-bottom: 1px solid #b1b1b1;
            }
            .acwc_prompt_icon{
				width: 80px;
				height: 80px;
				border-radius: 5px;
				display: flex;
				justify-content: center;
				align-items: center;
				color: #000;
				font-size: 30px;
				text-transform: uppercase;
				border: 1px solid #ccc;
				margin-right: 20px;
            }
            
            .acwc_prompt_head p{
                margin: 5px 0;
            }
            .acwc_prompt_head strong{
                font-size: 20px;
                display: block;
            }
            .acwc_prompt_content{
                padding: 10px 0;
            }
            .acwc_block_three{
                display: grid;
                grid-template-columns: repeat(3,1fr);
                grid-column-gap: 20px;
                grid-row-gap: 20px;
                grid-template-rows: auto auto;
            }
            .acwc_block_2{
                grid-column: span 2/span 1;
            }
            .acwc_block_1{
                grid-column: span 1/span 1;
            }
            .acwc_prompt_item .acwc_prompt_sample{
                display: block;
                position: relative;
                font-size: 13px;
            }
            .acwc_prompt_item .acwc_prompt_sample:hover .acwc_prompt_response{
                display: block;
            }
            .acwc_prompt_title{
                display: block;
                width: 100%;
                margin-bottom: 20px;
            }
            .acwc_prompt_result{
                width: 100%;
            }
            .acwc_prompt_max_lines{
                display: inline-block;
                width: auto;
                border: 1px solid #8f8f8f;
                margin-left: 10px;
                padding: 5px 10px;
                border-radius: 3px;
                font-size: 15px;
            }
            .acwc_prompt_button{
                margin-left: 10px;
            }
            .acwc_button{
                padding: 5px 10px;
                background: #424242;
                border: 1px solid #343434;
                border-radius: 4px;
                color: #fff;
                font-size: 15px;
                position: relative;
                display: inline-flex;
                align-items: center;
            }
            .acwc_button:disabled{
                background: #505050;
                border-color: #999;
            }
            .acwc_button:hover:not(:disabled),.acwc_button:focus:not(:disabled){
                color: #fff;
                background-color: #171717;
                text-decoration: none;
            }
            .acwc_prompt_item .acwc_prompt_response{
                background: #333;
                border: 1px solid #444;
                position: absolute;
                border-radius: 3px;
                color: #fff;
                padding: 5px;
                width: 320px;
                bottom: calc(100% + 5px);
                left: -100px;
                z-index: 99;
                display: none;
                font-size: 13px;
            }
            .acwc_prompt_item h3{
                font-size: 25px;
                margin: 0px;
            }
            .acwc_prompt_item .acwc_prompt_response:after,.acwc_prompt_item .acwc_prompt_response:before{
                top: 100%;
                left: 50%;
                border: solid transparent;
                content: "";
                height: 0;
                width: 0;
                position: absolute;
                pointer-events: none;
            }
            .acwc_prompt_item .acwc_prompt_response:before{
                border-color: rgba(68, 68, 68, 0);
                border-top-color: #444;
                border-width: 7px;
                margin-left: -7px;
            }
            .acwc_prompt_item .acwc_prompt_response:after{
                border-color: rgba(51, 51, 51, 0);
                border-top-color: #333;
                border-width: 6px;
                margin-left: -6px;
            }
            .acwc_prompt_item .acwc_prompt_field > strong{
                display: inline-flex;
                width: 50%;
                font-size: 13px;
                align-items: center;
                flex-wrap: wrap;
            }
            .acwc_prompt_item .acwc_prompt_field > strong > small{
                font-size: 12px;
                font-weight: normal;
                display: block;
            }
            .acwc_prompt_item .acwc_prompt_field > input,.acwc_prompt_item .acwc_prompt_field > select{
                border: 1px solid #8f8f8f;
                padding: 5px 10px;
                border-radius: 3px;
                font-size: 15px;
                display: inline-block;
                width: 50%;
            }
            .acwc_prompt_flex-center{
                display: flex;
                align-items: center;
            }
            .acwc_prompt_field{
                margin-bottom: 10px;
                display: flex;
            }
            .mb_10{
                margin-bottom: 10px;
            }
            .acwc_loader{
                width: 20px;
                height: 20px;
                border: 2px solid #FFF;
                border-bottom-color: transparent;
                border-radius: 50%;
                display: inline-block;
                box-sizing: border-box;
                animation: acwc_rotation 1s linear infinite;
            }
            .acwc_button .acwc_loader{
                float: right;
                margin-left: 5px;
                margin-top: 2px;
            }
            .acwc_form_field{
                margin-bottom: 10px;
            }
			.acwc_form_field label{
				display:block;
				font-size: 18px;
			}
			.acwc_form_field input,.acwc_form_field textarea,.acwc_form_field select{
				display: block;
				padding: 10px;
				font-size: 16px;
				width: 100%;
				max-width: 300px;
			}
			.generate_btn_container .acwc_prompt_button{
				font-size: 20px;
				margin: 20px 0;
				padding: 10px 20px;
				cursor:pointer;
			}
            @keyframes acwc_rotation {
                0% {
                    transform: rotate(0deg);
                }
                100% {
                    transform: rotate(360deg);
                }
            }
        </style>
        <?php
        $acwc_fields = [];
        if($acwc_item['fields'] !== '') {
            $acwc_fields = $acwc_item['type'] == 'custom' ? json_decode($acwc_item['fields'],true) : $acwc_item['fields'];
        }
        $acwc_response_type = isset($acwc_item['editor']) && $acwc_item['editor'] == 'div' ? 'div' : 'textarea';
        $kses_defaults = wp_kses_allowed_html( 'post' );
        $svg_args = array(
            'svg'   => array(
                'class'           => true,
                'aria-hidden'     => true,
                'aria-labelledby' => true,
                'role'            => true,
                'xmlns'           => true,
                'width'           => true,
                'height'          => true,
                'viewbox'         => true 
            ),
            'g'     => array( 'fill' => true ),
            'title' => array( 'title' => true ),
            'path'  => array(
                'd'               => true,
                'fill'            => true
            )
        );
        $allowed_tags = array_merge( $kses_defaults, $svg_args );
        ?>
        <div class="acwc_prompt_item" style="<?php echo isset($acwc_item['bgcolor']) && !empty($acwc_item['bgcolor']) ? 'background-color:'.esc_html($acwc_item['bgcolor']):'';?>">
            <div class="acwc_prompt_head" style="<?php echo isset($acwc_item['header']) && $acwc_item['header'] == 'no' ? 'display: none;':'';?>">
                <div class="acwc_prompt_icon"><?php preg_match_all('/\b\w/', $acwc_item['title'], $matches);$firstLetters = implode('', $matches[0]); echo  substr($firstLetters,0,2);?></div>
                <div class="">
                    <h3><?php echo isset($acwc_item['title']) && !empty($acwc_item['title']) ? esc_html($acwc_item['title']) : ''?></h3>
                    <?php
                    if(isset($acwc_item['description']) && !empty($acwc_item['description'])){
                        echo '<p>'.esc_html($acwc_item['description']).'</p>';
                    }
                    ?>
                </div>
            </div>
            <div class="acwc_prompt_content">
                <form method="post" action="" class="acwc_prompt_form" id="acwc_prompt_form">
					<div class="mb_10">
						<textarea style="display: none" class="acwc_prompt_title" id="acwc_prompt_title" rows="8"><?php echo esc_html($acwc_item['prompt'])?></textarea>
						<textarea style="display: none" name="title" class="acwc_prompt_title" id="acwc_prompt_title_filled" rows="8"><?php echo esc_html($acwc_item['prompt'])?></textarea>
						<?php
						if($acwc_fields && is_array($acwc_fields) && count($acwc_fields)){
							foreach($acwc_fields as $key=>$acwc_field){
							?>
								<div class="acwc_form_field">
									<label><?php echo esc_html(@$acwc_field['label'])?></label>
									<?php
									if($acwc_field['type'] == 'select'){
										$acwc_field_options = [];
										if(isset($acwc_field['options'])){
											if($acwc_item['type'] == 'custom'){
												$acwc_field_options = explode("|", $acwc_field['options']);
											}
											else{
												$acwc_field_options = $acwc_field['options'];
											}
										}
										?>
										<select required id="acwc_form_field-<?php echo esc_html($key)?>" name="<?php echo esc_html($acwc_field['id'])?>" data-label="<?php echo esc_html(@$acwc_field['label'])?>" data-type="<?php echo esc_html(@$acwc_field['type'])?>" data-min="<?php echo isset($acwc_field['min']) ? esc_html($acwc_field['min']) : ''?>" data-max="<?php echo isset($acwc_field['max']) ? esc_html($acwc_field['max']) : ''?>">
											<?php
											foreach($acwc_field_options as $acwc_field_option){
												echo '<option value="'.esc_html($acwc_field_option).'">'.esc_html($acwc_field_option).'</option>';
											}
											?>
										</select>
										<?php
									}
									elseif($acwc_field['type'] == 'checkbox' || $acwc_field['type'] == 'radio'){
										$acwc_field_options = [];
										if(isset($acwc_field['options'])){
											if($acwc_item['type'] == 'custom'){
												$acwc_field_options = explode("|", $acwc_field['options']);
											}
											else{
												$acwc_field_options = $acwc_field['options'];
											}
										}
										?>
										<div id="acwc_form_field-<?php echo esc_html($key)?>">
											<?php
											foreach($acwc_field_options as $acwc_field_option):
											?>
											<label><input name="<?php echo esc_html($acwc_field['id']).($acwc_field['type'] == 'checkbox' ? '[]':'')?>" value="<?php echo esc_html($acwc_field_option)?>" type="<?php echo esc_html($acwc_field['type'])?>">&nbsp;<?php echo esc_html($acwc_field_option)?></label>&nbsp;&nbsp;&nbsp;
											<?php
											endforeach;
											?>
										</div>
										<?php
									}
									elseif($acwc_field['type'] == 'textarea'){
									?>
										<textarea <?php echo isset($acwc_field['rows']) && !empty($acwc_field['rows']) ? ' rows="'.esc_html($acwc_field['rows']).'"': '';?><?php echo isset($acwc_field['cols']) && !empty($acwc_field['cols']) ? ' rows="'.esc_html($acwc_field['cols']).'"': '';?> required id="acwc_form_field-<?php echo esc_html($key)?>" name="<?php echo esc_html($acwc_field['id'])?>" data-label="<?php echo esc_html(@$acwc_field['label'])?>" data-type="<?php echo esc_html(@$acwc_field['type'])?>" type="<?php echo esc_html(@$acwc_field['type'])?>" data-min="<?php echo isset($acwc_field['min']) ? esc_html($acwc_field['min']) : ''?>" data-max="<?php echo isset($acwc_field['max']) ? esc_html($acwc_field['max']) : ''?>"></textarea>
										<?php
									}
									else{
										?>
										<input required id="acwc_form_field-<?php echo esc_html($key)?>" name="<?php echo esc_html($acwc_field['id'])?>" data-label="<?php echo esc_html(@$acwc_field['label'])?>" data-type="<?php echo esc_html(@$acwc_field['type'])?>" type="<?php echo esc_html(@$acwc_field['type'])?>" data-min="<?php echo isset($acwc_field['min']) ? esc_html($acwc_field['min']) : ''?>" data-max="<?php echo isset($acwc_field['max']) ? esc_html($acwc_field['max']) : ''?>">
										<?php
									}
									?>
								</div>
							<?php
							}
						}
						?>
						<div class="generate_btn_container">
							<button class="acwc_button acwc_prompt_button" id="acwc_prompt_button"><?php echo esc_html($acwc_generate_text);?></button>
							<button type="button" class="acwc_button acwc_prompt_stop-generate" id="acwc_prompt_stop-generate" style="display: none"><?php echo esc_html($acwc_stop_text);?></button>
						</div>
					</div>
					<div class="mb_5">
						<?php
						if($acwc_response_type == 'textarea'):
							if(is_user_logged_in()){
								wp_editor('','acwc_prompt_result', array('media_buttons' => true, 'textarea_name' => 'acwc_prompt_result'));
							}
							else{
								?>
								<textarea class="acwc_prompt_result" id="acwc_prompt_result" rows="12"></textarea>
								<?php
								if(isset($acwc_item['dnotice']) && $acwc_item['dnotice'] == 'no'):
								else:
									?>
								<a style="font-size: 13px;" href="<?php echo site_url('wp-login.php?action=register')?>"><?php echo esc_html($acwc_cnotice_text)?></a>
								<?php
								endif;
								?>
							<?php
							}
						else:
							echo '<div id="acwc_prompt_result"></div>';
							if(!is_user_logged_in()){
								if(isset($acwc_item['dnotice']) && $acwc_item['dnotice'] == 'no'){

								}
								else{
									?>
									<a style="font-size: 13px;" href="<?php echo site_url('wp-login.php?action=register')?>"><?php echo esc_html($acwc_cnotice_text)?></a>
									<?php
								}
							}
						endif;
						?>
					</div>
					<div class="acwc_prompt_save-result" id="acwc_prompt_save-result" style="display: none;margin-top: 10px;">
						<?php
						if(is_user_logged_in()):
						if(isset($acwc_item['ddraft']) && $acwc_item['ddraft'] == 'no'):
						else:
						?>
						<button type="button" class="acwc_button acwc_prompt_save-draft" id="acwc_prompt_save-draft"><?php echo esc_html($acwc_draft_text);?></button>
						<?php
							endif;
						endif;
						if(isset($acwc_item['dclear']) && $acwc_item['dclear'] == 'no'):
						else:
						?>
						<button type="button" class="acwc_button acwc_prompt_clear" id="acwc_prompt_clear"><?php echo esc_html($acwc_clear_text);?></button>
						<?php
						endif;
						?>
					</div>
						<input id="acwc_prompt_max_tokens" name="max_tokens" type="hidden" value="<?php echo esc_html($acwc_max_tokens);?>">
						<input id="acwc_prompt_temperature" name="temperature" type="hidden" value="<?php echo esc_html($acwc_temperature)?>">
						<input id="acwc_prompt_top_p" type="hidden" name="top_p" value="<?php echo esc_html($acwc_top_p)?>">
						<input id="acwc_prompt_best_of" name="best_of" type="hidden" value="<?php echo esc_html($acwc_best_of)?>">
						<input id="acwc_prompt_frequency_penalty" name="frequency_penalty" type="hidden" value="<?php echo esc_html($acwc_frequency_penalty)?>">
						<input id="acwc_prompt_presence_penalty" name="presence_penalty" type="hidden" value="<?php echo esc_html($acwc_presence_penalty)?>">
						<input id="acwc_prompt_stop" type="hidden" name="stop" type="hidden" value="<?php echo esc_html($acwc_stop_lists)?>">
						<input id="acwc_prompt_post_title" type="hidden" name="post_title" value="<?php echo esc_html($acwc_item['title'])?>">
                </form>
            </div>
        </div>
        <script>
            var acwc_prompt_logged = <?php echo is_user_logged_in() ? 'true' : 'false'?>;
            var acwcForm = document.getElementById('acwc_prompt_form');
            var acwcMaxToken = document.getElementById('acwc_prompt_max_tokens');
            var acwcTemperature = document.getElementById('acwc_prompt_temperature');
            var acwcTopP = document.getElementById('acwc_prompt_top_p');
            var acwcBestOf = document.getElementById('acwc_prompt_best_of');
            var acwcFP = document.getElementById('acwc_prompt_frequency_penalty');
            var acwcPP = document.getElementById('acwc_prompt_presence_penalty');
            var acwcStop = document.getElementById('acwc_prompt_stop-generate');
            var acwcPromptTitle = document.getElementById('acwc_prompt_title');
            var acwcPromptTitleFilled = document.getElementById('acwc_prompt_title_filled');
            var acwcGenerateBtn = document.getElementById('acwc_prompt_button');
            var acwcFormFields = <?php echo json_encode($acwc_fields,JSON_UNESCAPED_UNICODE)?>;
            var acwcItemType = '<?php echo esc_html($acwc_item['type'])?>';
            var acwcResponseType = '<?php echo esc_html($acwc_response_type)?>';
            var acwcUserLoggedIn = <?php echo is_user_logged_in() ? 'true': 'false'?>;
            var acwcEventURL = '<?php echo esc_html(add_query_arg('acwc_stream','yes',site_url().'/index.php'));?>';
            var acwcAjaxUrl = '<?php echo admin_url('admin-ajax.php')?>';
            var acwcAdminPost = '<?php echo admin_url('post.php')?>';
            <?php
            if(is_user_logged_in()):
            ?>
            var acwcSaveDraftBtn = document.getElementById('acwc_prompt_save-draft');
            <?php
            endif;
            ?>
            var acwcClearBtn = document.getElementById('acwc_prompt_clear');
            var acwcSaveResult = document.getElementById('acwc_prompt_save-result');
            var acwcFormSourceID = '<?php echo esc_html(get_the_ID())?>';
            var acwcFormNonce = '<?php echo esc_html(wp_create_nonce( 'acwc-formlog' ))?>';
            var acwcAjaxNonce = '<?php echo esc_html(wp_create_nonce( 'acwc-ajax-nonce' ))?>';
            var acwcFormId = <?php echo esc_html($acwc_item_id)?>;
            var acwcFormName = '<?php echo isset($acwc_item['title']) && !empty($acwc_item['title']) ? esc_html($acwc_item['title']) : ''?>';
        </script>
        <?php
    }
}
