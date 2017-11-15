<?php
    ini_set('default_charset', 'UTF-8');
    require_once(dirname(__FILE__) . '/util.php');
    require_once(dirname(__FILE__) . '/validation.php');
    require_once(dirname(__FILE__) . '/myexception.php');
    require_once dirname(__FILE__) . '/SQL_util.php';
    require_once dirname(__FILE__) . '/SQL_util_ctl.php';
    
    function deal_regist() {
        try {
            regist_str();
        } catch(MyException $Me) {
            Me_info($Me);
            if ($Me->getCode() === MyException::DEAL_WARN) {
                switch ($Me->getMessage()) {
                case 'comment not inputed':
                    echo "コメントを入力してください<br />\r\n";
                    break;
                case 'too long string given to validation':
                    echo "入力が長すぎます<br />\r\n";
                    break;
                case 'not permited character given to validation':
                    echo "入力に使用できない文字が含まれています<br />\r\n";
                    break;
                default:
                    echo "掲示板へのアクセスに失敗しました\r\n";
                    exit(1);
                }
            } else if ($Me->getCode() === MyException::DEAL_FATAL) {
                exit(1);
            }
        }
    }
    
    
    function regist_str() {
        if ( !isset($_POST['comment']) )
            return;
        
        $comment = $_POST['comment'];
        if ($comment === '')
            throw new MyException('comment not inputed',
                                  MyException::DEAL_WARN);
        
        if ( !isset($_POST['token']) ) 
            throw new MyException('CSRF token not set',
                                  MyException::DEAL_FATAL);
        
        if (!validate_token($_POST['token']))
            throw new MyException('CSRF token incorrect',
                                  MyException::DEAL_FATAL);
        
        Me_validation($_SESSION['username'], 0, 60, '');
        Me_validation($comment, 0, 500,  '\+\-\*\/\(\)\{\}\[\]\<\>\?\!\_\.\,\%\&\#\=\;\:\@\'\" \r\n');
        
        /* ファイルアップロード機能 */
        $content_type = NO_CONTENT;
        if (isset($_FILES['file']['error'])
            && is_int($_FILES['file']['error'])) {
            try {
                switch ($_FILES['file']['error']) {
                case UPLOAD_ERR_OK: // OK
                    break;
                case UPLOAD_ERR_NO_FILE:   // ファイル未選択
                    throw new MyException('file not uploaded',
                                          MyException::DEAL_WARN);
                    break;
                case UPLOAD_ERR_INI_SIZE:  // php.ini定義の最大サイズ超過
                case UPLOAD_ERR_FORM_SIZE: // フォーム定義の最大サイズ超過
                    throw new MyException('file size too large',
                                          MyException::DEAL_WARN);
                default:
                    throw new MyException('unknown error in file upload',
                                          MyException::DEAL_WARN);
                }
                
                switch ($_FILES['file']['type']) {
                    case "image/jpeg":
                    case "image/gif":
                    case "image/png":
                        $content_type = IMAGE_CONTENT;
                        break;
                    case "video/mp4":
                    case "video/mpeg":
                        $content_type = MOVIE_CONTENT;
                        break;
                    default:
                        throw new MyException('unpermited file upload',
                                              MyException::DEAL_FATAL);
                }
                
                // サイズ上限のオーバーチェック
                if ($_FILES['file']['size'] > 1024*1024*20) {
                    throw new MyException('file size too large',
                                          MyException::DEAL_WARN);
                }
            } catch (MyException $Me) {
                switch ($Me->getMessage()) {
                case 'file not uploaded':
                    echo "file not uploaded<br>\r\n";
                    break;
                default:
                    throw $Me;
                }
            }
        }
        
        
        $db = connect_db();
        global $board_a37;
        insert_element($db, $board_a37, $_SESSION['username'], $comment, $content_type);
    }
?>