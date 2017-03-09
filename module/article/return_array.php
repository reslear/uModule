<?php

    $id = $page['regex_result'][1];

    $page_array = array(
        'H1'=>'заголовк статьи!',
        'TITLE' => 'Статья имя',
        'CONTENT' => 'Статья №' .$id
    );

    if($id > 7) return false;

    return $page_array;

?>