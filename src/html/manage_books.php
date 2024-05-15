<?php
session_start();

require_once 'db_connection.php';

// Redirect if not logged in or not an admin
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true || $_SESSION['user_code'] != 2) {
    header("Location: index.php");
    exit;
}

$conn = new mysqli($hn, $un, $pw, $db);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

function get_post($conn, $var) {
    return $conn->real_escape_string($_POST[$var] ?? '');
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_FILES['book_image']) && $_FILES['book_image']['error'] == UPLOAD_ERR_OK) {
        $author = get_post($conn, 'author');
        $title = get_post($conn, 'title');
        $category = get_post($conn, 'category');
        $year = get_post($conn, 'year');
        $isbn = get_post($conn, 'isbn');
        $imgData = file_get_contents($_FILES['book_image']['tmp_name']);

        $stmt = $conn->prepare("INSERT INTO books (author, title, category, year, isbn, image) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssis", $author, $title, $category, $year, $isbn, $imgData);

        if ($stmt->execute()) {
            echo "<p>Book added successfully.</p>";
        } else {
            echo "<p>Error adding book: " . $stmt->error . "</p>";
        }
        $stmt->close();
    }

    if (isset($_POST['delete_records']) && !empty($_POST['delete_check'])) {
        foreach ($_POST['delete_check'] as $isbn) {
            // Begin transaction
            $conn->begin_transaction();
            
            try {
                // Delete related records first (e.g., from loans table)
                $stmt = $conn->prepare("DELETE FROM book_loans WHERE isbn = ?");
                $stmt->bind_param("s", $isbn);
                $stmt->execute();
                $stmt->close();
    
                // Now delete the book
                $stmt = $conn->prepare("DELETE FROM books WHERE isbn = ?");
                $stmt->bind_param("s", $isbn);
                $stmt->execute();
                
                if ($stmt->affected_rows > 0) {
                    echo "Book with ISBN: $isbn deleted successfully along with related borrow records.<br>";
                } else {
                    echo "No book found with ISBN: $isbn, or it's already been deleted.<br>";
                }
                
                // Commit transaction
                $conn->commit();
                
            } catch (Exception $e) {
                // An error occurred; rollback transaction
                $conn->rollback();
                echo "Error deleting book with ISBN: $isbn - " . $e->getMessage();
            }
            
            $stmt->close();
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Manage Books - Barca Library</title>
    <link rel="stylesheet" type="text/css" href="../css/style.css">
    <link rel="stylesheet" type="text/css" href="../css/table.css">
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


    <h1>Manage Books Here</h1>

    <form action="manage_books.php" method="post" enctype="multipart/form-data">
        <label>Author: <input type="text" name="author"></label>
        <label>Title: <input type="text" name="title"></label>
        <label>Category: <input type="text" name="category"></label>
        <label>Year: <input type="text" name="year"></label>
        <label>ISBN: <input type="text" name="isbn"></label>
        <label>Book Image: <input type="file" name="book_image"></label>
        <input type="submit" value="ADD RECORD">
    </form>

    <form action="manage_books.php" method="post">
        <input type="submit" name="delete_records" value="Delete Selected">
        <table>
            <!-- Table Headers -->
            <tr>
                <th>Author</th>
                <th>Title</th>
                <th>Category</th>
                <th>Year</th>
                <th>ISBN</th>
                <th>Status</th>
                <th>Image</th>
                <th>Select</th>
            </tr>
            <!-- Book Rows -->
            <?php
            $query = "SELECT * FROM books";
            $result = $conn->query($query);
            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    echo "<tr>";
                    echo "<td>" . htmlspecialchars($row['author']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['title']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['category']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['year']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['isbn']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['status']) . "</td>";
                    echo "<td><img src='get_image.php?isbn=" . htmlspecialchars($row['isbn']) . "' alt='Book Image' style='height: 100px;'></td>";
                    echo "<td><input type='checkbox' name='delete_check[]' value='" . htmlspecialchars($row['isbn']) . "'></td>";
                    echo "</tr>";
                }
            } else {
                echo "<tr><td colspan='8'>No books found</td></tr>";
            }
            ?>
        </table>
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
