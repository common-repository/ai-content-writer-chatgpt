<?php
if ( ! defined( 'ABSPATH' ) ) exit;
$acwc_custom_models = get_option('acwc_custom_models',[]);
$acwc_custom_models = array_merge(array('text-davinci-003','gpt-3.5-turbo','text-curie-001','text-babbage-001','text-ada-001','gpt-4','gpt-4-32k'),$acwc_custom_models);
$acwc = new \ACWC\ACWCGPT();
$acwc->acwc();
$acwc_parameters = array(
    'type' => 'topic',
    'post_type' => 'post',
    'model' => get_option('acwc_ai_model','gpt-3.5-turbo'),
    'temperature' => $acwc->temperature,
    'max_tokens' => 3000,
    'top_p' => $acwc->top_p,
    'best_of' => $acwc->best_of,
    'frequency_penalty' => $acwc->frequency_penalty,
    'presence_penalty' => $acwc->presence_penalty,
    'prompt_title' => 'Suggest [count] title for an article about [topic]',
    'prompt_section' => 'Write [count] consecutive headings for an article about [title]',
    'prompt_content' => 'Write a comprehensive article about [title], covering the following subtopics [sections]. Each subtopic should have at least [count] paragraphs. Use a cohesive structure to ensure smooth transitions between ideas. Include relevant statistics, examples, and quotes to support your arguments and engage the reader.',
    'prompt_meta' => 'Write a meta description about [title]. Max: 155 characters.',
    'prompt_excerpt' => 'Generate an excerpt for [title]. Max: 55 words.'
);
$acwc_all_templates = get_posts(array(
    'post_type' => 'acwc_mtemplate',
    'posts_per_page' => -1
));
$acwc_templates = array(array(
    'title' => 'Default',
    'content' => $acwc_parameters
));
foreach ($acwc_all_templates as $acwc_all_template){
    $acwc_template_content = is_serialized($acwc_all_template->post_content) ? unserialize($acwc_all_template->post_content) : array();
    $acwc_template_content = wp_parse_args($acwc_template_content,$acwc_parameters);
    $acwc_templates[$acwc_all_template->ID] = array(
        'title' => $acwc_all_template->post_title,
        'content' => $acwc_template_content
    );
}
$default_name = '';
if(isset($selected_template) && !empty($selected_template)){
    $acwc_parameters = $acwc_templates[$selected_template]['content'];
    $default_name = $acwc_templates[$selected_template]['title'];
}
?>
<h3>Settings</h3>
<div class="">
    <div class="acwc_form_field">
        <label><strong>Template:</strong></label>
        <select class="acwc_custom_template_select ">
            <?php
            foreach ($acwc_templates as $key=>$acwc_template){
                echo '<option'.(isset($selected_template) && $selected_template == $key ? ' selected':'').' class="acwc_custom_template_'.esc_html($key).'" data-parameters="'.esc_html(json_encode($acwc_template['content'], JSON_UNESCAPED_UNICODE)).'" value="'.esc_html($key).'">'.esc_html($acwc_template['title']).'</option>';
            }
            ?>
        </select>
    </div>
    <div class="acwc_form_field">
        <label><strong>Name:</strong></label>
        <input value="<?php echo esc_html($default_name)?>" type="text" class=" acwc_custom_template_title" name="title" placeholder="Enter a Template Name">
        <?php
        if(isset($selected_template) && !empty($selected_template)){
        ?>
            <input class="acwc_custom_template_id" type="hidden" name="id" value="<?php echo esc_html($selected_template)?>">
        <?php
        }
        ?>
    </div>
    <div class="acwc_form_field">
        <label><strong>Post Type:</strong></label>
        <select name="template[post_type]" class=" acwc_custom_template_post_type">
            <option value="post">Post</option>
            <option<?php echo isset($acwc_parameters['post_type']) && $acwc_parameters['post_type'] == 'page' ? ' selected' :''?> value="page">Page</option>
        </select>
    </div>
    <div class="acwc_form_field">
        <label><strong>Model:</strong></label>
        <select name="template[model]" class=" acwc_custom_template_model">
            <?php
            foreach($acwc_custom_models as $acwc_custom_model){
                echo '<option'.($acwc_custom_model == $acwc_parameters['model'] ? ' selected':'').' value="'.esc_html($acwc_custom_model).'">'.esc_html($acwc_custom_model).'</option>';
            }
            ?>
        </select>
    </div>
    <div id="gpt4-notice" class="acwc_form_field" style="display:none;">
        <p style="color: red;">Please note that GPT-4 is currently in limited beta, which means that access to the GPT-4 API from OpenAI is available only through a waiting list and is not open to everyone yet. You can sign up for the waiting list at <a href="https://openai.com/waitlist/gpt-4-api" target="_blank">here</a>.</p>
    </div>
    <?php
    foreach(array('temperature','max_tokens','top_p','best_of','frequency_penalty','presence_penalty') as $item){
        ?>
        <div class="acwc_form_field">
            <label><strong><?php echo esc_html(ucwords(str_replace('_',' ',$item))) ?>:</strong></label>
            <input type="text" value="<?php echo esc_html($acwc_parameters[$item])?>" class="acwc_custom_template_<?php echo esc_html($item)?>" name="template[<?php echo esc_html($item)?>]" style="width: 80px">
        </div>
        <?php
    }
    ?>
    <div class="mb_10">
        <label class="mb-5" style="display: block"><strong>Prompt for Title:</strong></label>
        <textarea class="acwc_custom_template_prompt_title" name="template[prompt_title]" rows="2"><?php echo esc_html($acwc_parameters['prompt_title'])?></textarea>
        <p style="margin-top: 0;font-size: 13px;font-style: italic;">Ensure <code>[count]</code> and <code>[topic]</code> is included in your prompt.</code></p>
    </div>
    <div class="mb_10">
        <label class="mb-5" style="display: block"><strong>Prompt for Sections:</strong></label>
        <textarea class="acwc_custom_template_prompt_section" name="template[prompt_section]" rows="2"><?php echo esc_html($acwc_parameters['prompt_section'])?></textarea>
        <p style="margin-top: 0;font-size: 13px;font-style: italic;">Ensure <code>[count]</code> and <code>[title]</code> is included in your prompt.</code></p>
    </div>
    <div class="mb_10">
        <label class="mb-5" style="display: block"><strong>Prompt for Content:</strong></label>
        <textarea class="acwc_custom_template_prompt_content" name="template[prompt_content]" rows="5"><?php echo esc_html($acwc_parameters['prompt_content'])?></textarea>
        <p style="margin-top: 0;font-size: 13px;font-style: italic;">Ensure <code>[title]</code>, <code>[sections]</code> and <code>[count]</code> is included in your prompt.</code></p>
    </div>
    <div class="mb_10">
        <label class="mb-5" style="display: block"><strong>Prompt for Excerpt:</strong></label>
        <textarea class="acwc_custom_template_prompt_excerpt" name="template[prompt_excerpt]" rows="2"><?php echo esc_html($acwc_parameters['prompt_excerpt'])?></textarea>
        <p style="margin-top: 0;font-size: 13px;font-style: italic;">Ensure <code>[title]</code> is included in your prompt.</code></p>
    </div>
    <div class="mb_10">
        <label class="mb-5" style="display: block"><strong>Prompt for Meta:</strong></label>
        <textarea class="acwc_custom_template_prompt_meta" name="template[prompt_meta]" rows="2"><?php echo esc_html($acwc_parameters['prompt_meta'])?></textarea>
        <p style="margin-top: 0;font-size: 13px;font-style: italic;">Ensure <code>[title]</code> is included in your prompt.</code></p>
    </div>
    <div style="display: flex;justify-content: space-between">
        <div>
            <button style="<?php echo isset($selected_template) ? '' : 'display:none'?>" type="button" class="button  acwc_template_update">Update</button>
            <button type="button" class="button  acwc_template_save">Save Template</button>
        </div>
        <button type="button" class="button button-link-delete acwc_template_delete" style="<?php echo isset($selected_template) ? '' : 'display:none'?>">Delete</button>
    </div>
</div>
<script>
    document.addEventListener('DOMContentLoaded', function () {
    const modelSelect = document.querySelector('.acwc_custom_template_model');
    const gpt4Notice = document.getElementById('gpt4-notice');

    modelSelect.addEventListener('change', function () {
        if (this.value === 'gpt-4' || this.value === 'gpt-4-32k') {
            gpt4Notice.style.display = 'block';
        } else {
            gpt4Notice.style.display = 'none';
        }
    });
});
</script>
