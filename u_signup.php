
<?php

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    require_once("db.php");

    $username = $_POST["username"];
    $email = $_POST["email"];
    $phone = $_POST["phone"];
    $address = $_POST["address"];
    $password = $_POST["password"];
    $role = "user";

    $hash_pw = password_hash($password, PASSWORD_DEFAULT);

    $query = "INSERT INTO user(username, phone, email, address_, password_, role_) VALUES (?, ?, ?, ?, ?, ?)";

    $stmt = $pdo->prepare($query);
    $stmt->execute([$username, $phone, $email, $address, $hash_pw, $role]);

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
    <title>Sign Up</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #000;  /* Black background */
            color: #fff;  /* White text */
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }

        .container {
            background-color: #111;
            padding: 20px;
            border-radius: 8px;
            width: 100%;
            max-width: 350px;
        }

        label, input, button {
            display: block;
            width: 100%;
            margin-bottom: 15px;
        }

        input {
            padding: 10px;
            background-color: #222;
            border: 1px solid #444;
            color: #fff;
        }

        button {
            background-color: #444;
            border: none;
            padding: 10px;
            color: #fff;
            cursor: pointer;
        }

        button:hover {
            background-color: #666;
        }
    </style>
</head>
<body>
<form action="" method="POST">

    <label for="username">Username: </label>
    <input type="text" id="username" name="username" required><br>

    <label for="email">Email: </label>
    <input type="text" id="email" name="email" required><br>

    <label for="phone">Phone: </label>
    <input type="text" id="phone" name="phone" required><br>

    <label for="password">Password: </label>
    <input type="password" id="password" name="password" required><br>

    <label for="address">Address: </label>
    <input type="text" id="address" name="address" required><br>


    <button type="submit">Sign Up</button><br>
    <button onclick="window.location.href='signup.php'">Back</button>

</form>


</body>
</html>





