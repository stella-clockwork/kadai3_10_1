<html>
<head>
    <title>ログインフォーム</title>
    <meta charset="utf-8">
<?php
    ini_set('default_charset', 'UTF-8');
    header('X-FRAME-OPTIONS:DENY');
    require_once dirname(__FILE__) . '/../sources/login_control.php';
?>

</head>
<body>
<?php try_login(); ?>
<div align="right"><a href="./user_regist_form.php">
    新規ユーザー登録
</a></div>
    <br />
    <form action="" method="post">
        <p>
            ユーザー名：<input type="text" name="username"><br />
            <br />
            パスワード：<input type="text" name="password"><br />
            <br />
            <input type="hidden" name="loginconfirm">
            <input type="submit" value="ログイン">
        </p>
    </form>
<br />
<div align="right"><a href="./password_reset_mail_send_form.php">
    パスワードを忘れた
</a></div>
    

</body>
</html>