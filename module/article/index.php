<?php

    /*  Каталог статей v0.0.1 | by ReSLeaR- | Апр. 2017 | upost.su
    ----------------------------------------------------------------------------------*/

    class F {

        static function check_isset_array( $check_array = array(), $array = array() ) {

            if( !count($check_array) || !count($array) ) return false;

            foreach($check_array as $i => $name ) {
                if( !isset($array[$name]) || empty($array[$name]) ) return false;
            }

            return true;
        }

        static function has_int($value) {
            return ((int) $value == $value && $value > 0);
        }
    }


    class Article {

        function __construct($uid, $cat) {

            $this->uid = $uid;

            $this->db = new Database('module/article/database/articles.db', 'article', array(

                'id'   => 'INTEGER PRIMARY KEY AUTOINCREMENT', // уникальынй id

                'cat'  => 'INTEGER NOT NULL', // категория
                'text' => 'VARCHAR(1000) NOT NULL', // текст статьи

                'uid'  => 'INTEGER NOT NULL', // uid автора
                'date' => 'DATETIME NOT NULL', //дата создания
            ));

            // категори - id и название
            $this->default_cat = $cat;
        }

        public function creat_db_array( $user_array ) {

            // выход если переменные пусты
            if( !F::check_isset_array( array('cat', 'text'), $user_array ) ){
                return false;
            };

            // Выход если передана категория не числом, или её нет в массиве
            if( !F::has_int($user_array['cat']) || !isset($this->default_cat[$user_array['cat']]) ) {
                return false;
            }

            // Формируем переменные
            $array = array(
                'cat'  => $user_array['cat'],
                'text' => $user_array['text'],
                'uid'  => $this->uid,
                'date' => date("Y-m-d H:i:s")
            );

            return $array;
        }

        private function db_init_write($array) {
            $write_status = $this->db->write($array);
            return $write_status;
        }

        public function write( $array) {

            // создаём массив
            $new_db_array = $this->creat_db_array($array);

            if( !$new_db_array ) {
                return 'Некоторые данные не валидны.';
            }

            // запись
            $write = $this->db_init_write($new_db_array);
            return $write ? print_r($new_db_array, true) : 'Ошибка, записи.';
        }

        public function print_all($category) {

            $array = array();

            // db
            $query = $this->db->PDO->prepare('SELECT * FROM `'. $this->db->table_name .'` where cat = :cat');
            $query->bindParam(':cat', $category);

            $query->execute();
            $all = $query->fetchAll(PDO::FETCH_ASSOC);

            // перебор
            foreach($all as $article) {
                $url ='$PAGE_MAIN_URL$'.$this->default_cat[$article['cat']][0].'/'.$article['id'];
                $array[] = '<div class="block"><a href="'.$url.'">id:'.$article['id'].'</a><br>date:'.$article['date'].'<br></div>';
            }

            return implode('', $array);
        }
    }

    $uid = ucoz_getinfo('SITEUSERID');
    $cat = array(1 => array('article','Статьи'), 2 => array('script','Скрипты') );
    $article = new Article($uid, $cat);

    /*  POST - запросы
    ----------------------------------------------------------------------------------*/
    if( isset($_POST['type']) && $_POST['type'] == 'add' ) {
        $write_status = $article->write($_POST);
        echo $write_status;
    }

    /*  GET - запросы
    ----------------------------------------------------------------------------------*/
    if( isset($_GET['type']) && $_GET['type'] == 'all' ) {

        $category = isset($_GET['cat']) ? $_GET['cat'] : '*';
        $all = $article->print_all($category);
        echo $all;
    }

?>