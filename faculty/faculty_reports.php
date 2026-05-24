<?php
session_start();
if (!isset($_SESSION['member_id'])) {
  header("Location: ../login.html");
  exit();
}
$faculty_id = $_SESSION['member_id'];

include '../SmartLib.php';
if (!$conn) {
  die("❌ Database connection failed");
}

// COUNTS
$issuedResult = $conn->query("SELECT COUNT(*) AS total FROM issued_books");
$totalIssued = $issuedResult->fetch_assoc()['total'] ?? 0;

$recResult = $conn->query("SELECT COUNT(*) AS total FROM book_recommendations");
$totalRecommendations = $recResult->fetch_assoc()['total'] ?? 0;

$returnedResult = $conn->query("SELECT COUNT(*) AS total FROM issued_books WHERE return_date IS NOT NULL");
$totalReturned = $returnedResult->fetch_assoc()['total'] ?? 0;
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <title>Reports - Faculty</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">

  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

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

    .report-box {
      display: grid;
      grid-template-columns: repeat(3, 1fr);
      gap: 20px;
      margin-bottom: 40px;
    }

    .box {
      background: #fff;
      padding: 25px;
      border-radius: 12px;
      text-align: center;
      box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    }

    .box h3 {
      color: #064e3b;
      margin-bottom: 10px;
    }

    .box p {
      font-size: 22px;
      font-weight: bold;
    }

    .graphs {
      display: grid;
      grid-template-columns: 2fr 1fr;
      gap: 20px;
    }

    .graph-card {
      background: #fff;
      padding: 25px;
      border-radius: 12px;
      box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
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
        <li><a href="faculty_requests.php">Priority Requests</a></li>
        <li><a class="active">Reports</a></li>
        <li><a href="faculty_profile.php">My Profile</a></li>

      </ul>
    </div>
    <form action="../logout.php" method="post">
      <button class="logout-btn">Logout</button>
    </form>
  </div>

  <div class="main-content">
    <header>
      <h1>Reports & Analytics</h1>
      <p>View issued, returned, and recommended book summaries.</p>
    </header>

    <section class="report-box">
      <div class="box">
        <h3>Total Books Issued</h3>
        <p><?= $totalIssued ?></p>
      </div>
      <div class="box">
        <h3>Total Recommendations</h3>
        <p><?= $totalRecommendations ?></p>
      </div>
      <div class="box">
        <h3>Books Returned</h3>
        <p><?= $totalReturned ?></p>
      </div>
    </section>

    <!-- 📊 GRAPHS -->
    <section class="graphs">
      <div class="graph-card">
        <h3>Books Activity Overview</h3>
        <canvas id="barChart"></canvas>
      </div>

      <div class="graph-card">
        <h3>Issued vs Returned</h3>
        <canvas id="pieChart"></canvas>
      </div>
    </section>
  </div>

  <script>
    const barCtx = document.getElementById('barChart');
    new Chart(barCtx, {
      type: 'bar',
      data: {
        labels: ['Issued', 'Returned', 'Recommendations'],
        datasets: [{
          data: [<?= $totalIssued ?>, <?= $totalReturned ?>, <?= $totalRecommendations ?>],
          backgroundColor: ['#3a5a40', '#10b981', '#b5a820']
        }]
      }
    });

    const pieCtx = document.getElementById('pieChart');
    new Chart(pieCtx, {
      type: 'pie',
      data: {
        labels: ['Issued', 'Returned'],
        datasets: [{
          data: [<?= $totalIssued ?>, <?= $totalReturned ?>],
          backgroundColor: ['#3a5a40', '#10b981']
        }]
      }
    });
  </script>

</body>

</html>

<?php $conn->close(); ?>