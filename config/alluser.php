<?php
    ini_set('default_charset', 'UTF-8');
    require_once dirname(__FILE__) . '/../sources/SQL_util.php';
    require_once dirname(__FILE__) . '/../sources/util.php';
    
    $db = connect_db();
    $sql = "SELECT * FROM " . "$user_info" . " ORDER BY username";
    $stmt = $db->query($sql);
    while ($user = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $name = $user['username'];
        $pass = $user['password'];
        $ll = $user['lastlogin'];
        $tl = $user['thislogin'];
        $m_adr = $user['mailaddress'];
        $line = "name:{$name}, pass:{$pass}, last login:{$ll}"
                ." mail:{$m_adr}";
        echo htmlspecialchars($line, ENT_QUOTES, "UTF-8")."<br />\r\n";
    }
    
?>