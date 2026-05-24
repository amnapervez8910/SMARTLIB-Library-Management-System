<?php
session_start();
if (!isset($_SESSION['member_id'])) {
  header("Location: ../login.html");
  exit();
}
$member_id = $_SESSION['member_id'];
$conn = new mysqli("localhost", "root", "", "smartlib");
if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}
$message = "";
$sql = "SELECT * FROM members WHERE member_id = '$member_id'";
$result = $conn->query($sql);
$user = $result->fetch_assoc();
if (!$user) {
  echo "Error: User not found.";
  exit();
}
if (isset($_POST['save_changes'])) {
  $name = $conn->real_escape_string($_POST['name']);
  $email = $conn->real_escape_string($_POST['email']);
  $phone = $conn->real_escape_string($_POST['phone']);
  $address = $conn->real_escape_string($_POST['address']);
  $password = $conn->real_escape_string($_POST['password']);
  $update = "UPDATE members SET name='$name', email='$email', phone='$phone', address='$address', password='$password' WHERE member_id='$member_id'";
  if ($conn->query($update) === TRUE) {
    $message = "<p style='color:green; font-weight:bold;'>Profile updated successfully.</p>";
    $result = $conn->query($sql);
    $user = $result->fetch_assoc();
  } else {
    $message = "<p style='color:red; font-weight:bold;'>Error updating profile.</p>";
  }
}
if (isset($_POST['delete_account'])) {
  $conn->query("DELETE FROM issued_books WHERE member_id='$member_id'");
  $conn->query("DELETE FROM requests WHERE member_id='$member_id'");
  $del = "DELETE FROM members WHERE member_id='$member_id'";
  if ($conn->query($del) === TRUE) {
    session_destroy();
    header("Location: ../login.html");
    exit();
  } else {
    $message = "<p style='color:red; font-weight:bold;'>Error deleting account: " . $conn->error . "</p>";
  }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Profile | Smart Library</title>
  <link rel="stylesheet" href="../assets/styles/theme.css" />
  <link rel="stylesheet" href="../assets/styles/layout.css" />
  <link rel="stylesheet" href="../assets/styles/student.css" />
  <style>
    .profile-container {
      background: #fff;
      padding: 25px;
      border-radius: 12px;
      box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
      margin-bottom: 30px;
    }

    .profile-container h2 {
      color: #064e3b;
      margin-bottom: 15px;
    }

    .profile-container label {
      margin-top: 10px;
      font-weight: 600;
      display: block;
    }

    .profile-container input,
    .profile-container textarea {
      width: 100%;
      padding: 12px;
      margin-top: 6px;
      border-radius: 8px;
      border: 1.5px solid #ccc;
      font-size: 14px;
    }

    /* Updated Button Logic */
    .profile-container button,
    .delete-btn {
      padding: 12px 20px;
      border: none;
      border-radius: 6px;
      font-size: 16px;
      cursor: pointer;
      width: 160px;
      text-align: center;
      transition: background 0.3s;
    }

    .profile-container button {
      background: #3a5a40;
      color: #fff;
      margin-top: 20px;
    }

    .delete-btn {
      background: #b91c1c;
      color: #fff;
      margin-top: 15px;
    }

    .delete-btn:hover {
      background: #7f1d1d;
    }

    .account-section {
      background: #fff;
      padding: 20px;
      border-radius: 12px;
      box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
      margin-top: 30px;
    }
  </style>
</head>

<body>
  <?php include '../partials/sidebar.php'; ?>
  <div class="main-content">
    <header>
      <h1>My Profile</h1>
      <p>Manage your account information</p>
    </header>
    <?= $message ?>
    <section class="profile-container">
      <h2>Personal Information</h2>
      <form method="POST">
        <label>Full Name</label>
        <input type="text" name="name" value="<?= htmlspecialchars($user['name']) ?>" required>
        <label>Email Address</label>
        <input type="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" required>
        <label>Phone Number</label>
        <input type="text" name="phone" value="<?= htmlspecialchars($user['phone']) ?>">
        <label>Address</label>
        <textarea name="address" rows="3"><?= htmlspecialchars($user['address']) ?></textarea>
        <label>Password</label>
        <input type="text" name="password" value="<?= htmlspecialchars($user['password']) ?>" required>
        <button type="submit" name="save_changes">Save Changes</button>
      </form>
    </section>
    <section class="account-section">
      <h3>Account Settings</h3>
      <p>Want to delete your account permanently?</p>
      <form method="POST">
        <input type="hidden" name="delete_account" value="1">
        <button type="submit" class="delete-btn"
          onclick="return confirm('Are you sure you want to delete your account? This action cannot be undone.')">Delete
          Account</button>
      </form>
    </section>
  </div>
</body>

</html>