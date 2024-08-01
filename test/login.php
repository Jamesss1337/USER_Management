<?php
session_start();
require 'config.php'; // Подключение к базе данных

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Обработка отправки формы входа
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    // Базовая валидация
    if (!empty($username) && !empty($password)) {
        // Предполагается, что имя пользователя должно быть "admin"
        if ($username === 'admin') {
            // Подготовка SQL-запроса для проверки наличия пользователя
            $stmt = $pdo->prepare("SELECT * FROM users WHERE username = :username");
            $stmt->execute(['username' => $username]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            // Проверяем существует ли пользователь и пароль
            if ($user) {
                // Проверяем совпадение пароля с хэшом
                if (password_verify($password, $user['password'])) {
                    // Успешный вход, устанавливаем сессию
                    $_SESSION['admin_logged_in'] = true;
                    header("Location: index.php");
                    exit();
                } else {
                    // Пароль неверный
                    $error = "Invalid password.";
                }
            } else {
                // Пользователь не найден
                $error = "NO user";
            }
        } else {
            // Если имя пользователя не "admin"
            $error = "Only the administrator can log in.";
        }
    } else {
        $error = "Please enter username and password.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            color: #333;
            margin: 0;
            padding: 20px;
        }

        h1 {
            text-align: center;
            color: #4CAF50;
        }

        form {
            max-width: 400px;
            margin: 0 auto;
            background: white;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            border-radius: 10px;
        }

        label {
            display: block;
            margin-bottom: 5px;
        }

        input[type="text"],
        input[type="password"] {
            width: 100%;
            padding: 10px;
            margin: 10px 0;
            border: 1px solid #ccc;
            border-radius: 5px;
        }

        button {
            background-color: #4CAF50;
            color: white;
            border: none;
            padding: 10px;
            cursor: pointer;
            border-radius: 5px;
            width: 100%;
        }

        button:hover {
            background-color: #45a049;
        }

        p {
            text-align: center;
            color: red;
        }
    </style>
</head>
<body>
    <h1>Login</h1>
    <form action="" method="post">
        <label>Username:</label>
        <input type="text" name="username" required>
        <label>Password:</label>
        <input type="password" name="password" required>
        <button type="submit">Login</button>
    </form>
    <?php if (!empty($error)) echo '<p>' . htmlspecialchars($error) . '</p>'; ?>
</body>
</html>
