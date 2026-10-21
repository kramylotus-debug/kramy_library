<?php
$pageTitle = "Kramy Library Dashboard";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?></title>

    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: #f4f6f9;
            color: #263238;
        }

        header {
            background: #26374a;
            color: white;
            padding: 24px 30px;
        }

        header h1 {
            margin: 0 0 8px;
        }

        header p {
            margin: 0;
            color: #dce4ed;
        }

        nav {
            background: white;
            padding: 16px 30px;
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            box-shadow: 0 2px 5px #00000010;
        }

        nav a {
            color: #26374a;
            text-decoration: none;
            font-weight: bold;
        }

        nav a:hover {
            color: #007bff;
        }

        main {
            padding: 30px;
            max-width: 1200px;
            margin: auto;
        }

        .welcome {
            background: white;
            padding: 25px;
            border-radius: 10px;
            margin-bottom: 25px;
            box-shadow: 0 2px 8px #00000010;
        }

        .welcome h2 {
            margin-top: 0;
            color: #26374a;
        }

        .dashboard-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 20px;
        }

        .card {
            background: white;
            padding: 24px;
            border-radius: 10px;
            box-shadow: 0 2px 8px #00000010;
            border-top: 4px solid #26374a;
        }

        .card h3 {
            margin-top: 0;
        }

        .card p {
            color: #596777;
            line-height: 1.5;
        }

        .card a {
            display: inline-block;
            margin-top: 8px;
            padding: 10px 15px;
            background: #26374a;
            color: white;
            text-decoration: none;
            border-radius: 5px;
        }

        .card a:hover {
            background: #405875;
        }

        footer {
            text-align: center;
            padding: 25px;
            color: #667085;
        }

        @media (max-width: 600px) {
            main {
                padding: 15px;
            }

            nav {
                padding: 15px;
                gap: 15px;
            }
        }
    </style>
</head>

<body>

<header>
    <h1>Kramy Library</h1>
    <p>Library Management System</p>
</header>

<nav>
    <a href="index.php">Dashboard</a>
    <a href="books.php">Books</a>
    <a href="members.php">Members</a>
    <a href="borrowings.php">Borrowings</a>
    <a href="reservations.php">Reservations</a>
    <a href="fines.php">Fines</a>
<a href="fine_payments.php">Fine Payments</a>
</nav>

<main>

    <section class="welcome">
        <h2>Welcome to Kramy Library!</h2>
        <p>
            Manage your library books, register members,
            record book borrowings, and view reservations
            from one place.
        </p>
    </section>

    <section class="dashboard-grid">

        <div class="card">
            <h3>Books Management</h3>
            <p>View the collection and search for books by title, author, or category.</p>
            <a href="books.php">View Books</a>
        </div>

        <div class="card">
            <h3>Members Management</h3>
            <p>Register new members and view their borrowing records.</p>
            <a href="members.php">Manage Members</a>
        </div>

        <div class="card">
            <h3>Borrowings</h3>
            <p>Record borrowed books, monitor due dates, and process returns.</p>
            <a href="borrowings.php">Manage Borrowings</a>
        </div>

        <div class="card">
            <h3>Reservations</h3>
            <p>View and manage members' requests to reserve books.</p>
            <a href="reservations.php">View Reservations</a>
        </div>

    </section>

</main>

<footer>
    <p>&copy; <?php echo date("Y"); ?> Kramy Library Management System</p>
</footer>

</body>
</html>