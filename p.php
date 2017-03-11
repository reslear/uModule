<?php

    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);

    $___notjson = 1;

    define('uphp', true);

    include_once 'engine/function.polyfill.php';
    include_once 'engine/classes/Template.php';
    include_once 'engine/classes/Page.php';

    $universal_variables = array(
        'MY_USER' => array(
            'ID' => 33, // гость 0
        ),
        'PAGE' => array(
            'URL' => '/php/'.$_REQUEST["___scriptdir"].$_REQUEST["___script"].'?u=',
        )
    );


    $table = array(
        'main' => array(
            'template' => 'main.html',
            'arr'      => array('H1'=>'заголовк главной страницы!','TITLE' => 'Главная страница')
        ),
        'article' => array(
            'regex'    => '/article\/(\d+)$/i',
            'template' => 'main.html',
            'handler'  => 'module/article/return_array.php'
        ),
    );

    $page = new Page(array(
        'url'    => $_GET['u'],
        'table'  => $table,
        'global' => $universal_variables
    ));
    $doc = $page->init();

    echo $doc;


?>