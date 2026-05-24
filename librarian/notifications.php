<?php
session_start();

// 1. Security Check: Only Librarians allowed
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'librarian') {
  header("Location: ../login.html");
  exit();
}

include '../SmartLib.php';

$name = $_SESSION['name'];

/* ======================
   DELETE NOTIFICATION
====================== */
if (isset($_GET['delete'])) {
  $id = intval($_GET['delete']);
  // Using your actual column name: notification_id
  $stmt = $conn->prepare("DELETE FROM notifications WHERE notification_id = ?");
  $stmt->bind_param("i", $id);
  $stmt->execute();
  header("Location: notifications.php");
  exit();
}

/* ======================
   FETCH NOTIFICATIONS
====================== */
// FIXED: Changed 'created_at' to 'date_sent' to match your table
$query = "SELECT * FROM notifications ORDER BY date_sent DESC";
$notifications = $conn->query($query);

// Safety check to prevent the "bool" error if the query fails for any reason
if (!$notifications) {
  die("Query Failed: " . $conn->error);
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <title>Notifications | SmartLib</title>
  <link rel="stylesheet" href="../assets/styles/theme.css">
  <link rel="stylesheet" href="../assets/styles/layout.css">
</head>

<body>

  <?php include '../partials/sidebar.php'; ?>

  <div class="main-content">
    <header>
      <h1>🔔 Notifications</h1>
      <p>View and manage library alerts and overdue reminders.</p>
    </header>

    <div class="notification-container" style="margin-top: 20px;">

      <?php if ($notifications->num_rows === 0): ?>
        <div class="card" style="text-align:center; padding: 40px;">
          <p>No notifications available at this time.</p>
        </div>
      <?php else: ?>
        <?php while ($n = $notifications->fetch_assoc()): ?>
          <div class="card"
            style="margin-bottom: 15px; display: flex; justify-content: space-between; align-items: center; padding: 20px; border-left: 5px solid #064e3b;">
            <div style="flex: 1;">
              <h3 style="margin: 0 0 5px 0; color: #064e3b;">Library Alert</h3>

              <p style="margin: 0; font-size: 1.1em; color: #374151;">
                <?= htmlspecialchars($n['message']) ?>
              </p>

              <small style="display: block; margin-top: 10px; color: #6b7280; font-weight: bold;">
                Sent on: <?= htmlspecialchars($n['date_sent']) ?>
              </small>
            </div>

            <div style="margin-left: 20px;">
              <a href="?delete=<?= $n['notification_id'] ?>"
                style="background: #fee2e2; color: #dc2626; padding: 10px 15px; border-radius: 6px; text-decoration: none; font-weight: bold; font-size: 0.9em;"
                onclick="return confirm('Delete this notification?')">
                Dismiss
              </a>
            </div>
          </div>
        <?php endwhile; ?>
      <?php endif; ?>

    </div>
  </div>

</body>

</html>