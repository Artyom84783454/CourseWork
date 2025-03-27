<?php
require_once 'db.php';
session_start();

// Функция для получения списка тарифов
function getTariffs($pdo) {
    $query = "SELECT * FROM tariffs";
    $stmt = $pdo->query($query);
    return $stmt->fetchAll();
}

// Функция для обновления тарифа
function updateTariff($pdo, $tariff_id, $name, $speed, $price, $description, $valid_from, $valid_to) {
    $query = "UPDATE tariffs SET name=?, speed=?, price=?, description=?, valid_from=?, valid_to=? WHERE tariff_id=?";
    $stmt = $pdo->prepare($query);
    $stmt->execute([$name, $speed, $price, $description, $valid_from, $valid_to, $tariff_id]);
    return $stmt;
}

// Обработка POST запроса на обновление тарифа
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $tariff_id = $_POST['tariff_id'];
    $name = $_POST['name'];
    $speed = $_POST['speed'];
    $price = $_POST['price'];
    $description = $_POST['description'];
    $valid_from = $_POST['valid_from'];
    $valid_to = $_POST['valid_to'];

    // Проверка на пустое значение для даты valid_to
    $valid_to = !empty($valid_to) ? $valid_to : null;

    updateTariff($pdo, $tariff_id, $name, $speed, $price, $description, $valid_from, $valid_to);
    // Редирект обратно на страницу после обновления
    header("Location: {$_SERVER['PHP_SELF']}");
    exit();
}

// Получение списка тарифов
$tariffs = getTariffs($pdo);

?>
<!DOCTYPE html>
<html lang="UK">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Подключение Swiper CSS -->
    <link rel="stylesheet" href="https://unpkg.com/swiper/swiper-bundle.min.css">
    <!-- Подключение Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="/css/tarifs.css" media="all" rel="stylesheet" type="text/css"/>
    <title>Редактирование тарифов</title>
</head>
<body>
<h1>Редактирование тарифов</h1>
<table>
    <thead>
    <tr>
        <th>Идентификатор</th>
        <th>Название</th>
        <th>Скорость (Мбит/с)</th>
        <th>Цена</th>
        <th>Описание</th>
        <th>Дата начала</th>
        <th>Дата окончания</th>
        <th>Действия</th>
    </tr>
    </thead>
    <tbody>
    <?php foreach ($tariffs as $tariff): ?>
        <tr>
            <form method="post">
                <td><div id="tarifff"><?= $tariff['tariff_id'] ?></div></td>
                <td><input type="text" name="name" value="<?= $tariff['name'] ?>"></td>
                <td><input type="number" name="speed" value="<?= $tariff['speed'] ?>"></td>
                <td><input type="text" name="price" value="<?= $tariff['price'] ?>"></td>
                <td><input type="text" name="description" value="<?= $tariff['description'] ?>"></td>
                <td><input type="date" name="valid_from" value="<?= $tariff['valid_from'] ?>"></td>
                <td><input type="date" name="valid_to" value="<?= $tariff['valid_to'] ?>"></td>
                <td><button type="submit">Сохранить</button></td>
                <input type="hidden" name="tariff_id" value="<?= $tariff['tariff_id'] ?>">
            </form>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>

    <a href="admin.php" class="button">Назад</a>

</body>
</html>
