<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class Ai_Content_Generator_Chatgpt {

	
	protected $loader;

	
	protected $plugin_name;

	
	protected $version;

	public function __construct() {
		if ( defined( 'AI_CONTENT_GENERATOR_CHATGPT_VERSION' ) ) {
			$this->version = AI_CONTENT_GENERATOR_CHATGPT_VERSION;
		} else {
			$this->version = '1.0.0';
		}
		$this->plugin_name = 'ai-content-generator-chatgpt';

		$this->load_dependencies();
		$this->set_locale();
		$this->define_admin_hooks();
		//$this->define_public_hooks();

	}

	private function load_dependencies() {

		
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/acwc-loader.php';

		
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/acwc-i18n.php';

		
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'backend/class-ai-content-generator-chatgpt-admin.php';


		$this->loader = new Ai_Content_Generator_Chatgpt_Loader();

	}

	private function set_locale() {

		$plugin_i18n = new Ai_Content_Generator_Chatgpt_i18n();

		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );

	}

	private function define_admin_hooks() {

		$plugin_admin = new Ai_Content_Generator_Chatgpt_Admin( $this->get_plugin_name(), $this->get_version() );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );
		$this->loader->add_action( 'wp_ajax_acwc_set_post_content_', $plugin_admin , 'acwc_set_post_content_' );
		$this->loader->add_action( 'admin_footer', $plugin_admin, 'acwc_load_db_vaule_js' );
	}

	private function define_public_hooks() {

		$plugin_public = new Ai_Content_Generator_Chatgpt_Public( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_styles' );
		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_scripts' ); 

	}

	public function run() {
		$this->loader->run();
	}

	public function get_plugin_name() {
		return $this->plugin_name;
	}

	public function get_loader() {
		return $this->loader;
	}

	public function get_version() {
		return $this->version;
	}

}
