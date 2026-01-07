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
            window.location.href = '../../../auth/login.php?logout=true';
        }
    }
</script>
</body>

</html>