<?php
    ini_set('default_charset', 'UTF-8');
    require_once(dirname(__FILE__) . '/util.php');
    require_once(dirname(__FILE__) . '/validation.php');
    require_once(dirname(__FILE__) . '/myexception.php');
    
    
    function connect_db() {
        //dsn, user, passwordを設定
        require(dirname(__FILE__) . '../config/dbseqinfos.php');
        
        $options = array(
            PDO::MYSQL_ATTR_READ_DEFAULT_FILE  => '/etc/my.cnf',
            PDO::MYSQL_ATTR_MAX_BUFFER_SIZE    => 1024*1024*20,
//            PDO::ATTR_PERSISTENT => true,
        );
        try {
            $db = new PDO($dsn, $user, $password, $options);
            $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $db->exec("SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;");

            //$db->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
        } catch (PDOException $PDOe) {
            throw new MyException(
                'failed to connect server: '.$PDOe->getMessage(),
                MyException::DEAL_WARN);
        }
        return $db;
    }
    
    
    function table_name_regist_validation($db, $table_name) {
        if (!tf_validation($table_name, 1, 100,
                           '\(\)\{\}\[\]\?\!\_\.\,\&\#\=\@'))
            throw new MyException('illegal table name for regist',
                                  MyException::DEAL_WARN);
        
        $all_table = get_all_table($db);
        foreach($all_table as $name) {
            if ($name[0] === $table_name)
                throw new MyException('name already exist in db',
                                      MyException::DEAL_WARN);
        }
        
    }
    
    
    function table_name_call_validation($db, $table_name) {
        global $user_info;
        if ($table_name === $user_info) {
            throw new MyException("don't give user info table for validation",
                                  MyException::DEAL_FATAL);
        }
        
        if (!tf_validation($table_name, 1, 100,
                           '\(\)\{\}\[\]\?\!\_\.\,\&\#\=\@'))
            throw new MyException('illegal table given',
                                  MyException::DEAL_FATAL);
        
        $all_table = get_all_table($db);
        foreach($all_table as $name) {
            if ($name[0] === $table_name) return;
        }
        throw new MyException('not exist table given',
                              MyException::DEAL_FATAL);
    }
    
    
    function print_contents($stmt) {
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $id = $row['id'];
            $name = $row['name'];
            $comment = $row['comment'];
            $post_date = $row['post_date'];
            
            if ( !is_numeric($id)
                 || !tf_validation($name, 0, 60, '')
                 || !tf_validation($comment, 0, 500,
                     '\+\-\*\/\(\)\{\}\[\]\<\>\?\!\_\.\,\%\&\#\=\;\:\@\'\" \r\n')
                 || !mb_ereg( '^[0-9\- \:]+$', $post_date ) ) {
                throw new MyException('log file illegally falsificated',
                                      MyException::DEAL_FATAL);
            }
            
            $id = htmlspecialchars($id, ENT_QUOTES | ENT_HTML401, "UTF-8");
            $post_date = htmlspecialchars($post_date, ENT_QUOTES | ENT_HTML401,
                                          "UTF-8");
            if ($name === $_SESSION['username']) {
                $name = htmlspecialchars($name, ENT_QUOTES | ENT_HTML401,
                                         "UTF-8");
                $name = "<font color=\"#ff4500\">". $name ."</font>";
            } else {
                $name = htmlspecialchars($name, ENT_QUOTES | ENT_HTML401,
                                         "UTF-8");
            }
            $line = "{$id}. {$name} ({$post_date})";
            
            $comment = htmlspecialchars($comment,
                                        ENT_QUOTES | ENT_HTML401, "UTF-8");
            $comment = mb_ereg_replace("\n", "<br />\n", $comment);
            $comment = mb_ereg_replace("\r\<br \/\>\n", "<br />\r\n", $comment);
            
            
            /* 画像処理 */
            $content = '';
            switch($row['content_type']) {
            case NO_CONTENT:
                break;
            case IMAGE_CONTENT:
                $content =
                  "<img src=\"./sources/content_metamoler.php?id={$id}\">\r\n";
                break;
            case MOVIE_CONTENT:
                $content = "<video"
                          ." src=\"./sources/content_metamoler.php?id={$id}\""
                          ." controls preload=\"metadata\">"
                          ."sorry, your browser doesn't support embedded videos"
                          ."</video>\r\n";
                break;
            default:
                break;
            }
            
            echo "<HR>\r\n";
            echo $line."<br />\r\n";
            echo $comment."<br />\r\n";
            echo $content;
            
        }
    }
    
    
    function get_table_contents($db, $table_name) {
        try {
            table_name_call_validation($db, $table_name);
            $sql = "SELECT * FROM " . "$table_name" . " ORDER BY id";
            $stmt = $db->query($sql);
            return $stmt;
        } catch (PDOException $PDOe) {
            throw new MyException(
                'failed to get table contents: '.$PDOe->getMessage(),
                MyException::DEAL_WARN);
        }
    }
    
    
    function get_one_element($db, $table_name, $get_id) {
        try {
            table_name_call_validation($db, $table_name);
            $sql = "SELECT * FROM " . "$table_name" . " where id=:get_id";
            $stmt = $db->prepare($sql);
            $stmt->bindValue(':get_id', $get_id, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt;
        } catch (PDOException $PDOe) {
            throw new MyException(
                'failed to get element: '.$PDOe->getMessage(),
                MyException::DEAL_WARN);
        }
    }
    
    
    function get_all_table($db) {
        try {
            $stmt = $db->query('SHOW TABLES from co_428_it_3919_com');
            $re = $stmt->fetchAll();
            return $re;
        } catch (PDOException $PDOe) {
            throw new MyException(
                'failed to get table list: '.$PDOe->getMessage(),
                MyException::DEAL_WARN);
        }
    }
    
?>