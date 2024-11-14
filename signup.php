<?php

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $role = $_POST["role"];
    
    if($role=="user")header("Location: u_signup.php");
    if($role=="driver")header("Location: d_signup.php");
    if($role=="company")header("Location: c_signup.php");

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
        max-width: 300px;
        text-align: center;
    }

    select, button {
        width: 100%;
        padding: 10px;
        margin-bottom: 15px;
        background-color: #222; /* Darker gray for inputs */
        border: 1px solid #555; /* Light gray border */
        border-radius: 4px;
        color: #fff; /* White text in inputs and buttons */
    }

    button {
        background-color: #444; /* Medium gray for button */
        border: none;
        cursor: pointer;
    }

    button:hover {
        background-color: #666; /* Lighter gray on hover */
    }

    button[type="button"] {
        background-color: #888; /* Different gray for "Back" */
    }

    button[type="button"]:hover {
        background-color: #aaa; /* Lighter gray on hover for "Back" */
    }

    select {
        background-color: #222;
        border: 1px solid #555;
        color: #fff;
    }
</style>

</head>
<body>
<form action="" method="POST">
<select name="role" id="role" required>
    <option value="">Select a Role</option>
    <option value="user">User</option>
    <option value="driver">Driver</option>
    <option value="company">Company</option>
</select><br>

    <button type="submit">continue</button><br>
    
    <button onclick="window.location.href='index.php'">Back</button>

</form>

</body>
</html>