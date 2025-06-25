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

// Create a single function for database connection
function getDbConnection($servername, $username, $password, $dbname) {
    $conn = new mysqli($servername, $username, $password, $dbname);
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
    return $conn;
}

// Function to execute a prepared statement and return the result (single or multiple results)
function executePreparedStatement($conn, $sql, $types = "", $params = [], $multiple = false) {
    $stmt = $conn->prepare($sql);
    if ($types && $params) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $result = $stmt->get_result(); // Use get_result for SELECT queries
    $stmt->close();
    
    if ($multiple) {
        // If multiple results are expected, fetch all rows
        return $result->fetch_all(MYSQLI_ASSOC);
    } else {
        // Return only one result
        return $result ? $result->fetch_assoc() : null;
    }
}

$filter = isset($_GET['action']) && $_GET['action'] == 'filter'; // Check if action=filter is passed

$conn = getDbConnection($servername, $username, $password, $dbname);

// Get total page count
if ($filter) {
    // Get distinct count by user_ip for filtered views
    $countSql = "SELECT COUNT(DISTINCT user_ip) AS total_count FROM views WHERE page='gym'";
} else {
    // Get total count of all views
    $countSql = "SELECT COUNT(*) AS total_count FROM views WHERE page='gym'";
}

$totalCount = executePreparedStatement($conn, $countSql);
$totalCount = $totalCount['total_count'];

// Get page views with or without filtering by user_ip
if ($filter) {
    $pageViewsSql = "SELECT user_ip, COUNT(*) AS view_count FROM views WHERE page='gym' GROUP BY user_ip ORDER BY view_count DESC";
} else {
    $pageViewsSql = "SELECT * FROM views WHERE page='gym' ORDER BY id DESC";
}

$pageViewsResult = executePreparedStatement($conn, $pageViewsSql, "", [], true);

// Close the connection
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Page Views</title>
    <!-- Bootstrap CSS -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-LN+7fdVzj6u52u30Kp6M/trliBMCMKTyK833zpbD+pXdCLuTusPj697FH4R/5mcr" crossorigin="anonymous"></head>
<body>

<div class="container my-5">
    <h1>Page Views for "Gym" Page</h1>
    
    <!-- Display total count -->
    <h4>Total Page Views: <?php echo $totalCount; ?></h4>

    <!-- Table for displaying page views -->
    <table class="table table-bordered mt-4">
        <thead>
            <tr>
                <?php if ($filter): ?>
                    <th>User IP</th>
                    <th>View Count</th>
                <?php else: ?>
                    <th>ID</th>
                    <th>Page</th>
                    <th>User IP</th>
                    <th>User Agent</th>
                    <th>View Date</th>
                <?php endif; ?>
            </tr>
        </thead>
        <tbody>
            <?php if ($pageViewsResult): ?>
                <?php foreach ($pageViewsResult as $row): ?>
                    <tr>
                        <?php if ($filter): ?>
                            <td><?php echo htmlspecialchars($row['user_ip']); ?></td>
                            <td><?php echo htmlspecialchars($row['view_count']); ?></td>
                        <?php else: ?>
                            <td><?php echo htmlspecialchars($row['id']); ?></td>
                            <td><?php echo htmlspecialchars($row['page']); ?></td>
                            <td><?php echo htmlspecialchars($row['user_ip']); ?></td>
                            <td><?php echo htmlspecialchars($row['user_agent']); ?></td>
                            <td><?php echo htmlspecialchars($row['view_date']); ?></td>
                        <?php endif; ?>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr><td colspan="5">No data found</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js" integrity="sha384-B4gt1jrGC7Jh4AgpNjZjDoJr2dyFg2k6Mw13mgP7EIB+g5Vrk4p+PiVKp3Mj6dH1" crossorigin="anonymous"></script>
</body>
</html>
