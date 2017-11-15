<?php
    ini_set('default_charset', 'UTF-8');
    require_once(dirname(__FILE__) . '/util.php');
    require_once(dirname(__FILE__) . '/validation.php');
    require_once(dirname(__FILE__) . '/myexception.php');
    require_once(dirname(__FILE__) . '/SQL_util.php');
    require_once(dirname(__FILE__) . '/SQL_util_ctl.php');
    
    function deal_delete(){
        try {
            if ( isset($_POST['deleteconfirm']) ) {
                delete_log();
                header('Location: http://co-428.99sv-coco.com/kadai3/kadai3_10_1/templates/index.php');
            } else {
                if ( !isset($_POST['deleteid'])
                    || !tf_validation($_POST['deleteid'], 1, 5, '') ) {
                    throw new MyException('delete id not set',
                                          MyException::DEAL_FATAL);
                }
                if ( !is_numeric($_POST['deleteid']) ) {
                    throw new MyException('non numeric delete id given',
                                          MyException::DEAL_WARN);
                }
                $db = connect_db();
                global $board_a37;
                $stmt = get_one_element($db, $board_a37, $_POST['deleteid']);
                $row = $stmt->fetch();
                if ($row['name'] !== $_SESSION['username']) {
                    throw new MyException('not your comment',
                                          MyException::DEAL_WARN);
                }
                
                $stmt = get_one_element($db, $board_a37, $_POST['deleteid']);
                print_contents($stmt);
            }
        } catch(MyException $Me) {
            Me_info($Me);
            if ($Me->getCode() === MyException::DEAL_WARN) {
                switch ($Me->getMessage()) {
                case 'non numeric delete id given':
                    header('Location: http://co-428.99sv-coco.com/kadai3/kadai3_10_1/templates/index.php');
                    break;
                case 'not your comment':
                    echo "あなたの投稿ではないため削除できません<br />\r\n";
                    exit(1);
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
    
    function delete_log() {
        if ( !isset($_POST['token']) ) 
            throw new MyException('CSRF token not set',
                                  MyException::DEAL_FATAL);
        
        if (!validate_token($_POST['token']))
            throw new MyException('CSRF token incorrect',
                                  MyException::DEAL_FATAL);
        
        if ( !isset($_POST['deleteid']) ) {
            throw new MyException('delete id not set',
                                  MyException::DEAL_FATAL);
        }
        $delete_id = $_POST['deleteid'];
        if ( $delete_id === '' ) {
            throw new MyException('delete id not inputed',
                                  MyException::DEAL_FATAL);
        }
        if ( !is_numeric($delete_id) ) {
            throw new MyException('non numeric delete id given',
                                  MyException::DEAL_FATAL);
        }
        $delete_id = (int) $delete_id;
        
        $db = connect_db();
        global $board_a37;
        delete_element($db, $board_a37, $delete_id);
    }
?>
