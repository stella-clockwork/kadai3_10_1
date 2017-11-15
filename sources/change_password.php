<?php
    ini_set('default_charset', 'UTF-8');
    require_once(dirname(__FILE__) . '/util.php');
    require_once(dirname(__FILE__) . '/validation.php');
    require_once(dirname(__FILE__) . '/myexception.php');
    require_once dirname(__FILE__) . '/SQL_util.php';
    require_once dirname(__FILE__) . '/password_func.php';
    
    function deal_change_password() {
        try {
            if (isset($_POST['passwordchangeconfirm'])) {
                change_password();
                header('Location: http://co-428.99sv-coco.com/kadai3/kadai3_10_1/templates/index.php');
            }
        } catch(MyException $Me) {
            Me_info($Me);
            if ($Me->getCode() === MyException::DEAL_WARN) {
                switch ($Me->getMessage()) {
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
        }
    }
    
    
    function change_password($tid) {
        if ( !isset($_POST['password']) )
            throw new MyException('post not set',
                                  MyException::DEAL_FATAL);
        
        $username = $_SESSION['username'];
        $password = $_POST['password'];
        
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
        $db = connect_db();
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
        
        errinfo("password for $username is changed");
    }
    
?>