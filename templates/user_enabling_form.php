<html>
<head>
    <title>ユーザー登録フォーム</title>
    <meta charset="utf-8">
<?php
    ini_set('default_charset', 'UTF-8');
    header('X-FRAME-OPTIONS:DENY');
    require_once dirname(__FILE__) . '/../sources/regist_user.php';
?>

</head>
<body>
<?php $id = deal_regist_user(); ?>
    <form action="./user_enabling_form.php" method="post">
        <p>
            ユーザー名：<input type="text" name="username"><br />
            <br />
            パスワード：<input type="text" name="password"><br />
            ユーザー名は半角英数字と全角文字20文字以内で登録してください
            パスワードは半角英数字8文字以上100文字以内で登録してください<br />
            パスワードは小文字と大文字、数字を各1字以上含む必要があります<br />
            <br />
<?php
    echo "<input type=\"hidden\" name=\"id\" value=\"";
    echo htmlspecialchars($id, ENT_QUOTES, "UTF-8");
    echo "\">";
?>
            <input type="hidden" name="userregistconfirm">
            <input type="submit" value="登録">
        </p>
    </form>
    <form action="./index.php">
        <p>
            <input type="submit" value="登録せずに戻る">
        </p>
    </form>

</body>
</html>