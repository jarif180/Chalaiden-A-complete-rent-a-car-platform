<?php
session_start();
require("db.php");  // Include the database connection

// Fetch garages (separated by user and company ownership)
$garage_query_user = "SELECT * FROM garage WHERE ownerUsername IN (SELECT username FROM user WHERE role_ = 'user')";
$garage_query_company = "SELECT * FROM garage WHERE ownerUsername IN (SELECT username FROM user WHERE role_ = 'company')";

$user_garages = $pdo->query($garage_query_user)->fetchAll(PDO::FETCH_ASSOC);
$company_garages = $pdo->query($garage_query_company)->fetchAll(PDO::FETCH_ASSOC);

// Handle garage posting
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["post_garage"])) {
    $location = $_POST["location"];
    $capacity = $_POST["capacity"];
    $availableSpaces = $_POST["availableSpaces"];
    $price_per_day = $_POST["price_per_day"];
    $availability_from = $_POST["availability_from"];
    $availability_to = $_POST["availability_to"];
    $description = $_POST["description"];

    // Determine the owner (user or company)
    $ownerUsername = $_SESSION['username'];

    // Check if owner exists
    $owner_check_query = "SELECT * FROM user WHERE username = ?";
    $stmt = $pdo->prepare($owner_check_query);
    $stmt->execute([$ownerUsername]);
    $owner = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$owner) {
        echo "Owner does not exist.";
    } else {
        // Insert the garage post into the database
        $insert_query = "INSERT INTO garage (ownerUsername, location, capacity, availableSpaces, price_per_day, status) 
                         VALUES (?, ?, ?, ?, ?, 'available')";
        $stmt = $pdo->prepare($insert_query);
        $stmt->execute([$ownerUsername, $location, $capacity, $availableSpaces, $price_per_day]);

        // Get the last inserted garageID for availability insertion
        $garageID = $pdo->lastInsertId();

        // Insert availability record for the garage
        $availability_query = "INSERT INTO garage_availability (garageID, available_from, available_to) 
                               VALUES (?, ?, ?)";
        $stmt = $pdo->prepare($availability_query);
        $stmt->execute([$garageID, $availability_from, $availability_to]);

        header("Location: garage.php");
        exit;
    }
}

// Handle garage booking
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["book_garage"])) {
    $garageID = $_POST["garageID"];
    $start_date = $_POST["start_date"];
    $end_date = $_POST["end_date"];
    $renterUsername = $_SESSION['username'];

    // Fetch garage details for calculating total price
    $garage_query = "SELECT price_per_day FROM garage WHERE garageID = ?";
    $stmt = $pdo->prepare($garage_query);
    $stmt->execute([$garageID]);
    $garage = $stmt->fetch(PDO::FETCH_ASSOC);

    // Calculate the total price for the rental period
    $days = (strtotime($end_date) - strtotime($start_date)) / (60 * 60 * 24);
    $total_price = $garage['price_per_day'] * $days;

    // Check for overlapping bookings
    $overlap_query = "SELECT * FROM garage_booking WHERE garageID = ? 
                      AND (start_date BETWEEN ? AND ? OR end_date BETWEEN ? AND ?)";
    $stmt = $pdo->prepare($overlap_query);
    $stmt->execute([$garageID, $start_date, $end_date, $start_date, $end_date]);
    $overlap = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($overlap) {
        echo "This garage space is already booked for the selected time.";
    } else {
        // Insert the booking into the database
        $booking_query = "INSERT INTO garage_booking (garageID, renterUsername, start_date, end_date, total_price, status) 
                          VALUES (?, ?, ?, ?, ?, 'pending')";
        $stmt = $pdo->prepare($booking_query);
        $stmt->execute([$garageID, $renterUsername, $start_date, $end_date, $total_price]);

        echo "Booking request submitted!";
    }
}

// Fetch booked garages for the current user
$booked_garages_query = "SELECT gb.*, g.location, g.price_per_day FROM garage_booking gb 
                         JOIN garage g ON gb.garageID = g.garageID 
                         WHERE gb.renterUsername = ?";
$booked_stmt = $pdo->prepare($booked_garages_query);
$booked_stmt->execute([$_SESSION['username']]);
$booked_garages = $booked_stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Garage Space Sharing</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 20px;
        }

        h1, h2 {
            color: #333;
        }

        form {
            background-color: white;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
        }

        label {
            display: block;
            margin-bottom: 5px;
            color: #555;
        }

        input[type="text"],
        input[type="number"],
        input[type="datetime-local"],
        textarea {
            width: 100%;
            padding: 10px;
            margin-bottom: 10px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }

        button {
            background-color: #28a745;
            color: white;
            border: none;
            padding: 10px;
            border-radius: 5px;
            cursor: pointer;
        }

        button:hover {
            background-color: #218838;
        }

        .garage-list {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
        }

        .garage-item {
            background-color: #fff;
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .garage-item h3 {
            margin-top: 0;
            color: #333;
        }

        .garage-item p {
            margin: 10px 0;
            color: #555;
        }

        .booked {
            margin-top: 30px;
            background-color: #d9edf7; /* Light blue background */
            padding: 15px;
            border-radius: 5px;
        }
    </style>
</head>
<body>
    <h1>Garage Space Sharing</h1>

    <!-- Garage Post Form -->
    <h2>Post Your Garage Space</h2>
    <form method="POST" action="">
        <label for="location">Location:</label>
        <input type="text" name="location" id="location" required>

        <label for="capacity">Capacity (in vehicles):</label>
        <input type="number" name="capacity" id="capacity" required>

        <label for="availableSpaces">Available Spaces:</label>
        <input type="number" name="availableSpaces" id="availableSpaces" required>

        <label for="price_per_day">Price per Day:</label>
        <input type="number" step="0.01" name="price_per_day" id="price_per_day" required>

        <label for="availability_from">Available From:</label>
        <input type="datetime-local" name="availability_from" id="availability_from" required>

        <label for="availability_to">Available Until:</label>
        <input type="datetime-local" name="availability_to" id="availability_to" required>

        <label for="description">Description:</label>
        <textarea name="description" id="description" rows="4" required></textarea>
        
        <button type="submit" name="post_garage">Post Garage Space</button>

        <button onclick="window.location.href='<?php echo ($_SESSION['role'] == 'company') ? 'c_dash.php' : ($_SESSION['role'] == 'user' ? 'u_dash.php' : '#'); ?>'">
        <-Back
        </button>
        
    </form>
    

    <!-- Display Available User Garages -->
    <h2>Available User Garages</h2>
    <div class="garage-list">
        <?php foreach ($user_garages as $garage): ?>
            <div class="garage-item">
                <h3>Location: <?= htmlspecialchars($garage['location']); ?></h3>
                <p>Capacity: <?= htmlspecialchars($garage['capacity']); ?> vehicles</p>
                <p>Available Spaces: <?= htmlspecialchars($garage['availableSpaces']); ?></p>
                <p>Price: BDT <?= htmlspecialchars($garage['price_per_day']); ?>/day</p>
                <form method="POST" action="">
                    <input type="hidden" name="garageID" value="<?= $garage['garageID']; ?>">
                    <label for="start_date">Start Date:</label>
                    <input type="datetime-local" name="start_date" required>

                    <label for="end_date">End Date:</label>
                    <input type="datetime-local" name="end_date" required>

                    <button type="submit" name="book_garage">Book Garage</button>
                </form>
                
                
            </div>
        <?php endforeach; ?>
    </div>

    <!-- Display Available Company Garages -->
    <h2>Available Company Garages</h2>
    <div class="garage-list">
        <?php foreach ($company_garages as $garage): ?>
            <div class="garage-item">
                <h3>Location: <?= htmlspecialchars($garage['location']); ?></h3>
                <p>Capacity: <?= htmlspecialchars($garage['capacity']); ?> vehicles</p>
                <p>Available Spaces: <?= htmlspecialchars($garage['availableSpaces']); ?></p>
                <p>Price: BDT <?= htmlspecialchars($garage['price_per_day']); ?>/day</p>
                <form method="POST" action="">
                    <input type="hidden" name="garageID" value="<?= $garage['garageID']; ?>">
                    <label for="start_date">Start Date:</label>
                    <input type="datetime-local" name="start_date" required>

                    <label for="end_date">End Date:</label>
                    <input type="datetime-local" name="end_date" required>

                    <button type="submit" name="book_garage">Book Garage</button>
                </form>
                
            </div>
        <?php endforeach; ?>
    </div>
    

    <!-- Display Booked Garages -->
    <div class="booked">
        <h2>Your Booked Garages</h2>
        <ul>
            <?php foreach ($booked_garages as $booking): ?>
                <li>
                    Garage Location: <?= htmlspecialchars($booking['location']); ?>, 
                    Start Date: <?= htmlspecialchars($booking['start_date']); ?>, 
                    End Date: <?= htmlspecialchars($booking['end_date']); ?>, 
                    Total Price: BDT <?= htmlspecialchars($booking['total_price']); ?> 
                    (Status: <?= htmlspecialchars($booking['status']); ?>)
                </li>
            <?php endforeach; ?>
        </ul>
    </div>
</body>
</html>
