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
if (!$conn) {
    die("❌ Database connection failed");
}

$message = "";

// ===============================
// FETCH FACULTY DATA
// ===============================
$sql = "SELECT * FROM members WHERE member_id=? AND role='faculty'";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $faculty_id);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows === 0) {
    die("❌ Faculty not found");
}
$faculty = $result->fetch_assoc();

// ===============================
// UPDATE PROFILE
// ===============================
if (isset($_POST['save_changes'])) {
    $name = $conn->real_escape_string($_POST['name']);
    $email = $conn->real_escape_string($_POST['email']);
    $phone = $conn->real_escape_string($_POST['phone']);
    $address = $conn->real_escape_string($_POST['address']);
    $password = $conn->real_escape_string($_POST['password']);

    $update = "UPDATE members SET name=?, email=?, phone=?, address=?, password=? WHERE member_id=? AND role='faculty'";
    $updStmt = $conn->prepare($update);
    $updStmt->bind_param("sssssi", $name, $email, $phone, $address, $password, $faculty_id);
    if ($updStmt->execute()) {
        $message = "✅ Profile updated successfully!";
        $faculty = $conn->query("SELECT * FROM members WHERE member_id='$faculty_id'")->fetch_assoc();
    } else {
        $message = "❌ Error updating profile.";
    }
}

// ===============================
// DELETE ACCOUNT
// ===============================
if (isset($_POST['delete_account'])) {
    $conn->query("DELETE FROM issued_books WHERE member_id='$faculty_id'");
    $conn->query("DELETE FROM requests WHERE member_id='$faculty_id'");
    $delStmt = $conn->prepare("DELETE FROM members WHERE member_id=? AND role='faculty'");
    $delStmt->bind_param("i", $faculty_id);
    if ($delStmt->execute()) {
        session_destroy();
        header("Location: ../login.html");
        exit();
    } else {
        $message = "❌ Error deleting account.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Faculty Profile</title>
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
        textarea {
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

        button.delete-btn {
            background: #b91c1c;
            margin-top: 10px
        }

        button.delete-btn:hover {
            background: #7f1d1d
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
                <li><a href="issue_books.php">Issue Books</a></li>
                <li><a href="faculty_return.php">Return Books</a></li>
                <li><a href="recommend_book.php">Recommended Books</a></li>
                <li><a href="faculty_requests.php">Priority Requests</a></li>
                <li><a href="faculty_reports.php">Reports</a></li>
                <li><a class="active">My Profile</a></li>
            </ul>
        </div>
        <form action="../logout.php" method="post">
            <button class="logout-btn">Logout</button>
        </form>
    </div>

    <div class="main-content">
        <header>
            <h1>My Profile</h1>
            <p>Manage your faculty account information</p>
        </header>

        <div class="card">
            <?php if ($message): ?>
                <div class="msg"><?= $message ?></div><?php endif; ?>

            <form method="post">
                <label>Full Name</label>
                <input type="text" name="name" value="<?= htmlspecialchars($faculty['name']) ?>" required>

                <label>Email Address</label>
                <input type="email" name="email" value="<?= htmlspecialchars($faculty['email']) ?>" required>

                <label>Phone Number</label>
                <input type="text" name="phone" value="<?= htmlspecialchars($faculty['phone']) ?>">

                <label>Address</label>
                <textarea name="address" rows="3"><?= htmlspecialchars($faculty['address']) ?></textarea>

                <label>Password</label>
                <input type="text" name="password" value="<?= htmlspecialchars($faculty['password']) ?>" required>

                <button type="submit" name="save_changes">Save Changes</button>
            </form>
        </div>

        <div class="card">
            <h3>Delete Account</h3>
            <p>Warning: This action cannot be undone.</p>
            <form method="post">
                <input type="hidden" name="delete_account" value="1">
                <button type="submit" class="delete-btn"
                    onclick="return confirm('Are you sure you want to delete your account?')">Delete Account</button>
            </form>
        </div>

    </div>

</body>

</html>

<?php
$stmt->close();
$conn->close();
?>