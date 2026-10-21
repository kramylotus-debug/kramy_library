<?php
require_once "db.php";

$search = trim($_GET["search"] ?? "");

if ($search !== "") {
    $stmt = $pdo->prepare(
        "SELECT * FROM books
         WHERE title LIKE ? OR author LIKE ? OR category LIKE ?
         ORDER BY title ASC"
    );
    $term = "%" . $search . "%";
    $stmt->execute([$term, $term, $term]);
    $books = $stmt->fetchAll(PDO::FETCH_ASSOC);
} else {
    $stmt = $pdo->query("SELECT * FROM books ORDER BY title ASC");
    $books = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Books | Kramy Library</title>

    <style>
        * { box-sizing: border-box; }

        body {
            margin: 0;
            padding: 30px;
            background: #f4f6f9;
            font-family: Arial, sans-serif;
            color: #243247;
        }

        .container {
            max-width: 1200px;
            margin: auto;
        }

        .header {
            background: #243247;
            color: white;
            padding: 25px;
            border-radius: 10px;
            margin-bottom: 25px;
        }

        .card {
            background: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 2px 8px #ddd;
        }

        .search {
            display: flex;
            gap: 10px;
            margin: 20px 0;
        }

        input {
            flex: 1;
            min-width: 0;
            padding: 12px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }

        button {
            padding: 12px 20px;
            background: #243247;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

        .table-wrapper { overflow-x: auto; }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }

        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }

        th {
            background: #243247;
            color: white;
        }

        tr:hover { background: #f4f6f9; }

        a { color: #243247; }

        .count {
            color: #526174;
            margin-top: 10px;
        }

        @media (max-width: 600px) {
            body { padding: 12px; }
            .search { flex-direction: column; }
        }
    </style>
</head>

<body>
<div class="container">

    <div class="header">
        <h1>Kramy Library</h1>
        <p>Books Collection</p>
    </div>

    <p><a href="index.php">← Back to Dashboard</a></p>

    <div class="card">
        <h2>Library Books</h2>
        <p>Browse and search the books in our collection.</p>

        <form class="search" method="GET">
            <input
                type="text"
                name="search"
                placeholder="Search by title, author or category..."
                value="<?= htmlspecialchars($search) ?>"
            >
            <button type="submit">Search</button>
        </form>

        <p class="count">
            Books found: <?= count($books) ?>
        </p>

        <?php if (count($books) > 0): ?>
            <div class="table-wrapper">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Title</th>
                            <th>Author</th>
                            <th>ISBN</th>
                            <th>Category</th>
                            <th>Quantity</th>
                            <th>Available</th>
                        </tr>
                    </thead>

                    <tbody>
                    <?php foreach ($books as $book): ?>
                        <tr>
                            <td><?= (int) $book["id"] ?></td>
                            <td><?= htmlspecialchars($book["title"]) ?></td>
                            <td><?= htmlspecialchars($book["author"]) ?></td>
                            <td><?= htmlspecialchars($book["isbn"] ?? "") ?></td>
                            <td><?= htmlspecialchars($book["category"] ?? "") ?></td>
                            <td><?= (int) $book["quantity"] ?></td>
                            <td><?= (int) $book["available"] ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <p>No books match your search.</p>
        <?php endif; ?>
    </div>

</div>
</body>
</html>