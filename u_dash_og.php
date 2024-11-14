<?php
session_start();
require("db.php"); // Include database connection

// Check if cancel request is made
if (isset($_POST['cancel_trip_id'])) {
    $cancel_trip_id = $_POST['cancel_trip_id'];
    
    // Update trip status to 'cancelled'
    $cancel_query = "UPDATE trip SET status = 'cancelled' WHERE tripID = ? AND user = ?";
    $cancel_stmt = $pdo->prepare($cancel_query);
    $cancel_stmt->execute([$cancel_trip_id, $_SESSION['username']]);
}

// Fetch trip data for the logged-in user
$trip_query = "SELECT tripID, user, driver, companyID, location, destination, distance, price, start_time, end_time, status FROM trip WHERE user = ?";
$trip_stmt = $pdo->prepare($trip_query);
$trip_stmt->execute([$_SESSION['username']]);
$trips = $trip_stmt->fetchAll(PDO::FETCH_ASSOC);

// Trip summary calculations
$total_trips = count($trips);
$total_distance = 0;
$total_spent = 0;

foreach ($trips as $trip) {
    // Only add distance and price to the totals if the trip is completed
    if ($trip['status'] === 'completed') {
        $total_distance += $trip['distance'];
        $total_spent += $trip['price'];
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Dashboard - Trip Details</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f4f4;
        }

        .container {
            max-width: 1000px;
            margin: 20px auto;
            padding: 20px;
            background: white;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        h1, h2, h3 {
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

        .summary-box {
            display: flex;
            justify-content: space-between;
            margin-bottom: 20px;
            padding: 10px;
            background-color: #f9f9f9;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }

        .summary-item {
            text-align: center;
        }

        .summary-item h3 {
            margin: 0;
            font-size: 1.5em;
            color: #4CAF50;
        }

        .summary-item p {
            margin: 5px 0 0;
            color: #555;
        }

        .redirect-btn {
            display: block;
            width: 200px;
            margin: 20px auto;
            padding: 10px;
            background-color: #4CAF50;
            color: white;
            text-align: center;
            text-decoration: none;
            border-radius: 5px;
        }

        .redirect-btn:hover {
            background-color: #45a049;
        }

        .cancel-btn {
            background-color: #f44336;
            color: white;
            padding: 5px 10px;
            border: none;
            cursor: pointer;
            border-radius: 5px;
        }

        .cancel-btn:hover {
            background-color: #d32f2f;
        }
    </style>
</head>
<body>
<div class="container">
    <h1>User Dashboard</h1>
    <h3>Hi, <?php echo $_SESSION['username']."!" ?> </h3>

    <div class="summary-box">
        <div class="summary-item">
            <h3><?php echo $total_trips; ?></h3>
            <p>Total Trips</p>
        </div>
        <div class="summary-item">
            <h3><?php echo $total_distance; ?> km</h3>
            <p>Total Distance</p>
        </div>
        <div class="summary-item">
            <h3>$<?php echo number_format($total_spent, 2); ?></h3>
            <p>Total Spent</p>
        </div>
    </div>

    <h2>Trip Details</h2>

    <?php if ($total_trips > 0): ?>
        <table>
            <thead>
                <tr>
                    <th>Trip ID</th>
                    <th>Driver</th>
                    <th>Company</th>
                    <th>Location</th>
                    <th>Destination</th>
                    <th>Distance (km)</th>
                    <th>Price ($)</th>
                    <th>Start Time</th>
                    <th>End Time</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($trips as $trip): ?>
                    <tr>
                        <td><?php echo $trip['tripID']; ?></td>
                        <td><?php echo $trip['driver']; ?></td>
                        <td><?php echo $trip['companyID']; ?></td>
                        <td><?php echo $trip['location']; ?></td>
                        <td><?php echo $trip['destination']; ?></td>
                        <td><?php echo $trip['distance']; ?></td>
                        <td><?php echo number_format($trip['price'], 2); ?></td>
                        <td><?php echo $trip['start_time']; ?></td>
                        <td><?php echo $trip['end_time']; ?></td>
                        <td><?php echo ucfirst($trip['status']); ?></td>
                        <td>
                            <?php if ($trip['status'] != 'cancelled' && $trip['status'] != 'completed'): ?>
                                <form method="POST">
                                    <input type="hidden" name="cancel_trip_id" value="<?php echo $trip['tripID']; ?>">
                                    <button type="submit" class="cancel-btn">Cancel</button>
                                </form>
                            <?php elseif ($trip['status'] == 'cancelled'): ?>
                                <span>Cancelled</span>
                            <?php else: ?>
                                <span>Completed</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        
    <?php else: ?>
        <p>No trips found.</p>
    <?php endif; ?>

    <a href="u_dash.php" class="redirect-btn">Bookings</a>
</div>
</body>
</html>
