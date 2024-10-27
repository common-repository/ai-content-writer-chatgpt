<?php
namespace ACWC;
if ( ! defined( 'ABSPATH' ) ) exit;
if(!class_exists('\\ACWC\\ACWC_Editor')) {
    class ACWC_Editor
    {
        private static $instance = null;
        public $acwc_edit_default_menus = array(
            array('name' => 'Write a paragraph about this', 'prompt' => ' Write a paragraph about this: [text]'),
            array('name' => 'Summarize', 'prompt' => 'Summarize this: [text]'),
            array('name' => 'Expand', 'prompt' => 'Expand this: [text]'),
            array('name' => 'Rewrite', 'prompt' => 'Rewrite this: [text]'),
            array('name' => 'Generate ideas about this', 'prompt' => 'Generate ideas about this: [text]'),
            array('name' => 'Make a bulleted list', 'prompt' => 'Make a bulleted list: [text]'),
            array('name' => 'Paraphrase', 'prompt' => 'Paraphrase this: [text]'),
            array('name' => 'Generate a call to action', 'prompt' => 'Generate a call to action about this: [text]'),
            array('name' => 'Correct grammar', 'prompt' => 'Correct grammar in this: [text]'),
            array('name' => 'Generate a question', 'prompt' => 'Generate a question about this: [text]'),
            array('name' => 'Suggest a title', 'prompt' => 'Suggest a title for this: [text]'),
            array('name' => 'Convert to passive voice', 'prompt' => 'Convert this to passive voice: [text]'),
            array('name' => 'Convert to active voice', 'prompt' => 'Convert this to active voice: [text]'),
            array('name' => 'Write a conclusion', 'prompt' => 'Write a conclusion for this: [text]'),
            array('name' => 'Provide a counterargument', 'prompt' => 'Provide a counterargument for this: [text]'),
            array('name' => 'Generate a quote', 'prompt' => 'Generate a quote related to this: [text]'),
            array('name' => 'Translate to Spanish', 'prompt' => 'Translate this to Spanish: [text]')
        );

        public static function get_instance()
        {
            if (is_null(self::$instance)) {
                self::$instance = new self();
            }
            return self::$instance;
        }

        public function __construct()
        {
            add_action( 'enqueue_block_editor_assets', array($this,'acwc_block_editor'), 9 );
            add_action('admin_head',array($this,'acwc_ai_buttons'));
            add_action('wp_ajax_acwc_editor_prompt', array($this,'acwc_editor_prompt'));
        }

        public function acwc_block_editor()
        {
                $acwc_editor_button_menus = get_option('acwc_editor_button_menus', []);
                if (!is_array($acwc_editor_button_menus) || count($acwc_editor_button_menus) == 0) {
                    $acwc_editor_button_menus = $this->acwc_edit_default_menus;
                }
                wp_localize_script('acwc-gutenberg-custom-button', 'acwc_gutenberg_editor', array(
                    'plugin_url' => ACWC_PLUGIN_URL,
                    'editor_ajax_url' => admin_url('admin-ajax.php'),
                    'editor_menus' => $acwc_editor_button_menus,
                    'change_action' => get_option('acwc_editor_change_action', 'below')
                ));
        }

        public function acwc_ai_buttons()
        {
                ?>
                <script>
                    var acwc_editor_wp_nonce = '<?php echo wp_create_nonce('acwc-ajax-nonce')?>';
                </script>
                <?php
                if ( ! current_user_can( 'edit_posts' ) && ! current_user_can( 'edit_pages' ) ) {
                    return;
                }
                if ( 'true' == get_user_option( 'rich_editing' ) ) {
                    $acwc_editor_button_menus = get_option('acwc_editor_button_menus', []);
                    if(!is_array($acwc_editor_button_menus) || count($acwc_editor_button_menus) == 0){
                        $acwc_editor_button_menus = $this->acwc_edit_default_menus;
                    }
                    ?>
                    <script>
                        var acwc_plugin_url = '<?php echo esc_html(ACWC_PLUGIN_URL)?>';
                        var acwc_editor_ajax_url = '<?php echo esc_html(admin_url('admin-ajax.php'))?>';
                        var acwcTinymceEditorMenus = <?php echo _wp_specialchars(json_encode($acwc_editor_button_menus, JSON_UNESCAPED_UNICODE),ENT_NOQUOTES,'UTF-8',true)?>;
                        var acwcEditorChangeAction = '<?php echo get_option('acwc_editor_change_action','replace')?>';
                    </script>
                    <?php
                    add_filter('mce_external_plugins', array($this, 'acwc_add_buttons'));
                    add_filter('mce_buttons', array($this, 'acwc_register_buttons'));       
                }
        }

       

        public function acwc_add_buttons($plugins)
        {
            $plugins['acwceditor'] = ACWC_PLUGIN_URL . 'backend/js/acwc_tinymce.js';
            return $plugins;
        }

        public function acwc_register_buttons($buttons)
        {
            array_push( $buttons, 'acwceditor' );
            return $buttons;
        }

        public function acwc_editor_prompt()
        {
            $acwc_result = array('status' => 'error', 'msg' => 'Missing request parameters');
            if ( ! wp_verify_nonce( $_POST['nonce'], 'acwc-ajax-nonce' ) ) {
                $acwc_result['status'] = 'error';
                $acwc_result['msg'] = WP_OPENAI_CG_NONCE_ERROR;
                wp_send_json($acwc_result);
            }
            if(isset($_REQUEST['prompt']) && !empty($_REQUEST['prompt'])){
                $prompt = sanitize_text_field($_REQUEST['prompt']);
                $acwc = ACWCGPT::get_instance()->acwc();
                $acwc_generator = ACWC_Generator::get_instance();
                $acwc_generator->acwc($acwc);
                $result = $acwc_generator->acwc_request(array(
                    'model' => 'gpt-3.5-turbo',
                    'prompt' => $prompt,
                    'temperature' => 0.7,
                    'max_tokens' => 2000,
                    'frequency_penalty' => 0.01,
                    'presence_penalty' => 0.01
                ));
                if($result['status'] == 'error'){
                    $acwc_result['msg'] = $result['msg'];
                }
                else {
                    $acwc_result['status'] = 'success';
                    $acwc_result['data'] = str_replace("\n",'<br>',$result['data']);
                }
            }
            wp_send_json($acwc_result);
        }
    }
    ACWC_Editor::get_instance();
}
