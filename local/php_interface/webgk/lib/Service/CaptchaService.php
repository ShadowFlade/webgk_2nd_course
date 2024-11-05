<?php

namespace Webgk\Service;


class CaptchaService
{
    public $IS_VALID = false;
    private $LOG_FILE = 'logs/captcha/captchaBotLog.log';
    private $SECRET_KEY;
    private $MIN_SCORE = 0.5;
    private $RECAPTCHA_URL = "https://www.google.com/recaptcha/api/siteverify";

    public function __construct($data)
    {
        $this->IS_VALID = $this->validate($data);
        $this->SECRET_KEY = CAPTCHA_SECERET;
    }

    public function validate($data)
    {
        if (!isset($data['recaptcha_response'])) return;
        $recaptcha_params = [
            'secret' => $this->SECRET_KEY,
            'response' => $data['recaptcha_response'],
            'remoteip' => $_SERVER['REMOTE_ADDR'],
        ];

        $ch = curl_init($this->RECAPTCHA_URL);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $recaptcha_params);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        $response = curl_exec($ch);
        if (!empty($response)) {
            $decoded_response = json_decode($response, true);
        }
//        $this->log(['score' => $decoded_response], false);

        if (!empty($decoded_response) && $decoded_response['success'] && $decoded_response['score'] > $this->MIN_SCORE) {
            // $this->log("Captcha validation: success " . $decoded_response['score'], false);

            return true;
        } else {

            $this->log([
                "Captcha validation failed",
                'date' => $data['date'],
                'success' => $decoded_response['success'],
                'score' => $decoded_response['score']
            ],
                false);

            return false;
        }
    }

    public function log($data, $clear = true)
    {


        $dataLog = (is_object($data) || is_array($data) || is_resource($data)
                ? json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) : $data) . PHP_EOL;

        file_put_contents($this->LOG_FILE, $dataLog,
            (!$clear)
                ? FILE_APPEND : 0);
    }

}