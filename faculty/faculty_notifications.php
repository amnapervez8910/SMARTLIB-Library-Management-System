<?php
include 'faculty_header.php';
include __DIR__ . '/../config/db.php';

$faculty_id = $_SESSION['member_id'];

$query = "SELECT message, date_sent 
          FROM notifications 
          WHERE member_id = ? 
          ORDER BY date_sent DESC";

$stmt = $conn->prepare($query);
if (!$stmt) {
    die("Query Error: " . $conn->error);
}

$stmt->bind_param("i", $faculty_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<h2>Notifications</h2>

<ul>
    <?php if ($result->num_rows > 0): ?>
        <?php while ($row = $result->fetch_assoc()): ?>
            <li>
                <?= htmlspecialchars($row['message']) ?>
                <small>(<?= $row['date_sent'] ?>)</small>
            </li>
        <?php endwhile; ?>
    <?php else: ?>
        <li>No notifications found</li>
    <?php endif; ?>
</ul>