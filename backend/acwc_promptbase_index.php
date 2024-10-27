<?php
if ( ! defined( 'ABSPATH' ) ) exit;
global $wpdb;
$acwc_categories = array();
$acwc_items = array();
$acwc_icons = array();
$acwc_models = array();
$acwc_authors = array('default' => array('name' => 'AI Content Writer - ChatGPT','count' => 0));
if(file_exists(ACWC_PLUGIN_DIR.'backend/json/categories.json')){
    $acwc_file_content = file_get_contents(ACWC_PLUGIN_DIR.'backend/json/categories.json');
    $acwc_file_content = json_decode($acwc_file_content, true);
    if($acwc_file_content && is_array($acwc_file_content) && count($acwc_file_content)){
        foreach($acwc_file_content as $key=>$item){
            $acwc_categories[$key] = trim($item);
        }
    }
}
if(file_exists(ACWC_PLUGIN_DIR.'backend/json/prompts.json')){
    $acwc_file_content = file_get_contents(ACWC_PLUGIN_DIR.'backend/json/prompts.json');
    $acwc_file_content = json_decode($acwc_file_content, true);
    if($acwc_file_content && is_array($acwc_file_content) && count($acwc_file_content)){
        foreach($acwc_file_content as $item){
            $item['type'] = 'json';
            $item['author'] = 'default';
            $acwc_authors['default']['count'] += 1;
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
$sql = "SELECT p.ID as id,p.post_title as title,p.post_author as author, p.post_content as description";
$acwc_meta_keys = array('prompt','editor','response','category','engine','max_tokens','temperature','top_p','best_of','frequency_penalty','presence_penalty','stop','bgcolor','header','ddraft','dclear','dnotice','generate_text','draft_text','clear_text','stop_text','cnotice_text');
foreach($acwc_meta_keys as $acwc_meta_key){

    $sql .= ", (".$wpdb->prepare("SELECT %i.%i FROM %i %i WHERE %i.%i=%s AND p.ID=%i.%i LIMIT 1",
            $acwc_meta_key,
            'meta_value',
            $wpdb->postmeta,
            $acwc_meta_key,
            $acwc_meta_key,
            'meta_key',
            'acwc_prompt_'.$acwc_meta_key,
            $acwc_meta_key,
            'post_id'
        ).") as  ".$acwc_meta_key;
}

$sql .= $wpdb->prepare(" FROM %i p WHERE p.post_type = 'acwc_prompt' AND p.post_status='publish' ORDER BY p.post_date DESC",$wpdb->posts);
$acwc_custom_prompts = $wpdb->get_results($sql,ARRAY_A);
if($acwc_custom_prompts && is_array($acwc_custom_prompts) && count($acwc_custom_prompts)){
    foreach ($acwc_custom_prompts as $acwc_custom_prompt){
        $acwc_custom_prompt['type'] = 'custom';
        $acwc_items[] = $acwc_custom_prompt;
        if(!isset($acwc_authors[$acwc_custom_prompt['author']])){
            $prompt_author = get_user_by('ID', $acwc_custom_prompt['author']);
            $acwc_authors[$acwc_custom_prompt['author']] = array('name' => $prompt_author->display_name, 'count' => 1);
        }
        else{
            $acwc_authors[$acwc_custom_prompt['author']]['count'] += 1;
        }
    }
}
$acwc_per_page = 36;
wp_enqueue_editor();
wp_enqueue_script('wp-color-picker');
wp_enqueue_style('wp-color-picker');
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
<style>
    .acwc_prompt_item{
        cursor: pointer;
        position: relative;
    }
    .acwc_prompt_content{
        margin-left: 10px;
        flex: 1;
    }
    .acwc_prompt_content p{
        margin: 5px 0;
        font-size: 12px;
        height: 36px;
        overflow: hidden;
    }
    .acwc_disappear_item{
        position: absolute;
        top: -10000px;		
    }
    .acwc_prompt_items{
        position: relative;
        overflow-y: hidden;
    }
    .acwc_paginate .page-numbers{
        background: #e5e5e5;
        margin-right: 5px;
        cursor: pointer;
    }
    .acwc_paginate .page-numbers.current{
        background: #fff;
    }
    .acwc_paginate_settings > div{
        display: flex;
        align-items: center;
    }
    .acwc_paginate_settings > div > strong{
        display: inline-block;
        width: 150px;
    }
    .acwc_paginate_settings > div > strong > small{
        font-weight: normal;
        display: block;
    }
    .acwc_paginate_settings > div > input,.acwc_paginate_settings > div > select{
        width: 200px;
        margin: 0;
    }
    .acwc_paginate_settings .acwc_prompt_sample{
        display: block;
        position: relative;
    }
    .acwc_paginate_settings .acwc_prompt_sample:hover .acwc_prompt_response{
        display: block;
    }
    .acwc_paginate_settings .acwc_prompt_response{
        background: #333;
        border: 1px solid #444;
        position: absolute;
        border-radius: 3px;
        color: #fff;
        padding: 5px;
        width: 100%;
        bottom: calc(100% + 5px);
        right: calc(50% - 55px);
        z-index: 99;
        display: none;
    }
    .acwc_paginate_settings .acwc_prompt_response:after,.acwc_paginate_settings .acwc_prompt_response:before{
        top: 100%;
        left: 50%;
        border: solid transparent;
        content: "";
        height: 0;
        width: 0;
        position: absolute;
        pointer-events: none;
    }
    .acwc_paginate_settings .acwc_prompt_response:before{
        border-color: rgba(68, 68, 68, 0);
        border-top-color: #444;
        border-width: 7px;
        margin-left: -7px;
    }
    .acwc_paginate_settings .acwc_prompt_response:after{
        border-color: rgba(51, 51, 51, 0);
        border-top-color: #333;
        border-width: 6px;
        margin-left: -6px;
    }
    .acwc_model_content{
        max-height: calc(100% - 103px);
        overflow-y: auto;
    }
    .acwc_notice_text {
        padding: 10px;
        background-color: #F8DC6F;
        text-align: left;
        margin-bottom: 12px;
        color: #000;
        box-shadow: rgba(99, 99, 99, 0.2) 0px 2px 8px 0px;
    }
    .acwc_create_prompt{
		margin-bottom: 10px!important;
		background: #1d2327;
		color: #FFF;
		font-size: 16px;
		border: 0;
		cursor: pointer;
		border-radius: 6px;
		height: 40px;
		line-height: 20px;		
    }
    .acwc_prompt_icons{}
    .acwc_prompt_icons span{
        padding: 10px;
        border-radius: 4px;
        border: 1px solid #ccc;
        display: inline-flex;
        justify-content: center;
        align-items: center;
        margin-right: 5px;
        margin-bottom: 5px;
        cursor: pointer;
        color: #333;
    }
    .acwc_prompt_icons span svg{
        fill: currentColor;
        width: 30px;
        height: 30px;
    }
    .acwc_prompt_icons span.icon_selected{
        background: #343434;
        color: #fff;
    }
    .acwc_holder_pick{
        position: absolute;
    }
    .acwc_container_pick{
        position: relative;
    }
    .acwc_prompt_action{
		position: absolute;
		right: 5px;
		top: 0;
		display: none;
		bottom: 0;
		margin: auto;
		height: 27px;
    }
    .acwc_prompt_item:hover .acwc_prompt_action{
        display: block;
    }
    .acwc_prompt_action-edit{}
    .acwc_prompt_action-delete{
        background: #9d0000!important;
        border-color: #9b0000!important;
        color: #fff!important;
    }
    .acwc_modal_tabs{
        margin: 0;
        display: flex;
    }
    .acwc_modal_tabs li{
        padding: 12px 15px;
        border-top-left-radius: 3px;
        border-top-right-radius: 3px;
        background: #1d2327;
        margin-bottom: 0;
        margin-right: 5px;
        border-top: 1px solid #ccc;
        border-left: 1px solid #ccc;
        border-right: 1px solid #ccc;
        cursor: pointer;
        position: relative;
        top: 1px;
        color: #fff;
    }
    .acwc_modal_tabs li.acwc_tab_active{
        background: #fff;
        color: #333;
    }
    .acwc_modal_tab_content{
        border: 1px solid #ccc;
    }
    .acwc_modal_tab{
        padding: 10px;
    }
    .acwc_notice_text_rw_b {
    padding: 10px;
    text-align: left;
    margin-bottom: 12px;
    }
	
.acwc_prompt_container{
	padding:10px;
}
.acwc_prompt_category_container{
	width:100%;
}
.acwc_prompt_category_container ul li{
	display: inline-block;
}
.acwc_prompt_category_container ul li label{
	background: #FFF;
	padding: 10px;
	border: 1px solid #ccc;
	border-radius: 5px;
	font-size: 16px;
	margin: 0;
	display: block;
	text-align: center;
}
.acwc_prompt_category_container ul li input[type=checkbox]{
	display:none;
}
.acwc_prompt_category_container ul li input[type=checkbox]:checked + label{
	background:#000;
	color:#FFF;
}
.acwc_prompt_search{
	width: 100% !important;
	padding: 5px !important;
	font-size: 17px;
}
.acwc_prompt_topic_container{}
.acwc_prompt_item{
	display: flex;
    background: #FFF;
    padding: 10px;
    border-radius: 5px;
    cursor: pointer;
	border: 1px solid #fff;
	box-shadow: 1px 1px 5px #ccc;
}
.acwc_prompt_item:hover{
	border-color: #ccc;
}
.acwc_model_settings_container{
	display:none;
	border-bottom: 1px solid;
    margin-bottom: 30px;
}
.model_settings{
	font-size: 15px;
	cursor: pointer;
	text-decoration: underline;
}
.acwc_prompt_result{
	width:100%;
}
</style>
<div class="acwc_create_prompt-content" style="display: none">
    <?php
    wp_nonce_field('acwc_promptbase_save');
    ?>
    <input type="hidden" name="action" value="acwc_update_prompt">
    <input type="hidden" name="id" value="" class="acwc_create_prompt-id">
    <ul class="acwc_modal_tabs">
        <li class="acwc_tab_active" data-target="properties">Title & Desc.</li>
        <li data-target="ai-engine">Open AI Option</li>
        <li data-target="frontend">Button & Message</li>
    </ul>
    <div class="acwc_modal_tab_content mb_10">
        <div class="acwc_modal_tab acwc_modal_tab-properties">
            <div class="acwc_block mb_10">
                <div class="acwc_block_3">
                    <strong class="d_block mb_5">Title</strong>
                    <input type="text" name="title" required class=" w_100">
                </div>
                <div class="acwc_block_3">
                    <strong class="d_block mb_5">Category</strong>
                    <select name="category" class="w_100 acwc_create_prompt_category">
                        <?php
                        foreach($acwc_categories as $key=>$acwc_category){
                            echo '<option value="'.esc_html($key).'">'.esc_html($acwc_category).'</option>';
                        }
                        ?>
                    </select>
                </div>
            </div>
            <div class="mb_10">
                <strong class="d_block mb_5">Description</strong>
                <input type="text" name="description" required class=" w_100 acwc_create_prompt-description">
            </div>
            <div class="mb_10">
                <strong class="d_block mb_5">Prompt</strong>
                <textarea name="prompt" required class=" w_100 acwc_create_prompt-prompt"></textarea>
            </div>            
        </div>
        <div class="acwc_modal_tab acwc_modal_tab-ai-engine" style="display: none">
            <div class="acwc_block mb_10">
                <div class="acwc_block_1">
                    <strong class="d_block mb_5">Engine</strong>
                    <select name="engine" class="w_100 acwc_create_prompt-engine" required>
                        <option value="gpt-3.5-turbo">gpt-3.5-turbo</option>
                        <?php
                        foreach($acwc_models as $acwc_model){
                            echo '<option value="' . esc_html($acwc_model) . '">' . esc_html($acwc_model) . '</option>';
                        }
                        ?>
                        <option value="gpt-4">gpt-4</option>
                        <option value="gpt-4-32k">gpt-4-32k</option>
                    </select>
                </div>
                <div>
                    <strong class="d_block mb_5">Max Tokens</strong>
                    <input type="text" name="max_tokens" class=" w_100 acwc_create_prompt-max_tokens">
                </div>
                <div>
                    <strong class="d_block mb_5">Temperature</strong>
                    <input type="text" name="temperature" class=" w_100 acwc_create_prompt-temperature">
                </div>
                <div>
                    <strong class="d_block mb_5">Top P</strong>
                    <input type="text" name="top_p" class=" w_100 acwc_create_prompt-top_p">
                </div>
                <div>
                    <strong class="d_block mb_5">Best Of</strong>
                    <input type="text" name="best_of" class=" w_100 acwc_create_prompt-best_of">
                </div>
                <div>
                    <strong class="d_block mb_5">Frequency Penalty</strong>
                    <input type="text" name="frequency_penalty" class=" w_100 acwc_create_prompt-frequency_penalty">
                </div>
                <div>
                    <strong class="d_block mb_5">Presence Penalty</strong>
                    <input type="text" name="presence_penalty" class=" w_100 acwc_create_prompt-presence_penalty">
                </div>
                <div>
                    <strong class="d_block mb_5">Stop</strong>
                    <input type="text" name="stop" class=" w_100 acwc_create_prompt-stop">
                </div>
            </div>
        </div>
        
        <div class="acwc_modal_tab acwc_modal_tab-frontend" style="display: none">
            <div class="acwc_block mb_10">
                <div class="acwc_block_1">
                    <strong class="d_block mb_5">Result</strong>
                    <select name="editor" class="w_100 acwc_create_prompt-editor">
                        <option value="textarea">Text Editor</option>
                        <option value="div">Inline</option>
                    </select>
                </div>
                <div class="acwc_block_1">
                    <strong class="d_block mb_5">Header</strong>
                    <select name="header" class="w_100 acwc_create_prompt-header">
                        <option value="">Yes</option>
                        <option value="no">No</option>
                    </select>
                </div>
                
                <div class="acwc_block_1">
                    <strong class="d_block mb_5">Draft Button</strong>
                    <select name="ddraft" class="w_100 acwc_create_prompt-ddraft">
                        <option value="">Yes</option>
                        <option value="no">No</option>
                    </select>
                </div>
                <div class="acwc_block_1">
                    <strong class="d_block mb_5">Clear Button</strong>
                    <select name="dclear" class="w_100 acwc_create_prompt-dclear">
                        <option value="">Yes</option>
                        <option value="no">No</option>
                    </select>
                </div>
                <div class="acwc_block_1">
                    <strong class="d_block mb_5">Notice</strong>
                    <select name="dnotice" class="w_100 acwc_create_prompt-dnotice">
                        <option value="">Yes</option>
                        <option value="no">No</option>
                    </select>
                </div>
                <div class="acwc_block_2">
                    <strong class="d_block mb_5">Generate Button</strong>
                    <input value="Generate" type="text" name="generate_text" class=" w_100 acwc_create_prompt-generate_text">
                </div>
                <div class="acwc_block_2">
                    <strong class="d_block mb_5">Draft Text</strong>
                    <input value="Save Draft" type="text" name="draft_text" class=" w_100 acwc_create_prompt-draft_text">
                </div>
                <div class="acwc_block_2">
                    <strong class="d_block mb_5">Clear Text</strong>
                    <input value="Clear" type="text" name="clear_text" class=" w_100 acwc_create_prompt-clear_text">
                </div>
                <div class="acwc_block_2">
                    <strong class="d_block mb_5">Stop Text</strong>
                    <input value="Stop" type="text" name="stop_text" class=" w_100 acwc_create_prompt-stop_text">
                </div>
                <div class="acwc_block_2">
                    <strong class="d_block mb_5">Notice Text</strong>
                    <input value="Please register to save your result" type="text" name="cnotice_text" class=" w_100 acwc_create_prompt-cnotice_text">
                </div>
            </div>
        </div>
    </div>
    <button class="button  acwc_create_prompt-save">Save</button>
</div>
<?php
if(isset($_GET['update_prompt']) && !empty($_GET['update_prompt'])):
    ?>
    <p style="padding: 6px 12px;border: 1px solid green;border-radius: 3px;background: lightgreen;">
        <strong>Success:</strong> Congrats! Your prompt created! You can add this shortcode to your page: [acwc_prompt id=<?php echo sanitize_text_field($_GET['update_prompt'])?> custom=yes]
    </p>
<?php
endif;
?>
<div class="acwc_prompt_container">
        
		<div class="acwc_prompt_category_container">        
            <div class=""><button class="acwc_create_prompt" type="button">+ Add Your Prompt</button></div>
            <h3>Author</h3>
            <ul class="acwc_list acwc_authors">
                <?php
                if(count($acwc_authors)){
					$count = 0;
                    foreach($acwc_authors as $key=>$acwc_author){
						$count++;
                        ?>
                        <li><input type="checkbox" value="<?php echo esc_attr($key)?>" id="auth<?php echo $count; ?>"><label for="auth<?php echo $count; ?>"><?php echo esc_html($acwc_author['name'])?> (<?php echo esc_html($acwc_author['count'])?>)</label></li>
                        <?php
                    }
                }
                ?>
            </ul>
            <h3>Category</h3>
            <ul class="acwc_list acwc_categories">
                <?php
                if(count($acwc_categories)){
					$count = 0;
                    foreach($acwc_categories as $acwc_category){
						$count++;
                        ?>
                        <li><input id="cat<?php echo $count; ?>" type="checkbox" value="<?php echo sanitize_title($acwc_category)?>"><label for="cat<?php echo $count; ?>"><?php echo esc_html($acwc_category)?></label></li>
                        <?php
                    }
                }
                ?>
            </ul>
        </div>
		<div class="acwc_prompt_topic_container">
        
            <div class="mb_10">
                <input class="w_100 d_block acwc_prompt_search" type="text" placeholder="Search Prompt">
            </div>
            <div class="acwc_prompt_topic_list acwc_prompt_topic_lists acwc_block_three">
                <?php
                if(count($acwc_items)):
                    foreach($acwc_items as $acwc_item):
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
                        $acwc_stop_lists = '';
                        if(is_array($acwc_stop) && count($acwc_stop)){
                            foreach($acwc_stop as $item_stop){
                                if($item_stop === "\n"){
                                    $item_stop = '\n';
                                }
                                $acwc_stop_lists = empty($acwc_stop_lists) ? $item_stop : ','.$item_stop;
                            }
                        }
                        elseif(is_array($acwc_stop) && !count($acwc_stop)){
                            $acwc_stop_lists = '';
                        }
                        else{
                            $acwc_stop_lists = $acwc_stop;
                        }
                        if(count($acwc_item_categories)){
                            foreach($acwc_item_categories as $acwc_item_category){
                                if(isset($acwc_categories[$acwc_item_category]) && !empty($acwc_categories[$acwc_item_category])){
                                    $acwc_item_categories_name[] = $acwc_categories[$acwc_item_category];
                                }
                            }
                        }
                        ?>
                        <div
                            id="acwc_prompt_item-<?php echo esc_html($acwc_item['id'])?>"
                            data-title="<?php echo esc_html($acwc_item['title'])?>"
                            data-type="<?php echo esc_html($acwc_item['type'])?>"
                            data-id="<?php echo esc_html($acwc_item['id'])?>"
                            data-post_title="<?php echo esc_html($acwc_item['title'])?>"
                            data-desc="<?php echo esc_html(@$acwc_item['description'])?>"
                            data-description="<?php echo esc_html(@$acwc_item['description'])?>"
                            data-engine="<?php echo esc_html($acwc_engine)?>"
                            data-max_tokens="<?php echo esc_html($acwc_max_tokens)?>"
                            data-temperature="<?php echo esc_html($acwc_temperature)?>"
                            data-top_p="<?php echo esc_html($acwc_top_p)?>"
                            data-best_of="<?php echo esc_html($acwc_best_of)?>"
                            data-frequency_penalty="<?php echo esc_html($acwc_frequency_penalty)?>"
                            data-presence_penalty="<?php echo esc_html($acwc_presence_penalty)?>"
                            data-stop="<?php echo esc_html($acwc_stop_lists)?>"
                            data-categories="<?php echo esc_html(implode(', ',$acwc_item_categories_name))?>"
                            data-category="<?php echo esc_html($acwc_item['category'])?>"
                            data-prompt="<?php echo esc_html(@$acwc_item['prompt']);?>"
                            data-estimated="<?php echo isset($acwc_item['estimated']) ? esc_html($acwc_item['estimated']) : '';?>"
                            data-editor="<?php echo isset($acwc_item['editor']) && $acwc_item['editor'] == 'div' ? 'div' : 'textarea'?>"
                            data-response="<?php echo esc_html(@$acwc_item['response']);?>"
                            data-header="<?php echo isset($acwc_item['header']) ? esc_html($acwc_item['header']) : '';?>"
                            data-bgcolor="<?php echo isset($acwc_item['bgcolor']) ? esc_html($acwc_item['bgcolor']) : '';?>"
                            data-ddraft="<?php echo isset($acwc_item['ddraft']) ? esc_html($acwc_item['ddraft']) : '';?>"
                            data-dclear="<?php echo isset($acwc_item['dclear']) ? esc_html($acwc_item['dclear']) : '';?>"
                            data-dnotice="<?php echo isset($acwc_item['dnotice']) ? esc_html($acwc_item['dnotice']) : '';?>"
                            data-generate_text="<?php echo isset($acwc_item['generate_text']) && !empty($acwc_item['generate_text']) ? esc_html($acwc_item['generate_text']) : 'Generate';?>"
                            data-draft_text="<?php echo isset($acwc_item['draft_text']) && !empty($acwc_item['draft_text']) ? esc_html($acwc_item['draft_text']) : 'Save Draft';?>"
                            data-clear_text="<?php echo isset($acwc_item['clear_text']) && !empty($acwc_item['clear_text']) ? esc_html($acwc_item['clear_text']) : 'Clear';?>"
                            data-stop_text="<?php echo isset($acwc_item['stop_text']) && !empty($acwc_item['stop_text']) ? esc_html($acwc_item['stop_text']) : 'Stop';?>"
                            data-cnotice_text="<?php echo isset($acwc_item['cnotice_text']) && !empty($acwc_item['cnotice_text']) ? esc_html($acwc_item['cnotice_text']) : 'Please register to save your result';?>"
                            class="acwc_prompt_item d_flex align_items_center <?php echo implode(' ',$acwc_item_categories)?><?php echo ' user-'.esc_html($acwc_item['author'])?><?php echo ' acwc_prompt_item-'.esc_html($acwc_item['type']).'-'.esc_html($acwc_item['id']);?>">
                            <div class="acwc_prompt_icon">
							<?php preg_match_all('/\b\w/', $acwc_item['title'], $matches);$firstLetters = implode('', $matches[0]); echo  substr($firstLetters,0,2);?>
							</div>
                            <div class="acwc_prompt_content">
                                <strong><?php echo isset($acwc_item['title']) && !empty($acwc_item['title']) ? esc_html($acwc_item['title']) : ''?></strong>
                                <?php
                                if(isset($acwc_item['description']) && !empty($acwc_item['description'])){
                                    echo '<p>'.esc_html($acwc_item['description']).'</p>';
                                }
                                ?>
                            </div>
                            <?php
                            if($acwc_item['type'] == 'custom'):
                                ?>
                                <div class="acwc_prompt_action">
                                    <button class="button button-small acwc_prompt_action-edit" data-id="<?php echo esc_html($acwc_item['id'])?>">Edit</button>
                                    <button class="button button-small acwc_prompt_action-delete" data-id="<?php echo esc_html($acwc_item['id'])?>">Delete</button>
                                </div>
                            <?php
                            endif;
                            ?>
                        </div>
                    <?php
                    endforeach;
                endif;
                ?>
            </div>
            <div class="acwc_paginate"></div>
        </div>
    
</div>
<div class="acwc_prompt_model_content" style="display: none">
    <form method="post" action="">
	<div class="acwc_model_settings_title"><span class="model_settings">Change Option Settings</span><span class="acwc_prompt_shortcode"></span></div>	
		<div class="acwc_block_1 acwc_model_settings_container">
			<div class="mb_5 acwc_model_setting_control acwc_prompt_engine">
				<label>Engine: </label>
				<select name="engine">
					<option value="text-davinci-003" <?php echo ( esc_attr(get_option("acwc_set_ai_model","en")) == 'text-davinci-003' ? 'selected' : '' ) ;?>>text-davinci-003</option>
					<option value="gpt-3.5-turbo" <?php echo ( esc_attr(get_option("acwc_set_ai_model","en")) == 'gpt-3.5-turbo' ? 'selected' : '' ) ;?>>gpt-3.5-turbo</option>
					<option value="text-curie-001" <?php echo ( esc_attr(get_option("acwc_set_ai_model","en")) == 'text-curie-001' ? 'selected' : '' ) ;?>>text-curie-001</option>
					<option value="text-babbage-001" <?php echo ( esc_attr(get_option("acwc_set_ai_model","en")) == 'text-babbage-001' ? 'selected' : '' ) ;?>>text-babbage-001</option>
				</select>
			</div>
			<div class="mb_5 acwc_model_setting_control acwc_prompt_max_tokens"><label>Max Tokens: </label><input name="max_tokens" type="text" min="1" max="2048" value="<?php echo get_option('acwc_max_token');?>"></div>
			<div class="mb_5 acwc_model_setting_control acwc_prompt_temperature"><label>Temperature: </label><input name="temperature" type="text" min="0" max="1" step="any" value="<?php echo get_option('acwc_temperature');?>"></div>
			<div class="mb_5 acwc_model_setting_control acwc_prompt_top_p"><label>Top P: </label><input name="top_p" type="text" min="0" max="1" value="<?php echo get_option('acwc_top_p');?>"></div>
			<div class="mb_5 acwc_model_setting_control acwc_prompt_best_of"><label>Best Of: </label><input name="best_of" type="text" min="1" max="20" value="<?php echo get_option('acwc_best_of');?>"></div>
			<div class="mb_5 acwc_model_setting_control acwc_prompt_frequency_penalty"><label>Frequency Penalty: </label><input name="frequency_penalty" type="text" min="0" max="2" step="any" value="<?php echo get_option('acwc_frequency_penalty');?>"></div>
			<div class="mb_5 acwc_model_setting_control acwc_prompt_presence_penalty"><label>Presence Penalty: </label><input name="presence_penalty" type="text" min="0" max="2" step="any" value="<?php echo get_option('acwc_presence_penalty');?>"></div>
			<div class="mb_5 acwc_model_setting_control acwc_prompt_stop"><label>Stop:<small>separate by commas</small></label><input name="stop" type="text"></div>
			<div class="mb_5 acwc_model_setting_control acwc_prompt_estimated"><label>Estimated: </label><span></span></div>
			<div class="mb_5 acwc_model_setting_control acwc_prompt_post_title"><input type="hidden" name="post_title" id="pstTitle" value="<?php echo esc_html($acwc_item['title'])?>"></div>
			<div class="clear"></div>
			
                
        </div>
        <div class="">
            <div class="acwc_block_2">
                <input type="hidden" class="acwc_prompt_response_type" value="textarea">
                <h3>Please Enter Your Text</h3>
                <div class="mb_10">
                    <textarea name="title" class="acwc_prompt_title" rows="3"></textarea>
                    <button class="button  acwc_prompt_button ">Generate</button>
                    &nbsp;<button type="button" class="button  acwc_prompt_stop-generate " style="display: none">Stop</button>
                </div>
                <div class="mb_5">
                    <div class="acwc_prompt_response_editor">
                        <textarea class="acwc_prompt_result" rows="12"></textarea>
                    </div>
                    <div class="acwc_prompt_response_element"></div>
                </div>
                <div class="acwc_prompt_save-result" style="display: none">
                    <button type="button" class="button  acwc_prompt_save-draft ">Save Draft</button>
                    <button type="button" class="button acwc_prompt_clear ">Clear</button>
                </div>
            </div>
            
        </div>
    </form>
</div>
<script>
    jQuery(document).ready(function ($){
        let prompt_id;
        let prompt_name;
        let prompt_response = '';
        let acwc_limited_token = false;
        let wp_nonce = '<?php echo esc_html(wp_create_nonce( 'acwc-promptbase' ))?>'
        $(document).on('click','.acwc_modal_tabs li', function (e){
            var tab = $(e.currentTarget);
            var target =  tab.attr('data-target');
            var modal = tab.closest('.acwc_model_content');
            modal.find('.acwc_modal_tabs li').removeClass('acwc_tab_active');
            tab.addClass('acwc_tab_active');
            modal.find('.acwc_modal_tab').hide();
            modal.find('.acwc_modal_tab-'+target).show();
        })
        var acwcPromptContent = $('.acwc_create_prompt-content');
        $(document).on('click','.acwc_prompt_icons span', function (e){
            var icon = $(e.currentTarget);
            icon.parent().find('span').removeClass('icon_selected');
            icon.addClass('icon_selected');
            icon.parent().parent().find('.acwc_create_prompt-icon').val(icon.attr('data-key'));
        });
        $('.acwc_create_prompt').click(function (){
            $('.acwc_model_content').empty();
            $('.acwc_model_title').html('Add Your Custom Prompt');
            $('.acwc_model_content').html('<form action="" method="post" class="acwc_create_prompt-form">'+acwcPromptContent.html()+'</form>');
            $('.acwc_create_prompt-form .acwc_create_prompt-color').wpColorPicker();
            $('.acwc_create_prompt-form .acwc_create_prompt-bgcolor').wpColorPicker();
            $('.acwc_create_prompt-form .acwc_create_prompt_category').val('generation');
            $('.acwc_create_prompt-form .acwc_prompt_icons span[data-key=robot]').addClass('icon_selected');
            $('.acwc_model').css('height','auto');
            $('.acwc_out_overlay').show();
            $('.acwc_model').show();
        })
        $(document).on('click','.acwc_prompt_item .acwc_prompt_action-delete',function (e){
            var id = $(e.currentTarget).attr('data-id');
            var conf = confirm('Are you sure?');
            if(conf){
                $('.acwc_prompt_item-custom-'+id).remove();
                $.post('<?php echo admin_url('admin-ajax.php')?>', {action: 'acwc_prompt_delete', id: id,'nonce': '<?php echo wp_create_nonce('acwc-ajax-nonce')?>'});
				setTimeout(function () { location.reload(); }, 500);
            }
        });
        $(document).on('click','.acwc_prompt_item .acwc_prompt_action-edit',function (e){
            var id = $(e.currentTarget).attr('data-id');
            var item = $('.acwc_prompt_item-custom-'+id);
            $('.acwc_model_content').empty();
            $('.acwc_model_title').html('Edit Your Custom Prompt');
            $('.acwc_model_content').html('<form action="" method="post" class="acwc_create_prompt-form">'+acwcPromptContent.html()+'</form>');
            var form = $('.acwc_create_prompt-form');
            var acwc_prompt_keys = ['engine','editor','title','description','max_tokens','temperature','top_p','best_of','frequency_penalty','presence_penalty','stop','prompt','response','category','bgcolor','header','ddraft','dclear','dnotice','generate_text','draft_text','clear_text','stop_text','cnotice_text'];
            for(var i = 0; i < acwc_prompt_keys.length;i++){
                var acwc_prompt_key = acwc_prompt_keys[i];
                var acwc_prompt_key_value = item.attr('data-'+acwc_prompt_key);
                form.find('.acwc_create_prompt-'+acwc_prompt_key).val(acwc_prompt_key_value);
                if(acwc_prompt_key === 'icon'){
                    $('.acwc_create_prompt-form .acwc_prompt_icons span[data-key='+acwc_prompt_key_value+']').addClass('icon_selected');
                }
            }
            form.find('.acwc_create_prompt-id').val(id);
            $('.acwc_create_prompt-form .acwc_create_prompt-color').wpColorPicker();
            $('.acwc_create_prompt-form .acwc_create_prompt-bgcolor').wpColorPicker();
            $('.acwc_out_overlay').show();
            $('.acwc_model').css('height','auto');
            $('.acwc_model').show();
        });
       
        $(document).on('submit','.acwc_create_prompt-form', function(e){
            var form = $(e.currentTarget);
            var btn = form.find('.acwc_create_prompt-save');
            var max_tokens = form.find('.acwc_create_prompt-make-tokens').val();
            var temperature = form.find('.acwc_create_prompt-temperature').val();
            var top_p = form.find('.acwc_create_prompt-top_p').val();
            var best_of = form.find('.acwc_create_prompt-best_of').val();
            var frequency_penalty = form.find('.acwc_create_prompt-frequency_penalty').val();
            var presence_penalty = form.find('.acwc_create_prompt-presence_penalty').val();
            var error_message = false;
            var data = form.serialize();
            if(max_tokens !== '' && (parseFloat(max_tokens) < 1 || parseFloat(max_tokens) > 8000)){
                error_message = 'Please enter a valid max tokens value between 1 and 8000';
            }
            else if(temperature !== '' && (parseFloat(temperature) < 0 || parseFloat(temperature) > 1)){
                error_message = 'Please enter a valid temperature value between 0 and 1';
            }
            else if(top_p !== '' && (parseFloat(top_p) < 0 || parseFloat(top_p) > 1)){
                error_message = 'Please enter a valid Top P value between 0 and 1';
            }
            else if(best_of !== '' && (parseFloat(best_of) < 1 || parseFloat(best_of) > 20)){
                error_message = 'Please enter a valid best of value between 0 and 1';
            }
            else if(frequency_penalty !== '' && (parseFloat(frequency_penalty) < 0 || parseFloat(frequency_penalty) > 2)){
                error_message = 'Please enter a valid frequency penalty value between 0 and 2';
            }
            else if(presence_penalty !== '' && (parseFloat(presence_penalty) < 0 || parseFloat(presence_penalty) > 2)){
                error_message = 'Please enter a valid presence penalty value between 0 and 2';
            }
            if(error_message){
                alert(error_message);
            }
            else{
                $.ajax({
                    url: '<?php echo admin_url('admin-ajax.php')?>',
                    data: data,
                    dataType: 'JSON',
                    type: 'POST',
                    beforeSend: function (){
                        acwcLoading(btn)
                    },
                    success: function (res){
                        acwcRmLoading(btn);
                        if(res.status === 'success'){
                            window.location.href = '<?php echo admin_url('admin.php?page=acwc_promptbase&update_prompt=')?>'+res.id;
                        }
                        else{
                            alert(res.msg)
                        }
                    },
                    error: function (){
                        acwcRmLoading(btn);
                        alert('Something went wrong');
                    }
                })
            }
            return false;
        })
        
        var acwcNumberParse = 3;
        if($(window).width() < 900){
            acwcNumberParse = 2;
        }
        if($(window).width() < 480){
            acwcNumberParse = 1;
        }
        var acwc_per_page = <?php echo esc_html($acwc_per_page);?>;
        var acwc_count_prompts = <?php echo esc_html(count($acwc_items))?>;
        $('.acwc_list input').on('change',function (){
            acwcPromptsFilter();
        });
        var acwcPromptItem = $('.acwc_prompt_item');
        var acwcPromptSearch = $('.acwc_prompt_search');
        var acwcPromptItems = $('.acwc_prompt_items');
        var acwcPromptSettings = ['engine','max_tokens','temperature','top_p','best_of','frequency_penalty','presence_penalty','stop','post_title','generate_text','noanswer_text','draft_text','clear_text','stop_text','cnotice_text'];
        var acwcPromptDefaultContent = $('.acwc_prompt_model_content');
        var acwcPromptEditor = false;
        var eventGenerator = false;
        acwcPromptSearch.on('input', function (){
            acwcPromptsFilter();
        });
        function acwcPromptsFilter(){
            var categories = [];
            var users = [];
            var filterClasses = [];
            $('.acwc_categories input').each(function (idx, item){
                if($(item).prop('checked')){
                    categories.push($(item).val());
                    filterClasses.push($(item).val());
                }
            });
            $('.acwc_authors input').each(function (idx, item){
                if($(item).prop('checked')){
                    users.push('user-'+$(item).val());
                    filterClasses.push('user-'+$(item).val());
                }
            });
            var search = acwcPromptSearch.val();
            acwcPromptItem.each(function (idx, item){
                var item_title = $(item).attr('data-title');
                var item_desc = $(item).attr('data-desc');
                var show = false;
                if(categories.length){
                    for(var i=0;i<categories.length;i++){
                        if($(item).hasClass(categories[i])){
                            show = true;
                            break;
                        }
                        else{
                            show = false;
                        }
                    }
                    if(show && users.length){
                        for(var i=0;i<users.length;i++){
                            if($(item).hasClass(users[i])){
                                show = true;
                                break;
                            }
                            else{
                                show = false;
                            }
                        }
                    }
                }
                if(users.length){
                    for(var i=0;i<users.length;i++){
                        if($(item).hasClass(users[i])){
                            show = true;
                            break;
                        }
                        else{
                            show = false;
                        }
                    }
                    if(show && categories.length){
                        for(var i=0;i<categories.length;i++){
                            if($(item).hasClass(categories[i])){
                                show = true;
                                break;
                            }
                            else{
                                show = false;
                            }
                        }
                    }
                }
                if(!users.length && !categories.length){
                    show = true;
                }
                if(search !== '' && show){
                    search = search.toLowerCase();
                    item_title = item_title.toLowerCase();
                    item_desc = item_desc.toLowerCase();
                    if(item_title.indexOf(search) === -1 && item_desc.indexOf(search) === -1){
                        show = false;
                    }
                }
                if(show){
                    $(item).show();
                }
                else{
                    $(item).hide();
                }
            });
            acwcPromptPagination();
        }
        acwcPromptPagination();
        function acwcPromptPagination(){
            acwcPromptItem.removeClass('acwc_disappear_item');
            var number_rows = 0 ;
            acwcPromptItem.each(function (idx, item){
                if($(item).is(':visible')){
                    number_rows++;
                }
            });
            $('.acwc_paginate').empty();
            if(number_rows > acwc_per_page){
                var  totalPage = Math.ceil(number_rows/acwc_per_page);
                for(var i=1;i <=totalPage;i++){
                    var classSpan = 'page-numbers';
                    if(i === 1){
                        classSpan = 'page-numbers current';
                    }
                    $('.acwc_paginate').append('<span class="'+classSpan+'" data-page="'+i+'">'+i+'</span>');
                }
            }
            var rowDisplay = 0;
            acwcPromptItem.each(function (idx, item){
                if($(item).is(':visible')){
                    rowDisplay += 1;
                }
            });
            if(rowDisplay > acwc_per_page) {
                acwcPromptItems.css('height', ((Math.ceil(acwc_per_page/acwcNumberParse) * 120) - 20) + 'px');
            }
            else{
                acwcPromptItems.css('height', ((Math.ceil(rowDisplay/acwcNumberParse) * 120) - 20) + 'px');
            }
        }

        $(document).on('click','.acwc_paginate span:not(.current)', function (e){
            var btn = $(e.currentTarget);
            var page = parseInt(btn.attr('data-page'));
            $('.acwc_paginate span').removeClass('current');
            btn.addClass('current');
            var prevpage = page-1;
            var startRow = prevpage*acwc_per_page;
            var endRow = startRow+acwc_per_page;
            var keyRow = 0;
            var rowDisplay = 0;
            acwcPromptItem.each(function (idx, item){
                if($(item).is(':visible')){
                    keyRow += 1;
                    if(keyRow > startRow && keyRow <= endRow){
                        rowDisplay += 1;
                        $(item).removeClass('acwc_disappear_item');
                    }
                    else{
                        $(item).addClass('acwc_disappear_item');
                    }
                }
            });
            acwcPromptItems.css('height',((Math.ceil(rowDisplay/acwcNumberParse)*120)- 20)+'px');
        });
        $('.acwc_model_close').click(function (){
            $('.acwc_model_close').closest('.acwc_model').hide();
            $('.acwc_model_close').closest('.acwc_model').removeClass('acwc_small_modell');
            $('.acwc_out_overlay').hide();
            if(eventGenerator){
                eventGenerator.close();
            }
        });
        var acwcEditorNumber;
        $(document).on('click','.acwc_prompt_form .acwc_prompt_save-draft', function(e){
            var basicEditor = true;
            var btn = $(e.currentTarget);
            var response_type = $('.acwc_prompt_form .acwc_prompt_response_type').val();
            var post_content = '';
            if(response_type === 'textarea') {
                var editor = tinyMCE.get('editor-' + acwcEditorNumber);
                if ($('#wp-editor-' + acwcEditorNumber + '-wrap').hasClass('tmce-active') && editor) {
                    basicEditor = false;
                }
                if (basicEditor) {
                    post_content = $('#editor-' + acwcEditorNumber).val();
                } else {
                    post_content = editor.getContent();
                }
            }
            else{
                post_content = $('.acwc_prompt_response_element').html();
            }
            var post_title = $('.acwc_prompt_form .acwc_prompt_post_title input').val();
            if(post_content !== ''){
                $.ajax({
                    url: '<?php echo admin_url('admin-ajax.php')?>',
                    data: {title: post_title, content: post_content, action: 'acwc_save_draft_post_extra',save_source: 'promptbase','nonce': '<?php echo wp_create_nonce('acwc-ajax-nonce')?>'},
                    dataType: 'json',
                    type: 'POST',
                    beforeSend: function (){
                        acwcLoading(btn);
                    },
                    success: function (res){
                        acwcRmLoading(btn);
                        if(res.status === 'success'){
                            window.location.href = '<?php echo admin_url('post.php')?>?post='+res.id+'&action=edit';
                        }
                        else{
                            alert(res.msg);
                        }
                    },
                    error: function (){
                        acwcRmLoading(btn);
                        alert('Something went wrong');
                    }
                });
            }
            else{
                alert('Please wait content generated');
            }

        });
        $(document).on('click','.acwc_prompt_form .acwc_prompt_clear', function(e){
            var basicEditor = true;
            var response_type = $('.acwc_prompt_form .acwc_prompt_response_type').val();
            if(response_type === 'textarea') {
                var editor = tinyMCE.get('editor-' + acwcEditorNumber);
                if ($('#wp-editor-' + acwcEditorNumber + '-wrap').hasClass('tmce-active') && editor) {
                    basicEditor = false;
                }
                if (basicEditor) {
                    $('#editor-' + acwcEditorNumber).val('');
                } else {
                    editor.setContent('');
                }
            }
            else{
                $('.acwc_prompt_response_element').empty();
            }
        });
        $(document).on('input','.acwc_prompt_form .acwc_prompt_max_tokens input', function(e){
            var maxtokens = $(e.currentTarget).val();
            var acwc_estimated_cost = maxtokens !== '' ? parseFloat(maxtokens)*0.02/1000 : 0;
            acwc_estimated_cost = '$'+parseFloat(acwc_estimated_cost.toFixed(5));
            $('.acwc_prompt_form .acwc_prompt_estimated span').html(acwc_estimated_cost);
        });
        $(document).on('click','.acwc_prompt_item .acwc_prompt_content,.acwc_prompt_item .acwc_prompt_icon',function (e){
            var item = $(e.currentTarget).parent();
            var title = item.attr('data-title');

            $("#pstTitle").val(title);
            var id = item.attr('data-id');
            var type = item.attr('data-type');
            var response_type = item.attr('data-editor');
            prompt_name = title;
            prompt_id = id;
            $('.acwc_prompt_response_type').val(response_type);
            var categories = item.attr('data-categories');
            $('.acwc_model_content').empty();
           
            var modal_head = '<div class="d_flex align_items_center acwc_model_prompt_head"><div style="margin-left: 10px;">';
            modal_head += '<strong>'+title+'</strong>';
            if(categories !== ''){
                modal_head += '<div><small>'+categories+'</small></div>';
            }
            modal_head += '</div></div>';
            $('.acwc_model_title').html(modal_head);
            $('.acwc_model_prompt_head').prepend(item.find('.acwc_prompt_icon').clone());
            var prompt = item.attr('data-prompt');
            if(type === 'custom'){
                prompt += ".\n\n";
            }
            var response = item.attr('data-response');
            acwcEditorNumber = Math.ceil(Math.random()*1000000);
            $('.acwc_model_content').html('<div class="acwc_prompt_form">'+acwcPromptDefaultContent.html()+'</div>');
            $('.acwc_prompt_form').find('.acwc_prompt_title').val(prompt);
            acwcPromptEditor = $('.acwc_prompt_form').find('.acwc_prompt_result');
            if(id !== undefined){
                var embed_message = 'Shortcode: [acwc_prompt id='+id+' settings=no';
                if(type === 'custom'){
                    embed_message += ' custom=yes';
                }
                embed_message += ']';
                $('.acwc_prompt_form .acwc_prompt_shortcode').html(embed_message);
            }
            for(var i = 0; i < acwcPromptSettings.length; i++){
                var item_name = acwcPromptSettings[i];
                var item_value = item.attr('data-'+item_name);
                if(item_name === 'max_tokens'){
                    var acwc_estimated_cost = item_value !== undefined ? parseFloat(item_value)*0.02/1000 : 0;
                    acwc_estimated_cost = '$'+parseFloat(acwc_estimated_cost.toFixed(5));
                    $('.acwc_prompt_form .acwc_prompt_estimated span').html(acwc_estimated_cost);
                }
                if(item_value !== undefined){
                    if(
                        item_name === 'generate_text'
                        || item_name === 'draft_text'
                        || item_name === 'noanswer_text'
                        || item_name === 'clear_text'
                        || item_name === 'stop_text'
                    ){
                        $('.acwc_prompt_text-'+item_name).html(item_value);
                    }
                    else{
                        if(item_name !== 'engine' && item_name !== 'stop' && item_name !== 'post_title'){
                            item_value = parseFloat(item_value);
                            item_value = item_value.toString().replace(/,/g, '.');
                        }

                        $('.acwc_prompt_form .acwc_prompt_'+item_name).show();
                    }
                }
                else{
                    $('.acwc_prompt_form .acwc_prompt_'+item_name).hide();
                }
            }
            $('.acwc_prompt_form .acwc_prompt_response').html(response);
            acwcPromptEditor.attr('id','editor-'+acwcEditorNumber);
            if(response_type === 'textarea') {
                wp.editor.initialize('editor-' + acwcEditorNumber, {
                    tinymce: {
                        wpautop: false,
                        plugins: 'charmap colorpicker hr lists paste tabfocus textcolor fullscreen wordpress wpautoresize wpeditimage wpemoji wpgallery wplink wptextpattern',
                        toolbar1: 'formatselect,bold,italic,bullist,numlist,blockquote,alignleft,aligncenter,alignright,link,wp_more,spellchecker,fullscreen,wp_adv,listbuttons',
                        toolbar2: 'styleselect,strikethrough,hr,forecolor,pastetext,removeformat,charmap,outdent,indent,undo,redo,wp_help',
                        height: 300
                    },
                    quicktags: {buttons: 'strong,em,link,block,del,ins,img,ul,ol,li,code,more,close'},
                    mediaButtons: true
                });
            }
            else{
                $('.acwc_template_form .acwc_prompt_response_editor').hide();
            }
            $('.acwc_model').css('top','');
            $('.acwc_model').css('height','');
            $('.acwc_out_overlay').show();
            $('.acwc_model').show();
        });
        function acwcLoading(btn){
            btn.attr('disabled','disabled');
            if(!btn.find('spinner').length){
                btn.append('<span class="spinner"></span>');
            }
            btn.find('.spinner').css('visibility','unset');
        }
        function acwcRmLoading(btn){
            btn.removeAttr('disabled');
            btn.find('.spinner').remove();
        }
        function stopOpenAIGenerator(){
            $('.acwc_prompt_form .acwc_prompt_stop-generate').hide();
            if(!acwc_limited_token){
                $('.acwc_prompt_form .acwc_prompt_save-result').show();
            }
            acwcRmLoading($('.acwc_prompt_form .acwc_prompt_button'));
            eventGenerator.close();
        }
        $(document).on('click','.acwc_prompt_form .acwc_prompt_stop-generate', function (e){
            stopOpenAIGenerator();
        });
        $(document).on('submit','.acwc_prompt_form form', function (e){
            var form = $(e.currentTarget);
            var btn = form.find('.acwc_prompt_button');
            var prompt_title = form.find('.acwc_prompt_title').val();
            var response_type = form.find('.acwc_prompt_response_type').val();
            if(prompt_title !== '') {
                var max_tokens = form.find('.acwc_prompt_max_tokens input').val();
                var temperature = form.find('.acwc_prompt_temperature input').val();
                var top_p = form.find('.acwc_prompt_top_p input').val();
                var best_of = form.find('.acwc_prompt_best_of input').val();
                var frequency_penalty = form.find('.acwc_prompt_frequency_penalty input').val();
                var presence_penalty = form.find('.acwc_prompt_presence_penalty input').val();

                var checkKey = "<?php echo get_option('acwc_api_key'); ?>";
                var error_message = false;
                if(max_tokens === ''){
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
                else if(typeof(checkKey) == 'undefined' || checkKey == '')
                {
                    error_message = 'Please enter your api key';   
                }

                if(error_message){
                    alert(error_message);
                }
                else {
                    let startTime = new Date();
                    var data = form.serialize();
                    var basicEditor = true;
                    prompt_response = '';
                    if(response_type === 'textarea') {
                        var editor = tinyMCE.get('editor-' + acwcEditorNumber);
                        if ($('#wp-editor-' + acwcEditorNumber + '-wrap').hasClass('tmce-active') && editor) {
                            basicEditor = false;
                        }
                        if (basicEditor) {
                            $('#editor-' + acwcEditorNumber).val('');
                        } else {
                            editor.setContent('');
                        }
                    }
                    else{
                        $('.acwc_prompt_response_element').empty();
                    }
                    acwcLoading(btn);
                    form.find('.acwc_prompt_stop-generate').show();
                    form.find('.acwc_prompt_save-result').hide();
                    var acwc_limitLines = 1;
                    var count_line = 0;
                    var currentContent = '';
					
                    data += '&source_stream=promptbase&nonce=<?php echo wp_create_nonce('acwc-ajax-nonce')?>';
                    eventGenerator = new EventSource('<?php echo esc_html(add_query_arg('acwc_stream','yes',site_url().'/index.php'));?>&' + data);
				    var acwc_response_events = 0;
                    var acwc_newline_before = false;
                    acwc_limited_token = false;
				    eventGenerator.onmessage = function (e) {
				
                        if(response_type === 'textarea') {
                            if (basicEditor) {
                                currentContent = $('#editor-' + acwcEditorNumber).val();
                            } else {
                                currentContent = editor.getContent();
                                currentContent = currentContent.replace(/<\/?p(>|$)/g, "");
                            }
                        }
                        else{
                            currentContent = $('.acwc_prompt_response_element').html();
                        }
						
                        if (e.data === "[DONE]") {
                            count_line += 1;
                            if(response_type === 'textarea') {
                                if (basicEditor) {
                                    $('#editor-' + acwcEditorNumber).val(currentContent + "<br /><br />");
                                } else {
                                    editor.setContent(currentContent + "<br /><br />");
                                }
                            }
                            else{
                                $('.acwc_prompt_response_element').append("<br>");
                            }
                            acwc_response_events = 0;
                        }
                        else if (e.data === "[LIMITED]") {
                            acwc_limited_token = true;
                            count_line += 1;
                            if(response_type === 'textarea') {
                                if (basicEditor) {
                                    $('#editor-' + acwcEditorNumber).val(currentContent + "<br /><br />");
                                } else {
                                    editor.setContent(currentContent + "<br /><br />");
                                }
                            }
                            else{
                                $('.acwc_prompt_response_element').append("<br>");
                            }
                            acwc_response_events = 0;
                        } else {
                            var result = JSON.parse(e.data);
                            if (result.error !== undefined) {
                                var content_generated = result.error.message;
                            } else {
                                var content_generated = result.choices[0].delta !== undefined ? (result.choices[0].delta.content !== undefined ? result.choices[0].delta.content : '') : result.choices[0].text;
                            }
                            prompt_response += content_generated;
                            if((content_generated === '\n' || content_generated === ' \n' || content_generated === '.\n' || content_generated === '\n\n' || content_generated === '.\n\n') && acwc_response_events > 0 && currentContent !== ''){
                                if(!acwc_newline_before) {
                                    acwc_newline_before = true;
                                    if (response_type === 'textarea') {
                                        if (basicEditor) {
                                            $('#editor-' + acwcEditorNumber).val(currentContent + "<br /><br />");
                                        } else {
                                            editor.setContent(currentContent + "<br /><br />");
                                        }
                                    } else {
                                        $('.acwc_prompt_response_element').append("<br/>");
                                    }
                                }
                            }
                            else if(content_generated === '\n' && acwc_response_events === 0  && currentContent === ''){

                            }
                            else {
                                acwc_newline_before = false;
                                acwc_response_events += 1;
                                if(response_type === 'textarea') {
                                    if (basicEditor) {
                                        $('#editor-' + acwcEditorNumber).val(currentContent + content_generated);
                                    } else {
                                        editor.setContent(currentContent + content_generated);
                                    }
                                }
                                else{
                                    $('.acwc_prompt_response_element').append(content_generated);
                                }
                            }
                        }
                        if (count_line === acwc_limitLines) {
                            $('.acwc_prompt_form .acwc_prompt_stop-generate').hide();
                            if(!acwc_limited_token) {
                                let endTime = new Date();
                                let timeDiff = endTime - startTime;
                                timeDiff = timeDiff / 1000;
                                data += '&action=acwc_prompt_log&prompt_id=' + prompt_id + '&prompt_name=' + prompt_name + '&prompt_response=' + prompt_response + '&duration=' + timeDiff + '&_wpnonce=' + wp_nonce;
                                $.ajax({
                                    url: '<?php echo admin_url('admin-ajax.php')?>',
                                    data: data,
                                    dataType: 'JSON',
                                    type: 'POST',
                                    success: function (res) {

                                    }
                                })
                            }
							$('.acwc_prompt_post_title input').val(prompt_title);
                            stopOpenAIGenerator();
                            acwcRmLoading(btn);
                        }
                    }
                }
            }
            else{
                alert('Please enter prompt');
            }
            return false;
        })
		$(document).on('click','.model_settings', function(){
			$('.acwc_model_settings_container').toggle(500);
		})
    })
</script>
