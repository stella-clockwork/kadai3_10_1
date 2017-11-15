<html>
<head>
    <title>掲示板</title>
    <meta charset="utf-8">
    <link rel="stylesheet" href="../templates/scrollbox.css" type="text/css">
<?php
    ini_set('default_charset', 'UTF-8');
    header('X-FRAME-OPTIONS:DENY');
    require_once dirname(__FILE__) . '/regist_comment.php';
    require_once dirname(__FILE__) . '/printlog.php';
    require_once dirname(__FILE__) . '/login_control.php';
?>

</head>
<body>
<div align="center"><font size="5">掲示板</font></div>
<?php deal_login(); ?>
<?php deal_regist(); ?>

<div align="right"><a href="./password_change_form.php">
パスワードを変更する
</a></div>
    <form action="" method="post" enctype="multipart/form-data">
        <p>
            コメント：<textarea cols="60" rows="8" maxlength="500" name="comment" wrap="soft"></textarea><br />
            <input type="hidden" name="token"  value="<?=htmlspecialchars(generate_token(), ENT_QUOTES | ENT_HTML401, 'UTF-8')?>">
            画像・動画を添付<input type="file" name="file" accept="image/png,image/jpeg,image/gif,video/mp4,video/mpeg">
            <input type="submit" value="投稿">
        </p>
    </form>
    
    <form action="./deleteform.php" method="post">
        <p>
            投稿を削除：<input type="number" name="deleteid" size="5">
            　　<input type="submit" value="削除">
        </p>
    </form>

    <form action="./editform.php" method="post">
        <p>
            投稿を編集：<input type="number" name="editid" size="5">
            　　<input type="submit" value="編集"><br />
        </p>
    </form>

    <div class="scrollbox">
<?php deal_print(); ?>
    </div>
</body>
</html>