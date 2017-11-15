<?php
    ini_set('default_charset', 'UTF-8');
    require_once(dirname(__FILE__) . '/util.php');
    require_once(dirname(__FILE__) . '/validation.php');
    require_once(dirname(__FILE__) . '/myexception.php');
    require_once dirname(__FILE__) . '/SQL_util.php';
    require_once dirname(__FILE__) . '/password_func.php';
    
    function deal_tentative_regist() {
        try {
            if (isset($_POST['tentativeregistconfirm'])) {
                tentative_regist();
                header('Location: http://co-428.99sv-coco.com/kadai3/kadai3_10_1/templates/after_tentative_regist_form.php');
            }
        } catch(MyException $Me) {
            Me_info($Me);
            if ($Me->getCode() === MyException::DEAL_WARN) {
                switch ($Me->getMessage()) {
                case 'mail address already used':
                    header('Location: http://co-428.99sv-coco.com/kadai3/kadai3_10_1/templates/after_tentative_regist_form.php');
                    break;
                case 'invalid mailaddress':
                    echo "メールアドレスとして認識できませんでした\r\n";
                case 'failed to send mail':
                    echo "メールの送信に失敗しました\r\n";
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
    
    
    function tentative_regist() {
        if ( !isset($_POST['mailaddress']) )
            throw new MyException('post not set',
                                  MyException::DEAL_FATAL);
        
        $m_adr = $_POST['mailaddress'];
        if ( !is_string($m_adr)
             || !mb_check_encoding($m_adr, 'UTF-8') )
            throw new MyException('illegal mailaddress',
                                  MyException::DEAL_FATAL);
        
        if ( !filter_var($m_adr, FILTER_VALIDATE_EMAIL)
             || !preg_match('/@(?!\[)(.++)\z/', $m_adr, $m) )
            throw new MyException('invalid mailaddress',
                                  MyException::DEAL_WARN);
        
        $db = connect_db();
        mail_duplication_check($db, $m_adr);
        
        $t_id = get_random_str();
        //$at_id = at_regist($m_adr);
        
        global $tentative_regist_table;
        $sql = "INSERT INTO " . "$tentative_regist_table"
              ." (mailaddress, tentativepass)"
              ." VALUES (:m_adr, :t_id)";
        $stmt = $db->prepare($sql);
        
        $stmt->bindValue(':m_adr', $m_adr, PDO::PARAM_STR);
        $stmt->bindValue(':t_id', $t_id, PDO::PARAM_STR);
        $stmt->execute();
        
        mb_language("japanese");
        mb_internal_encoding("UTF-8");
        $subject = "掲示板本登録用メール";
        $body = 
            "こちらのアドレスにアクセスし、掲示板への登録を完了させてください。\r\n"
."http://co-428.99sv-coco.com/kadai3/kadai3_10_1/templates/user_enabling_form.php?id={$t_id}\r\n"
           ."\r\n"
           ."もしお心当たりがない場合、このメールはご放念ください。\r\n";
        
        $encoding = mb_detect_encoding($body, "SJIS,EUC-JP,JIS,UTF-8");
        if ($encoding != "JIS") {
            $subject = mb_convert_encoding($subject, "JIS", $encoding);
            $body = mb_convert_encoding($body, "JIS", $encoding);
        }
        
        //$subject = base64_encode($subject);
        //$subject = '=?ISO-2022-JP?B?' . $subject . '?=';
        
        $body = mb_convert_kana($body, "KVa");
        
        if ( !mb_send_mail("$m_adr", $subject, $body, "From: ") )
            throw new MyException('failed to send mail',
                                  MyException::DEAL_WARN);
    }
    
    
    function mail_duplication_check($db, $m_adr) {
        global $tentative_regist_table;
        $sql = "SELECT * FROM " . "$tentative_regist_table"
               ." where mailaddress =:m_adr";
        $stmt = $db->prepare($sql);
        $stmt->bindValue(':m_adr', $m_adr, PDO::PARAM_STR);
        $stmt->execute();
        
        if ($row = $stmt->fetch(PDO::FETCH_ASSOC))
            throw new MyException('mail address already used',
                                  MyException::DEAL_WARN);
    }
    
    
    function at_regist($m_adr) {
        $hour = date("H");
        $min = date("i");
        $month = date("m");
        $day = date("d");
        $year = date("Y");
        $now = time() + 60;
        if($now > mktime($hour, $min, 0, $month, $day))
             $year++; 
        
        /* at コマンドの入出力用パイプ */
        $desc = array(
            0 => array("pipe", "r"),
            1 => array("pipe", "w"),
            2 => array("pipe", "w"),
        );

        /* at コマンド実行 (at hhmm MMDDYYYY) */
        if(($proc = proc_open(sprintf("%s %02d%02d %02d%02d%04d",
                "/usr/bin/at", $hour, $min, $month, $day, $year), $desc, $pipe))){

            /* コマンド登録 */
            fputs($pipe[0],
                  "php del_tentative_id_by_deadline.php?mad={$m_adr}");
            fclose($pipe[0]);

            /* job 番号を STDERR から取得 */ 
            $buf = trim(fgets($pipe[2], 4096));
            fclose($pipe[2]);

            /* STDOUT close */
            fclose($pipe[1]);

            proc_close($proc);

            /* job 番号をリターン */
            return(preg_replace("/^job\s+(\d+).*$/", "$1", $buf));
        }
    }
?>