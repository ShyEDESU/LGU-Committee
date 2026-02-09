<?php include 'header.php'; ?>

<!-- Hero Section -->
<section class="hero-pattern text-white py-20">
    <div class="container mx-auto px-4 text-center">
        <h1 class="text-4xl md:text-5xl font-bold mb-4">Welcome to the Public Portal</h1>
        <p class="text-xl md:text-2xl mb-8 text-red-100">Transparency and Accountability in Local Governance</p>
        <p class="text-lg mb-8 max-w-3xl mx-auto">
            Access committee meetings, agendas, minutes, and legislative information from the City Government of
            Valenzuela
        </p>
        <div class="flex flex-wrap justify-center gap-4">
            <a href="meetings.php"
                class="bg-white text-red-600 px-8 py-3 rounded-lg font-semibold hover:bg-gray-100 transition">
                <i class="bi bi-calendar-event mr-2"></i>View Meetings
            </a>
            <a href="committees.php"
                class="bg-red-700 text-white px-8 py-3 rounded-lg font-semibold hover:bg-red-800 transition">
                <i class="bi bi-people mr-2"></i>Browse Committees
            </a>
        </div>
    </div>
</section>

<!-- Quick Stats -->
<section class="py-12 bg-white">
    <div class="container mx-auto px-4">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
            <div class="text-center p-6 bg-gray-50 rounded-lg">
                <div class="text-4xl font-bold text-red-600 mb-2">12</div>
                <div class="text-gray-600">Active Committees</div>
            </div>
            <div class="text-center p-6 bg-gray-50 rounded-lg">
                <div class="text-4xl font-bold text-red-600 mb-2">8</div>
                <div class="text-gray-600">Meetings This Month</div>
            </div>
            <div class="text-center p-6 bg-gray-50 rounded-lg">
                <div class="text-4xl font-bold text-green-600 mb-2">45</div>
                <div class="text-gray-600">Published Agendas</div>
            </div>
            <div class="text-center p-6 bg-gray-50 rounded-lg">
                <div class="text-4xl font-bold text-purple-600 mb-2">120</div>
                <div class="text-gray-600">Available Minutes</div>
            </div>
        </div>
    </div>
</section>

<!-- Upcoming Meetings -->
<section class="py-12 bg-gray-50">
    <div class="container mx-auto px-4">
        <div class="flex items-center justify-between mb-8">
            <h2 class="text-3xl font-bold text-gray-900">Upcoming Meetings</h2>
            <a href="meetings.php" class="text-red-600 hover:text-red-700 font-semibold">
                View All <i class="bi bi-arrow-right"></i>
            </a>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <!-- Meeting Card 1 -->
            <div class="bg-white rounded-lg shadow-md p-6 hover:shadow-lg transition">
                <div class="flex items-start justify-between mb-4">
                    <div class="bg-red-100 rounded-lg p-3">
                        <i class="bi bi-calendar-event text-red-600 text-2xl"></i>
                    </div>
                    <span class="bg-green-100 text-green-800 text-xs font-semibold px-2 py-1 rounded">Public</span>
                </div>
                <h3 class="text-lg font-bold text-gray-900 mb-2">Finance Committee Meeting</h3>
                <p class="text-gray-600 text-sm mb-4">Discussion on Q4 Budget Allocation and Revenue Enhancement</p>
                <div class="space-y-2 text-sm text-gray-500">
                    <div class="flex items-center">
                        <i class="bi bi-calendar3 mr-2"></i>
                        <span>December 15, 2025</span>
                    </div>
                    <div class="flex items-center">
                        <i class="bi bi-clock mr-2"></i>
                        <span>2:00 PM - 4:00 PM</span>
                    </div>
                    <div class="flex items-center">
                        <i class="bi bi-geo-alt mr-2"></i>
                        <span>City Hall Conference Room A</span>
                    </div>
                </div>
                <a href="meetings.php?id=1"
                    class="mt-4 block text-center bg-red-600 hover:bg-red-700 text-white py-2 rounded-lg font-medium transition">
                    View Details
                </a>
            </div>

            <!-- Meeting Card 2 -->
            <div class="bg-white rounded-lg shadow-md p-6 hover:shadow-lg transition">
                <div class="flex items-start justify-between mb-4">
                    <div class="bg-red-100 rounded-lg p-3">
                        <i class="bi bi-calendar-event text-red-600 text-2xl"></i>
                    </div>
                    <span class="bg-green-100 text-green-800 text-xs font-semibold px-2 py-1 rounded">Public</span>
                </div>
                <h3 class="text-lg font-bold text-gray-900 mb-2">Health Committee Meeting</h3>
                <p class="text-gray-600 text-sm mb-4">Review of Healthcare Facilities and Public Health Programs</p>
                <div class="space-y-2 text-sm text-gray-500">
                    <div class="flex items-center">
                        <i class="bi bi-calendar3 mr-2"></i>
                        <span>December 16, 2025</span>
                    </div>
                    <div class="flex items-center">
                        <i class="bi bi-clock mr-2"></i>
                        <span>10:00 AM - 12:00 PM</span>
                    </div>
                    <div class="flex items-center">
                        <i class="bi bi-geo-alt mr-2"></i>
                        <span>City Hall Conference Room B</span>
                    </div>
                </div>
                <a href="meetings.php?id=2"
                    class="mt-4 block text-center bg-red-600 hover:bg-red-700 text-white py-2 rounded-lg font-medium transition">
                    View Details
                </a>
            </div>

            <!-- Meeting Card 3 -->
            <div class="bg-white rounded-lg shadow-md p-6 hover:shadow-lg transition">
                <div class="flex items-start justify-between mb-4">
                    <div class="bg-green-100 rounded-lg p-3">
                        <i class="bi bi-calendar-event text-green-600 text-2xl"></i>
                    </div>
                    <span class="bg-green-100 text-green-800 text-xs font-semibold px-2 py-1 rounded">Public</span>
                </div>
                <h3 class="text-lg font-bold text-gray-900 mb-2">Public Safety Committee</h3>
                <p class="text-gray-600 text-sm mb-4">Disaster Preparedness and Emergency Response Planning</p>
                <div class="space-y-2 text-sm text-gray-500">
                    <div class="flex items-center">
                        <i class="bi bi-calendar3 mr-2"></i>
                        <span>December 18, 2025</span>
                    </div>
                    <div class="flex items-center">
                        <i class="bi bi-clock mr-2"></i>
                        <span>3:00 PM - 5:00 PM</span>
                    </div>
                    <div class="flex items-center">
                        <i class="bi bi-geo-alt mr-2"></i>
                        <span>City Hall Main Hall</span>
                    </div>
                </div>
                <a href="meetings.php?id=3"
                    class="mt-4 block text-center bg-red-600 hover:bg-red-700 text-white py-2 rounded-lg font-medium transition">
                    View Details
                </a>
            </div>
        </div>
    </div>
</section>

<!-- Features -->
<section class="py-12 bg-white">
    <div class="container mx-auto px-4">
        <h2 class="text-3xl font-bold text-gray-900 text-center mb-12">What You Can Access</h2>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <div class="text-center">
                <div class="bg-red-100 rounded-full w-20 h-20 flex items-center justify-center mx-auto mb-4">
                    <i class="bi bi-calendar-event text-red-600 text-3xl"></i>
                </div>
                <h3 class="text-xl font-bold text-gray-900 mb-2">Meeting Schedules</h3>
                <p class="text-gray-600">
                    View upcoming and past committee meetings with complete details including date, time, and location.
                </p>
            </div>

            <div class="text-center">
                <div class="bg-red-100 rounded-full w-20 h-20 flex items-center justify-center mx-auto mb-4">
                    <i class="bi bi-list-ul text-red-600 text-3xl"></i>
                </div>
                <h3 class="text-xl font-bold text-gray-900 mb-2">Meeting Agendas</h3>
                <p class="text-gray-600">
                    Access published agendas to know what topics will be discussed in upcoming meetings.
                </p>
            </div>

            <div class="text-center">
                <div class="bg-green-100 rounded-full w-20 h-20 flex items-center justify-center mx-auto mb-4">
                    <i class="bi bi-file-text text-green-600 text-3xl"></i>
                </div>
                <h3 class="text-xl font-bold text-gray-900 mb-2">Meeting Minutes</h3>
                <p class="text-gray-600">
                    Read approved minutes from past meetings to stay informed about decisions and actions taken.
                </p>
            </div>

            <div class="text-center">
                <div class="bg-purple-100 rounded-full w-20 h-20 flex items-center justify-center mx-auto mb-4">
                    <i class="bi bi-people text-purple-600 text-3xl"></i>
                </div>
                <h3 class="text-xl font-bold text-gray-900 mb-2">Committee Directory</h3>
                <p class="text-gray-600">
                    Browse all committees, their members, and areas of jurisdiction.
                </p>
            </div>

            <div class="text-center">
                <div class="bg-yellow-100 rounded-full w-20 h-20 flex items-center justify-center mx-auto mb-4">
                    <i class="bi bi-search text-yellow-600 text-3xl"></i>
                </div>
                <h3 class="text-xl font-bold text-gray-900 mb-2">Search & Filter</h3>
                <p class="text-gray-600">
                    Easily find specific meetings, agendas, or minutes using our search and filter tools.
                </p>
            </div>

            <div class="text-center">
                <div class="bg-orange-100 rounded-full w-20 h-20 flex items-center justify-center mx-auto mb-4">
                    <i class="bi bi-download text-orange-600 text-3xl"></i>
                </div>
                <h3 class="text-xl font-bold text-gray-900 mb-2">Download Documents</h3>
                <p class="text-gray-600">
                    Download agendas, minutes, and other public documents for offline viewing.
                </p>
            </div>
        </div>
    </div>
</section>

<!-- Call to Action -->
<section class="py-12 bg-red-600 text-white">
    <div class="container mx-auto px-4 text-center">
        <h2 class="text-3xl font-bold mb-4">Stay Informed</h2>
        <p class="text-xl mb-8 text-red-100">
            Subscribe to receive notifications about upcoming meetings and published documents
        </p>
        <div class="max-w-md mx-auto flex gap-2">
            <input type="email" placeholder="Enter your email address"
                class="flex-1 px-4 py-3 rounded-lg text-gray-900 focus:outline-none focus:ring-2 focus:ring-white">
            <button class="bg-white text-red-600 px-6 py-3 rounded-lg font-semibold hover:bg-gray-100 transition">
                Subscribe
            </button>
        </div>
    </div>
</section>

<?php include 'footer.php'; ?>
