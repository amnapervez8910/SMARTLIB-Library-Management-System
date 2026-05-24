<?php
session_start();
if (!isset($_SESSION['name']) || $_SESSION['role'] !== 'librarian') {
  header("Location: ../login.html");
  exit();
}

require_once '../SmartLib.php';

// Initialize variables
$booksCount = $membersCount = $issuedCount = $overdueCount = 0;
$categories = [];
$categoryTotals = [];
$recentBooks = [];

try {
  // Fetch Counts
  $booksCount = $conn->query("SELECT COUNT(*) as c FROM books")->fetch_assoc()['c'];
  $membersCount = $conn->query("SELECT COUNT(*) as c FROM members")->fetch_assoc()['c'];
  $issuedCount = $conn->query("SELECT COUNT(*) as c FROM issued_books WHERE status = 'issued'")->fetch_assoc()['c'];
  $overdueCount = $conn->query("SELECT COUNT(*) as c FROM issued_books WHERE status = 'issued' AND due_date < CURDATE()")->fetch_assoc()['c'];

  // Fetch Category Data for Chart
  $catResult = $conn->query("SELECT category, COUNT(*) as total FROM books GROUP BY category");
  while ($row = $catResult->fetch_assoc()) {
    $categories[] = $row['category'] ?? 'Uncategorized';
    $categoryTotals[] = $row['total'];
  }

  // Fetch Recent Books
  $recentResult = $conn->query("SELECT book_id, title, author, status FROM books ORDER BY book_id DESC LIMIT 5");
  if ($recentResult) {
    while ($row = $recentResult->fetch_assoc()) {
      $recentBooks[] = $row;
    }
  }
} catch (Exception $e) {
  error_log($e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <title>Reports & Analytics | SmartLib</title>
  <link rel="stylesheet" href="../assets/styles/theme.css">
  <link rel="stylesheet" href="../assets/styles/layout.css">
  <style>
    /* 1. Header Standardizing (Matches Dashboard) */
    .content-header {
      margin-bottom: 30px;
    }

    .content-header h1 {
      font-size: 28px;
      color: #064e3b;
      font-weight: 700;
      margin-bottom: 5px;
    }

    .content-header p {
      color: #555;
      font-size: 16px;
    }

    /* 2. Stats Grid (Matching Dashboard) */
    .stats-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
      gap: 20px;
      margin-bottom: 30px;
    }

    .stat-card {
      background: #fff;
      padding: 20px;
      border-radius: 12px;
      box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
      border-left: 5px solid #3a5a40;
      /* Sage Green Border */
      text-align: center;
    }

    .stat-card h3 {
      color: #374151;
      font-size: 14px;
      margin-bottom: 5px;
      text-transform: uppercase;
      letter-spacing: 0.5px;
    }

    .stat-card h2 {
      color: #064e3b;
      font-size: 24px;
    }

    /* 3. Report Sections (Charts & Tables) */
    .report-section {
      background: #fff;
      padding: 25px;
      border-radius: 12px;
      box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
      margin-top: 25px;
    }

    .report-section h2 {
      color: #064e3b;
      font-size: 18px;
      margin-bottom: 20px;
      display: flex;
      align-items: center;
      gap: 10px;
    }

    /* 4. Table Styling (Dashboard Recent Activity Style) */
    table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 15px;
    }

    th {
      background: #f3f4f6;
      /* Dashboard Table Header Grey */
      color: #374151;
      padding: 12px;
      text-align: left;
      font-size: 14px;
      font-weight: 600;
      border-bottom: 1px solid #eee;
    }

    td {
      padding: 12px;
      border-bottom: 1px solid #eee;
      font-size: 14px;
      color: #4b5563;
    }

    /* Status Pills */
    .status-pill {
      padding: 4px 10px;
      border-radius: 20px;
      font-size: 11px;
      font-weight: bold;
      text-transform: uppercase;
    }

    .status-available {
      background: #dcfce7;
      color: #166534;
    }

    .status-borrowed {
      background: #fee2e2;
      color: #b91c1c;
    }

    .status-requested {
      background: #e0f2fe;
      color: #075985;
    }

    canvas {
      max-height: 300px;
    }
  </style>
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>

<body>

  <?php include '../partials/sidebar.php'; ?>

  <div class="main-content">
    <div class="content-header">
      <h1>📊 Reports & Analytics</h1>
      <p>A high-level overview of library performance and inventory distribution.</p>
    </div>

    <div class="stats-grid">
      <div class="stat-card">
        <h3>Total Books</h3>
        <h2><?= number_format($booksCount) ?></h2>
      </div>
      <div class="stat-card">
        <h3>Total Members</h3>
        <h2><?= number_format($membersCount) ?></h2>
      </div>
      <div class="stat-card">
        <h3>Current Loans</h3>
        <h2><?= number_format($issuedCount) ?></h2>
      </div>
      <div class="stat-card" style="border-left-color: #b91c1c;">
        <h3 style="color: #b91c1c;">Overdue Items</h3>
        <h2 style="color: #b91c1c;"><?= number_format($overdueCount) ?></h2>
      </div>
    </div>

    <div class="report-section">
      <h2>📚 Category Distribution</h2>
      <div style="height: 300px;">
        <canvas id="categoryChart"></canvas>
      </div>
    </div>

    <div class="report-section">
      <h2>📋 Recently Added to Catalog</h2>
      <table>
        <thead>
          <tr>
            <th>Book ID</th>
            <th>Title</th>
            <th>Author</th>
            <th>Availability</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($recentBooks as $book): ?>
            <tr>
              <td><strong>#<?= $book['book_id'] ?></strong></td>
              <td><?= htmlspecialchars($book['title']) ?></td>
              <td><?= htmlspecialchars($book['author']) ?></td>
              <td>
                <span class="status-pill status-<?= strtolower($book['status']) ?>">
                  <?= ucfirst($book['status']) ?>
                </span>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>

  <script>
    const ctx = document.getElementById('categoryChart').getContext('2d');
    new Chart(ctx, {
      type: 'doughnut',
      data: {
        labels: <?= json_encode($categories) ?>,
        datasets: [{
          data: <?= json_encode($categoryTotals) ?>,
          // Balanced Sage/Forest green palette
          backgroundColor: ['#064e3b', '#3a5a40', '#4f772d', '#90a955', '#ecf39e'],
          borderWidth: 2,
          borderColor: '#ffffff'
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: {
            position: 'right',
            labels: {
              boxWidth: 12,
              font: { size: 12, family: 'Poppins' }
            }
          }
        },
        cutout: '70%'
      }
    });
  </script>
</body>

</html>