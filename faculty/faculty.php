<?php
session_start();
if (!isset($_SESSION['name']) || !isset($_SESSION['role'])) {
  header("Location: ../login.html");
  exit();
}
if ($_SESSION['role'] != "faculty") {
  echo "❌ Access denied!";
  exit();
}

$faculty_name = $_SESSION['name'];
$faculty_id = $_SESSION['member_id'];

include '../SmartLib.php';
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Faculty Dashboard</title>

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

    /* Sidebar */
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
      flex-grow: 1;
    }

    .sidebar ul li {
      margin-bottom: 10px;
    }

    .sidebar ul li a {
      display: block;
      padding: 12px;
      border-radius: 8px;
      color: #fff;
      text-decoration: none;
      transition: 0.3s;
    }

    .sidebar ul li a:hover,
    .sidebar ul li a.active {
      background: #3a5a40;
    }

    /* ✅ LOGOUT BUTTON (EXACT SAME AS faculty_return.php) */
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

    /* Main Content */
    .main-content {
      margin-left: 250px;
      padding: 20px;
      width: calc(100% - 250px);
    }

    header h1 {
      font-size: 28px;
      margin-bottom: 5px;
      color: #064e3b;
    }

    header p {
      color: #555;
      margin-bottom: 20px;
    }

    /* Cards Layout */
    .cards {
      display: grid;
      grid-template-columns: repeat(3, 1fr);
      gap: 20px;
      margin-bottom: 40px;
    }

    .card {
      background: #fff;
      padding: 25px;
      border-radius: 12px;
      box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
      text-align: center;
      transition: transform 0.3s;
    }

    .card:hover {
      transform: translateY(-5px);
    }

    .card h3 {
      font-size: 20px;
      margin-bottom: 10px;
      color: #064e3b;
    }

    .card p {
      font-size: 16px;
      margin-bottom: 15px;
    }

    .card a {
      display: inline-block;
      padding: 10px 16px;
      background: #3a5a40;
      color: #fff;
      text-decoration: none;
      border-radius: 8px;
    }

    .card a:hover {
      background: #064e3b;
    }

    /* Faculty Privileges */
    .features h2 {
      font-size: 24px;
      margin-bottom: 15px;
      color: #064e3b;
    }

    .features ul {
      list-style: none;
    }

    .features ul li {
      background: #fff;
      margin-bottom: 10px;
      padding: 15px;
      border-radius: 10px;
      box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }

    .features ul li span {
      font-weight: bold;
      color: #10b981;
    }
  </style>
</head>

<body>

  <div class="sidebar">
    <div>
      <h2>📚 SmartLib</h2>
      <ul>
        <li><a class="active" href="#">Dashboard</a></li>
        <li><a href="issue_books.php">Issue Books</a></li>
        <li><a href="faculty_return.php">Return Books</a></li>
        <li><a href="recommend_book.php">Recommended Books</a></li>
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
      <h1>Welcome, <?php echo htmlspecialchars($faculty_name); ?></h1>
      <p>Manage your issued books, recommendations, and reports with higher privileges.</p>
      <?php if (isset($_SESSION['last_visit'])): ?>
        <p
          style="background: #e0f2fe; color: #0369a1; display: inline-block; padding: 4px 12px; border-radius: 4px; font-size: 13px;">
          🕒 Last Session: <?= $_SESSION['last_visit'] ?>
        </p>
      <?php endif; ?>
    </header>

    <section class="cards">
      <div class="card">
        <h3>Issue Books</h3>
        <p>Issue new books and manage your borrowed list.</p>
        <a href="issue_books.php">Go</a>
      </div>

      <div class="card">
        <h3>Return Books</h3>
        <p>Return your issued books and view return history.</p>
        <a href="faculty_return.php">Go</a>
      </div>

      <div class="card">
        <h3>Recommended Books</h3>
        <p>Recommend new books for the library collection.</p>
        <a href="recommend_book.php">Go</a>
      </div>

      <div class="card">
        <h3>Priority Requests</h3>
        <p>View and manage book requests with higher priority.</p>
        <a href="faculty_requests.php">Go</a>
      </div>

      <div class="card">
        <h3>Reports</h3>
        <p>Check your book issue summary and statistics.</p>
        <a href="faculty_reports.php">Go</a>
      </div>
    </section>

    <section class="features">
      <h2>Faculty Privileges</h2>
      <ul>
        <li><span>Extended Issue Duration:</span> Faculty can issue books for up to 30 days.</li>
        <li><span>No Late Fines:</span> Faculty are exempted from overdue fines.</li>
        <li><span>Book Recommendations:</span> Faculty can suggest new titles.</li>
        <li><span>Priority Access:</span> Faculty requests are processed first.</li>
      </ul>
    </section>
  </div>

</body>

</html>