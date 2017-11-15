<html>
<head>
    <title>リセット用フォーム</title>
    <meta charset="utf-8">
<?php
    ini_set('default_charset', 'UTF-8');
    header('X-FRAME-OPTIONS:DENY');
    require_once dirname(__FILE__).'/../sources/tentative_regist_for_reset.php';
?>

</head>
<body>
<?php deal_tentative_regist_for_password_reset(); ?>
    ユーザー名を入力してください。アカウント作成時に登録されたメールアドレスにリセット用メールを送付します。<br />
    <form action="" method="post">
        <p>
            <input type="text" name="username">
            <input type="hidden" name="passwordresetmailconfirm">
            <input type="submit" value="送信">
        </p>
    </form>
    <form action="./login_form.php">
        <p>
            <input type="submit" value="送信せずに戻る">
        </p>
    </form>

</body>
</html>