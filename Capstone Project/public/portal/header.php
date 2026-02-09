<?php
// Public Portal - No authentication required
// This is the public-facing interface for transparency

// Get current page for navigation highlighting
$currentPage = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="theme-color" content="#dc2626">
    <title>Public Portal | Committee Management System - City of Valenzuela</title>
    <meta name="description"
        content="Public access to committee meetings, agendas, minutes, and legislation - City Government of Valenzuela">
    <meta name="keywords" content="Valenzuela, Committee, Public Portal, Transparency, Meetings, Agendas, Minutes">

    <link rel="icon" type="image/png" href="../assets/images/logo.png">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

    <style>
        .hero-pattern {
            background-color: #dc2626;
            background-image: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.05'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
        }
    </style>
</head>

<body class="bg-gray-50">
    <!-- Public Header -->
    <header class="bg-white shadow-md sticky top-0 z-50">
        <div class="container mx-auto px-4">
            <div class="flex items-center justify-between h-20">
                <!-- Logo and Title -->
                <div class="flex items-center space-x-4">
                    <img src="../assets/images/logo.png" alt="City of Valenzuela" class="w-14 h-14 object-contain">
                    <div>
                        <h1 class="text-xl font-bold text-gray-900">Committee Management System</h1>
                        <p class="text-sm text-gray-600">City Government of Valenzuela</p>
                    </div>
                </div>

                <!-- Navigation -->
                <nav class="hidden md:flex items-center space-x-6">
                    <a href="index.php"
                        class="<?php echo $currentPage === 'index.php' ? 'text-red-600 font-semibold' : 'text-gray-700 hover:text-red-600'; ?> transition">
                        <i class="bi bi-house-door mr-1"></i> Home
                    </a>
                    <a href="meetings.php"
                        class="<?php echo $currentPage === 'meetings.php' ? 'text-red-600 font-semibold' : 'text-gray-700 hover:text-red-600'; ?> transition">
                        <i class="bi bi-calendar-event mr-1"></i> Meetings
                    </a>
                    <a href="agendas.php"
                        class="<?php echo $currentPage === 'agendas.php' ? 'text-red-600 font-semibold' : 'text-gray-700 hover:text-red-600'; ?> transition">
                        <i class="bi bi-list-ul mr-1"></i> Agendas
                    </a>
                    <a href="minutes.php"
                        class="<?php echo $currentPage === 'minutes.php' ? 'text-red-600 font-semibold' : 'text-gray-700 hover:text-red-600'; ?> transition">
                        <i class="bi bi-file-text mr-1"></i> Minutes
                    </a>
                    <a href="committees.php"
                        class="<?php echo $currentPage === 'committees.php' ? 'text-red-600 font-semibold' : 'text-gray-700 hover:text-red-600'; ?> transition">
                        <i class="bi bi-people mr-1"></i> Committees
                    </a>
                    <a href="../auth/login.php"
                        class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg font-medium transition">
                        <i class="bi bi-box-arrow-in-right mr-1"></i> Staff Login
                    </a>
                </nav>

                <!-- Mobile Menu Button -->
                <button id="mobile-menu-btn" class="md:hidden text-gray-700 hover:text-red-600">
                    <i class="bi bi-list text-3xl"></i>
                </button>
            </div>
        </div>

        <!-- Mobile Menu -->
        <div id="mobile-menu" class="hidden md:hidden bg-white border-t border-gray-200">
            <div class="container mx-auto px-4 py-4 space-y-2">
                <a href="index.php"
                    class="block px-4 py-2 <?php echo $currentPage === 'index.php' ? 'bg-red-50 text-red-600 font-semibold' : 'text-gray-700 hover:bg-gray-50'; ?> rounded-lg transition">
                    <i class="bi bi-house-door mr-2"></i> Home
                </a>
                <a href="meetings.php"
                    class="block px-4 py-2 <?php echo $currentPage === 'meetings.php' ? 'bg-red-50 text-red-600 font-semibold' : 'text-gray-700 hover:bg-gray-50'; ?> rounded-lg transition">
                    <i class="bi bi-calendar-event mr-2"></i> Meetings
                </a>
                <a href="agendas.php"
                    class="block px-4 py-2 <?php echo $currentPage === 'agendas.php' ? 'bg-red-50 text-red-600 font-semibold' : 'text-gray-700 hover:bg-gray-50'; ?> rounded-lg transition">
                    <i class="bi bi-list-ul mr-2"></i> Agendas
                </a>
                <a href="minutes.php"
                    class="block px-4 py-2 <?php echo $currentPage === 'minutes.php' ? 'bg-red-50 text-red-600 font-semibold' : 'text-gray-700 hover:bg-gray-50'; ?> rounded-lg transition">
                    <i class="bi bi-file-text mr-2"></i> Minutes
                </a>
                <a href="committees.php"
                    class="block px-4 py-2 <?php echo $currentPage === 'committees.php' ? 'bg-red-50 text-red-600 font-semibold' : 'text-gray-700 hover:bg-gray-50'; ?> rounded-lg transition">
                    <i class="bi bi-people mr-2"></i> Committees
                </a>
                <a href="../auth/login.php"
                    class="block px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg font-medium transition text-center">
                    <i class="bi bi-box-arrow-in-right mr-2"></i> Staff Login
                </a>
            </div>
        </div>
    </header>

    <script>
        // Mobile menu toggle
        document.getElementById('mobile-menu-btn').addEventListener('click', function () {
            const menu = document.getElementById('mobile-menu');
            menu.classList.toggle('hidden');
        });
    </script>
</body>

</html>
