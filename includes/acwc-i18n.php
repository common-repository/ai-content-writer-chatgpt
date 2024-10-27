<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class Ai_Content_Generator_Chatgpt_i18n {


	public function load_plugin_textdomain() {

		load_plugin_textdomain(
			'ai-content-generator-chatgpt',
			false,
			dirname( dirname( plugin_basename( __FILE__ ) ) ) . '/languages/'
		);

	}



}
