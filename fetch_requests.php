<?php
// Include your database connection
include 'db.php';

$companyID = $_GET['company_id'];

// Fetch incoming car requests
$incomingCarRequests = mysqli_query($conn, "SELECT * FROM car_exchange_request WHERE requestedCompanyID = $companyID");
$incomingCarRequestsData = mysqli_fetch_all($incomingCarRequests, MYSQLI_ASSOC);

// Fetch incoming driver requests
$incomingDriverRequests = mysqli_query($conn, "SELECT * FROM driver_exchange_request WHERE requestedCompanyID = $companyID");
$incomingDriverRequestsData = mysqli_fetch_all($incomingDriverRequests, MYSQLI_ASSOC);

// Fetch outgoing car requests
$outgoingCarRequests = mysqli_query($conn, "SELECT * FROM car_exchange_request WHERE requestingCompanyID = $companyID");
$outgoingCarRequestsData = mysqli_fetch_all($outgoingCarRequests, MYSQLI_ASSOC);

// Fetch outgoing driver requests
$outgoingDriverRequests = mysqli_query($conn, "SELECT * FROM driver_exchange_request WHERE requestingCompanyID = $companyID");
$outgoingDriverRequestsData = mysqli_fetch_all($outgoingDriverRequests, MYSQLI_ASSOC);

$response = [
    'incomingCarRequests' => $incomingCarRequestsData,
    'incomingDriverRequests' => $incomingDriverRequestsData,
    'outgoingCarRequests' => $outgoingCarRequestsData,
    'outgoingDriverRequests' => $outgoingDriverRequestsData,
];

echo json_encode($response);
?>
