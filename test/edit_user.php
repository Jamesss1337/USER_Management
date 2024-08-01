<?php
session_start();
require 'config.php';

// Check if the user is logged in as admin
if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: login.php");
    exit();
}

// Fetch user details
if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = :id");
    $stmt->execute(['id' => $id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
}

// Initialize an error message variable
$errorMessage = "";

// Update user data
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];
    $gender = $_POST['gender'];
    $birth_date = $_POST['birth_date'];

    // Проверка на уникальность имени пользователя, если оно изменено
    if ($username !== $user['username']) {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = :username");
        $stmt->execute(['username' => $username]);
        $existingUser = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($existingUser) {
            $errorMessage = "A user with that name already exists.";
        } else {
            // Если имя пользователя уникально, обновляем данные
            $stmt = $pdo->prepare("UPDATE users SET username = :username, first_name = :first_name, last_name = :last_name,
                                    gender = :gender, birth_date = :birth_date WHERE id = :id");
            $stmt->execute([
                ':username' => $username,
                ':first_name' => $first_name,
                ':last_name' => $last_name,
                ':gender' => $gender,
                ':birth_date' => $birth_date,
                ':id' => $id
            ]);
            header("Location: index.php");
            exit();
        }
    } else {
        // Если имя пользователя не изменилось, просто обновляем остальные данные
        $stmt = $pdo->prepare("UPDATE users SET first_name = :first_name, last_name = :last_name,
                                gender = :gender, birth_date = :birth_date WHERE id = :id");
        $stmt->execute([
            ':first_name' => $first_name,
            ':last_name' => $last_name,
            ':gender' => $gender,
            ':birth_date' => $birth_date,
            ':id' => $id
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
    <title>Edit User</title>
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

        input[type="text"], input[type="date"], select {
            width: 100%;
            padding: 10px;
            margin: 10px 0;
            border: 1px solid #ccc;
            border-radius: 5px;
        }

        input[type="text"]:focus, input[type="date"]:focus, select:focus {
            border-color: #4CAF50;
        }

        .error {
            color: red;
            margin-top: 10px;
            font-weight: bold;
            text-align: center;
        }

        /* Styles for the modal */
        .modal {
            display: none; /* Скрываем модальное окно по умолчанию */
            position: fixed;
            z-index: 1;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0,0,0,0.4);
            align-items: center;
            justify-content: center;
        }

        .modal-content {
            background-color: #fefefe;
            margin: 15% auto;
            padding: 20px;
            border: 1px solid #888;
            width: 80%;
        }

        .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
        }

        .close:hover,
        .close:focus {
            color: black;
            text-decoration: none;
            cursor: pointer;
        }

        .highlight {
            color: red; /* Подсветка нового значения красным */
            font-weight: bold; /* Установить жирный шрифт */
        }
    </style>
    <script>
        function showModal() {
            const usernameOld = "<?php echo htmlspecialchars($user['username']); ?>";
            const firstNameOld = "<?php echo htmlspecialchars($user['first_name']); ?>";
            const lastNameOld = "<?php echo htmlspecialchars($user['last_name']); ?>";
            const genderOld = "<?php echo htmlspecialchars($user['gender']); ?>";
            const birthDateOld = "<?php echo htmlspecialchars($user['birth_date']); ?>";

            const usernameNew = document.querySelector('input[name="username"]').value;
            const firstNameNew = document.querySelector('input[name="first_name"]').value;
            const lastNameNew = document.querySelector('input[name="last_name"]').value;
            const genderNew = document.querySelector('select[name="gender"]').value;
            const birthDateNew = document.querySelector('input[name="birth_date"]').value;

            // Проверяем, изменились ли данные
            const isChanged = (
                usernameOld !== usernameNew ||
                firstNameOld !== firstNameNew ||
                lastNameOld !== lastNameNew ||
                genderOld !== genderNew ||
                birthDateOld !== birthDateNew
            );

            const modal = document.getElementById('myModal');
            // Если ничего не изменилось, показываем другое сообщение
            if (!isChanged) {
                document.getElementById('modalText').innerHTML = "You haven't changed anything.";
            } else {
                // Обновляем текст в модальном окне с подсветкой
                document.getElementById('modalText').innerHTML = `
                    Do you really want to change the user's data?
                    <br><strong>Username:</strong> ${highlightChange(usernameOld, usernameNew)}
                    <br><strong>First Name:</strong> ${highlightChange(firstNameOld, firstNameNew)}
                    <br><strong>Last Name:</strong> ${highlightChange(lastNameOld, lastNameNew)}
                    <br><strong>Gender:</strong> ${highlightChange(genderOld, genderNew)}
                    <br><strong>Birth Date:</strong> ${highlightChange(birthDateOld, birthDateNew)}
                `;
            }

            modal.style.display = 'flex';
        }

        function highlightChange(oldValue, newValue) {
            return oldValue === newValue ? oldValue : `${oldValue} -> <span class="highlight">${newValue}</span>`;
        }

        function closeModal() {
            document.getElementById('myModal').style.display = 'none';
        }

        function confirmUpdate(event) {
            // Отправляем форму только при подтверждении
            document.getElementById('updateForm').submit();
        }

        window.onclick = function(event) {
            const modal = document.getElementById('myModal');
            if (event.target === modal) {
                closeModal();
            }
        }
    </script>
</head>
<body>
    <h1>Edit User</h1>
    <button type="button" onclick="window.location.href='index.php';">Back</button>

    <!-- Display error message if exists -->
    <?php if ($errorMessage): ?>
        <div class="error"><?php echo $errorMessage; ?></div>
    <?php endif; ?>

    <form id="updateForm" action="" method="post" onsubmit="event.preventDefault(); showModal();">
        <label><strong>Username:</strong></label>
        <input type="text" name="username" value="<?php echo htmlspecialchars($user['username']); ?>" required>
        <label><strong>First Name:</strong></label>
        <input type="text" name="first_name" value="<?php echo htmlspecialchars($user['first_name']); ?>">
        <label><strong>Last Name:</strong></label>
        <input type="text" name="last_name" value="<?php echo htmlspecialchars($user['last_name']); ?>">
        <label><strong>Gender:</strong></label>
        <select name="gender">
            <option value="male" <?php if ($user['gender'] === 'male') echo 'selected'; ?>>Male</option>
            <option value="female" <?php if ($user['gender'] === 'female') echo 'selected'; ?>>Female</option>
        </select>
        <label><strong>Birth Date:</strong></label>
        <input type="date" name="birth_date" value="<?php echo htmlspecialchars($user['birth_date']); ?>">
        <button type="submit">Update User</button>
    </form>

    <!-- Модальное окно -->
    <div id="myModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal()">&times;</span>
            <p id="modalText"></p>
            <button onclick="confirmUpdate()">Yes</button>
            <button onclick="closeModal()">No</button>
        </div>
    </div>
</body>
</html>
