<?php

class Module {

    function ___construct(){

    }

    public function load($___modules, $___isEcho) {

        $return = array();

        for( $i = 0; $i < count($___modules); $i++ ) {

            $___module = $___modules[$i];
            $___folder = 'module/'.$___module.'/';

            $___index  = $___folder.'index.php';
            $___templates  = $___folder.'/templates/';

            if( file_exists( $___index ) ) {

                ob_start();

                try {
                    include($___index);
                } catch (Exception $e) {
                    echo 'Error: '.  $e->getMessage();
                }


                if( ob_get_length() ) {
                    $source = ob_get_contents();
                }

                ob_end_clean();

                if( isset($___isEcho) ) {
                    $return[$___isEcho.$___module] = $source;
                } else {
                    if( !isset($return[$___module]['source']) && isset($source) ) {
                        $return[$___module]['source'] = $source;
                    }
                }
            }
        }

        return $return;
    }
}
?>