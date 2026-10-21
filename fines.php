<?php
require_once "db.php";

$message = "";
$error = "";

// Record a fine for a borrowing
if ($_SERVER["REQUEST_METHOD"] === "POST"
    && isset($_POST["add_fine"])) {

    $borrowing_id = (int) ($_POST["borrowing_id"] ?? 0);
    $amount = filter_var($_POST["amount"] ?? "", FILTER_VALIDATE_FLOAT);
    $reason = trim($_POST["reason"] ?? "");

    if ($borrowing_id < 1 || $amount === false || $amount <= 0) {
        $error = "Select a borrowing and enter a valid fine amount.";
    } else {
        try {
            $stmt = $pdo->prepare(
                "SELECT id FROM borrowings WHERE id = ?"
            );
            $stmt->execute([$borrowing_id]);

            if (!$stmt->fetch()) {
                throw new Exception("Borrowing record not found.");
            }

            $stmt = $pdo->prepare(
                "INSERT INTO fines (borrowing_id, amount, reason)
                 VALUES (?, ?, ?)"
            );
            $stmt->execute([
                $borrowing_id,
                $amount,
                $reason !== "" ? $reason : "Overdue book"
            ]);

            $message = "Fine recorded successfully.";
        } catch (Throwable $e) {
            $error = $e->getMessage();
        }
    }
}

// Update fine payment status
if ($_SERVER["REQUEST_METHOD"] === "POST"
    && isset($_POST["update_fine"])) {

    $fine_id = (int) ($_POST["fine_id"] ?? 0);
    $status = $_POST["status"] ?? "";

    if (!in_array($status, ["Unpaid", "Partially Paid", "Paid"], true)) {
        $error = "Invalid payment status.";
    } else {
        $stmt = $pdo->prepare(
            "UPDATE fines SET status = ? WHERE id = ?"
        );
        $stmt->execute([$status, $fine_id]);

        $message = "Fine status updated.";
    }
}

// Load borrowing records
$borrowings = $pdo->query(
    "SELECT br.id, b.title, m.full_name, br.due_date, br.status
     FROM borrowings br
     JOIN books b ON br.book_id = b.id
     JOIN members m ON br.member_id = m.id
     ORDER BY br.id DESC"
)->fetchAll(PDO::FETCH_ASSOC);

// Load fines
$fines = $pdo->query(
    "SELECT f.id, f.borrowing_id, f.amount, f.reason,
            f.status, f.created_at, b.title, m.full_name
     FROM fines f
     JOIN borrowings br ON f.borrowing_id = br.id
     JOIN books b ON br.book_id = b.id
     JOIN members m ON br.member_id = m.id
     ORDER BY f.id DESC"
)->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Fines | Kramy Library</title>

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

        label {
            display: block;
            margin-bottom: 7px;
            font-weight: bold;
        }

        input, select, button {
            width: 100%;
            padding: 11px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }

        button {
            background: #243247;
            color: white;
            cursor: pointer;
            border: none;
        }

        .form-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
        }

        .table-wrapper { overflow-x: auto; }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }

        th, td {
            padding: 11px;
            border-bottom: 1px solid #ddd;
            text-align: left;
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
        <p>Fines Management</p>
    </div>

    <p><a href="index.php">← Back to Dashboard</a></p>

    <?php if ($message !== ""): ?>
        <p class="success"><?= htmlspecialchars($message) ?></p>
    <?php endif; ?>

    <?php if ($error !== ""): ?>
        <p class="error"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>

    <div class="card">
        <h2>Record a Fine</h2>
        <p>Select a borrowing record and enter the fine amount.</p>

        <form method="POST">
            <div class="form-grid">
                <div>
                    <label for="borrowing_id">Borrowing Record</label>
                    <select name="borrowing_id" id="borrowing_id" required>
                        <option value="">Select borrowing</option>
                        <?php foreach ($borrowings as $item): ?>
                            <option value="<?= (int) $item["id"] ?>">
                                #<?= (int) $item["id"] ?> —
                                <?= htmlspecialchars($item["title"]) ?> /
                                <?= htmlspecialchars($item["full_name"]) ?> —
                                Due <?= htmlspecialchars($item["due_date"]) ?>
                                (<?= htmlspecialchars($item["status"]) ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div>
                    <label for="amount">Fine Amount (TZS)</label>
                    <input type="number" name="amount" id="amount"
                           min="0.01" step="0.01" required>
                </div>

                <div>
                    <label for="reason">Reason</label>
                    <input type="text" name="reason" id="reason"
                           placeholder="e.g. Overdue book">
                </div>
            </div>

            <p>
                <button type="submit" name="add_fine">Record Fine</button>
            </p>
        </form>
    </div>

    <div class="card">
        <h2>Fine Records</h2>
        <p>Total fine records: <?= count($fines) ?></p>

        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Book</th>
                        <th>Member</th>
                        <th>Amount (TZS)</th>
                        <th>Reason</th>
                        <th>Status</th>
                        <th>Update</th>
                    </tr>
                </thead>

                <tbody>
                <?php foreach ($fines as $fine): ?>
                    <tr>
                        <td><?= (int) $fine["id"] ?></td>
                        <td><?= htmlspecialchars($fine["title"]) ?></td>
                        <td><?= htmlspecialchars($fine["full_name"]) ?></td>
                        <td><?= number_format((float) $fine["amount"], 2) ?></td>
                        <td><?= htmlspecialchars($fine["reason"] ?? "") ?></td>
                        <td><?= htmlspecialchars($fine["status"]) ?></td>
                        <td>
                            <form method="POST">
                                <input type="hidden" name="fine_id"
                                       value="<?= (int) $fine["id"] ?>">

                                <select name="status">
                                    <?php foreach (
                                        ["Unpaid", "Partially Paid", "Paid"]
                                        as $status
                                    ): ?>
                                        <option value="<?= $status ?>"
                                            <?= $fine["status"] === $status
                                                ? "selected" : "" ?>>
                                            <?= $status ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>

                                <button type="submit" name="update_fine">
                                    Update
                                </button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>

                <?php if (count($fines) === 0): ?>
                    <tr>
                        <td colspan="7">No fines recorded yet.</td>
                    </tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

</div>
</body>
</html>