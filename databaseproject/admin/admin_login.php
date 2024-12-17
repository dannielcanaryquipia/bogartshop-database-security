<?php
include '../components/connect.php';

session_start();

if (!isset($_SESSION['attempts'])) {
    $_SESSION['attempts'] = 0; // Initialize login attempts
    $_SESSION['timeout'] = 0; // Initialize timeout
}

// Default credentials
$default_username = 'admin';
$default_password = '111';
$default_password_hashed = password_hash($default_password, PASSWORD_BCRYPT); // Store securely



if ($_SESSION['timeout'] > time()) {
    $remaining_time = $_SESSION['timeout'] - time();
    $message = ['Too many failed attempts. Please wait ' . $remaining_time . ' seconds.'];
} else {
    if (isset($_POST['submit'])) {
        $name = $_POST['name'];
        $name = filter_var($name, FILTER_SANITIZE_STRING);
        $pass = $_POST['pass']; // No hashing here, as bcrypt comparison happens during validation

        $is_valid_login = false;

        // Check against database first
        $select_admin = $conn->prepare("SELECT * FROM admins WHERE name = ?");
        $select_admin->execute([$name]);
        $row = $select_admin->fetch(PDO::FETCH_ASSOC);

        if ($row && password_verify($pass, $row['password'])) {
            // Successful login from database
            $_SESSION['admin_id'] = $row['id'];
            $_SESSION['attempts'] = 0; // Reset attempts on successful login
            $is_valid_login = true;
        }

        // Check against hardcoded default credentials if database check fails
        if (!$is_valid_login && $name === $default_username && password_verify($pass, $default_password_hashed)) {
            $_SESSION['admin_id'] = 'default_admin'; // Assign a default admin ID
            $_SESSION['attempts'] = 0; // Reset attempts on successful login
            $is_valid_login = true;
        }

        if ($is_valid_login) {
            header('location:dashboard.php');
        } else {
            // Failed login
            $_SESSION['attempts']++;
            if ($_SESSION['attempts'] >= 3) {
                $_SESSION['timeout'] = time() + 30; // Set a 30-second timeout
                $message = ['Too many failed attempts. Please wait 30 seconds.'];
            } else {
                $message = ['Incorrect username or password!'];
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
    <title>Login</title>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
    <link rel="stylesheet" href="../css/admin_style.css">
    <style>
        #countdown {
            text-align: center;
            color: red;
            font-size: 14px;
            margin-top: 5px;
        }
    </style>

    <script>
        // Function to start countdown
        function startCountdown(remaining) {
            const countdownElement = document.getElementById('countdown');
            const formElement = document.getElementById('login-form');

            if (remaining > 0) {
                formElement.style.display = 'none'; // Hide form during countdown
                countdownElement.innerText = `Please wait ${remaining} seconds...`;
                setTimeout(() => startCountdown(remaining - 1), 1000);
            } else {
                formElement.style.display = 'block'; // Show form after countdown
                countdownElement.innerText = '';
            }
        }

        // Check if timeout is set
        <?php if ($_SESSION['timeout'] > time()): ?>
        window.onload = function() {
            const remaining = <?php echo $_SESSION['timeout'] - time(); ?>;
            startCountdown(remaining);
        };
        <?php endif; ?>
    </script>
</head>
<body>
    <?php
    if (isset($message)) {
        foreach ($message as $message) {
            echo '
            <div class="message">
                <span>' . $message . '</span>
                <i class="fas fa-times" onclick="this.parentElement.remove();"></i>
            </div>
            ';
        }
    }
    ?>

    <div id="countdown"></div>

    <section class="form-container">
        <form id="login-form" action="" method="post" style="display: <?php echo ($_SESSION['timeout'] > time()) ? 'none' : 'block'; ?>;">
            <h3>Login now</h3>
            <p>Default username = <span>admin</span> & password = <span>111</span></p>
            <input type="text" name="name" required placeholder="enter your username" maxlength="20" class="box" oninput="this.value = this.value.replace(/\s/g, '')">
            <input type="password" name="pass" required placeholder="enter your password" maxlength="20" class="box" oninput="this.value = this.value.replace(/\s/g, '')">
            <input type="submit" value="login now" class="btn" name="submit">
        </form>
    </section>
</body>
</html>
