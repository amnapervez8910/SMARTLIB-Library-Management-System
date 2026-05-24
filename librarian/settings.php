<?php
session_start();

// 1. Security Check
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'librarian') {
    header("Location: ../login.html");
    exit();
}

include '../SmartLib.php';

$id = $_SESSION['member_id'];
$msg = "";

/* FETCH CURRENT DATA */
$stmt = $conn->prepare("SELECT name, email FROM members WHERE member_id = ?");
if (!$stmt) {
    die("Internal Server Error: " . $conn->error);
}
$stmt->bind_param("i", $id);
$stmt->execute();
$data = $stmt->get_result()->fetch_assoc();

if (!$data) {
    die("Error: Librarian profile not found for ID: " . htmlspecialchars($id));
}

/* UPDATE PROFILE LOGIC (Unchanged) */
if (isset($_POST['update_profile'])) {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $stmt = $conn->prepare("UPDATE members SET name = ?, email = ? WHERE member_id = ?");
    $stmt->bind_param("ssi", $name, $email, $id);
    if ($stmt->execute()) {
        $msg = "✅ Profile updated successfully!";
        $data['name'] = $name;
        $data['email'] = $email;
    }
}

/* UPDATE PASSWORD LOGIC (Unchanged) */
if (isset($_POST['update_password'])) {
    $current = $_POST['current'];
    $new = $_POST['new'];
    $confirm = $_POST['confirm'];

    if ($new === $confirm) {
        $stmt = $conn->prepare("SELECT password FROM members WHERE member_id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $userRow = $stmt->get_result()->fetch_assoc();
        $stored_password = $userRow['password'];

        $is_current_valid = false;
        if (str_starts_with($stored_password, '$2y$')) {
            if (password_verify($current, $stored_password)) {
                $is_current_valid = true;
            }
        } else {
            if ($current === $stored_password) {
                $is_current_valid = true;
            }
        }

        if ($is_current_valid) {
            $newHash = password_hash($new, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("UPDATE members SET password = ? WHERE member_id = ?");
            $stmt->bind_param("si", $newHash, $id);
            if ($stmt->execute()) {
                $msg = "✅ Password updated successfully!";
            }
        } else {
            $msg = "❌ Current password is incorrect!";
        }
    } else {
        $msg = "❌ New passwords do not match!";
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Settings | SmartLib</title>
    <link rel="stylesheet" href="../assets/styles/theme.css">
    <link rel="stylesheet" href="../assets/styles/layout.css">
    <style>
        /* 1. Header Standardizing */
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

        /* 2. Success/Error Alerts */
        .msg-box {
            padding: 12px;
            margin-bottom: 25px;
            border-radius: 8px;
            font-weight: 600;
            border-left: 5px solid transparent;
        }

        .msg-success {
            background: #dcfce7;
            color: #166534;
            border-left-color: #166534;
        }

        .msg-error {
            background: #fee2e2;
            color: #991b1b;
            border-left-color: #991b1b;
        }

        /* 3. Settings Sections */
        .settings-section {
            background: #fff;
            padding: 30px;
            border-radius: 12px;
            margin-bottom: 25px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
        }

        .settings-section h2 {
            color: #064e3b;
            font-size: 18px;
            margin-bottom: 20px;
            border-bottom: 1px solid #f3f4f6;
            padding-bottom: 10px;
        }

        /* 4. Form Controls */
        label {
            display: block;
            font-weight: 600;
            font-size: 14px;
            margin-bottom: 8px;
            color: #4b5563;
        }

        input {
            width: 100%;
            padding: 12px;
            margin-bottom: 20px;
            border: 1px solid #d1d5db;
            border-radius: 8px;
            font-family: inherit;
            box-sizing: border-box;
        }

        input:focus {
            outline: none;
            border-color: #3a5a40;
            box-shadow: 0 0 0 3px rgba(58, 90, 64, 0.1);
        }

        /* 5. Button Standardizing (Sage Green) */
        .btn {
            background: #3a5a40;
            color: #fff;
            border: none;
            padding: 12px 24px;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
            font-size: 14px;
            transition: 0.3s;
        }

        .btn:hover {
            background: #064e3b;
            transform: translateY(-1px);
        }
    </style>
</head>

<body>

    <?php include '../partials/sidebar.php'; ?>

    <div class="main-content">
        <div class="content-header">
            <h1>⚙️ Account Settings</h1>
            <p>Update your personal information and manage your login security.</p>
        </div>

        <?php if ($msg): ?>
            <div class="msg-box <?= strpos($msg, '✅') !== false ? 'msg-success' : 'msg-error' ?>">
                <?= $msg ?>
            </div>
        <?php endif; ?>

        <div style="max-width: 800px;">
            <div class="settings-section">
                <h2>👤 Profile Settings</h2>
                <form method="post">
                    <label>Full Name</label>
                    <input type="text" name="name" value="<?= htmlspecialchars($data['name']) ?>" required>

                    <label>Email Address</label>
                    <input type="email" name="email" value="<?= htmlspecialchars($data['email']) ?>" required>

                    <button type="submit" name="update_profile" class="btn">Save Profile Changes</button>
                </form>
            </div>

            <div class="settings-section">
                <h2>🔒 Security</h2>
                <form method="post">
                    <label>Current Password</label>
                    <input type="password" name="current" placeholder="••••••••" required>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                        <div>
                            <label>New Password</label>
                            <input type="password" name="new" placeholder="••••••••" required>
                        </div>
                        <div>
                            <label>Confirm New Password</label>
                            <input type="password" name="confirm" placeholder="••••••••" required>
                        </div>
                    </div>

                    <button type="submit" name="update_password" class="btn">Update Password</button>
                </form>
            </div>
        </div>
    </div>

</body>

</html>