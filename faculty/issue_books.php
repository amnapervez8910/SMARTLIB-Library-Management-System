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

include '../SmartLib.php';

$message = "";

// ===============================
// FETCH AVAILABLE BOOKS
// ===============================
$booksQuery = $conn->prepare("
    SELECT book_id, title, author 
    FROM books 
    WHERE copies_available > 0
   ORDER BY title ASC

");
$booksQuery->execute();
$booksResult = $booksQuery->get_result();

// ===============================
// ISSUE BOOK LOGIC
// ===============================
if ($_SERVER["REQUEST_METHOD"] === "POST") {
  $book_id = $_POST['book_id'];
  $issue_days = $_POST['issue_days'];

  $check = $conn->prepare("
        SELECT title, author, copies_available 
        FROM books 
        WHERE book_id = ?
    ");
  $check->bind_param("i", $book_id);
  $check->execute();
  $bookRes = $check->get_result();

  if ($bookRes->num_rows == 0) {
    $message = "❌ Book not found!";
  } else {
    $book = $bookRes->fetch_assoc();

    if ($book['copies_available'] <= 0) {
      $message = "❌ No copies available!";
    } else {

      $issue_date = date("Y-m-d");
      $due_date = date("Y-m-d", strtotime("+$issue_days days"));

      // Insert issued book
      $insert = $conn->prepare("
        INSERT INTO issued_books
        (member_id, book_id, issue_date, due_date, status)
        VALUES (?, ?, ?, ?, 'requested')
      ");
      $insert->bind_param("iiss", $faculty_id, $book_id, $issue_date, $due_date);
      $insert->execute();

      // Insert priority request
      $request = $conn->prepare("
        INSERT INTO requests
        (member_id, book_title, author, priority, status, request_date)
        VALUES (?, ?, ?, 'priority', 'pending', NOW())
      ");
      $request->bind_param("iss", $faculty_id, $book['title'], $book['author']);
      $request->execute();

      // Update book copies
      $newCopies = $book['copies_available'] - 1;
      $update = $conn->prepare("
        UPDATE books 
        SET copies_available = ?
        WHERE book_id = ?
      ");
      $update->bind_param("ii", $newCopies, $book_id);
      $update->execute();

      $message = "✅ Book requested successfully!";
    }
  }
}

// ===============================
// FETCH ISSUED BOOKS
// ===============================
$query = "
  SELECT 
    b.title,
    b.author,
    ib.issue_date,
    ib.due_date,
    ib.return_date,
    ib.status
  FROM issued_books ib
  JOIN books b ON ib.book_id = b.book_id
  WHERE ib.member_id = ?
  ORDER BY ib.issue_date DESC
";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $faculty_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <title>Issue Books</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">

  <style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
      font-family: "Segoe UI", sans-serif
    }

    body {
      display: flex;
      background: #f9fafb;
      color: #333
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
      border-radius: 0 12px 12px 0
    }

    .sidebar h2 {
      text-align: center;
      margin-bottom: 25px
    }

    .sidebar ul {
      list-style: none
    }

    .sidebar ul li {
      margin-bottom: 10px
    }

    .sidebar ul li a {
      display: block;
      padding: 12px;
      color: #fff;
      text-decoration: none;
      border-radius: 8px
    }

    .sidebar ul li a:hover,
    .active {
      background: #3a5a40
    }

    .logout-btn {
      width: 80%;
      margin: 25px auto;
      padding: 10px;
      background: #b5a820;
      color: #fff;
      border: none;
      border-radius: 8px;
      cursor: pointer
    }

    .logout-btn:hover {
      background: #7c1111
    }

    .main-content {
      margin-left: 250px;
      padding: 20px;
      width: calc(100% - 250px)
    }

    header h1 {
      color: #064e3b;
      font-size: 26px;
      margin-bottom: 5px
    }

    header p {
      margin-bottom: 20px
    }

    .card {
      background: #fff;
      padding: 25px;
      border-radius: 12px;
      box-shadow: 0 4px 6px rgba(0, 0, 0, .1);
      margin-bottom: 30px
    }

    label {
      display: block;
      margin-bottom: 8px;
      font-weight: bold;
      color: #064e3b
    }

    input,
    select {
      width: 100%;
      padding: 10px;
      margin-bottom: 15px;
      border: 1px solid #ccc;
      border-radius: 8px
    }

    button {
      background: #3a5a40;
      color: #fff;
      padding: 12px 25px;
      border: none;
      border-radius: 8px;
      cursor: pointer
    }

    button:hover {
      background: #2d4a33
    }

    table {
      width: 100%;
      border-collapse: collapse
    }

    th,
    td {
      padding: 12px;
      border-bottom: 1px solid #ddd
    }

    th {
      background: #3a5a40;
      color: #fff
    }

    .msg {
      margin-bottom: 15px;
      font-weight: bold
    }
  </style>
</head>

<body>

  <div class="sidebar">
    <div>
      <h2>📚 SmartLib</h2>
      <ul>
        <li><a href="faculty.php">Dashboard</a></li>
        <li><a class="active">Issue Books</a></li>
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
      <h1>Issue Books</h1>
      <p>Faculty can issue books for up to <b>30 days</b>.</p>
    </header>

    <div class="card">
      <?php if ($message): ?>
        <div class="msg"><?= $message ?></div><?php endif; ?>

      <form method="post">
        <label>Book ID</label>
        <select name="book_id" required>
          <option value="">Select Book</option>
          <?php while ($b = $booksResult->fetch_assoc()): ?>
            <option value="<?= $b['book_id'] ?>">
              <?= htmlspecialchars($b['title']) ?> — <?= htmlspecialchars($b['author']) ?>
            </option>
          <?php endwhile; ?>
        </select>

        <label>Issue Duration (Days)</label>
        <select name="issue_days">
          <option value="15">15 Days</option>
          <option value="30" selected>30 Days</option>
        </select>

        <button type="submit">Issue Book</button>
      </form>
    </div>

    <div class="card">
      <table>
        <tr>
          <th>Title</th>
          <th>Author</th>
          <th>Issue Date</th>
          <th>Due Date</th>
          <th>Return Date</th>
          <th>Status</th>
        </tr>

        <?php if ($result->num_rows > 0): ?>
          <?php while ($row = $result->fetch_assoc()): ?>
            <tr>
              <td><?= htmlspecialchars($row['title']) ?></td>
              <td><?= htmlspecialchars($row['author']) ?></td>
              <td><?= $row['issue_date'] ?></td>
              <td><?= $row['due_date'] ?></td>
              <td><?= $row['return_date'] ?? '—' ?></td>
              <td><?= ucfirst($row['status']) ?></td>
            </tr>
          <?php endwhile; ?>
        <?php else: ?>
          <tr>
            <td colspan="6">No books issued yet.</td>
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