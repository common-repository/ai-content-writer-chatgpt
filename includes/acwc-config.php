<?php

define('ACWC_MODEL', 'text-davinci-003');


require 'acwc-class.php';

use ACWC\ACWC;

function acwc_scripts()
{
     wp_enqueue_script('ACWCJs',ACWC_DIR_URL.'assests/js/acwc.js');
     wp_enqueue_style('ACWCCss',ACWC_DIR_URL.'assests/css/acwc.css');
}

add_action('admin_init', 'acwc_scripts');
add_action('add_meta_boxes', 'acwc_add_metabox');

function acwc_menu()
{
	add_menu_page('OpenAI Content Generator', 'AI Content Writer - ChatGPT','manage_options' , 'acwc', 'acwc_main_page',ACWC_PLUGIN_URL.'assests/images/icon.png');
    add_submenu_page('acwc','Help','Help','manage_options','acwc_help','acwc_help',7);
}

function acwc_help()
{
    include ACWC_PLUGIN_DIR . 'backend/acwc_help.php';
}

function acwc_main_page() {

    require ACWC_DIR_PATH.'backend/acwc-page.php';
}
function acwc_get_api_key_text_converted(){
    $acwcGetApiKey = esc_attr(get_option('acwc_api_key'));
	if($acwcGetApiKey !=""){
		return 'sk-*****************************'.substr($acwcGetApiKey, -4);
	}else{
		return "";
	}
}

function acwc_add_metabox()
{
    add_meta_box(
            'acwc_ai_metabox', // Unique ID
            'OpenAi Content Generator', // Title
            'acwc_content_writer_metabox_content', // Callback function
            array('post', 'page'), // Screen (post, page, link, attachment, or custom post type)
            'side', // Context (normal, advanced, or side)
            'high' // Priority (high, core, default, or low)
        );
}

function acwc_content_writer_metabox_content()
{
    echo '<a id="acwc-btn" class="components-button acwc-btn" href="#">'."OpenAi Content Generator".'</a>';
}


add_action('admin_menu', 'acwc_menu');


add_action("wp_ajax_acwc_data_setting" , "acwc_data_setting");
function acwc_data_setting()
{
    $formData = isset($_POST['formData']) ? wp_filter_kses($_POST['formData']) : "";

    parse_str(str_replace('&amp;', '&', $formData), $formVal);

    $apiKey = isset($formVal['acwc_api_key']) ? sanitize_text_field($formVal['acwc_api_key']) : "";
    $maxToken = isset($formVal['max_token']) && !empty($formVal['max_token']) ? sanitize_text_field($formVal['max_token']) : "200";
    $temperature = isset($formVal['temperature']) && !empty($formVal['temperature']) ? sanitize_text_field($formVal['temperature']) : "0.3";
    $topP = isset($formVal['top_p']) && !empty($formVal['top_p']) ? sanitize_text_field($formVal['top_p']) : "0.9";
    $bestOf = isset($formVal['best_of']) && !empty($formVal['best_of']) ? sanitize_text_field($formVal['best_of']) : "1";
    $frequencyPenalty = isset($formVal['frequency_penalty']) && !empty($formVal['frequency_penalty']) ? sanitize_text_field($formVal['frequency_penalty']) : "0";

    $presencePenalty = isset($formVal['presence_penalty']) && !empty($formVal['presence_penalty']) ? sanitize_text_field($formVal['presence_penalty']) : "0";

    $setLanguage = isset($formVal['content_language']) && !empty($formVal['content_language']) ? sanitize_text_field($formVal['content_language']) : "en";
    $orgLanguage = isset($formVal['orgLang']) && !empty($formVal['orgLang']) ? sanitize_text_field($formVal['orgLang']) : "en";

    $writingStyle = isset($formVal['writing_style']) && !empty($formVal['writing_style']) ? sanitize_text_field($formVal['writing_style']) : "informative";

    $writingTone = isset($formVal['orgLang']) && !empty($formVal['orgLang']) ? sanitize_text_field($formVal['orgLang']) : "formal";

    $aiModel = isset($formVal['ai_model']) && !empty($formVal['ai_model']) ? sanitize_text_field($formVal['ai_model']) : "text-davinci-003";

    if(!empty($apiKey) && strpos($apiKey, '*') == false)
    {
        update_option('acwc_api_key', $apiKey);
    }

    update_option('acwc_max_token', $maxToken);
    update_option('acwc_temperature', $temperature);
    update_option('acwc_top_p', $topP);
    update_option('acwc_best_of', $bestOf);
    update_option('acwc_frequency_penalty', $frequencyPenalty);
    update_option('acwc_presence_penalty', $presencePenalty);
    update_option('acwc_set_language', $setLanguage);
    update_option('acwc_set_org_language', $orgLanguage);
    update_option('acwc_set_writing_style', $writingStyle);
    update_option('acwc_set_writing_tone', $writingTone);
    update_option('acwc_set_ai_model', $aiModel);


    echo 1;
    wp_die();
}
require 'acwc-footer.php';