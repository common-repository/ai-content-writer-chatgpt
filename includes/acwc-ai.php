<?php
namespace ACWC;
if ( ! defined( 'ABSPATH' ) ) exit;
class ACWC_Url
{
    const ORIGIN = 'https://api.openai.com';
    const API_VERSION = 'v1';
    const OPEN_AI_URL = self::ORIGIN . "/" . self::API_VERSION;

    public static function completionURL(string $engine): string
    {
        return self::OPEN_AI_URL . "/engines/$engine/completions";
    }

    public static function completionsURL(): string
    {
        return self::OPEN_AI_URL . "/completions";
    }

    public static function editsUrl(): string
    {
        return self::OPEN_AI_URL . "/edits";
    }

    public static function searchURL(string $engine): string
    {
        return self::OPEN_AI_URL . "/engines/$engine/search";
    }

    public static function enginesUrl(): string
    {
        return self::OPEN_AI_URL . "/engines";
    }

    public static function engineUrl(string $engine): string
    {
        return self::OPEN_AI_URL . "/engines/$engine";
    }

    public static function classificationsUrl(): string
    {
        return self::OPEN_AI_URL . "/classifications";
    }

    public static function moderationUrl(): string
    {
        return self::OPEN_AI_URL . "/moderations";
    }

    public static function filesUrl(): string
    {
        return self::OPEN_AI_URL . "/files";
    }

    public static function fineTuneUrl(): string
    {
        return self::OPEN_AI_URL . "/fine-tunes";
    }

    public static function chatUrl(): string
    {
        return self::OPEN_AI_URL . "/chat/completions";
    }

    public static function fineTuneModel(): string
    {
        return self::OPEN_AI_URL . "/models";
    }

    public static function answersUrl(): string
    {
        return self::OPEN_AI_URL . "/answers";
    }

    public static function imageUrl(): string
    {
        return self::OPEN_AI_URL . "/images";
    }

    public static function transcriptionsUrl(): string
    {
        return self::OPEN_AI_URL . "/audio/transcriptions";
    }

    public static function translationsUrl(): string
    {
        return self::OPEN_AI_URL . "/audio/translations";
    }

    public static function embeddings(): string
    {
        return self::OPEN_AI_URL . "/embeddings";
    }
}

if (!class_exists('\\ACWC\\ACWCGPT')){
    class ACWCGPT
    {
        private  static $instance = null ;
        private $engine = "davinci";
        private $model = "text-davinci-003";
        private $headers;
        public $response;

        private $timeout = 200;
        private $stream_method;

        public static function get_instance()
        {
            if ( is_null( self::$instance ) ) {
                self::$instance = new self();
            }
            return self::$instance;
        }

        public function acwc()
        {
            if(!empty(get_option('acwc_api_key'))){
                add_action('http_api_curl', array($this, 'filterCurlForStream'));
                $this->headers = [
                    'Content-Type' => 'application/json',
                    'Authorization' => 'Bearer '.get_option('acwc_api_key'),
                ];
                
				$this->acwc_max_token = get_option('acwc_max_token');
				$this->acwc_temperature = get_option('acwc_temperature');
				$this->acwc_frequency_penalty = get_option('acwc_frequency_penalty');
				$this->acwc_presence_penalty = get_option('acwc_presence_penalty');
				
                return $this;
            }
            else return false;
        }

        public function filterCurlForStream($handle)
        {
            if ($this->stream_method !== null){
                curl_setopt($handle, CURLOPT_SSL_VERIFYPEER, false);
                curl_setopt($handle, CURLOPT_SSL_VERIFYHOST, false);
                curl_setopt($handle, CURLOPT_WRITEFUNCTION, function ($curl_info, $data) {
                    return call_user_func($this->stream_method, $this, $data);
                });
            }
        }

        public function listModels()
        {
            $url = ACWC_Url::fineTuneModel();

            return $this->sendRequest($url, 'GET');
        }

        public function retrieveModel($model)
        {
            $model = "/$model";
            $url = ACWC_Url::fineTuneModel() . $model;

            return $this->sendRequest($url, 'GET');
        }

        public function setResponse($content="")
        {
            $this->response = $content;
        }

        public function complete($opts)
        {
            $engine = $opts['engine'] ?? $this->engine;
            $url = ACWC_Url::completionURL($engine);
            unset($opts['engine']);

            return $this->sendRequest($url, 'POST', $opts);
        }

        public function completion($opts, $stream = null)
        {
            if ($stream != null && array_key_exists('stream', $opts)) {
                if (! $opts['stream']) {
                    throw new \Exception(
                        'Please provide a stream function.'
                    );
                }
                $this->stream_method = $stream;
            }

            $opts['model'] = $opts['model'] ?? $this->model;
            $url = ACWC_Url::completionsURL();

            return $this->sendRequest($url, 'POST', $opts);
        }

        public function chat($opts, $stream = null)
        {
            if ($stream != null && array_key_exists('stream', $opts)) {
                if (! $opts['stream']) {
                    throw new \Exception(
                        'Please provide a stream function.'
                    );
                }
                $this->stream_method = $stream;
            }

            $opts['model'] = $opts['model'] ?? $this->model;

            $url = ACWC_Url::chatUrl();
            return $this->sendRequest($url, 'POST', $opts);
        }

        public function transcriptions($opts)
        {
            $url = ACWC_Url::transcriptionsUrl();
            return $this->sendRequest($url, 'POST', $opts);
        }

        public function translations($opts)
        {
            $url = ACWC_Url::translationsUrl();
            return $this->sendRequest($url, 'POST', $opts);
        }

        public function createEdit($opts)
        {
            $url = ACWC_Url::editsUrl();

            return $this->sendRequest($url, 'POST', $opts);
        }

        public function image($opts)
        {
            $url = ACWC_Url::imageUrl() . "/generations";

            return $this->sendRequest($url, 'POST', $opts);
        }

        public function imageEdit($opts)
        {
            $url = ACWC_Url::imageUrl() . "/edits";

            return $this->sendRequest($url, 'POST', $opts);
        }

        public function createImageVariation($opts)
        {
            $url = ACWC_Url::imageUrl() . "/variations";

            return $this->sendRequest($url, 'POST', $opts);
        }

        public function search($opts)
        {
            $engine = $opts['engine'] ?? $this->engine;
            $url = ACWC_Url::searchURL($engine);
            unset($opts['engine']);

            return $this->sendRequest($url, 'POST', $opts);
        }

        public function answer($opts)
        {
            $url = ACWC_Url::answersUrl();
            return $this->sendRequest($url, 'POST', $opts);
        }

        public function classification($opts)
        {
            $url = ACWC_Url::classificationsUrl();

            return $this->sendRequest($url, 'POST', $opts);
        }

        public function moderation($opts)
        {
            $url = ACWC_Url::moderationUrl();

            return $this->sendRequest($url, 'POST', $opts);
        }

        public function uploadFile($opts)
        {
            $url = ACWC_Url::filesUrl();

            return $this->sendRequest($url, 'POST', $opts);
        }

        public function retrieveFile($file_id)
        {
            $file_id = "/$file_id";
            $url = ACWC_Url::filesUrl() . $file_id;

            return $this->sendRequest($url, 'GET');
        }

        public function retrieveFileContent($file_id)
        {
            $file_id = "/$file_id/content";
            $url = ACWC_Url::filesUrl() . $file_id;

            return $this->sendRequest($url, 'GET');
        }

        public function deleteFile($file_id)
        {
            $file_id = "/$file_id";
            $url = ACWC_Url::filesUrl() . $file_id;

            return $this->sendRequest($url, 'DELETE');
        }

        public function createFineTune($opts)
        {
            $url = ACWC_Url::fineTuneUrl();

            return $this->sendRequest($url, 'POST', $opts);
        }

        public function listFineTunes()
        {
            $url = ACWC_Url::fineTuneUrl();

            return $this->sendRequest($url, 'GET');
        }

        public function retrieveFineTune($fine_tune_id)
        {
            $fine_tune_id = "/$fine_tune_id";
            $url = ACWC_Url::fineTuneUrl() . $fine_tune_id;

            return $this->sendRequest($url, 'GET');
        }

        public function cancelFineTune($fine_tune_id)
        {
            $fine_tune_id = "/$fine_tune_id/cancel";
            $url = ACWC_Url::fineTuneUrl() . $fine_tune_id;

            return $this->sendRequest($url, 'POST');
        }

        public function listFineTuneEvents($fine_tune_id)
        {
            $fine_tune_id = "/$fine_tune_id/events";
            $url = ACWC_Url::fineTuneUrl() . $fine_tune_id;

            return $this->sendRequest($url, 'GET');
        }

        public function deleteFineTune($fine_tune_id)
        {
            $fine_tune_id = "/$fine_tune_id";
            $url = ACWC_Url::fineTuneModel() . $fine_tune_id;

            return $this->sendRequest($url, 'DELETE');
        }

        public function engines()
        {
            $url = ACWC_Url::enginesUrl();

            return $this->sendRequest($url, 'GET');
        }

        public function engine($engine)
        {
            $url = ACWC_Url::engineUrl($engine);

            return $this->sendRequest($url, 'GET');
        }

        public function embeddings($opts)
        {
            $url = ACWC_Url::embeddings();

            return $this->sendRequest($url, 'POST', $opts);
        }

        public function setTimeout(int $timeout)
        {
            $this->timeout = $timeout;
        }

        public function create_body_for_file($file, $boundary)
        {
            $fields = array(
                'purpose' => 'fine-tune',
                'file' => $file['filename']
            );

            $body = '';
            foreach ($fields as $name => $value) {
                $body .= "--$boundary\r\n";
                $body .= "Content-Disposition: form-data; name=\"$name\"";
                if ($name == 'file') {
                    $body .= "; filename=\"{$value}\"\r\n";
                    $body .= "Content-Type: application/json\r\n\r\n";
                    $body .= $file['data'] . "\r\n";
                } else {
                    $body .= "\r\n\r\n$value\r\n";
                }
            }
            $body .= "--$boundary--\r\n";
            return $body;
        }

        public function create_body_for_audio($file, $boundary, $fields)
        {
            $fields['file'] = $file['filename'];
            unset($fields['audio']);
            $body = '';
            foreach ($fields as $name => $value) {
                $body .= "--$boundary\r\n";
                $body .= "Content-Disposition: form-data; name=\"$name\"";
                if ($name == 'file') {
                    $body .= "; filename=\"{$value}\"\r\n";
                    $body .= "Content-Type: application/json\r\n\r\n";
                    $body .= $file['data'] . "\r\n";
                } else {
                    $body .= "\r\n\r\n$value\r\n";
                }
            }
            $body .= "--$boundary--\r\n";
            return $body;
        }

        public function listFiles()
        {
            $url = ACWC_Url::filesUrl();

            return $this->sendRequest($url, 'GET');
        }

        private function sendRequest(string $url, string $method, array $opts = [])
        {
            $post_fields = json_encode($opts);
            if (array_key_exists('file', $opts)) {
                $boundary = wp_generate_password(24, false);
                $this->headers['Content-Type'] = 'multipart/form-data; boundary='.$boundary;
                $post_fields = $this->create_body_for_file($opts['file'], $boundary);
            }
            elseif (array_key_exists('audio', $opts)) {
                $boundary = wp_generate_password(24, false);
                $this->headers['Content-Type'] = 'multipart/form-data; boundary='.$boundary;
                $post_fields = $this->create_body_for_audio($opts['audio'], $boundary, $opts);
            } else {
                $this->headers['Content-Type'] = 'application/json';
            }
            $stream = false;
            if (array_key_exists('stream', $opts) && $opts['stream']) {
                $stream = true;
            }
            $request_options = array(
                'timeout' => $this->timeout,
                'headers' => $this->headers,
                'method' => $method,
                'body' => $post_fields,
                'stream' => $stream
            );
            if($post_fields == '[]'){
                unset($request_options['body']);
            }
            $response = wp_remote_request($url,$request_options);
            if(is_wp_error($response)){
                return json_encode(array('error' => array('message' => $response->get_error_message())));
            }
            else{
                if ($stream){
                    return $this->response;
                }
                else{
                    return wp_remote_retrieve_body($response);
                }
            }
        }
    }
}
