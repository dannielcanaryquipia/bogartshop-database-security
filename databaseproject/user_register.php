<?php

include 'components/connect.php';

session_start();

if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
} else {
    $user_id = '';
}

if (isset($_POST['submit'])) {

    $name = $_POST['name'];
    $name = filter_var($name, FILTER_SANITIZE_STRING);
    $email = $_POST['email'];
    $email = filter_var($email, FILTER_SANITIZE_EMAIL);
    $pass = $_POST['pass'];
    $cpass = $_POST['cpass'];

    // Check if email already exists
    $select_user = $conn->prepare("SELECT * FROM users WHERE email = ?");
    $select_user->execute([$email]);
    $row = $select_user->fetch(PDO::FETCH_ASSOC);

    if ($select_user->rowCount() > 0) {
        $message[] = 'Email already exists!';
    } else {
        // Check if passwords match
        if ($pass !== $cpass) {
            $message[] = 'Confirm password does not match!';
        } else {
            // Validate password strength (using regex)
            if (!preg_match("/^(?=.*[A-Za-z])(?=.*\d)(?=.*[!@#$%^&*()_+|~=`{}\[\]:;'<>?,.\/]).{8,20}$/", $pass)) {
                $message[] = 'Password must be 8-20 characters long, include at least one letter, one number, and one special character.';
            } else {
                // Hash the password using bcrypt
                $hashed_pass = password_hash($pass, PASSWORD_DEFAULT);

                // Insert new user into the database
                $insert_user = $conn->prepare("INSERT INTO users(name, email, password) VALUES(?,?,?)");
                $insert_user->execute([$name, $email, $hashed_pass]);
                $message[] = 'Registered successfully! Please login.';
            }
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
    <title>Register</title>

    <!-- Font Awesome CDN Link -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">

    <!-- Custom CSS file link -->
    <link rel="stylesheet" href="css/style.css">

    <script>
        function validatePassword() {
            var pass = document.getElementById('password').value;
            var confirmPass = document.getElementById('confirm_password').value;
            var regex = /^(?=.*[A-Za-z])(?=.*\d)(?=.*[!@#$%^&*()_+|~=`{}\[\]:;'<>?,.\/]).{8,20}$/;

            // Check password strength
            if (!regex.test(pass)) {
                alert('Password must be 8-20 characters long, include at least one letter, one number, and one special character.');
                return false;
            }

            // Check if passwords match
            if (pass !== confirmPass) {
                alert('Passwords do not match!');
                return false;
            }

            return true;
        }
    </script>
</head>

<body>

    <?php include 'components/user_header.php'; ?>

    <section class="form-container">

        <form action="" method="post" onsubmit="return validatePassword()">
            <h3>Register Now</h3>
            <input type="text" name="name" required placeholder="Enter your username" maxlength="20" class="box">
            <input type="email" name="email" required placeholder="Enter your email" maxlength="50" class="box" oninput="this.value = this.value.replace(/\s/g, '')">
            <input type="password" name="pass" id="password" required placeholder="Enter your password" maxlength="20" class="box" oninput="this.value = this.value.replace(/\s/g, '')">
            <input type="password" name="cpass" id="confirm_password" required placeholder="Confirm your password" maxlength="20" class="box" oninput="this.value = this.value.replace(/\s/g, '')">
            <input type="submit" value="Register Now" class="btn" name="submit">
            <p>Already have an account?</p>
            <a href="user_login.php" class="option-btn">Login Now</a>
        </form>

    </section>

    <?php include 'components/footer.php'; ?>

    <script src="js/script.js"></script>

</body>

</html>
