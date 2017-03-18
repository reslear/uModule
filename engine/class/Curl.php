<?php

    if(!defined('uphp')) exit;   


class Curl {

    var $ch;
    var $options;
    var $header;

    function __construct( $url ) {

        $this->ch = curl_init();
        $file = "engine/database/cookies.txt";

        $protocol = stripos($_SERVER['SERVER_PROTOCOL'],'https') === true ? 'https://' : 'http://';

        $temp_url = parse_url($url);
        $url = empty( $temp_url['scheme'] ) ? $protocol.$_SERVER['HTTP_HOST'].$url : $url;

        $this->options = array(
            CURLOPT_URL            => $url,
            CURLOPT_HEADER         => 0,
            CURLOPT_USERAGENT      => 'php bot',
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_NOBODY         => 0,
            CURLOPT_FOLLOWLOCATION => 0,
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_BINARYTRANSFER => 0,
            CURLOPT_TIMEOUT        => 15,
            CURLOPT_COOKIESESSION  => 1,
            CURLOPT_COOKIEFILE     => $file,
            CURLOPT_COOKIEJAR      => $file
        );

    }

    public function init() {

        curl_setopt_array($this->ch, $this->options);
        $result = curl_exec($this->ch);

        if (!curl_errno($this->ch)) {
            $this->header = curl_getinfo($this->ch);
            return $result;
        } else {
            addLog('Ошибка Curl');
            return false;
        }
    }

    public function getHeader() {
        return count($this->header) ? $this->header : false;
    }

    public function get() {
        return $this->init();
    }

    public function post( $post = array() ) {

        $this->options[CURLOPT_POST] = 1;
        $this->options[CURLOPT_POSTFIELDS] = http_build_query( $post );

        return $this->init();
    }

    function __destruct() {
        curl_close($this->ch);
    }
}


?>