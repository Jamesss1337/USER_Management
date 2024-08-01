<?php
session_start();
require 'config.php';

// Check if the user is logged in as admin
if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: login.php");
    exit();
}

$error = '';

// Handle user creation
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];

    // Check if the username already exists
    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = :username");
    $stmt->execute(['username' => $username]);
    $existingUser = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($existingUser) {
        $error = "A user with that name already exists.";
    } else {
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT); // Hash the password
        $first_name = $_POST['first_name'];
        $last_name = $_POST['last_name'];
        $gender = $_POST['gender'];
        $birth_date = $_POST['birth_date'];

        // Insert new user into the database
        $stmt = $pdo->prepare("INSERT INTO users (username, password, first_name, last_name, gender, birth_date)
                                VALUES (:username, :password, :first_name, :last_name, :gender, :birth_date)");
        $stmt->execute([
            ':username' => $username,
            ':password' => $password,
            ':first_name' => $first_name,
            ':last_name' => $last_name,
            ':gender' => $gender,
            ':birth_date' => $birth_date
        ]);

        header("Location: index.php");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add User</title>
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

        button {
            background-color: #4CAF50;
            color: white;
            border: none;
            padding: 10px 15px;
            cursor: pointer;
            border-radius: 5px;
            margin-top: 10px;
        }

        button:hover {
            background-color: #45a049;
        }

        form {
            max-width: 600px;
            margin: 0 auto;
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }

        label {
            display: block;
            margin-bottom: 5px;
        }

        input[type="text"], input[type="password"], input[type="date"], select {
            width: 100%;
            padding: 10px;
            margin: 10px 0;
            border: 1px solid #ccc;
            border-radius: 5px;
        }

        input[type="text"]:focus, input[type="password"]:focus, input[type="date"]:focus, select:focus {
            border-color: #4CAF50;
        }

        p {
            color: red;
            text-align: center;
        }
    </style>
</head>
<body>
    <h1>Add New User</h1>
    <button type="button" onclick="window.location.href='index.php';">Back</button>

    <form action="" method="post">
        <label>Username:</label>
        <input type="text" name="username" required>
        <label>Password:</label>
        <input type="password" name="password" required>
        <label>First Name:</label>
        <input type="text" name="first_name">
        <label>Last Name:</label>
        <input type="text" name="last_name">
        <label>Gender:</label>
        <select name="gender">
            <option value="male">Male</option>
            <option value="female">Female</option>
        </select>
        <label>Birth Date:</label>
        <input type="date" name="birth_date">
        <button type="submit">Add User</button>
    </form>

    <?php if (!empty($error)) echo '<p>' . htmlspecialchars($error) . '</p>'; ?>
</body>
</html>
