<?php
    ini_set('default_charset', 'UTF-8');
    require_once(dirname(__FILE__) . '/util.php');
    require_once(dirname(__FILE__) . '/validation.php');
    require_once(dirname(__FILE__) . '/myexception.php');
    require_once dirname(__FILE__) . '/SQL_util.php';
    require_once dirname(__FILE__) . '/password_func.php';
    
    function deal_password_reset() {
        try {
            if (isset($_GET['id'])) {
                $id = $_GET['id'];
                for_reset_id_validation($id);
            } else if (isset($_POST['id'])) {
                $id = $_POST['id'];
                for_reset_id_validation($id);
                if (isset($_POST['resetconfirm'])) {
                    reset_password();
                    delete_for_reset_id($id);
                    header('Location: http://co-428.99sv-coco.com/kadai3/kadai3_10_1/templates/index.php');
                }
            } else {
                throw new MyException('for reset id not set',
                                      MyException::DEAL_FATAL);
            }
            return $id;
        } catch(MyException $Me) {
            Me_info($Me);
            if ($Me->getCode() === MyException::DEAL_WARN) {
                switch ($Me->getMessage()) {
                case 'username incorrect':
                    echo "ユーザー名が違います<br />\r\n";
                    break;
                case 'password empty':
                    echo "パスワードを入力してください<br />\r\n";
                    break;
                case 'invalid password':
                    echo "パスワードが条件を満たしていません<br />\r\n";
                    break;
                default:
                    echo "掲示板へのアクセスに失敗しました\r\n";
                    exit(1);
                }
            } else if ($Me->getCode() === MyException::DEAL_FATAL) {
                http_response_code(403);
                exit(1);
            }
            return $id;
        }
    }
    
    
    function reset_password() {
        if ( !isset($_POST['username'])
             || !isset($_POST['password']) )
            throw new MyException('post not set',
                                  MyException::DEAL_FATAL);
        
        $username = $_POST['username'];
        $password = $_POST['password'];
        $id = $_POST['id'];
        
        Me_validation($username, 0, 60, '');
        
        $db = connect_db();
        global $table_for_reset;
        $sql = "SELECT * FROM " . "$table_for_reset"
              ." where tentativepass =:tid";
        $stmt = $db->prepare($sql);
        $stmt->bindParam(':tid', $id, PDO::PARAM_STR);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ( !timing_safe_strcmp($username, $row['username']) )
            throw new MyException('username incorrect',
                                  MyException::DEAL_WARN);
        
        if ($password === '')
            throw new MyException('password empty',
                                  MyException::DEAL_WARN);
        
        if (!mb_ereg("\A(?=.*?[a-z])(?=.*?[A-Z])(?=.*?\d)[a-zA-Z\d]{8,100}+\z",
                     $password))
            throw new MyException('invalid password',
                                  MyException::DEAL_WARN);
        
        $salt = get_random_str();
        $password = hash_password($password, $salt);
        
        
        //ユーザー情報を更新
        global $user_info;
        $sql = "UPDATE " . "$user_info"
              ." SET password =:password,"
              ." salt =:salt"
              ." WHERE username = :username";
        $stmt = $db->prepare($sql);
        
        $stmt->bindParam(':password', $password, PDO::PARAM_STR);
        $stmt->bindParam(':salt', $salt, PDO::PARAM_STR);
        $stmt->bindParam(':username', $username, PDO::PARAM_STR);
        $stmt->execute();
        
        errinfo("password for $username is reset");
    }
    
    
    
    function for_reset_id_validation($id) {
        global $table_for_reset;
        $db = connect_db();
        $sql = "SELECT * FROM " . "$table_for_reset";
        $stmt = $db->prepare($sql);
        $stmt->execute();
        
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            if ( timing_safe_strcmp($id, $row['tentativepass']) ) return;
        }
        
        throw new MyException('not registerd id',
                              MyException::DEAL_FATAL);
        
    }
    
    function delete_for_reset_id($id) {
        global $table_for_reset;
        $db = connect_db();
        
        $sql = "DELETE FROM " . "$table_for_reset"
              ." where tentativepass =:id";
        $stmt = $db->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_STR);
        $stmt->execute();
    }
?>