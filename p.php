<?php


    $___notjson = 1;

    include 'engine/classes/Template.php';
    $template = new Template();

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

    $page = get_in_table($table, isset($_GET['u']) && trim($_GET['u']) != '' ? $_GET['u'] : 'main' );
    $template_file = 'engine/template/'.$page['template'];

    if( !$page || !file_exists($template_file) || !file_exists($template_file) ){
        exit('404');
    }

    $array = $page['arr'] ? $page['arr'] : include $page['handler'];
    if( !$array ){exit('404');}

    $document = $template->init($template_file,  $array);
    echo $document;

    /* Функции
    ---------------------------------------------------------------------------------- */
    function get_in_table( $arr, $value) {

        foreach($arr as $key => $line) {

            if( $line['regex'] ) {

                preg_match($line['regex'], $value, $match);

                if( $match[0] ) {
                    return array_merge($line, array('regex_result' => $match) );
                }

            } else if( $key == $value ){
                return $line;
            }

        }

        return false;
    }

        ini_set('display_errors', 1);
        ini_set('display_startup_errors', 1);
        error_reporting(E_ALL);


?>