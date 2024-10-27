<?php

namespace ACWCPromptContent;
class ACWCPromptContent
{

    public $acwcOpenAiTyp = '';
    public function __construct($acwcOpenAiType)
    {
        $this->acwcOpenAiTyp = isset($acwcOpenAiType) ? sanitize_text_field($acwcOpenAiType) : "";
    }

	public function acwc_first_content()
    {
        $acwcOpenaiCgvalue = $acwcOpenaiTxt = "";
        if ($this->acwcOpenAiTyp == "get_titles"){
            $acwcOpenaiCgvalue = "5";
            $acwcOpenaiTxt = ($acwcOpenaiCgvalue > 1) ? 'title' : 'titles';
        }

        switch ($this->acwcOpenAiTyp) {
            case 'about_info':
                return "write a artical about ";
                break;
            case 'get_titles':
                return "write different {$acwcOpenaiCgvalue} blog {$acwcOpenaiTxt} about ";
                break;
            default:
                return "write blog post in detailed about ";
        }
    }


    public function acwc_last_content()
    {
        $acwcOpenAiVal = "";
        if ($this->acwcOpenAiTyp == 'about_info'){
            $acwcOpenAiVal = " topic wise and long.";
        }

        $lang = (!empty(get_option('acwc_set_org_language'))) ? esc_attr(get_option('acwc_set_org_language')) : 'en';
        $acwcOpenAiLang = ' Write in "'.esc_attr($lang).'" language.';

        switch ($this->acwcOpenAiTyp) {
            case 'about_info':
                return $acwcOpenAiVal . $acwcOpenAiLang;
                break;
            default:
                return ' '.$acwcOpenAiLang;
        }
    }



}