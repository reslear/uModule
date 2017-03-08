<?php

    /*

        скрипт: UniversalPHP
        автор : ReSLeaR- (Korchevskiy Evgeniy)
        ---
        http://vk.com/reslear
        http://upost.su/

        ---
        ~ ucoz PHP Version 5.2.12
    */

    define('uphp', true);
    session_start();
    date_default_timezone_set('Europe/Moscow');
    setlocale(LC_ALL, 'ru_RU');
    

    $___notjson = 1;

    if( isset($_REQUEST['error']) ) {
        ini_set('display_errors', 1);
        ini_set('display_startup_errors', 1);
        error_reporting(E_ALL);
    }


    //    $return['debug'][] =;
    $return = array();

    $___modules = isset($_REQUEST['m']) ? explode( ",", $_REQUEST['m'] ) : array();
    $___update = isset($_REQUEST['update']) ? explode( ",", $_REQUEST['update'] ) : array();


    include 'data/include.php';


    // Если админ панель
    if( isset($_REQUEST['admin']) && file_exists('data/admin/index.php') ) {
        include 'data/admin/index.php';
        exit;
    }

    // Начало
    if( count( $___modules ) ) {

        for( $i = 0; $i < count($___modules); $i++ ) {

            $___module = $___modules[$i];
            $___folder = 'modules/'.$___module.'/';

            $___index  = $___folder.'index.php';
            $___templates  = $___folder.'/templates/';

            if( file_exists( $___index ) ) {

                ob_start();

                try {
                    include($___index);
                } catch (Exception $e) {
                    echo 'Error: '.  $e->getMessage();
                }


                if( ob_get_length() ) {
                    $source = ob_get_contents();
                }

                ob_end_clean();

                if( !isset($return[$___module]['source']) && isset($source) ) $return[$___module]['source'] = $source;
            }
        }
    } else {

        // вывод главной страницы
        if( file_exists('modules/pages/index.php') ) {
            include 'modules/pages/index.php';
        }

    }

    // вывод
    if( count($return) ) {
        if( isset($_REQUEST['error']) ) {
            echo '<pre>'.print_r($return, true).'</pre>';
        } else {
            header('Content-Type: application/json; charset=utf-8');

            $return = clear_rnts($return);
            $return = unicode_decode($return);

            echo $return;
        }
    }

    /*
    if( isset($_REQUEST['error']) ){
        $db = new Database('modules/chat/base.db', 'chat');
        $fo = $db->addRow( array('last_date'=>'DATETIME') );
        echo 'БД '.($fo ? '' :'не').'обновлена!';
    }
    */

?>