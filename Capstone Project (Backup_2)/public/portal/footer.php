<!-- Footer -->
<footer class="bg-gray-900 text-white mt-12">
    <div class="container mx-auto px-4 py-8">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
            <!-- About -->
            <div>
                <h3 class="text-lg font-bold mb-4">About CMS</h3>
                <p class="text-gray-400 text-sm">
                    The Committee Management System provides transparent access to committee meetings, agendas, and
                    legislative activities of the City Government of Valenzuela.
                </p>
            </div>

            <!-- Quick Links -->
            <div>
                <h3 class="text-lg font-bold mb-4">Quick Links</h3>
                <ul class="space-y-2 text-sm">
                    <li><a href="index.php" class="text-gray-400 hover:text-white transition">Home</a></li>
                    <li><a href="meetings.php" class="text-gray-400 hover:text-white transition">Meeting Schedules</a>
                    </li>
                    <li><a href="agendas.php" class="text-gray-400 hover:text-white transition">Agendas</a></li>
                    <li><a href="minutes.php" class="text-gray-400 hover:text-white transition">Minutes</a></li>
                    <li><a href="committees.php" class="text-gray-400 hover:text-white transition">Committees</a></li>
                </ul>
            </div>

            <!-- Contact -->
            <div>
                <h3 class="text-lg font-bold mb-4">Contact Us</h3>
                <ul class="space-y-2 text-sm text-gray-400">
                    <li><i class="bi bi-geo-alt mr-2"></i>City Hall, Valenzuela City</li>
                    <li><i class="bi bi-telephone mr-2"></i>(02) 1234-5678</li>
                    <li><i class="bi bi-envelope mr-2"></i>committee@valenzuela.gov.ph</li>
                </ul>
            </div>

            <!-- Social Media -->
            <div>
                <h3 class="text-lg font-bold mb-4">Follow Us</h3>
                <div class="flex space-x-4">
                    <a href="#" class="text-gray-400 hover:text-white transition text-2xl">
                        <i class="bi bi-facebook"></i>
                    </a>
                    <a href="#" class="text-gray-400 hover:text-white transition text-2xl">
                        <i class="bi bi-twitter"></i>
                    </a>
                    <a href="#" class="text-gray-400 hover:text-white transition text-2xl">
                        <i class="bi bi-youtube"></i>
                    </a>
                </div>
            </div>
        </div>

        <div class="border-t border-gray-800 mt-8 pt-8 text-center text-sm text-gray-400">
            <p>&copy; <?php echo date('Y'); ?> City Government of Valenzuela. All rights reserved.</p>
            <p class="mt-2">
                <a href="#" class="hover:text-white transition">Privacy Policy</a> |
                <a href="#" class="hover:text-white transition">Terms of Service</a> |
                <a href="#" class="hover:text-white transition">Accessibility</a>
            </p>
        </div>
    </div>
</footer>