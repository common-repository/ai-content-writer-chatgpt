<?php

/**
 * @wordpress-plugin
 * Plugin Name:       AI Content Writer - ChatGPT
 * Description:       AI Gpt Content Generator - Content Generator,Content Writer, ChatGPT, Title Suggestions,Auto Content Writer with just one click.
 * Version:           1.0.2
 * Author:            wisdomlogix
 * Author URI:        https://wisdomlogix.com/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       ai-content-writer-chatgpt
 */


use ACWC\ACWC;

define('ACWC_DIR_PATH', dirname(__FILE__) . '/');
define('ACWC_DIR_URL', plugin_dir_url(__FILE__));
define('ACWC_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define('ACWC_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define('ACWC_NONCE_ERROR','Invalid nonce. This means we are unable to verify the validity of the nonce. There are couple of possible reasons for this. 1. A cache plugin is caching the nonce. 2. The nonce has expired. 3. Invalid SSL certificate. 4. Network issue. Please check and try again.');

require 'includes/acwc-config.php';

$plugin = new ACWC;
$plugin->acwc_activator();

$getClass = new ACWC;
add_action("wp_ajax_acwc_generate_title" , array($getClass, 'acwc_generate_title'));

require plugin_dir_path( __FILE__ ) . 'includes/acwc-generator.php';

if ( !class_exists( '\\ACWC\\ACWCGPT' ) ) {
   require_once __DIR__ . '/includes/acwc-ai.php';
}

function run_ai_content_generator_chatgpt()
{
	$plugin = new Ai_Content_Generator_Chatgpt();
    $plugin->run();
}
    
run_ai_content_generator_chatgpt();

require_once __DIR__.'/classes/acwc_util.php';
require_once __DIR__.'/classes/acwc_content.php';
require_once __DIR__.'/classes/acwc_forms.php';
require_once __DIR__.'/classes/acwc_promptbase.php';
require_once __DIR__.'/classes/acwc_playground.php';
require_once __DIR__.'/classes/acwc_roles.php';
require_once __DIR__.'/classes/acwc_frontend.php';
require_once __DIR__.'/classes/acwc_regenerate_title.php';
require_once __DIR__.'/classes/acwc_hook.php';
require_once __DIR__.'/classes/acwc_search.php';
require_once __DIR__.'/classes/acwc_template.php';
require_once __DIR__.'/classes/acwc_editor.php';
require_once __DIR__.'/classes/acwc_generator.php';