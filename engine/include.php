<?php

    if(!defined('uphp')) exit;

    // подгружаем конфиг
    include 'data/config.php';

    // подгружаем функции
    include 'data/functions.php';



    /*  Классы
    ------------------------------------------------------------------------------------*/

    // подгружаем класс работы с CURL
    include 'data/classes/Curl.php';

    // подгружаем класс работы с базой данных
    include 'data/classes/Database.php';

    // подгружаем класс работы с пользователями
    include 'data/classes/User.php';

    // подгружаем класс работы с шаблонами
    include 'data/classes/Template.php';



    /*  Плагины
    ------------------------------------------------------------------------------------*/

    // подгружаем данные текущего пользователя
    include 'data/user_vars.php';

    // подгружаем плагины
    foreach (glob("data/plugins/*.php") as $filename) {
        include $filename;
    }



    /*  комманды
    ------------------------------------------------------------------------------------*/
    include 'data/comands.php';


?>