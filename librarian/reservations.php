<?php
session_start();

if (!isset($_SESSION['member_id']) || $_SESSION['role'] !== 'librarian') {
  header("Location: ../login.html");
  exit();
}

include '../SmartLib.php';

$msg = $_GET['msg'] ?? "";

/* =========================================
   LIBRARIAN APPROVAL LOGIC (Stayed the same)
============================================ */
if (isset($_GET['action']) && isset($_GET['id'])) {
  $req_id = intval($_GET['id']);
  $action = $_GET['action'];

  if ($action === 'approve') {
    $stmt = $conn->prepare("
        SELECT r.member_id, r.book_title, m.role 
        FROM requests r 
        JOIN members m ON r.member_id = m.member_id 
        WHERE r.request_id = ?
    ");
    $stmt->bind_param("i", $req_id);
    $stmt->execute();
    $reqData = $stmt->get_result()->fetch_assoc();

    if ($reqData) {
      $title = $reqData['book_title'];
      $m_id = $reqData['member_id'];
      $role = strtolower($reqData['role']);

      $loanPeriod = ($role === 'librarian' || $role === 'faculty') ? 30 : 14;
      $today = date('Y-m-d');
      $due_date = date('Y-m-d', strtotime("+$loanPeriod days"));

      $updateStmt = $conn->prepare("
            UPDATE issued_books ib 
            JOIN books b ON ib.book_id = b.book_id 
            SET ib.status = 'issued', 
                ib.issue_date = ?, 
                ib.due_date = ? 
            WHERE ib.member_id = ? AND b.title = ? AND ib.status = 'requested'
        ");
      $updateStmt->bind_param("ssis", $today, $due_date, $m_id, $title);
      $updateStmt->execute();

      $conn->query("UPDATE books SET status = 'issued' WHERE title = '$title' AND copies_available = 0");
      $conn->query("DELETE FROM requests WHERE request_id = $req_id");

      header("Location: reservations.php?msg=Request Approved. Due Date: $due_date");
    }
  } elseif ($action === 'reject') {
    $stmt = $conn->prepare("SELECT book_title, member_id FROM requests WHERE request_id = ?");
    $stmt->bind_param("i", $req_id);
    $stmt->execute();
    $data = $stmt->get_result()->fetch_assoc();

    if ($data) {
      $title = $data['book_title'];
      $m_id = $data['member_id'];
      $conn->query("UPDATE books SET copies_available = copies_available + 1, status = 'available' WHERE title = '$title'");
      $conn->query("DELETE ib FROM issued_books ib 
                    JOIN books b ON ib.book_id = b.book_id 
                    WHERE ib.member_id = $m_id AND b.title = '$title' AND ib.status = 'requested'");
      $conn->query("DELETE FROM requests WHERE request_id = $req_id");
      header("Location: reservations.php?msg=Request Rejected and Removed");
    }
  }
  exit();
}

/* FETCH DATA */
$query = "SELECT r.*, m.name as member_name 
          FROM requests r 
          JOIN members m ON r.member_id = m.member_id 
          ORDER BY CASE WHEN r.priority = 'priority' THEN 1 ELSE 2 END, r.request_date DESC";
$requests = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <title>Pending Approvals | SmartLib</title>
  <link rel="stylesheet" href="../assets/styles/theme.css">
  <link rel="stylesheet" href="../assets/styles/layout.css">
  <style>
    /* 1. Header Standardizing */
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

    /* 2. Success Alert Style */
    .success-alert {
      padding: 12px;
      background: #dcfce7;
      color: #166534;
      margin-bottom: 20px;
      border-radius: 8px;
      border-left: 5px solid #166534;
      font-weight: 600;
    }

    /* 3. Table Standardizing (Dashboard Style) */
    .action-section {
      background: #fff;
      border-radius: 12px;
      box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
      padding: 25px;
    }

    .action-section h2 {
      color: #064e3b;
      font-size: 18px;
      margin-bottom: 20px;
    }

    table {
      width: 100%;
      border-collapse: collapse;
      border-radius: 12px;
      overflow: hidden;
    }

    th {
      background: #f3f4f6;
      color: #374151;
      padding: 15px;
      text-align: left;
      font-size: 14px;
      font-weight: 600;
      border-bottom: 1px solid #eee;
    }

    td {
      padding: 15px;
      border-bottom: 1px solid #eee;
      font-size: 14px;
      color: #4b5563;
    }

    tr:hover {
      background: #f9fafb;
    }

    /* 4. Badges & Buttons */
    .priority-badge {
      padding: 4px 10px;
      border-radius: 20px;
      font-size: 11px;
      font-weight: bold;
      text-transform: uppercase;
    }

    .badge-faculty {
      background: #fef3c7;
      color: #92400e;
    }

    .badge-student {
      background: #e0f2fe;
      color: #075985;
    }

    .btn-action {
      padding: 6px 12px;
      border-radius: 6px;
      font-size: 12px;
      font-weight: 600;
      text-decoration: none;
      transition: 0.3s;
      display: inline-block;
    }

    .btn-approve {
      background: #3a5a40;
      color: white;
      margin-right: 5px;
    }

    .btn-approve:hover {
      background: #064e3b;
    }

    .btn-reject {
      background: #f3f4f6;
      color: #b91c1c;
      border: 1px solid #fee2e2;
    }

    .btn-reject:hover {
      background: #fee2e2;
    }
  </style>
</head>

<body>

  <?php include '../partials/sidebar.php'; ?>

  <div class="main-content">
    <div class="content-header">
      <h1>📥 Pending Book Requests</h1>
      <p>Review and process incoming book reservations. High priority items are listed first.</p>
    </div>

    <?php if ($msg): ?>
      <div class="success-alert">✅ <?= htmlspecialchars($msg) ?></div>
    <?php endif; ?>

    <div class="action-section">
      <h2>📋 Action Required</h2>
      <table>
        <thead>
          <tr>
            <th>Priority</th>
            <th>Member Info</th>
            <th>Book Title</th>
            <th>Request Date</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php if ($requests && $requests->num_rows > 0): ?>
            <?php while ($r = $requests->fetch_assoc()): ?>
              <tr style="<?= $r['priority'] == 'priority' ? 'background: #fffcf0;' : '' ?>">
                <td>
                  <span class="priority-badge <?= $r['priority'] == 'priority' ? 'badge-faculty' : 'badge-student' ?>">
                    <?= $r['priority'] == 'priority' ? 'FACULTY' : 'STUDENT' ?>
                  </span>
                </td>
                <td>
                  <strong><?= htmlspecialchars($r['member_name']) ?></strong><br>
                  <small style="color: #666;">Member ID: #<?= $r['member_id'] ?></small>
                </td>
                <td><strong><?= htmlspecialchars($r['book_title']) ?></strong></td>
                <td><?= date("M d, Y", strtotime($r['request_date'])) ?></td>
                <td>
                  <a href="?action=approve&id=<?= $r['request_id'] ?>" class="btn-action btn-approve">Approve</a>
                  <a href="?action=reject&id=<?= $r['request_id'] ?>" class="btn-action btn-reject"
                    onclick="return confirm('Are you sure you want to reject this request?')">Reject</a>
                </td>
              </tr>
            <?php endwhile; ?>
          <?php else: ?>
            <tr>
              <td colspan="5" style="text-align:center; padding: 50px; color: #6b7280;">
                All clear! No pending requests to process.
              </td>
            </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</body>

</html>