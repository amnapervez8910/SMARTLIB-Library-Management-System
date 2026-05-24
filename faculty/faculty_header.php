<?php
session_start();
if (!isset($_SESSION['member_id']) || $_SESSION['role'] !== 'faculty') {
    header("Location: ../login.html");
    exit();
}
?>
<div class="sidebar">
    <h2>Faculty Panel</h2>
    <a href="faculty.php" class="active">Dashboard</a>
    <a href="recommend_book.php">Recommend Book</a>
    <a href="faculty_requests.php">Requests</a>
    <a href="faculty_notifications.php">Notifications</a>
    <a href="faculty_reports.php">Reports</a>
    <a href="issue_books.php">Issued Books</a>
    <a href="faculty_return.php">Return Books</a>
    <a href="../logout.php">Logout</a>
</div>