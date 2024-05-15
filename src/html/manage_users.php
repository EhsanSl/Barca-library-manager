<?php
  // Start the session
  session_start();

  // Check if the user is logged in and is an admin
  if (!isset ($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true || $_SESSION['user_code'] != 2) {
    // Redirect to the homepage or login page
    header("Location: index.php");
    exit;
  }

  require_once 'db_connection.php'; // Make sure this points to your actual database connection script
  $conn = new mysqli($hn, $un, $pw, $db);
  if ($conn->connect_error)
    die ($conn->connect_error);

  // Logic for deleting a user
  if (isset ($_POST['delete_records'])) {
    if (isset ($_POST['delete_check'])) {
      $checkboxes = $_POST['delete_check'];
      foreach ($checkboxes as $isbn) {
        $query = "DELETE FROM books WHERE isbn='$isbn'";
        $result = $conn->query($query);
        if (!$result)
          echo "DELETE failed: $query<br>" . $conn->error . "<br><br>";
      }
    }
  }
?>

<!DOCTYPE html>
<html>

<head>
  <title>Manage Profiles - Barca Library</title>
  <link rel="stylesheet" type="text/css" href="../css/style.css">
  <link rel="stylesheet" type="text/css" href="../css/table.css">
  <script src="../js/script.js" defer></script>
  <script>
    // JavaScript function to toggle checkbox selection
    function check_all(source) {
      checkboxes = document.querySelectorAll('input[type="checkbox"]');
      for (var i = 0, n = checkboxes.length; i < n; i++) {
        checkboxes[i].checked = source.checked;
      }
    }
  </script>
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

  <?php


  // Table for displaying and managing books
  echo <<<_END
  <form action="manage_users.php" method="post">
    <input type="submit" name="delete_records" value="Delete Selected">
    <table>
      <tr>
        <th>First Name</th>
        <th>Last Name</th>
        <th>User Type</th>
        <th>Email</th>
        <th>Title</th>
        <th>Created Date</th>
        <th>Select</th>
      </tr>
_END;

  $query = "SELECT user_profiles.fname, user_profiles.lname, user_profiles.email, user_profiles.title, user_profiles.created_date, user_codes.user_description FROM user_profiles LEFT JOIN user_codes ON user_profiles.user_code = user_codes.user_code";

  $result = $conn->query($query);
  if (!$result)
    die ("Database access failed: " . $conn->error);

  $rows = $result->fetch_all(MYSQLI_ASSOC);

  foreach ($rows as $row) {
    echo "<tr>";
    echo "<td>" . htmlspecialchars($row['fname']) . "</td>";
    echo "<td>" . htmlspecialchars($row['lname']) . "</td>";
    echo "<td>" . htmlspecialchars($row['user_description']) . "</td>"; // Use 'user_description' instead of 'user_code'
    echo "<td>" . htmlspecialchars($row['email']) . "</td>";
    echo "<td>" . htmlspecialchars($row['title']) . "</td>";
    echo "<td>" . htmlspecialchars($row['created_date']) . "</td>";
    echo "<td><input type='checkbox' name='delete_check[]' value='" . htmlspecialchars($row['email']) . "'></td>";
    echo "</tr>";
  }

  echo "</table></form>";

  $result->close();
  $conn->close();

  function get_post($conn, $var)
  {
    return $conn->real_escape_string($_POST[$var]);
  }
  ?>
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