<?php
session_start();
include 'SmartLib.php';

// --- COOKIE LOGIC FOR LOGIN PAGE ---
$rememberedEmail = isset($_COOKIE['remembered_user']) ? $_COOKIE['remembered_user'] : '';

// Check if form was submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
  $email = $_POST['email'] ?? '';
  $password = $_POST['password'] ?? '';
  $selected_role = $_POST['role'] ?? '';

  if (empty($email) || empty($password) || empty($selected_role)) {
    echo "<script>alert('❌ Please fill in all fields!'); window.location.href = 'login.php';</script>";
    exit();
  }

  $stmt = $conn->prepare("SELECT * FROM members WHERE email=? AND role=?");
  $stmt->bind_param("ss", $email, $selected_role);
  $stmt->execute();
  $result = $stmt->get_result();

  if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();

    if (password_verify($password, $row['password']) || $password === $row['password']) {

      $_SESSION['member_id'] = $row['member_id'];
      $_SESSION['name'] = $row['name'];
      $_SESSION['email'] = $row['email'];
      $_SESSION['role'] = $row['role'];

      // --- SETTING COOKIES UPON SUCCESSFUL LOGIN ---

      // 1. Remember the email for 30 days
      setcookie("remembered_user", $email, time() + (86400 * 30), "/");

      // 2. Track the "Last Visit" time
      // We store the OLD cookie value in a session to show it on the dashboard
      if (isset($_COOKIE['current_visit_time'])) {
        $_SESSION['last_visit'] = $_COOKIE['current_visit_time'];
      }
      // Now update the cookie with the NEW current time
      setcookie("current_visit_time", date("M d, Y @ H:i"), time() + (86400 * 30), "/");

      // Redirect
      if (strtolower($row['role']) == 'librarian') {
        header("Location: librarian/librarian-dashboard.php");
      } elseif (strtolower($row['role']) == 'student') {
        header("Location: student/student.php");
      } else {
        header("Location: faculty/faculty.php");
      }
      exit();

    } else {
      echo "<script>alert('❌ Invalid Password!'); window.location.href = 'login.php';</script>";
    }
  } else {
    echo "<script>alert('❌ No user found!'); window.location.href = 'login.php';</script>";
  }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <title>SmartLib - Login</title>
  <link rel="stylesheet" href="assets/styles/theme.css">
  <style>
    /* ... (Your existing CSS stays the same) ... */
    body {
      background: url('fjwu.jpg') no-repeat center center fixed;
      background-size: cover;
      height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      font-family: sans-serif;
      margin: 0;
    }

    .overlay {
      position: absolute;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background: rgba(0, 100, 0, 0.65);
    }

    .login-container {
      position: relative;
      z-index: 2;
      background: rgba(255, 255, 255, 0.95);
      padding: 35px;
      border-radius: 12px;
      width: 360px;
      text-align: center;
    }

    .input-field,
    .role-select {
      width: 100%;
      padding: 12px;
      margin: 10px 0;
      border-radius: 6px;
      border: 1px solid #ccc;
      box-sizing: border-box;
    }

    .btn {
      width: 100%;
      padding: 12px;
      background-color: #4CAF50;
      border: none;
      color: white;
      font-weight: bold;
      cursor: pointer;
      border-radius: 6px;
      margin-top: 10px;
    }
  </style>
</head>

<body>
  <div class="overlay"></div>
  <div class="login-container">
    <h2>SmartLib Login</h2>
    <form action="login.php" method="POST">
      <input type="text" name="email" class="input-field" placeholder="University ID / Email"
        value="<?= htmlspecialchars($rememberedEmail) ?>" required>

      <input type="password" name="password" class="input-field" placeholder="Password" required>

      <select name="role" class="role-select" required>
        <option value="" disabled selected>Select Role</option>
        <option value="student">Student</option>
        <option value="faculty">Faculty</option>
        <option value="librarian">Librarian</option>
      </select>
      <button type="submit" class="btn">Login</button>
    </form>
  </div>
</body>

</html>