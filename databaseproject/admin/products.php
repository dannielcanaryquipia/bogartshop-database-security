<?php

include '../components/connect.php';

session_start();

$admin_id = $_SESSION['admin_id'];

if (!isset($admin_id)) {
    header('location:admin_login.php');
    exit;
}

// Fetch the profile data for the logged-in admin
$select_profile = $conn->prepare("SELECT * FROM admins WHERE id = ?");
$select_profile->execute([$admin_id]);

$fetch_profile = $select_profile->fetch(PDO::FETCH_ASSOC);

$admin_name = $fetch_profile ? $fetch_profile['name'] : 'Default Admin';

// Check if the current admin is the default admin
$is_default_admin = ($admin_name === 'Default Admin');

// If the default admin is logged in, block access to the product page
if ($is_default_admin) {
    $block_message = "Access Denied. Default Admin cannot access the Product page.";
}

// Handle the form submission for adding a product
if (isset($_POST['add_product'])) {
    $name = $_POST['name'];
    $price = $_POST['price'];
    $details = $_POST['details'];

    // Handle file uploads
    $image_01 = $_FILES['image_01']['name'];
    $image_02 = $_FILES['image_02']['name'];
    $image_03 = $_FILES['image_03']['name'];

    $image_01_tmp_name = $_FILES['image_01']['tmp_name'];
    $image_02_tmp_name = $_FILES['image_02']['tmp_name'];
    $image_03_tmp_name = $_FILES['image_03']['tmp_name'];

    $image_folder = '../uploaded_img/';

    // Move uploaded files to the server
    move_uploaded_file($image_01_tmp_name, $image_folder . $image_01);
    move_uploaded_file($image_02_tmp_name, $image_folder . $image_02);
    move_uploaded_file($image_03_tmp_name, $image_folder . $image_03);

    // Insert product into the database
    $insert_product = $conn->prepare("INSERT INTO products (name, price, details, image_01, image_02, image_03) VALUES (?, ?, ?, ?, ?, ?)");
    $insert_product->execute([$name, $price, $details, $image_01, $image_02, $image_03]);

    // Confirmation or error handling
    if ($insert_product) {
      $message[] = 'Product added successfully!';
    } else {
        $error_message = "Failed to add the product.";
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Products</title>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
    <link rel="stylesheet" href="../css/admin_style.css">

</head>
<body>

<?php include '../components/admin_header.php'; ?>

<?php if ($is_default_admin): ?>
    <section class="access-denied">
        <h1>Access Denied</h1>
        <p><?= $block_message; ?></p>
    </section>
<?php else: ?>

<section class="add-products">

   <h1 class="heading">Add Product</h1>

   <?php if (isset($success_message)): ?>
       <p class="success"><?= $success_message; ?></p>
   <?php endif; ?>

   <?php if (isset($error_message)): ?>
       <p class="error"><?= $error_message; ?></p>
   <?php endif; ?>

   <form action="" method="post" enctype="multipart/form-data">
      <div class="flex">
         <div class="inputBox">
            <span>Product Name (required)</span>
            <input type="text" class="box" required maxlength="100" placeholder="enter product name" name="name">
         </div>
         <div class="inputBox">
            <span>Product Price (required)</span>
            <input type="number" min="0" class="box" required max="9999999999" placeholder="enter product price" onkeypress="if(this.value.length == 100) return false;" name="price">
         </div>
        <div class="inputBox">
            <span>Image 01 (required)</span>
            <input type="file" name="image_01" accept="image/jpg, image/jpeg, image/png, image/webp" class="box" required>
        </div>
        <div class="inputBox">
            <span>Image 02 (required)</span>
            <input type="file" name="image_02" accept="image/jpg, image/jpeg, image/png, image/webp" class="box" required>
        </div>
        <div class="inputBox">
            <span>Image 03 (required)</span>
            <input type="file" name="image_03" accept="image/jpg, image/jpeg, image/png, image/webp" class="box" required>
        </div>
         <div class="inputBox">
            <span>Product description (required)</span>
            <textarea name="details" placeholder="enter product details" class="box" required maxlength="500" cols="30" rows="10"></textarea>
         </div>
      </div>
      
      <input type="submit" value="add product" class="btn" name="add_product">
   </form>

</section>

<section class="show-products">

   <h1 class="heading">Products Added</h1>

   <div class="box-container">

   <?php
      $select_products = $conn->prepare("SELECT * FROM products");
      $select_products->execute();
      if ($select_products->rowCount() > 0) {
         while ($fetch_products = $select_products->fetch(PDO::FETCH_ASSOC)) { 
   ?>
   <div class="box">
      <img src="../uploaded_img/<?= $fetch_products['image_01']; ?>" alt="">
      <div class="name"><?= $fetch_products['name']; ?></div>
      <div class="price"><span><?= $fetch_products['price']; ?></span></div>
      <div class="details"><span><?= $fetch_products['details']; ?></span></div>
      <div class="flex-btn">
         <a href="update_product.php?update=<?= $fetch_products['id']; ?>" class="option-btn">update</a>
         <a href="products.php?delete=<?= $fetch_products['id']; ?>" class="delete-btn" onclick="return confirm('delete this product?');">delete</a>
      </div>
   </div>
   <?php
         }
      } else {
         echo '<p class="empty">no products added yet!</p>';
      }
   ?>
   
   </div>

</section>

<?php endif; ?>

<script src="../js/admin_script.js"></script>
   
</body>
</html>
