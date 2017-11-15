<html>
<head>
    <title>パスワードリセットフォーム</title>
    <meta charset="utf-8">
<?php
    ini_set('default_charset', 'UTF-8');
    header('X-FRAME-OPTIONS:DENY');
    require_once dirname(__FILE__) . '/../sources/reset_password.php';
?>

</head>
<body>
<?php $id = deal_password_reset(); ?>
    <form action="password_reset_form.php" method="post">
        <p>
            ユーザー名：<input type="text" name="username"><br />
            <br />
            パスワード：<input type="text" name="password"><br />
            リセットするユーザー名を入力してください<br />
            パスワードは半角英数字8文字以上100文字以内で登録してください<br />
            パスワードは小文字と大文字、数字を各1字以上含む必要があります<br />
            <br />
<?php
    echo "<input type=\"hidden\" name=\"id\" value=\"";
    echo htmlspecialchars($id, ENT_QUOTES, "UTF-8");
    echo "\">";
?>
            <input type="hidden" name="resetconfirm">
            <input type="submit" value="パスワード変更">
        </p>
    </form>
</body>
</html>