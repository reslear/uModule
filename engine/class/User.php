<?php

    if(!defined('uphp')) exit;

// array('USER_ID','USER_PROFILE','USER_GROUPID','USER_GROUPNAME','USER_USERNAME','USER_NAME','USER_GENDER','USER_AVATAR','USER_EMAIL','USER_ISVERIFIEDEMAIL','USER_HOMEPAGE','USER_COUNTRY','USER_CITY','USER_ICQ','USER_AOL','USER_MSN','USER_YAHOO','USER_BIRTHDAY','USER_ZODIAC','USER_AGE','USER_IPADDRESS','USER_BANNEDTILL','USER_RANK','USER_RANKNAME','USER_REPUTATION','USER_AWARDS','USER_REGTIMESTAMP','USER_LOGTIMESTAMP','USER_STATUS','USER_UNETPROFILE','USER_UNETID')


class User {

    public $db;

    function __construct() {
        $this->db = new Database('engine/database/userbase.db', 'users', $this->constArray(array(
            'INTEGER PRIMARY KEY NOT NULL',
            'varchar(255) NOT NULL',
            'varchar(255) NOT NULL',
            'INTEGER  NOT NULL',
            'varchar(255)',
            'varchar(255)',
        )));
    }

    public function writeUser( $writeApi ) {
        $write = $this->db->write( $writeApi );
        return $write ? $writeApi : false;
    }

    public function getUserApi( $id ) {

        if( $id == 0 ) {
            // addLog('Гостей запрещено добавлять!');
            return false;
        }

        $api_array = F::getApi( 'index/8-'.$id );
        if( !$api_array ) return false;

        $writeApi = $this->constArray( array(
            $api_array['USER_ID'],
            $api_array['USER_USERNAME'],
            $api_array['USER_GROUPNAME'],
            $api_array['USER_GROUPID'],
            $api_array['USER_AVATAR'],
            $api_array['USER_NAME'] )
        );

        return $this->gaps( $writeApi );
    }

    public function getUser( $id ) {
        $user = $this->db->getOne( 'uid = '. $id );

        if( !$user ) {
            $user = $this->getUserApi( $id );

            if ( $user ) {
                $user = $this->writeUser( $user );
            }
        }

        return $this->gaps( $user );
    }


    public function getUserInBd( $param, $string ) {
        $user = $this->db->getOne( $param .' = '. $string );
        return $this->gaps( $user );
    }

    public function updateUser( $id, $array = false ) {

        if( $array ) {
            $array['uid'] = $id;
            $update = $this->db->update( 'uid', $array );
        } else {
            $user = $this->getUserApi( $id );
            $update = $this->db->update( 'uid', $user );
        }

        return $update;
    }

    public function constArray( $array ) {



        $mask = array('uid', 'user', 'group_name', 'group_id', 'avatar', 'name');
        $temp = array_combine($mask, $array);

        return $temp;
    }

    public function gaps( $array ) {
        global $___config;

        if( !is_array($array) ) {
            user_error("'$array' - не массив!");
            return;
        }

        $new_array = array(4 => $___config['noavatar'], 5 => '');
        $array = array_filter(array_values($array)) + $new_array;

        ksort($array);

        return $this->constArray( $array );
    }

    public function registerNewColumn() {

    }
}

//$user = new User();

//$user->getUser(1546)

//$newuser = $user->addUser(1);
//$user->getUser(1)
//$user->getUserInBd('name', "'Евгений ReSLeaR-'");
//$user->updateUser(1);
//print_r($user->getUser(1));

?>