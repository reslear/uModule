<?php

    /*!
        CavusParser (Conditions And Variables Ucoz Style Parser) v0.1 release
        (c) 2016 Korchevskiy Evgeniy (aka ReSLeaR-)
        ---
        vk.com/reslear | upost.su | github.com/reslear
        Released under the MIT @license.
    !*/


    class CavusParser {

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

?>