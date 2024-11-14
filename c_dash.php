<?php
session_start();  // Start session

// Check if the user is logged in as a company
if (!isset($_SESSION['businessID'])) {
    echo "Error: Not logged in as a company.";
    exit;
}

$businessID = $_SESSION['businessID'];  // Retrieve businessID from session

// Database connection
$dsn = "mysql:host=localhost;dbname=chalaiden";
$username = "root";
$pw = "";
$pdo = new PDO($dsn, $username, $pw);

// Query to fetch total revenue for the company
$revenueQuery = "SELECT SUM(totalCompanyRevenue) AS totalRevenue FROM revenue WHERE businessID = ?";
$revenueStmt = $pdo->prepare($revenueQuery);
$revenueStmt->execute([$businessID]);
$totalRevenue = $revenueStmt->fetchColumn();

// Query to fetch available cars for the company
$query = "SELECT * FROM car WHERE businessID = ?";
$stmt = $pdo->prepare($query);
$stmt->execute([$businessID]);
$cars = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Query to fetch available drivers for the company
$query = "SELECT * FROM driver WHERE businessID = ?";
$stmt = $pdo->prepare($query);
$stmt->execute([$businessID]);
$drivers = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Query to fetch pending booking requests for the company
$query = "SELECT * FROM trip WHERE companyID = ? AND status = 'pending'";
$stmt = $pdo->prepare($query);
$stmt->execute([$businessID]);
$pendingRequests = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Query to fetch confirmed booking requests for the company
$query = "SELECT * FROM trip WHERE companyID = ? AND status = 'confirmed'";
$stmt = $pdo->prepare($query);
$stmt->execute([$businessID]);
$confirmedRequests = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Query to fetch completed booking requests along with the driver who completed the trip
$query = "SELECT trip.*, driver.username as driver_name 
          FROM trip 
          JOIN driver ON trip.driver = driver.username 
          WHERE trip.companyID = ? AND trip.status = 'completed'";
$stmt = $pdo->prepare($query);
$stmt->execute([$businessID]);
$completedRequests = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Handle booking confirmation
if (isset($_POST['confirmBooking'])) {
    $tripID = $_POST['tripID'];

    // Update the trip status to 'confirmed'
    $updateQuery = "UPDATE trip SET status = 'confirmed' WHERE tripID = ?";
    $updateStmt = $pdo->prepare($updateQuery);
    $updateStmt->execute([$tripID]);

    // Fetch the driver's username from the trip table for the confirmed trip
    $driverQuery = "SELECT driver, distance, start_time FROM trip WHERE tripID = ?";
    $driverStmt = $pdo->prepare($driverQuery);
    $driverStmt->execute([$tripID]);
    $driverResult = $driverStmt->fetch(PDO::FETCH_ASSOC);

    // If the driver is found, update their status to 'booked'
    if ($driverResult) {
        $driverUsername = $driverResult['driver'];

        // Calculate end time based on distance
        $distance = $driverResult['distance'];
        $startTime = new DateTime($driverResult['start_time']);
        
        define('AVERAGE_SPEED', 60); // Average speed in km/h
        $durationHours = $distance / AVERAGE_SPEED; // Duration in hours
        $endTime = clone $startTime; // Clone start time to create end time
        $endTime->modify("+{$durationHours} hours"); // Add duration to start time

        // Update the trip with end time
        $endTimeFormatted = $endTime->format('Y-m-d H:i:s');
        $updateTripQuery = "UPDATE trip SET end_time = ? WHERE tripID = ?";
        $updateTripStmt = $pdo->prepare($updateTripQuery);
        $updateTripStmt->execute([$endTimeFormatted, $tripID]);

        // Insert availability data for the driver
        $bookedFrom = $startTime->format('Y-m-d H:i:s');
        $bookedTo = $endTime->format('Y-m-d H:i:s');
        
        $insertAvailabilityQuery = "INSERT INTO driver_availability (driverUsername, booked_from, booked_to) VALUES (?, ?, ?)";
        $insertAvailabilityStmt = $pdo->prepare($insertAvailabilityQuery);
        $insertAvailabilityStmt->execute([$driverUsername, $bookedFrom, $bookedTo]);
        
    }

    // Reload the page to reflect the changes
    header("Location: c_dash.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Company Dashboard</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f4f4;
        }

        .container {
            width: 80%; /* Adjusted width */
            max-width: 900px; /* Set a maximum width */
            margin: 20px auto; /* Center the container */
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.1);
        }

        h2, h3 {
            text-align: center;
            color: #333;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        table th, table td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }

        table th {
            background-color: #f2f2f2;
            font-weight: bold;
        }

        button {
            background-color: #5cb85c;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        button:hover {
            background-color: #4cae4c;
        }

        form {
            display: inline-block;
        }

        a {
            color: #5cb85c;
            text-decoration: none;
            display: block;
            text-align: center;
            margin-top: 20px;
        }

        a:hover {
            text-decoration: underline;
        }
        .garage-btn {
            background-color: #4CAF50; /* Green */
            border: none;
            color: white;
            padding: 10px 20px;
            text-align: center;
            text-decoration: none;
            display: inline-block;
            font-size: 16px;
            margin: 10px 2px;
            cursor: pointer;
        }
        .btn-primary {
        background-color: #007bff;
        color: white;
        padding: 10px 20px;
        border: none;
        border-radius: 5px;
        cursor: pointer;
        font-size: 16px;
        }

        .btn-primary:hover {
            background-color: #0056b3;
        }

        .dashboard-actions {
            margin-top: 20px;
            text-align: center; /* Adjust based on your dashboard layout */
        }
    </style>
</head>
<body>

<div class="container">
    <h2>Welcome to the Company Dashboard</h2>
    <h3><?php echo "Total revenue: $".$totalRevenue ?></h3>
    <h3>Available Cars</h3>
    <table>
        <tr>
            <th>Car ID</th>
            <th>Model</th>
            <th>License Plate</th>
            <th>Status</th>
        </tr>
        <?php foreach ($cars as $car): ?>
        <tr>
            <td><?php echo htmlspecialchars($car['carID']); ?></td>
            <td><?php echo htmlspecialchars($car['model']); ?></td>
            <td><?php echo htmlspecialchars($car['licensePlate']); ?></td>
            <td><?php echo htmlspecialchars($car['status']); ?></td>
        </tr>
        <?php endforeach; ?>
    </table>

    <h3>Available Drivers</h3>
    <table>
        <tr>
            <th>Name</th>
            <th>License Number</th>
            <th>Status</th>
        </tr>
        <?php foreach ($drivers as $driver): ?>
        <tr>
            <td><?php echo htmlspecialchars($driver['username']); ?></td>
            <td><?php echo htmlspecialchars($driver['lisenceNo']); ?></td>
            <td><?php echo htmlspecialchars($driver['status_']); ?></td>
        </tr>
        <?php endforeach; ?>
    </table>

    <h3>Pending Booking Requests</h3>
    <table>
        <tr>
            <th>Trip ID</th>
            <th>Customer</th>
            <th>Pickup Location</th>
            <th>Destination</th>
            <th>Date</th>
            <th>Action</th>
        </tr>
        <?php foreach ($pendingRequests as $request): ?>
        <tr>
            <td><?php echo htmlspecialchars($request['tripID']); ?></td>
            <td><?php echo htmlspecialchars($request['user']); ?></td>
            <td><?php echo htmlspecialchars($request['location']); ?></td>
            <td><?php echo htmlspecialchars($request['destination']); ?></td>
            <td><?php echo htmlspecialchars($request['start_time']); ?></td>
            <td>
                <form action="c_dash.php" method="POST">
                    <input type="hidden" name="tripID" value="<?php echo htmlspecialchars($request['tripID']); ?>">
                    <button type="submit" name="confirmBooking">Confirm</button>
                </form>
            </td>
        </tr>
        <?php endforeach; ?>
    </table>

    <h3>Confirmed Booking Requests</h3>
    <table>
        <tr>
            <th>Trip ID</th>
            <th>Customer</th>
            <th>Pickup Location</th>
            <th>Destination</th>
            <th>Date</th>
            <th>Status</th>
        </tr>
        <?php foreach ($confirmedRequests as $request): ?>
        <tr>
            <td><?php echo htmlspecialchars($request['tripID']); ?></td>
            <td><?php echo htmlspecialchars($request['user']); ?></td>
            <td><?php echo htmlspecialchars($request['location']); ?></td>
            <td><?php echo htmlspecialchars($request['destination']); ?></td>
            <td><?php echo htmlspecialchars($request['start_time']); ?></td>
            <td><?php echo htmlspecialchars($request['status']); ?></td>
        </tr>
        <?php endforeach; ?>
    </table>

    <h3>Completed Booking Requests</h3>
    <table>
        <tr>
            <th>Trip ID</th>
            <th>Customer</th>
            <th>Pickup Location</th>
            <th>Destination</th>
            <th>Date</th>
            <th>Status</th>
            <th>Driver</th> <!-- New column for Driver -->
        </tr>
        <?php foreach ($completedRequests as $request): ?>
        <tr>
            <td><?php echo htmlspecialchars($request['tripID']); ?></td>
            <td><?php echo htmlspecialchars($request['user']); ?></td>
            <td><?php echo htmlspecialchars($request['location']); ?></td>
            <td><?php echo htmlspecialchars($request['destination']); ?></td>
            <td><?php echo htmlspecialchars($request['start_time']); ?></td>
            <td><?php echo htmlspecialchars($request['status']); ?></td>
            <td><?php echo htmlspecialchars($request['driver_name']); ?></td> <!-- Display driver's name -->
        </tr>
        <?php endforeach; ?>
    </table>
    <div class="dashboard-actions">
        <!-- Other buttons -->
        <button onclick="window.location.href='req_and_exchange.php'" class="garage-btn">Request & Exchange</button>
        <button class="garage-btn" onclick="window.location.href='garage.php';">Go to Garage Space Sharing</button>
    </div>
    <!-- Button to redirect to garage space sharing page -->
    <a href="logout.php">Logout</a>

</div>

</body>
</html>
