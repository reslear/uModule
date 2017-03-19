<?php

    if(!defined('uphp')) exit;

    class Database {
        public $PDO;
        public $table_name;
        public $user_query;
        public $end;

        function __construct( $db_name, $table_name, $create_table = false ) {

            $this->PDO = new PDO('sqlite:'.$db_name);
            $this->PDO->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING );

            $this->table_name = $table_name;

            if( !file_exists($db_name) || filesize($db_name) == 0 || $this->checkTable( $table_name ) == false){
                $this->createTable( $create_table );
            }

        }

        function __destruct() {
            $this->PDO = NULL;
        }

        public function getTableInfo() {
            $info = $this->user_query = "PRAGMA table_info( $this->table_name )";
            return $this->getAll();
        }

        public function checkColumn( $column ) {

            $info = $this->getTableInfo();

            foreach( $info as $key => $line ) {
                if( $line['name'] == $column ) return true;
            }

            return false;
        }


        public function checkTable( $table_name ) {
            $is = $this->query('SELECT 1 FROM '.$table_name);
            return $is;
        }

        public function generateTable( $data, $prefix = '', $fx = false ) {

            if( !is_array($data) ){
                user_error("'$data' - это не массив!");
                return false;
            }

            $return = array();

            foreach($data as $key => $item) {
                if( $fx ){

                    if( $this->$fx( $key ) ){
                        continue;
                    } else {
                        $return[] = "$prefix `$key` $item";
                    }

                } else {
                    $return[] = "$prefix `$key` $item";
                }
            }

            return implode(',', $return);
        }

        public function createTable( $create_table ) {
            $table = $this->PDO->query('CREATE TABLE `'. $this->table_name .'` ('. (count($create_table) ? $this->generateTable( $create_table ) : trim($create_table)) .')');
            return $table;
        }

        private function userQuery( $query ) {

            if ( isset($this->user_query) ) {
                $user_query = $this->user_query;
                unset($this->user_query);
                return $user_query;
            } else {
                return $query;
            }

        }

        public function getError() {
            return $this->PDO->errorInfo();
        }

        public function lastId(){
            return  $this->PDO->lastInsertId();
        }



        /* GET*/
        public function query( $query = false ) {
            $status = $this->PDO->query( $this->userQuery($query) );
            return $status;
        }

        public function getAll( $column = '*', $where = '' ) {
            echo 'SELECT '. $column .' FROM `'.  $this->table_name .'` '.$where;
            $status = $this->query( 'SELECT '. $column .' FROM `'.  $this->table_name .'` '.($where ? 'where '.$where : '') )->fetchAll(PDO::FETCH_ASSOC);
            return $status;
        }

        public function getOne( $where = '') {
            $status = $this->query( 'SELECT * FROM `'. $this->table_name .'` WHERE '.$where )->fetch(PDO::FETCH_ASSOC);
            return $status;
        }


        /* post */
        public function write( $array = false ) {

            if( !$this->user_query && count($array) ) {
                $keys = implode(',',array_keys($array));
                $val_keys = ':' . implode(',:',array_keys($array));
            }

     		$status = $this->PDO->prepare( $this->userQuery( "INSERT INTO `". $this->table_name ."` (" .$keys. ") values (" . $val_keys . " )" ) )->execute( $array );
            return $status;
        }

        /* update */

        public function update( $where = false, $array=false ) {

            if( !$this->user_query && count($array) && $where ) {
                $set = array();

                foreach( $array as $key => $line ) {
                    if( $where != $key ) {
                        $set[] = $key.'=:'.$key;
                    }
                }
            }

     		$status = $this->PDO->prepare( $this->userQuery("UPDATE `". $this->table_name ."` SET ".implode(',', $set)." WHERE ".$where." = :".$where) )->execute( $array );
            return $status;
        }

        /* delete */
        public function remove( $where = false ) {
            $status = $this->PDO->exec( $this->userQuery('DELETE FROM `'. $this->table_name .'` WHERE '.$where) );
            return $status;
        }


        /* addRow */
        public function addRow( $data ) {

            $new_data = $this->generateTable( $data, 'ADD COLUMN', "checkColumn" );
            $status = !empty($new_data) ? ($this->PDO->exec( $this->userQuery("ALTER TABLE `$this->table_name` ".$new_data) ) === false ? false : true ) : false;

//            if( empty($status) ) user_error(print_r($this->getError(), true));
            return $status;
        }


    }


?>