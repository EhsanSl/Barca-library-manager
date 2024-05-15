<?php
// Start the session
session_start();

// Check if the logout action has been requested
if (isset ($_POST['logout'])) {
  // Destroy the session to log out
  session_destroy();
  // Redirect to the homepage
  header("Location: index.php");
  exit;
}

require_once 'db_connection.php';

// Initialize a variable to store user permissions and title
$userPermissions = 'ALLOWED'; // Default value
$userTitle = ''; // Default value

// Check if the user is logged in
if (isset ($_SESSION['user_email'])) {
  $email = $_SESSION['user_email'];
  // Fetch the user's permissions from the database
  $permissionQuery = "SELECT permissions, title FROM user_profiles WHERE email = ?";
  $stmt = $conn->prepare($permissionQuery);
  $stmt->bind_param("s", $email);
  $stmt->execute();
  $result = $stmt->get_result();
  if ($row = $result->fetch_assoc()) {
    $userPermissions = $row['permissions'];
    $userTitle = $row['title'];
  }
  $stmt->close();
  // echo "<script>console.log('Debug User Title from PHP:', '" . $userTitle . "');</script>";
}
?>
<!DOCTYPE html>
<html>

<head>
  <meta charset="utf-8">
  <title>Barca Library</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="../css/style.css">
  <script>
    var userCode = <?php echo json_encode(isset ($_SESSION['user_code']) ? $_SESSION['user_code'] : ""); ?>;
    var borrowerEmail = <?php echo json_encode(isset ($_SESSION['user_email']) ? $_SESSION['user_email'] : ""); ?>;
    var userTitle = <?php echo json_encode($userTitle); ?>;
    // console.log('User Title from PHP:', userTitle);
  </script>
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

  <section class="section">
    <div class="container">
      <h1 class="title">Welcome To our Online Library Manager</h1>
      <div class="books-container">
        <?php
        $query = "SELECT isbn, title, author FROM books WHERE status= 'AVAILABLE'";
        $result = $conn->query($query);

        if ($result->num_rows > 0) {
          while ($row = $result->fetch_assoc()) {
            echo "<div class='book-box'>";
            echo "<img src='get_image.php?isbn=" . htmlspecialchars($row['isbn']) . "' alt='Book Image' class='book-image'>";
            echo "<h3>" . htmlspecialchars($row['title']) . "</h3>";
            echo "<p>Author: " . htmlspecialchars($row['author']) . "</p>";
            echo "<p>ISBN: " . htmlspecialchars($row['isbn']) . "</p>";

            // Check if user is logged in and allowed to borrow books
            if (isset ($_SESSION['logged_in']) && $_SESSION['logged_in'] === true && $userPermissions == 'ALLOWED') {
              echo "<button onclick=\"openModal('" . htmlspecialchars($row['title']) . "', '" . htmlspecialchars($row['isbn']) . "')\">Borrow This Book</button>";
            } else if (!isset ($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
              // Optionally, you can display a message or do nothing if the user is not logged in
              // echo "<p>Please log in to borrow books.</p>";
            } else {
              // Display a message if the user is not allowed to borrow books
              echo "<p>You are not allowed to borrow books at this time.</p>";
            }

            echo "</div>";
          }
        } else {
          echo "No books found.";
        }

        $conn->close();
        ?>
      </div>
  </section>

  <!-- The Modal -->
  <div id="borrowModal" class="modal">
    <!-- Modal content -->
    <div class="modal-content">
      <span class="close">&times;</span>
      <h2 id="modalTitle"></h2>
      <p>Expected Return Date: <span id="returnDate"></span></p>
      <button id="confirmBorrow">Confirm</button>
      <button id="cancelBorrow">Cancel</button>
    </div>
  </div>
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