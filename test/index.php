<?php
session_start();
require 'config.php'; // Подключение к базе данных

// Проверка, залогинен ли пользователь как администратор
if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: login.php");
    exit();
}

// Извлечение пользователей с пагинацией
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 5; // Количество записей на странице
$offset = ($page - 1) * $limit;

$stmt = $pdo->prepare("SELECT * FROM users LIMIT :limit OFFSET :offset");
$stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Подсчет общего количества пользователей для пагинации
$totalUsers = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
$totalPages = ceil($totalUsers / $limit);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Management</title>
    <link rel="stylesheet" href="css/styles.css">
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

        a {
            color: #4CAF50;
            text-decoration: none;
        }

        a:hover {
            text-decoration: underline;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }

        th {
            background-color: #4CAF50;
            color: white;
        }

        tr:hover {
            background-color: #f1f1f1;
        }

        button {
            background-color: #4CAF50;
            color: white;
            border: none;
            padding: 10px 15px;
            cursor: pointer;
            border-radius: 5px;
            margin: 5px;
        }

        button:hover {
            background-color: #45a049;
        }

        .modal {
            display: none;
            position: fixed;
            z-index: 1;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0,0,0,0.4);
        }

        .modal-content {
            background-color: #fefefe;
            margin: 15% auto;
            padding: 20px;
            border: 1px solid #888;
            width: 80%;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.2);
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

        .pagination {
            text-align: center;
            margin: 20px 0;
        }

        .pagination a {
            margin: 0 5px;
            padding: 8px 12px;
            border: 1px solid #4CAF50;
            border-radius: 5px;
            color: #4CAF50;
            text-decoration: none;
        }

        .pagination a:hover {
            background-color: #4CAF50;
            color: white;
        }

        .exit-button {
            float: right; /* Выравнивание кнопки вправо */
            margin-top: -50px; /* Смещение на верх, чтобы выровнять с заголовком */
        }
    </style>
    <script>
        let userIdToDelete = null;

        function openModal(username, userId) {
            userIdToDelete = userId; // Сохраняем ID пользователя для удаления
            const modal = document.getElementById("myModal");
            document.getElementById("modalUsername").innerText = username;
            modal.style.display = "block";
        }

        function closeModal() {
            const modal = document.getElementById("myModal");
            modal.style.display = "none";
        }

        function confirmDelete() {
            window.location.href = `delete_user.php?id=${userIdToDelete}`;
        }
    </script>
</head>
<body>
    <h1>User Management</h1>
    <form action="logout.php" method="post" class="exit-button">
        <button type="submit">Exit</button>
    </form>
    <a href="add_user.php">Add New User</a>

    <table>
        <thead>
            <tr>
                <th>Username</th>
                <th>First Name</th>
                <th>Last Name</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($users as $user): ?>
            <tr>
                <td><?php echo htmlspecialchars($user['username']); ?></td>
                <td><?php echo htmlspecialchars($user['first_name']); ?></td>
                <td><?php echo htmlspecialchars($user['last_name']); ?></td>
                <td>
                    <a href="edit_user.php?id=<?php echo $user['id']; ?>">Edit</a>
                    <?php if ($user['username'] !== 'admin'): // Exclude delete button for admin user ?>
                        <a href="javascript:void(0);" onclick="openModal('<?php echo htmlspecialchars($user['username']); ?>', <?php echo $user['id']; ?>)">Delete</a>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <div class="pagination">
        <strong>Pages:</strong>
        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
            <a href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
        <?php endfor; ?>
    </div>

    <!-- Модальное окно -->
    <div id="myModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal()">&times;</span>
            <h2>Confirmation of deletion</h2>
            <p>Do you really want to delete the user "<span id="modalUsername"></span>"?</p>
            <button onclick="confirmDelete()">Yes</button>
            <button onclick="closeModal()">No</button>
        </div>
    </div>

    <script>
        // Закрытие модального окна при клике вне его
        window.onclick = function(event) {
            const modal = document.getElementById("myModal");
            if (event.target == modal) {
                closeModal();
            }
        }
    </script>
</body>
</html>
