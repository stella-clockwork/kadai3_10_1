<?php
    ini_set('default_charset', 'UTF-8');
    
    $user_info = "users";
    $tentative_regist_table = "tentativeusers";
    $default_board = "board";
    $board_a37 = "board2";
    $table_for_reset = "forpassreset";
    
    define("NO_CONTENT", 0);
    define("IMAGE_CONTENT", 1);
    define("MOVIE_CONTENT", 2);

    function errinfo($message) {
        //echo htmlspecialchars($message, ENT_QUOTES, "UTF-8")."<br />\r\n";
        
        file_put_contents(dirname(__FILE__) . '/serverlog.log',
                          $message." at ".date('Y-m-d H:i:s')."\r\n",
                          FILE_APPEND | LOCK_EX);
        
    }
?>