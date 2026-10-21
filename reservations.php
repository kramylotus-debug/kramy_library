<?php
require_once "db.php";

$message = "";
$error = "";

// Create a reservation
if ($_SERVER["REQUEST_METHOD"] === "POST"
    && isset($_POST["add_reservation"])) {

    $book_id = (int) ($_POST["book_id"] ?? 0);
    $member_id = (int) ($_POST["member_id"] ?? 0);

    try {
        $stmt = $pdo->prepare(
            "SELECT id FROM books WHERE id = ?"
        );
        $stmt->execute([$book_id]);

        if (!$stmt->fetch()) {
            throw new Exception("Please select a valid book.");
        }

        $stmt = $pdo->prepare(
            "SELECT id FROM members WHERE id = ?"
        );
        $stmt->execute([$member_id]);

        if (!$stmt->fetch()) {
            throw new Exception("Please select a valid member.");
        }

        $stmt = $pdo->prepare(
            "INSERT INTO reservations
             (book_id, member_id, reservation_date, status)
             VALUES (?, ?, CURDATE(), 'Pending')"
        );
        $stmt->execute([$book_id, $member_id]);

        $message = "Reservation added successfully.";

    } catch (Throwable $e) {
        $error = $e->getMessage();
    }
}

// Update reservation status
if ($_SERVER["REQUEST_METHOD"] === "POST"
    && isset($_POST["update_status"])) {

    $reservation_id = (int) ($_POST["reservation_id"] ?? 0);
    $new_status = $_POST["status"] ?? "";

    $allowed_statuses = [
        "Pending", "Approved", "Cancelled", "Fulfilled"
    ];

    if (!in_array($new_status, $allowed_statuses, true)) {
        $error = "Invalid reservation status.";
    } else {
        $stmt = $pdo->prepare(
            "UPDATE reservations SET status = ?
             WHERE id = ?"
        );
        $stmt->execute([$new_status, $reservation_id]);

        $message = "Reservation status updated.";
    }
}

// Load books and members
$books = $pdo->query(
    "SELECT id, title FROM books ORDER BY title"
)->fetchAll(PDO::FETCH_ASSOC);

$members = $pdo->query(
    "SELECT id, full_name FROM members ORDER BY full_name"
)->fetchAll(PDO::FETCH_ASSOC);

// Load reservations
$reservations = $pdo->query(
    "SELECT r.id, b.title, m.full_name,
            r.reservation_date, r.status
     FROM reservations r
     JOIN books b ON r.book_id = b.id
     JOIN members m ON r.member_id = m.id
     ORDER BY r.id DESC"
)->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Reservations | Kramy Library</title>

    <style>
        * { box-sizing: border-box; }

        body {
            margin: 0;
            padding: 25px;
            font-family: Arial, sans-serif;
            background: #f4f6f9;
            color: #243247;
        }

        .container {
            max-width: 1150px;
            margin: auto;
        }

        .header, .card {
            padding: 24px;
            border-radius: 10px;
            margin-bottom: 20px;
        }

        .header {
            background: #243247;
            color: white;
        }

        .card {
            background: white;
            box-shadow: 0 2px 8px #ddd;
        }

        .form-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
        }

        label {
            display: block;
            margin-bottom: 7px;
            font-weight: bold;
        }

        select, button {
            width: 100%;
            padding: 11px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }

        button {
            background: #243247;
            color: white;
            cursor: pointer;
        }

        .table-wrapper { overflow-x: auto; }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }

        th, td {
            text-align: left;
            padding: 11px;
            border-bottom: 1px solid #ddd;
        }

        th {
            background: #243247;
            color: white;
        }

        .success { color: #167a35; }
        .error { color: #b42318; }

        @media (max-width: 600px) {
            body { padding: 12px; }
        }
    </style>
</head>

<body>
<div class="container">

    <div class="header">
        <h1>Kramy Library</h1>
        <p>Book Reservations Management</p>
    </div>

    <p><a href="index.php">← Back to Dashboard</a></p>

    <?php if ($message !== ""): ?>
        <p class="success"><?= htmlspecialchars($message) ?></p>
    <?php endif; ?>

    <?php if ($error !== ""): ?>
        <p class="error"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>

    <div class="card">
        <h2>Add a Reservation</h2>

        <form method="POST">
            <div class="form-grid">
                <div>
                    <label for="book_id">Select Book</label>
                    <select id="book_id" name="book_id" required>
                        <option value="">Choose a book</option>
                        <?php foreach ($books as $book): ?>
                            <option value="<?= (int) $book["id"] ?>">
                                <?= htmlspecialchars($book["title"]) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div>
                    <label for="member_id">Select Member</label>
                    <select id="member_id" name="member_id" required>
                        <option value="">Choose a member</option>
                        <?php foreach ($members as $member): ?>
                            <option value="<?= (int) $member["id"] ?>">
                                <?= htmlspecialchars($member["full_name"]) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <p>
                <button type="submit" name="add_reservation">
                    Add Reservation
                </button>
            </p>
        </form>
    </div>

    <div class="card">
        <h2>Reservation List</h2>
        <p>Total reservations: <?= count($reservations) ?></p>

        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Book</th>
                        <th>Member</th>
                        <th>Date</th>
                        <th>Status</th>
                        <th>Update Status</th>
                    </tr>
                </thead>

                <tbody>
                <?php foreach ($reservations as $reservation): ?>
                    <tr>
                        <td><?= (int) $reservation["id"] ?></td>
                        <td><?= htmlspecialchars($reservation["title"]) ?></td>
                        <td><?= htmlspecialchars($reservation["full_name"]) ?></td>
                        <td><?= htmlspecialchars($reservation["reservation_date"]) ?></td>
                        <td><?= htmlspecialchars($reservation["status"]) ?></td>
                        <td>
                            <form method="POST">
                                <input type="hidden"
                                       name="reservation_id"
                                       value="<?= (int) $reservation["id"] ?>">

                                <select name="status" required>
                                    <?php foreach (
                                        ["Pending", "Approved", "Cancelled", "Fulfilled"]
                                        as $status
                                    ): ?>
                                        <option value="<?= $status ?>"
                                            <?= $reservation["status"] === $status
                                                ? "selected" : "" ?>>
                                            <?= $status ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>

                                <button type="submit" name="update_status">
                                    Update
                                </button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>

                <?php if (count($reservations) === 0): ?>
                    <tr>
                        <td colspan="6">No reservations recorded yet.</td>
                    </tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

</div>
</body>
</html>