<?php
    ini_set('default_charset', 'UTF-8');
    require_once(dirname(__FILE__) . '/util.php');
    require_once(dirname(__FILE__) . '/validation.php');
    require_once(dirname(__FILE__) . '/myexception.php');
    require_once dirname(__FILE__) . '/SQL_util.php';
    require_once dirname(__FILE__) . '/SQL_util_ctl.php';
    require_once dirname(__FILE__) . '/session_module.php';
    require_once dirname(__FILE__) . '/password_func.php';
    
    function deal_login() {
        try {
            if (!super_session_start(0, 30, true,
                                     Array('cookie_httponly' => true)))
                throw new MyException('failed to start session',
                                      MyException::DEAL_FATAL);
            
            if ( !isset($_SESSION['username']) ) header('Location: http://co-428.99sv-coco.com/kadai3/kadai3_10_1/templates/login_form.php');
            
            echo "<div align=\"right\">\r\n";
            echo htmlspecialchars("ユーザー:{$_SESSION['username']}　",
                                  ENT_QUOTES, "UTF-8");
            echo "前回ログイン："
                .htmlspecialchars($_SESSION['lastlogin'], ENT_QUOTES, "UTF-8");
            echo "<br />\r\n";
            echo "</div>\r\n";
        } catch(MyException $Me) {
            Me_info($Me);
            if ($Me->getCode() === MyException::DEAL_WARN) {
                switch ($Me->getMessage()) {
                default:
                    header('Content-Type: text/plain; charset=UTF-8', true, 500);
                    echo "掲示板へのアクセスに失敗しました\r\n";
                    exit(1);
                }
            } else if ($Me->getCode() === MyException::DEAL_FATAL) {
                http_response_code(403);
                exit(1);
            }
        }
    }
    
    
    function try_login() {
        try {
            global $user_info;
            if ( !isset($_POST['loginconfirm']) ) return;
            
            if ( !isset($_POST['username'])
                 || !isset($_POST['password']) )
                throw new MyException('username or password not set',
                                      MyException::DEAL_FATAL);
            
            $username = $_POST['username'];
            $password = $_POST['password'];
            
            $db = connect_db();
            $sql = "SELECT * FROM "."$user_info"." WHERE username=:username";
            $stmt = $db->prepare($sql);
            $stmt->bindParam(':username', $username, PDO::PARAM_STR);
            $stmt->execute();
            $row = $stmt->fetch();
            if ($row === false) {
                usleep(10000);
                throw new MyException('username or password incorrect',
                                      MyException::DEAL_WARN);
            }
            
            $password = hash_password($password, $row['salt']);
            if (!compare_hashed_password(
                    $password,
                    $row['password']
               )) {
                throw new MyException('username or password incorrect',
                                      MyException::DEAL_WARN);
            }
            $newlastlogin = $row['thislogin'];
            $sql = "UPDATE " . "$user_info"
                  ." SET lastlogin =:ll, thislogin=:tl"
                  ." WHERE username = :username";
            $stmt = $db->prepare($sql);
            
            $stmt->bindParam(':username', $username, PDO::PARAM_STR);
            $stmt->bindValue(':ll', $newlastlogin, PDO::PARAM_STR);
            $stmt->bindValue(':tl', date('Y-m-d H:i:s'), PDO::PARAM_INT);
            $stmt->execute();
            
            if (!super_session_start(0, 30, true,
                                     Array('cookie_httponly' => true)))
                throw new MyException('failed to start session',
                                      MyException::DEAL_FATAL);
            
            $_SESSION['username'] = $username;
            $_SESSION['lastlogin'] = $newlastlogin;
            header('Location: http://co-428.99sv-coco.com/kadai3/kadai3_10_1/templates/index.php');
            
        } catch(MyException $Me) {
            Me_info($Me);
            if ($Me->getCode() === MyException::DEAL_WARN) {
                switch ($Me->getMessage()) {
                case 'username or password incorrect':
                    echo "ユーザー名かパスワードが違います<br />\r\n";
                    break;
                default:
                    header('Content-Type: text/plain; charset=UTF-8', true, 500);
                    echo "掲示板へのアクセスに失敗しました\r\n";
                    exit(1);
                }
            } else if ($Me->getCode() === MyException::DEAL_FATAL) {
                http_response_code(403);
                exit(1);
            }
        } catch(PDOException $PDOe) {
            errinfo($PDOe->getMessage());
        }
    }
?>