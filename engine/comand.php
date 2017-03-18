<?php

    if(!defined('uphp')) exit;


    /* программные Комманды */

    if( isset($_REQUEST['c']) ) {


        /*  Обновление данных пользователя [ с : 'update_user', user : '1' ]
            $.post('/php/u.php', {c:'update_user',user:1490}, function(x){
                console.log(x);
            })
        -------------------------------------------------------------------------------*/

        if( $_POST['c'] == 'update_user' && $uid != 0 ) {
            if( !session('update_user') ) exit('Много частых запросов.');

            $update_id = $___isAdmin ? ( $_POST['user'] ? $_POST['user'] : $uid) : $uid;
            $update    = $user->updateUser( $update_id );

            exit( $update ? $update_id.' обновлён!' : 'Ошибка обновления '.$update_id );
        }

    }


    /* Комманды плагинов */

    if( isset($_REQUEST['m']) ) {


        /*  Статистика
        -------------------------------------------------------------------------------*/

        if( in_array('stat_write', $___modules) ) {
            $return['stat_write'] = writeStatistic();
        }

        if( in_array('stat_online', $___modules) ) {
            $return['stat_online'] = getOnline();
        }

        if( in_array('stat_week', $___modules) ) {
            $return['stat_week'] = getStatisticWeek();
        }

        if( in_array('stat_day', $___modules) ) {
            $return['stat_day'] = getStatisticDay();
        }


    }


?>