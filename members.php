<?php
require_once "db.php";

$message = "";
$error = "";

// Register a new member
if ($_SERVER["REQUEST_METHOD"] === "POST"
    && isset($_POST["add_member"])) {

    $name = trim($_POST["full_name"] ?? "");
    $email = trim($_POST["email"] ?? "");
    $phone = trim($_POST["phone"] ?? "");

    if ($name === "") {
        $error = "Please enter the member's full name.";
    } elseif ($email !== "" && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Please enter a valid email address.";
    } else {
        try {
            $stmt = $pdo->prepare(
                "INSERT INTO members (full_name, email, phone)
                 VALUES (?, ?, ?)"
            );

            $stmt->execute([
                $name,
                $email !== "" ? $email : null,
                $phone !== "" ? $phone : null
            ]);

            $message = "Member registered successfully.";
        } catch (PDOException $e) {
            $error = "Could not register the member. Please check the details.";
        }
    }
}

// Get all members and their borrowing totals
$stmt = $pdo->query(
    "SELECT m.id, m.full_name, m.email, m.phone,
            COUNT(br.id) AS total_borrowings
     FROM members m
     LEFT JOIN borrowings br ON br.member_id = m.id
     GROUP BY m.id, m.full_name, m.email, m.phone
     ORDER BY m.id DESC"
);

$members = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Members | Kramy Library</title>

    <style>
        * { box-sizing: border-box; }

        body {
            margin: 0;
            padding: 25px;
            background: #f4f6f9;
            color: #243247;
            font-family: Arial, sans-serif;
        }

        .container {
            max-width: 1100px;
            margin: auto;
        }

        .header {
            background: #243247;
            color: white;
            padding: 24px;
            border-radius: 10px;
            margin-bottom: 20px;
        }

        .card {
            background: white;
            padding: 24px;
            border-radius: 10px;
            box-shadow: 0 2px 8px #ddd;
            margin-bottom: 20px;
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

        input, button {
            width: 100%;
            padding: 12px;
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
            padding: 12px;
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
        <p>Members Management</p>
    </div>

    <p><a href="index.php">← Back to Dashboard</a></p>

    <?php if ($message !== ""): ?>
        <p class="success"><?= htmlspecialchars($message) ?></p>
    <?php endif; ?>

    <?php if ($error !== ""): ?>
        <p class="error"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>

    <div class="card">
        <h2>Register a New Member</h2>

        <form method="POST">
            <div class="form-grid">
                <div>
                    <label for="full_name">Full Name *</label>
                    <input type="text" id="full_name"
                           name="full_name" required>
                </div>

                <div>
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email">
                </div>

                <div>
                    <label for="phone">Phone Number</label>
                    <input type="text" id="phone" name="phone">
                </div>
            </div>

            <p>
                <button type="submit" name="add_member">
                    Register Member
                </button>
            </p>
        </form>
    </div>

    <div class="card">
        <h2>Registered Members</h2>
        <p>Total members: <?= count($members) ?></p>

        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Full Name</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Total Borrowings</th>
                    </tr>
                </thead>

                <tbody>
                <?php foreach ($members as $member): ?>
                    <tr>
                        <td><?= (int) $member["id"] ?></td>
                        <td><?= htmlspecialchars($member["full_name"]) ?></td>
                        <td><?= htmlspecialchars($member["email"] ?? "") ?></td>
                        <td><?= htmlspecialchars($member["phone"] ?? "") ?></td>
                        <td><?= (int) $member["total_borrowings"] ?></td>
                    </tr>
                <?php endforeach; ?>

                <?php if (count($members) === 0): ?>
                    <tr>
                        <td colspan="5">No members registered yet.</td>
                    </tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

</div>
</body>
</html>