<?php
    ini_set('default_charset', 'UTF-8');
    require_once(dirname(__FILE__) . '/myexception.php');
    
    function error_exit() {
        http_response_code(403);
        exit(1);
    }
    
    function kill_validation($val, $min_len = 0, $max_len=60, $permitchar='') {
        if (!tf_validation($val, $min_len, $max_len, $permitchar)) error_exit();
    }
    
    function tf_validation($val, $min_len = 0, $max_len=60, $permitchar='') {
        if (!is_string($val)) return false;
        if (ini_get('default_charset') != 'UTF-8') return false;
        if (!mb_check_encoding($val, 'UTF-8')) return false;
        if (strlen($val) < $min_len || strlen($val) > $max_len) return false;
        if (!mb_ereg('\A[0-9A-Za-z\x80-\xFF' . $permitchar . ']*\z', $val))
            return false;
        
        return true;
    }
    
    function Me_validation($val, $min_len = 0, $max_len=60, $permitchar='') {
        if (!is_string($val))
            throw new MyException('array given to validation',
                                  MyException::DEAL_FATAL);
        if (ini_get('default_charset') != 'UTF-8')
            throw new MyException('encode is illegal',
                                  MyException::DEAL_FATAL);
        if (!mb_check_encoding($val, 'UTF-8'))
            throw new MyException('illegal encode given to validation',
                                  MyException::DEAL_FATAL);
        if (strlen($val) < $min_len || strlen($val) > $max_len)
            throw new MyException('too long string given to validation',
                                  MyException::DEAL_WARN);
        if (!mb_ereg('\A[0-9A-Za-z\x80-\xFF' . $permitchar . ']*\z', $val))
            throw new MyException('not permited character given to validation',
                                  MyException::DEAL_WARN);
        
    }
?>