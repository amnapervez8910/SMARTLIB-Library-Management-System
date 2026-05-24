<?php
session_start();
if (!isset($_SESSION['member_id'])) {
    header("Location: ../login.html");
    exit();
}

$member_id = $_SESSION['member_id'];
$conn = new mysqli("localhost", "root", "", "smartlib");

if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

$message = "";

// 1. DYNAMIC CATEGORY FETCHING (HCI Lab 10 Principle: Single Source of Truth)
// This replaces hardcoded lists with a dynamic query from the catalog
$cat_query = "SELECT DISTINCT category FROM books ORDER BY category ASC";
$categories_result = $conn->query($cat_query);

// 2. PROCESS FORM SUBMISSION
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $title = trim($_POST["title"]);
    $author = trim($_POST["author"]);
    $category = trim($_POST["category"]);

    // Check if a faculty member already has a pending priority request for this book
    $prioCheck = $conn->prepare("SELECT request_id FROM requests WHERE book_title = ? AND priority = 'priority' AND status = 'pending'");
    $prioCheck->bind_param("s", $title);
    $prioCheck->execute();
    $prioResult = $prioCheck->get_result();

    if ($prioResult->num_rows > 0) {
        $message = "<div class='alert error'>⚠️ This book is currently reserved for Faculty priority. Please try again later.</div>";
    } else {
        // Verify book existence and status in the library catalog
        $stmt = $conn->prepare("SELECT book_id, status FROM books WHERE title=? AND author=? AND category=?");
        $stmt->bind_param("sss", $title, $author, $category);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows == 0) {
            $message = "<div class='alert error'>❌ Book not found in library database for the selected category!</div>";
        } else {
            $book = $result->fetch_assoc();
            $book_id = $book['book_id'];

            // Allow request only if book is available or recently returned
            $valid_statuses = ['available', 'requested', 'returned'];
            if (in_array($book['status'], $valid_statuses)) {
                
                // Start Transaction for data integrity
                $conn->begin_transaction();
                try {
                    // Insert into requests table
                    $insertReq = $conn->prepare("INSERT INTO requests (member_id, book_title, author, status) VALUES (?, ?, ?, 'pending')");
                    $insertReq->bind_param("iss", $member_id, $title, $author);
                    $insertReq->execute();

                    // Update book status to 'requested'
                    $updateBook = $conn->prepare("UPDATE books SET status='requested' WHERE book_id=?");
                    $updateBook->bind_param("i", $book_id);
                    $updateBook->execute();

                    // Check and Update/Insert issued_books record
                    $checkIssued = $conn->prepare("SELECT * FROM issued_books WHERE book_id=? AND member_id=?");
                    $checkIssued->bind_param("ii", $book_id, $member_id);
                    $checkIssued->execute();
                    $issuedResult = $checkIssued->get_result();

                    if ($issuedResult->num_rows > 0) {
                        $updateIss = $conn->prepare("UPDATE issued_books SET status='requested' WHERE book_id=? AND member_id=?");
                        $updateIss->bind_param("ii", $book_id, $member_id);
                        $updateIss->execute();
                    } else {
                        $insertIss = $conn->prepare("INSERT INTO issued_books (member_id, book_id, issue_date, status) VALUES (?, ?, CURDATE(), 'requested')");
                        $insertIss->bind_param("ii", $member_id, $book_id);
                        $insertIss->execute();
                    }

                    $conn->commit();
                    $message = "<div class='alert success'>✅ Book request submitted successfully!</div>";
                } catch (Exception $e) {
                    $conn->rollback();
                    $message = "<div class='alert error'>❌ Error processing request. Please try again.</div>";
                }
            } else {
                $message = "<div class='alert warning'>⚠️ Book is currently not available for requesting.</div>";
            }
        }
    }
}

// 3. FETCH PREVIOUS REQUESTS FOR DISPLAY
$req_sql = "SELECT * FROM requests WHERE member_id=? ORDER BY request_id DESC";
$stmt_req = $conn->prepare($req_sql);
$stmt_req->bind_param("i", $member_id);
$stmt_req->execute();
$requests = $stmt_req->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Request Book | Smart Library</title>
    <link rel="stylesheet" href="../assets/styles/theme.css">
    <link rel="stylesheet" href="../assets/styles/layout.css">
    <link rel="stylesheet" href="../assets/styles/student.css">
    <style>
        .request-form {
            background: #fff;
            padding: 20px;
            border-radius: 12px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1); /* Lab 12: Drop Shadow Effect */
            margin-bottom: 30px;
        }
        input, select {
            width: 100%;
            padding: 12px;
            margin: 10px 0;
            border-radius: 8px;
            border: 1.5px solid #ccc; /* Lab 12: Stroke Effect */
        }
        button {
            background: #3a5a40;
            color: white;
            padding: 12px 20px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 700; /* Lab 11: Text Hierarchy */
        }
        .alert { padding: 15px; border-radius: 8px; margin-bottom: 20px; font-weight: 600; }
        .success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .error { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        .warning { background: #fff3cd; color: #856404; border: 1px solid #ffeeba; }
    </style>
</head>
<body>
    <?php include '../partials/sidebar.php'; ?>
    
    <div class="main-content">
        <header>
            <h1>Request a Book</h1>
            <p>Submit a request for your desired book 📖</p>
        </header>

        <?= $message ?>

        <section class="request-form">
            <h2>📌 New Book Request</h2>
            <form method="POST">
                <input type="text" name="title" placeholder="Book Title" required>
                <input type="text" name="author" placeholder="Author Name" required>
                
                <select name="category" required>
                    <option value="">-- Select Category --</option>
                    <?php 
                    if ($categories_result && $categories_result->num_rows > 0) {
                        while ($cat_row = $categories_result->fetch_assoc()) {
                            $cat_name = htmlspecialchars($cat_row['category']);
                            echo "<option value=\"$cat_name\">$cat_name</option>";
                        }
                    } else {
                        echo "<option value=\"\">No categories available in catalog</option>";
                    }
                    ?>
                </select>

                <button type="submit">Submit Request</button>
            </form>
        </section>

        <section class="book-list-section">
            <h2>📜 My Previous Requests</h2>
            <table>
                <thead>
                    <tr>
                        <th>Book Title</th>
                        <th>Author</th>
                        <th>Request Date</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($requests->num_rows > 0): ?>
                        <?php while ($row = $requests->fetch_assoc()): ?>
                            <tr>
                                <td><?= htmlspecialchars($row['book_title']) ?></td>
                                <td><?= htmlspecialchars($row['author']) ?></td>
                                <td><?= $row['request_date'] ?></td>
                                <td><span class="status-badge <?= strtolower($row['status']) ?>"><?= ucfirst($row['status']) ?></span></td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="4" style="text-align: center;">No requests found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </section>
    </div>
</body>
</html>