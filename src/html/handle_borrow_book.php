<?php
require_once 'db_connection.php'; // Ensure this path is correct
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Assuming borrower_email is passed along with isbn
if (isset($_POST['isbn']) && isset($_POST['borrower_email'])) {
    $isbn = $_POST['isbn'];
    $borrower_email = $_POST['borrower_email'];
    $loan_date = date('Y-m-d'); // Assuming loan starts today
    $due_date = date('Y-m-d', strtotime('+1 month')); // Assuming due in one month

    // Begin transaction
    $conn->begin_transaction();

    try {
        // Step 1: Update the book's status to BORROWED
        $stmt = $conn->prepare("UPDATE books SET status = 'BORROWED' WHERE isbn = ?");
        $stmt->bind_param("s", $isbn);
        $stmt->execute();
        if ($stmt->affected_rows === 0) {
            throw new Exception("No rows updated. Make sure the ISBN exists and the book is not already BORROWED.");
        }

        // Step 2: Insert a new record into book_loans
        $insertStmt = $conn->prepare("INSERT INTO book_loans (isbn, borrower_email, loan_date, due_date) VALUES (?, ?, ?, ?)");
        $insertStmt->bind_param("ssss", $isbn, $borrower_email, $loan_date, $due_date);
        $insertStmt->execute();
        if ($insertStmt->affected_rows === 0) {
            throw new Exception("Failed to insert book loan record.");
        }

        // If everything is fine, commit the transaction
        $conn->commit();
        echo "success";
    } catch (Exception $e) {
        // An error occurred, roll back the transaction
        $conn->rollback();
        echo "Error borrowing book: " . $e->getMessage();
    }

    $stmt->close();
    $insertStmt->close();
    $conn->close();
} else {
    echo "Missing ISBN or borrower email.";
}
?>
