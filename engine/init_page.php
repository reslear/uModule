<?php


    $universal_variables = array(
        'MY_USER' => array(
            'ID' => ucoz_getinfo('SITEUSERID'), // гость 0
        ),
        'PAGE' => array(
            'MAIN_URL' => '/php/'.$_REQUEST["___scriptdir"].$_REQUEST["___script"].'?p=',
        )
    );


    $table = array(
        'main' => array(
            'handler'  => 'engine/template/main_handler.php'
        ),
        'article_id' => array(
            'regex'    => '/^(article|script)(?:\/|\/(\d+|add))?(?:\/)?(edit|)?$/',
            'handler'  => 'module/article/page.handler.php'
        ),
    );



    $page = new Page(array(
        'url'    => $___page,
        'table'  => $table,
        'global' => $universal_variables
    ));
    $doc = $page->init();

    echo $doc;


?>