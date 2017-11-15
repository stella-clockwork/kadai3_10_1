<?php
    ini_set('default_charset', 'UTF-8');
    require_once(dirname(__FILE__) . '/util.php');
    require_once(dirname(__FILE__) . '/validation.php');
    require_once(dirname(__FILE__) . '/myexception.php');
    require_once dirname(__FILE__) . '/SQL_util.php';
    require_once dirname(__FILE__) . '/SQL_util_ctl.php';
    
    function deal_edit() {
        try {
            if ( isset($_POST['editconfirm']) ) {
                edit_log();
                header('Location: http://co-428.99sv-coco.com/kadai3/kadai3_10_1/templates/index.php');
            } else {
                if ( !isset($_POST['editid'])
                    || !tf_validation($_POST['editid'], 1, 5, '') ) {
                    throw new MyException('edit id not set',
                                          MyException::DEAL_FATAL);
                }
                if ( !is_numeric($_POST['editid']) ) {
                    throw new MyException('non numeric edit id given',
                                          MyException::DEAL_WARN);
                }
                $edit_id = (int) $_POST['editid'];
                
                $db = connect_db();
                global $board_a37;
                $stmt = get_one_element($db, $board_a37, $edit_id);
                $row = $stmt->fetch();
                if ($row['name'] !== $_SESSION['username']) {
                    throw new MyException('not your comment',
                                          MyException::DEAL_WARN);
                }
                
                return get_edit_target($edit_id);
            }
        } catch(MyException $Me) {
            Me_info($Me);
            if ($Me->getCode() === MyException::DEAL_WARN) {
                switch ($Me->getMessage()) {
                case 'non numeric edit id given':
                    header('Location: http://co-428.99sv-coco.com/kadai3/kadai3_10_1/templates/index.php');
                    break;
                case 'comment not inputed':
                    echo "コメントを入力してください<br />\r\n";
                    return get_edit_target($_POST['editid']);
                    break;
                case 'too long string given to validation':
                    echo "入力が長すぎます<br />\r\n";
                    return get_edit_target($_POST['editid']);
                    break;
                case 'not permited character given to validation':
                    echo "入力に使用できない文字が含まれています<br />\r\n";
                    return get_edit_target($_POST['editid']);
                    break;
                case 'not your comment':
                    echo "あなたの投稿ではないため編集できません<br />\r\n";
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
    
    
    function get_edit_target($target) {
        $db = connect_db();
        global $board_a37;
        $stmt = get_one_element($db, $board_a37, $target);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $comment = htmlspecialchars($result['comment'],
                                    ENT_QUOTES | ENT_HTML401, "UTF-8");
        return array("id" => $target, "comment" => $comment);
    }
    
    
    function edit_log() {
        if ( !isset($_POST['token']) ) 
            throw new MyException('CSRF token not set',
                                  MyException::DEAL_FATAL);
        
        if (!validate_token($_POST['token']))
            throw new MyException('CSRF token incorrect',
                                  MyException::DEAL_FATAL);
        
        if ( !isset($_POST['editid']) ) {
            throw new MyException('edit id not set',
                                  MyException::DEAL_FATAL);
        }
        $edit_id = $_POST['editid'];
        if ( $edit_id === '' ) {
            throw new MyException('edit id not inputed',
                                  MyException::DEAL_FATAL);
        }
        if ( !tf_validation($edit_id, 1, 5, '')
             || !is_numeric($edit_id) ) {
            throw new MyException('non numeric edit id given',
                                  MyException::DEAL_FATAL);
        }
        $edit_id = (int) $edit_id;
        
        if ( !isset($_POST['comment']) ) {
            throw new MyException('comment not set',
                                  MyException::DEAL_FATAL);
        }
        $comment = $_POST['comment'];
        if ($comment === '') {
            throw new MyException('comment not inputed',
                                  MyException::DEAL_WARN);
        }
        Me_validation($comment, 0, 500,  '\+\-\*\/\(\)\{\}\[\]\<\>\?\!\_\.\,\%\&\#\=\;\:\@\'\"\s\r\n');
        
        $db = connect_db();
        global $board_a37;
        update_element($db, $board_a37, $edit_id, $comment);
    }
?>