<?php

    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);

    $___notjson = 1;

    define('uphp', true);

    include_once 'engine/classes/Template.php';
    include_once 'engine/classes/Page.php';

function array_extend($a, $b) {
    foreach($b as $k=>$v) {
        if( is_array($v) ) {
            if( !isset($a[$k]) ) {
                $a[$k] = $v;
            } else {
                $a[$k] = array_extend($a[$k], $v);
            }
        } else {
            $a[$k] = $v;
        }
    }
    return $a;
}


    $universal_variables = array(
        'MY_USER' => array(
            'ID' => 33, // гость 0
        ),
        'PAGE' => array(
            'MAIN_URL' => '/php/'.$_REQUEST["___scriptdir"].$_REQUEST["___script"].'?u=',
            'MODULE_NAME' => 'main'
        )
    );


    $table = array(
        'main' => array(
            'handler'  => 'module/mymodule/_main/handler.php'
        ),
        'article_id' => array(
            'regex'    => '/^(article|script)(?:\/|\/(\d+|add))?(?:\/)?(edit|)?$/',
            'handler'  => 'module/mymodule/handler.php'
        ),
    );

    $page = new Page(array(
        'url'    => isset($_GET['u']) && $_GET['u'] !== '' ? $_GET['u'] : 'main',
        'table'  => $table,
        'global' => $universal_variables
    ));
    $doc = $page->init();

    echo $doc;


?>