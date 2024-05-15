<?php
// start session
session_start();
// Check if the form was submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = strip_tags(trim($_POST["name"]));
    $email = filter_var(trim($_POST["email"]), FILTER_SANITIZE_EMAIL);
    $message = trim($_POST["message"]);

    // Specify your admin email address
    $to = 'esalimi1997@gmail.com';
    $subject = "New Contact from $name";
    $email_content = "Name: $name\n";
    $email_content .= "Email: $email\n\n";
    $email_content .= "Message:\n$message\n";

    // Headers to send HTML email
    $headers = "From: $name <$email>";
    $headers .= "Reply-To: $email";

    // Ensure the email address is valid
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email format";
    } else {
        // Send the email
        if (mail($to, $subject, $email_content, $headers)) {
            $success = "Thank You! Your message has been sent.";
        } else {
            $error = "Oops! Something went wrong, and we couldn't send your message.";
        }
    }
}
?>

<!DOCTYPE html>
<html>

<head>
    <title>Contact Us - Barca Library</title>
    <link rel="stylesheet" type="text/css" href="../css/style.css"> <!-- Adjust the path as needed -->
    <script src="../js/script.js" defer></script>
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
    <h2>Contact Us</h2>

    <?php if (isset ($success)): ?>
        <p>
            <?php echo $success; ?>
        </p>
    <?php elseif (isset ($error)): ?>
        <p>
            <?php echo $error; ?>
        </p>
    <?php endif; ?>

    <form action="contact_us.php" method="post">
        Name: <input type="text" name="name" required><br>
        Email: <input type="email" name="email" required><br>
        Message:<br>
        <textarea name="message" rows="5" cols="30" required></textarea><br>
        <input type="submit" value="Send">
    </form>
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