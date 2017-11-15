<?php
    ini_set('default_charset', 'UTF-8');
    require_once(dirname(__FILE__) . '/util.php');
    require_once(dirname(__FILE__) . '/validation.php');
    require_once(dirname(__FILE__) . '/myexception.php');
    require_once dirname(__FILE__) . '/SQL_util.php';
    require_once dirname(__FILE__) . '/password_func.php';
    
    function deal_regist_user() {
        try {
            if (isset($_GET['id'])) {
                $id = $_GET['id'];
                tentative_id_validation($id);
            } else if (isset($_POST['id'])) {
                $id = $_POST['id'];
                tentative_id_validation($id);
                if (isset($_POST['userregistconfirm'])) {
                    regist_user($id);
                    delete_tentative_id($id);
                    header('Location: http://co-428.99sv-coco.com/kadai3/kadai3_10_1/templates/index.php');
                }
            } else {
                throw new MyException('tentative id not set',
                                      MyException::DEAL_FATAL);
            }
            return $id;
        } catch(MyException $Me) {
            Me_info($Me);
            if ($Me->getCode() === MyException::DEAL_WARN) {
                switch ($Me->getMessage()) {
                case 'user name empty':
                    echo "ユーザー名を入力してください<br />\r\n";
                    break;
                case 'password empty':
                    echo "パスワードを入力してください<br />\r\n";
                    break;
                case 'invalid password':
                    echo "パスワードが条件を満たしていません<br />\r\n";
                    break;
                case 'too long string given to validation':
                    echo "ユーザー名が長すぎます<br />\r\n";
                    break;
                case 'not permited character given to validation':
                    echo "ユーザー名には半角英数字と全角文字以外は使用できません<br />\r\n";
                    break;
                case 'user name already used':
                    echo "入力されたユーザー名は既に使用されています<br />\r\n";
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
    
    
    function regist_user($tid) {
        if ( !isset($_POST['username'])
             || !isset($_POST['password']) )
            throw new MyException('post not set',
                                  MyException::DEAL_FATAL);
        
        $username = $_POST['username'];
        $password = $_POST['password'];
        if ($username === '')
            throw new MyException('user name empty',
                                  MyException::DEAL_WARN);
        if ($password === '')
            throw new MyException('password empty',
                                  MyException::DEAL_WARN);
        Me_validation($username, 0, 60, '');
        
        if (!mb_ereg("\A(?=.*?[a-z])(?=.*?[A-Z])(?=.*?\d)[a-zA-Z\d]{8,100}+\z",
                     $password))
            throw new MyException('invalid password',
                                  MyException::DEAL_WARN);
        $db = connect_db();
        user_duplication_check($db, $username);
        
        $salt = get_random_str();
        $password = hash_password($password, $salt);
        
        //メールアドレスを移管するために取得
        global $tentative_regist_table;
        $sql = "SELECT * FROM " . "$tentative_regist_table"
              ." where tentativepass =:tid";
        $stmt = $db->prepare($sql);
        $stmt->bindParam(':tid', $tid, PDO::PARAM_STR);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $m_adr = $row['mailaddress'];
        
        //ユーザー登録
        global $user_info;
        $sql = "INSERT INTO " . "$user_info"
                  ." (username, password, salt, mailaddress)"
                  ." VALUES (:username, :password, :salt, :madr)";
        $stmt = $db->prepare($sql);
        
        $stmt->bindParam(':username', $username, PDO::PARAM_STR);
        $stmt->bindParam(':password', $password, PDO::PARAM_STR);
        $stmt->bindParam(':salt', $salt, PDO::PARAM_STR);
        $stmt->bindParam(':madr', $m_adr, PDO::PARAM_STR);
        $stmt->execute();
        
    }
    
    
    function user_duplication_check($db, $username) {
        global $user_info;
        $sql = "SELECT * FROM " . "$user_info" . " where username=:username";
        $stmt = $db->prepare($sql);
        $stmt->bindValue(':username', $username, PDO::PARAM_STR);
        $stmt->execute();
        
        if ($row = $stmt->fetch())
            throw new MyException('user name already used',
                                  MyException::DEAL_WARN);
    }
    
    function tentative_id_validation($id) {
        global $tentative_regist_table;
        $db = connect_db();
        $sql = "SELECT * FROM " . "$tentative_regist_table";
        $stmt = $db->prepare($sql);
        $stmt->execute();
        
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            if ( timing_safe_strcmp($id, $row['tentativepass']) ) return;
        }
        
        throw new MyException('not registerd id',
                              MyException::DEAL_FATAL);
        
    }
    
    function delete_tentative_id($id) {
        global $tentative_regist_table;
        $db = connect_db();
        
        $sql = "DELETE FROM " . "$tentative_regist_table"
              ." where tentativepass =:id";
        $stmt = $db->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_STR);
        $stmt->execute();
    }
?>