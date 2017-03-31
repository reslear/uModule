<?php

    if(!defined('uphp')) exit;

    /*  Каталог статей v0.0.1 | by ReSLeaR- | Апр. 2017 | upost.su
    ----------------------------------------------------------------------------------*/

    class Article {

        function __construct($uid, $cat, $perm) {

            // парсер
            $this->cavus = new CavusParser();

            // uid
            $this->uid = $uid;

            // создаём базу если нет
            $this->db = new Database('module/article/database/articles.db', 'article', array(

                'id'   => 'INTEGER PRIMARY KEY AUTOINCREMENT', // уникальынй id

                'title' => 'VARCHAR(200) NOT NULL',

                'cat'  => 'INTEGER NOT NULL', // категория
                'text' => 'MEDIUMTEXT NOT NULL', // текст статьи

                'uid'  => 'INTEGER NOT NULL', // uid автора
                'date' => 'DATETIME NOT NULL', //дата создания
            ));

            // категори - id и название
            $this->default_cat = $cat;

            // права
            $this->perm = $perm;
        }


        public function creat_db_array( $user_array ) {

            // выход если переменные пусты
            if( !F::check_isset_array( array('cat', 'text', 'title'), $user_array ) ){
                return false;
            };

            $cat_index = $this->search_cat_id(0, intval($user_array['cat']) );

            // Выход если передана категория не числом, или её нет в массиве
            if( !F::has_int($user_array['cat']) || !$cat_index[0] ) {
                return false;
            }

            // Формируем переменные
            $array = array(
                'title'=> strip_tags($user_array['title']),
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

        /*  Проверка по категории
        ------------------------------------------------------------------------------- */
        public function check_category($key, $value) {

            $all_cats = $this->default_cat;

            // перебираем массив конфига категрий
            foreach($all_cats as $array) {

                // если есть такой ключ, и значение ключа = значения
                if( isset($array[$key]) && $array[$key] === $value ) {
                    return $array;
                }
            }

            return false;
        }


        public function print_category( $select_id = 0, $select_template, $option_template ) {

            // задаём дефольтные значения шаблонов
            $select_template = isset($select_template) ? $select_template : '<select name="cat" id="select_category">%s</select>';
            $option_template = isset($option_template) ? $option_template : '<option value="%1$d" %2$s>%3$s</option>';

            // получаем данные категорий и добавляем первый пункт
            $all_cats = $this->default_cat;
            array_unshift($all_cats, array( 'id' => 0, 'url' => '', 'title' => '- Выбрать -'));

            // переменная в которой будут пункты
            $options = '';

            // перебор option и шаблонизация
            foreach($all_cats as $array) {
                $is_selected = (intval($select_id) === intval($array['id']) ? 'selected': '');
                $options .= sprintf($option_template, $array['id'], $is_selected, $array['title']);
            }

            // шаблонизация select
            $select = sprintf($select_template, $options);
            return $select;
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

        public function print_all($category, $style_type = 1, $columns = 1, $reverse = true ) {

            $array = array(
                'ARTICLE_STYLE_TYPE' => $style_type
            );

            // db
            $_cat_index = $this->search_cat_id(1, $category);
            $cat_index = $_cat_index[0];

            if( !$cat_index ) {
                return false;
            }

            $all = $this->db->getAll( '*', 'cat = '.$cat_index );
            $template_view = F::get_file('module/article/template/view_type'.$style_type.'.html');

            // перебор
            foreach($all as $article) {

                $array_view = array();

                foreach($article as $key => $value) {
                    $array_view['ARTICLE_'.strtoupper($key)] = $value;
                }

                $article_url = '$PAGE_MAIN_URL$'.$_cat_index[1].'/'.$article['id'];

                $array_view = array_merge($array_view, array(
                    'ARTICLE_URL'  => $article_url,
                    'ARTICLE_DATE' => F::smart_date($article['date']),

                    'ARTICLE_DELETE' => F::check_right($this->perm['remove']) === true ? 'javascript://" onclick="Article.remove('.$article['id'].');return false;' : '',
                    'ARTICLE_EDIT' => F::check_right($this->perm['edit']) === true  || $article['uid'] === $this->uid ? $article_url.'/edit' : ''
                ));

                $array['ARTICLE_BODY'][] = $this->cavus->parse($template_view, $array_view);
            }

            // если ничего нет в body например материалов не найдено в категории
            if( !empty($array['ARTICLE_BODY']) ){

                if( $reverse ) {
                    $array['ARTICLE_BODY'] = array_reverse($array['ARTICLE_BODY']);
                }

                # обычный вид тип 1
                if( $style_type === 1 ) {
                    $array['ARTICLE_BODY'] = implode('',$array['ARTICLE_BODY']);
                }

                # уникальный вид тип 2
                if( $style_type === 2 ) {

                    // для того что бы первая статья была верху
                    $new_article = $array['ARTICLE_BODY'][0];
                    unset($array['ARTICLE_BODY'][0]);

                    $array['ARTICLE_BODY'] = F::print_columns($array['ARTICLE_BODY'], $columns, false, '<div class="entries-col">%2$s</div>', '<div class="entries-cols">%s</div>');

                    $array['ARTICLE_BODY'] = '<div class="entries-col new">'.$new_article.'</div>'.$array['ARTICLE_BODY'];
                }

            } else {
                $array['ARTICLE_BODY'] = '<div class="not-search">Ничего не найдено</div>';
            }

            // template
            $template_all = $this->cavus->parse('module/article/template/all.html', $array);
            return $template_all;
        }

        public function print_one($id) {


            if(!F::has_int($id)) return false;
            $one = $this->db->getOne('id = '.$id );

            $html = '<div class="block">id:'.$one['id'].'<br>date:'.$one['date'].'<br><br>'.$one['text'].'<br></div>';
            return $html;
        }

        public function is_idset($id) {

            if(!F::has_int($id)) return false;
            $one = $this->db->getOne('id = '.$id );
            return isset($one['id']);
        }

        public function print_edit($id, $is_add = false, $cat = 0) {

            $edit_array = array();

            if( !$is_add ) {
                if(!F::has_int($id)) return false;
                $edit_array = $this->db->getOne('id = '.$id );
                $cat = $edit_array['cat'];
            }

            if( $cat !== 0) {
                $search = $this->search_cat_id(1, $cat);
                $cat = $search[0];
            }

            $edit_array['ARTICLE_IS_EDIT'] = $is_add ? false : true;
            $edit_array['ARTICLE_CATS'] = $this->print_cats($cat);

            $edit = $this->cavus->parse('module/article/template/add.html', $edit_array);
            return $edit;
        }


        public function remove($id) {


            if(!F::has_int($id)) return false;
            $remove = $this->db->remove('id='.$id);

            return $remove ? $id.'Удалено' : 'не удалено';
        }

        /////    echo $article->addRow( array('title', 'VARCHAR(200)') );
        public function addRow($arr){
            $fo = $this->db->addRow( $arr );
            return 'строка <b>'.$arr[0].'</b> '.($fo ? '' :'не').'добавлена!';
        }
    }



    $uid = ucoz_getinfo('SITEUSERID');

    $cat = array(
        array(
            'id' => 1,
            'url' => 'article',
            'title' => 'Статьи'
        ),
        array(
            'id' => 2,
            'url' => 'script',
            'title' => 'Скрипты'
        )
    );

    $perm = array(
        'remove' => array(
            'uid' => array(1, 2),
            'group' => array()
        ),
        'edit' => array(
            'uid' => array(1,0),
            'group' => array()
        ),
    );

    $article = new Article($uid, $cat, $perm);

    /*  POST - запросы
    ----------------------------------------------------------------------------------*/
    if( isset($_POST['type']) && $_POST['type'] == 'add' ) {

        // права групп
        $check_right = F::check_right( array($uid) );

        if( $check_right === true ) {

            $write_status = $article->write($_POST);
            echo $write_status;

        } else {
            echo $check_right;
        }

    }

    // удаление
    if( isset($_POST['type']) && $_POST['type'] == 'remove' && isset($_POST['id']) && F::has_int($_POST['id']) ) {

        // права групп
        $check_right = F::check_right($uid);

        if( $check_right === true ) {
            echo $article->remove($_POST['id']);
        } else {
            echo $check_right;
        }

    }

    /*  GET - запросы
    ----------------------------------------------------------------------------------*/
    if( isset($_GET['type']) && $_GET['type'] == 'all' ) {

        $category = isset($_GET['cat']) ? $_GET['cat'] : '*';
        $all = $article->print_all($category);
        echo $all;
    }

    # если загрузка только одной, или страница редактирования
    if( isset($_GET['type']) && $_GET['type'] == 'one' && isset($_GET['id']) && F::has_int($_GET['id']) ) {

        $id = intval($_GET['id']);

        // права групп
        $check_right = F::check_right( array($uid,1) );

        // Редактирование
        if( isset($_GET['edit']) ) {
            if( $check_right === true ) {


                if($article->is_idset($id)) {

                    $edit = $article->print_edit($id);
                    echo print_r($edit, true);
                } else {
                    echo 'нет такой';
                }
            }else{
                echo $check_right;
            }
        } else {
            $one = $article->print_one($id);
            echo $one;
        }
    }

    if( isset($_GET['add']) ) {

        $temp_cat = strval($_GET['add']);

        $write_status = $article->print_edit(0, 1, $temp_cat);
        echo $write_status;
    }

?>