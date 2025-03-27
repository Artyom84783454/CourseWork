<?php
require 'db.php'; // Подключение файла с настройками базы данных и объектом $pdo
session_start(); // Начало сессии для работы с $_SESSION

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php'); // Перенаправление на страницу логина, если пользователь не авторизован
    exit;
}

$user_id = $_SESSION['user_id']; // Получение ID пользователя из сессии

// Запрос для получения данных пользователя из таблицы users, включая balance
$stmt = $pdo->prepare("SELECT address, phone_number, email, status, balance FROM users WHERE user_id = ?");
$stmt->execute([$user_id]);
$userData = $stmt->fetch(PDO::FETCH_ASSOC); // Получение данных пользователя

// Запрос для получения данных профиля пользователя из таблицы user_profiles
$stmt = $pdo->prepare("SELECT first_name, last_name, gender, birthdate FROM user_profiles WHERE user_id = ?");
$stmt->execute([$user_id]);
$userProfileData = $stmt->fetch(PDO::FETCH_ASSOC); // Получение данных профиля пользователя

// Запрос для получения текущего тарифа пользователя из таблицы connections и tariffs
$stmt = $pdo->prepare("SELECT c.tariff_id, t.name AS tariff_name, t.price AS tariff_price FROM connections c JOIN tariffs t ON c.tariff_id = t.tariff_id WHERE c.user_id = ?");
$stmt->execute([$user_id]);
$tariffData = $stmt->fetch(PDO::FETCH_ASSOC); // Получение данных текущего тарифа пользователя

if (!$tariffData) {
    $tariffData = ['tariff_id' => null, 'tariff_name' => 'Не визначено', 'tariff_price' => '']; // Если тариф не найден, устанавливаем значения по умолчанию
}

// Запрос для получения всех доступных тарифов из таблицы tariffs
$stmt = $pdo->query("SELECT tariff_id, name, price FROM tariffs");
$tariffs = $stmt->fetchAll(PDO::FETCH_ASSOC); // Получение списка всех тарифов

// Проверка наличия профиля пользователя в таблице user_profiles
$stmt = $pdo->prepare("SELECT COUNT(*) FROM user_profiles WHERE user_id = ?");
$stmt->execute([$user_id]);
$count = $stmt->fetchColumn();

if (isset($_SESSION['message'])) {
    echo "<p>{$_SESSION['message']}</p>";
    unset($_SESSION['message']); // Удаляем сообщение из сессии после отображения
}

// Проверка метода запроса (POST)
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Обновление тарифа пользователя
    if (isset($_POST['tariff']) && $_POST['tariff'] != $tariffData['tariff_id']) {
        $new_tariff_id = $_POST['tariff'];
        // Обновление тарифа пользователя в таблице connections
        $stmt = $pdo->prepare("UPDATE connections SET tariff_id = ? WHERE user_id = ?");
        $stmt->execute([$new_tariff_id, $user_id]);
        header("Location: cabinet.php"); // Перенаправление на страницу кабинета после обновления тарифа
        exit();
    }

    // Подготовка данных для обновления пользователя
    $address = !empty($_POST['address']) ? $_POST['address'] : $userData['address'];
    $phone_number = !empty($_POST['phone_number']) ? $_POST['phone_number'] : $userData['phone_number'];
    $email = !empty($_POST['email']) ? $_POST['email'] : $userData['email'];

    // Обновление данных пользователя только если они изменились
    if ($address != $userData['address'] || $phone_number != $userData['phone_number'] || $email != $userData['email']) {
        $stmt = $pdo->prepare("UPDATE users SET address = ?, phone_number = ?, email = ? WHERE user_id = ?");
        $stmt->execute([$address, $phone_number, $email, $user_id]);
        header("Location: cabinet.php"); // Перенаправление на страницу кабинета после обновления данных
        exit();
    }

    // Подготовка данных для обновления профиля
    $first_name = !empty($_POST['first_name']) ? $_POST['first_name'] : $userProfileData['first_name'];
    $last_name = !empty($_POST['last_name']) ? $_POST['last_name'] : $userProfileData['last_name'];
    $gender = !empty($_POST['gender']) ? $_POST['gender'] : $userProfileData['gender'];
    $birthdate = !empty($_POST['birthdate']) ? $_POST['birthdate'] : $userProfileData['birthdate'];

    // Обновление данных профиля пользователя только если они изменились
    if ($first_name != $userProfileData['first_name'] || $last_name != $userProfileData['last_name'] || $gender != $userProfileData['gender'] || $birthdate != $userProfileData['birthdate']) {
        if ($count > 0) {
            // Обновление существующего профиля
            $stmt = $pdo->prepare("UPDATE user_profiles SET first_name = ?, last_name = ?, gender = ?, birthdate = ? WHERE user_id = ?");
            $stmt->execute([$first_name, $last_name, $gender, $birthdate, $user_id]);
        } else {
            // Добавление нового профиля
            $stmt = $pdo->prepare("INSERT INTO user_profiles (user_id, first_name, last_name, gender, birthdate) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$user_id, $first_name, $last_name, $gender, $birthdate]);
        }
        header("Location: cabinet.php"); // Перенаправление на страницу кабинета после обновления данных профиля
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Astroconnect - Профіль</title>
    <link rel="stylesheet" href="/css/cabinet.css"> <!-- Подключение стилей для страницы кабинета -->
    <!-- Подключение Swiper CSS -->
    <link rel="stylesheet" href="https://unpkg.com/swiper/swiper-bundle.min.css">
    <!-- Подключение Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&display=swap" rel="stylesheet">

    <script>
        // Автоматическое добавление слеша при вводе срока действия карты
        document.addEventListener('DOMContentLoaded', function () {
            const expiryDateInput = document.getElementById('expiry_date');
            expiryDateInput.addEventListener('input', function () {
                let value = this.value.replace(/\D/g, ''); // Удалить все нецифровые символы
                if (value.length >= 3) {
                    this.value = value.slice(0, 2) + '/' + value.slice(2, 6); // Добавить слеш после двух цифр
                } else {
                    this.value = value;
                }
            });

            // Функция проверки срока действия карты
            function validateExpiryDate(expiryDate) {
                // Регулярное выражение для проверки формата MM/YYYY
                const expiryDateRegex = /^(0[1-9]|1[0-2])\/[0-9]{4}$/;
                return expiryDateRegex.test(expiryDate);
            }

            // Функция для обработки отправки формы
            function validatePaymentForm(event) {
                const expiryDateInput = document.getElementById('expiry_date').value;

                // Проверка формата срока действия карты
                if (!validateExpiryDate(expiryDateInput)) {
                    alert('Ошибка: Неверный срок действия карты. Ожидаемый формат: MM/YYYY.');
                    event.preventDefault(); // Отменить отправку формы, если проверка не пройдена
                }
            }

            // Добавляем слушатель события на отправку формы
            const paymentForm = document.getElementById('payment-form');
            paymentForm.addEventListener('submit', validatePaymentForm);
        });
    </script>
</head>
<body>
<div class="container">
    <h1>Профіль користувача</h1>
    <p>Ласкаво просимо, <?php echo htmlspecialchars($userProfileData['first_name'] ?? ''); ?> <?php echo htmlspecialchars($userProfileData['last_name'] ?? ''); ?>!</p>
    <p>Логін: <?php echo htmlspecialchars($_SESSION['username'] ?? ''); ?></p>
    <p>Статус: <?php echo htmlspecialchars($userData['status'] ?? ''); ?></p> <!-- Вывод статуса пользователя -->
    <p>Ім'я: <?php echo htmlspecialchars($userProfileData['first_name'] ?? ''); ?></p>
    <p>Прізвище: <?php echo htmlspecialchars($userProfileData['last_name'] ?? ''); ?></p>
    <p>Адреса: <?php echo htmlspecialchars($userData['address'] ?? ''); ?></p>
    <p>Номер телефону: <?php echo htmlspecialchars($userData['phone_number'] ?? ''); ?></p>
    <p>Email: <?php echo htmlspecialchars($userData['email'] ?? ''); ?></p>
    <p>Стать: <?php echo htmlspecialchars($userProfileData['gender'] ?? ''); ?></p>
    <p>Дата народження: <?php echo htmlspecialchars($userProfileData['birthdate'] ?? ''); ?></p>
    <p>Поточний баланс: <?php echo htmlspecialchars($userData['balance'] ?? ''); ?> грн</p> <!-- Вывод текущего счета пользователя -->

    <h2>Тариф користувача</h2>
    <p>Назва тарифу: <?php echo htmlspecialchars($tariffData['tariff_name'] ?? ''); ?></p>
    <p>Ціна тарифу: <?php echo htmlspecialchars($tariffData['tariff_price'] ?? ''); ?></p>

    <h2>Оновлення даних</h2>
    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
        <label for="first_name">Ім'я:</label>
        <input type="text" id="first_name" name="first_name" placeholder="Нове Ім'я">

        <label for="last_name">Прізвище:</label>
        <input type="text" id="last_name" name="last_name" placeholder="Нове Прізвище">

        <label for="address">Адреса:</label>
        <input type="text" id="address" name="address" placeholder="Нова Адреса">

        <label for="phone_number">Номер телефону:</label>
        <input type="text" id="phone_number" name="phone_number" placeholder="Новий номер телефону">

        <label for="email">Email:</label>
        <input type="email" id="email" name="email" placeholder="Новий Email">

        <label for="gender">Стать:</label>
        <select id="gender" name="gender">
            <option value="">Оберіть стать</option>
            <option value="Чоловік">Чоловік</option>
            <option value="Жінка">Жінка</option>
        </select>

        <label for="birthdate">Дата народження:</label>
        <input type="date" id="birthdate" name="birthdate">

        <input type="submit" value="Оновити">
    </form>

    <h2>Зміна тарифу</h2>
    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
        <label for="tariff">Оберіть новий тариф:</label>
        <select id="tariff" name="tariff">
            <option value="">Оберіть тариф</option>
            <?php foreach ($tariffs as $tariff): ?>
                <option value="<?php echo $tariff['tariff_id']; ?>"><?php echo htmlspecialchars($tariff['name']); ?> - <?php echo htmlspecialchars($tariff['price']); ?> грн</option>
            <?php endforeach; ?>
        </select>
        <input type="submit" value="Змінити тариф">
    </form>

    <!-- Форма для поповнення балансу -->
    <h2>Поповнення балансу</h2>
    <form action="payment.php" method="post" id="payment-form">
        <input type="hidden" name="user_id" value="<?php echo $user_id; ?>">

        <label for="amount">Сума поповнення:</label>
        <input type="number" id="amount" name="amount" step="0.01" min="1" required>

        <label for="card_number">Номер карти:</label>
        <input type="text" id="card_number" name="card_number" maxlength="16" required>

        <label for="expiry_date">Термін дії (MM/YYYY):</label>
        <input type="text" id="expiry_date" name="expiry_date" placeholder="MM/YYYY" maxlength="7" required>

        <label for="cvv">CVV:</label>
        <input type="text" id="cvv" name="cvv" maxlength="3" required>

        <input type="submit" value="Поповнити баланс">
    </form>

    <a href="logout.php">Вийти</a>
</div>
</body>
</html>
