<?php
// get_image.php
require_once 'db_connection.php'; // Adjust this path as needed

if (isset($_GET['isbn'])) {
    $isbn = $_GET['isbn'];
    $conn = new mysqli($hn, $un, $pw, $db);
    
    if ($conn->connect_error) die($conn->connect_error);

    $query = "SELECT image FROM books WHERE isbn=?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $isbn);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $stmt->bind_result($image);
        $stmt->fetch();
        header("Content-Type: image/jpeg"); // Adjust the content type if necessary
        echo $image;
    } else {
        echo "No image found.";
    }
    
    $stmt->close();
    $conn->close();
}
?>
