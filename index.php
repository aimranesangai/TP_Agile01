<?php

define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'todolist');
define('DB_HOST', '127.0.0.1');
define('DB_PORT', '3306');

$dsn = 'mysql:host=' . DB_HOST . ';port=' . DB_PORT
    . ';dbname=' . DB_NAME . ';charset=utf8mb4';

try {
    $pdo = new PDO($dsn, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die('Database connection failed: ' . $e->getMessage());
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'new') {
        $title = trim($_POST['title'] ?? '');

        if ($title !== '') {
            $sql = "INSERT INTO todo (title) VALUES (:title)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute(['title' => $title]);
        }
    }

    if ($action === 'delete') {
        $id = filter_var($_POST['id'] ?? '', FILTER_VALIDATE_INT);

        if ($id !== false && $id !== null && $id > 0) {
            $sql = "DELETE FROM todo WHERE id = :id";
            $stmt = $pdo->prepare($sql);
            $stmt->execute(['id' => $id]);
        }
    }

    if ($action === 'toggle') {
        $id = filter_var($_POST['id'] ?? '', FILTER_VALIDATE_INT);

        if ($id !== false && $id !== null && $id > 0) {
            $sql = "UPDATE todo SET done = 1 - done WHERE id = :id";
            $stmt = $pdo->prepare($sql);
            $stmt->execute(['id' => $id]);
        }
    }

    header('Location: index.php');
    exit;
}

$sql = "SELECT * FROM todo ORDER BY created_at DESC";
$stmt = $pdo->query($sql);
$taches = $stmt->fetchAll(PDO::FETCH_OBJ);

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>TodoList</title>

    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="min-h-screen bg-gray-100">

    <nav class="bg-gray-800 shadow-md">
        <div class="max-w-3xl mx-auto px-6 py-4">
            <a href="index.php"
                class="text-white text-xl font-bold">
                TodoList
            </a>
        </div>
    </nav>

    <main class="max-w-3xl mx-auto px-6 py-12">

        <h1 class="text-3xl font-bold text-gray-800 text-center mb-8">
            My Todo List
        </h1>

        <form method="POST"
            action="index.php"
            class="flex gap-3 mb-8">

            <input
                type="text"
                name="title"
                placeholder="Task Title"
                required
                class="flex-1 px-4 py-3 border border-gray-300
                rounded-lg outline-none focus:ring-2
                focus:ring-blue-500"
            >

            <button
                type="submit"
                name="action"
                value="new"
                class="bg-blue-600 hover:bg-blue-700
                text-white font-medium px-6 py-3
                rounded-lg transition"
            >
                Add
            </button>

        </form>

        <div class="space-y-3">

            <?php foreach ($taches as $tache): ?>

                <div class="<?= $tache->done
                    ? 'bg-green-100 border-green-300'
                    : 'bg-yellow-100 border-yellow-300' ?>
                    border rounded-lg p-4 flex items-center
                    justify-between gap-4">

                    <span class="text-gray-700 break-words">
                        <?= htmlspecialchars(
                            $tache->title,
                            ENT_QUOTES,
                            'UTF-8'
                        ) ?>
                    </span>

                    <form method="POST"
                        action="index.php"
                        class="flex gap-2 shrink-0">

                        <input
                            type="hidden"
                            name="id"
                            value="<?= $tache->id ?>"
                        >

                        <button
                            type="submit"
                            name="action"
                            value="toggle"
                            class="bg-blue-600 hover:bg-blue-700
                            text-white text-sm px-3 py-2
                            rounded-md transition"
                        >
                            <?= $tache->done ? 'Undo' : 'Done' ?>
                        </button>

                        <button
                            type="submit"
                            name="action"
                            value="delete"
                            class="bg-red-500 hover:bg-red-600
                            text-white text-sm px-3 py-2
                            rounded-md transition"
                        >
                            X
                        </button>

                    </form>

                </div>

            <?php endforeach; ?>

            <?php if (empty($taches)): ?>

                <p class="text-center text-gray-500 py-8">
                    No tasks yet. Add your first task!
                </p>

            <?php endif; ?>

        </div>

    </main>

</body>
</html>
