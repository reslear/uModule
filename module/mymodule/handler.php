<?php


    $modules = array('article', 'script');

    // результат
    $result = $page['regex_result'];

    if( !$result || !isset($result[1]) || !in_array($result[1], $modules) ) {
        return false;
    }

    $module_name = $result[1];
    $module_id   = isset($result[2]) ? $result[2] : null;

    // задам имя модуля и id
    $page_array = array(
        'PAGE_MODULE_NAME' => $module_name,
        'PAGE_MODULE_ID'   => $module_id
    );

    // обработка
    if( $module_id ) {

        if( $module_id > 7) return false;

        $page_array = array_merge(array(
            'TITLE' => 'id'.$module_id,
            'CONTENT' => $module_name == 'article' ? 'Статья'.$module_id : ($module_name == 'script' ? 'Скрипт'.$module_id : '')
        ), $page_array);

    } else {
        $page_array = array_merge(array(
            'TITLE' => 'Все',
            'CONTENT' =>  $module_name == 'article' ? 'Все статьи' :  ($module_name == 'script' ? 'Все скрипты' : '')
        ), $page_array);
    }

    return $page_array;

?>