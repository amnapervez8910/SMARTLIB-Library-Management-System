<?php
session_start();
include '../SmartLib.php';

if (!isset($_SESSION['name']) || !isset($_SESSION['member_id'])) {
  header("Location: ../login.html");
  exit();
}

$name = $_SESSION['name'];
$member_id = $_SESSION['member_id'];

$q1 = "SELECT COUNT(*) AS borrowed FROM issued_books 
       WHERE member_id = $member_id AND status IN ('issued','overdue')";
$res1 = $conn->query($q1);
$borrowed = ($res1 && $res1->num_rows > 0) ? $res1->fetch_assoc()['borrowed'] : 0;

$q2 = "SELECT COUNT(*) AS overdue FROM issued_books 
       WHERE member_id = $member_id AND status='overdue'";
$res2 = $conn->query($q2);
$overdue = ($res2 && $res2->num_rows > 0) ? $res2->fetch_assoc()['overdue'] : 0;

$q3 = "SELECT due_date, return_date, status FROM issued_books 
       WHERE member_id = $member_id";
$res3 = $conn->query($q3);

$total_fine = 0;
$today = date("Y-m-d");

if ($res3 && $res3->num_rows > 0) {
  while ($row = $res3->fetch_assoc()) {
    if ($row['status'] != "returned") {
      $due = $row['due_date'];
      if (!empty($due) && $today > $due) {
        $days = (strtotime($today) - strtotime($due)) / 86400;
        $total_fine += ($days * 10);
      }
    }
  }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Student Dashboard | Smart Library</title>
  <link rel="stylesheet" href="../assets/styles/theme.css">
  <link rel="stylesheet" href="../assets/styles/layout.css">
  <link rel="stylesheet" href="../assets/styles/student.css">
</head>

<body>
  <?php include '../partials/sidebar.php'; ?>
  <div class="main-content">
    <header>
      <h1>Welcome, <?php echo htmlspecialchars($name); ?> 👋</h1>
      <?php if (isset($_SESSION['last_visit'])): ?>
        <p style="color: #555; font-size: 14px;">Last logged in: <?= $_SESSION['last_visit'] ?></p>
      <?php endif; ?>
    </header>
    <div class="cards">
      <div class="card">
        <h3>📚 Books Borrowed</h3>
        <p><?php echo $borrowed; ?></p>
      </div>
      <div class="card">
        <h3>⏳ Overdue</h3>
        <p><?php echo $overdue; ?></p>
      </div>
      <div class="card">
        <h3>💰 Fine</h3>
        <p>Rs. <?php echo $total_fine; ?></p>
      </div>
    </div>
    <section class="search-section">
      <input type="text" id="searchInput" placeholder="🔍 Search books by title or author...">
    </section>
    <section class="book-list-section">
      <h2>📚 Available Books</h2>
      <?php
      $sql = "SELECT title, author, category, status FROM books WHERE status='available'";
      $result = $conn->query($sql);
      ?>
      <table id="bookTable">
        <thead>
          <tr>
            <th>Title</th>
            <th>Author</th>
            <th>Category</th>
            <th>Status</th>
          </tr>
        </thead>
        <tbody>
          <?php if ($result && $result->num_rows > 0): ?>
            <?php while ($row = $result->fetch_assoc()): ?>
              <tr>
                <td><?= htmlspecialchars($row['title']) ?></td>
                <td><?= htmlspecialchars($row['author']) ?></td>
                <td><?= htmlspecialchars($row['category']) ?></td>
                <td><span class="status <?= $row['status'] ?>"><?= ucfirst($row['status']) ?></span></td>
              </tr>
            <?php endwhile; ?>
          <?php else: ?>
            <tr>
              <td colspan="4">No available books at the moment.</td>
            </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </section>
  </div>
  <script>
    document.getElementById("date").textContent = new Date().toDateString();
    document.getElementById("logoutBtn").addEventListener("click", () => {
      if (confirm("Are you sure you want to log out?")) {
        alert("You have been logged out successfully!");
        window.location.href = "../login.html";
      }
    });
    document.getElementById("searchInput").addEventListener("keyup", function () {
      let filter = this.value.toLowerCase();
      let rows = document.querySelectorAll("#bookTable tbody tr");
      rows.forEach(row => {
        let text = row.textContent.toLowerCase();
        row.style.display = text.includes(filter) ? "" : "none";
      });
    });
  </script>
</body>

</html>