<?php

    if(!defined('uphp')) exit;


    class Page {

        public $template;
        public $option;

        function __construct( $default ) {

            if( !class_exists('CavusParser', false) ) return user_error("Класс \"CavusParser\" не объявлен. Выход.");

            $this->template = new CavusParser();
            $this->module = new Module();

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
            return $this->template ? $this->template->parse($this->option['page404'], $array ) : '';
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
                    $document = file_get_contents($filename);

                    $array[strtoupper($name['filename'])] = $document;
                }
            }

            return $array;
        }

        public function parse_g_template( $array, $template_array ) {

            foreach( $array as $key => $line) {
                $document = $this->template->parse($line,  $template_array);
                $array[$key] = $document;
            }

            return $array;
        }

        private function includeClousure($page, $global_array) {
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

        public function search_module_in_array( $array ) {

            function anon_cb($arr) {
                foreach($arr as $i => $item) {
                    $arr .= is_array($item) ? anon_cb($item) : $item;
                }
                return $arr;
            }

            $source = anon_cb($array);
            preg_match_all('/\$MODULE\_(.+?)\$/sm', $source, $output_array);

            // удаляем дубликаты
            $modules = array_unique($output_array[1]);

            return $modules;
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

            $template_array = array('PAGE_MODULE_NAME'=>'');

            // добавляем глобальные переменные
            $user_array = $this->parse_user_variable($option['global']);
            $template_array = array_merge($template_array, $user_array);

            // handler
            $include = isset($page['arr']) ? $page['arr'] : $this->includeClousure($page, $user_array);
            if( !$include ){return $this->error_404();}
            $template_array = array_merge($template_array,  $include);

            // получаем глобальные блоки
            $g_template_array = $this->get_g_template(array('array' => $template_array));
            $template_array = array_merge($template_array,  $g_template_array);

            // подгрузка модулей
            $modules_array = $this->search_module_in_array($template_array);

            $modules_return = $this->module->load($modules_array, 'MODULE_');
            $template_array = array_merge($template_array,  $modules_return);


            // парсим глобальные блоки
            $parse_g_template = $this->parse_g_template($g_template_array, $template_array);
            $template_array = array_merge($template_array,  $parse_g_template);

            // append скрипты
            $append = isset($template_array['append']) ? $template_array['append'] : array();
            if( isset($append) ) {
                if( is_array($append) ) {
                    foreach($append as $key => $value) {

                        $key = strtoupper($key);

                        if( !isset($template_array[$key]) || !is_array($value) ) continue;

                        foreach( $value as $arr_value) {
                            $template_array[$key] .= $arr_value;
                        }

                    }
                }
                unset($template_array['append']);
            }

            header("Content-type: text/html; charset=utf-8");
            header('HTTP/1.1 200 OK', true, 200);
            return $this->template->parse($template_url, $template_array);
        }
    }


?>