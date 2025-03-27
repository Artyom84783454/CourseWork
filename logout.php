<?php
session_start();

// Уничтожение всех данных сессии
$_SESSION = array();

// Если требуется, удалите сессионную куку
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Уничтожение сессии
session_destroy();

// Возвращаем успешный ответ
http_response_code(200);

// Перенаправление на страницу авторизации
header('Location: login.php');
exit;
?>
