<?php
session_start();
if (!isset($_SESSION['name']) || $_SESSION['role'] !== 'librarian') {
  header("Location: ../login.html");
  exit();
}

include '../SmartLib.php';
$name = $_SESSION['name'];

// --- 1. STATISTICS QUERIES ---
$totalBooks = $conn->query("SELECT COUNT(*) as total FROM books")->fetch_assoc()['total'];
$totalMembers = $conn->query("SELECT COUNT(*) as total FROM members")->fetch_assoc()['total'];
$totalIssued = $conn->query("SELECT COUNT(*) as total FROM issued_books WHERE status = 'issued'")->fetch_assoc()['total'];
$totalOverdue = $conn->query("SELECT COUNT(*) as total FROM issued_books WHERE status = 'issued' AND due_date < CURDATE()")->fetch_assoc()['total'];

// --- 2. RECENT ACTIVITY QUERY ---
$recent_sql = "SELECT ib.issue_date, b.title, m.name as member_name, ib.due_date, ib.status 
               FROM issued_books ib 
               JOIN books b ON ib.book_id = b.book_id 
               JOIN members m ON ib.member_id = m.member_id 
               ORDER BY ib.issue_date DESC, ib.issue_id DESC 
               LIMIT 5";
$recent_activity = $conn->query($recent_sql);
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <title>Librarian Dashboard | SmartLib</title>
  <link rel="stylesheet" href="../assets/styles/theme.css">
  <link rel="stylesheet" href="../assets/styles/layout.css">
  <style>
    /* Professional Heading Style */
    .content-header {
      margin-bottom: 30px;
    }

    .content-header h1 {
      font-size: 28px;
      color: #064e3b;
      font-weight: 700;
      margin-bottom: 5px;
    }

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
      text-align: center;
    }

    .stat-card h3 {
      font-size: 14px;
      color: #6b7280;
      text-transform: uppercase;
    }

    .stat-card h2 {
      color: #064e3b;
      font-size: 24px;
      margin-top: 5px;
    }

    .action-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
      gap: 20px;
      margin-bottom: 30px;
    }

    .action-card {
      background: #fff;
      padding: 25px;
      border-radius: 12px;
      box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
    }

    .btn-go {
      display: inline-block;
      padding: 10px 20px;
      background: #3a5a40;
      color: #fff;
      text-decoration: none;
      border-radius: 6px;
      margin-top: 15px;
      font-weight: 600;
      font-size: 14px;
    }

    .activity-section {
      background: #fff;
      padding: 25px;
      border-radius: 12px;
      box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
    }

    table {
      width: 100%;
      border-collapse: collapse;
    }

    th {
      background: #f3f4f6;
      color: #374151;
      padding: 12px;
      text-align: left;
      font-size: 14px;
    }

    td {
      padding: 12px;
      border-bottom: 1px solid #eee;
      font-size: 14px;
      color: #4b5563;
    }

    .status-pill {
      padding: 4px 10px;
      border-radius: 20px;
      font-size: 11px;
      font-weight: bold;
      background: #dcfce7;
      color: #166534;
      text-transform: uppercase;
    }
  </style>
</head>

<body>
  <?php include '../partials/sidebar.php'; ?>

  <div class="main-content">
    <div class="content-header">
      <h1>Welcome, <?= htmlspecialchars($name); ?> 👋</h1>
      <p>Here is what's happening in the library today.</p>

      <?php if(isset($_SESSION['last_visit'])): ?>
        <p style="font-size: 0.85rem; color: #666;">
          🗓️ Your last visit: <strong><?= $_SESSION['last_visit'] ?></strong>
        </p>
      <?php else: ?>
        <p>Welcome! This is your first visit this month. ✨</p>
      <?php endif; ?>
    </div>

    <div class="stats-grid">
      <div class="stat-card">
        <h3>Total Books</h3>
        <h2><?= $totalBooks ?></h2>
      </div>
      <div class="stat-card">
        <h3>Total Members</h3>
        <h2><?= $totalMembers ?></h2>
      </div>
      <div class="stat-card">
        <h3>Active Loans</h3>
        <h2><?= $totalIssued ?></h2>
      </div>
      <div class="stat-card" style="border-left-color: #b91c1c;">
        <h3 style="color: #b91c1c;">Overdue Items</h3>
        <h2 style="color: #b91c1c;"><?= $totalOverdue ?></h2>
      </div>
    </div>

    <div class="action-grid">
      <div class="action-card">
        <h3>📥 Reservations</h3>
        <p>Review and process pending book requests from users.</p>
        <a href="reservations.php" class="btn-go">Manage Requests</a>
      </div>
      <div class="action-card">
        <h3>📚 Book Stock</h3>
        <p>Add new titles or manage existing library inventory.</p>
        <a href="book-management.php" class="btn-go">Update Catalog</a>
      </div>
    </div>

    <div class="activity-section">
      <h2 style="color: #064e3b; font-size: 18px; margin-bottom: 20px;">🕒 Recent Activity</h2>
      <table>
        <thead>
          <tr>
            <th>Date Issued</th>
            <th>Book Title</th>
            <th>Borrower</th>
            <th>Due Date</th>
            <th>Status</th>
          </tr>
        </thead>
        <tbody>
          <?php if ($recent_activity->num_rows > 0): ?>
            <?php while ($row = $recent_activity->fetch_assoc()): ?>
              <tr>
                <td><?= date("M d, Y", strtotime($row['issue_date'])) ?></td>
                <td><strong><?= htmlspecialchars($row['title']) ?></strong></td>
                <td><?= htmlspecialchars($row['member_name']) ?></td>
                <td><?= date("M d, Y", strtotime($row['due_date'])) ?></td>
                <td><span class="status-pill"><?= ucfirst($row['status']) ?></span></td>
              </tr>
            <?php endwhile; ?>
          <?php else: ?>
            <tr>
              <td colspan="5" style="text-align:center; padding: 30px;">No recent activity found.</td>
            </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</body>

</html>