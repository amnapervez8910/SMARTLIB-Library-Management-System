<?php
// ===============================
// SESSION CHECK
// ===============================
session_start();
if (!isset($_SESSION['member_id'])) {
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
// FETCH LOGGED-IN FACULTY PRIORITY REQUESTS
// ===============================
$query = "
    SELECT 
        r.request_id,
        r.book_title,
        r.author,
        r.priority,
        r.status,
        r.request_date,
        m.name AS requested_by
    FROM requests r
    LEFT JOIN members m ON r.member_id = m.member_id
    WHERE r.priority = 'priority'
      AND r.member_id = ?
    ORDER BY r.request_date ASC
";

$stmt = $conn->prepare($query);
if (!$stmt) {
  die("❌ Prepare failed: " . $conn->error);
}

$stmt->bind_param("i", $faculty_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <title>Priority Requests</title>
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
    }

    table {
      width: 100%;
      border-collapse: collapse;
    }

    th,
    td {
      padding: 12px;
      border-bottom: 1px solid #ddd;
      text-align: left;
    }

    th {
      background: #3a5a40;
      color: #fff;
    }

    tr:hover {
      background: #f1f5f3;
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
        <li><a href="recommend_book.php">Recommended Books</a></li>
        <li><a class="active">Priority Requests</a></li>
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
      <h1>Priority Book Requests</h1>
      <p>Your priority requests only.</p>
    </header>

    <div class="card">
      <table>
        <tr>
          <th>ID</th>
          <th>Book Title</th>
          <th>Author</th>
          <th>Priority</th>
          <th>Status</th>
          <th>Request Date</th>
          <th>Requested By</th>
        </tr>

        <?php if ($result->num_rows > 0): ?>
          <?php while ($row = $result->fetch_assoc()): ?>
            <tr>
              <td><?= $row['request_id'] ?></td>
              <td><?= htmlspecialchars($row['book_title']) ?></td>
              <td><?= htmlspecialchars($row['author']) ?></td>
              <td><?= htmlspecialchars($row['priority']) ?></td>
              <td><?= htmlspecialchars($row['status']) ?></td>
              <td><?= $row['request_date'] ?></td>
              <td><?= htmlspecialchars($row['requested_by'] ?? 'N/A') ?></td>
            </tr>
          <?php endwhile; ?>
        <?php else: ?>
          <tr>
            <td colspan="7" style="text-align:center;">No priority requests found.</td>
          </tr>
        <?php endif; ?>
      </table>
    </div>

  </div>
</body>

</html>

<?php
$stmt->close();
$conn->close();
?>