<?php

    if(!defined('uphp')) exit;

    // результат
    $result = $page['regex_result'];

    if( !$result || !isset($result[1]) || !in_array($result[1], array('article', 'script', 'all') ) ) {
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
        'PAGE_MODULE_ID'    => $___module_id,
        'PAGE_MODULE_URI'   => $___is_edit ? 'edit' : '',
    );

    // добавлем скрипт
    $page_array['append']['_script'][] = file_get_contents('module/article/template/script.html');


    // если передан id, и вывод не всех для безопасности вроде напр.: /all/85
    if( $___module_id && $___module_name !== 'all' ) {

        ### Если добавление
        if( $___is_add ){

            // проверка прав
            $check_right = F::check_right( array( 'uid' => array(1) ) );

            if( $check_right ) {

                $arr = $this->module->load(array('article'), '', array('add'=>$___module_name));

                $page_array['TITLE'] = 'Добавление нового '.$page_array['PAGE_MODULE_TITLE'];
                $page_array['CONTENT'] = $arr['article']['source'];

                $page_array['append']['_head'][] = '<!-- google captha <script src=""></script> -->';

            } else {
                $page_array['CONTENT'] = 'Вы не можете добавлять материалы.';
            }


        } else {

            // параметры для загрузки модуля
            $get_params = array('type'=>'one', 'id'=> $___module_id );
            if($___is_edit) $get_params['edit'] = 1;

            // подгрузка модуля
            $arr = $this->module->load(array('article'), '', $get_params);

            ### если редактирование
            if($___is_edit) {
                $page_array['TITLE'] = 'Редактирование '.$page_array['PAGE_MODULE_TITLE'].' '.$___module_id;
                $page_array['CONTENT'] = !empty($arr['article']) ? $arr['article'] : 'такой страицы несуществует';

            } else {

                ### если вывод материала

                $page_array['TITLE'] = $page_array['PAGE_MODULE_TITLE'].' '.$___module_id;
                $page_array['CONTENT'] = $arr['article']['source'];

            }

        }

    } else {

        ### Если вывод всех

        $category = $___module_name === 'all' ? '' : $___module_name;
        $arr = $this->module->load(array('article'), '', array('type'=>'all','cat'=> $category));

        $page_array['TITLE'] = 'Все';
        $page_array['CONTENT'] = isset($arr['article']) ? $this->template->parse($arr['article']['source'], $global_array) : '';
    }

    return $page_array;

?>