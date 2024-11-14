<?php
session_start();  

require("db.php");  

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $in_username = $_POST["username"];
    $in_password = $_POST["password"];

    if($_POST["role"]=="user"){

        $query = "SELECT password_ FROM user WHERE username = ?";
        $stmt = $pdo->prepare($query);
        $stmt->execute([$in_username]);  

        $pw = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($pw && password_verify($in_password, $pw['password_'])) {
            $_SESSION['username'] = $in_username;
            $_SESSION['role'] = $_POST['role'];

            header("Location: u_dash_og.php");
            exit;
        } else {
            header("Location: index.php");
            exit;
        }
    }   

    if($_POST["role"]=="driver"){

        $query = "SELECT password_ FROM driver WHERE username = ?";
        $stmt = $pdo->prepare($query);
        $stmt->execute([$in_username]);  

        $pw = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($pw && password_verify($in_password, $pw['password_'])) {
            $_SESSION['username'] = $in_username;
            $_SESSION['role'] = $_POST['role'];

            header("Location: d_dash.php");
            exit;
        } else {
            header("Location: index.php");
            exit;
        }

    }

    if($_POST["role"]=="company"){

        // Select businessID and password for the company
        $query = "SELECT businessID, password_ FROM company WHERE username = ?";
        $stmt = $pdo->prepare($query);
        $stmt->execute([$in_username]);  

        $company = $stmt->fetch(PDO::FETCH_ASSOC);

        // Verify password and store businessID in session
        if ($company && password_verify($in_password, $company['password_'])) {
            $_SESSION['username'] = $in_username;
            $_SESSION['businessID'] = $company['businessID'];  // Store businessID in session
            $_SESSION['role'] = $_POST['role'];

            header("Location: c_dash.php");
            exit;
        } else {
            header("Location: index.php");
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
    <title>Login</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            display: flex;
            height: 100vh;
            background-color: #f4f4f4;
        }
        .container {
            display: flex;
            width: 100%;
        }
        .left {
            width: 40%;
            background-color: #333;
            color: white;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .left img {
            max-width: 150px;
            margin-bottom: 30px;
        }
        .left .nav-buttons {
            margin-top: 30px;
        }
        .left .nav-buttons button {
            background-color: #555;
            color: white;
            border: none;
            padding: 10px 20px;
            margin: 5px 0;
            cursor: pointer;
            width: 100%;
            text-align: left;
        }
        .left .nav-buttons button:hover {
            background-color: #777;
        }
        .right {
            width: 60%;
            background-color: white;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .right h2 {
            margin-bottom: 20px;
            color: #333;
        }
        .right .cta {
            margin-bottom: 30px;
        }
        .right .cta button {
            background-color: #333;
            color: white;
            border: none;
            padding: 10px 20px;
            cursor: pointer;
            margin: 10px;
        }
        .right .cta button:hover {
            background-color: #555;
        }
        .popup {
            display: none;
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background-color: white;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.5);
            padding: 20px;
            z-index: 1000;
            width: 300px;
        }
        .popup.active {
            display: block;
        }
        .popup h3 {
            margin-top: 0;
        }
        .popup form {
            display: flex;
            flex-direction: column;
        }
        .popup form label,
        .popup form input,
        .popup form select,
        .popup form button {
            margin: 10px 0;
        }
        .overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            z-index: 500;
        }
        .overlay.active {
            display: block;
        }
    </style>
</head>
<body>

<div class="container">
    <div class="left">
        <img src="bogo.png" alt="Logo">
        <div class="nav-buttons">
            <button onclick="alert('Help Section')">Help</button>
            <button onclick="alert('About Section')">About</button>
            <button onclick="alert('FAQ Section')">FAQ</button>
            <button onclick="alert('Support Us Section')">Support Us</button>
        </div>
    </div>
    <div class="right">
        <h2>Let's have an easy life with us</h2>
        <div class="cta">
            <button onclick="window.location.href='signup.php'">Register here</button>
            <button onclick="openPopup('login')">Log in here</button>
        </div>
    </div>
</div>

<div class="popup" id="popup">
    <h3 id="popupTitle"></h3>
    <form action="" method="POST">
        <label for="username">Username:</label>
        <input type="text" id="username" name="username" required>
        
        <label for="password">Password:</label>
        <input type="password" id="password" name="password" required>
        
        <label for="role">Select your role:</label>
        <select name="role" id="role" required>
            <option value="">Select a Role</option>
            <option value="user">User</option>
            <option value="driver">Driver</option>
            <option value="company">Agency</option>
        </select>

        <button type="submit" id="popupButton"></button>
    </form>
</div>

<div class="overlay" id="overlay" onclick="closePopup()"></div>

<script>
    function openPopup(type) {
        const popup = document.getElementById('popup');
        const overlay = document.getElementById('overlay');
        const popupTitle = document.getElementById('popupTitle');
        const popupButton = document.getElementById('popupButton');
        
        if (type === 'register') {
            window.location.href = 'signup.php'; // Redirect to signup.php
        } else {
            popupTitle.innerText = 'Login';
            popupButton.innerText = 'Login';
            popup.classList.add('active');
            overlay.classList.add('active');
        }
    }

    function closePopup() {
        const popup = document.getElementById('popup');
        const overlay = document.getElementById('overlay');
        
        popup.classList.remove('active');
        overlay.classList.remove('active');
    }
</script>

</body>
</html>
