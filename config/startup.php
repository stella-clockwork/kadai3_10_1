<?php
    ini_set('default_charset', 'UTF-8');
    require_once dirname(__FILE__) . '/../sources/SQL_util.php';
    require_once dirname(__FILE__) . '/../sources/util.php';
    
    $db = connect_db();
    $sql =   "CREATE TABLE IF NOT EXISTS `" . "$default_board" . "`"
            ."("
            . "`id`        INT auto_increment primary key,"
            . "`name`      VARCHAR(60),"
            . "`comment`   TEXT,"
            . "`post_date` DATETIME,"
            . "`password`  TEXT"
            .");";
    $stmt = $db->prepare($sql);
    $stmt->execute();
    
    $sql =   "CREATE TABLE IF NOT EXISTS `" . "$board_a37" . "`"
            ."("
            . "`id`        INT auto_increment primary key,"
            . "`name`      VARCHAR(60),"
            . "`comment`   TEXT,"
            . "`post_date` DATETIME,"
            . "`content`   LONGBLOB,"
            . "`content_type` INT,"
            . "`mime_type` TEXT"
            .");";
    $stmt = $db->prepare($sql);
    $stmt->execute();
    
    $sql =   "CREATE TABLE IF NOT EXISTS `" . "$user_info" . "`"
            ."("
            . "`username`  VARCHAR(60) unique primary key,"
            . "`password`  TEXT,"
            . "`salt`      TEXT,"
            . "`lastlogin` DATETIME,"
            . "`thislogin` DATETIME,"
            . "`mailaddress` TEXT"
            .");";
    $stmt = $db->prepare($sql);
    $stmt->execute();
    
    
    $sql =   "CREATE TABLE IF NOT EXISTS `" . "$tentative_regist_table" . "`"
                    ."("
                    . "`mailaddress`   TEXT,"
                    . "`tentativepass` VARCHAR(100) primary key,"
                    . "`atjobid`       INT"
                    .");";
    $stmt = $db->prepare($sql);
    $stmt->execute();
    
    $sql =   "CREATE TABLE IF NOT EXISTS `" . "$table_for_reset" . "`"
                    ."("
                    . "`tentativepass` VARCHAR(100) primary key,"
                    . "`username`      TEXT,"
                    . "`atjobid`       INT"
                    .");";
    $stmt = $db->prepare($sql);
    $stmt->execute();
    

?>