<html>
<head>
<?php
    ini_set('default_charset', 'UTF-8');
    require_once( dirname(__FILE__) . '/../sources/SQL_util.php' );
    
    $db = connect_db();
    global $tentative_regist_table;
    
    if ( isset($_POST['reset']) ) {
        $sql = "DROP TABLE IF EXISTS `" . "$tentative_regist_table" . "`";
        $db->exec($sql);
    } else {
        $sql = "SELECT * FROM " . "$tentative_regist_table";
        $stmt = $db->prepare($sql);
        $stmt->execute();
        
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            var_dump($row);
            echo "<br />\r\n";
        }
    }
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