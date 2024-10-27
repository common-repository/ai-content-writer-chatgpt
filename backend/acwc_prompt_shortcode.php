<?php
if ( ! defined( 'ABSPATH' ) ) exit;
$acwc_items = array();
$acwc_icons = array();
$acwc_models = array();
if(file_exists(ACWC_PLUGIN_DIR.'backend/json/categories.json')){
    $acwc_file_content = file_get_contents(ACWC_PLUGIN_DIR.'backend/json/categories.json');
    $acwc_file_content = json_decode($acwc_file_content, true);
    if($acwc_file_content && is_array($acwc_file_content) && count($acwc_file_content)){
        foreach($acwc_file_content as $key=>$item){
            $acwc_categories[$key] = trim($item);
        }
    }
}
if(file_exists(ACWC_PLUGIN_DIR.'backend/json/icons.json')){
    $acwc_file_content = file_get_contents(ACWC_PLUGIN_DIR.'backend/json/icons.json');
    $acwc_file_content = json_decode($acwc_file_content, true);
    if($acwc_file_content && is_array($acwc_file_content) && count($acwc_file_content)){
        foreach($acwc_file_content as $key=>$item){
            $acwc_icons[$key] = trim($item);
        }
    }
}
if(file_exists(ACWC_PLUGIN_DIR.'backend/json/prompts.json')){
    $acwc_file_content = file_get_contents(ACWC_PLUGIN_DIR.'backend/json/prompts.json');
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
        $acwc_meta_keys = array('prompt','editor','response','category','engine','max_tokens','temperature','top_p','best_of','frequency_penalty','presence_penalty','stop','color','icon','bgcolor','header','dans','ddraft','dclear','dnotice','generate_text','noanswer_text','draft_text','clear_text','stop_text','cnotice_text');
        foreach($acwc_meta_keys as $acwc_meta_key){
            $sql .= ", (".$wpdb->prepare("SELECT %i.%i FROM %i %i WHERE %i.%i = %s AND p.ID=%i.%i LIMIT 1",
                    $acwc_meta_key,
                    'meta_value',
                    $wpdb->postmeta,
                    $acwc_meta_key,
                    $acwc_meta_key,
                    'meta_key',
                    'acwc_prompt_'.$acwc_meta_key,
                    $acwc_meta_key,
                    'post_id'
                ).") as ".$acwc_meta_key;
        }
        $sql .= $wpdb->prepare(" FROM %i p WHERE p.post_type = 'acwc_prompt' AND p.post_status='publish' AND p.ID=%d ORDER BY p.post_date DESC",$wpdb->posts,$acwc_item_id);

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
        $acwc_icon = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 512"><path d="M320 0c17.7 0 32 14.3 32 32V96H480c35.3 0 64 28.7 64 64V448c0 35.3-28.7 64-64 64H160c-35.3 0-64-28.7-64-64V160c0-35.3 28.7-64 64-64H288V32c0-17.7 14.3-32 32-32zM208 384c-8.8 0-16 7.2-16 16s7.2 16 16 16h32c8.8 0 16-7.2 16-16s-7.2-16-16-16H208zm96 0c-8.8 0-16 7.2-16 16s7.2 16 16 16h32c8.8 0 16-7.2 16-16s-7.2-16-16-16H304zm96 0c-8.8 0-16 7.2-16 16s7.2 16 16 16h32c8.8 0 16-7.2 16-16s-7.2-16-16-16H400zM264 256c0-22.1-17.9-40-40-40s-40 17.9-40 40s17.9 40 40 40s40-17.9 40-40zm152 40c22.1 0 40-17.9 40-40s-17.9-40-40-40s-40 17.9-40 40s17.9 40 40 40zM48 224H64V416H48c-26.5 0-48-21.5-48-48V272c0-26.5 21.5-48 48-48zm544 0c26.5 0 48 21.5 48 48v96c0 26.5-21.5 48-48 48H576V224h16z"/></svg>';
        if(isset($acwc_item['icon']) && !empty($acwc_item['icon']) && isset($acwc_icons[$acwc_item['icon']]) && !empty($acwc_icons[$acwc_item['icon']])){
            $acwc_icon = $acwc_icons[$acwc_item['icon']];
        }
        $acwc_icon_color = isset($acwc_item['color']) && !empty($acwc_item['color']) ? $acwc_item['color'] : '#19c37d';
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
        $acwc_response_type = isset($acwc_item['editor']) && $acwc_item['editor'] == 'div' ? 'div' : 'textarea';
        ?>
        <div class="acwc_prompt_item">
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
					<h3>Enter Your Text</h3>
					<div class="mb_10">
						<textarea name="title" class="acwc_prompt_title" id="acwc_prompt_title" rows="8"><?php echo $acwc_item['type'] == 'custom' ? esc_html($acwc_item['prompt']).".\n\n":esc_html($acwc_item['prompt'])?></textarea>
						<div class="generate_btn_container">
							<button style="<?php echo isset($acwc_item['dans']) && $acwc_item['dans'] == 'no' ? 'margin-left:0':''?>" class="acwc_button acwc_prompt_button" id="acwc_prompt_button"><?php echo esc_html($acwc_generate_text);?></button>
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
								<a style="font-size: 13px;" href="<?php echo site_url('wp-login.php?action=register'); ?>"><?php echo esc_html($acwc_cnotice_text); ?></a>
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
					<input id="acwc_prompt_stop" name="stop" type="hidden" value="<?php echo esc_html($acwc_stop_lists)?>">
					<input id="acwc_prompt_post_title" type="hidden" name="post_title" value="<?php echo esc_html($acwc_item['title'])?>">
                </form>
            </div>
        </div>
        <script>
            let prompt_id = <?php echo esc_html($acwc_item_id)?>;
            let prompt_name = '<?php echo isset($acwc_item['title']) && !empty($acwc_item['title']) ? esc_html($acwc_item['title']) : ''?>';
            let prompt_response = '';
            let wp_nonce = '<?php echo esc_html(wp_create_nonce( 'acwc-promptbase' ))?>'
            let wp_ajax_nonce = '<?php echo esc_html(wp_create_nonce( 'acwc-ajax-nonce' ))?>'
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
            var acwcGenerateBtn = document.getElementById('acwc_prompt_button');
            var acwcResponseType = '<?php echo esc_html($acwc_response_type)?>';
            let acwc_limited_token = false;
            <?php
            if(is_user_logged_in()):
            ?>
            var acwcSaveDraftBtn = document.getElementById('acwc_prompt_save-draft');
            <?php
            endif;
            ?>
            var acwcClearBtn = document.getElementById('acwc_prompt_clear');
            var acwcSaveResult = document.getElementById('acwc_prompt_save-result');
            var eventGenerator = false;
            function acwcBasicEditor(){
                var basicEditor = true;
                if(acwc_prompt_logged){
                    var editor = tinyMCE.get('acwc_prompt_result');
                    if ( document.getElementById('wp-acwc_prompt_result-wrap').classList.contains('tmce-active') && editor ) {
                        basicEditor = false;
                    }
                }
                return basicEditor;
            }
            function acwcSetContent(value){
                if(acwcResponseType === 'textarea') {
                    if (acwcBasicEditor()) {
                        document.getElementById('acwc_prompt_result').value = value;
                    } else {
                        var editor = tinyMCE.get('acwc_prompt_result');
                        editor.setContent(value);
                    }
                }
                else{
                    document.getElementById('acwc_prompt_result').innerHTML = value;
                }
            }
            function acwcGetContent(){
                if(acwcResponseType === 'textarea') {
                    if (acwcBasicEditor()) {
                        return document.getElementById('acwc_prompt_result').value
                    } else {
                        var editor = tinyMCE.get('acwc_prompt_result');
                        var content = editor.getContent();
                        content = content.replace(/<\/?p(>|$)/g, "");
                        return content;
                    }
                }
                else return document.getElementById('acwc_prompt_result').innerHTML;
            }
            function acwcLoadingBtn(btn){
                btn.setAttribute('disabled','disabled');
                btn.innerHTML += '<span class="acwc_loader"></span>';
            }
            function acwcRmLoading(btn){
                btn.removeAttribute('disabled');
                btn.removeChild(btn.getElementsByTagName('span')[0]);
            }
            function acwcEventClose(){
                acwcStop.style.display = 'none';
                if(!acwc_limited_token) {
                    acwcSaveResult.style.display = 'block';
                }
                acwcRmLoading(acwcGenerateBtn);
                eventGenerator.close();
            }
            var acwc_break_newline = "<?php echo is_user_logged_in() ? '<br/><br />': '\n\n'?>";
            acwcForm.addEventListener('submit', function(e){
				
                e.preventDefault();
                var max_tokens = acwcMaxToken.value;
                var temperature = acwcTemperature.value;
                var top_p = acwcTopP.value;
                var best_of = acwcBestOf.value;
                var frequency_penalty = acwcFP.value;
                var presence_penalty = acwcPP.value;
                var error_message = false;
                var title = acwcPromptTitle.value;
                if(title === ''){
                    error_message = 'Please insert prompt';
                }
                else if(max_tokens === ''){
                    error_message = 'Please enter max tokens';
                }
                else if(parseFloat(max_tokens) < 1 || parseFloat(max_tokens) > 8000){
                    error_message = 'Please enter a valid max tokens value between 1 and 8000';
                }
                else if(temperature === ''){
                    error_message = 'Please enter temperature';
                }
                else if(parseFloat(temperature) < 0 || parseFloat(temperature) > 1){
                    error_message = 'Please enter a valid temperature value between 0 and 1';
                }
                else if(top_p === ''){
                    error_message = 'Please enter Top P';
                }
                else if(parseFloat(top_p) < 0 || parseFloat(top_p) > 1){
                    error_message = 'Please enter a valid Top P value between 0 and 1';
                }
                else if(best_of === ''){
                    error_message = 'Please enter best of';
                }
                else if(parseFloat(best_of) < 1 || parseFloat(best_of) > 20){
                    error_message = 'Please enter a valid best of value between 0 and 1';
                }
                else if(frequency_penalty === ''){
                    error_message = 'Please enter frequency penalty';
                }
                else if(parseFloat(frequency_penalty) < 0 || parseFloat(frequency_penalty) > 2){
                    error_message = 'Please enter a valid frequency penalty value between 0 and 2';
                }
                else if(presence_penalty === ''){
                    error_message = 'Please enter presence penalty';
                }
                else if(parseFloat(presence_penalty) < 0 || parseFloat(presence_penalty) > 2){
                    error_message = 'Please enter a valid presence penalty value between 0 and 2';
                }
                if(error_message){
                    alert(error_message);
                }
                else{
                    prompt_response = '';
                    let startTime = new Date();
                    let queryString = new URLSearchParams(new FormData(acwcForm)).toString();
                    acwcLoadingBtn(acwcGenerateBtn);
                    acwcSaveResult.style.display = 'none';
                    acwcStop.style.display = 'inline';
                    acwcSetContent('');
                    var acwc_limitLines = 1;
                    var count_line = 0;
                    var currentContent = '';
                    queryString += '&source_stream=promptbase';
                    queryString += '&nonce='+wp_ajax_nonce;
                    eventGenerator = new EventSource('<?php echo esc_html(add_query_arg('acwc_stream','yes',site_url().'/index.php'));?>&' + queryString);
                    var acwc_response_events = 0;
                    var acwc_newline_before = false;
                    acwc_limited_token = false;
                    eventGenerator.onmessage = function (e) {
                        currentContent = acwcGetContent();
                        if(e.data === "[DONE]"){
                            count_line += 1;
                            acwcSetContent(currentContent+acwc_break_newline);
                            acwc_response_events = 0;
                        }
                        else if(e.data === "[LIMITED]"){
                            acwc_limited_token = true;
                            count_line += 1;
                            acwcSetContent(currentContent+acwc_break_newline);
                            acwc_response_events = 0;
                        }
                        else{
                            var result = JSON.parse(e.data);
                            var content_generated= '';
                            if (result.error !== undefined) {
                                content_generated = result.error.message;
                            } else {
                                content_generated = result.choices[0].delta !== undefined ? (result.choices[0].delta.content !== undefined ? result.choices[0].delta.content : '') : result.choices[0].text;
                            }
                            prompt_response += content_generated;
                            if((content_generated === '\n' || content_generated === ' \n' || content_generated === '.\n' || content_generated === '\n\n' || content_generated === '.\n\n') && acwc_response_events > 0 && currentContent !== ''){
                                if(!acwc_newline_before) {
                                    acwc_newline_before = true;
                                    acwcSetContent(currentContent + acwc_break_newline);
                                }
                            }
                            else if(content_generated === '\n' && acwc_response_events === 0 && currentContent === ''){

                            }
                            else {
                                acwc_newline_before = false;
                                acwc_response_events += 1;
                                acwcSetContent(currentContent + content_generated);
                            }
                        }
                        if (count_line === acwc_limitLines) {
                            if(!acwc_limited_token) {
                                let endTime = new Date();
                                let timeDiff = endTime - startTime;
                                timeDiff = timeDiff / 1000;
                                queryString += '&action=acwc_prompt_log&prompt_id=' + prompt_id + '&prompt_name=' + prompt_name + '&prompt_response=' + prompt_response + '&duration=' + timeDiff + '&_wpnonce=' + wp_nonce + '&source_id=<?php echo get_the_ID()?>';
                                const xhttp = new XMLHttpRequest();
                                xhttp.open('POST', '<?php echo admin_url('admin-ajax.php')?>');
                                xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
                                xhttp.send(queryString);
                                xhttp.onreadystatechange = function (oEvent) {
                                    if (xhttp.readyState === 4) {

                                    }
                                }
                            }
                            acwcEventClose();
                        }
                    }
                }
                return false;
            });
            acwcStop.addEventListener('click', function (e){
                e.preventDefault();
                acwcEventClose();
            });
            if(acwcClearBtn) {
                acwcClearBtn.addEventListener('click', function (e) {
                    e.preventDefault();
                    acwcSetContent('');
                });
            }
            <?php
            if(is_user_logged_in()):
            ?>
            if(acwcSaveDraftBtn) {
                acwcSaveDraftBtn.addEventListener('click', function (e) {
                    e.preventDefault();
                    var title = document.getElementById('acwc_prompt_post_title').value;
                    var content = acwcGetContent();
                    if (title === '') {
                        alert('Please insert title');
                    } else if (content === '') {
                        alert('Please wait generate content')
                    } else {
                        const xhttp = new XMLHttpRequest();
                        xhttp.open('POST', '<?php echo admin_url('admin-ajax.php')?>');
                        xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
                        xhttp.send('action=acwc_save_draft_post_extra&title=' + title + '&content=' + content+'&save_source=promptbase&nonce='+wp_ajax_nonce);
                        acwcLoadingBtn(acwcSaveDraftBtn);
                        xhttp.onreadystatechange = function (oEvent) {
                            if (xhttp.readyState === 4) {
                                acwcRmLoading(acwcSaveDraftBtn);
                                if (xhttp.status === 200) {
                                    var acwc_response = this.responseText;
                                    acwc_response = JSON.parse(acwc_response);
                                    if (acwc_response.status === 'success') {
                                        window.location.href = '<?php echo admin_url('post.php')?>?post=' + acwc_response.id + '&action=edit';
                                    } else {
                                        alert(acwc_response.msg);
                                    }
                                } else {
                                    alert('Something went wrong');
                                }
                            }
                        }
                    }
                })
            }
            <?php
            endif;
            ?>
        </script>
        <?php
    }
}
