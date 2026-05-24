<?php
session_start();
include '../SmartLib.php';
if (!isset($_SESSION['name'])) {
  header("Location: ../login.html");
  exit();
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Browse Books | Smart Library</title>
  <link rel="stylesheet" href="../assets/styles/theme.css" />
  <link rel="stylesheet" href="../assets/styles/layout.css" />
  <link rel="stylesheet" href="../assets/styles/student.css" />
  <style>
    /* Status Badge Styling for consistency */
    .status {
      padding: 4px 10px;
      border-radius: 12px;
      font-size: 11px;
      font-weight: bold;
      text-transform: uppercase;
      display: inline-block;
    }

    .available {
      background: #dcfce7;
      color: #166534;
    }

    /* Green */
    .requested {
      background: #fef9c3;
      color: #854d0e;
    }

    /* Yellow */
    .issued,
    .borrowed {
      background: #fee2e2;
      color: #991b1b;
    }

    /* Red */

    .search-section {
      display: flex;
      gap: 15px;
      background: #fff;
      padding: 20px;
      border-radius: 12px;
      box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
      margin-bottom: 25px;
    }
  </style>
</head>

<body>
  <?php include '../partials/sidebar.php'; ?>
  <div class="main-content">
    <header>
      <h1>Browse Books</h1>
      <p>Find books by searching or filtering below 📖</p>
    </header>

    <section class="search-section">
      <input type="text" id="searchInput" placeholder="🔍 Search by title, author, or ISBN..." style="flex: 2;">

      <select id="categoryFilter" class="filter" style="flex: 1;">
        <option value="">All Categories</option>
        <?php
        $catQuery = "SELECT DISTINCT category FROM books";
        $catResult = $conn->query($catQuery);
        while ($cat = $catResult->fetch_assoc()) {
          $c = htmlspecialchars($cat['category']);
          echo "<option value='$c'>$c</option>";
        }
        ?>
      </select>

      <select id="statusFilter" class="filter" style="flex: 1;">
        <option value="">All Status</option>
        <option value="available">Available</option>
        <option value="requested">Requested</option>
        <option value="issued">Issued (Borrowed)</option>
      </select>
    </section>

    <section class="book-list-section">
      <h2>📚 Library Catalog</h2>
      <table id="bookTable">
        <thead>
          <tr>
            <th>Title</th>
            <th>Author</th>
            <th>Category</th>
            <th>ISBN</th>
            <th>Copies</th>
            <th>Status</th>
            <th>Action</th>
          </tr>
        </thead>
        <tbody id="bookData">
          <?php
          // We fetch the status directly from the books table
          $sql = "SELECT * FROM books ORDER BY title ASC";
          $result = $conn->query($sql);
          if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
              $status = strtolower($row['status']);
              $displayStatus = ucfirst($status);

              // Map 'borrowed' to 'issued' for consistency if needed
              if ($status == 'borrowed')
                $status = 'issued';

              echo "
                  <tr>
                    <td><strong>" . htmlspecialchars($row['title']) . "</strong></td>
                    <td>" . htmlspecialchars($row['author']) . "</td>
                    <td>" . htmlspecialchars($row['category']) . "</td>
                    <td>" . htmlspecialchars($row['isbn']) . "</td>
                    <td>" . $row['copies_available'] . "</td>
                    <td><span class='status $status'>$displayStatus</span></td>
                    <td>";

              if ($row['copies_available'] > 0 && $status !== 'issued') {
                echo "<a href='request.php?book_id={$row['book_id']}' class='btn-small' style='text-decoration:none; background:#3a5a40; color:white; padding:5px 10px; border-radius:4px; font-size:12px;'>Request</a>";
              } else {
                echo "<span style='color:#999; font-size:12px;'>Unavailable</span>";
              }

              echo "</td>
                  </tr>
              ";
            }
          } else {
            echo "<tr><td colspan='7' style='text-align:center;'>No books found.</td></tr>";
          }
          ?>
        </tbody>
      </table>
    </section>
  </div>

  <script>
    function filterBooks() {
      const search = document.getElementById("searchInput").value.toLowerCase();
      const category = document.getElementById("categoryFilter").value.toLowerCase();
      const status = document.getElementById("statusFilter").value.toLowerCase();
      const rows = document.querySelectorAll("#bookData tr");

      rows.forEach(row => {
        const title = row.cells[0].textContent.toLowerCase();
        const author = row.cells[1].textContent.toLowerCase();
        const bookCategory = row.cells[2].textContent.toLowerCase();
        const isbn = row.cells[3].textContent.toLowerCase();
        const bookStatus = row.cells[5].textContent.toLowerCase(); // Status is now in cell index 5

        const matchesSearch = title.includes(search) || author.includes(search) || isbn.includes(search);
        const matchesCategory = !category || bookCategory === category;
        const matchesStatus = !status || bookStatus.includes(status);

        row.style.display = (matchesSearch && matchesCategory && matchesStatus) ? "" : "none";
      });
    }

    document.getElementById("searchInput").addEventListener("input", filterBooks);
    document.getElementById("categoryFilter").addEventListener("change", filterBooks);
    document.getElementById("statusFilter").addEventListener("change", filterBooks);

    // Sidebar Logout Toggle
    const logoutBtn = document.getElementById("logoutBtn");
    if (logoutBtn) {
      logoutBtn.addEventListener("click", () => {
        if (confirm("Are you sure you want to log out?")) {
          window.location.href = "../logout.php";
        }
      });
    }
  </script>
</body>

</html>