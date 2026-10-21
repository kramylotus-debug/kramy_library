<?php
require_once "db.php";

$message = "";
$error = "";

// Record a payment
if ($_SERVER["REQUEST_METHOD"] === "POST"
    && isset($_POST["record_payment"])) {

    $fine_id = (int) ($_POST["fine_id"] ?? 0);
    $amount_paid = filter_var(
        $_POST["amount_paid"] ?? "",
        FILTER_VALIDATE_FLOAT
    );
    $payment_method = $_POST["payment_method"] ?? "Cash";
    $reference = trim($_POST["reference_number"] ?? "");

    $allowed_methods = ["Cash", "Mobile Money", "Bank", "Other"];

    try {
        if ($fine_id < 1 || $amount_paid === false || $amount_paid <= 0) {
            throw new Exception("Select a fine and enter a valid payment amount.");
        }

        if (!in_array($payment_method, $allowed_methods, true)) {
            throw new Exception("Select a valid payment method.");
        }

        $pdo->beginTransaction();

        $stmt = $pdo->prepare(
            "SELECT id, amount, status
             FROM fines WHERE id = ? FOR UPDATE"
        );
        $stmt->execute([$fine_id]);
        $fine = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$fine) {
            throw new Exception("Fine not found.");
        }

        $stmt = $pdo->prepare(
            "SELECT COALESCE(SUM(amount_paid), 0)
             FROM fine_payments WHERE fine_id = ?"
        );
        $stmt->execute([$fine_id]);
        $already_paid = (float) $stmt->fetchColumn();

        $balance = (float) $fine["amount"] - $already_paid;

        if ($balance <= 0) {
            throw new Exception("This fine has already been paid in full.");
        }

        if ($amount_paid > $balance) {
            throw new Exception(
                "Payment exceeds the remaining balance of TZS "
                . number_format($balance, 2)
            );
        }

        $stmt = $pdo->prepare(
            "INSERT INTO fine_payments
             (fine_id, amount_paid, payment_method, reference_number)
             VALUES (?, ?, ?, ?)"
        );
        $stmt->execute([
            $fine_id,
            $amount_paid,
            $payment_method,
            $reference !== "" ? $reference : null
        ]);

        $total_paid = $already_paid + $amount_paid;

        if ($total_paid >= (float) $fine["amount"]) {
            $new_status = "Paid";
        } else {
            $new_status = "Partially Paid";
        }

        $stmt = $pdo->prepare(
            "UPDATE fines SET status = ? WHERE id = ?"
        );
        $stmt->execute([$new_status, $fine_id]);

        $pdo->commit();
        $message = "Payment recorded successfully.";

    } catch (Throwable $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        $error = $e->getMessage();
    }
}

// Load fines and calculate balances
$fines = $pdo->query(
    "SELECT f.id, f.amount, f.reason, f.status,
            COALESCE(SUM(p.amount_paid), 0) AS total_paid
     FROM fines f
     LEFT JOIN fine_payments p ON p.fine_id = f.id
     GROUP BY f.id, f.amount, f.reason, f.status
     ORDER BY f.id DESC"
)->fetchAll(PDO::FETCH_ASSOC);

// Load payment history
$payments = $pdo->query(
    "SELECT p.id, p.amount_paid, p.payment_date,
            p.payment_method, p.reference_number,
            f.id AS fine_id, f.amount AS fine_amount,
            m.full_name, b.title
     FROM fine_payments p
     JOIN fines f ON p.fine_id = f.id
     JOIN borrowings br ON f.borrowing_id = br.id
     JOIN members m ON br.member_id = m.id
     JOIN books b ON br.book_id = b.id
     ORDER BY p.id DESC"
)->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Fine Payments | Kramy Library</title>

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
        <p>Fine Payments and Balances</p>
    </div>

    <p><a href="index.php">← Back to Dashboard</a></p>

    <?php if ($message !== ""): ?>
        <p class="success"><?= htmlspecialchars($message) ?></p>
    <?php endif; ?>

    <?php if ($error !== ""): ?>
        <p class="error"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>

    <div class="card">
        <h2>Record a Payment</h2>

        <form method="POST">
            <div class="form-grid">
                <div>
                    <label for="fine_id">Select Fine</label>
                    <select name="fine_id" id="fine_id" required>
                        <option value="">Choose a fine</option>
                        <?php foreach ($fines as $fine): ?>
                            <?php
                            $balance = max(
                                0,
                                (float) $fine["amount"] -
                                (float) $fine["total_paid"]
                            );
                            ?>
                            <?php if ($balance > 0): ?>
                                <option value="<?= (int) $fine["id"] ?>">
                                    Fine #<?= (int) $fine["id"] ?>
                                    — Balance: TZS
                                    <?= number_format($balance, 2) ?>
                                </option>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div>
                    <label for="amount_paid">Amount Paid (TZS)</label>
                    <input type="number" name="amount_paid"
                           id="amount_paid" min="0.01"
                           step="0.01" required>
                </div>

                <div>
                    <label for="payment_method">Payment Method</label>
                    <select name="payment_method" id="payment_method" required>
                        <option value="Cash">Cash</option>
                        <option value="Mobile Money">Mobile Money</option>
                        <option value="Bank">Bank</option>
                        <option value="Other">Other</option>
                    </select>
                </div>

                <div>
                    <label for="reference_number">Reference Number</label>
                    <input type="text" name="reference_number"
                           id="reference_number"
                           placeholder="Optional transaction reference">
                </div>
            </div>

            <p>
                <button type="submit" name="record_payment">
                    Record Payment
                </button>
            </p>
        </form>
    </div>

    <div class="card">
        <h2>Fine Balances</h2>

        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>Fine ID</th>
                        <th>Fine Amount</th>
                        <th>Total Paid</th>
                        <th>Balance</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($fines as $fine): ?>
                    <?php
                    $balance = max(
                        0,
                        (float) $fine["amount"] -
                        (float) $fine["total_paid"]
                    );
                    ?>
                    <tr>
                        <td><?= (int) $fine["id"] ?></td>
                        <td><?= number_format((float) $fine["amount"], 2) ?></td>
                        <td><?= number_format((float) $fine["total_paid"], 2) ?></td>
                        <td><?= number_format($balance, 2) ?></td>
                        <td><?= htmlspecialchars($fine["status"]) ?></td>
                    </tr>
                <?php endforeach; ?>

                <?php if (count($fines) === 0): ?>
                    <tr><td colspan="5">No fines recorded yet.</td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="card">
        <h2>Payment History</h2>

        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>Payment ID</th>
                        <th>Fine ID</th>
                        <th>Book</th>
                        <th>Member</th>
                        <th>Amount Paid</th>
                        <th>Method</th>
                        <th>Date</th>
                        <th>Reference</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($payments as $payment): ?>
                    <tr>
                        <td><?= (int) $payment["id"] ?></td>
                        <td><?= (int) $payment["fine_id"] ?></td>
                        <td><?= htmlspecialchars($payment["title"]) ?></td>
                        <td><?= htmlspecialchars($payment["full_name"]) ?></td>
                        <td><?= number_format((float) $payment["amount_paid"], 2) ?></td>
                        <td><?= htmlspecialchars($payment["payment_method"]) ?></td>
                        <td><?= htmlspecialchars($payment["payment_date"]) ?></td>
                        <td><?= htmlspecialchars($payment["reference_number"] ?? "") ?></td>
                    </tr>
                <?php endforeach; ?>

                <?php if (count($payments) === 0): ?>
                    <tr><td colspan="8">No payments recorded yet.</td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

</div>
</body>
</html>