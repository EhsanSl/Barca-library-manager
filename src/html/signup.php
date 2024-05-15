<?php
session_start();
require 'db_connection.php'; // Ensure you have the correct path to your database connection file

// Check if the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $fname = $_POST['fname'];
    $lname = $_POST['lname'];
    $user_code = $_POST['user_code'];
    $email = $_POST['email'];
    $password = $_POST['password']; // Consider hashing the password
    $title = $_POST['title'];

    // Prepare an INSERT statement
    $stmt = $conn->prepare("INSERT INTO user_profiles (fname, lname, user_code, email, password, title) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssisss", $fname, $lname, $user_code, $email, $password, $title);

    if ($stmt->execute()) {
        echo "Registration successful!";
        // Optionally, redirect to the login page or automatically log the user in
    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();
}
?>

<!DOCTYPE html>
<html>

<head>
    <title>Sign Up - Barca Library</title>
    <link rel="stylesheet" href="../css/style.css">
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
    <form method="POST" action="signup.php">
        <label for="fname">First Name:</label>
        <input type="text" name="fname" required><br>

        <label for="lname">Last Name:</label>
        <input type="text" name="lname" required><br>



        <label for="email">Email:</label>
        <input type="email" name="email" required><br>

        <label for="password">Password:</label>
        <input type="password" name="password" required><br>

        <label for="user_code">User type:</label>
        <select name="user_code">
            <option value="1">user</option>
            <option value="2">admin</option>
        </select><br>

        <label for="title">Title:</label>
        <select name="title">
            <option value="STUDENT">Student</option>
            <option value="TEACHER">Teacher</option>
        </select><br>

        <input type="submit" value="Sign Up">
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