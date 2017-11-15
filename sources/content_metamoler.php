<?php
    ini_set('default_charset', 'UTF-8');
    require_once dirname(__FILE__) . '/util.php';
    require_once dirname(__FILE__) . '/validation.php';
    require_once dirname(__FILE__) . '/myexception.php';
    require_once dirname(__FILE__) . '/SQL_util.php';
    require_once dirname(__FILE__) . '/session_module.php';
    
    if ( !isset($_GET['id'])
         || !is_numeric($_GET['id'])
         || !tf_validation($_GET['id'], 0, 100, '') ) exit(1);
    
    if (!super_session_start(0, 30, true,
                             Array('cookie_httponly' => true))) exit(1);
    
    if ( !isset($_SESSION['username']) ) exit(1);
    
    
    $id = $_GET['id'];
    $db = connect_db();
    global $board_a37;
    $stmt = get_one_element($db, $board_a37, $id);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!isset($row['mime_type'])
        || !tf_validation($row['mime_type'], 8, 13,'/')) exit(1);
    
    switch ($row['mime_type']) {
    case "image/jpeg":
        header('Content-Disposition: inline; filename=image.jpg');
        break;
    case "image/gif":
        header('Content-Disposition: inline; filename=image.gif');
        break;
    case "image/png":
        header('Content-Disposition: inline; filename=image.png');
        break;
    case "video/mp4":
        header('Content-Disposition: inline; filename=video.mp4');
        break;
    case "video/mpeg":
        header('Content-Disposition: inline; filename=video.mpeg');
        break;
    default:
        exit(1);
    }
    header('Content-type: '. $row['mime_type']);
    header('X-Content-Type-Options: nosniff');
    
    echo $row['content'];
?>