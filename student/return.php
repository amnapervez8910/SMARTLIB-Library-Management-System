<?php
session_start();
include("../SmartLib.php");
if (!isset($_SESSION['member_id'])) {
  header("Location: ../login.html");
  exit();
}
$member_id = $_SESSION['member_id'];
$message = "";
$issuedBooksQuery = "
    SELECT issued_books.issue_id, books.book_id, books.title, issued_books.due_date
    FROM issued_books
    INNER JOIN books ON issued_books.book_id = books.book_id
    WHERE issued_books.member_id = '$member_id'
    AND (issued_books.status = 'issued' OR issued_books.status = 'overdue')
";
$issuedBooks = mysqli_query($conn, $issuedBooksQuery);
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $book_id = $_POST['book_id'];
  $checkQuery = "
        SELECT * FROM issued_books
        WHERE book_id = '$book_id'
        AND member_id = '$member_id'
        AND (status = 'issued' OR status = 'overdue')
        LIMIT 1
    ";
  $checkResult = mysqli_query($conn, $checkQuery);
  if (mysqli_num_rows($checkResult) == 0) {
    $message = "❌ This book is not issued or overdue under your account!";
  } else {
    $updateIssue = "
            UPDATE issued_books
            SET status='returned', return_date=CURDATE()
            WHERE book_id='$book_id' AND member_id='$member_id'
        ";
    mysqli_query($conn, $updateIssue);
    $updateBook = "UPDATE books SET status='available' WHERE book_id='$book_id'";
    mysqli_query($conn, $updateBook);
    $message = "✅ Book returned successfully!";
  }
}
$returnHistoryQuery = "
    SELECT books.title, issued_books.return_date, issued_books.status
    FROM issued_books
    INNER JOIN books ON issued_books.book_id = books.book_id
    WHERE issued_books.member_id = '$member_id'
    AND issued_books.status = 'returned'
    ORDER BY issued_books.return_date DESC
";
$returnHistory = mysqli_query($conn, $returnHistoryQuery);
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <title>Return Book | Smart Library</title>
  <link rel="stylesheet" href="../assets/styles/theme.css">
  <link rel="stylesheet" href="../assets/styles/layout.css">
  <link rel="stylesheet" href="../assets/styles/student.css">
  <style>
    .request-form {
      background: #fff;
      padding: 20px;
      border-radius: 12px;
      box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
      margin-bottom: 30px;
    }

    input,
    select {
      width: 100%;
      padding: 12px;
      margin: 10px 0;
      border-radius: 8px;
      border: 1.5px solid #ccc;
    }

    button {
      background: #3a5a40;
      color: white;
      padding: 12px 20px;
      border: none;
      border-radius: 6px;
      cursor: pointer;
    }
  </style>
</head>

<body>
  <?php include '../partials/sidebar.php'; ?>
  <div class="main-content">
    <header>
      <h1>Return a Book</h1>
      <p>Select the book you want to return 📖</p>
    </header>
    <?php if ($message != ""): ?>
      <div class="alert-box"><?php echo $message; ?></div><?php endif; ?>
    <section class="return-section">
      <h2>📌 Select Book to Return</h2>
      <form method="POST" action="return.php">
        <select name="book_id" required>
          <option value="">-- Choose Your Issued Book --</option>
          <?php while ($row = mysqli_fetch_assoc($issuedBooks)): ?>
            <option value="<?= $row['book_id'] ?>"><?= $row['title'] ?> (Due: <?= $row['due_date'] ?>)</option>
          <?php endwhile; ?>
        </select>
        <button type="submit">Return Book</button>
      </form>
    </section>
    <section class="book-list-section">
      <h2>📜 Return History</h2>
      <table>
        <thead>
          <tr>
            <th>Title</th>
            <th>Return Date</th>
            <th>Status</th>
          </tr>
        </thead>
        <tbody>
          <?php while ($row = mysqli_fetch_assoc($returnHistory)): ?>
            <tr>
              <td><?= $row['title'] ?></td>
              <td><?= $row['return_date'] ?></td>
              <td><span class="status"><?= ucfirst($row['status']) ?></span></td>
            </tr>
          <?php endwhile; ?>
        </tbody>
      </table>
    </section>
  </div>
  <script>
    document.getElementById("logoutBtn").addEventListener("click", () => {
      if (confirm("Are you sure you want to log out?")) {
        window.location.href = "../logout.php";
      }
    });
  </script>
</body>

</html>