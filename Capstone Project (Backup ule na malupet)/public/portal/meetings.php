<?php include 'header.php'; ?>

<!-- Page Header -->
<section class="bg-gray-100 py-12">
    <div class="container mx-auto px-4">
        <h1 class="text-4xl font-bold text-gray-900 mb-4">Committee Meetings</h1>
        <p class="text-lg text-gray-600">View upcoming and past committee meetings open to the public</p>
    </div>
</section>

<!-- Filters -->
<section class="py-8 bg-white border-b border-gray-200">
    <div class="container mx-auto px-4">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Committee</label>
                <select class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-600">
                    <option>All Committees</option>
                    <option>Finance Committee</option>
                    <option>Health Committee</option>
                    <option>Education Committee</option>
                    <option>Public Safety Committee</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                <select class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-600">
                    <option>Upcoming</option>
                    <option>Past</option>
                    <option>All</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Month</label>
                <select class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-600">
                    <option>December 2025</option>
                    <option>November 2025</option>
                    <option>October 2025</option>
                </select>
            </div>
            <div class="flex items-end">
                <button
                    class="w-full bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg font-medium transition">
                    <i class="bi bi-search mr-2"></i>Search
                </button>
            </div>
        </div>
    </div>
</section>

<!-- Meetings List -->
<section class="py-12">
    <div class="container mx-auto px-4">
        <div class="space-y-6">
            <!-- Meeting Item 1 -->
            <div class="bg-white rounded-lg shadow-md p-6 hover:shadow-lg transition">
                <div class="flex flex-col md:flex-row md:items-center md:justify-between">
                    <div class="flex-1">
                        <div class="flex items-center gap-3 mb-3">
                            <span
                                class="bg-green-100 text-green-800 text-xs font-semibold px-3 py-1 rounded-full">Upcoming</span>
                            <span
                                class="bg-red-100 text-red-800 text-xs font-semibold px-3 py-1 rounded-full">Public</span>
                        </div>
                        <h3 class="text-xl font-bold text-gray-900 mb-2">Finance Committee - Q4 Budget Review</h3>
                        <p class="text-gray-600 mb-4">Discussion on Q4 Budget Allocation and Revenue Enhancement
                            Strategies</p>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm text-gray-500">
                            <div class="flex items-center">
                                <i class="bi bi-calendar3 mr-2 text-red-600"></i>
                                <span>December 15, 2025</span>
                            </div>
                            <div class="flex items-center">
                                <i class="bi bi-clock mr-2 text-red-600"></i>
                                <span>2:00 PM - 4:00 PM</span>
                            </div>
                            <div class="flex items-center">
                                <i class="bi bi-geo-alt mr-2 text-red-600"></i>
                                <span>City Hall Conference Room A</span>
                            </div>
                        </div>
                    </div>
                    <div class="mt-4 md:mt-0 md:ml-6 flex flex-col gap-2">
                        <a href="#"
                            class="bg-red-600 hover:bg-red-700 text-white px-6 py-2 rounded-lg font-medium transition text-center">
                            View Agenda
                        </a>
                        <a href="#"
                            class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-6 py-2 rounded-lg font-medium transition text-center">
                            Add to Calendar
                        </a>
                    </div>
                </div>
            </div>

            <!-- Meeting Item 2 -->
            <div class="bg-white rounded-lg shadow-md p-6 hover:shadow-lg transition">
                <div class="flex flex-col md:flex-row md:items-center md:justify-between">
                    <div class="flex-1">
                        <div class="flex items-center gap-3 mb-3">
                            <span
                                class="bg-green-100 text-green-800 text-xs font-semibold px-3 py-1 rounded-full">Upcoming</span>
                            <span
                                class="bg-red-100 text-red-800 text-xs font-semibold px-3 py-1 rounded-full">Public</span>
                        </div>
                        <h3 class="text-xl font-bold text-gray-900 mb-2">Health Committee - Healthcare Facilities Review
                        </h3>
                        <p class="text-gray-600 mb-4">Review of Healthcare Facilities and Public Health Programs
                            Implementation</p>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm text-gray-500">
                            <div class="flex items-center">
                                <i class="bi bi-calendar3 mr-2 text-red-600"></i>
                                <span>December 16, 2025</span>
                            </div>
                            <div class="flex items-center">
                                <i class="bi bi-clock mr-2 text-red-600"></i>
                                <span>10:00 AM - 12:00 PM</span>
                            </div>
                            <div class="flex items-center">
                                <i class="bi bi-geo-alt mr-2 text-red-600"></i>
                                <span>City Hall Conference Room B</span>
                            </div>
                        </div>
                    </div>
                    <div class="mt-4 md:mt-0 md:ml-6 flex flex-col gap-2">
                        <a href="#"
                            class="bg-red-600 hover:bg-red-700 text-white px-6 py-2 rounded-lg font-medium transition text-center">
                            View Agenda
                        </a>
                        <a href="#"
                            class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-6 py-2 rounded-lg font-medium transition text-center">
                            Add to Calendar
                        </a>
                    </div>
                </div>
            </div>

            <!-- Meeting Item 3 (Past) -->
            <div class="bg-white rounded-lg shadow-md p-6 hover:shadow-lg transition opacity-75">
                <div class="flex flex-col md:flex-row md:items-center md:justify-between">
                    <div class="flex-1">
                        <div class="flex items-center gap-3 mb-3">
                            <span
                                class="bg-gray-100 text-gray-800 text-xs font-semibold px-3 py-1 rounded-full">Completed</span>
                            <span
                                class="bg-red-100 text-red-800 text-xs font-semibold px-3 py-1 rounded-full">Public</span>
                        </div>
                        <h3 class="text-xl font-bold text-gray-900 mb-2">Education Committee - School Infrastructure
                        </h3>
                        <p class="text-gray-600 mb-4">Discussion on School Building Improvements and Educational
                            Technology</p>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm text-gray-500">
                            <div class="flex items-center">
                                <i class="bi bi-calendar3 mr-2 text-red-600"></i>
                                <span>December 10, 2025</span>
                            </div>
                            <div class="flex items-center">
                                <i class="bi bi-clock mr-2 text-red-600"></i>
                                <span>1:00 PM - 3:00 PM</span>
                            </div>
                            <div class="flex items-center">
                                <i class="bi bi-geo-alt mr-2 text-red-600"></i>
                                <span>City Hall Main Hall</span>
                            </div>
                        </div>
                    </div>
                    <div class="mt-4 md:mt-0 md:ml-6 flex flex-col gap-2">
                        <a href="#"
                            class="bg-red-600 hover:bg-red-700 text-white px-6 py-2 rounded-lg font-medium transition text-center">
                            View Minutes
                        </a>
                        <a href="#"
                            class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-6 py-2 rounded-lg font-medium transition text-center">
                            View Agenda
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Pagination -->
        <div class="mt-8 flex justify-center">
            <nav class="flex gap-2">
                <button class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg transition">
                    <i class="bi bi-chevron-left"></i>
                </button>
                <button class="px-4 py-2 bg-red-600 text-white rounded-lg">1</button>
                <button class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg transition">2</button>
                <button class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg transition">3</button>
                <button class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg transition">
                    <i class="bi bi-chevron-right"></i>
                </button>
            </nav>
        </div>
    </div>
</section>

<?php include 'footer.php'; ?>
