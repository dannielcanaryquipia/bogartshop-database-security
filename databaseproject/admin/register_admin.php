<?php

include '../components/connect.php';

session_start();

$admin_id = $_SESSION['admin_id'];

if (!isset($admin_id)) {
    header('location:admin_login.php');
}

if (isset($_POST['submit'])) {

    $name = $_POST['name'];
    $name = filter_var($name, FILTER_SANITIZE_STRING);
    $pass = $_POST['pass'];
    $pass = filter_var($pass, FILTER_SANITIZE_STRING);
    $cpass = $_POST['cpass'];
    $cpass = filter_var($cpass, FILTER_SANITIZE_STRING);

    $select_admin = $conn->prepare("SELECT * FROM admins WHERE name = ?");
    $select_admin->execute([$name]);

    if ($select_admin->rowCount() > 0) {
        $message[] = 'Username already exists!';
    } else {
        if ($pass !== $cpass) {
            $message[] = 'Confirm password does not match!';
        } else {
            // Hash the password using bcrypt
            $hashed_password = password_hash($pass, PASSWORD_BCRYPT);

            $insert_admin = $conn->prepare("INSERT INTO `admins`(name, password) VALUES(?, ?)");
            $insert_admin->execute([$name, $hashed_password]);
            $message[] = 'New admin registered successfully!';
        }
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register Admin</title>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">

    <link rel="stylesheet" href="../css/admin_style.css">

    <script>
        // Function to validate the password strength
        function validatePassword() {
            var password = document.getElementById("pass").value;
            var strengthMessage = document.getElementById("strengthMessage");
            var regex = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/;

            if (password.match(regex)) {
                strengthMessage.style.color = "green";
                strengthMessage.textContent = "Password is strong!";
            } else {
                strengthMessage.style.color = "red";
                strengthMessage.textContent = "Password must be at least 8 characters long, and include a mix of uppercase, lowercase, numbers, and special characters.";
            }
        }
    </script>

</head>
<body>

<?php include '../components/admin_header.php'; ?>

<section class="form-container">

    <form action="" method="post">
        <h3>Register Now</h3>
        <input type="text" name="name" required placeholder="Enter your username" maxlength="20" class="box" oninput="this.value = this.value.replace(/\s/g, '')">
        <input type="password" name="pass" id="pass" required placeholder="Enter your password" maxlength="20" class="box" oninput="this.value = this.value.replace(/\s/g, ''); validatePassword()">
        <input type="password" name="cpass" required placeholder="Confirm your password" maxlength="20" class="box" oninput="this.value = this.value.replace(/\s/g, '')">
        <span id="strengthMessage"></span>
        <input type="submit" value="Register Now" class="btn" name="submit">
    </form>

</section>

<script src="../js/admin_script.js"></script>
   
</body>
</html>
