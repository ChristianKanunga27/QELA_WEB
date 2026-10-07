<?php
require_once __DIR__ . '/config.php';

error_reporting(E_ALL);
ini_set('display_errors',1);
// Only process POST requests
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    // Get submitted data
    $email = trim($_POST["email"] ?? "");
    $password = $_POST["password"] ?? "";


    // Check if fields are empty
    if (empty($email) || empty($password)) {
        die("Email and password are required.");
    }


    // Search for administrator
    $sql = "SELECT id, email, password 
            FROM administrators 
            WHERE email = ? 
            LIMIT 1";    

    // Prepare statement
    $stmt = mysqli_prepare($connection, $sql);

    if (!$stmt) {
        die("Query preparation failed: " . mysqli_error($connection));
    }


    // Bind email
    mysqli_stmt_bind_param($stmt, "s", $email);


    // Execute query
    mysqli_stmt_execute($stmt);


    // Get result
    $result = mysqli_stmt_get_result($stmt);


    // Get administrator
    $admin = mysqli_fetch_assoc($result);


    // Check login credentials
    if ($admin && password_verify($password, $admin["password"])) {

 session_start();

    $_SESSION['admin_logged_in'] = true;
        // Redirect to dashboard
        header("Location: ./dashboard.php");
        exit;

    } else {

        // Login failed
        header("location: ./login.html");
    }


    // Close statement
    mysqli_stmt_close($stmt);
}


// Close database connection
mysqli_close($connection);

?>
