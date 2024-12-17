<?php

include 'components/connect.php';

session_start();

if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
} else {
    $user_id = '';
}

// Initialize $message as an array
$message = [];

// Initialize failed login attempts and lockout time in session
if (!isset($_SESSION['failed_attempts'])) {
    $_SESSION['failed_attempts'] = 0;
    $_SESSION['lockout_time'] = 0;
}

$cooldown_period = 30; // Cooldown period in seconds
$current_time = time();

if (isset($_POST['submit'])) {
    // Check if user is in cooldown period
    if ($_SESSION['failed_attempts'] >= 3 && $current_time < $_SESSION['lockout_time']) {
        $remaining_time = $_SESSION['lockout_time'] - $current_time;
        $message[] = "Too many failed attempts. Please wait $remaining_time seconds before trying again.";
    } else {
        $email = filter_var($_POST['email'], FILTER_VALIDATE_EMAIL);
        $pass = filter_var($_POST['pass'], FILTER_SANITIZE_STRING);

        if (!$email) {
            $message[] = 'Invalid email address.';
        } else {
            // Fetch the stored hashed password from the database
            $select_user = $conn->prepare("SELECT * FROM users WHERE email = ?");
            $select_user->execute([$email]);
            $row = $select_user->fetch(PDO::FETCH_ASSOC);

            if ($select_user->rowCount() > 0) {
                // Verify the entered password against the stored bcrypt hash
                if (password_verify($pass, $row['password'])) {
                    $_SESSION['user_id'] = $row['id'];
                    $_SESSION['failed_attempts'] = 0; // Reset failed attempts
                    header('location:home.php');
                    exit; // Stop further execution
                } else {
                    $_SESSION['failed_attempts']++;
                    if ($_SESSION['failed_attempts'] >= 3) {
                        $_SESSION['lockout_time'] = $current_time + $cooldown_period;
                    }
                    $message[] = 'Incorrect username or password!';
                }
            } else {
                $_SESSION['failed_attempts']++;
                if ($_SESSION['failed_attempts'] >= 3) {
                    $_SESSION['lockout_time'] = $current_time + $cooldown_period;
                }
                $message[] = 'Incorrect username or password!';
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
    
    <!-- Font Awesome CDN -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">

    <!-- Custom CSS -->
    <link rel="stylesheet" href="css/style.css">
    <style>
        .countdown-container {
            margin-top: 20px;
            color: #ff0000;
            font-size: 16px;
            text-align: center;
        }
    </style>
</head>
<body>
<?php include 'components/user_header.php'; ?>

<section class="form-container">

    <form action="" method="post">
        <h3>Login Now</h3>
        <?php if (!empty($message) && is_array($message)): ?>
            <?php foreach ($message as $msg): ?>
                <p class="error-msg"><?= htmlspecialchars($msg); ?></p>
            <?php endforeach; ?>
        <?php endif; ?>
        <input type="email" name="email" required placeholder="Enter your email" maxlength="50" class="box" 
               oninput="this.value = this.value.replace(/\s/g, '')" 
               <?= ($_SESSION['failed_attempts'] >= 3 && $current_time < $_SESSION['lockout_time']) ? 'disabled' : ''; ?>>
        <input type="password" name="pass" required placeholder="Enter your password" maxlength="20" class="box" 
               oninput="this.value = this.value.replace(/\s/g, '')" 
               <?= ($_SESSION['failed_attempts'] >= 3 && $current_time < $_SESSION['lockout_time']) ? 'disabled' : ''; ?>>
        <input type="submit" value="Login Now" class="btn" name="submit" 
               <?= ($_SESSION['failed_attempts'] >= 3 && $current_time < $_SESSION['lockout_time']) ? 'disabled' : ''; ?>>
        
        <?php if ($_SESSION['failed_attempts'] >= 3 && $current_time < $_SESSION['lockout_time']): ?>
            <div class="countdown-container" id="countdown">Please wait 30 seconds before trying again.</div>
        <?php endif; ?>

        <p>Don't have an account?</p>
        <a href="user_register.php" class="option-btn">Register Now.</a>
    </form>

</section>

<?php include 'components/footer.php'; ?>

<script>
    // JavaScript Countdown Timer
    <?php if ($_SESSION['failed_attempts'] >= 3 && $current_time < $_SESSION['lockout_time']): ?>
        let countdown = <?= $_SESSION['lockout_time'] - $current_time; ?>;
        const countdownDisplay = document.getElementById('countdown');

        const timer = setInterval(() => {
            if (countdown <= 0) {
                clearInterval(timer);
                countdownDisplay.innerText = "You can now try logging in again.";
                document.querySelectorAll('.box, .btn').forEach(el => el.disabled = false);
            } else {
                countdownDisplay.innerText = `Please wait ${countdown--} seconds before trying again.`;
            }
        }, 1000);
    <?php endif; ?>
</script>

</body>
</html>
