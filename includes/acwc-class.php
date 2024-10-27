<?php

namespace ACWC;
/**
 * 
 */

use ACWCPromptContent\ACWCPromptContent;

require 'acwc-prompt-content-class.php';

class ACWC
{
	public function acwc_activator()
	{
		update_option( 'acwc_activation_date', time());
	}

    function acwc_check_json($str){
        $jsonData = json_decode($str);

        if(json_last_error() !== JSON_ERROR_NONE) {
            return false;
        } else {
            return true;
           }
        }


    function acwc_remove_chr($contData) {
        $cnt = strpos($contData, "<br><br>");
        return substr($contData, $cnt+2);
    }


	public function acwc_generate_title()
    {
        @ini_set('zlib.output_compression',0);
        @ini_set('implicit_flush',1);
        @ob_end_clean();


        header('Content-Type: text/event-stream');

  		//set model as per 
        $url = "https://api.openai.com/v1/engines/".ACWC_MODEL."/completions";
        $stream = false;
        if (isset($data['stream']) && $data['stream'] == true){
            $stream = true;
        }

        $main_title = isset($_POST['prompt']) && !empty($_POST['prompt']) ? sanitize_text_field($_POST['prompt']) : '';

        if(!empty(get_option('acwc_api_key')))
        {
            $getActionMod = (isset($_POST['actionMode'])) ? sanitize_text_field($_POST['actionMode']) : 1;

            $type = 'get_titles';
            if($getActionMod == 2)
            {
                $type = 'about_info';
            }

            $temperatureVal = (!empty(get_option('acwc_temperature'))) ? esc_attr(get_option('acwc_temperature')) : "0.5";
            $maxTokenVal = (!empty(get_option('acwc_max_token'))) ? esc_attr(get_option('acwc_max_token')) : "2000";
            $topPVal = (!empty(get_option('acwc_top_p'))) ? esc_attr(get_option('acwc_top_p')) : "1.0";
            $best_of = (!empty(get_option('acwc_best_of'))) ? esc_attr(get_option('acwc_best_of')) : "1.0";
            $frequencyPenaltyVal = (!empty(get_option('acwc_frequency_penalty'))) ? esc_attr(get_option('acwc_frequency_penalty')) : "0";
            $presencePenaltyVal = (!empty(get_option('acwc_presence_penalty'))) ? esc_attr(get_option('acwc_presence_penalty')) : "0";

            $stream = false;
            
            $getContent = new ACWCPromptContent($type);
            $contentTitle = $getContent->acwc_first_content() . $main_title . $getContent->acwc_last_content();

            $data = array(
                'prompt' =>  $contentTitle,
                'temperature' => floatval($temperatureVal),
                'max_tokens' => floatval($maxTokenVal),
                'top_p' => floatval($topPVal),
                'frequency_penalty' => floatval($frequencyPenaltyVal),
                'presence_penalty' => floatval($presencePenaltyVal),
                'stream'=> $stream
            );

            $response = wp_remote_post( $url, array(
                    'method' => 'POST',
                    'timeout' => 100,
                    'httpversion' => '1.0',
                    'headers' => array(
                        'Content-Type' => 'application/json',
                        'Authorization' => 'Bearer '.esc_attr(get_option('acwc_api_key')),
                    ),
                    'body' => json_encode( $data ),
                    'stream' => $stream
                )
            );


            if ($stream){
                $response = $response;
            }
            else{
                $response = wp_remote_retrieve_body($response);
            }

            $getData = '';
            $returnContent = '';
            if (isset($response) && !empty($response) && $this->acwc_check_json($response)){

                $jsonData = json_decode($response);
                if (isset($jsonData->choices)){
                    $getData = $this->acwc_remove_chr($jsonData->choices[0]->text);

                    if(!empty($getData))
                    {
                        if($getActionMod == 2)
                        {
                            $returnContent = '<div class="post_contant_container">
                                <div class="post_contant_container_header">
                                    <button class="acwc_copy_contant">Copy</button>
                                    <button class="acwc_insert_contant">Insert</button>
                                </div><div class="acwc_post_contant_container_body">';


                            $generatedDataArr = explode("\n\n", $getData);

                            for ($i=0; $i < count($generatedDataArr); $i++) { 

                                if(strpos($generatedDataArr[$i], "\n") !== false)
                                {
                                    $getSeparateContArr = explode("\n", $generatedDataArr[$i]);

                                    for ($j=0; $j < count($getSeparateContArr); $j++) { 

                                        if($j==0)
                                        {
                                            $returnContent .= '<h2>'.$getSeparateContArr[$j].'</h2>';
                                        }
                                        else
                                        {
                                            $returnContent .= '<p>'.$getSeparateContArr[$j].'</p>';
                                        }
                                    }
                                }
                                else
                                {
                                    $returnContent .= '<p>'.$generatedDataArr[$i].'</p>';
                                }
                            }

                            $returnContent .= '</div><div class="post_contant_container_footer"></div></div>';
                        }
                        else
                        {
                            $generatedDataArr = explode("\n", $getData);

                            if(isset($generatedDataArr[0]) && !empty($generatedDataArr[0]))
                            {
                                $returnContent = '<div class="acwc_post_title_container"><h2>Select The Title Which you Want to Write In Details.</h2><ol>';
                                for ($i=0; $i < count($generatedDataArr); $i++) { 

                                    $getTitleVal = (!empty($generatedDataArr[$i])) ? trim($generatedDataArr[$i]) : '';

                                    $getTitleVal = preg_replace('/^\d+\./', '', $getTitleVal);
                                    $getTitleVal = (!empty($getTitleVal)) ? trim($getTitleVal) : '';                                
                                    $getTitleVal = trim($getTitleVal,'"');
                                    if($getTitleVal != '')
                                    {
                                        $returnContent .= '<li class="acwc_post_title_container_li"><a href="#" data-title="'.$getTitleVal.'">'.$getTitleVal.'</a></li>';
                                    }
                                }

                                $returnContent .= '</ol></div>';
                            }
                            else
                            {
                                wp_send_json_error('undefined_error');       
                            }
                        }
                    }
                    else
                    {
                        wp_send_json_error('undefined_error');
                    }                    
                }
                else{
                    $respError = $jsonData->error->message;
                    if ($respError!==false){
                        wp_send_json_error($respError);
                    }
                    else{
                        wp_send_json_error('undefined_error');
                    }
                }
            }
            
            wp_send_json_success($returnContent);
            
            wp_die();       
             
        }
    }	
}