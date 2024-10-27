<style>
.acwc_settings_contant_container {
    background: #fff;
    padding: 20px;
    margin-top: 50px;
	margin-right:20px;
	box-shadow: 1px 1px 5px #ccc;	
}
</style>
<div class="acwc_settings_contant_container">
	<form name="acwc_seting_form" class="acwc_seting_form" id="acwc_seting_form" method="post">
	
	<h3 class="acwc_opt_main_title">Open Ai Option Settings</h3>
	<div class="acwc_settings_contant_container">
		<div class="acwc_setting_control_container">
			<label class="acwc_opt_label">API Key</label>

			<?php $acwcGetApiKey = acwc_get_api_key_text_converted(); ?>

			<input type="text" name="acwc_api_key" id="acwcApiKey" class="acwc_opt_field" value="<?php echo esc_html($acwcGetApiKey); ?>" placeholder="Please Enter API Key">
			<p>Enter your API key to use the Open Ai. <a href="https://platform.openai.com/account/api-keys" target="_blank">Get the API key</a></p>
		</div>
		<div class="acwc_setting_control_container">
			<label class="acwc_opt_label">Max Tokens<span class="acwc_more_details"><i class="dashicons-before dashicons-info"></i></span></label>
			<input type="number" name="max_token" class="acwc_opt_field" max="4000" min="50" value="<?php echo (get_option('acwc_max_token') !="")?esc_html(get_option('acwc_max_token')):"2000";?>">			
			<div class="acwc_option_details_container">
			<p class="acwc_more_option_details">Max tokens for set maximum number of tokens to generate for output.Token is consider text,words,symbols.So if the set max token is 1000 then it will be output in 1000 tokens.</p>
			</div>
		</div>
		<div class="acwc_setting_control_container">
			<label class="acwc_opt_label">Temperature<span class="acwc_more_details"><i class="dashicons-before dashicons-info"></i></span></label>
			<?php $acwcTemperature = (get_option('acwc_temperature') !="")?get_option('acwc_temperature'):"0.7"; ?>	
			<select name="temperature" class="acwc_opt_field">			
			<option value="0" <?php echo esc_attr($acwcTemperature)=='0' ? 'selected': ''; ?>>0</option>
			<option value="0.1" <?php echo esc_attr($acwcTemperature)=='0.1' ? 'selected': ''; ?>>0.1</option>
			<option value="0.2" <?php echo esc_attr($acwcTemperature)=='0.2' ? 'selected': ''; ?>>0.2</option>
			<option value="0.3" <?php echo esc_attr($acwcTemperature)=='0.3' ? 'selected': ''; ?>>0.3</option>
			<option value="0.4" <?php echo esc_attr($acwcTemperature)=='0.4' ? 'selected': ''; ?>>0.4</option>
			<option value="0.5" <?php echo esc_attr($acwcTemperature)=='0.5' ? 'selected': ''; ?>>0.5</option>
			<option value="0.6" <?php echo esc_attr($acwcTemperature)=='0.6' ? 'selected': ''; ?>>0.6</option>
			<option value="0.7" <?php echo esc_attr($acwcTemperature)=='0.7' ? 'selected': ''; ?>>0.7</option>
			<option value="0.8" <?php echo esc_attr($acwcTemperature)=='0.8' ? 'selected': ''; ?>>0.8</option>
			<option value="0.9" <?php echo esc_attr($acwcTemperature)=='0.9' ? 'selected': ''; ?>>0.9</option>
			<option value="1" <?php echo esc_attr($acwcTemperature)=='1' ? 'selected': ''; ?>>1</option>
			</select>
			<div class="acwc_option_details_container">
			<p class="acwc_more_option_details">To Define randomness or creativity in the generated text define temperature.If set to 0 then the openAI model generates similar text.And if will increase then it will be more creative.</p>
			</div>
		</div>
		<div class="acwc_setting_control_container">
			<label class="acwc_opt_label">Top Prediction (Top-P)<span class="acwc_more_details"><i class="dashicons-before dashicons-info"></i></span></label>
			<?php $acwcTopP = (get_option('acwc_top_p') !="")?get_option('acwc_top_p'):"1"; ?>	
			<select name="top_p" class="acwc_opt_field">
			<option value="0" <?php echo esc_attr($acwcTopP)=='0' ? 'selected': ''; ?>>0</option>
			<option value="0.1" <?php echo esc_attr($acwcTopP)=='0.1' ? 'selected': ''; ?>>0.1</option>
			<option value="0.2" <?php echo esc_attr($acwcTopP)=='0.2' ? 'selected': ''; ?>>0.2</option>
			<option value="0.3" <?php echo esc_attr($acwcTopP)=='0.3' ? 'selected': ''; ?>>0.3</option>
			<option value="0.4" <?php echo esc_attr($acwcTopP)=='0.4' ? 'selected': ''; ?>>0.4</option>
			<option value="0.5" <?php echo esc_attr($acwcTopP)=='0.5' ? 'selected': ''; ?>>0.5</option>
			<option value="0.6" <?php echo esc_attr($acwcTopP)=='0.6' ? 'selected': ''; ?>>0.6</option>
			<option value="0.7" <?php echo esc_attr($acwcTopP)=='0.7' ? 'selected': ''; ?>>0.7</option>
			<option value="0.8" <?php echo esc_attr($acwcTopP)=='0.8' ? 'selected': ''; ?>>0.8</option>
			<option value="0.9" <?php echo esc_attr($acwcTopP)=='0.9' ? 'selected': ''; ?>>0.9</option>
			<option value="1" <?php echo esc_attr($acwcTopP)=='1' ? 'selected': ''; ?>>1</option>
			</select>
			<div class="acwc_option_details_container">
			<p class="acwc_more_option_details">To control probability of the generated text being more similar to the inserted text.The parameter controls randomness or creativity for the generated text. If set 0.1 means content text will be generated similar 10% of inserted text.</p>
			</div>
		</div>
		<div class="acwc_setting_control_container">
			<label class="acwc_opt_label">Best of<span class="acwc_more_details"><i class="dashicons-before dashicons-info"></i></span></label>
			<?php $acwcBestOf = (get_option('acwc_best_of') !="")?get_option('acwc_best_of'):"1"; ?>	
			<select name="best_of" class="acwc_opt_field">
			<option value="0" <?php echo esc_attr($acwcBestOf)=='0' ? 'selected': ''; ?>>0</option>
			<option value="0.1" <?php echo esc_attr($acwcBestOf)=='0.1' ? 'selected': ''; ?>>0.1</option>
			<option value="0.2" <?php echo esc_attr($acwcBestOf)=='0.2' ? 'selected': ''; ?>>0.2</option>
			<option value="0.3" <?php echo esc_attr($acwcBestOf)=='0.3' ? 'selected': ''; ?>>0.3</option>
			<option value="0.4" <?php echo esc_attr($acwcBestOf)=='0.4' ? 'selected': ''; ?>>0.4</option>
			<option value="0.5" <?php echo esc_attr($acwcBestOf)=='0.5' ? 'selected': ''; ?>>0.5</option>
			<option value="0.6" <?php echo esc_attr($acwcBestOf)=='0.6' ? 'selected': ''; ?>>0.6</option>
			<option value="0.7" <?php echo esc_attr($acwcBestOf)=='0.7' ? 'selected': ''; ?>>0.7</option>
			<option value="0.8" <?php echo esc_attr($acwcBestOf)=='0.8' ? 'selected': ''; ?>>0.8</option>
			<option value="0.9" <?php echo esc_attr($acwcBestOf)=='0.9' ? 'selected': ''; ?>>0.9</option>
			<option value="1" <?php echo esc_attr($acwcBestOf)=='1' ? 'selected': ''; ?>>1</option>
			</select>
			<div class="acwc_option_details_container">
			<p class="acwc_more_option_details">To generate multiple variations of the same text can be used this parameter.For the controls the outputs for single input and randomness or creativity.If set 4 then four outputs generates for single inserted text.</p>
			</div>
		</div>
		<div class="acwc_setting_control_container">
			<label class="acwc_opt_label">Frequency Penalty<span class="acwc_more_details"><i class="dashicons-before dashicons-info"></i></span></label>
			<?php $acwcFrequencyPenalty = (get_option('acwc_frequency_penalty') !="")?get_option('acwc_frequency_penalty'):"0"; ?>	
			<select name="frequency_penalty" class="acwc_opt_field">
			<option value="0"   <?php echo esc_attr($acwcFrequencyPenalty)=='0' ? 'selected': ''; ?>>0</option>
			<option value="0.1" <?php echo esc_attr($acwcFrequencyPenalty)=='0.1' ? 'selected': ''; ?>>0.1</option>
			<option value="0.2" <?php echo esc_attr($acwcFrequencyPenalty)=='0.2' ? 'selected': ''; ?>>0.2</option>
			<option value="0.3" <?php echo esc_attr($acwcFrequencyPenalty)=='0.3' ? 'selected': ''; ?>>0.3</option>
			<option value="0.4" <?php echo esc_attr($acwcFrequencyPenalty)=='0.4' ? 'selected': ''; ?>>0.4</option>
			<option value="0.5" <?php echo esc_attr($acwcFrequencyPenalty)=='0.5' ? 'selected': ''; ?>>0.5</option>
			<option value="0.6" <?php echo esc_attr($acwcFrequencyPenalty)=='0.6' ? 'selected': ''; ?>>0.6</option>
			<option value="0.7" <?php echo esc_attr($acwcFrequencyPenalty)=='0.7' ? 'selected': ''; ?>>0.7</option>
			<option value="0.8" <?php echo esc_attr($acwcFrequencyPenalty)=='0.8' ? 'selected': ''; ?>>0.8</option>
			<option value="0.9" <?php echo esc_attr($acwcFrequencyPenalty)=='0.9' ? 'selected': ''; ?>>0.9</option>
			<option value="1"   <?php echo esc_attr($acwcFrequencyPenalty)=='1' ? 'selected': ''; ?>>1</option>
			<option value="1.1" <?php echo esc_attr($acwcFrequencyPenalty)=='1.1' ? 'selected': ''; ?>>1.1</option>
			<option value="1.2" <?php echo esc_attr($acwcFrequencyPenalty)=='1.2' ? 'selected': ''; ?>>1.2</option>
			<option value="1.3" <?php echo esc_attr($acwcFrequencyPenalty)=='1.3' ? 'selected': ''; ?>>1.3</option>
			<option value="1.4" <?php echo esc_attr($acwcFrequencyPenalty)=='1.4' ? 'selected': ''; ?>>1.4</option>
			<option value="1.5" <?php echo esc_attr($acwcFrequencyPenalty)=='1.5' ? 'selected': ''; ?>>1.5</option>
			<option value="1.6" <?php echo esc_attr($acwcFrequencyPenalty)=='1.6' ? 'selected': ''; ?>>1.6</option>
			<option value="1.7" <?php echo esc_attr($acwcFrequencyPenalty)=='1.7' ? 'selected': ''; ?>>1.7</option>
			<option value="1.8" <?php echo esc_attr($acwcFrequencyPenalty)=='1.8' ? 'selected': ''; ?>>1.8</option>
			<option value="1.9" <?php echo esc_attr($acwcFrequencyPenalty)=='1.9' ? 'selected': ''; ?>>1.9</option>
			<option value="2"   <?php echo esc_attr($acwcFrequencyPenalty)=='2' ? 'selected': ''; ?>>2</option>
			</select>
			<div class="acwc_option_details_container">
			<p class="acwc_more_option_details">The frequency penalty is applied if the suggested text output is repeated (for example, the model used the exact token in previous completions or during the same session) and the model chooses an old output over a new one.</p>
			</div>			
		</div>
		<div class="acwc_setting_control_container">
			<label class="acwc_opt_label">Presence Penalty<span class="acwc_more_details"><i class="dashicons-before dashicons-info"></i></span></label>
			<?php $acwcPresencePenalty = (get_option('acwc_presence_penalty') !="")?get_option('acwc_presence_penalty'):"0"; ?>	
			<select name="presence_penalty" class="acwc_opt_field">
			<option value="0" <?php echo esc_attr($acwcPresencePenalty)=='0' ? 'selected': ''; ?>>0</option>
			<option value="0.1" <?php echo esc_attr($acwcPresencePenalty)=='0.1' ? 'selected': ''; ?>>0.1</option>
			<option value="0.2" <?php echo esc_attr($acwcPresencePenalty)=='0.2' ? 'selected': ''; ?>>0.2</option>
			<option value="0.3" <?php echo esc_attr($acwcPresencePenalty)=='0.3' ? 'selected': ''; ?>>0.3</option>
			<option value="0.4" <?php echo esc_attr($acwcPresencePenalty)=='0.4' ? 'selected': ''; ?>>0.4</option>
			<option value="0.5" <?php echo esc_attr($acwcPresencePenalty)=='0.5' ? 'selected': ''; ?>>0.5</option>
			<option value="0.6" <?php echo esc_attr($acwcPresencePenalty)=='0.6' ? 'selected': ''; ?>>0.6</option>
			<option value="0.7" <?php echo esc_attr($acwcPresencePenalty)=='0.7' ? 'selected': ''; ?>>0.7</option>
			<option value="0.8" <?php echo esc_attr($acwcPresencePenalty)=='0.8' ? 'selected': ''; ?>>0.8</option>
			<option value="0.9" <?php echo esc_attr($acwcPresencePenalty)=='0.9' ? 'selected': ''; ?>>0.9</option>
			<option value="1" <?php echo esc_attr($acwcPresencePenalty)=='1' ? 'selected': ''; ?>>1</option>
			<option value="1.1" <?php echo esc_attr($acwcPresencePenalty)=='1.1' ? 'selected': ''; ?>>1.1</option>
			<option value="1.2" <?php echo esc_attr($acwcPresencePenalty)=='1.2' ? 'selected': ''; ?>>1.2</option>
			<option value="1.3" <?php echo esc_attr($acwcPresencePenalty)=='1.3' ? 'selected': ''; ?>>1.3</option>
			<option value="1.4" <?php echo esc_attr($acwcPresencePenalty)=='1.4' ? 'selected': ''; ?>>1.4</option>
			<option value="1.5" <?php echo esc_attr($acwcPresencePenalty)=='1.5' ? 'selected': ''; ?>>1.5</option>
			<option value="1.6" <?php echo esc_attr($acwcPresencePenalty)=='1.6' ? 'selected': ''; ?>>1.6</option>
			<option value="1.7" <?php echo esc_attr($acwcPresencePenalty)=='1.7' ? 'selected': ''; ?>>1.7</option>
			<option value="1.8" <?php echo esc_attr($acwcPresencePenalty)=='1.8' ? 'selected': ''; ?>>1.8</option>
			<option value="1.9" <?php echo esc_attr($acwcPresencePenalty)=='1.9' ? 'selected': ''; ?>>1.9</option>
			<option value="2" <?php echo esc_attr($acwcPresencePenalty)=='2' ? 'selected': ''; ?>>2</option>
			</select>
			<div class="acwc_option_details_container">
			<p class="acwc_more_option_details">Penalizes new tokens based on whether they have appeared in the text so far.In general, the default value for presence_penalty is 0 and it’s used when you want to generate text that is coherent with the input prompt, by using words that are present in the input.</p>
			</div>
		</div>
	</div>

	<?php $acwcOpenAiSelectedLang = (!empty(get_option('acwc_set_org_language'))) ? esc_attr(get_option('acwc_set_org_language')) : 'en'; ?>

	<input type="hidden" name="orgLang" id="orgLang" value="<?php echo esc_html($acwcOpenAiSelectedLang); ?>">

	<h3 class="acwc_opt_main_title">Content Style Settings</h3>
	<div class="acwc_settings_contant_container">
		<div class="acwc_setting_control_container">
		<label class="acwc_opt_label">Select Language<span class="acwc_more_details"><i class="dashicons-before dashicons-info"></i></span></label>
		<select name="content_language" class="acwc_opt_field acwc_select_lang">
			<option data-name="Deutsch" id="dde" <?php echo esc_attr(get_option("acwc_set_language","en")) == "de"? "selected":""; ?> value="de">Deutsch</option>
			<option data-name="English" id="den" <?php echo esc_attr(get_option("acwc_set_language","en")) == "en"? "selected":""; ?> value="en">English</option>
			<option data-name="español" id="des" <?php echo esc_attr(get_option("acwc_set_language","en")) == "es"? "selected":""; ?> value="es">español</option>
			<option data-name="español (Latinoamérica)" id="des-419" <?php echo esc_attr(get_option("acwc_set_language","en")) == "es-419"? "selected":""; ?> value="es-419">español (Latinoamérica)</option>
			<option data-name="français" id="dfr" <?php echo esc_attr(get_option("acwc_set_language","en")) == "fr"? "selected":""; ?> value="fr">français</option>
			<option data-name="hrvatski" id="dhr" <?php echo esc_attr(get_option("acwc_set_language","en")) == "hr"? "selected":""; ?> value="hr">hrvatski</option>
			<option data-name="italiano" id="dit" <?php echo esc_attr(get_option("acwc_set_language","en")) == "it"? "selected":""; ?> value="it">italiano</option>
			<option data-name="Nederlands" id="dnl" <?php echo esc_attr(get_option("acwc_set_language","en")) == "nl"? "selected":""; ?> value="nl">Nederlands</option>
			<option data-name="polski" id="dpl" <?php echo esc_attr(get_option("acwc_set_language","en")) == "pl"? "selected":""; ?> value="pl">polski</option>
			<option data-name="português (Brasil)" id="dpt-BR" <?php echo esc_attr(get_option("acwc_set_language","en")) == "pt-BR"? "selected":""; ?> value="pt-BR">português (Brasil)</option>
			<option data-name="português (Portugal)" id="dpt-PT" <?php echo esc_attr(get_option("acwc_set_language","en")) == "pt-PT"? "selected":""; ?> value="pt-PT">português (Portugal)</option>
			<option data-name="Tiếng Việt" id="dvi" <?php echo esc_attr(get_option("acwc_set_language","en")) == "vi"? "selected":""; ?> value="vi">Tiếng Việt</option>
			<option data-name="Türkçe" id="dtr" <?php echo esc_attr(get_option("acwc_set_language","en")) == "tr"? "selected":""; ?> value="tr">Türkçe</option>
			<option data-name="русский" id="dru" <?php echo esc_attr(get_option("acwc_set_language","en")) == "ru"? "selected":""; ?> value="ru">русский</option>
			<option data-name="العربية" id="dar" <?php echo esc_attr(get_option("acwc_set_language","en")) == "ar"? "selected":""; ?> value="ar">العربية</option>
			<option data-name="ไทย" id="dth" <?php echo esc_attr(get_option("acwc_set_language","en")) == "th"? "selected":""; ?> value="th">ไทย</option>
			<option data-name="한국어" id="dko" <?php echo esc_attr(get_option("acwc_set_language","en")) == "ko"? "selected":""; ?> value="ko">한국어</option>
			<option data-name="中文 (简体)" id="dzh-CN" <?php echo esc_attr(get_option("acwc_set_language","en")) == "zh-CN"? "selected":""; ?> value="zh-CN">中文 (简体)</option>
			<option data-name="中文 (繁體)" id="dzh-TW" <?php echo esc_attr(get_option("acwc_set_language","en")) == "zh-TW"? "selected":""; ?> value="zh-TW">中文 (繁體)</option>
			<option data-name="香港中文" id="dzh-HK" <?php echo esc_attr(get_option("acwc_set_language","en")) == "zh-HK"? "selected":""; ?> value="zh-HK">香港中文</option>
			<option data-name="日本語" id="dja" <?php echo esc_attr(get_option("acwc_set_language","en")) == "ja"? "selected":""; ?> value="ja">日本語</option>
			<option data-name="Acoli" id="dach" <?php echo esc_attr(get_option("acwc_set_language","en")) == "ach"? "selected":""; ?> value="ach">Acoli</option>
			<option data-name="Afrikaans" id="daf" <?php echo esc_attr(get_option("acwc_set_language","en")) == "af"? "selected":""; ?> value="af">Afrikaans</option>
			<option data-name="Akan" id="dak" <?php echo esc_attr(get_option("acwc_set_language","en")) == "ak"? "selected":""; ?> value="ak">Akan</option>
			<option data-name="azərbaycan" id="daz" <?php echo esc_attr(get_option("acwc_set_language","en")) == "az"? "selected":""; ?> value="az">azərbaycan</option>
			<option data-name="Balinese" id="dban" <?php echo esc_attr(get_option("acwc_set_language","en")) == "ban"? "selected":""; ?> value="ban">Balinese</option>
			<option data-name="Basa Sunda" id="dsu" <?php echo esc_attr(get_option("acwc_set_language","en")) == "su"? "selected":""; ?> value="su">Basa Sunda</option>
			<option data-name="Bork, bork, bork!" id="dxx-bork" <?php echo esc_attr(get_option("acwc_set_language","en")) == "xx-bork"? "selected":""; ?> value="xx-bork">Bork, bork, bork!</option>
			<option data-name="bosanski" id="dbs" <?php echo esc_attr(get_option("acwc_set_language","en")) == "bs"? "selected":""; ?> value="bs">bosanski</option>
			<option data-name="brezhoneg" id="dbr" <?php echo esc_attr(get_option("acwc_set_language","en")) == "br"? "selected":""; ?> value="br">brezhoneg</option>
			<option data-name="català" id="dca" <?php echo esc_attr(get_option("acwc_set_language","en")) == "ca"? "selected":""; ?> value="ca">català</option>
			<option data-name="Cebuano" id="dceb" <?php echo esc_attr(get_option("acwc_set_language","en")) == "ceb"? "selected":""; ?> value="ceb">Cebuano</option>
			<option data-name="čeština" id="dcs" <?php echo esc_attr(get_option("acwc_set_language","en")) == "cs"? "selected":""; ?> value="cs">čeština</option>
			<option data-name="chiShona" id="dsn" <?php echo esc_attr(get_option("acwc_set_language","en")) == "sn"? "selected":""; ?> value="sn">chiShona</option>
			<option data-name="Corsican" id="dco" <?php echo esc_attr(get_option("acwc_set_language","en")) == "co"? "selected":""; ?> value="co">Corsican</option>
			<option data-name="créole haïtien" id="dht" <?php echo esc_attr(get_option("acwc_set_language","en")) == "ht"? "selected":""; ?> value="ht">créole haïtien</option>
			<option data-name="Cymraeg" id="dcy" <?php echo esc_attr(get_option("acwc_set_language","en")) == "cy"? "selected":""; ?> value="cy">Cymraeg</option>
			<option data-name="dansk" id="dda" <?php echo esc_attr(get_option("acwc_set_language","en")) == "da"? "selected":""; ?> value="da">dansk</option>
			<option data-name="Èdè Yorùbá" id="dyo" <?php echo esc_attr(get_option("acwc_set_language","en")) == "yo"? "selected":""; ?> value="yo">Èdè Yorùbá</option>
			<option data-name="eesti" id="det" <?php echo esc_attr(get_option("acwc_set_language","en")) == "et"? "selected":""; ?> value="et">eesti</option>
			<option data-name="Elmer Fudd" id="dxx-elmer" <?php echo esc_attr(get_option("acwc_set_language","en")) == "xx-elmer"? "selected":""; ?> value="xx-elmer">Elmer Fudd</option>
			<option data-name="esperanto" id="deo" <?php echo esc_attr(get_option("acwc_set_language","en")) == "eo"? "selected":""; ?> value="eo">esperanto</option>
			<option data-name="euskara" id="deu" <?php echo esc_attr(get_option("acwc_set_language","en")) == "eu"? "selected":""; ?> value="eu">euskara</option>
			<option data-name="Eʋegbe" id="dee" <?php echo esc_attr(get_option("acwc_set_language","en")) == "ee"? "selected":""; ?> value="ee">Eʋegbe</option>
			<option data-name="Filipino" id="dtl" <?php echo esc_attr(get_option("acwc_set_language","en")) == "tl"? "selected":""; ?> value="tl">Filipino</option>
			<option data-name="Filipino" id="dfil" <?php echo esc_attr(get_option("acwc_set_language","en")) == "fil"? "selected":""; ?> value="fil">Filipino</option>
			<option data-name="føroyskt" id="dfo" <?php echo esc_attr(get_option("acwc_set_language","en")) == "fo"? "selected":""; ?> value="fo">føroyskt</option>
			<option data-name="Frysk" id="dfy" <?php echo esc_attr(get_option("acwc_set_language","en")) == "fy"? "selected":""; ?> value="fy">Frysk</option>
			<option data-name="Ga" id="dgaa" <?php echo esc_attr(get_option("acwc_set_language","en")) == "gaa"? "selected":""; ?> value="gaa">Ga</option>
			<option data-name="Gaeilge" id="dga" <?php echo esc_attr(get_option("acwc_set_language","en")) == "ga"? "selected":""; ?> value="ga">Gaeilge</option>
			<option data-name="Gàidhlig" id="dgd" <?php echo esc_attr(get_option("acwc_set_language","en")) == "gd"? "selected":""; ?> value="gd">Gàidhlig</option>
			<option data-name="galego" id="dgl" <?php echo esc_attr(get_option("acwc_set_language","en")) == "gl"? "selected":""; ?> value="gl">galego</option>
			<option data-name="Guarani" id="dgn" <?php echo esc_attr(get_option("acwc_set_language","en")) == "gn"? "selected":""; ?> value="gn">Guarani</option>
			<option data-name="Hacker" id="dxx-hacker" <?php echo esc_attr(get_option("acwc_set_language","en")) == "xx-hacker"? "selected":""; ?> value="xx-hacker">Hacker</option>
			<option data-name="Hausa" id="dha" <?php echo esc_attr(get_option("acwc_set_language","en")) == "ha"? "selected":""; ?> value="ha">Hausa</option>
			<option data-name="ʻŌlelo Hawaiʻi" id="dhaw" <?php echo esc_attr(get_option("acwc_set_language","en")) == "haw"? "selected":""; ?> value="haw">ʻŌlelo Hawaiʻi</option>
			<option data-name="Ichibemba" id="dbem" <?php echo esc_attr(get_option("acwc_set_language","en")) == "bem"? "selected":""; ?> value="bem">Ichibemba</option>
			<option data-name="Igbo" id="dig" <?php echo esc_attr(get_option("acwc_set_language","en")) == "ig"? "selected":""; ?> value="ig">Igbo</option>
			<option data-name="Ikirundi" id="drn" <?php echo esc_attr(get_option("acwc_set_language","en")) == "rn"? "selected":""; ?> value="rn">Ikirundi</option>
			<option data-name="Indonesia" id="did" <?php echo esc_attr(get_option("acwc_set_language","en")) == "id"? "selected":""; ?> value="id">Indonesia</option>
			<option data-name="interlingua" id="dia" <?php echo esc_attr(get_option("acwc_set_language","en")) == "ia"? "selected":""; ?> value="ia">interlingua</option>
			<option data-name="IsiXhosa" id="dxh" <?php echo esc_attr(get_option("acwc_set_language","en")) == "xh"? "selected":""; ?> value="xh">IsiXhosa</option>
			<option data-name="isiZulu" id="dzu" <?php echo esc_attr(get_option("acwc_set_language","en")) == "zu"? "selected":""; ?> value="zu">isiZulu</option>
			<option data-name="íslenska" id="dis" <?php echo esc_attr(get_option("acwc_set_language","en")) == "is"? "selected":""; ?> value="is">íslenska</option>
			<option data-name="Jawa" id="djw" <?php echo esc_attr(get_option("acwc_set_language","en")) == "jw"? "selected":""; ?> value="jw">Jawa</option>
			<option data-name="Kinyarwanda" id="drw" <?php echo esc_attr(get_option("acwc_set_language","en")) == "rw"? "selected":""; ?> value="rw">Kinyarwanda</option>
			<option data-name="Kiswahili" id="dsw" <?php echo esc_attr(get_option("acwc_set_language","en")) == "sw"? "selected":""; ?> value="sw">Kiswahili</option>
			<option data-name="Klingon" id="dtlh" <?php echo esc_attr(get_option("acwc_set_language","en")) == "tlh"? "selected":""; ?> value="tlh">Klingon</option>
			<option data-name="Kongo" id="dkg" <?php echo esc_attr(get_option("acwc_set_language","en")) == "kg"? "selected":""; ?> value="kg">Kongo</option>
			<option data-name="kreol morisien" id="dmfe" <?php echo esc_attr(get_option("acwc_set_language","en")) == "mfe"? "selected":""; ?> value="mfe">kreol morisien</option>
			<option data-name="Krio (Sierra Leone)" id="dkri" <?php echo esc_attr(get_option("acwc_set_language","en")) == "kri"? "selected":""; ?> value="kri">Krio (Sierra Leone)</option>
			<option data-name="Latin" id="dla" <?php echo esc_attr(get_option("acwc_set_language","en")) == "la"? "selected":""; ?> value="la">Latin</option>
			<option data-name="latviešu" id="dlv" <?php echo esc_attr(get_option("acwc_set_language","en")) == "lv"? "selected":""; ?> value="lv">latviešu</option>
			<option data-name="lea fakatonga" id="dto" <?php echo esc_attr(get_option("acwc_set_language","en")) == "to"? "selected":""; ?> value="to">lea fakatonga</option>
			<option data-name="lietuvių" id="dlt" <?php echo esc_attr(get_option("acwc_set_language","en")) == "lt"? "selected":""; ?> value="lt">lietuvių</option>
			<option data-name="lingála" id="dln" <?php echo esc_attr(get_option("acwc_set_language","en")) == "ln"? "selected":""; ?> value="ln">lingála</option>
			<option data-name="Lozi" id="dloz" <?php echo esc_attr(get_option("acwc_set_language","en")) == "loz"? "selected":""; ?> value="loz">Lozi</option>
			<option data-name="Luba-Lulua" id="dlua" <?php echo esc_attr(get_option("acwc_set_language","en")) == "lua"? "selected":""; ?> value="lua">Luba-Lulua</option>
			<option data-name="Luganda" id="dlg" <?php echo esc_attr(get_option("acwc_set_language","en")) == "lg"? "selected":""; ?> value="lg">Luganda</option>
			<option data-name="magyar" id="dhu" <?php echo esc_attr(get_option("acwc_set_language","en")) == "hu"? "selected":""; ?> value="hu">magyar</option>
			<option data-name="Malagasy" id="dmg" <?php echo esc_attr(get_option("acwc_set_language","en")) == "mg"? "selected":""; ?> value="mg">Malagasy</option>
			<option data-name="Malti" id="dmt" <?php echo esc_attr(get_option("acwc_set_language","en")) == "mt"? "selected":""; ?> value="mt">Malti</option>
			<option data-name="Māori" id="dmi" <?php echo esc_attr(get_option("acwc_set_language","en") == "mi"? "selected":""); ?> value="mi">Māori</option>
			<option data-name="Melayu" id="dms" <?php echo esc_attr(get_option("acwc_set_language","en") == "ms"? "selected":""); ?> value="ms">Melayu</option>
			<option data-name="Nigerian Pidgin" id="dpcm" <?php echo esc_attr(get_option("acwc_set_language","en")) == "pcm"? "selected":""; ?> value="pcm">Nigerian Pidgin</option>
			<option data-name="norsk" id="dno" <?php echo esc_attr(get_option("acwc_set_language","en")) == "no"? "selected":""; ?> value="no">norsk</option>
			<option data-name="norsk nynorsk" id="dnn" <?php echo esc_attr(get_option("acwc_set_language","en") == "nn"? "selected":""); ?> value="nn">norsk nynorsk</option>
			<option data-name="Northern Sotho" id="dnso" <?php echo esc_attr(get_option("acwc_set_language","en") == "nso"? "selected":""); ?> value="nso">Northern Sotho</option>
			<option data-name="Nyanja" id="dny" <?php echo esc_attr(get_option("acwc_set_language","en")) == "ny"? "selected":""; ?> value="ny">Nyanja</option>
			<option data-name="o‘zbek" id="duz" <?php echo esc_attr(get_option("acwc_set_language","en")) == "uz"? "selected":""; ?> value="uz">o‘zbek</option>
			<option data-name="Occitan" id="doc" <?php echo esc_attr(get_option("acwc_set_language","en")) == "oc"? "selected":""; ?> value="oc">Occitan</option>
			<option data-name="Oromoo" id="dom" <?php echo esc_attr(get_option("acwc_set_language","en") == "om"? "selected":""); ?> value="om">Oromoo</option>
			<option data-name="Pirate" id="dxx-pirate" <?php echo esc_attr(get_option("acwc_set_language","en") == "xx-pirate"? "selected":""); ?> value="xx-pirate">Pirate</option>
			<option data-name="română" id="dro" <?php echo esc_attr(get_option("acwc_set_language","en")) == "ro"? "selected":""; ?> value="ro">română</option>
			<option data-name="rumantsch" id="drm" <?php echo esc_attr(get_option("acwc_set_language","en")) == "rm"? "selected":""; ?> value="rm">rumantsch</option>
			<option data-name="Runasimi" id="dqu" <?php echo esc_attr(get_option("acwc_set_language","en") == "qu"? "selected":""); ?> value="qu">Runasimi</option>
			<option data-name="Runyankore" id="dnyn" <?php echo esc_attr(get_option("acwc_set_language","en") == "nyn"? "selected":""); ?> value="nyn">Runyankore</option>
			<option data-name="Seychellois Creole" id="dcrs" <?php echo esc_attr(get_option("acwc_set_language","en") == "crs"? "selected":""); ?> value="crs">Seychellois Creole</option>
			<option data-name="shqip" id="dsq" <?php echo esc_attr(get_option("acwc_set_language","en") == "sq"? "selected":""); ?> value="sq">shqip</option>
			<option data-name="slovenčina" id="dsk" <?php echo esc_attr(get_option("acwc_set_language","en")) == "sk"? "selected":""; ?> value="sk">slovenčina</option>
			<option data-name="slovenščina" id="dsl" <?php echo esc_attr(get_option("acwc_set_language","en")) == "sl"? "selected":""; ?> value="sl">slovenščina</option>
			<option data-name="Soomaali" id="dso" <?php echo esc_attr(get_option("acwc_set_language","en") == "so"? "selected":""); ?> value="so">Soomaali</option>
			<option data-name="Southern Sotho" id="dst" <?php echo esc_attr(get_option("acwc_set_language","en") == "st"? "selected":""); ?> value="st">Southern Sotho</option>
			<option data-name="srpski (Crna Gora)" id="dsr-ME" <?php echo esc_attr(get_option("acwc_set_language","en") == "sr-ME"? "selected":""); ?> value="sr-ME">srpski (Crna Gora)</option>
			<option data-name="srpski (latinica)" id="dsr-Latn" <?php echo esc_attr(get_option("acwc_set_language","en") == "sr-Latn"? "selected":""); ?> value="sr-Latn">srpski (latinica)</option>
			<option data-name="suomi" id="dfi" <?php echo esc_attr(get_option("acwc_set_language","en")) == "fi"? "selected":""; ?> value="fi">suomi</option>
			<option data-name="svenska" id="dsv" <?php echo esc_attr(get_option("acwc_set_language","en")) == "sv"? "selected":""; ?> value="sv">svenska</option>
			<option data-name="Tswana" id="dtn" <?php echo esc_attr(get_option("acwc_set_language","en")) == "tn"? "selected":""; ?> value="tn">Tswana</option>
			<option data-name="Tumbuka" id="dtum" <?php echo esc_attr(get_option("acwc_set_language","en")) == "tum"? "selected":""; ?> value="tum">Tumbuka</option>
			<option data-name="türkmen dili" id="dtk" <?php echo esc_attr(get_option("acwc_set_language","en")) == "tk"? "selected":""; ?> value="tk">türkmen dili</option>
			<option data-name="Twi" id="dtw" <?php echo esc_attr(get_option("acwc_set_language","en")) == "tw"? "selected":""; ?> value="tw">Twi</option>
			<option data-name="Wolof" id="dwo" <?php echo esc_attr(get_option("acwc_set_language","en")) == "wo"? "selected":""; ?> value="wo">Wolof</option>
			<option data-name="Ελληνικά" id="del" <?php echo esc_attr(get_option("acwc_set_language","en")) == "el"? "selected":""; ?> value="el">Ελληνικά</option>
			<option data-name="беларуская" id="dbe" <?php echo esc_attr(get_option("acwc_set_language","en")) == "be"? "selected":""; ?> value="be">беларуская</option>
			<option data-name="български" id="dbg" <?php echo esc_attr(get_option("acwc_set_language","en")) == "bg"? "selected":""; ?> value="bg">български</option>
			<option data-name="кыргызча" id="dky" <?php echo esc_attr(get_option("acwc_set_language","en")) == "ky"? "selected":""; ?> value="ky">кыргызча</option>
			<option data-name="қазақ тілі" id="dkk" <?php echo esc_attr(get_option("acwc_set_language","en")) == "kk"? "selected":""; ?> value="kk">қазақ тілі</option>
			<option data-name="македонски" id="dmk" <?php echo esc_attr(get_option("acwc_set_language","en")) == "mk"? "selected":""; ?> value="mk">македонски</option>
			<option data-name="монгол" id="dmn" <?php echo esc_attr(get_option("acwc_set_language","en")) == "mn"? "selected":""; ?> value="mn">монгол</option>
			<option data-name="српски" id="dsr" <?php echo esc_attr(get_option("acwc_set_language","en")) == "sr"? "selected":""; ?> value="sr">српски</option>
			<option data-name="татар" id="dtt" <?php echo esc_attr(get_option("acwc_set_language","en")) == "tt"? "selected":""; ?> value="tt">татар</option>
			<option data-name="тоҷикӣ" id="dtg" <?php echo esc_attr(get_option("acwc_set_language","en")) == "tg"? "selected":""; ?> value="tg">тоҷикӣ</option>
			<option data-name="українська" id="duk" <?php echo esc_attr(get_option("acwc_set_language","en")) == "uk"? "selected":""; ?> value="uk">українська</option>
			<option data-name="ქართული" id="dka" <?php echo esc_attr(get_option("acwc_set_language","en")) == "ka"? "selected":""; ?> value="ka">ქართული</option>
			<option data-name="հայերեն" id="dhy" <?php echo esc_attr(get_option("acwc_set_language","en")) == "hy"? "selected":""; ?> value="hy">հայերեն</option>
			<option data-name="ייִדיש" id="dyi" <?php echo esc_attr(get_option("acwc_set_language","en")) == "yi"? "selected":""; ?> value="yi">ייִדיש</option>
			<option data-name="עברית" id="diw" <?php echo esc_attr(get_option("acwc_set_language","en")) == "iw"? "selected":""; ?> value="iw">עברית</option>
			<option data-name="ئۇيغۇرچە" id="dug" <?php echo esc_attr(get_option("acwc_set_language","en")) == "ug"? "selected":""; ?> value="ug">ئۇيغۇرچە</option>
			<option data-name="اردو" id="dur" <?php echo esc_attr(get_option("acwc_set_language","en")) == "ur"? "selected":""; ?> value="ur">اردو</option>
			<option data-name="پښتو" id="dps" <?php echo esc_attr(get_option("acwc_set_language","en")) == "ps"? "selected":""; ?> value="ps">پښتو</option>
			<option data-name="سنڌي" id="dsd" <?php echo esc_attr(get_option("acwc_set_language","en")) == "sd"? "selected":""; ?> value="sd">سنڌي</option>
			<option data-name="فارسی" id="dfa" <?php echo esc_attr(get_option("acwc_set_language","en")) == "fa"? "selected":""; ?> value="fa">فارسی</option>
			<option data-name="کوردیی ناوەندی" id="dckb" <?php echo esc_attr(get_option("acwc_set_language","en")) == "ckb"? "selected":""; ?> value="ckb">کوردیی ناوەندی</option>
			<option data-name="ትግርኛ" id="dti" <?php echo esc_attr(get_option("acwc_set_language","en")) == "ti"? "selected":""; ?> value="ti">ትግርኛ</option>
			<option data-name="አማርኛ" id="dam" <?php echo esc_attr(get_option("acwc_set_language","en")) == "am"? "selected":""; ?> value="am">አማርኛ</option>
			<option data-name="বাংলা" id="dbn" <?php echo esc_attr(get_option("acwc_set_language","en")) == "bn"? "selected":""; ?> value="bn">বাংলা</option>
			<option data-name="नेपाली" id="dne" <?php echo esc_attr(get_option("acwc_set_language","en")) == "ne"? "selected":""; ?> value="ne">नेपाली</option>
			<option data-name="मराठी" id="dmr" <?php echo esc_attr(get_option("acwc_set_language","en")) == "mr"? "selected":""; ?> value="mr">मराठी</option>
			<option data-name="हिन्दी" id="dhi" <?php echo esc_attr(get_option("acwc_set_language","en")) == "hi"? "selected":""; ?> value="hi">हिन्दी</option>
			<option data-name="ਪੰਜਾਬੀ" id="dpa" <?php echo esc_attr(get_option("acwc_set_language","en")) == "pa"? "selected":""; ?> value="pa">ਪੰਜਾਬੀ</option>
			<option data-name="ગુજરાતી" id="dgu" <?php echo esc_attr(get_option("acwc_set_language","en")) == "gu"? "selected":""; ?> value="gu">ગુજરાતી</option>
			<option data-name="ଓଡ଼ିଆ" id="dor" <?php echo esc_attr(get_option("acwc_set_language","en")) == "or"? "selected":""; ?> value="or">ଓଡ଼ିଆ</option>
			<option data-name="தமிழ்" id="dta" <?php echo esc_attr(get_option("acwc_set_language","en")) == "ta"? "selected":""; ?> value="ta">தமிழ்</option>
			<option data-name="Assamese" id="Assamese" <?php echo esc_attr(get_option("acwc_set_language","en")) == "Assamese"? "selected":""; ?> value="Assamese">অসমীয়া</option>
			<option data-name="తెలుగు" id="dte" <?php echo esc_attr(get_option("acwc_set_language","en")) == "te"? "selected":""; ?> value="te">తెలుగు</option>
			<option data-name="ಕನ್ನಡ" id="dkn" <?php echo esc_attr(get_option("acwc_set_language","en")) == "kn"? "selected":""; ?> value="kn">ಕನ್ನಡ</option>
			<option data-name="മലയാളം" id="dml" <?php echo esc_attr(get_option("acwc_set_language","en")) == "ml"? "selected":""; ?> value="ml">മലയാളം</option>
			<option data-name="සිංහල" id="dsi" <?php echo esc_attr(get_option("acwc_set_language","en")) == "si"? "selected":""; ?> value="si">සිංහල</option>
			<option data-name="ລາວ" id="dlo" <?php echo esc_attr(get_option("acwc_set_language","en")) == "lo"? "selected":""; ?> value="lo">ລາວ</option>
			<option data-name="မြန်မာ" id="dmy" <?php echo esc_attr(get_option("acwc_set_language","en")) == "my"? "selected":""; ?> value="my">မြန်မာ</option>
			<option data-name="ខ្មែរ" id="dkm" <?php echo esc_attr(get_option("acwc_set_language","en")) == "km"? "selected":""; ?> value="km">ខ្មែរ</option>
			<option data-name="ᏣᎳᎩ" id="dchr" <?php echo esc_attr(get_option("acwc_set_language","en")) == "chr"? "selected":""; ?> value="chr">ᏣᎳᎩ</option>
		</select>
		<div class="acwc_option_details_container">
			<p class="acwc_more_option_details">For need to output in which language need to set here.If select 'Hindi' language then output will be in Hindi language title and content in hindi language.</p>
		</div>
		</div>
		<div class="acwc_setting_control_container">
		<label class="acwc_opt_label">Writing Style<span class="acwc_more_details"><i class="dashicons-before dashicons-info"></i></span></label>
		<select class="acwc_opt_field" name="writing_style" >

            <option value="infor" <?php echo ( esc_attr(get_option("acwc_set_writing_style","en")) == 'infor' ? 'selected' : '' ) ;?>>Informative</option>
            <option value="acade" <?php echo ( esc_attr(get_option("acwc_set_writing_style","en")) == 'acade' ? 'selected' : '' ) ;?>>Academic</option>
            <option value="analy" <?php echo ( esc_attr(get_option("acwc_set_writing_style","en")) == 'analy' ? 'selected' : '' ) ;?>>Analytical</option>
            <option value="anect" <?php echo ( esc_attr(get_option("acwc_set_writing_style","en")) == 'anect' ? 'selected' : '' ) ;?>>Anecdotal</option>
            <option value="argum" <?php echo ( esc_attr(get_option("acwc_set_writing_style","en")) == 'argum' ? 'selected' : '' ) ;?>>Argumentative</option>
            <option value="artic" <?php echo ( esc_attr(get_option("acwc_set_writing_style","en")) == 'artic' ? 'selected' : '' ) ;?>>Articulate</option>
            <option value="biogr" <?php echo ( esc_attr(get_option("acwc_set_writing_style","en")) == 'biogr' ? 'selected' : '' ) ;?>>Biographical</option>
            <option value="blog" <?php echo ( esc_attr(get_option("acwc_set_writing_style","en")) == 'blog' ? 'selected' : '' ) ;?>>Blog</option>
            <option value="casua" <?php echo ( esc_attr(get_option("acwc_set_writing_style","en")) == 'casua' ? 'selected' : '' ) ;?>>Casual</option>
            <option value="collo" <?php echo ( esc_attr(get_option("acwc_set_writing_style","en")) == 'collo' ? 'selected' : '' ) ;?>>Colloquial</option>
            <option value="compa" <?php echo ( esc_attr(get_option("acwc_set_writing_style","en")) == 'compa' ? 'selected' : '' ) ;?>>Comparative</option>
            <option value="conci" <?php echo ( esc_attr(get_option("acwc_set_writing_style","en")) == 'conci' ? 'selected' : '' ) ;?>>Concise</option>
            <option value="creat" <?php echo ( esc_attr(get_option("acwc_set_writing_style","en")) == 'creat' ? 'selected' : '' ) ;?>>Creative</option>
            <option value="criti" <?php echo ( esc_attr(get_option("acwc_set_writing_style","en")) == 'criti' ? 'selected' : '' ) ;?>>Critical</option>
            <option value="descr" <?php echo ( esc_attr(get_option("acwc_set_writing_style","en")) == 'descr' ? 'selected' : '' ) ;?>>Descriptive</option>
            <option value="detai" <?php echo ( esc_attr(get_option("acwc_set_writing_style","en")) == 'detai' ? 'selected' : '' ) ;?>>Detailed</option>
            <option value="dialo" <?php echo ( esc_attr(get_option("acwc_set_writing_style","en")) == 'dialo' ? 'selected' : '' ) ;?>>Dialogue</option>
            <option value="direct" <?php echo ( esc_attr(get_option("acwc_set_writing_style","en")) == 'direct' ? 'selected' : '' ) ;?>>Direct</option>
            <option value="drama" <?php echo ( esc_attr(get_option("acwc_set_writing_style","en")) == 'drama' ? 'selected' : '' ) ;?>>Dramatic</option>
            <option value="evalu" <?php echo ( esc_attr(get_option("acwc_set_writing_style","en")) == 'evalu' ? 'selected' : '' ) ;?>>Evaluative</option>
            <option value="emoti" <?php echo ( esc_attr(get_option("acwc_set_writing_style","en")) == 'emoti' ? 'selected' : '' ) ;?>>Emotional</option>
            <option value="expos" <?php echo ( esc_attr(get_option("acwc_set_writing_style","en")) == 'expos' ? 'selected' : '' ) ;?>>Expository</option>
            <option value="ficti" <?php echo ( esc_attr(get_option("acwc_set_writing_style","en")) == 'ficti' ? 'selected' : '' ) ;?>>Fiction</option>
            <option value="histo" <?php echo ( esc_attr(get_option("acwc_set_writing_style","en")) == 'histo' ? 'selected' : '' ) ;?>>Historical</option>
            <option value="journ" <?php echo ( esc_attr(get_option("acwc_set_writing_style","en")) == 'journ' ? 'selected' : '' ) ;?>>Journalistic</option>
            <option value="lette" <?php echo ( esc_attr(get_option("acwc_set_writing_style","en")) == 'lette' ? 'selected' : '' ) ;?>>Letter</option>
            <option value="lyric" <?php echo ( esc_attr(get_option("acwc_set_writing_style","en")) == 'lyric' ? 'selected' : '' ) ;?>>Lyrical</option>
            <option value="metaph" <?php echo ( esc_attr(get_option("acwc_set_writing_style","en")) == 'metaph' ? 'selected' : '' ) ;?>>Metaphorical</option>
            <option value="monol" <?php echo ( esc_attr(get_option("acwc_set_writing_style","en")) == 'monol' ? 'selected' : '' ) ;?>>Monologue</option>
            <option value="narra" <?php echo ( esc_attr(get_option("acwc_set_writing_style","en")) == 'narra' ? 'selected' : '' ) ;?>>Narrative</option>
            <option value="news" <?php echo ( esc_attr(get_option("acwc_set_writing_style","en")) == 'news' ? 'selected' : '' ) ;?>>News</option>
            <option value="objec" <?php echo ( esc_attr(get_option("acwc_set_writing_style","en")) == 'objec' ? 'selected' : '' ) ;?>>Objective</option>
            <option value="pasto" <?php echo ( esc_attr(get_option("acwc_set_writing_style","en")) == 'pasto' ? 'selected' : '' ) ;?>>Pastoral</option>
            <option value="perso" <?php echo ( esc_attr(get_option("acwc_set_writing_style","en")) == 'perso' ? 'selected' : '' ) ;?>>Personal</option>
            <option value="persu" <?php echo ( esc_attr(get_option("acwc_set_writing_style","en")) == 'persu' ? 'selected' : '' ) ;?>>Persuasive</option>
            <option value="poeti" <?php echo ( esc_attr(get_option("acwc_set_writing_style","en")) == 'poeti' ? 'selected' : '' ) ;?>>Poetic</option>
            <option value="refle" <?php echo ( esc_attr(get_option("acwc_set_writing_style","en")) == 'refle' ? 'selected' : '' ) ;?>>Reflective</option>
            <option value="rheto" <?php echo ( esc_attr(get_option("acwc_set_writing_style","en")) == 'rheto' ? 'selected' : '' ) ;?>>Rhetorical</option>
            <option value="satir" <?php echo ( esc_attr(get_option("acwc_set_writing_style","en")) == 'satir' ? 'selected' : '' ) ;?>>Satirical</option>
            <option value="senso" <?php echo ( esc_attr(get_option("acwc_set_writing_style","en")) == 'senso' ? 'selected' : '' ) ;?>>Sensory</option>
            <option value="simpl" <?php echo ( esc_attr(get_option("acwc_set_writing_style","en")) == 'simpl' ? 'selected' : '' ) ;?>>Simple</option>
            <option value="techn" <?php echo ( esc_attr(get_option("acwc_set_writing_style","en")) == 'techn' ? 'selected' : '' ) ;?>>Technical</option>
            <option value="theore" <?php echo ( esc_attr(get_option("acwc_set_writing_style","en")) == 'theore' ? 'selected' : '' ) ;?>>Theoretical</option>
            <option value="vivid" <?php echo ( esc_attr(get_option("acwc_set_writing_style","en")) == 'vivid' ? 'selected' : '' ) ;?>>Vivid</option>
            <option value="busin" <?php echo ( esc_attr(get_option("acwc_set_writing_style","en")) == 'busin' ? 'selected' : '' ) ;?>>Business</option>
            <option value="repor" <?php echo ( esc_attr(get_option("acwc_set_writing_style","en")) == 'repor' ? 'selected' : '' ) ;?>>Report</option>
            <option value="resea" <?php echo ( esc_attr(get_option("acwc_set_writing_style","en")) == 'resea' ? 'selected' : '' ) ;?>>Research</option>
        </select>
			<div class="acwc_option_details_container">
				<p class="acwc_more_option_details">To select writing style for your content you can select writing style from dropdown.</p>
			</div>
		</div>
		<div class="acwc_setting_control_container">
		<label class="acwc_opt_label">Writing tone<span class="acwc_more_details"><i class="dashicons-before dashicons-info"></i></span></label>
		<select class="acwc_opt_field" name="writing_tone" >
            <option value="formal" <?php echo ( esc_attr(get_option("acwc_set_writing_tone","en")) == 'formal' ? 'selected' : '' ) ;?>>Formal</option>
            <option value="asser" <?php echo ( esc_attr(get_option("acwc_set_writing_tone","en")) == 'asser' ? 'selected' : '' ) ;?>>Assertive</option>
            <option value="authoritative" <?php echo ( esc_attr(get_option("acwc_set_writing_tone","en")) == 'authoritative' ? 'selected' : '' ) ;?>>Authoritative</option>
            <option value="cheer" <?php echo ( esc_attr(get_option("acwc_set_writing_tone","en")) == 'cheer' ? 'selected' : '' ) ;?>>Cheerful</option>
            <option value="confident" <?php echo ( esc_attr(get_option("acwc_set_writing_tone","en")) == 'confident' ? 'selected' : '' ) ;?>>Confident</option>
            <option value="conve" <?php echo ( esc_attr(get_option("acwc_set_writing_tone","en")) == 'conve' ? 'selected' : '' ) ;?>>Conversational</option>
            <option value="factual" <?php echo ( esc_attr(get_option("acwc_set_writing_tone","en")) == 'factual' ? 'selected' : '' ) ;?>>Factual</option>
            <option value="friendly" <?php echo ( esc_attr(get_option("acwc_set_writing_tone","en")) == 'friendly' ? 'selected' : '' ) ;?>>Friendly</option>
            <option value="humor" <?php echo ( esc_attr(get_option("acwc_set_writing_tone","en")) == 'humor' ? 'selected' : '' ) ;?>>Humorous</option>
            <option value="informal" <?php echo ( esc_attr(get_option("acwc_set_writing_tone","en")) == 'informal' ? 'selected' : '' ) ;?>>Informal</option>
            <option value="inspi" <?php echo ( esc_attr(get_option("acwc_set_writing_tone","en")) == 'inspi' ? 'selected' : '' ) ;?>>Inspirational</option>
            <option value="neutr" <?php echo ( esc_attr(get_option("acwc_set_writing_tone","en")) == 'neutr' ? 'selected' : '' ) ;?>>Neutral</option>
            <option value="nostalgic" <?php echo ( esc_attr(get_option("acwc_set_writing_tone","en")) == 'nostalgic' ? 'selected' : '' ) ;?>>Nostalgic</option>
            <option value="polite" <?php echo ( esc_attr(get_option("acwc_set_writing_tone","en")) == 'polite' ? 'selected' : '' ) ;?>>Polite</option>
            <option value="profe" <?php echo ( esc_attr(get_option("acwc_set_writing_tone","en")) == 'profe' ? 'selected' : '' ) ;?>>Professional</option>
            <option value="romantic" <?php echo ( esc_attr(get_option("acwc_set_writing_tone","en")) == 'romantic' ? 'selected' : '' ) ;?>>Romantic</option>
            <option value="sarca" <?php echo ( esc_attr(get_option("acwc_set_writing_tone","en")) == 'sarca' ? 'selected' : '' ) ;?>>Sarcastic</option>
            <option value="scien" <?php echo ( esc_attr(get_option("acwc_set_writing_tone","en")) == 'scien' ? 'selected' : '' ) ;?>>Scientific</option>
            <option value="sensit" <?php echo ( esc_attr(get_option("acwc_set_writing_tone","en")) == 'sensit' ? 'selected' : '' ) ;?>>Sensitive</option>
            <option value="serious" <?php echo ( esc_attr(get_option("acwc_set_writing_tone","en")) == 'serious' ? 'selected' : '' ) ;?>>Serious</option>
            <option value="sincere" <?php echo ( esc_attr(get_option("acwc_set_writing_tone","en")) == 'sincere' ? 'selected' : '' ) ;?>>Sincere</option>
            <option value="skept" <?php echo ( esc_attr(get_option("acwc_set_writing_tone","en")) == 'skept' ? 'selected' : '' ) ;?>>Skeptical</option>
            <option value="suspenseful" <?php echo ( esc_attr(get_option("acwc_set_writing_tone","en")) == 'suspenseful' ? 'selected' : '' ) ;?>>Suspenseful</option>
            <option value="sympathetic" <?php echo ( esc_attr(get_option("acwc_set_writing_tone","en")) == 'sympathetic' ? 'selected' : '' ) ;?>>Sympathetic</option>
			<option value="curio" <?php echo ( esc_attr(get_option("acwc_set_writing_tone","en")) == 'curio' ? 'selected' : '' ) ;?>>Curious</option>
			<option value="disap" <?php echo ( esc_attr(get_option("acwc_set_writing_tone","en")) == 'disap' ? 'selected' : '' ) ;?>>Disappointed</option>
			<option value="encou" <?php echo ( esc_attr(get_option("acwc_set_writing_tone","en")) == 'encou' ? 'selected' : '' ) ;?>>Encouraging</option>
			<option value="optim" <?php echo ( esc_attr(get_option("acwc_set_writing_tone","en")) == 'optim' ? 'selected' : '' ) ;?>>Optimistic</option>
			<option value="surpr" <?php echo ( esc_attr(get_option("acwc_set_writing_tone","en")) == 'surpr' ? 'selected' : '' ) ;?>>Surprised</option>
			<option value="worry" <?php echo ( esc_attr(get_option("acwc_set_writing_tone","en")) == 'worry' ? 'selected' : '' ) ;?>>Worried</option>
        </select>
		<div class="acwc_option_details_container">
				<p class="acwc_more_option_details">If you convey your emotion and attitude with your content you can select your writing tone from dropdown.</p>
			</div>
		</div>
	</div>
	<button class="acwc_setting_save_btn">Save Data</button>
	<span class="acwc_success" id="successSaveData">Save Successfully</span>
	</form>
</div>