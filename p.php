<?php

    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);

    $___notjson = 1;

    include_once 'engine/classes/Template.php';

    class Page {

        public $template;

        function __construct() {

            if( class_exists('Template', false) ){
                $this->template = new Template();
            }
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
            return '404';
        }

        public function get_g_template( $mask = '_*', $array = array() ) {

            $files = glob('engine/template/'.$mask.'.html');

            for( $i = 0; $i < count($files); $i++ ) {

                $filename = $files[$i];

                if( $filename ) {
                    $name = pathinfo($filename);
                    $array[strtoupper($name['filename'])] = file_get_contents($filename);
                }
            }

            return $array;
        }


        public function init( $_url = '', $table = array(), $_tmpl_patch = 'engine/template/' ) {

            // Если не шаблона, выходим
            if( !$this->template ) {
                return $this->error_404();
            }

            $url = isset($_url) && trim($_url) != '' ? $_url : 'main';
            $page = $this->get_in_table($table, $url);

            $tmpl_patch = $_tmpl_patch.$page['template'];

            if( !$page || !file_exists($tmpl_patch) ){
                return $this->error_404();
            }

            $return_array = isset($page['arr']) ? $page['arr'] : include $page['handler'];

            if( !$return_array ){
                return $this->error_404();
            }

            // работа с глобальными блоками
            $globals_array = $this->get_g_template();
            $return_array = array_merge($return_array,  $globals_array);

            return $this->template->init($tmpl_patch,  $return_array);
        }
    }


    $table = array(
        'main' => array(
            'template' => 'main.html',
            'arr' => array('H1'=>'заголовк главной страницы!','TITLE' => 'Главная страница')
        ),
        'article' => array(
            'regex' => '/article\/(\d+)$/i',
            'template' => 'main.html',
            'handler' => 'module/article/return_array.php'
        ),
    );

    $page = new Page();
    $doc = $page->init( $_GET['u'], $table );

    echo $doc;


?>