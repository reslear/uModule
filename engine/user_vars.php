<?php

    // переменные 
    if(!defined('uphp')) exit;

    $uid = function_exists( 'ucoz_getinfo' ) ?  ucoz_getinfo('SITEUSERID') : 0;
    
    $___isGuest = $uid == 0 ? true : false;

    $user  = new User();

    $___user    = $___isGuest ? false : $user->getUser( $uid );
    $___isAdmin = $___isGuest ? false : in_array($___user['group_id'], $___config['admin'] );


?>