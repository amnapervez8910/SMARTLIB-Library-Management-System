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

include '../SmartLib.php';
$message = "";

// ===============================
// FETCH ISSUED / OVERDUE BOOKS
// ===============================
$issuedBooksQuery = "
    SELECT ib.issue_id, b.book_id, b.title, ib.issue_date
    FROM issued_books ib
    JOIN books b ON ib.book_id = b.book_id
    WHERE ib.member_id = ?
      AND (ib.status = 'issued' OR ib.status = 'overdue')
";
$stmt = $conn->prepare($issuedBooksQuery);
$stmt->bind_param("i", $faculty_id);
$stmt->execute();
$issuedBooks = $stmt->get_result();

// ===============================
// HANDLE RETURN
// ===============================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['book_id'])) {
  $book_id = (int) $_POST['book_id'];

  $check = $conn->prepare("
        SELECT issue_id FROM issued_books
        WHERE book_id = ? AND member_id = ?
          AND (status = 'issued' OR status = 'overdue')
        LIMIT 1
    ");
  $check->bind_param("ii", $book_id, $faculty_id);
  $check->execute();
  $res = $check->get_result();

  if ($res->num_rows === 0) {
    $message = "❌ This book is not currently issued under your account.";
  } else {
    $updateIssue = $conn->prepare("
            UPDATE issued_books
            SET status='returned', return_date=CURDATE()
            WHERE book_id=? AND member_id=?
        ");
    $updateIssue->bind_param("ii", $book_id, $faculty_id);
    $updateIssue->execute();

    $updateBook = $conn->prepare("
            UPDATE books SET status='available' WHERE book_id=?
        ");
    $updateBook->bind_param("i", $book_id);
    $updateBook->execute();

    $message = "✅ Book returned successfully!";

    // refresh list
    $stmt->execute();
    $issuedBooks = $stmt->get_result();
  }
}

// ===============================
// RETURN HISTORY
// ===============================
$returnHistoryQuery = "
    SELECT b.title, ib.return_date
    FROM issued_books ib
    JOIN books b ON ib.book_id = b.book_id
    WHERE ib.member_id = ? AND ib.status = 'returned'
    ORDER BY ib.return_date DESC
";
$hist = $conn->prepare($returnHistoryQuery);
$hist->bind_param("i", $faculty_id);
$hist->execute();
$returnHistory = $hist->get_result();
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <title>Return Books</title>
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

    select {
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

    table {
      width: 100%;
      border-collapse: collapse;
    }

    th,
    td {
      padding: 12px;
      border-bottom: 1px solid #ddd;
    }

    th {
      background: #3a5a40;
      color: #fff;
    }

    .msg {
      margin-bottom: 15px;
      font-weight: bold;
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
        <li><a class="active">Return Books</a></li>
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
      <h1>Return Books</h1>
      <p>Faculty members can return issued books anytime 📚</p>
    </header>

    <?php if ($message): ?>
      <div class="msg"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>

    <!-- RETURN FORM -->
    <div class="card">
      <form method="post">
        <label>Select Issued Book</label>
        <select name="book_id" required>
          <option value="">-- Select Issued Book --</option>
          <?php while ($row = $issuedBooks->fetch_assoc()): ?>
            <option value="<?= $row['book_id'] ?>">
              <?= htmlspecialchars($row['title']) ?> (Issued: <?= $row['issue_date'] ?>)
            </option>
          <?php endwhile; ?>
        </select>
        <button type="submit">Return Book</button>
      </form>
    </div>

    <!-- RETURN HISTORY -->
    <div class="card">
      <table>
        <tr>
          <th>Book Title</th>
          <th>Return Date</th>
        </tr>
        <?php if ($returnHistory->num_rows > 0): ?>
          <?php while ($row = $returnHistory->fetch_assoc()): ?>
            <tr>
              <td><?= htmlspecialchars($row['title']) ?></td>
              <td><?= $row['return_date'] ?></td>
            </tr>
          <?php endwhile; ?>
        <?php else: ?>
          <tr>
            <td colspan="2">No returned books yet.</td>
          </tr>
        <?php endif; ?>
      </table>
    </div>

  </div>
</body>

</html>

<?php
$stmt->close();
$hist->close();
$conn->close();
?>