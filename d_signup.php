<?php

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    require_once("db.php");

    $username = $_POST["username"];
    $lisenceNo = $_POST["lisenceNo"];
    $lisenceLev = $_POST["lisenceLev"];
    $phone = $_POST["phone"];
    $address = $_POST["address"];
    $accident_history = $_POST["accident_history"];
    $businessID = $_POST["businessID"]; // Use businessID
    $password = $_POST["password"];
    $role = "driver";

    // Hash the password
    $hash_pw = password_hash($password, PASSWORD_DEFAULT);

    // Updated query to insert into the driver table
    $query = "INSERT INTO driver(username, password_, lisenceNo, lisenceLev, phone, address_, accident_history, businessID) VALUES (?, ?, ?, ?, ?, ?, ?, ?);";

    // Prepare and execute the statement
    $stmt = $pdo->prepare($query);
    $stmt->execute([$username, $hash_pw, $lisenceNo, $lisenceLev, $phone, $address, $accident_history, $businessID]);

    // Close the connection
    $pdo = null;
    
    // Redirect to the index page
    header("Location: index.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #000; /* Black background */
            color: #fff; /* White text */
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }

        form {
            background-color: #111; /* Dark gray form background */
            padding: 20px;
            border-radius: 8px;
            width: 100%;
            max-width: 400px;
        }

        label {
            display: block;
            margin-bottom: 8px;
            color: #ccc; /* Lighter text for labels */
        }

        input {
            width: 100%;
            padding: 10px;
            margin-bottom: 15px;
            background-color: #222; /* Darker gray input background */
            border: 1px solid #555; /* Light gray border */
            border-radius: 4px;
            color: #fff; /* White text in inputs */
        }

        button {
            width: 100%;
            padding: 10px;
            background-color: #444; /* Medium gray for button */
            border: none;
            color: #fff; /* White button text */
            border-radius: 4px;
            cursor: pointer;
        }

        button:hover {
            background-color: #666; /* Lighter gray on hover */
        }

        button[type="button"] {
            background-color: #888; /* Different button for "Back" */
        }

        button[type="button"]:hover {
            background-color: #aaa; /* Lighter hover for "Back" */
        }
    </style>
</head>
<body>
<form action="" method="POST">

    <label for="username">Username: </label>
    <input type="text" id="username" name="username" required><br>

    <label for="lisenceNo">Lisence No: </label>
    <input type="text" id="lisenceNo" name="lisenceNo" required><br>

    <label for="lisenceLev">Lisence Level: </label>
    <input type="text" id="lisenceLev" name="lisenceLev" required><br>

    <label for="phone">Phone no: </label>
    <input type="text" id="phone" name="phone" required><br>

    <label for="address">Address: </label>
    <input type="text" id="address" name="address" required><br>

    <label for="accident_history">No of previous accidents: </label>
    <input type="text" id="accident_history" name="accident_history" required><br>

    <label for="businessID">Business ID: </label>
    <input type="text" id="businessID" name="businessID" required><br> <!-- Updated input field -->

    <label for="password">Password: </label>
    <input type="password" id="password" name="password" required><br>

    <button type="submit">Sign Up</button><br>
    <button type="button" onclick="window.location.href='signup.php'">Back</button>

</form>

</body>
</html>
