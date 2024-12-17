<?php

include '../components/connect.php';

session_start();

$admin_id = $_SESSION['admin_id'];

if (!isset($admin_id)) {
   header('location:admin_login.php');
}

// Fetch admin profile data
$fetch_profile_query = $conn->prepare("SELECT * FROM `admins` WHERE id = ?");
$fetch_profile_query->execute([$admin_id]);
$fetch_profile = $fetch_profile_query->fetch(PDO::FETCH_ASSOC);

if (!$fetch_profile) {
   $message[] = 'Profile not found!';
   // Handle the error, e.g., redirect or show an error message
}

if (isset($_POST['submit'])) {

   $name = $_POST['name'];
   $name = filter_var($name, FILTER_SANITIZE_STRING);

   $update_profile_name = $conn->prepare("UPDATE `admins` SET name = ? WHERE id = ?");
   $update_profile_name->execute([$name, $admin_id]);

   $empty_pass = ''; // Placeholder for no password entry
   $prev_pass = $_POST['prev_pass'];
   $old_pass = $_POST['old_pass'];
   $old_pass = filter_var($old_pass, FILTER_SANITIZE_STRING);
   $new_pass = $_POST['new_pass'];
   $new_pass = filter_var($new_pass, FILTER_SANITIZE_STRING);
   $confirm_pass = $_POST['confirm_pass'];
   $confirm_pass = filter_var($confirm_pass, FILTER_SANITIZE_STRING);

   // Password strength check (at least 8 characters, upper/lowercase, number, special character)
   $password_pattern = "/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/";

   if ($old_pass == $empty_pass) {
      $message[] = 'Please enter old password!';
   } elseif (!password_verify($old_pass, $fetch_profile['password'])) {
      // Check if the entered old password matches the hashed one in the database
      $message[] = 'Old password not matched!';
   } elseif ($new_pass != $confirm_pass) {
      $message[] = 'Confirm password not matched!';
   } elseif (!preg_match($password_pattern, $new_pass)) {
      $message[] = 'New password must be at least 8 characters, contain uppercase and lowercase letters, numbers, and special characters!';
   } else {
      if (!empty($new_pass)) {
         // Hash the new password with bcrypt
         $hashed_new_pass = password_hash($new_pass, PASSWORD_BCRYPT);

         // Update the password in the database with the new hashed password
         $update_admin_pass = $conn->prepare("UPDATE `admins` SET password = ? WHERE id = ?");
         $update_admin_pass->execute([$hashed_new_pass, $admin_id]);
         $message[] = 'Password updated successfully!';
      } else {
         $message[] = 'Please enter a new password!';
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

   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
   <link rel="stylesheet" href="../css/admin_style.css">

</head>
<body>

<?php include '../components/admin_header.php'; ?>

<section class="form-container">

   <form action="" method="post">
      <h3>Update Profile</h3>
      <input type="hidden" name="prev_pass" value="<?= isset($fetch_profile['password']) ? $fetch_profile['password'] : ''; ?>">
      <input type="text" name="name" value="<?= isset($fetch_profile['name']) ? $fetch_profile['name'] : ''; ?>" required placeholder="Enter your username" maxlength="20" class="box" oninput="this.value = this.value.replace(/\s/g, '')">
      <input type="password" name="old_pass" placeholder="Enter old password" maxlength="20" class="box" oninput="this.value = this.value.replace(/\s/g, '')">
      <input type="password" name="new_pass" placeholder="Enter new password" maxlength="20" class="box" oninput="this.value = this.value.replace(/\s/g, '')">
      <input type="password" name="confirm_pass" placeholder="Confirm new password" maxlength="20" class="box" oninput="this.value = this.value.replace(/\s/g, '')">
      <input type="submit" value="Update Now" class="btn" name="submit">
   </form>

</section>

<script src="../js/admin_script.js"></script>
   
</body>
</html>
