<?php

//    if(!defined('uphp')) exit;


    /* Функции для работы с файлами
    ================================================================================== */


    // функция возвращает ответ скрипта
    function file_return_include( $patch, $empty = array() ) {
        return is_file($patch) ? include $patch : $empty;
    }


    /* ыыыыыыы
    ================================================================================== */

    # Склонение числительных
    function decl($n, $forms, $isEchoNumber = true){
        return ($isEchoNumber ? $n." " : '').$n%10==1&&$n%100!=11?$forms[0]:($n%10>=2&&$n%10<=4&&($n%100<10||$n%100>=20)?$forms[1]:$forms[2]);
    }

    function smart_date( $_date, $y=false, $abb_year = false  ){

        $months = array(
            'January'   => array('Янв','Январь', 'Января'),
            'February'  => array('Фев','Февраль', 'Февраля'),
            'March'     => array('Мар','Март', 'Марта'),
            'April'     => array('Апр','Апрель', 'Апреля'),
            'May'       => array('Май','Май', 'Мая'),
            'June'      => array('Июн','Июнь', 'Июня'),
            'July'      => array('Июл','Июль', 'Июля'),
            'August'    => array('Авг','Август', 'Августа'),
            'September' => array('Сен','Сентябрь', 'Сентября'),
            'October'   => array('Окт','Октябрь', 'Октября'),
            'November'  => array('Ноя','Ноябрь', 'Ноября'),
            'December'  => array('Дек','Декабрь','Декабря')
        );

        $weeks = array(
            'Monday'    => array('Пн', 'Понедельник'),
            'Tuesday'   => array('Вт', 'Вторник'),
            'Wednesday' => array('Ср', 'Среда' ),
            'Thursday'  => array('Чт', 'Четверг'),
            'Friday'    => array('Пт', 'Пятница'),
            'Saturday'  => array('Сб', 'Суббота'),
            'Sunday'    => array('Вс', 'Воскресение')
        );

        $now = new DateTime();
        $frm = new DateTime($_date);

        if( $now->format('Y-m-d H:i:s') == $frm->format('Y-m-d H:i:s')){
            return 'Только что';
        }

        // вчера
        $yesterday = new DateTime( $now->format('Y-m-d H:i:s') );
        $yesterday->modify('-1 day');

        // interval
        $minutes = $now->format('i') - $frm->format('i');
        $secunds = $now->format('s') - $frm->format('s');

        $time = $frm->format('H:i');
        $mon_diff = $frm->format('d').' '.($abb_year ? key($months[$frm->format('F')]) : $months[$frm->format('F')][2]);
        $day_diff = $frm->format('d') == $yesterday->format('d') ? 'Вчера в' : ($now->format('W') == $frm->format('W') ?  $weeks[$frm->format('l')][1].',' : $mon_diff.',');


        // diff minute
        $minutes = str_pad($minutes, 2, '0', STR_PAD_LEFT);
        $minutes = substr($minutes, 0, 1) === '0' ? substr($minutes, 1, 1) : $minutes;

        // diff secund
        $secunds = str_pad($secunds, 2, '0', STR_PAD_LEFT);
        $secunds = substr($secunds, 0, 1) === '0' ? substr($secunds, 1, 1) : $secunds;

        $time_age = array(
            'Y' => $frm->format('d.m.Y, H:i'),
            'm' => $mon_diff.', '.$time,
            'd' => $day_diff.' '.$time,
            'H' => 'Сегодня в '.$time,
            'i' => $minutes.' минут'.decl($minutes, array('а','ы',''), 0).' назад',
            's' => $secunds.' секунд'.decl($secunds, array('а','ы',''), 0).' назад'
        );

        foreach( $time_age as $key => $line ){

            $_now = $now->format($key);
            $_frm = $frm->format($key);

            if( $_now <= $_frm ) {
                if( $_now < $_frm ) return false; // Временная заглушка на будущее
                continue;
            } else {
                return $line;
            }
        }

    }

    // получение нормального вида даты
    function get_date( $date = false, $template = 'd.m.Y H:i', $return_time = false ) {

        $today = strtotime( date("Y-m-d H:i") );
        $date = $date ? strtotime($date) : $today;

        $return_time = $return_time ? ' '.( is_string($return_time) ? $return_time : 'в').' '.date('H:i', $date ) : '';

        // Сегодня
        if( date('Y-m-d', $date ) == date('Y-m-d', $today) ) {
            return 'Сегодня'.$return_time;
        }

        // Вчера
        if( date('Y-m-d', $date ) == date('Y-m-d', strtotime('-1 day')) ) {
            return 'Вчера'.$return_time;
        }

        // Завтра
        if( date('Y-m-d', $date ) == date('Y-m-d', strtotime('+1 day')) ) {
            return 'Завтра'.$return_time;
        }

        return date( $template, $date );
    }

    function GetApi ( $apiurl ) {
        global $___config;

        $temp_url = parse_url($apiurl);
        $apiurl = ( empty( $temp_url['scheme'] ) ? $___config['domain'].'/api/' . $apiurl : $apiurl ) . ( $___config['getApiKey'] ? '/?apikey='.$___config['getApiKey'] : '' );


        $curl = new Curl( $apiurl );
        $data = $curl->get();

        $headers = $curl->getHeader();

        if( strpos($headers['content_type'], 'xml') === false ) {

            user_error('GetApi error - (Возможно отключён API) - получен не xml - '.$apiurl);
            return false;
        } else {
            $array = xmlrpc_decode($data, 'utf8');

            if( !count($array) ) {
                addLog('GetApi - массив пуст - '.$apiurl);
                return false;
            }

            if( isset( $array['faultCode'] ) && $array['faultCode'] === 0 ){
                addLog('GetApi - '.$array['faultString'].' - '.$apiurl);
                return false;
            }

            return xmlrpc_decode($data, 'utf8');
        }
    }

    # Защита от частых запросов by ВэйДлин old upost
    function session($name = 'uniphp', $ban = 20){

        $name = 'time_'.$name;
        $time = date('U');

        if(empty($_SESSION[$name])){
            $_SESSION[$name] = $time;
            return true;
        }

        if(($_SESSION[$name]+$ban-$time) <= 0){
            unset($_SESSION[$name]);
            return true;
        }

        return false;
    }


    function unicode_decode($array){
        return preg_replace("/\\\\u([a-f0-9]{4})/e", "iconv('UCS-4LE','UTF-8',pack('V', hexdec('U$1')))", json_encode($array));
    }

    function addLog( $text ) {
        global $___config;

        $text = count($text) ? print_r( $text, true ) : $text;

        if ( $___config['echoError'] ) {
            echo $text;
        } else {
            $text = file_put_contents( 'data/log.txt', date("Y-m-d H:i:s")."\t".$text.PHP_EOL, FILE_APPEND);
        }

        return $text;
    }


    function clear_rnts( $array )  {

        foreach( $array as $key => $line ) {

            if( is_array($line) && count($line) ) {
                $array[$key] = clear_rnts( $line );
            } else {
                $line = str_replace(PHP_EOL, '', $line);
                $array[$key] = preg_replace('#\t+|\s{2,}#', '', $line);
            }
        }

        return $array;
    }

?>