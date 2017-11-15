<html>
<head>
    <title>投稿編集フォーム</title>
    <meta charset="utf-8">
<?php
    ini_set('default_charset', 'UTF-8');
    header('X-FRAME-OPTIONS:DENY');
    require_once dirname(__FILE__) . '/../sources/editlog.php';
    require_once dirname(__FILE__) . '/../sources/login_control.php';
?>

</head>
<body>
<?php deal_login(); ?>
<?php $edit_target = deal_edit(); ?>
    <form action="" method="post">
        <p>
            <input type="hidden" name="editid" size="5" value="<?= $edit_target['id'] ?>"><br />
            <input type="hidden" name="token"  value="<?=htmlspecialchars(generate_token(), ENT_QUOTES | ENT_HTML401, 'UTF-8')?>">
            コメント：<textarea cols="60" rows="10" maxlength="500" name="comment" wrap="soft">
<?php
    echo $edit_target['comment'];
?></textarea><br />
            <input type="hidden" name="editconfirm">
            <input type="submit" value="編集を実行">
        </p>
    </form>
    <form action="./index.php">
        <p>
            <input type="submit" value="編集せずに戻る">
        </p>
    </form>

</body>
</html>