<?php


    $___notjson = 1;
    include 'engine/classes/Template.php';

    $table = array(
        'main' => array(
            'template' => 'main',
            'arr' => array('H1'=>'заголовк главной страницы!','TITLE' => 'Главная страница')
        ),
        'article/\d+' => array(
            'template' => 'main',
            'arr' => array('H1'=>'Хер!','TITLE' => 'Педик')
        ),
    );

    $url = isset($_GET['u']) ? $_GET['u'] : 'main';
    $page = get_in_table($table, $url);
    $template_file = 'page/template/'.$page['template'].'.htm';

    if( !$page || !file_exists($template_file) ){
        exit('404');
    }

    $template = new Template();
    $document = $template->init($template_file, $page['arr']);

    echo $document;

    /* Функции
    ---------------------------------------------------------------------------------- */
    function get_in_table( $arr, $value) {

        foreach($arr as $key => $line) {
            if( $key == $value ){
                return $line;
            }
        }

        return false;
    }

        ini_set('display_errors', 1);
        ini_set('display_startup_errors', 1);
        error_reporting(E_ALL);


?>