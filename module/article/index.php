<?php

/* каталог статей */


    class Article {

        public static function write( $post ) {
            return isset($post) ? $post : 'Not!';
        }
    }

?>