<?php

include 'components/connect.php';

session_start();

if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
} else {
    $user_id = '';
}

if (isset($_POST['submit'])) {

    // Sanitize user inputs
    $name = $_POST['name'];
    $name = filter_var($name, FILTER_SANITIZE_STRING);
    $email = $_POST['email'];
    $email = filter_var($email, FILTER_SANITIZE_STRING);

    // Update user profile information (name, email)
    $update_profile = $conn->prepare("UPDATE `users` SET name = ?, email = ? WHERE id = ?");
    $update_profile->execute([$name, $email, $user_id]);

    $empty_pass = 'da39a3ee5e6b4b0d3255bfef95601890afd80709';
    $prev_pass = $_POST['prev_pass']; // Previously stored password hash in database
    $old_pass = $_POST['old_pass'];  // Old password input by the user
    $old_pass = filter_var($old_pass, FILTER_SANITIZE_STRING);
    $new_pass = $_POST['new_pass'];  // New password input by the user
    $new_pass = filter_var($new_pass, FILTER_SANITIZE_STRING);
    $cpass = $_POST['cpass'];  // Confirm new password input by the user
    $cpass = filter_var($cpass, FILTER_SANITIZE_STRING);

    // Check if old password is entered and matches the current password in the database
    if ($old_pass == $empty_pass) {
        $message[] = 'Please enter your old password!';
    } elseif (!password_verify($old_pass, $prev_pass)) {
        // Verify the old password against the hashed password in the database
        $message[] = 'Old password does not match!';
    } elseif ($new_pass != $cpass) {
        $message[] = 'Confirm password does not match!';
    } else {
        // Check if the new password is strong using regex
        if (!preg_match('/^(?=.*[A-Z])(?=.*[a-z])(?=.*\d)(?=.*[@$!%*?&#])[A-Za-z\d@$!%*?&#]{8,}$/', $new_pass)) {
            $message[] = 'Password must be at least 8 characters long, including one uppercase letter, one lowercase letter, one number, and one special character.';
        } else {
            if ($new_pass != $empty_pass) {
                // Hash the new password with bcrypt
                $hashed_password = password_hash($new_pass, PASSWORD_BCRYPT);

                // Update the password in the database with the bcrypt hash
                $update_admin_pass = $conn->prepare("UPDATE `users` SET password = ? WHERE id = ?");
                $update_admin_pass->execute([$hashed_password, $user_id]);
                $message[] = 'Password updated successfully!';
            } else {
                $message[] = 'Please enter a new password!';
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
    <title>Update Profile</title>

    <!-- Font Awesome CDN Link -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">

    <!-- Custom CSS Link -->
    <link rel="stylesheet" href="css/style.css">

</head>

<body>

    <?php include 'components/user_header.php'; ?>

    <section class="form-container">
        <form action="" method="post">
            <h3>Update Now</h3>

            <input type="hidden" name="prev_pass" value="<?= $fetch_profile["password"]; ?>">

            <!-- User information fields -->
            <input type="text" name="name" required placeholder="Enter your username" maxlength="20" class="box" value="<?= $fetch_profile["name"]; ?>">
            <input type="email" name="email" required placeholder="Enter your email" maxlength="50" class="box" oninput="this.value = this.value.replace(/\s/g, '')" value="<?= $fetch_profile["email"]; ?>">

            <!-- Password fields -->
            <input type="password" name="old_pass" placeholder="Enter your old password" maxlength="255" class="box" oninput="this.value = this.value.replace(/\s/g, '')">
            <input type="password" name="new_pass" placeholder="Enter your new password" maxlength="255" class="box" oninput="this.value = this.value.replace(/\s/g, '')">
            <input type="password" name="cpass" placeholder="Confirm your new password" maxlength="255" class="box" oninput="this.value = this.value.replace(/\s/g, '')">

            <!-- Submit button -->
            <input type="submit" value="Update Now" class="btn" name="submit">
        </form>
    </section>

    <?php include 'components/footer.php'; ?>

    <script src="js/script.js"></script>

</body>

</html>
