<?php

    /*
        universal php v0.0.1 (апр. 2017)
        ---------------------------------------------------------------------------------
        Автор и разработчик: ReSLeaR- (Korchevskiy Evgeniy) vk.com/reslear | upost.su | github.com/reslear
        Разработчики:
            ...
        Тестирование:
            ...
        ---------------------------------------------------------------------------------
        Released under the MIT @license.
    */

    # ~ ucoz PHP Version 5.2.12

    define('uphp', true);
    session_start();
    date_default_timezone_set('Europe/Moscow');
    setlocale(LC_ALL, 'ru_RU');


    # Не выводить в формате json
    $___notjson = 1;


    # Отображение ошибок, если указан параметр "error" (напр.: /php/u.php?error )
    if( isset($_REQUEST['error']) ) {
        ini_set('display_errors', 1);
        ini_set('display_startup_errors', 1);
        error_reporting(E_ALL);
    }

    # подгружаем конфиг
    include 'engine/config.php';

    # Выходим если сайт закрыт      TODO: для групп
    if( $___config['site_off'] && ucoz_getinfo('SITEUSERID') !== 1 ) {
        include 'engine/template/off.html';
        exit;
    }


    # Переменные адреса Page
    $___page = isset($_REQUEST['p']) ? ( empty($_REQUEST['p']) ? 'main' : $_REQUEST['p'] ) : '';

    # Переменные адреса Module
    $___modules = isset($_REQUEST['m']) ? explode( ",", $_REQUEST['m'] ) : array();
    $___update = isset($_REQUEST['update']) ? explode( ",", $_REQUEST['update'] ) : array();


    # Запускаем функционал
    include 'engine/include.php';


    # Если админ панель
    if( isset($_REQUEST['admin']) && is_file('engine/admin/index.php') ) {
        include 'engine/admin/index.php';

    # Если Page
    } else if( !empty($___page) ) {
        include 'engine/init_page.php';

    # Если Module
    } else if( count($___modules) ) {
        include 'engine/init_module.php';

    # Если phpinfo
    } else if( isset($_REQUEST['info']) ) {
        phpinfo();
    }

    # Выходим
    exit;

?>