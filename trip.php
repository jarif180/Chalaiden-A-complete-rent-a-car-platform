<?php
session_start();
require("db.php");  // Include the database connection

// Retrieve data from the query string
$driver = $_GET['driver'] ?? 'Unknown Driver';
$location = $_GET['location'] ?? 'Unknown Location';
$destination = $_GET['destination'] ?? 'Unknown Destination';
$distance = (int)($_GET['distance'] ?? 0);
$trip_price = (float)($_GET['trip_price'] ?? 0.0);
$start_time = $_GET['start_time'] ?? 'Unknown Start Time';

// Fetch trip status from the database for display (if needed)
$status_query = "SELECT status FROM trip WHERE user = ? AND driver = ? AND start_time = ?";
$status_stmt = $pdo->prepare($status_query);
$status_stmt->execute([$_SESSION['username'], $driver, $start_time]);
$status_result = $status_stmt->fetch(PDO::FETCH_ASSOC);
$status = $status_result ? $status_result['status'] : 'pending';

// Display trip confirmation
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Trip Confirmation</title>
</head>
<body>
    <div class="container">
        <h1>Trip Confirmation</h1>
        <p>Driver: <?php echo htmlspecialchars($driver); ?></p>
        <p>Location: <?php echo htmlspecialchars($location); ?></p>
        <p>Destination: <?php echo htmlspecialchars($destination); ?></p>
        <p>Distance: <?php echo $distance; ?> km</p>
        <p>Trip Price: $<?php echo number_format($trip_price, 2); ?></p>
        <p>Start Time: <?php echo htmlspecialchars($start_time); ?></p>
        <p>Status: <?php echo htmlspecialchars($status); ?></p>

        <button onclick="window.location.href='u_dash.php';">Back to Dashboard</button>
    </div>
</body>
</html>
