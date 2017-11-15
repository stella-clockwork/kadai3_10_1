<html>
<head>
<?php
    ini_set('default_charset', 'UTF-8');
    require_once( dirname(__FILE__) . '/../sources/SQL_util.php' );
    
    $db = connect_db();
    $tables = get_all_table($db);
    if ( isset($_POST['reset']) ) {
        foreach($tables as $tmp_table) {
            $sql = "DROP TABLE IF EXISTS `" . "$tmp_table[0]" . "`";
            $db->exec($sql);
        }
        $tables = get_all_table($db);
    }
    
    var_dump($tables);
    echo "<br />\r\n";
?>
</head>
<body>
    <form action="" method="post">
        <p>
            <input type="hidden" name="reset"><br />
            <input type="submit" value="リセット">
        </p>
    </form>
</body>
</html>