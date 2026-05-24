<?php
$current = basename($_SERVER['PHP_SELF']);
$path = $_SERVER['PHP_SELF'];
$isStudent = strpos($path, '/student/') !== false;
$isLibrarian = strpos($path, '/librarian/') !== false;
$isFaculty = strpos($path, '/faculty/') !== false;
$logoutHref = ($isStudent || $isLibrarian || $isFaculty) ? '../logout.php' : 'logout.php';
?>
<div class="sidebar">
  <h2>📚 SmartLib</h2>
  <ul>
    <?php if ($isStudent): ?>
      <li class="<?php echo $current === 'student.php' ? 'active' : ''; ?>">
        <a href="student.php">Dashboard</a>
      </li>
      <li class="<?php echo $current === 'browse.php' ? 'active' : ''; ?>">
        <a href="browse.php">Browse Books</a>
      </li>
      <li class="<?php echo $current === 'mybooks.php' ? 'active' : ''; ?>">
        <a href="mybooks.php">My Books</a>
      </li>
      <li class="<?php echo $current === 'request.php' ? 'active' : ''; ?>">
        <a href="request.php">Request Book</a>
      </li>
      <li class="<?php echo $current === 'return.php' ? 'active' : ''; ?>">
        <a href="return.php">Return Book</a>
      </li>
      <li class="<?php echo $current === 'profile.php' ? 'active' : ''; ?>">
        <a href="profile.php">Profile</a>
      </li>

    <?php elseif ($isLibrarian): ?>
      <li class="<?php echo $current === 'librarian-dashboard.php' ? 'active' : ''; ?>">
        <a href="librarian-dashboard.php">Dashboard</a>
      </li>
      <li class="<?php echo $current === 'book-management.php' ? 'active' : ''; ?>">
        <a href="book-management.php">Book Management</a>
      </li>
      <li class="<?php echo $current === 'member-management.php' ? 'active' : ''; ?>">
        <a href="member-management.php">Member Management</a>
      </li>
      <li class="<?php echo $current === 'issue-return.php' ? 'active' : ''; ?>">
        <a href="issue-return.php">Issue & Return</a>
      </li>
      <li class="<?php echo $current === 'reservations.php' ? 'active' : ''; ?>">
        <a href="reservations.php">Reservations</a>
      </li>
      <li class="<?php echo $current === 'reports.php' ? 'active' : ''; ?>">
        <a href="reports.php">Reports & Analytics</a>
      </li>
      <li class="<?php echo $current === 'settings.php' ? 'active' : ''; ?>">
        <a href="settings.php">Settings</a>
      </li>

    <?php elseif ($isFaculty): ?>
      <li class="<?php echo $current === 'faculty_dashboard.php' ? 'active' : ''; ?>">
        <a href="faculty_dashboard.php">Dashboard</a>
      </li>
      <li class="<?php echo $current === 'recommend_book.php' ? 'active' : ''; ?>">
        <a href="recommend_book.php">Recommend Book</a>
      </li>
      <li class="<?php echo $current === 'faculty_requests.php' ? 'active' : ''; ?>">
        <a href="faculty_requests.php">Requests</a>
      </li>
      <li class="<?php echo $current === 'faculty_reports.php' ? 'active' : ''; ?>">
        <a href="faculty_reports.php">Reports</a>
      </li>
      <li class="<?php echo $current === 'faculty_notifications.php' ? 'active' : ''; ?>">
        <a href="faculty_notifications.php">Notifications</a>
      </li>
      <li class="<?php echo $current === 'issue_books.php' ? 'active' : ''; ?>">
        <a href="issue_books.php">Issued Books</a>
      </li>
    <?php endif; ?>
  </ul>
  <button id="logoutBtn" class="logout-btn">Logout</button>
  <script>
    document.getElementById("logoutBtn").addEventListener("click", () => {
      if (confirm("Are you sure you want to log out?")) {
        window.location.href = "<?php echo $logoutHref; ?>";
      }
    });
  </script>
</div>