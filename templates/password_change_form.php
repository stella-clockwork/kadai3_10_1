<html>
<head>
    <title>パスワード変更フォーム</title>
    <meta charset="utf-8">
<?php
    ini_set('default_charset', 'UTF-8');
    header('X-FRAME-OPTIONS:DENY');
    require_once dirname(__FILE__) . '/../sources/change_password.php';
    require_once dirname(__FILE__) . '/../sources/login_control.php';
?>

</head>
<body>
<?php deal_login(); ?>
<?php deal_change_password(); ?>
    <form action="" method="post">
        <p>
            新パスワード：<input type="text" name="password"><br />
            パスワードは半角英数字8文字以上100文字以内で登録してください<br />
            パスワードは小文字と大文字、数字を各1字以上含む必要があります<br />
            <br />
            <input type="hidden" name="passwordchangeconfirm">
            <input type="submit" value="変更">
        </p>
    </form>
    <form action="./index.php">
        <p>
            <input type="submit" value="変更せずに戻る">
        </p>
    </form>

</body>
</html>