<?php
session_start();

// Include your database connection
require 'db_connection.php'; // Ensure you have this file for your database connection

// Check if the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['email']) && isset($_POST['password'])) {
    $email = $_POST['email'];
    $password = $_POST['password']; // This is now assumed to be plain text

    // Prepare a select statement to check the email
    $stmt = $conn->prepare("SELECT fname, lname, password, user_code FROM user_profiles WHERE LOWER(email) = LOWER(?)");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $stmt->bind_result($fname, $lname, $stored_password, $user_code);
        $stmt->fetch();

        // Directly compare the submitted password with the one stored in the database
        if ($password === $stored_password) {
            // Password matches, set session variables
            $_SESSION['logged_in'] = true;
            $_SESSION['user_email'] = $email;
            $_SESSION['fname'] = $fname;
            $_SESSION['lname'] = $lname;
            $_SESSION['user_code'] = $user_code;

            // Calculate fines for overdue books and update permissions
            $totalFine = 0;
            $overdueBooksQuery = "SELECT loan_id, due_date FROM book_loans WHERE borrower_email = ? AND return_date IS NULL AND due_date < CURDATE()";
            $stmt = $conn->prepare($overdueBooksQuery);
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $result = $stmt->get_result();

            while ($loan = $result->fetch_assoc()) {
                $dueDate = new DateTime($loan['due_date']);
                $today = new DateTime();
                $interval = $dueDate->diff($today);
                $weeksOverdue = floor($interval->days / 7);
                $fineAmount = $weeksOverdue * 5; // Assuming $5 fine per week overdue

                // Update total fine
                $totalFine += $fineAmount;

                // Update or insert fine record for the loan
                $updateFineQuery = "INSERT INTO fines (loan_id, fine_amount, paid) VALUES (?, ?, FALSE) ON DUPLICATE KEY UPDATE fine_amount = ?, paid = FALSE";
                $fineStmt = $conn->prepare($updateFineQuery);
                $fineStmt->bind_param("idi", $loan['loan_id'], $fineAmount, $fineAmount);
                $fineStmt->execute();
            }

            // Check if total fine exceeds $15 and update permissions if necessary
            if ($totalFine > 15) {
                $updatePermissionsQuery = "UPDATE user_profiles SET permissions = 'NOT ALLOWED' WHERE email = ?";
                $permStmt = $conn->prepare($updatePermissionsQuery);
                $permStmt->bind_param("s", $email);
                $permStmt->execute();
            }
            
            // Redirect to homepage
            header("Location: index.php");
            exit();
        } else {
            // Password does not match
            echo "The password you entered was not valid.";
        }
    } else {
        // No account found with that email
        echo "No account found with that email.";
    }

    $stmt->close();
    $conn->close();
} else {
    // Form not submitted or missing email/password
    echo "Please fill both the email and password fields.";
}
?>
