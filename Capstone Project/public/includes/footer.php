</main>
</div>
</div>

<!-- Font Awesome for icons -->
<script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>

<!-- Template Scripts -->
<script src="../../assets/js/script-updated.js"></script>

<script>
    // Logout function
    function logout() {
        if (confirm('Are you sure you want to logout?')) {
            // Use fetch to destroy session first, then redirect
            fetch('../../../app/controllers/AuthController.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'action=logout'
            })
                .then(() => {
                    // Redirect to login page after logout
                    window.location.href = '../../../auth/login.php?logout=success';
                })
                .catch(() => {
                    // Even if fetch fails, redirect anyway
                    window.location.href = '../../../auth/login.php?logout=success';
                });
        }
    }
</script>
</body>

</html>