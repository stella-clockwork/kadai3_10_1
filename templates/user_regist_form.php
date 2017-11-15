<html>
<head>
    <title>ユーザー仮登録フォーム</title>
    <meta charset="utf-8">
<?php
    ini_set('default_charset', 'UTF-8');
    header('X-FRAME-OPTIONS:DENY');
    require_once dirname(__FILE__) . '/../sources/tentative_regist.php';
?>

</head>
<body>
<?php deal_tentative_regist(); ?>
    仮登録用のメールアドレスをご入力ください。<br />
    <form action="" method="post">
        <p>
            メールアドレス：<input type="text" name="mailaddress"><br />
            <input type="hidden" name="tentativeregistconfirm">
            <input type="submit" value="登録">
        </p>
    </form>
    <form action="./login_form.php">
        <p>
            <input type="submit" value="登録せずに戻る">
        </p>
    </form>

</body>
</html>