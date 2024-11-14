<?php
// Include your database connection
include 'db.php';

$requestType = $_GET['request_type'];
$requestID = $_GET['request_id'];
$action = $_GET['action'];

$status = $action === 'approve' ? 'approved' : 'rejected';

if ($requestType === 'car') {
    $query = "UPDATE car_exchange_request SET status = '$status' WHERE requestID = $requestID";
} else {
    $query = "UPDATE driver_exchange_request SET status = '$status' WHERE requestID = $requestID";
}

if (mysqli_query($conn, $query)) {
    echo "Request has been $status!";
} else {
    echo "Error: " . mysqli_error($conn);
}
?>
