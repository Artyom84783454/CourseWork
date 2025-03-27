<?php
// login.php

// Подключение к базе данных
require 'db.php';

// Начало сессии
session_start();

// Переменная для сообщений об ошибке
$errorMsg = '';

// Обработка POST запроса при отправке формы
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Получение и очистка данных из формы
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    // Валидация данных
    if (empty($username) || empty($password)) {
        $errorMsg = "Please fill in all required fields.";
    } else {
        // Проверка данных пользователя в базе данных
        try {
            $stmt = $pdo->prepare("SELECT * FROM users WHERE username = :username");
            $stmt->execute([':username' => $username]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            // Проверка совпадения пароля
            if ($user && password_verify($password, $user['password_hash'])) {
                // Успешная аутентификация, установка данных сессии
                $_SESSION['user_id'] = $user['user_id'];
                $_SESSION['username'] = $user['username'];

                // Проверка роли пользователя
                if ($user['role'] === 'admin') {
                    // Перенаправление на страницу для администратора
                    header('Location: admin.php');
                    exit;
                } else {
                    // Перенаправление на защищенную страницу или профиль пользователя
                    header('Location: cabinet.php');
                    exit;
                }
            } else {
                $errorMsg = "Невірний Логін чи Пароль."; // Сообщение об ошибке при неправильном логине или пароле
            }
        } catch (PDOException $e) {
            $errorMsg = "Помилка входу: " . $e->getMessage(); // Сообщение об ошибке при ошибках базы данных
        }
    }
}
?>

<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Логін</title>
    <link href="/css/login.css" media="all" rel="stylesheet" type="text/css"/>
    <link rel="stylesheet" href="https://unpkg.com/swiper/swiper-bundle.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&display=swap" rel="stylesheet">
    <nav class="navbar">
        <div class="logo">
            <a href="index.html">Astroconnect</a>
        </div>
        <ul id="nav-list" class="nav-menu">
            <li><a href="services.html">Послуги</a></li>
            <li><a href="contact.html">Контакти</a></li>
        </ul>
        <div class="nav-toggle">
            <span></span>
            <span></span>
            <span></span>
        </div>
    </nav>
</head>
<body>
<div class="login-container">
    <?php if (!empty($errorMsg)) : ?>
        <p style="text-align: center;"><?php echo $errorMsg; ?></p>
    <?php endif; ?>
    <h2>Логін</h2>
    <form method="POST" action="login.php">
        <input type="text" name="username" placeholder="Введіть логін" required>
        <input type="password" name="password" placeholder="Введіть пароль" required>
        <input type="submit" value="Войти">
    </form>
    <div class="link">
        <a href="registration.php">Регістрація</a>
    </div>
</div>
<script>
    // Когда весь HTML-документ загружен и построен
    document.addEventListener('DOMContentLoaded', function () {
        // Находим элементы на странице
        const navToggle = document.querySelector('.nav-toggle'); // Кнопка переключения меню
        const navMenu = document.querySelector('.nav-menu'); // Само меню

        // Добавляем обработчик события клика на кнопку переключения меню
        navToggle.addEventListener('click', function () {
            // Переключаем класс 'open' у меню
            navMenu.classList.toggle('open');
        });
    });
</script>
</body>
</html>
