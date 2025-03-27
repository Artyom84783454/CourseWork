<?php
// Подключаем файл db.php для работы с базой данных
require 'db.php';
session_start();

// Функция для получения данных пользователя по имени пользователя
function getUserByUsername($pdo, $username) {
    $sql = "SELECT u.*, p.*, t.name AS tariff_name, c.connection_start, c.connection_end, c.ip_address, u.balance
            FROM users u 
            LEFT JOIN user_profiles p ON u.user_id = p.user_id 
            LEFT JOIN connections c ON u.user_id = c.user_id 
            LEFT JOIN tariffs t ON c.tariff_id = t.tariff_id
            WHERE u.username = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$username]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

// Функция для обновления данных пользователя
function updateUser($pdo, $user_id, $email, $first_name, $last_name, $birthdate, $gender, $role, $status, $balance, $new_password = null) {
    // Получаем текущее время с учетом смещения TimeZone: UTC+3
    $current_time = gmdate("Y-m-d H:i:s", time() + 3*60*60);

    // Если статус меняется на "online", обновляем поле connection_start и генерируем новый IP
    if ($status === 'online') {
        $sql = "UPDATE connections SET connection_start = ?, connection_end = NULL, ip_address = ? WHERE user_id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$current_time, generateIP($pdo, $user_id), $user_id]);
    }

    // Если статус меняется на "offline", записываем текущее время в поле connection_end
    if ($status === 'offline') {
        $sql = "UPDATE connections SET connection_end = ?, ip_address = NULL WHERE user_id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$current_time, $user_id]);
    }

    // Обновляем данные пользователя в таблице users, включая баланс
    $sql = "UPDATE users SET email = ?, role = ?, status = ?, balance = ? WHERE user_id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$email, $role, $status, $balance, $user_id]);

    // Обновляем данные пользователя в таблице user_profiles
    $sql = "UPDATE user_profiles SET first_name = ?, last_name = ?, birthdate = ?, gender = ? WHERE user_id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$first_name, $last_name, $birthdate, $gender, $user_id]);

    // Обновляем пароль, если передан
    if ($new_password !== null) {
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        $sql = "UPDATE users SET password_hash = ? WHERE user_id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$hashed_password, $user_id]);
    }

    return true; // Возвращаем успех
}

// Функция для генерации нового IP
function generateIP($pdo, $user_id) {
    // Генерируем случайный IP
    $ip = mt_rand(0, 255) . "." . mt_rand(0, 255) . "." . mt_rand(0, 255) . "." . mt_rand(0, 255);

    // Вставляем сгенерированный IP в базу данных
    $sql = "UPDATE connections SET ip_address = ? WHERE user_id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$ip, $user_id]);

    return $ip;
}

// Если нажата кнопка выхода, вызываем функцию logout
if(isset($_POST['logout'])) {
    logout();
    header("Location: login.php"); // Перенаправляем на страницу входа после выхода
    exit;
}

// Функция для выхода пользователя
function logout() {
    session_unset(); // Очищаем все переменные сессии
    session_destroy(); // Разрушаем сессию
}

// Обновление пользователя
if(isset($_POST['update_user'])) {
    $user_id = $_POST['user_id'];
    $email = $_POST['email'];
    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];
    $birthdate = $_POST['birthdate'];
    $gender = $_POST['gender'];
    $role = $_POST['role'];
    $status = $_POST['status'];
    $balance = $_POST['balance'];
    $new_password = isset($_POST['new_password']) ? $_POST['new_password'] : null;

    // Проверка наличия нового пароля
    if($new_password === '') {
        $new_password = null; // Если новый пароль пустой, присваиваем null
    }

    // Проверяем, что значение статуса находится в списке допустимых значений
    if ($status !== 'online' && $status !== 'offline') {
        // Выводим сообщение об ошибке
        echo "<p class='error-msg'>Недопустиме значення для статусу користувача.</p>";
        // Останавливаем выполнение кода
        exit;
    }

    // Обновляем пользователя
    if(updateUser($pdo, $user_id, $email, $first_name, $last_name, $birthdate, $gender, $role, $status, $balance, $new_password)) {
        echo "<p class='success-msg'>Дані користувача оновлені успішно.</p>";
    } else {
        echo "<p class='error-msg'>Помилка при оновленні даних користувача.</p>";
    }
}

// Если нажата кнопка "Generate IP"
if(isset($_POST['generate_ip'])) {
    $user_id = $_POST['user_id'];
    $ip = generateIP($pdo, $user_id);
    echo "<p class='success-msg'>Сгенеровано новий IP: $ip</p>";
}

// Вспомогательная функция для установки атрибута selected
function isSelected($value, $selection) {
    return $value == $selection ? 'selected' : '';
}
?>

<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Astroconnect</title>
    <!-- Подключение Swiper CSS -->
    <link rel="stylesheet" href="https://unpkg.com/swiper/swiper-bundle.min.css">
    <!-- Подключение Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&display=swap" rel="stylesheet">
    <!-- Подключение вашего CSS файла -->
    <link rel="stylesheet" href="/css/admin.css">
</head>
<body>
<form action="" method="post" class="search-form">
    <div class="form-group">
        <label>Введіть ім`я користувача:</label>
        <input type="text" name="search_username" class="form-control">
        <button type="submit" class="btn btn-primary">Пошук</button>
        <div class="logout-container">
            <form action="" method="post">
                <button type="submit" name="logout" class="logout-button">Вийти</button>
            </form>
        </div>
        <!-- Кнопка для перехода на страницу тарифов -->
        <form action="/tarifs.php" method="get">
            <button type="submit" class="btn btn-primary">Перейти до тарифів</button>
        </form>
    </div>
</form>

<?php
// Если передано имя пользователя для поиска
if(isset($_POST['search_username'])) {
    $search_username = $_POST['search_username'];
    $user = getUserByUsername($pdo, $search_username);
    if ($user) {
        echo "<form action='' method='post' class='user-form'>";
        echo "<h1 class='user_prof'>Профіль користувача</h1>";
        foreach ($user as $key => $value) {
            if ($key !== 'user_id' && $key !== 'profile_id' && $key !== 'password_hash') {
                // Переводим метки на украинский
                $labels = [
                    'username' => "Ім'я користувача",
                    'email' => 'Електронна пошта',
                    'phone_number' => 'Номер телефону',
                    'registration_date' => 'Дата реєстрації',
                    'address' => 'Адреса',
                    'role' => 'Роль',
                    'status' => 'Статус',
                    'first_name' => "Ім'я",
                    'last_name' => 'Прізвище',
                    'birthdate' => 'Дата народження',
                    'gender' => 'Стать',
                    'tariff_name' => 'Назва тарифу',
                    'connection_start' => "Початок з'єднання",
                    'connection_end' => "Кінець з'єднання",
                    'ip_address' => "IP адреса",
                    'balance' => "Баланс"
                ];

                // Применяем переведенную метку или оставляем оригинальную, если перевод отсутствует
                $label = isset($labels[$key]) ? $labels[$key] : $key;

                echo "<div class='form-group'>";
                echo "<label>$label:</label>";
                if ($key === 'role') {
                    // Создаем выпадающее меню для выбора роли
                    echo "<select name='$key'>";
                    $roles = ['user', 'admin', 'support']; // Варианты ролей
                    foreach ($roles as $role) {
                        echo "<option " . isSelected($role, $value) . ">$role</option>";
                    }
                    echo "</select>";
                } else if ($key === 'status') {
                    // Создаем выпадающее меню для выбора статуса
                    echo "<select name='$key'>";
                    $statuses = ['online', 'offline']; // Варианты статусов
                    foreach ($statuses as $status) {
                        echo "<option " . isSelected($status, $value) . ">$status</option>";
                    }
                    echo "</select>";
                } else if ($key === 'balance') {
                    // Поле для изменения баланса
                    echo "<input type='text' name='$key' value='$value' class='form-control'>";
                } else {
                    echo "<input type='text' name='$key' value='$value' class='form-control'>";
                }
                echo "</div>";
            }
        }
        echo "<input type='hidden' name='user_id' value='{$user['user_id']}'>";
        echo "<div class='form-group'>";
        echo "<label>Новий пароль:</label>";
        echo "<input type='password' name='new_password' class='form-control'>";
        echo "</div>";
        echo "<button type='submit' name='update_user' class='btn btn-primary'>Оновити</button>";

        // Кнопка для генерации нового IP
        echo "<div id='generate_ip_btn'><button type='submit' name='generate_ip' class='btn btn-secondary'>Генерувати новий IP</button></div>";

        echo "</form>";
    } else {
        echo "<p class='error-msg'>Користувача не знайдено.</p>";
    }
}
?>
</body>
</html>

