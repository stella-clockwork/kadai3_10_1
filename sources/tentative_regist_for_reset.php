<?php
    ini_set('default_charset', 'UTF-8');
    require_once(dirname(__FILE__) . '/util.php');
    require_once(dirname(__FILE__) . '/validation.php');
    require_once(dirname(__FILE__) . '/myexception.php');
    require_once dirname(__FILE__) . '/SQL_util.php';
    require_once dirname(__FILE__) . '/password_func.php';
    
    function deal_tentative_regist_for_password_reset() {
        try {
            if (isset($_POST['passwordresetmailconfirm'])) {
                send_reset_mail();
                header('Location: http://co-428.99sv-coco.com/kadai3/kadai3_10_1/templates/after_password_reset_mail_form.php');
            }
        } catch(MyException $Me) {
            Me_info($Me);
            if ($Me->getCode() === MyException::DEAL_WARN) {
                switch ($Me->getMessage()) {
                case 'not registered username':
                    header('Location: http://co-428.99sv-coco.com/kadai3/kadai3_10_1/templates/after_password_reset_mail_form.php');
                    break;
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
    
    
    function send_reset_mail() {
        if ( !isset($_POST['username']) )
            throw new MyException('post not set',
                                  MyException::DEAL_FATAL);
        
        $username = $_POST['username'];
        if ( !tf_validation($username, 0, 60, '') )
            throw new MyException('illegal username',
                                  MyException::DEAL_FATAL);
        
        $db = connect_db();
        global $user_info;
        $sql = "SELECT * FROM " . "$user_info"
             ." where username =:username";
        $stmt = $db->prepare($sql);
        $stmt->bindValue(':username', $username, PDO::PARAM_STR);
        $stmt->execute();
        $row = $stmt->fetch();
        if ($row === false)
            throw new MyException('not registered username',
                                  MyException::DEAL_WARN);
        $m_adr = $row['mailaddress'];
        
        mail_duplication_check_for_regist($db, $username);
        $t_id = get_random_str();
        //$at_id = at_regist($m_adr);
        
        global $table_for_reset;
        $sql = "INSERT INTO " . "$table_for_reset"
              ." (tentativepass, username)"
              ." VALUES (:t_id, :username)";
        $stmt = $db->prepare($sql);
        $stmt->bindValue(':t_id', $t_id, PDO::PARAM_STR);
        $stmt->bindValue(':username', $username, PDO::PARAM_STR);
        $stmt->execute();
        
        mb_language("japanese");
        mb_internal_encoding("UTF-8");
        $subject = "リセット用メール";
        $body = 
            "こちらのアドレスにアクセスし、リセットを完了させてください。\r\n"
."http://co-428.99sv-coco.com/kadai3/kadai3_10_1/templates/password_reset_form.php?id={$t_id}\r\n"
           ."\r\n"
           ."もしこのメールにお心当たりがない場合、このメールはご放念ください。\r\n";
        
        $encoding = mb_detect_encoding($body, "SJIS,EUC-JP,JIS,UTF-8");
        if ($encoding != "JIS") {
            $subject = mb_convert_encoding($subject, "JIS", $encoding);
            $body = mb_convert_encoding($body, "JIS", $encoding);
        }
        
        $body = mb_convert_kana($body, "KVa");
        
        if ( !mb_send_mail("$m_adr", $subject, $body, "From: ") )
            throw new MyException('failed to send mail',
                                  MyException::DEAL_WARN);
    }
    
    
    function mail_duplication_check_for_regist($db, $username) {
        global $table_for_reset;
        $sql = "SELECT * FROM " . "$table_for_reset"
               ." where username =:username";
        $stmt = $db->prepare($sql);
        $stmt->bindValue(':username', $username, PDO::PARAM_STR);
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