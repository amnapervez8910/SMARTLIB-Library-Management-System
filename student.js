// Show today's date
document.getElementById('date').textContent = new Date().toDateString();

// Live search filter
const searchInput = document.getElementById('searchInput');
const rows = document.querySelectorAll('#bookTable tbody tr');

searchInput.addEventListener('input', () => {
  const filter = searchInput.value.toLowerCase();
  rows.forEach(row => {
    const title = row.cells[0].textContent.toLowerCase();
    const author = row.cells[1].textContent.toLowerCase();
    row.style.display = title.includes(filter) || author.includes(filter) ? '' : 'none';
  });
});
