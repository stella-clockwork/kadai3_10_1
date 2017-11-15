<?php
    ini_set('default_charset', 'UTF-8');
/**
 * このコードは以下のURLからのコピーです。著作権はmpyw様に帰属します。
http://qiita.com/mpyw/items/7f4772e4d4d360fc100c#_reference-bf380bb5da8f35579fca
 */

// CSRFトークン生成に使うハッシュアルゴリズム
define("CSRF_TOKEN_HASHALGO", 'sha256');

// セッションIDの更新時刻に使う $_SESSION のキー
define("SESSION_UPDATETIME_KEY", '__NEXT_UPDATE__');

/**
 * 延長可能なセッションを開始する
 * 安全のため定期的にセッションIDの更新を行う
 * ついでに不正なセッションIDによるエラーの発生も防ぐ
 *
 * @param int $lifetime 最終アクセスから失効までの秒数 (0でセッションクッキー化)
 * @param int $updatetime 最終更新から次回更新までの秒数 (0で更新無し)
 * @param bool $extend 再アクセスでセッションを延長するかどうか
 * @param array $options PHP7.0以降から使えるsession_startの第1引数互換
 * @return bool 可否
 */
function super_session_start($lifetime = 0, $updatetime = 0, $extend = true, $options = Array())
{
    // セッションファイルおよびクッキーの両方の有効期限を設定
    ini_set('session.gc_maxlifetime', $lifetime);
    ini_set('session.cookie_lifetime', $lifetime);

    // その他のオプションを設定
    foreach ($options as $key => $value) {
        ini_set("session.$key", $value);
    }

    // 不正なセッションIDの無効化とセッションの延長
    $name = session_name();
    if (isset($_COOKIE[$name])) {
        if (!ctype_alnum($_COOKIE[$name])) {
            unset($_COOKIE[$name]);
        } elseif ($lifetime > 0 && $extend) {
            setcookie(
                $name,
                $_COOKIE[$name],
                time() + $lifetime,
                ini_get('session.cookie_path'),
                ini_get('session.cookie_domain'),
                (bool)ini_get('session.cookie_secure'),
                (bool)ini_get('session.httponly')
            );
        }
    }

    // セッションの開始
    if (!session_start()) {
        return false;
    }

    // セッションIDの更新
    if ($updatetime > 0) {
        if (!isset($_SESSION[SESSION_UPDATETIME_KEY])) {
            $_SESSION[SESSION_UPDATETIME_KEY] = time() + $updatetime;
        } elseif ($_SESSION[SESSION_UPDATETIME_KEY] <= time()) {
            session_regenerate_id(true);
            $_SESSION[SESSION_UPDATETIME_KEY] = time() + $updatetime;
        }
    }

    return true;
}

/**
 * CSRFトークンから送信の有効性を検証する
 * セッションIDの更新が行われても対応できるように，リクエスト受理時のものを優先する
 * 
 * @param string $token 送信されてきたCSRFトークン
 * @return bool 可否
 */
function validate_token($token)
{
    return $token === hash(CSRF_TOKEN_HASHALGO,
                           filter_input(INPUT_COOKIE, session_name())
                               ?   filter_input(INPUT_COOKIE, session_name())
                                 : session_id()
                      );
}

/**
 * CSRFトークンを生成する
 * 
 * @return bool 可否
 */
function generate_token()
{
    return hash(CSRF_TOKEN_HASHALGO, session_id());
}
?>