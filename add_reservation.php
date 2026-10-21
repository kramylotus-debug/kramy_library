<?php
require_once "db.php";

$message = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $book_id = (int) $_POST["book_id"];
    $member_id = (int) $_POST["member_id"];
    $reservation_date = $_POST["reservation_date"];
    $status = "Pending";

    try {
        $stmt = $pdo->prepare(
            "INSERT INTO reservations
            (book_id, member_id, reservation_date, status)
            VALUES (?, ?, ?, ?)"
        );

        $stmt->execute([
            $book_id,
            $member_id,
            $reservation_date,
            $status
        ]);

        $message = "Reservation added successfully!";
    } catch (PDOException $e) {
        $message = "Could not add reservation. Check that the selected book and member exist.";
    }
}

$books = $pdo->query("SELECT id, title FROM books ORDER BY title")->fetchAll(PDO::FETCH_ASSOC);
$members = $pdo->query("SELECT id, full_name FROM members ORDER BY full_name")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Reservation | Kramy Library</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f4f6f9;
            padding: 30px;
        }
        .card {
            background: white;
            max-width: 550px;
            margin: auto;
            padding: 25px;
            border-radius: 10px;
        }
        label {
            display: block;
            margin-top: 15px;
        }
        select, input, button {
            width: 100%;
            padding: 10px;
            margin-top: 6px;
            box-sizing: border-box;
        }
        button {
            background: #243247;
            color: white;
            border: none;
            cursor: pointer;
            margin-top: 20px;
        }
        a { color: #243247; }
    </style>
</head>
<body>
    <div class="card">
        <h1>Add Book Reservation</h1>

        <p><?= htmlspecialchars($message) ?></p>

        <?php if (count($books) > 0 && count($members) > 0): ?>
            <form method="POST">
                <label for="book_id">Select Book</label>
                <select name="book_id" id="book_id" required>
                    <?php foreach ($books as $book): ?>
                        <option value="<?= $book["id"] ?>">
                            <?= htmlspecialchars($book["title"]) ?>
                        </option>
                    <?php endforeach; ?>
                </select>

                <label for="member_id">Select Member</label>
                <select name="member_id" id="member_id" required>
                    <?php foreach ($members as $member): ?>
                        <option value="<?= $member["id"] ?>">
                            <?= htmlspecialchars($member["full_name"]) ?>
                        </option>
                    <?php endforeach; ?>
                </select>

                <label for="reservation_date">Reservation Date</label>
                <input
                    type="date"
                    name="reservation_date"
                    id="reservation_date"
                    value="<?= date('Y-m-d') ?>"
                    required
                >

                <button type="submit">Save Reservation</button>
            </form>
        <?php else: ?>
            <p>You need at least one book and one registered member before making a reservation.</p>
        <?php endif; ?>

        <p><a href="reservations.php">View Reservations</a></p>
    </div>
</body>
</html>