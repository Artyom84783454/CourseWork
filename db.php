<?php
$host = 'db'; // Адрес сервера базы данных
$db   = 'astraconnect'; // Имя базы данных
$user = 'postgres'; // Имя пользователя базы данных
$pass = '1607'; // Пароль пользователя базы данных
$charset = 'utf8'; // Кодировка символов для подключения

$dsn = "pgsql:host=$host;dbname=$db"; // Строка подключения для PDO
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // Установка режима обработки ошибок на исключения
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC, // Установка режима извлечения данных по умолчанию в ассоциативный массив
    PDO::ATTR_EMULATE_PREPARES   => false, // Отключение эмуляции подготовленных запросов
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options); // Создание объекта PDO для соединения с базой данных
} catch (\PDOException $e) {
    throw new \PDOException($e->getMessage(), (int)$e->getCode()); // Обработка исключений PDO и передача сообщения об ошибке
}
?>