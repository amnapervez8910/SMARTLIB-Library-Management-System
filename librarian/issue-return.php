<?php
session_start();
if (!isset($_SESSION['name']) || $_SESSION['role'] !== 'librarian') {
  header("Location: ../login.html");
  exit();
}

require_once '../SmartLib.php';

/* ======================
    RETURN BOOK LOGIC
====================== */
if (isset($_GET['return'])) {
  $id = intval($_GET['return']);

  $res = $conn->query("SELECT book_id FROM issued_books WHERE issue_id=$id");
  if ($data = $res->fetch_assoc()) {
    $book_id = $data['book_id'];
    $conn->query("UPDATE issued_books SET status='returned', return_date=CURDATE() WHERE issue_id=$id");
    $conn->query("UPDATE books SET status='available' WHERE book_id=$book_id");
  }

  header("Location: issue-return.php?success=returned");
  exit();
}

/* ======================
    FETCH ACTIVE LOANS
====================== */
$query = "SELECT 
            ib.issue_id, 
            ib.member_id, 
            m.name AS member_name, 
            b.title AS book_title, 
            ib.issue_date, 
            ib.due_date 
          FROM issued_books ib
          JOIN members m ON ib.member_id = m.member_id
          JOIN books b ON ib.book_id = b.book_id
          WHERE ib.status = 'issued'
          ORDER BY ib.due_date ASC";

$issued = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <title>Issue & Return | SmartLib</title>
  <link rel="stylesheet" href="../assets/styles/theme.css">
  <link rel="stylesheet" href="../assets/styles/layout.css">
  <style>
    /* 1. Header Standardizing (Matches Dashboard & Book Management) */
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

    /* 2. Action Section Standardizing */
    .action-section {
      background: #fff;
      border-radius: 12px;
      padding: 25px;
      margin-bottom: 25px;
      box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
    }

    .action-section h2 {
      color: #064e3b;
      font-size: 18px;
      margin-bottom: 20px;
    }

    /* 3. Table Standardizing (Matches Recent Activity Style) */
    table {
      width: 100%;
      border-collapse: collapse;
      background: #fff;
      border-radius: 12px;
      overflow: hidden;
    }

    th {
      background: #f3f4f6;
      /* Dashboard Table Header Grey */
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
      vertical-align: middle;
    }

    tr:hover {
      background: #f9fafb;
    }

    /* 4. Button Standardizing (Sage Green like Dashboard) */
    .return-btn {
      background: #3a5a40;
      color: white;
      border: none;
      padding: 8px 16px;
      border-radius: 6px;
      cursor: pointer;
      font-weight: 600;
      font-size: 13px;
      transition: 0.3s;
    }

    .return-btn:hover {
      background: #064e3b;
    }

    /* 5. Status & Tags */
    .overdue {
      color: #b91c1c;
      /* Established red for overdue */
      font-weight: 700;
    }

    .member-tag {
      font-size: 11px;
      color: #075985;
      background: #e0f2fe;
      padding: 3px 8px;
      border-radius: 20px;
      font-weight: bold;
      text-transform: uppercase;
      display: inline-block;
      margin-top: 4px;
    }

    .success-alert {
      padding: 12px;
      background: #dcfce7;
      color: #166534;
      margin-bottom: 20px;
      border-radius: 8px;
      border-left: 5px solid #166534;
      font-weight: 600;
    }
  </style>
</head>

<body>

  <?php include '../partials/sidebar.php'; ?>

  <div class="main-content">
    <div class="content-header">
      <h1>📖 Issue & Return Books</h1>
      <p>Track active loans and process library returns ✨</p>
    </div>

    <?php if (isset($_GET['success'])): ?>
      <div class="success-alert">
        ✅ Book has been successfully returned and marked as available.
      </div>
    <?php endif; ?>

    <div class="action-section">
      <h2>📋 Currently Issued Books</h2>
      <table>
        <thead>
          <tr>
            <th>Issue ID</th>
            <th>Borrower</th>
            <th>Book Title</th>
            <th>Issue Date</th>
            <th>Due Date</th>
            <th>Action</th>
          </tr>
        </thead>
        <tbody>
          <?php if ($issued && $issued->num_rows > 0): ?>
            <?php while ($row = $issued->fetch_assoc()):
              $isOverdue = (strtotime($row['due_date']) < time());
              ?>
              <tr>
                <td><strong>#<?= $row['issue_id'] ?></strong></td>
                <td>
                  <strong><?= htmlspecialchars($row['member_name']) ?></strong><br>
                  <span class="member-tag">ID: <?= $row['member_id'] ?></span>
                </td>
                <td><?= htmlspecialchars($row['book_title']) ?></td>
                <td><?= date("M d, Y", strtotime($row['issue_date'])) ?></td>
                <td class="<?= $isOverdue ? 'overdue' : '' ?>">
                  <?= date("M d, Y", strtotime($row['due_date'])) ?>     <?= $isOverdue ? '⚠️' : '' ?>
                </td>
                <td>
                  <a href="?return=<?= $row['issue_id'] ?>" onclick="return confirm('Confirm return for this book?')">
                    <button class="return-btn">Process Return</button>
                  </a>
                </td>
              </tr>
            <?php endwhile; ?>
          <?php else: ?>
            <tr>
              <td colspan="6" style="text-align:center; padding: 50px; color: #6b7280;">
                No books are currently issued.
              </td>
            </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</body>

</html>