<?php
session_start();
require("db.php");  // Include the database connection

// Fetch companies for dropdown
$company_query = "SELECT * FROM company";
$company_stmt = $pdo->prepare($company_query);
$company_stmt->execute();
$companies = $company_stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch available drivers
$driver_query = "SELECT driver.username, driver.lisenceNo, driver.rating, company.username AS company_username 
                 FROM driver 
                 JOIN company ON driver.businessID = company.businessID
                 WHERE driver.status_ = 'available' and driver.businessID = company.businessID";
$driver_stmt = $pdo->prepare($driver_query);
$driver_stmt->execute();
$drivers = $driver_stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch locations for dropdown
$location_query = "SELECT location_ FROM distances";
$location_stmt = $pdo->prepare($location_query);
$location_stmt->execute();
$locations = $location_stmt->fetchAll(PDO::FETCH_COLUMN);

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $role = $_POST["action"];

    // Handle hiring a driver
    if ($role == "hire_driver") {
        $driver_username = $_POST['driver'];
        $companyID = $_POST['company'];
        $hire_days = $_POST['hire_days'];
        $start_time = $_POST['start_time'];

        // Calculate the end time based on the hire duration
        $start_timestamp = strtotime($start_time);
        $end_timestamp = $start_timestamp + ($hire_days * 86400); // Convert days to seconds
        $end_time = date('Y-m-d H:i:s', $end_timestamp);

        // Check driver availability
        $availability_query = "
            SELECT * FROM driver_availability 
            WHERE driverUsername = ? 
            AND (
                (booked_from <= ? AND booked_to >= ?)  -- Overlaps with the start
                OR 
                (booked_from <= ? AND booked_to >= ?)  -- Overlaps with the end
                OR 
                (booked_from >= ? AND booked_to <= ?)  -- Enclosed within the trip
            )
        ";
        $availability_stmt = $pdo->prepare($availability_query);
        $availability_stmt->execute([$driver_username, $start_time, $start_time, $end_time, $end_time, $start_time, $end_time]);
        $availability_result = $availability_stmt->fetch(PDO::FETCH_ASSOC);

        if ($availability_result) {
            echo "Driver is not available.";
        } else {
            // Insert the driver hire details into availability
            $insert_hire_query = "INSERT INTO driver_availability (driverUsername, booked_from, booked_to) VALUES (?, ?, ?)";
            $insert_hire_stmt = $pdo->prepare($insert_hire_query);
            $insert_hire_stmt->execute([$driver_username, $start_time, $end_time]);

            echo "Driver hired successfully!";
        }
    }
    if($role == "rent_car"){
        header("Location: rent_car.php?");
    }

    // Handle trip booking
    if ($role == "book_trip") {
        $driver_username = $_POST["driver"];
        $location = $_POST["location"];
        $destination = $_POST["destination"];
        $start_time = $_POST["start_time"];

        // Generate random distance between 20 and 100
        $distance = (int)rand(20, 100); // Random distance between 20 and 100
        $price_per_km = 5; // Price per kilometer
        $trip_price = $distance * $price_per_km;

        // Calculate end time (assuming an average speed of 30 km/h)
        $average_speed = 30; // in km/h
        $duration_in_hours = $distance / $average_speed;
        $start_timestamp = strtotime($start_time);
        $end_timestamp = $start_timestamp + ($duration_in_hours * 3600);
        $end_time = date('Y-m-d H:i:s', $end_timestamp);

        // Check driver availability
        $availability_query = "
            SELECT * FROM driver_availability 
            WHERE driverUsername = ? 
            AND (
                (booked_from <= ? AND booked_to >= ?)  -- Overlaps with the start
                OR 
                (booked_from <= ? AND booked_to >= ?)  -- Overlaps with the end
                OR 
                (booked_from >= ? AND booked_to <= ?)  -- Enclosed within the trip
            )
        ";
        $availability_stmt = $pdo->prepare($availability_query);
        $availability_stmt->execute([$driver_username, $start_time, $start_time, $end_time, $end_time, $start_time, $end_time]);
        $availability_result = $availability_stmt->fetch(PDO::FETCH_ASSOC);

        if ($availability_result) {
            $error_message = "The selected driver is not available during the requested time.";
        } else {
            // Insert the trip into the database
            $insert_query = "
                INSERT INTO trip (user, driver, companyID, location, destination, distance, price, start_time, end_time, businessID) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ";
            $insert_stmt = $pdo->prepare($insert_query);
            $insert_stmt->execute([
                $_SESSION['username'],
                $driver_username,
                $_POST['company'],
                $location,
                $destination,
                $distance,
                $trip_price,
                $start_time,
                $end_time,
                $_SESSION['businessID']
            ]);

            // Insert into driver availability
            $insert_availability_query = "
                INSERT INTO driver_availability (driverUsername, booked_from, booked_to) 
                VALUES (?, ?, ?)
            ";
            $insert_availability_stmt = $pdo->prepare($insert_availability_query);
            $insert_availability_stmt->execute([$driver_username, $start_time, $end_time]);

            // Redirect or confirmation
            $data = [
                'driver' => $driver_username,
                'location' => $location,
                'destination' => $destination,
                'distance' => $distance,
                'trip_price' => $trip_price,
                'start_time' => $start_time,
                'end_time' => $end_time
            ];
            header("Location: trip.php?" . http_build_query($data));
            exit;
        }
    }
}
?>



<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Dashboard</title>
    <link rel="stylesheet" href="styles.css"> <!-- Link to the CSS file -->
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f4f4;
        }

        .container {
            max-width: 800px; /* Set a maximum width for the container */
            margin: 20px auto; /* Center the container and add some vertical spacing */
            padding: 20px; /* Add padding for spacing inside the container */
            background: white;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        h1 {
            text-align: center;
            color: #333;
        }

        h2 {
            color: #555;
            margin-bottom: 10px;
        }

        label {
            display: block;
            margin: 10px 0 5px;
            font-weight: bold;
        }

        select,input[type="text"], input[type="datetime-local"], button {
            width: 100%;
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }

        button {
            background: #5cb85c;
            color: white;
            border: none;
            cursor: pointer;
            transition: background 0.3s;
        }

        button:hover {
            background: #4cae4c;
        }

        .result {
            background: #e9ecef;
            padding: 10px;
            border-radius: 5px;
            margin-top: 20px;
        }

        .error-message {
            color: red;
            margin-top: 10px;
        }

        .action-buttons {
            display: flex;
            justify-content: space-between;
        }

        .action-buttons button {
            flex: 1;
            margin-right: 10px;
        }

        .action-buttons button:last-child {
            margin-right: 0;
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
    </style>
</head>
<body>

<div class="container"> <!-- Add the container class here -->
    <h1>Bookings</h1>
    <h1><?php echo "Hello " . $_SESSION['username']; ?></h1>
    
    <form action="" method="POST" id="tripForm">
        <h2>Select Driver and Book Trip</h2>
        <input type="hidden" name="action" value="book_trip">

        <label for="company">Choose a Company:</label>
        <select name="company" id="company" required>
            <?php foreach ($companies as $company): ?>
                <option value="<?php echo $company['businessID']; ?>"><?php echo $company['username']; ?></option>
            <?php endforeach; ?>
        </select>

        <!-- <label for="location">Location:</label>
        <select name="location" id="location" required>
            <option value="">Select Location</option>
            <?php foreach ($locations as $loc): ?>
                <option value="<?php echo $loc; ?>"><?php echo $loc; ?></option>
            <?php endforeach; ?>
        </select>

        <label for="destination">Destination:</label>
        <select name="destination" id="destination" required>
            <option value="">Select Destination</option>
            <?php foreach ($locations as $loc): ?>
                <option value="<?php echo $loc; ?>"><?php echo $loc; ?></option>
            <?php endforeach; ?>
        </select> -->




        <label for="location">Location:</label>
        <!-- <select name="location" id="location" required>
            <option value="">Select Location</option>
            <?php foreach ($locations as $loc): ?>
                <option value="<?php echo $loc; ?>"><?php echo $loc; ?></option>
            <?php endforeach; ?>
        </select> -->

        <input type="text" id="location" name="location" required><br>

         <label for="destination">Destination:</label>
        <!--<select name="destination" id="destination" required>
            <option value="">Select Destination</option>
            <?php foreach ($locations as $loc): ?>
                <option value="<?php echo $loc; ?>"><?php echo $loc; ?></option>
            <?php endforeach; ?>
        </select> -->

        <input type="text" id="destination" name="destination" required><br>


        
     
        <label for="start_time">Trip Start Time:</label>
        <input type="datetime-local" name="start_time" id="start_time" required>

        <label for="driver">Choose a Driver:</label>
        <select name="driver" id="driver" required>
            <option value="">Select Driver</option>
            <?php foreach ($drivers as $driver): ?>
                <option value="<?php echo $driver['username']; ?>">
                    <?php echo $driver['username']; ?> (Completed trips: <?php echo $driver['rating']; ?>)
                </option>
            <?php endforeach; ?>
        </select>


        <button type="submit">Book Trip</button>
    </form>

    <?php if (isset($error_message)): ?>
        <div class="error-message"><?php echo $error_message; ?></div>
    <?php endif; ?>

    <!-- Button to redirect to garage space sharing page -->
    <button class="garage-btn" onclick="window.location.href='garage.php';">Go to Garage Space Sharing</button>

</div>
<div class="container">

    <!-- Form for hiring only a driver -->
    <form action="" method="POST" id="hireDriverForm">
        <h2>Hire a Driver for Specific Days</h2>
        <input type="hidden" name="action" value="hire_driver">

        <label for="company">Choose a Company:</label>
        <select name="company" id="company" required>
            <?php foreach ($companies as $company): ?>
                <option value="<?php echo $company['businessID']; ?>"><?php echo $company['username']; ?></option>
            <?php endforeach; ?>
        </select>

        <label for="driver">Choose an Available Driver:</label>
        <select name="driver" id="driver" required>
            <option value="">Select Driver</option>
            <?php foreach ($drivers as $driver): ?>
                <option value="<?php echo $driver['username']; ?>">
                    <?php echo $driver['username']; ?> (Rating: <?php echo $driver['rating']; ?>)
                </option>
            <?php endforeach; ?>
        </select>

        <label for="hire_days">Choose Duration:</label>
        <select name="hire_days" id="hire_days" required>
            <option value="0.5">Half Day</option>
            <option value="1">1 Day</option>
            <option value="2">2 Days</option>
            <option value="3">3 Days</option>
            <option value="4">4 Days</option>
        </select>

        <label for="start_time">Start Date and Time:</label>
        <input type="datetime-local" name="start_time" id="start_time" required>

        <button type="submit">Hire Driver</button>
    </form>


    <!-- Form for booking a car without a driver -->
    <form action="" method="POST" id="rentCarForm">
        <h2>Rent a Car (No Driver)</h2>
        <input type="hidden" name="action" value="rent_car">
        
        <label for="company">Choose a Company:</label>
        <select name="company" id="company" required>
            <?php foreach ($companies as $company): ?>
                <option value="<?php echo $company['businessID']; ?>"><?php echo $company['username']; ?></option>
            <?php endforeach; ?>
        </select>

        <label for="car">Choose an Available Car:</label>
        <select name="car" id="car" required>
            <option value="">Select Car</option>
            <?php
            // Fetch available cars
            $car_query = "SELECT * FROM car WHERE status = 'available'";
            $car_stmt = $pdo->prepare($car_query);
            $car_stmt->execute();
            $cars = $car_stmt->fetchAll(PDO::FETCH_ASSOC);
            foreach ($cars as $car): ?>
                <option value="<?php echo $car['carID']; ?>"><?php echo $car['model'] . " - " . $car['licensePlate']; ?></option>
            <?php endforeach; ?>
        </select>

        <button type="submit">Rent Car</button>
    </form>

    <!-- Existing booking trip form remains unchanged -->
    <!-- Form for booking trip remains here -->
    <div class="action-buttons">
        <button onclick="window.location.href='d_signup.php';">Signup as a Driver</button>
        <button onclick="window.location.href='logout.php';" class="logout-button">Logout</button>
        <button onclick="window.location.href='u_dash_og.php'" class="dash-button">back to dash</button>
    </div>

</div>


<script>
    const distances = <?php echo json_encode(array_column($distances, 'distances', 'location_')); ?>;

    function calculateTrip() {
        const location = document.getElementById("location").value;
        const destination = document.getElementById("destination").value;

        if (location && destination) {
            const distance = distances[location];
            const pricePerKm = 5; // Price per kilometer

            // Check if distance is available
            if (distance) {
                const tripPrice = distance * pricePerKm;

                document.getElementById("tripDistance").innerText = `Distance: ${distance} km`;
                document.getElementById("tripPrice").innerText = `Trip Price: $${tripPrice}`;
                document.getElementById("results").style.display = "block";
            } else {
                document.getElementById("tripDistance").innerText = "Distance not available.";
                document.getElementById("tripPrice").innerText = "";
            }
        } else {
            document.getElementById("results").style.display = "none";
        }
    }

    document.getElementById("location").addEventListener("change", calculateTrip);
    document.getElementById("destination").addEventListener("change", calculateTrip);
</script>

</body>
</html>
