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
        public function check_category($key, $value, $false_result = false) {

            // $false_result = например можно указать ''


            if( !isset($key) || !isset($value)  ) {
                return $false_result;
            }

            $all_cats = $this->default_cat;

            // перебираем массив конфига категрий
            foreach($all_cats as $array) {

                $check_key = $key === 'id' ? intval($value) : strval($value);

                // если есть такой ключ, и значение ключа = значения
                if( isset($array[$key]) && $array[$key] === $check_key ) {
                    return $array;
                }
            }

            return $false_result;
        }




        /*  Создание HTML списка SELECT
        ------------------------------------------------------------------------------- */
        public function print_category( $select_id = 0, $select_template = false, $option_template = false ) {

            // задаём дефольтные значения шаблонов
            $select_template = $select_template ? $select_template : '<select name="cat" id="select_category">%s</select>';
            $option_template = $option_template ? $option_template : '<option value="%1$d" %2$s>%3$s</option>';

            // получаем данные категорий и добавляем первый пункт
            $all_cats = $this->default_cat;
            array_unshift($all_cats, array( 'id' => 0, 'name' => '', 'title' => '- Выбрать -'));

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





        /*  Вывод HTML всех данных
        ------------------------------------------------------------------------------- */
        public function print_all($category, $style_type = 1, $columns = 1, $reverse = true ) {


            // получаем данные категории
            $category = $this->check_category($category[0],$category[1]);

            // получаем массив из базы данных
            $database_result = $this->db->getAll( '*', ($category ? 'cat = '.$category['id'] : '') );
            $style_template = F::get_file('module/article/template/view_type'.$style_type.'.html');

            // массив переменныех для шаблонизации
            $array = array(
                'ARTICLE_STYLE_TYPE' => $style_type
            );

            // перебор данных
            foreach($database_result as $article) {

                // создаём пользовательские переменные для использования в шаблоне
                $array_view = F::create_uservar($article, 'ARTICLE');

                // получаем категорю по id, и готовим url
                $article_cat = $this->check_category('id', $article['cat'], '');
                $article_url = '$PAGE_MAIN_URL$'.$article_cat['name'].'/'.$article['id'];

                // добавляем перемененные
                $array_view = array_merge($array_view, array(
                    'ARTICLE_URL'  => $article_url,
                    'ARTICLE_DATE' => F::smart_date($article['date']),

                    'ARTICLE_CAT_NAME' => $article_cat['name'],
                    'ARTICLE_CAT_TITLE' => $article_cat['title'],
                    'ARTICLE_CAT_URL' => '$PAGE_MAIN_URL$'.$article_cat['name'],

                    'ARTICLE_DELETE' => F::check_right($this->perm['remove'], 'javascript://" onclick="Article.remove('.$article['id'].');return false;', ''),
                    'ARTICLE_EDIT' => F::check_right($this->perm['edit']) || intval($article['uid']) === $this->uid ? $article_url.'/edit' : ''
                ));

                // шаблонизируем и добавляем в переменную body
                $array['ARTICLE_BODY'][] = $this->cavus->parse($style_template, $array_view);
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

        public function print_add_edit($id, $is_add = false, $cat = 0) {

            $edit_array = array();

            // если редактирование
            if( !$is_add ) {
                if(!F::has_int($id)) return false;
                $edit_array = $this->db->getOne('id = '.$id );

                // Если статья не найдена
                if( !isset($edit_array['id']) ) {
                    return false;
                }

                $cat = $edit_array['cat'];
            }

            if( $cat !== 0) {
                $search = $this->check_category('name', $cat, '');
                $cat = $search['id'];
            }

            $edit_array['ARTICLE_IS_EDIT'] = $is_add ? false : true;
            $edit_array['ARTICLE_CATS'] = $this->print_category($cat);

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
            'name' => 'article',
            'title' => 'Статьи'
        ),
        array(
            'id' => 2,
            'name' => 'script',
            'title' => 'Скрипты'
        )
    );

    $perm = array(
        'add' => array(
            'uid' => array(1),
        ),
        'remove' => array(
            'uid' => array(1),
        ),
        'edit' => array(
            'uid' => array(1),
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

        $category = isset($_GET['cat']) ? $_GET['cat'] : '';
        $all = $article->print_all(array('name', $category));
        echo $all;
    }

    # если загрузка только одной, или страница редактирования
    if( isset($_GET['type']) && $_GET['type'] === 'one' && isset($_GET['id']) && F::has_int($_GET['id']) ) {

        $id = intval($_GET['id']);

        // Редактирование
        if( isset($_GET['edit']) ) {

            // вывод страницы редактирования
            $edit = $article->print_add_edit($id);
            $___return[$___module] = $edit;
        } else {

            // вывод полной статьи
            $one = $article->print_one($id);
            echo $one;
        }
    }

    # загрузка страницы добавления
    if( isset($_GET['add']) ) {

        $temp_cat = strval($_GET['add']);

        $write_status = $article->print_add_edit(0, 1, $temp_cat);
        echo $write_status;
    }

?>