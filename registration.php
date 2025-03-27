<?php
// registration.php

// Подключение к базе данных
require "db.php";

// Обработка POST запроса при отправке формы
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Получение данных из формы и их очистка
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);
    $email = trim($_POST['email']);
    $phone_number = trim($_POST['phone_number']);
    $address = trim($_POST['address']);
    $first_name = trim($_POST['first_name']);
    $last_name = trim($_POST['last_name']);
    $birthdate = $_POST['birthdate'];
    $gender = $_POST['gender'];

    // Валидация данных
    if (empty($username) || empty($password) || empty($email)) {
        $error_message = "Please fill in all required fields."; // Сообщение об ошибке, если не заполнены обязательные поля
    } else {
        // Хеширование пароля
        $password_hash = password_hash($password, PASSWORD_DEFAULT);

        // Вставка данных в базу
        try {
            $pdo->beginTransaction(); // Начало транзакции

            // Вставка в таблицу users
            $stmt = $pdo->prepare("INSERT INTO users (username, password_hash, email, phone_number, address) 
                                   VALUES (:username, :password_hash, :email, :phone_number, :address)");
            $stmt->execute([
                ':username' => $username,
                ':password_hash' => $password_hash,
                ':email' => $email,
                ':phone_number' => $phone_number,
                ':address' => $address
            ]);

            // Получение ID нового пользователя
            $user_id = $pdo->lastInsertId();

            // Вставка в таблицу user_profiles
            $stmt = $pdo->prepare("INSERT INTO user_profiles (user_id, first_name, last_name, birthdate, gender) 
                                   VALUES (:user_id, :first_name, :last_name, :birthdate, :gender)");
            $stmt->execute([
                ':user_id' => $user_id,
                ':first_name' => $first_name,
                ':last_name' => $last_name,
                ':birthdate' => $birthdate,
                ':gender' => $gender
            ]);

            $pdo->commit(); // Фиксация транзакции

            $success_message = "Регістрація успішна!"; // Сообщение об успешной регистрации
        } catch (PDOException $e) {
            $pdo->rollBack(); // Откат транзакции в случае ошибки
            if ($e->getCode() == '23505') { // Уникальное нарушение
                $error_message = "Логін чи пошта вже використовуються."; // Сообщение об ошибке при дублировании уникального поля
            } else {
                $error_message = "Registration failed: " . $e->getMessage(); // Общее сообщение об ошибке при неудачной регистрации
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registration</title>
    <!-- Подключение стилей CSS -->
    <link href="/css/registration.css" media="all" rel="stylesheet" type="text/css"/>
    <link rel="stylesheet" href="https://unpkg.com/swiper/swiper-bundle.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
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
<div class="registration-container">
    <h2>Регістрація</h2>
    <?php if(isset($error_message)): ?>
        <p class="error"><?php echo $error_message; ?></p> <!-- Вывод сообщения об ошибке, если таковое имеется -->
    <?php endif; ?>
    <?php if(isset($success_message)): ?>
        <p class="success"><?php echo $success_message; ?></p> <!-- Вывод сообщения об успешной регистрации, если таковое имеется -->
    <?php endif; ?>
    <form method="POST" action="registration.php">
        <label>Логін:</label>
        <input type="text" name="username" required>
        <label>Пароль:</label>
        <input type="password" name="password" required>
        <label>Ім`я:</label>
        <input type="text" name="first_name">
        <label>Прізвище:</label>
        <input type="text" name="last_name">
        <label>Пошта:</label>
        <input type="email" name="email" required>
        <label>Номер телефона:</label>
        <input type="text" name="phone_number">
        <label>Адреса:</label>
        <input type="text" name="address">
        <label>Дата Народження:</label>
        <input type="date" name="birthdate">
        <label>Пол:</label>
        <select name="gender">
            <option value="male">Чоловік</option>
            <option value="female">Жінка</option>
        </select>
        <input type="submit" value="Регістрація">
    </form>
    <div class="link">
        <a href="login.php">Авторизація</a>
    </div>
</div>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const navToggle = document.querySelector('.nav-toggle'); // Найти кнопку переключения меню
        const navMenu = document.querySelector('.nav-menu'); // Н   айти меню навигации

        navToggle.addEventListener('click', function () {
            navMenu.classList.toggle('open'); // Переключить класс 'open' у меню навигации при клике на кнопку
        });
    });
</script>
</body>
</html>
