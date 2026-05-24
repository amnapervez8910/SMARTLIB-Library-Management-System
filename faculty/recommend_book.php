<?php
// ===============================
// SESSION CHECK
// ===============================
session_start();
if (!isset($_SESSION['member_id']) || $_SESSION['role'] !== 'faculty') {
  header("Location: ../login.html");
  exit();
}
$faculty_id = $_SESSION['member_id'];

// ===============================
// DB CONNECTION
// ===============================
include '../SmartLib.php';
if (!$conn) {
  die("❌ Database connection failed");
}

// ===============================
// HANDLE FORM SUBMISSION
// ===============================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $title = trim($_POST['title'] ?? '');
  $author = trim($_POST['author'] ?? '');
  $reason = trim($_POST['reason'] ?? '');
  $priority = "Normal";

  if ($title && $author && $reason) {
    $stmt = $conn->prepare(
      "INSERT INTO book_recommendations 
             (faculty_id, book_title, author, priority, comments, status, created_at) 
             VALUES (?, ?, ?, ?, ?, 'pending', NOW())"
    );
    $stmt->bind_param("issss", $faculty_id, $title, $author, $priority, $reason);

    if ($stmt->execute()) {
      $success = "✅ Recommendation submitted successfully!";
    } else {
      $error = "❌ Failed to submit recommendation!";
    }
    $stmt->close();
  } else {
    $error = "❌ Please fill all fields.";
  }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <title>Recommended Books</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">

  <style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
      font-family: "Segoe UI", sans-serif;
    }

    body {
      display: flex;
      background: #f9fafb;
      color: #333;
    }

    .sidebar {
      width: 250px;
      height: 100vh;
      background: #061d17;
      color: #fff;
      padding: 20px;
      position: fixed;
      display: flex;
      flex-direction: column;
      justify-content: space-between;
      border-radius: 0 12px 12px 0;
    }

    .sidebar h2 {
      text-align: center;
      margin-bottom: 25px;
    }

    .sidebar ul {
      list-style: none;
    }

    .sidebar ul li {
      margin-bottom: 10px;
    }

    .sidebar ul li a {
      display: block;
      padding: 12px;
      color: #fff;
      text-decoration: none;
      border-radius: 8px;
    }

    .sidebar ul li a:hover,
    .active {
      background: #3a5a40;
    }

    .logout-btn {
      width: 80%;
      margin: 25px auto;
      padding: 10px;
      background: #b5a820;
      color: #fff;
      border: none;
      border-radius: 8px;
      cursor: pointer;
    }

    .logout-btn:hover {
      background: #7c1111;
    }

    .main-content {
      margin-left: 250px;
      padding: 20px;
      width: calc(100% - 250px);
    }

    header h1 {
      color: #064e3b;
      font-size: 26px;
      margin-bottom: 5px;
    }

    header p {
      margin-bottom: 20px;
    }

    .card {
      background: #fff;
      padding: 25px;
      border-radius: 12px;
      box-shadow: 0 4px 6px rgba(0, 0, 0, .1);
      margin-bottom: 30px;
    }

    label {
      display: block;
      margin-bottom: 8px;
      font-weight: bold;
      color: #064e3b;
    }

    input,
    textarea {
      width: 100%;
      padding: 10px;
      margin-bottom: 15px;
      border: 1px solid #ccc;
      border-radius: 8px;
    }

    button {
      background: #3a5a40;
      color: #fff;
      padding: 12px 25px;
      border: none;
      border-radius: 8px;
      cursor: pointer;
    }

    button:hover {
      background: #2d4a33;
    }

    .msg {
      margin-bottom: 15px;
      font-weight: bold;
    }

    .success {
      color: #065f46;
    }

    .error {
      color: #b91c1c;
    }
  </style>
</head>

<body>

  <div class="sidebar">
    <div>
      <h2>📚 SmartLib</h2>
      <ul>
        <li><a href="faculty.php">Dashboard</a></li>
        <li><a href="issue_books.php">Issue Books</a></li>
        <li><a href="faculty_return.php">Return Books</a></li>
        <li><a class="active">Recommended Books</a></li>
        <li><a href="faculty_requests.php">Priority Requests</a></li>
        <li><a href="faculty_reports.php">Reports</a></li>
        <li><a href="faculty_profile.php">My Profile</a></li>

      </ul>
    </div>
    <form action="../logout.php" method="post">
      <button class="logout-btn">Logout</button>
    </form>
  </div>

  <div class="main-content">
    <header>
      <h1>Recommend a New Book</h1>
      <p>Suggest a book you'd like the library to add 📘</p>
    </header>

    <div class="card">
      <?php if (isset($success)): ?>
        <div class="msg success"><?= htmlspecialchars($success) ?></div>
      <?php elseif (isset($error)): ?>
        <div class="msg error"><?= htmlspecialchars($error) ?></div>
      <?php endif; ?>

      <form method="post">
        <label>Book Title</label>
        <input type="text" name="title" required>

        <label>Author Name</label>
        <input type="text" name="author" required>

        <label>Reason for Recommendation</label>
        <textarea name="reason" rows="4" required></textarea>

        <button type="submit">Submit Recommendation</button>
      </form>
    </div>
  </div>

</body>

</html>

<?php $conn->close(); ?>