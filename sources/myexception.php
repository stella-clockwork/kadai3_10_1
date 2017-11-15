<?php
    ini_set('default_charset', 'UTF-8');
    require_once(dirname(__FILE__) . '/util.php');

/**
 * MyException::DEAL_FATALとMyException::DEAL_WARNが$codeとして投げられる
 * $messageはgetMessage()、$codeはgetCode()でそれぞれ取得できる
 */
class MyException extends Exception {
    const DEAL_FATAL = 1;
    const DEAL_WARN = 2;
    
    public function __construct($message, $code = 0) {
        parent::__construct($message, $code);
    }
}

function Me_info($Me) {
    if ($Me->getCode() === MyException::DEAL_WARN) {
        errinfo($Me->getMessage());
    } else if ($Me->getCode() === MyException::DEAL_FATAL) {
        errinfo($Me->getMessage());
    }
}

?>