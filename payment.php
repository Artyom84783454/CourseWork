<?php
require_once 'db.php'; // Подключение к базе данных
session_start(); // Начало сессии для использования сообщений и перенаправлений

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Проверяем наличие и корректность параметров
    if (!isset($_POST['user_id'], $_POST['amount'], $_POST['card_number'], $_POST['expiry_date'], $_POST['cvv'])) {
        die("Ошибка: Не все данные были переданы.");
    }

    $user_id = $_POST['user_id'];
    $amount = $_POST['amount'];
    $card_number = $_POST['card_number'];
    $expiry_date = $_POST['expiry_date'];
    $cvv = $_POST['cvv'];

    // Проверяем, что amount не пусто и является числом
    if (!is_numeric($amount) || $amount <= 0) {
        die("Ошибка: Неверное значение суммы.");
    }

    // Проверка номера карты (напр., длина и формат)
    if (!preg_match("/^[0-9]{16}$/", $card_number)) {
        die("Ошибка: Неверный номер карты.");
    }

    // Проверка срока действия карты (формат MM/YYYY)
    if (!preg_match("/^(0[1-9]|1[0-2])\/[0-9]{4}$/", $expiry_date)) {
        die("Ошибка: Неверный срок действия карты. Ожидаемый формат: MM/YYYY.");
    }

    // Проверка CVV (длина и формат)
    if (!preg_match("/^[0-9]{3}$/", $cvv)) {
        die("Ошибка: Неверный CVV.");
    }

    // Подготовка данных для вставки в таблицу payments
    $payment_date = date('Y-m-d H:i:s'); // Текущая дата и время
    $payment_method = 'credit_card'; // Метод оплаты (можно дополнительно расширить функционал)

    // Вставка данных в таблицу payments
    $sql = "INSERT INTO payments (user_id, amount, payment_date, payment_method, status)
            VALUES (:user_id, :amount, :payment_date, :payment_method, 'completed')";

    // Подготовка и выполнение запроса с использованием подготовленных запросов PDO
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->bindParam(':amount', $amount, PDO::PARAM_STR);
    $stmt->bindParam(':payment_date', $payment_date, PDO::PARAM_STR);
    $stmt->bindParam(':payment_method', $payment_method, PDO::PARAM_STR);

    try {
        $stmt->execute();

        // Обновление баланса пользователя
        $updateBalanceSQL = "UPDATE users SET balance = balance + :amount WHERE user_id = :user_id";
        $updateBalanceStmt = $pdo->prepare($updateBalanceSQL);
        $updateBalanceStmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $updateBalanceStmt->bindParam(':amount', $amount, PDO::PARAM_STR);
        $updateBalanceStmt->execute();

        // Устанавливаем сообщение об успешной оплате
        $_SESSION['message'] = "Баланс успешно пополнен на $amount грн.";

        // Перенаправление на страницу кабинета с обновленным балансом
        header("Location: cabinet.php");
        exit();
    } catch (PDOException $e) {
        die("Ошибка при выполнении запроса: " . $e->getMessage());
    }
}
?>

<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Оплата</title>
</head>
<body>
<form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
    <label for="card_number">Номер карти:</label>
    <input type="text" id="card_number" name="card_number" required><br><br>

    <label for="expiry_date">Термін дії (MM/YYYY):</label>
    <input type="text" id="expiry_date" name="expiry_date" placeholder="MM/YYYY" maxlength="7" required><br><br>

    <label for="cvv">CVV:</label>
    <input type="text" id="cvv" name="cvv" maxlength="3" required><br><br>

    <label for="amount">Сума оплати:</label>
    <input type="number" id="amount" name="amount" step="0.01" required min="1"><br><br>

    <input type="hidden" name="user_id" value="<?php echo htmlspecialchars($user_id); ?>">

    <button type="submit">Оплатити</button>
</form>

</body>
</html>
