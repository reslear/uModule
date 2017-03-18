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

                //echo "<b>$condition</b>";
                //user_error("Ошибка, при обработке условия. Пропущено.");

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

        public function replace_on_thisvar( $matches ) {
            $var_name = strtoupper($matches[1]);
            return isset( $this->array[$var_name] ) ? '$this->array["'.$var_name.'"]' : ($this->remove_empty_var ? '' : $matches[0]);
        }

        public function replace_this_tovar( $output ) {

            foreach( $this->array as $key => $value ) {

                if( is_array($value) ) continue;
                $output = str_replace( '$this->array["'.$key.'"]', $value, $output);
            }

            return $output;
        }

        public function diff_user_array( $user_array ) {

            $array = array();

            foreach($user_array as $key => $value ) {

                $key = strtoupper($key);
                $array[$key] = $value;
            }

            return $array;
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


        public function parse( $source, $array = array(), $remove_empty_var = true ) {

            // если файл
            if( strlen($source) <= 100 && is_file($source) ) {
                $source = file_get_contents($source);
            }


            $this->remove_empty_var = $remove_empty_var;
            $this->array = $this->diff_user_array($array);

            $html = preg_replace_callback('/\$(.+?)\$/', array($this, 'replace_on_thisvar'), $source);
            $output = $this->parseBlocksRecursive( $html );

            $output = $this->replace_this_tovar($output);

            return $output;
        }

    }

?>