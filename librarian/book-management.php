<?php
session_start();
if (!isset($_SESSION['member_id']) || $_SESSION['role'] !== 'librarian') {
  header("Location: ../login.html");
  exit();
}

include '../SmartLib.php';

/* =======================
   ADD / UPDATE LOGIC
======================= */
if (isset($_POST['save_book'])) {
  $title = $_POST['title'];
  $author = $_POST['author'];
  $category = $_POST['category'];
  $isbn = $_POST['isbn'];
  $publisher = $_POST['publisher'];
  $year = $_POST['year_published'];
  $copies = $_POST['copies_available'];
  $status = $_POST['status'];

  if (!empty($_POST['book_id'])) {
    // UPDATE existing book
    $stmt = $conn->prepare("UPDATE books SET title=?, author=?, category=?, isbn=?, publisher=?, year_published=?, copies_available=?, status=? WHERE book_id=?");
    $stmt->bind_param("sssssiisi", $title, $author, $category, $isbn, $publisher, $year, $copies, $status, $_POST['book_id']);
  } else {
    // INSERT new book
    $stmt = $conn->prepare("INSERT INTO books (title, author, category, isbn, publisher, year_published, copies_available, status) VALUES (?,?,?,?,?,?,?,?)");
    $stmt->bind_param("sssssiis", $title, $author, $category, $isbn, $publisher, $year, $copies, $status);
  }

  if ($stmt->execute()) {
    header("Location: book-management.php?msg=Success");
  } else {
    die("Error: " . $conn->error);
  }
  exit();
}

/* =======================
   DELETE LOGIC
======================= */
if (isset($_GET['delete'])) {
  $stmt = $conn->prepare("DELETE FROM books WHERE book_id=?");
  $stmt->bind_param("i", $_GET['delete']);
  $stmt->execute();
  header("Location: book-management.php?msg=Deleted");
  exit();
}

/* =======================
   SEARCH LOGIC
======================= */
$search = $_GET['search'] ?? '';
if (!empty($search)) {
  $query = "SELECT * FROM books WHERE title LIKE ? OR author LIKE ? OR category LIKE ? OR isbn LIKE ? ORDER BY book_id ASC";
  $stmt = $conn->prepare($query);
  $searchTerm = "%$search%";
  $stmt->bind_param("ssss", $searchTerm, $searchTerm, $searchTerm, $searchTerm);
  $stmt->execute();
  $books = $stmt->get_result();
} else {
  $books = $conn->query("SELECT * FROM books ORDER BY book_id ASC");
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <title>Book Management | SmartLib</title>
  <link rel="stylesheet" href="../assets/styles/theme.css">
  <link rel="stylesheet" href="../assets/styles/layout.css">
  <style>
    .content-header h1 {
      font-size: 28px;
      color: #064e3b;
      font-weight: 700;
      margin-bottom: 5px;
    }

    .content-header p {
      color: #555;
      margin-bottom: 25px;
    }

    .actions {
      display: flex;
      gap: 15px;
      margin-bottom: 25px;
    }

    .actions button,
    .btn-search,
    .save-btn {
      background: #3a5a40;
      color: #fff;
      border: none;
      padding: 10px 20px;
      border-radius: 6px;
      cursor: pointer;
      font-weight: 600;
      transition: 0.3s;
    }

    .actions button:hover,
    .btn-search:hover,
    .save-btn:hover {
      background: #064e3b;
    }

    .btn-delete-action {
      background: #b91c1c !important;
    }

    .search-container {
      background: #fff;
      padding: 15px;
      border-radius: 12px;
      box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
      margin-bottom: 20px;
      display: flex;
      gap: 10px;
    }

    .search-input {
      flex: 1;
      padding: 10px;
      border: 1px solid #ddd;
      border-radius: 6px;
    }

    .book-table {
      width: 100%;
      border-collapse: collapse;
      background: #fff;
      border-radius: 12px;
      overflow: hidden;
      box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    }

    .book-table th {
      background: #f3f4f6;
      padding: 12px;
      text-align: left;
      font-size: 14px;
    }

    .book-table td {
      padding: 12px;
      border-bottom: 1px solid #eee;
      font-size: 14px;
      color: #4b5563;
    }

    /* MODAL */
    .modal {
      display: none;
      position: fixed;
      z-index: 1000;
      left: 0;
      top: 0;
      width: 100%;
      height: 100%;
      background: rgba(0, 0, 0, 0.5);
      justify-content: center;
      align-items: center;
    }

    .modal-content {
      background: white;
      padding: 30px;
      border-radius: 12px;
      width: 450px;
      max-height: 90vh;
      overflow-y: auto;
    }

    .modal-content input,
    .modal-content select {
      width: 100%;
      padding: 10px;
      margin: 8px 0;
      border: 1px solid #ccc;
      border-radius: 6px;
      box-sizing: border-box;
    }

    .cancel-btn {
      background: #6b7280;
      color: white;
      border: none;
      padding: 12px;
      border-radius: 6px;
      cursor: pointer;
      width: 100%;
      margin-top: 10px;
      font-weight: 600;
    }

    .save-btn {
      width: 100%;
      padding: 12px;
      margin-top: 10px;
    }
  </style>
</head>

<body>
  <?php include '../partials/sidebar.php'; ?>

  <div class="main-content">
    <div class="content-header">
      <h1>📘 Book Management</h1>
      <p>Add, Modify, or Delete books from the system.</p>
    </div>

    <div class="actions">
      <button type="button" onclick="openModal()">➕ Add Book</button>
      <button type="button" onclick="promptEdit()">✏️ Modify Book</button>
      <button type="button" onclick="promptDelete()" class="btn-delete-action">🗑️ Delete Book</button>
    </div>

    <form method="GET" class="search-container">
      <input type="text" name="search" class="search-input" placeholder="Search by Title, Author, or ISBN..."
        value="<?= htmlspecialchars($search) ?>">
      <button type="submit" class="btn-search">Search</button>
      <?php if ($search): ?> <a href="book-management.php"
          style="padding:10px; text-decoration:none; color:#666;">Reset</a> <?php endif; ?>
    </form>

    <table class="book-table">
      <thead>
        <tr>
          <th>ID</th>
          <th>Title</th>
          <th>Author</th>
          <th>Category</th>
          <th>ISBN</th>
          <th>Copies</th>
          <th>Status</th>
        </tr>
      </thead>
      <tbody>
        <?php while ($row = $books->fetch_assoc()): ?>
          <tr data-id="<?= $row['book_id'] ?>" data-title="<?= htmlspecialchars($row['title'], ENT_QUOTES) ?>"
            data-author="<?= htmlspecialchars($row['author'] ?? '', ENT_QUOTES) ?>"
            data-category="<?= htmlspecialchars($row['category'] ?? '', ENT_QUOTES) ?>"
            data-isbn="<?= htmlspecialchars($row['isbn'] ?? '', ENT_QUOTES) ?>"
            data-publisher="<?= htmlspecialchars($row['publisher'] ?? '', ENT_QUOTES) ?>"
            data-year="<?= $row['year_published'] ?? '' ?>" data-copies="<?= $row['copies_available'] ?? 0 ?>"
            data-status="<?= $row['status'] ?>">
            <td><?= $row['book_id'] ?></td>
            <td><strong><?= htmlspecialchars($row['title']) ?></strong></td>
            <td><?= htmlspecialchars($row['author'] ?? 'N/A') ?></td>
            <td><?= htmlspecialchars($row['category'] ?? 'N/A') ?></td>
            <td><?= htmlspecialchars($row['isbn'] ?? 'N/A') ?></td>
            <td><?= $row['copies_available'] ?></td>
            <td><span
                style="text-transform: capitalize; font-weight: bold; color: <?= $row['status'] == 'available' ? '#059669' : '#dc2626' ?>;"><?= $row['status'] ?></span>
            </td>
          </tr>
        <?php endwhile; ?>
      </tbody>
    </table>
  </div>

  <div class="modal" id="bookModal">
    <div class="modal-content">
      <h2 id="modalTitle">Add Book</h2>
      <form method="post">
        <input type="hidden" name="book_id" id="book_id">
        <input type="text" name="title" id="title" placeholder="Book Title" required>
        <input type="text" name="author" id="author" placeholder="Author">
        <input type="text" name="category" id="category" placeholder="Category">
        <input type="text" name="isbn" id="isbn" placeholder="ISBN">
        <input type="text" name="publisher" id="publisher" placeholder="Publisher">
        <input type="number" name="year_published" id="year_published" placeholder="Year Published">
        <input type="number" name="copies_available" id="copies_available" placeholder="Number of Copies" required>
        <select name="status" id="status">
          <option value="available">available</option>
          <option value="borrowed">borrowed</option>
          <option value="requested">requested</option>
        </select>

        <button type="submit" name="save_book" class="save-btn">Save Book</button>
        <button type="button" onclick="closeModal()" class="cancel-btn">Cancel</button>
      </form>
    </div>
  </div>

  <script>
    const modal = document.getElementById("bookModal");

    function openModal() {
      document.getElementById("modalTitle").innerText = "Add New Book";
      document.getElementById("book_id").value = "";
      document.querySelector("form").reset(); // Clears all inputs
      modal.style.display = "flex";
    }

    function promptEdit() {
      const id = prompt("Enter Book ID to modify:");
      if (!id) return;
      const row = document.querySelector(`tr[data-id="${id}"]`);
      if (row) {
        document.getElementById("modalTitle").innerText = "Modify Book";
        document.getElementById("book_id").value = id;
        document.getElementById("title").value = row.getAttribute("data-title");
        document.getElementById("author").value = row.getAttribute("data-author");
        document.getElementById("category").value = row.getAttribute("data-category");
        document.getElementById("isbn").value = row.getAttribute("data-isbn");
        document.getElementById("publisher").value = row.getAttribute("data-publisher");
        document.getElementById("year_published").value = row.getAttribute("data-year");
        document.getElementById("copies_available").value = row.getAttribute("data-copies");
        document.getElementById("status").value = row.getAttribute("data-status");
        modal.style.display = "flex";
      } else { alert("❌ Book ID not found!"); }
    }

    function promptDelete() {
      const id = prompt("Enter Book ID to delete:");
      if (!id) return;
      if (confirm(`Are you sure you want to delete Book ID ${id}?`)) {
        window.location.href = `book-management.php?delete=${id}`;
      }
    }

    function closeModal() { modal.style.display = "none"; }
    window.onclick = (e) => { if (e.target === modal) closeModal(); };
  </script>
</body>

</html>