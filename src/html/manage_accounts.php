<?php
session_start();
require 'db_connection.php'; // Ensure you have this file for your database connection

// Redirect if not logged in
if (!isset ($_SESSION['logged_in']) || !$_SESSION['logged_in']) {
    header("Location: signin.php");
    exit;
}

$email = $_SESSION['user_email'];

// Handle fine payment
if (isset ($_POST['pay_fine'])) {
    // Reset the fine_amount in the fines table for this user's overdue book loans
    $resetFineQuery = "UPDATE fines JOIN book_loans ON fines.loan_id = book_loans.loan_id SET fines.fine_amount = 0 fines.paid = TRUE WHERE book_loans.borrower_email = ? AND fines.paid = FALSE";
    $stmt = $conn->prepare($resetFineQuery);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->close();
    // Optionally, mark the fines as paid
    header("Refresh:0"); // Refresh the page to update the fine amount
}

// Fetch total fine amount
$totalFineQuery = "SELECT SUM(fines.fine_amount) AS total_fine FROM fines JOIN book_loans ON fines.loan_id = book_loans.loan_id WHERE book_loans.borrower_email = ? AND fines.paid = FALSE";
$stmt = $conn->prepare($totalFineQuery);
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();
$totalFine = $result->fetch_assoc()['total_fine'];
$stmt->close();

// Handle book return
if (isset ($_POST['return_books']) && !empty ($_POST['selected_books'])) {
    $returnDate = date('Y-m-d'); // Current date as return date
    foreach ($_POST['selected_books'] as $loanId) {
        // Begin transaction
        $conn->begin_transaction();

        // Update the return_date in the book_loans table
        $stmt = $conn->prepare("UPDATE book_loans SET return_date = ? WHERE loan_id = ? AND borrower_email = ?");
        $stmt->bind_param("sis", $returnDate, $loanId, $_SESSION['user_email']);
        if (!$stmt->execute()) {
            // Handle error - Rollback and break
            $conn->rollback();
            echo "Error updating return date for loan ID: $loanId";
            break;
        }

        // Retrieve the ISBN of the returned book
        $isbnQuery = $conn->prepare("SELECT isbn FROM book_loans WHERE loan_id = ?");
        $isbnQuery->bind_param("i", $loanId);
        $isbnQuery->execute();
        $isbnResult = $isbnQuery->get_result();
        if ($isbnRow = $isbnResult->fetch_assoc()) {
            $isbn = $isbnRow['isbn'];

            // Update the status in the books table
            $updateBookStatus = $conn->prepare("UPDATE books SET status = 'AVAILABLE' WHERE isbn = ?");
            $updateBookStatus->bind_param("s", $isbn);
            if (!$updateBookStatus->execute()) {
                // Handle error - Rollback
                $conn->rollback();
                echo "Error updating status for ISBN: $isbn";
                break;
            }
        } else {
            // ISBN not found - Rollback
            $conn->rollback();
            echo "ISBN not found for loan ID: $loanId";
            break;
        }

        // Commit transaction
        $conn->commit();

        $stmt->close();
        $isbnQuery->close();
        $updateBookStatus->close();
    }
    // Optionally, add logic here to recalculate fines or update user permissions based on returned books
    header("Refresh:0"); // Refresh the page to update the book list
}

// Fetch borrowed books
$borrowedBooksQuery = "SELECT bl.loan_id, b.title, b.author, b.isbn, bl.loan_date, bl.due_date FROM book_loans bl JOIN books b ON bl.isbn = b.isbn WHERE bl.borrower_email = ? AND bl.return_date IS NULL";
$stmt = $conn->prepare($borrowedBooksQuery);
$stmt->bind_param("s", $_SESSION['user_email']);
$stmt->execute();
$result = $stmt->get_result();
?>
<!DOCTYPE html>
<html>

<head>
    <title>Manage Account - Barca Library</title>
    <link rel="stylesheet" href="../css/table.css">
    <link rel="stylesheet" type="text/css" href="../css/style.css">
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
    <h1>Your Borrowed Books</h1>
    <form method="POST" action="manage_accounts.php">
        <table>
            <tr>
                <th>Title</th>
                <th>Author</th>
                <th>ISBN</th>
                <th>Loan Date</th>
                <th>Due Date</th>
                <th>Select</th>
            </tr>
            <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td>
                        <?= htmlspecialchars($row['title']) ?>
                    </td>
                    <td>
                        <?= htmlspecialchars($row['author']) ?>
                    </td>
                    <td>
                        <?= htmlspecialchars($row['isbn']) ?>
                    </td>
                    <td>
                        <?= htmlspecialchars($row['loan_date']) ?>
                    </td>
                    <td>
                        <?= htmlspecialchars($row['due_date']) ?>
                    </td>
                    <td><input type="checkbox" name="selected_books[]" value="<?= $row['loan_id'] ?>"></td>
                </tr>
            <?php endwhile; ?>
        </table>
        <button type="submit" name="return_books">Return Selected Books</button>
    </form>
    <br>
    <h2>Total Fine: $
        <?= number_format((float) $totalFine, 2, '.', '') ?>
    </h2>
    <form method="POST" action="manage_accounts.php">
        <button type="submit" name="pay_fine">Pay Now</button>
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
<?php
$conn->close();
?>