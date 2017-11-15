<?php
    ini_set('default_charset', 'UTF-8');
    define ('STRETCH_COUNT', 316);
    
    function timing_safe_strcmp($send, $saved) {
        $send_length = strlen($send);
        $retval = ($send_length === strlen($saved));
        
        for($i = 0; $i < $send_length; $i++) {
            $retval = ($send[$i] === $saved[$i]) && $retval;
        }
        return $retval;
    }
    
    function compare_hashed_password($send, $saved) {
        /* ユニークsalt+streching状態でタイミング攻撃の心配は無用だが */
        return timing_safe_strcmp($send, $saved);
    }
    
    function hash_func($message) {
        return hash('sha256', $message);
    }
    
    function hash_password($native, $salt = '') {
        $hashed = '';
        $salt = hash_func($salt);
        for ($i = 0; $i < STRETCH_COUNT; $i++) {
            $hashed = hash_func($hashed . $native . $salt);
        }
        return $hashed;
    }
    
    
    function get_random_str() {
        return md5(uniqid(rand(), true));
    }
?>