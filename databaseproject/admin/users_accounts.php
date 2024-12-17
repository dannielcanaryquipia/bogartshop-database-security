<?php

include '../components/connect.php';

session_start();

$admin_id = $_SESSION['admin_id'];

if (!isset($admin_id)) {
    header('location:admin_login.php');
    exit;
}

// Fetch the admin's name to check for "Default Admin"
$select_admin = $conn->prepare("SELECT * FROM `admins` WHERE id = ?");
$select_admin->execute([$admin_id]);

$fetch_admin = $select_admin->fetch(PDO::FETCH_ASSOC);

$admin_name = $fetch_admin ? $fetch_admin['name'] : 'Default Admin';

// Check if the logged-in admin is the "Default Admin"
$is_default_admin = ($admin_name === 'Default Admin');

if (isset($_GET['delete']) && !$is_default_admin) {
    $delete_id = $_GET['delete'];
    $delete_user = $conn->prepare("DELETE FROM `users` WHERE id = ?");
    $delete_user->execute([$delete_id]);
    $delete_orders = $conn->prepare("DELETE FROM `orders` WHERE user_id = ?");
    $delete_orders->execute([$delete_id]);
    $delete_messages = $conn->prepare("DELETE FROM `messages` WHERE user_id = ?");
    $delete_messages->execute([$delete_id]);
    $delete_cart = $conn->prepare("DELETE FROM `cart` WHERE user_id = ?");
    $delete_cart->execute([$delete_id]);
    $delete_wishlist = $conn->prepare("DELETE FROM `wishlist` WHERE user_id = ?");
    $delete_wishlist->execute([$delete_id]);
    header('location:users_accounts.php');
    exit;
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Users Accounts</title>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
    <link rel="stylesheet" href="../css/admin_style.css">
</head>
<body>

<?php include '../components/admin_header.php'; ?>

<?php if ($is_default_admin): ?>
    <!-- Access Denied Section -->
    <section class="access-denied">
        <h1>Access Denied</h1>
        <p>Default Admin cannot access the Users Accounts page.</p>
    </section>
<?php else: ?>

<!-- User Accounts Section -->
<section class="accounts">

    <h1 class="heading">User Accounts</h1>

    <div class="box-container">

    <?php
        $select_accounts = $conn->prepare("SELECT * FROM `users`");
        $select_accounts->execute();
        if ($select_accounts->rowCount() > 0) {
            while ($fetch_accounts = $select_accounts->fetch(PDO::FETCH_ASSOC)) {   
    ?>
    <div class="box">
        <p> User id : <span><?= $fetch_accounts['id']; ?></span> </p>
        <p> Username : <span><?= $fetch_accounts['name']; ?></span> </p>
        <p> Email : <span><?= $fetch_accounts['email']; ?></span> </p>
        <a href="users_accounts.php?delete=<?= $fetch_accounts['id']; ?>" 
           onclick="return confirm('delete this account? The user\'s related information will also be deleted!')" 
           class="delete-btn">delete</a>
    </div>
    <?php
            }
        } else {
            echo '<p class="empty">No accounts available!</p>';
        }
    ?>

    </div>

</section>

<?php endif; ?>

<script src="../js/admin_script.js"></script>
   
</body>
</html>
