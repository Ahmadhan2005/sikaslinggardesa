// Remove old logout confirmation
// Replace with direct navigation
document.querySelectorAll('a[href="logout.php"]').forEach(link => {
    link.addEventListener('click', (e) => {
        e.preventDefault();
        window.location.href = 'logout.php';
    });
});
