<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);


// Database connection details based on environment (localhost or production)
if ($_SERVER['SERVER_NAME'] == 'localhost' || $_SERVER['SERVER_ADDR'] == '127.0.0.1' || $_SERVER['SERVER_ADDR'] == '::1') {
    // Code for localhost
    $servername = "localhost";
    $username = "debian-sys-maint";
    $password = "X8ELMCUGIypycqn2";
    $dbname = "portfolio";
} else {
    // Code for production server or remote server
    $servername = "localhost"; // Replace with actual production server name if needed
    $username = "joshibip_joshibip";
    $dbname = "joshibip_portfolio";
    $password = "QG(Kit70IPG9";
}

//    $username = "joshibip_joshibip";
//     $dbname = "joshibip_portfolio";
//     $password = "QG(Kit70IPG9";

// Create a single function for database connection
function getDbConnection($servername, $username, $password, $dbname) {
    $conn = new mysqli($servername, $username, $password, $dbname);
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
    return $conn;
}

// Function to execute a prepared statement and return the result
function executePreparedStatement($conn, $sql, $types = "", $params = []) {
    $stmt = $conn->prepare($sql);
    if ($types && $params) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $result = $stmt->get_result(); // Use get_result for SELECT queries
    $stmt->close();
    return $result ? $result->fetch_assoc() : null;
}

$conn = getDbConnection($servername, $username, $password, $dbname);

// Get user IP address and page name
$user_ip = $_SERVER['REMOTE_ADDR'];
$user_agent = $_SERVER['HTTP_USER_AGENT']; 
// echo '<pre>';
// print_r($_SERVER['HTTP_USER_AGENT']);
// echo '</pre>';
// die;
// $page = basename($_SERVER['PHP_SELF']);
$page = 'gym';

// Insert page view
$insertSql = "INSERT INTO views (page, user_ip, user_agent, view_date) VALUES (?, ?, ?, CURDATE())";
executePreparedStatement($conn, $insertSql, "sss", [$page, $user_ip, $user_agent]);


// Get total page count
// $countSql = "SELECT COUNT(*) AS total_count FROM views where page='gym'";
// $totalCount = executePreparedStatement($conn, $countSql);
// $totalCount = $totalCount['total_count'];

// Get distinct page count
$distinctCountSql = "SELECT COUNT(DISTINCT user_ip) AS distinct_count FROM views";
$distinctCount = executePreparedStatement($conn, $distinctCountSql);
$distinctCount = $distinctCount['distinct_count'];

// Close connection
$conn->close();
?>