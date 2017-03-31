<?php

    if(!defined('uphp')) exit;

    // id пользователи которые видят ошибки


    class F {

        static function check_right($uids, $error_text = 'У вас не достаточно прав') {
            global $___config;

            // по умолчанию админ id =1
            $id = ucoz_getinfo('SITEUSERID');
            $uids = $uids ? (is_array($uids) ? $uids : array($uids) ) : array(1);

            if( in_array($id, $uids, true) ){
                return true;
            }

            return $error_text;
        }

        /*  Функция по рекурсии заменяет значения первого массива на значения второго. */
        static function array_extend( $default_array, $ext_array ) {

            foreach($ext_array as $key => $value) {

                if( is_array($value) ) {

                    if( !isset($default_array[$key]) ) {
                        $default_array[$key] = $value;
                    } else {
                        $default_array[$key] = array_extend($default_array[$key], $value);
                    }

                } else {
                    $default_array[$key] = $value;
                }
            }
            return $default_array;
        }

        /* Функция для массовой проверки ключей в массиве, при отсутствии хотя-бы одно ключа или пустого значения - возвращает false  */
        static function check_isset_array( $check_array = array(), $array = array() ) {

            if( !count($check_array) || !count($array) ) return false;

            foreach($check_array as $i => $name ) {
                if( !isset($array[$name]) || empty($array[$name]) ) return false;
            }

            return true;
        }

        /* Функция для удаления из массива переносов, пробелов, отступов */
        static function clear_rnts( $array )  {

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

        /*  ФУНКЦИИ ДЛЯ РАБОТЫ С СЕССИЯМИ
        ------------------------------------------------------------------------------- */

        # Защита от частых запросов by ВэйДлин old upost
        function protect_session($name = '', $ban = 20){

            $name = 'uphp_session'.$name;
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



        /*  ФУНКЦИИ ДЛЯ РАБОТЫ С JSON
        ------------------------------------------------------------------------------- */
        static function unicode_decode($array){
            return preg_replace("/\\\\u([a-f0-9]{4})/e", "iconv('UCS-4LE','UTF-8',pack('V', hexdec('U$1')))", json_encode($array));
        }



        /*  ФУНКЦИИ ДЛЯ РАБОТЫ С API
        ------------------------------------------------------------------------------- */

        # Функция для полуения массива api
        static function getApi ( $apiurl ) {

            global $___config;

            $temp_url = parse_url($apiurl);
            $apiurl = empty($temp_url['scheme']) ? $___config['domain'].'/api/' . $apiurl : $apiurl ;

            if( isset($___config['api_key']) ) {
                $apiurl = $apiurl.'/?apikey='.$___config['api_key'];
            }


            $curl = new Curl( $apiurl );
            $data = $curl->get();

            $headers = $curl->getHeader();

            $curl_content_type = $headers['content_type'] ? strtolower($headers['content_type']) : '';

            if( strpos( $curl_content_type, 'xml') === false ) {

                user_error('getApi error. Документ не xml. Возможные причины: непраильный url, на сайте технические работы или отключён API. - <b>'.$apiurl.'</b>');
                return false;
            } else {
                $array = xmlrpc_decode($data, 'utf8');

                if( !count($array) ) {
                    user_error('getApi error. Массив пуст - <b>'.$apiurl.'</b>');
                    return false;
                }

                if( isset( $array['faultCode'] ) && $array['faultCode'] === 0 ){
                    user_error('getApi error. Получен faultCode: <b>'.$array['faultString'].'</b> - <b>'.$apiurl.'</b>');
                    return false;
                }

                return $array;
            }
        }



        /*  ФУНКЦИИ ДЛЯ РАБОТЫ С ЦИФРАМИ
        ------------------------------------------------------------------------------- */

        # Функция проверяет, является ли переменная числом
        static function has_int($value) {
            return ((int) $value == $value && $value > 0);
        }

        # Склонение числительных
        static function decl($n, $value, $printNumber = true){
            return ($printNumber ? $n." " : '').$n%10==1&&$n%100!=11?$value[0]:($n%10>=2&&$n%10<=4&&($n%100<10||$n%100>=20)?$value[1]:$value[2]);
        }



        /*  ФУНКЦИИ ДЛЯ РАБОТЫ С ФАЙЛАМИ
        ------------------------------------------------------------------------------- */

        # функция возвращает ответ скрипта
        static function include_return( $patch, $empty = array() ) {
            return is_file($patch) ? include $patch : $empty;
        }

        # функци получения файл с конвертацией в UTF8 без BOM
        static function get_file($patch) {

            if( !is_file($patch) ){
                user_error('Файл <b>'.$patch.'</b> не найден. return \'\'; ');
                return '';
            }

            $html = file_get_contents($patch);
            $html = str_replace("\xEF\xBB\xBF", '', $html);

            return $html;
        }

        static function log($text, $file = 'engine/database.txt') {

            $text = count($text) ? print_r( $text, true ) : $text;
            $write = file_put_contents( $file, date("Y-m-d H:i:s")."\t".$text.PHP_EOL, FILE_APPEND);

            return $write;
        }


        /*  ДЛЯ РАБОТЫ С ШАБЛОНАМИ
        ------------------------------------------------------------------------------- */

        // функция для вывода колонок           TODO: array_extend
        static function print_columns($user_array, $num = 2, $only_source = false, $t_item = '<div class="col col-%1$d">%2$s</div>', $t_parent = '<div class="cols">%s</div>') {

            $array = array();
            $source = '';

            if( empty($user_array) ) {
                return false;
            }

            foreach($user_array as $i => $item) {
                $array[$i % $num][] = $item;
            }

            foreach($array as $i => $item ) {
                $source .= sprintf($t_item, $i, implode('', $item));
            }

            return $only_source ? $source : sprintf($t_parent, $source);
        }

        // Функция возвращает человеко-понятное время. ( Автор: ReSLeaR-)
        static function smart_date( $_date, $y=false, $abb_year = false  ){

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
                'i' => $minutes.' минут'.F::decl($minutes, array('а','ы',''), 0).' назад',
                's' => $secunds.' секунд'.F::decl($secunds, array('а','ы',''), 0).' назад'
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




    }


?>