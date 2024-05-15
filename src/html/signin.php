<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Sign In</title>
    <link rel="stylesheet" href="../css/style.css"> <!-- Ensure the path to your CSS is correct -->
    <meta name="viewport" content="width=device-width, initial-scale=1">
</head>

<body>
    <nav>
        <div class="logo">
            <a href="index.php"><img src="../images/logo.png" alt="Barca Library Logo" style="height:50px;"></a>
        </div>
        <div class="nav-links">
            <a href="index.php">Home</a>
            <a href="contact_us.php">Contact Us</a>
            <?php if (isset ($_SESSION['logged_in']) && $_SESSION['logged_in'] === true): ?>
                <a href="manage_accounts.php">Manage Account</a>
                <?php if ($_SESSION['user_code'] == 2): ?>
                    <a href="manage_books.php">Manage Books</a>
                    <a href="manage_users.php">Manage Users</a>
                <?php endif; ?>
                <form method="post" action="" style="display:inline;">
                    <button type="submit" name="logout">Log Out</button>
                </form>
            <?php else: ?>
                <a href="signin.php">Sign In</a>
                <a href="signup.php">Sign Up</a>
            <?php endif; ?>
        </div>
    </nav>
    <section class="section">
        <div class="container">
            <h1 class="title">Sign In</h1>
            <form action="login.php" method="post">
                <div class="field">
                    <label class="label" for="email">Email</label>
                    <div class="control">
                        <input class="input" type="email" name="email" id="email" required>
                    </div>
                </div>

                <div class="field">
                    <label class="label" for="password">Password</label>
                    <div class="control">
                        <input class="input" type="password" name="password" id="password" required>
                    </div>
                </div>

                <div class="field">
                    <div class="control">
                        <button type="submit" class="button is-link">Submit</button>
                    </div>
                </div>
            </form>
        </div>
    </section>
    <footer>
        <div class="footer-content">
            <p>Copy right Ehsan Salimi 2024</p>
            <div>
                <a href="index.php">Home</a>
                <a href="contact_us.php">Contact Us</a>
            </div>
        </div>
    </footer>
</body>

</html>