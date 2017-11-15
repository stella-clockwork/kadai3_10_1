<html>
<head>
    <title>投稿削除フォーム</title>
    <meta charset="utf-8">
<?php
    ini_set('default_charset', 'UTF-8');
    header('X-FRAME-OPTIONS:DENY');
    require_once dirname(__FILE__) . '/../sources/deletelog.php';
    require_once dirname(__FILE__) . '/../sources/login_control.php';
?>

</head>
<body>
<?php deal_login(); ?>
<?php deal_delete(); ?>
    この投稿を本当に削除しますか？<br />
    <form action="" method="post">
        <p>
            <input type="hidden" name="deleteid" value="<?= $_POST['deleteid'] ?>">
            <input type="hidden" name="token"  value="<?=htmlspecialchars(generate_token(), ENT_QUOTES | ENT_HTML401, 'UTF-8')?>">
            <input type="hidden" name="deleteconfirm">
            <input type="submit" value="削除">
        </p>
    </form>
    <form action="./index.php">
        <p>
            <input type="submit" value="削除せずに戻る">
        </p>
    </form>

</body>
</html>
