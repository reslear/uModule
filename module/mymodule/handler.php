<?php


    // результат
    $result = $page['regex_result'];

    if( !$result || !isset($result[1]) || !in_array($result[1], array('article', 'script') ) ) {
        return false;
    }

    $___module_name = $result[1];
    $___module_id   = isset($result[2]) && !empty($result[2]) ? $result[2] : null;
    $___is_add      = !ctype_digit($___module_id);
    $___is_edit     = isset($result[3]) && !empty($result[3]);

    // задам имя модуля и id
    $page_array = array(
        'PAGE_MODULE_NAME'  => $___module_name,
        'PAGE_MODULE_TITLE' => $___module_name == 'article' ? 'Статья' : ($___module_name == 'script' ? 'Cкрипт' : ''),
        'PAGE_MODULE_ID'    => $___module_id
    );

    // если передан id
    if( $___module_id ) {

        ### Если добавление
        if( $___is_add ){

            $page_array['TITLE'] = 'Добавление нового '.$page_array['PAGE_MODULE_TITLE'];
            $page_array['CONTENT'] = 'Шаблон добавления '.$page_array['PAGE_MODULE_TITLE'];
            $page_array['append']['_script'][] = '<script></script>';
            $page_array['append']['_script'][] = '<script></script>';
            $page_array['append']['_head'] = '<script></script>';  

        } else {

            // Проверка на наличие id
            if( $___module_id > 7) return false;

            ### если редактирование
            if($___is_edit) {

                $page_array['TITLE'] = 'Редактирование '.$page_array['PAGE_MODULE_TITLE'].' '.$___module_id;

            } else {

                ### если вывод материала

                $page_array['TITLE'] = $page_array['PAGE_MODULE_TITLE'].' '.$___module_id;
                $page_array['CONTENT'] = 'Шаблон '.$page_array['PAGE_MODULE_TITLE'].' '.$___module_id;

            }

        }

    } else {

        ### Если вывод всех

        $page_array['TITLE'] = 'Все';
        $page_array['CONTENT'] =  'Шаблон всех материалов модуля '.$page_array['PAGE_MODULE_TITLE'];
    }

    return $page_array;

?>