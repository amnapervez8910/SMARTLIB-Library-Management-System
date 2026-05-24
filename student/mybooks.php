<?php
session_start();
if (!isset($_SESSION['member_id'])) {
  header("Location: ../login.php");
  exit();
}
require '../SmartLib.php';
$member_id = $_SESSION['member_id'];

// We only need to check issued_books. If the request was approved, status is 'issued'.
// If it's still waiting, status is 'requested'.
$sql = "SELECT ib.issue_id, ib.book_id, ib.issue_date, ib.due_date, ib.return_date, ib.status AS issue_status,
               b.title, b.author
        FROM issued_books ib
        JOIN books b ON ib.book_id = b.book_id
        WHERE ib.member_id = ? 
        ORDER BY ib.issue_date DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $member_id);
$stmt->execute();
$result = $stmt->get_result();

$books = [];
while ($row = $result->fetch_assoc()) {
  $today = new DateTime();
  $due_date = $row['due_date'] ? new DateTime($row['due_date']) : null;
  $return_date = $row['return_date'] ? new DateTime($row['return_date']) : null;

  $fine = "-";
  $status_text = "";
  $status_class = "";

  // 1. HANDLE PENDING (Still 'requested' in issued_books table)
  if ($row['issue_status'] === 'requested') {
    $status_text = "Pending Approval";
    $status_class = "pending";
  }
  // 2. HANDLE RETURNED
  elseif ($row['issue_status'] === 'returned') {
    $status_text = "Returned";
    $status_class = "available";
    if ($return_date && $due_date && $return_date > $due_date) {
      $days_overdue = $return_date->diff($due_date)->days;
      $fine = "Rs. " . ($days_overdue * 10);
    }
  }
  // 3. HANDLE ISSUED (Librarian has approved it)
  elseif ($row['issue_status'] === 'issued') {
    if ($due_date && $today > $due_date) {
      $status_text = "Overdue";
      $status_class = "overdue";
      $days_overdue = $today->diff($due_date)->days;
      $fine = "Rs. " . ($days_overdue * 10);
    } else {
      $status_text = "Issued / On Time";
      $status_class = "available";
    }
  }

  $row['status_display'] = $status_text;
  $row['class_display'] = $status_class;
  $row['fine_amount'] = $fine;
  $books[] = $row;
}
$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <title>My Books | Smart Library</title>
  <link rel="stylesheet" href="../assets/styles/theme.css" />
  <link rel="stylesheet" href="../assets/styles/layout.css" />
  <link rel="stylesheet" href="../assets/styles/student.css" />
  <style>
    /* Status Badge Styling */
    .status {
      padding: 5px 10px;
      border-radius: 20px;
      font-size: 12px;
      font-weight: bold;
      text-transform: uppercase;
    }

    .available {
      background: #dcfce7;
      color: #166534;
    }

    /* Green */
    .pending {
      background: #fef9c3;
      color: #854d0e;
    }

    /* Yellow */
    .overdue {
      background: #fee2e2;
      color: #991b1b;
    }

    /* Red */
    .rejected-priority {
      background: #f3f4f6;
      color: #374151;
      border: 1px dashed #9ca3af;
    }

    /* Grey */

    .fine-highlight {
      color: #dc2626;
      font-weight: bold;
    }
  </style>
</head>

<body>
  <?php include '../partials/sidebar.php'; ?>

  <div class="main-content">
    <header>
      <h1>My Books</h1>
      <p>Track your borrowed books and request status 📖</p>
    </header>

    <section class="book-list-section">
      <h2>📕 Your Book History</h2>
      <table>
        <thead>
          <tr>
            <th>Title</th>
            <th>Author</th>
            <th>Issue Date</th>
            <th>Due Date</th>
            <th>Status</th>
            <th>Fine</th>
          </tr>
        </thead>
        <tbody>
          <?php if (!empty($books)): ?>
            <?php foreach ($books as $book): ?>
              <tr>
                <td><strong><?= htmlspecialchars($book['title']) ?></strong></td>
                <td><?= htmlspecialchars($book['author']) ?></td>
                <td><?= $book['issue_date'] ?: '—' ?></td>
                <td><?= $book['due_date'] ?: '—' ?></td>
                <td>
                  <span class="status <?= $book['class_display'] ?>">
                    <?= $book['status_display'] ?>
                  </span>
                </td>
                <td class="<?= $book['fine_amount'] != '-' ? 'fine-highlight' : '' ?>">
                  <?= $book['fine_amount'] ?>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php else: ?>
            <tr>
              <td colspan="6" style="text-align:center;">No books found in your account.</td>
            </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </section>
  </div>
</body>

</html>