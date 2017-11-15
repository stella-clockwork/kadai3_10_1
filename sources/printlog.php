<?php
    ini_set('default_charset', 'UTF-8');
    require_once(dirname(__FILE__) . '/util.php');
    require_once(dirname(__FILE__) . '/myexception.php');
    require_once dirname(__FILE__) . '/SQL_util.php';
    
    function deal_print() {
        try {
            print_log();
        } catch(MyException $Me) {
            Me_info($Me);
            if ($Me->getCode() === MyException::DEAL_WARN) {
                echo "掲示板へのアクセスに失敗しました<br />\r\n";
                exit(1);
            } else if ($Me->getCode() === MyException::DEAL_FATAL) {
                exit(1);
            }
        }
    }
    
    
    function print_log() {
        $db = connect_db();
        global $board_a37;
        $stmt = get_table_contents($db, $board_a37);
        print_contents($stmt);
    }
?>