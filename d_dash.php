<?php
session_start();
require("db.php");  // Include the database connection

// Assuming the driver is logged in and their username is stored in the session
$driver_username = $_SESSION['username'];

// Fetch driver information
$driver_query = "SELECT * FROM driver WHERE username = ?";
$driver_stmt = $pdo->prepare($driver_query);
$driver_stmt->execute([$driver_username]);
$driver = $driver_stmt->fetch(PDO::FETCH_ASSOC);

// Fetch total driver revenue
$total_revenue_query = "SELECT SUM(totalDriverEarnings) AS totalEarnings FROM revenue WHERE driverUsername = ?";
$total_revenue_stmt = $pdo->prepare($total_revenue_query);
$total_revenue_stmt->execute([$driver_username]);
$total_revenue = $total_revenue_stmt->fetch(PDO::FETCH_ASSOC);

// Store total earnings in a variable
$total_earnings = $total_revenue['totalEarnings'] ? $total_revenue['totalEarnings'] : 0.00;

// Update driver availability status if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['toggle_status'])) {
    $new_status = $_POST['status'] == 'available' ? 'unavailable' : 'available';

    // Update status in the database
    $update_status_query = "UPDATE driver SET status_ = ? WHERE username = ?";
    $update_status_stmt = $pdo->prepare($update_status_query);
    $update_status_stmt->execute([$new_status, $driver_username]);

    // Reload the page to reflect the updated status
    header("Location: d_dash.php");
    exit;
}

// Update trip status if the driver marks it as completed
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['complete_trip'])) {
    $trip_id = $_POST['tripID'];

    // Check if the trip is confirmed and assigned to the driver
    $check_trip_query = "SELECT status, price, companyID, start_time, end_time FROM trip WHERE tripID = ? AND driver = ?";
    $check_trip_stmt = $pdo->prepare($check_trip_query);
    $check_trip_stmt->execute([$trip_id, $driver_username]);
    $trip = $check_trip_stmt->fetch(PDO::FETCH_ASSOC);

    if ($trip) {
        // Handle trip completion
        if ($trip['status'] == 'confirmed') {
            // Update trip status to 'completed'
            $complete_trip_query = "UPDATE trip SET status = 'completed' WHERE tripID = ? AND driver = ?";
            $complete_trip_stmt = $pdo->prepare($complete_trip_query);
            $complete_trip_stmt->execute([$trip_id, $driver_username]);

            // Increment driver's rating (completed trips count) by 1
            $increment_rating_query = "UPDATE driver SET rating = rating + 1 WHERE username = ?";
            $increment_rating_stmt = $pdo->prepare($increment_rating_query);
            $increment_rating_stmt->execute([$driver_username]);

            // Calculate revenue share
            $total_price = $trip['price'];
            $company_revenue = $total_price * 0.70; // 70% for company
            $driver_earnings = $total_price * 0.30;  // 30% for driver
            $businessID = $trip['companyID']; // Ensure this is correct

            // Check if revenue entry exists for this driver and business
            $check_revenue_query = "SELECT * FROM revenue WHERE businessID = ? AND driverUsername = ?";
            $check_revenue_stmt = $pdo->prepare($check_revenue_query);
            $check_revenue_stmt->execute([$businessID, $driver_username]);
            $revenue_entry = $check_revenue_stmt->fetch(PDO::FETCH_ASSOC);

            if ($revenue_entry) {
                // If entry exists, update it
                $update_revenue_query = "UPDATE revenue SET totalCompanyRevenue = totalCompanyRevenue + ?, totalDriverEarnings = totalDriverEarnings + ?, lastUpdated = CURRENT_TIMESTAMP WHERE revenueID = ?";
                $update_revenue_stmt = $pdo->prepare($update_revenue_query);
                $update_revenue_stmt->execute([$company_revenue, $driver_earnings, $revenue_entry['revenueID']]);
            } else {
                // If entry does not exist, insert a new one
                $insert_revenue_query = "INSERT INTO revenue (businessID, driverUsername, totalCompanyRevenue, totalDriverEarnings) VALUES (?, ?, ?, ?)";
                $insert_revenue_stmt = $pdo->prepare($insert_revenue_query);
                $insert_revenue_stmt->execute([$businessID, $driver_username, $company_revenue, $driver_earnings]);
            }

            // Update driver availability to free up the timeslot
            $booked_from = $trip['start_time'];
            $booked_to = $trip['end_time'];

            $delete_availability_query = "DELETE FROM driver_availability WHERE driverUsername = ? AND booked_from = ? AND booked_to = ?";
            $delete_availability_stmt = $pdo->prepare($delete_availability_query);
            $delete_availability_stmt->execute([$driver_username, $booked_from, $booked_to]);

            // Reload the page to reflect the updated trip list and rating
            header("Location: d_dash.php");
            exit;
        } else {
            echo "Only trips with 'confirmed' status can be marked as completed.";
        }
    }
}

// Handle trip cancellation
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['cancel_trip'])) {
    $trip_id = $_POST['tripID'];

    // Check if the trip is confirmed and assigned to the driver
    $check_trip_query = "SELECT status, start_time, end_time FROM trip WHERE tripID = ? AND driver = ?";
    $check_trip_stmt = $pdo->prepare($check_trip_query);
    $check_trip_stmt->execute([$trip_id, $driver_username]);
    $trip = $check_trip_stmt->fetch(PDO::FETCH_ASSOC);

    if ($trip) {
        // Handle trip cancellation
        if ($trip['status'] == 'confirmed') {
            // Update trip status to 'cancelled'
            $cancel_trip_query = "UPDATE trip SET status = 'cancelled' WHERE tripID = ? AND driver = ?";
            $cancel_trip_stmt = $pdo->prepare($cancel_trip_query);
            $cancel_trip_stmt->execute([$trip_id, $driver_username]);

            // Update driver availability to free up the timeslot
            $booked_from = $trip['start_time'];
            $booked_to = $trip['end_time'];

            $delete_availability_query = "DELETE FROM driver_availability WHERE driverUsername = ? AND booked_from = ? AND booked_to = ?";
            $delete_availability_stmt = $pdo->prepare($delete_availability_query);
            $delete_availability_stmt->execute([$driver_username, $booked_from, $booked_to]);

            // Reload the page to reflect the updated trip list
            header("Location: d_dash.php");
            exit;
        } else {
            echo "Only trips with 'confirmed' status can be cancelled.";
        }
    }
}

// Fetch upcoming trips for the driver (only trips that are not completed or cancelled)
$trips_query = "SELECT tripID, location, destination, start_time, end_time, status, price FROM trip WHERE driver = ? AND status NOT IN ('completed', 'cancelled') ORDER BY start_time ASC";
$trips_stmt = $pdo->prepare($trips_query);
$trips_stmt->execute([$driver_username]);
$upcoming_trips = $trips_stmt->fetchAll(PDO::FETCH_ASSOC);
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Driver Dashboard</title>

    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f4f4;
        }
        .container {
            width: 80%;
            margin: auto;
            padding: 20px;
            background: white;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        h1 {
            text-align: center;
            color: #333;
        }
        .driver-info {
            margin-bottom: 30px;
        }
        .driver-info h2 {
            color: #555;
        }
        .trip-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        .trip-table th, .trip-table td {
            border: 1px solid #ddd;
            padding: 10px;
            text-align: center;
        }
        .trip-table th {
            background-color: #f2f2f2;
            color: #333;
        }
        .trip-table td {
            background-color: #fff;
        }
        .action-buttons {
            margin-top: 20px;
        }
        button {
            background-color: #5cb85c;
            color: white;
            border: none;
            padding: 10px;
            cursor: pointer;
            border-radius: 5px;
        }
        button:hover {
            background-color: #4cae4c;
        }
        .toggle-status {
            margin-bottom: 20px;
        }
    </style>
</head>
<body>

<div class="container">
    <h1>Driver Dashboard</h1>
    <h3><?php echo "Total Revenue: $".$total_earnings ?></h3>

    <!-- Driver Info Section -->
    <div class="driver-info">
        <h2>Driver Information</h2>
        <p><strong>Name:</strong> <?php echo $driver['username']; ?></p>
        <p><strong>License Number:</strong> <?php echo $driver['lisenceNo']; ?></p>
        <p><strong>Completed Trips:</strong> <?php echo $driver['rating']; ?></p>
        <p><strong>Accident History:</strong> <?php echo $driver['accident_history']; ?></p>
        <p><strong>Status:</strong> <?php echo ucfirst($driver['status_']); ?></p>

        <!-- Toggle Driver Availability -->
        <form action="" method="POST" class="toggle-status">
            <input type="hidden" name="status" value="<?php echo $driver['status_']; ?>">
            <button type="submit" name="toggle_status">
                Mark as <?php echo $driver['status_'] == 'available' ? 'Unavailable' : 'Available'; ?>
            </button>
        </form>
    </div>

    <!-- Upcoming Trips Table -->
    <h2>Upcoming Trips</h2>
    <?php if (count($upcoming_trips) > 0): ?>
        <table class="trip-table">
            <thead>
                <tr>
                    <th>Trip ID</th>
                    <th>Location</th>
                    <th>Destination</th>
                    <th>Start Time</th>
                    <th>End Time</th>
                    <th>Status</th>
                    <th>Actions</th> <!-- Added Actions column -->
                </tr>
            </thead>
            <tbody>
                <?php foreach ($upcoming_trips as $trip): ?>
                    <tr>
                        <td><?php echo $trip['tripID']; ?></td>
                        <td><?php echo $trip['location']; ?></td>
                        <td><?php echo $trip['destination']; ?></td>
                        <td><?php echo date('Y-m-d H:i', strtotime($trip['start_time'])); ?></td>
                        <td><?php echo date('Y-m-d H:i', strtotime($trip['end_time'])); ?></td>
                        <td><?php echo ucfirst($trip['status']); ?></td>
                        <td>
                            <?php if ($trip['status'] == 'confirmed'): ?>
                                <!-- Complete Trip Button -->
                                <form action="" method="POST" style="display:inline;">
                                    <input type="hidden" name="tripID" value="<?php echo $trip['tripID']; ?>">
                                    <button type="submit" name="complete_trip">Complete</button>
                                </form>
                            <?php else: ?>
                                <span>Cannot complete</span>
                            <?php endif; ?>
                        </td> <!-- End of Actions column -->
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p>No upcoming trips available.</p>
    <?php endif; ?>

    <!-- Action Buttons -->
    <div class="action-buttons">
        <button onclick="window.location.href='logout.php';">Logout</button>
    </div>
</div>

</body>
</html>
