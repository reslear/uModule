<?php
// fdsfd аа
class Module {

    function ___construct(){

    }

    public function load($___modules, $___isEcho = '', $native_request = array() ) {

        $___return = array();

        for( $i = 0; $i < count($___modules); $i++ ) {

            $___module = $___modules[$i];
            $___folder = 'module/'.$___module.'/';

            $___index  = $___folder.'index.php';
            $___templates  = $___folder.'/templates/';

            if( !empty($native_request) ){
                 // name => value
                foreach($native_request as $name => $value) {
                    $_GET[$name] = $value;
                }
            }

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

                if( !empty($___isEcho) ) {
                    $___return[$___isEcho.$___module] = $source;
                } else {
                    if( !isset($___return[$___module]['source']) && isset($source) ) {
                        $___return[$___module]['source'] = $source;
                    }
                }
            }
        }

        return $___return;
    }
}
?>