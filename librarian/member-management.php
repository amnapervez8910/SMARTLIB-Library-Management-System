<?php
session_start();
if (!isset($_SESSION['member_id']) || $_SESSION['role'] !== 'librarian') {
  header("Location: ../login.html");
  exit();
}
include '../SmartLib.php';
$msg = $_GET['msg'] ?? "";

/* Logic remains the same (Add, Modify, Delete, Search) */
// ... [Keep your PHP logic exactly as it is] ...

/* SEARCH LOGIC */
$search = $_GET['search'] ?? '';
if (!empty($search)) {
  $query = "SELECT * FROM members WHERE name LIKE ? OR email LIKE ? OR member_id = ? ORDER BY member_id DESC";
  $stmt = $conn->prepare($query);
  $term = "%$search%";
  $stmt->bind_param("sss", $term, $term, $search);
  $stmt->execute();
  $members = $stmt->get_result();
} else {
  $members = $conn->query("SELECT * FROM members ORDER BY member_id DESC");
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <title>Member Management | SmartLib</title>
  <link rel="stylesheet" href="../assets/styles/theme.css">
  <link rel="stylesheet" href="../assets/styles/layout.css">
  <link rel="stylesheet" href="../assets/styles/student.css">
  <style>
    /* Unifying the Header with Dashboard */
    .content-header {
      margin-bottom: 30px;
    }

    .content-header h1 {
      font-size: 28px;
      color: #064e3b;
      font-weight: 700;
      margin-bottom: 5px;
    }

    .content-header p {
      color: #555;
      font-size: 16px;
    }

    /* Standardizing the Action Boxes */
    .action-section {
      background: #fff;
      border-radius: 12px;
      box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
      padding: 25px;
      margin-bottom: 25px;
    }

    .action-section h2 {
      color: #064e3b;
      font-size: 18px;
      margin-bottom: 15px;
      display: flex;
      align-items: center;
      gap: 10px;
    }

    /* Standardizing Inputs & Buttons */
    form {
      display: flex;
      flex-wrap: wrap;
      gap: 10px;
    }

    input,
    select {
      padding: 10px;
      border: 1px solid #ddd;
      border-radius: 6px;
      font-size: 14px;
    }

    /* Dashboard Button Style (btn-go) */
    button {
      background: #3a5a40;
      color: white;
      border: none;
      padding: 8px 18px;
      border-radius: 6px;
      cursor: pointer;
      font-weight: 600;
      font-size: 14px;
      transition: background 0.3s;
    }

    button:hover {
      background: #064e3b;
    }

    /* Delete Button established style */
    .btn-delete {
      background: #b91c1c !important;
    }

    .btn-delete:hover {
      background: #7f1d1d !important;
    }

    /* Search Box styling consistent with Book Management */
    .search-box {
      background: #fff;
      padding: 15px;
      border-radius: 12px;
      box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
      margin-bottom: 20px;
      display: flex;
      gap: 10px;
      border: 1px solid #eee;
    }

    .search-box input {
      flex: 1;
    }

    .reset-link {
      background: #f3f4f6;
      color: #374151;
      padding: 8px 15px;
      border-radius: 6px;
      text-decoration: none;
      font-size: 13px;
      font-weight: 600;
    }

    /* Table styling to match Recent Activity dashboard */
    table {
      width: 100%;
      border-collapse: collapse;
      background: #fff;
      border-radius: 12px;
      overflow: hidden;
      box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    }

    th {
      background: #f3f4f6;
      color: #374151;
      padding: 12px;
      text-align: left;
      font-size: 14px;
      font-weight: 600;
      border-bottom: 1px solid #eee;
    }

    td {
      padding: 12px;
      border-bottom: 1px solid #eee;
      font-size: 14px;
      color: #4b5563;
    }

    tr:hover {
      background: #f9fafb;
    }

    /* Status Badges */
    .role-badge {
      padding: 4px 10px;
      border-radius: 20px;
      font-size: 11px;
      font-weight: bold;
      text-transform: uppercase;
    }

    .role-librarian {
      background: #dcfce7;
      color: #166534;
    }

    .role-student {
      background: #e0f2fe;
      color: #075985;
    }

    .role-faculty {
      background: #fef3c7;
      color: #92400e;
    }
  </style>
</head>

<body>

  <?php include '../partials/sidebar.php'; ?>

  <div class="main-content">
    <div class="content-header">
      <h1>👥 Member Management</h1>
      <p>Manage library access for students, faculty, and staff.</p>
    </div>

    <?php if ($msg): ?>
      <div
        style="padding:12px; background:#dcfce7; color:#166534; margin-bottom:20px; border-radius:8px; border-left: 5px solid #166534;">
        <b>✅ <?= htmlspecialchars($msg) ?></b>
      </div>
    <?php endif; ?>

    <div class="action-section">
      <h2>➕ Add New Member</h2>
      <form method="post">
        <input type="text" name="name" placeholder="Full Name" required>
        <input type="email" name="email" placeholder="Email" required>
        <input type="text" name="phone" placeholder="Phone">
        <input type="text" name="address" placeholder="Address">
        <select name="role" required>
          <option value="student">Student</option>
          <option value="faculty">Faculty</option>
          <option value="librarian">Librarian</option>
        </select>
        <button type="submit" name="add_member">Add Member</button>
      </form>
    </div>

    <div style="display: flex; gap: 20px;">
      <div class="action-section" style="flex: 2;">
        <h2>✏️ Modify Member</h2>
        <form method="post">
          <input type="number" name="member_id" placeholder="ID" required style="width: 80px; flex: none;">
          <input type="text" name="name" placeholder="New Name" style="flex: 1;">
          <input type="email" name="email" placeholder="New Email" style="flex: 1;">
          <select name="role" style="flex: 1;">
            <option value="">Change Role (Optional)</option>
            <option value="student">Student</option>
            <option value="faculty">Faculty</option>
            <option value="librarian">Librarian</option>
          </select>
          <button type="submit" name="modify_member">Update</button>
        </form>
      </div>

      <div class="action-section" style="flex: 1;">
        <h2>🗑️ Delete Member</h2>
        <form method="post" onsubmit="return confirm('Confirm Delete?');">
          <input type="number" name="delete_id" placeholder="Enter Member ID" required style="flex: 1;">
          <button type="submit" name="delete_member" class="btn-delete">Delete</button>
        </form>
      </div>
    </div>

    <div class="action-section">
      <h2>📋 Member Registry</h2>

      <form method="GET" class="search-box">
        <input type="text" name="search" placeholder="Search by Name, Email, or ID..."
          value="<?= htmlspecialchars($search) ?>">
        <button type="submit">Search</button>
        <?php if ($search): ?>
          <a href="member-management.php" class="reset-link">Clear</a>
        <?php endif; ?>
      </form>

      <table>
        <thead>
          <tr>
            <th>ID</th>
            <th>Name</th>
            <th>Email</th>
            <th>Role</th>
            <th>Date Joined</th>
          </tr>
        </thead>
        <tbody>
          <?php if ($members->num_rows > 0): ?>
            <?php while ($m = $members->fetch_assoc()): ?>
              <tr>
                <td><strong>#<?= $m['member_id'] ?></strong></td>
                <td><?= htmlspecialchars($m['name']) ?></td>
                <td><?= htmlspecialchars($m['email']) ?></td>
                <td>
                  <span class="role-badge role-<?= strtolower($m['role']) ?>">
                    <?= $m['role'] ?>
                  </span>
                </td>
                <td><?= date("M d, Y", strtotime($m['registration_date'])) ?></td>
              </tr>
            <?php endwhile; ?>
          <?php else: ?>
            <tr>
              <td colspan="5" style="text-align:center; padding:30px;">No members found in the records.</td>
            </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</body>

</html>