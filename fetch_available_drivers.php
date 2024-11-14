<?php
session_start();
require("db.php");  // Include the database connection

if (isset($_GET['start_time']) && isset($_GET['companyID'])) {
    $start_time = $_GET['start_time'];
    $companyID = $_GET['companyID'];

    // Calculate end time based on the distance and average speed
    // Here we assume you have a method to calculate the end time based on selected trip details

    $end_time = date('Y-m-d H:i:s', strtotime($start_time) + (60 * 60)); // Placeholder for 1 hour duration

    // Fetch available drivers
    $driver_query = "
        SELECT username, rating 
        FROM drivers 
        WHERE companyID = ? 
        AND username NOT IN (
            SELECT driver FROM trip 
            WHERE (start_time BETWEEN ? AND ?)
            OR (end_time BETWEEN ? AND ?)
        )
    ";
    $driver_stmt = $pdo->prepare($driver_query);
    $driver_stmt->execute([$companyID, $start_time, $end_time, $start_time, $end_time]);
    $available_drivers = $driver_stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($available_drivers);
} else {
    echo json_encode([]); // Return empty array if parameters are missing
}
?>
