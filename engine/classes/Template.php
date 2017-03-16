<?php

    //s


    class Template {

        public $array;

        public function runCondition( $condition ) {

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

        public function checkCondition( $condition, $content ) {

            $result = $this->runCondition( $condition );
            $block = explode( '<?else?>', $content );

            return $result ? $block[0] : ( isset($block[1]) ? $block[1] : '');
        }

        public function check_var( $matches ) {
            return isset($this->array[$matches[1]]) ? '$this->array["'.$matches[1].'"]' : '';
        }

        private function parseBlocksRecursive( $template ) {

            preg_match_all("/<\?if\((.+?)\)\?>((?>(?R)|.)*?)<\?endif\?>/s", $template, $output, PREG_OFFSET_CAPTURE);

            $blocks_outer = array_reverse($output[0]);
            $conditions = array_reverse($output[1]);
            $blocks_inner = array_reverse($output[2]);

            foreach( $blocks_inner as $i => $block ) {

                $outer = $blocks_outer[$i];

                $temp = $this->parseBlocksRecursive( $block[0] );
                $parsed_block = $this->checkCondition($conditions[$i][0], $temp);

                $template = substr_replace( $template, $parsed_block, $outer[1], strlen($outer[0]) );

            }

            return $template;

        }

        private function init( $source, $array ){

            $this->array = $array;

            $html = preg_replace_callback('/\$(.+?)\$/', array($this, 'check_var'), $source);
            $output = $this->parseBlocksRecursive( $html );

            foreach( $this->array as $key => $value ) {
                if( is_array($value) ){
                    user_error('Внимание! Переменная передана в виде массива. Пропуск.');
                    // TODO: Записать переменную в файл лога (в связи с безопасностью);
                } else {
                    $output = str_replace( '$this->array["'.$key.'"]', $value, $output);
                }
            }

            return $output;
        }



        /* pub */
        public function file( $file_patch, $array = array() ){

            if( is_file($file_patch) ) {

                $source = file_get_contents($file_patch);
                return $this->init($source, $array);
            } else {

                user_error("Ошибка: отсутствует файл \"$file_patch\".");
            }
        }

        public function text( $source, $array = array() ){
            return $this->init($source, $array);
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