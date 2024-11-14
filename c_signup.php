<?php

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    require_once("db.php");

    $username = $_POST["username"];
    $businessID = $_POST["businessID"];
    $totalDriver = $_POST["totalDriver"];
    $totalVehicle = $_POST["totalVehicle"];
    $password = $_POST["password"];
    $role = "driver";

    $hash_pw = password_hash($password, PASSWORD_DEFAULT);

    $query = "insert into company(username,password_,businessID,totalDriver,totalVehicle) values(?,?,?,?,?);";

    $stmt = $pdo->prepare($query);
    $stmt->execute([$username,$hash_pw,$businessID,$totalDriver,$totalVehicle]);

    $pdo = null;
    header("Location: index.php");
    exit;
} 
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <style>
    body {
        font-family: Arial, sans-serif;
        background-color: #121212; /* Dark background */
        color: #ffffff; /* White text color */
        padding: 20px;
    }

    h1 {
        text-align: center; /* Centered heading */
        color: #ffffff; /* White for heading */
    }

    form {
        background-color: #1e1e1e; /* Dark grey for form */
        padding: 20px;
        border-radius: 10px; /* Rounded corners */
        box-shadow: 0 0 10px rgba(255, 255, 255, 0.1); /* Subtle shadow */
        max-width: 400px; /* Max width of the form */
        margin: 20px auto; /* Center the form */
    }

    label {
        display: block; /* Block layout for labels */
        margin-bottom: 5px; /* Space between label and input */
        color: #ffffff; /* White for labels */
    }

    input[type="text"],
    input[type="number"],
    input[type="password"] {
        width: 100%; /* Full width input */
        padding: 10px; /* Padding inside input */
        margin-bottom: 15px; /* Space below input */
        border: 1px solid #444; /* Dark border */
        border-radius: 5px; /* Rounded corners */
        background-color: #333; /* Dark input background */
        color: #ffffff; /* White text in inputs */
        font-size: 16px; /* Font size for input */
    }

    input[type="text"]:focus,
    input[type="number"]:focus,
    input[type="password"]:focus {
        border-color: #007bff; /* Blue border on focus */
        outline: none; /* Remove default outline */
    }

    button {
        width: 100%; /* Full width button */
        padding: 10px; /* Padding inside button */
        background-color: #007bff; /* Blue background for button */
        border: none; /* Remove border */
        color: white; /* White text color */
        border-radius: 5px; /* Rounded corners */
        font-size: 16px; /* Font size for button */
        cursor: pointer; /* Pointer cursor on hover */
        transition: background-color 0.3s; /* Smooth background change */
    }

    button:hover {
        background-color: #0056b3; /* Darker blue on hover */
    }

    .back-button {
        margin-top: 10px; /* Space above back button */
        background-color: #444; /* Dark grey background for back button */
        color: white; /* White text color */
    }

    .back-button:hover {
        background-color: #555; /* Lighter grey on hover */
    }
</style>


</head>
<body>
    <form action="" method="POST">

    <label for="username">Username: </label>
    <input type="text" id="username" name="username" required><br>

    <label for="businessID">Busines ID: </label>
    <input type="text" id="businessID" name="businessID" required><br>


    <label for="totalVehicle">Total Vehicles: </label>
    <input type="number" id="totalVehicle" name="totalVehicle" required><br>

    <label for="totalDriver">Total Drivers: </label>
    <input type="number" id="totalDriver" name="totalDriver" required><br>

    <label for="businessID">Company ID: </label>
    <input type="text" id="businessID" name="businessID" required><br>


    <label for="password">Password: </label>
    <input type="password" id="password" name="password" required><br>



    <button type="submit">Sign Up</button><br>
    <button onclick="window.location.href='signup.php'">Back</button>

    </form>
    
</body>
</html>