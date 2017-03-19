<?php



    $module = new Module();
    $return = $module->load($___modules);

    // вывод
    if( count($return) ) {
        if( isset($_REQUEST['error']) ) {
            echo '<pre>'.print_r($return, true).'</pre>';
        } else {
            header( "HTTP/1.1 200 OK" );
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