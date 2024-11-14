<?php
// Include your database connection
include 'db.php';

$requestType = $_POST['request_type'];
$companyID = $_POST['company_id'];
$requestedCompanyID = $_POST['requested_company_id'];

if ($requestType === 'car') {
    $carID = $_POST['car_id'];
    $query = "INSERT INTO car_exchange_request (requestingCompanyID, requestedCompanyID, carID, status)
              VALUES ($companyID, $requestedCompanyID, $carID, 'pending')";
} else {
    $driverUsername = $_POST['driver_username'];
    $query = "INSERT INTO driver_exchange_request (requestingCompanyID, requestedCompanyID, driverUsername, status)
              VALUES ($companyID, $requestedCompanyID, '$driverUsername', 'pending')";
}

if (mysqli_query($conn, $query)) {
    echo "Request submitted successfully!";
} else {
    echo "Error: " . mysqli_error($conn);
}
?>
