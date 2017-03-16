<?php

    if(!defined('uphp')) exit;


    class Page {

        public $template;
        public $option;

        function __construct( $default ) {

            if( !class_exists('Template', false) ) return user_error("Класс \"Template\" не объявлен. Выход.");

            $this->template = new Template();

            // дефольтные значения
            $this->option = array_merge(array(
                'url'   => 'main',
                'table' => array(),
                'patch' => 'engine/template/',
                'page404' => 'engine/template/404.html',
                'global'  => array()
            ), $default);

            $this->page_options = array(
                'regex'        => '',
                'regex_result' => array(),
                'template'     => 'main.html',
                'handler'      => ''
            );
        }

        public function get_in_table( $arr, $value) {

            foreach($arr as $key => $line) {

                if( isset($line['regex']) ) {

                    preg_match($line['regex'], $value, $match);

                    if( isset($match[0]) ) {
                        return array_merge($line, array('regex_result' => $match) );
                    }

                } else if( $key == $value ){
                    return $line;
                }

            }

            return false;
        }

        public function error_404() {
            header("HTTP/1.1 404 Not Found");
            $array = $this->parse_user_variable( $this->option['global'] );
            return $this->template ? $this->template->file($this->option['page404'], $array ) : '';
        }

        public function get_g_template( $default ) {

            // дефольтные значения
            $option = array_merge(array(
                'mask'   => '_*.html',
                'patch' => 'engine/template/',
                'array'  => array()
            ), $default);

            $array = array();
            $files = glob($option['patch'].$option['mask']);

            for( $i = 0; $i < count($files); $i++ ) {

                $filename = $files[$i];

                if( $filename ) {
                    $name = pathinfo($filename);
                    $document = $this->template->file($filename,  $option['array']);

                    $array[strtoupper($name['filename'])] = $document;
                }
            }

            return $array;
        }

        private function includeClousure($page) {
            return (include $page['handler']);
        }

        public function parse_user_variable( $array ) {

            $output = array();

            foreach($array as $_key => $line) {
                foreach( $line as $key => $value) {
                    $output[$_key.'_'.$key] = $value;
                }
            }

            return $output;
        }


        public function init( $default = array() ) {

            $option = $this->option;

            // Если не шаблона, выходим
            if( !$this->template ) {
                return $this->error_404();
            }

            $page = $this->get_in_table($option['table'], $option['url']);
            if( !$page ) return $this->error_404();

            // дефольтные значения для page
            $page = array_merge($this->page_options, $page);

            $template_url = $option['patch'].$page['template'];
            if( !file_exists($template_url) ) return $this->error_404();

            // handler
            $return_array = isset($page['arr']) ? $page['arr'] : $this->includeClousure($page);
            if( !$return_array ) {
                return $this->error_404();
            }

            // добавляем глобальные переменные
            $user_array = $this->parse_user_variable($option['global']);
            $return_array = array_merge($user_array, $return_array);


            // работа с глобальными блоками
            $globals_array = $this->get_g_template(array('array' => $return_array));
            $return_array = array_merge($return_array,  $globals_array);

            // append скрипты
            $append = isset($return_array['append']) ? $return_array['append'] : array();
            if( isset($append) ) {
                if( is_array($append) ) {
                    foreach($append as $key => $value) {

                        if( !isset($return_array[$key]) || !is_array($value) ) continue;

                        foreach( $value as $arr_value) {
                            $return_array[$key] .= $arr_value;
                        }

                    }
                }

                unset($return_array['append']);
//                $return_array = array_diff_key($return_array, array('append'));
//                unset($return_array['append']);
            }

            print_r($return_array);
            //return $this->template->file($template_url,  $return_array);
        }
    }


?>