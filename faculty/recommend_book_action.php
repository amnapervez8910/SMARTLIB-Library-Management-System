<?php
include 'faculty_header.php';
include '../SmartLib.php';
$faculty_id = $_SESSION['member_id'];
$book_title = $_POST['book_title'] ?? '';
$author = $_POST['author'] ?? '';
$priority = $_POST['priority'] ?? 'normal';
$comments = $_POST['comments'] ?? '';
if (empty($book_title) || empty($author)) {
    echo "<script>alert('Please fill in all required fields'); window.history.back();</script>";
    exit();
}
$stmt = $conn->prepare("INSERT INTO book_recommendations (faculty_id, book_title, author, priority, comments, status, created_at) VALUES (?, ?, ?, ?, ?, 'pending', CURDATE())");
$stmt->bind_param("issss", $faculty_id, $book_title, $author, $priority, $comments);
if ($stmt->execute()) {
    echo "<script>alert('Book recommended successfully!'); window.location.href='faculty_dashboard.php';</script>";
} else {
    echo "<script>alert('Error: " . $conn->error . "'); window.history.back();</script>";
}
$stmt->close();
$conn->close();
