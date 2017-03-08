<?php

    //s


    class Template {

        public $parents = array();
        public $array;

        private function runCondition( $condition ) {

            ob_start();
            echo eval("return $condition;");
            $result = ob_get_clean();

            if( strpos($result, 'error') !== false ) {
                user_error("Ошибка, при парсе условия \"$condition\"");
                return false;
            } else {
                return $result;
            }

        }

        private function checkCondition( $condition, $content ) {

            $result = $this->runCondition( $condition );
            $block = explode( '<?else?>', $content );

            return $result ? $block[0] : ( isset($block[1]) ? $block[1] : '');
        }

        public function check_var( $matches) {
            return isset($this->array[$matches[1]]) ? '$this->array["'.$matches[1].'"]' : '';
        }

        private function parseBlocksRecursive( $template ) {

            preg_match_all("/<\?if\((.+?)\)\?>((?>(?R)|.)*?)<\?endif\?>/s", $template, $output, PREG_OFFSET_CAPTURE);

            $blocks_outer = array_reverse($output[0]);
            $conditions = array_reverse($output[1]);
            $blocks_inner = array_reverse($output[2]);

//            if( empty($blocks_inner) ) return false;

            foreach( $blocks_inner as $i => $block ) {

                $outer = $blocks_outer[$i];

                $temp = $this->parseBlocksRecursive( $block[0] );
                $parsed_block = $this->checkCondition($conditions[$i][0], $temp);

//                echo '!'.$temp.'!';

                $template = substr_replace( $template, $parsed_block, $outer[1], strlen($outer[0]) );

            }

            return $template;

        }

        public function init( $file, $array = array() ){
            $this->array = $array;

            $html = implode( '', file( $file ) );

            $html = preg_replace_callback('/\$(.+?)\$/', array($this, 'check_var'), $html);
            $output = $this->parseBlocksRecursive( $html );

            foreach( $this->array as $key => $value ) {
                $output = str_replace( '$this->array["'.$key.'"]', $value, $output);
            }

            return $output;
        }
    }

/* OLDDDDDDD

    class Template {

        public $array;
        public $template;

        public function init( $file, $array ) {

            $file = implode( '', file( $file ) );
            $this->array = $array;

            $template_file = preg_replace_callback('/\$(.+?)\$/', array($this, 'check_var'), $file);
            $template_file = preg_replace_callback('/<\?if\((.+?)\)\?>(.*?)<\?endif\?>/su', array($this, 'check'), $template_file);

            foreach( $this->array as $key => $value ) {
                $template_file = str_replace( '$this->array["'.$key.'"]', $value, $template_file);
            }

            $template_file = preg_replace('/<\?if\((.+?)\)\?>(.*?)<\?endif\?>/su', '', $template_file);

            return $template_file;

        }

        public function check( $matches ) {

            $matches[1] = trim( $matches[1] );

            if( isset($matches[1]) && $matches[1] !='' ) {
                $else = explode( '<?else?>', $matches[2] );
                return @eval('return '.$matches[1].';') ? $else[0] : $else[1];
            }

        }

        public function check_var( $matches) {
            return isset($this->array[$matches[1]]) ? '$this->array["'.$matches[1].'"]' : '';
        }

    }

*/
?>