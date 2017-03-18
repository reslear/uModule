<?php

    if(!defined('uphp')) exit;

    // подгружаем функции
    include 'engine/function.php';



    /*  Классы
    ------------------------------------------------------------------------------------*/

    // подгружаем класс работы с CURL
    include 'engine/class/Curl.php';

    // подгружаем класс работы с базой данных
    include 'engine/class/Database.php';

    // подгружаем класс работы с пользователями
    include 'engine/class/User.php';

    // подгружаем класс работы с шаблонами
    include 'engine/class/CavusParser.php';

    // подгружаем класс Module
    include 'engine/class/Module.php';

    // подгружаем класс Page
    include 'engine/class/Page.php';


    /*  Плагины
    ------------------------------------------------------------------------------------*/

    // подгружаем данные текущего пользователя
    include 'engine/user_var.php';

    // подгружаем плагины
    $files = glob("engine/plugin/*.php");
    for( $i = 0; $i < count($files); $i++ ) {
        $filename = $files[$i];
        if($filename) include $filename;
    }



    /*  комманды
    ------------------------------------------------------------------------------------*/
    include 'engine/comand.php';


?>