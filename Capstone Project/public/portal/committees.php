<?php include 'header.php'; ?>

<!-- Page Header -->
<section class="bg-gray-100 py-12">
    <div class="container mx-auto px-4">
        <h1 class="text-4xl font-bold text-gray-900 mb-4">Committee Directory</h1>
        <p class="text-lg text-gray-600">Browse all committees, their members, and areas of jurisdiction</p>
    </div>
</section>

<!-- Filters -->
<section class="py-8 bg-white border-b border-gray-200">
    <div class="container mx-auto px-4">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Committee Type</label>
                <select class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-600">
                    <option>All Types</option>
                    <option>Standing Committee</option>
                    <option>Special Committee</option>
                    <option>Ad Hoc Committee</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                <select class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-600">
                    <option>Active</option>
                    <option>Inactive</option>
                    <option>All</option>
                </select>
            </div>
            <div class="flex items-end">
                <input type="text" placeholder="Search committees..."
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-600">
            </div>
        </div>
    </div>
</section>

<!-- Committees Grid -->
<section class="py-12">
    <div class="container mx-auto px-4">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <!-- Committee Card 1 -->
            <div class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-lg transition">
                <div class="bg-red-600 text-white p-4">
                    <div class="flex items-center justify-between">
                        <h3 class="text-xl font-bold">Finance Committee</h3>
                        <span class="bg-white text-red-600 text-xs font-semibold px-2 py-1 rounded">Standing</span>
                    </div>
                </div>
                <div class="p-6">
                    <div class="mb-4">
                        <p class="text-sm text-gray-600 mb-2"><strong>Chairperson:</strong></p>
                        <p class="text-gray-900">Hon. Maria Santos</p>
                    </div>
                    <div class="mb-4">
                        <p class="text-sm text-gray-600 mb-2"><strong>Members:</strong></p>
                        <p class="text-gray-900">7 members</p>
                    </div>
                    <div class="mb-4">
                        <p class="text-sm text-gray-600 mb-2"><strong>Jurisdiction:</strong></p>
                        <p class="text-gray-700 text-sm">Budget, appropriations, revenue, taxation, and financial
                            matters</p>
                    </div>
                    <div class="flex gap-2 text-sm text-gray-500 mb-4">
                        <span><i class="bi bi-calendar-event mr-1"></i>12 meetings</span>
                        <span><i class="bi bi-file-text mr-1"></i>8 reports</span>
                    </div>
                    <a href="#"
                        class="block text-center bg-red-600 hover:bg-red-700 text-white py-2 rounded-lg font-medium transition">
                        View Details
                    </a>
                </div>
            </div>

            <!-- Committee Card 2 -->
            <div class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-lg transition">
                <div class="bg-red-600 text-white p-4">
                    <div class="flex items-center justify-between">
                        <h3 class="text-xl font-bold">Health Committee</h3>
                        <span class="bg-white text-red-600 text-xs font-semibold px-2 py-1 rounded">Standing</span>
                    </div>
                </div>
                <div class="p-6">
                    <div class="mb-4">
                        <p class="text-sm text-gray-600 mb-2"><strong>Chairperson:</strong></p>
                        <p class="text-gray-900">Hon. Juan Dela Cruz</p>
                    </div>
                    <div class="mb-4">
                        <p class="text-sm text-gray-600 mb-2"><strong>Members:</strong></p>
                        <p class="text-gray-900">6 members</p>
                    </div>
                    <div class="mb-4">
                        <p class="text-sm text-gray-600 mb-2"><strong>Jurisdiction:</strong></p>
                        <p class="text-gray-700 text-sm">Public health, hospitals, healthcare facilities, and medical
                            services</p>
                    </div>
                    <div class="flex gap-2 text-sm text-gray-500 mb-4">
                        <span><i class="bi bi-calendar-event mr-1"></i>10 meetings</span>
                        <span><i class="bi bi-file-text mr-1"></i>6 reports</span>
                    </div>
                    <a href="#"
                        class="block text-center bg-red-600 hover:bg-red-700 text-white py-2 rounded-lg font-medium transition">
                        View Details
                    </a>
                </div>
            </div>

            <!-- Committee Card 3 -->
            <div class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-lg transition">
                <div class="bg-green-600 text-white p-4">
                    <div class="flex items-center justify-between">
                        <h3 class="text-xl font-bold">Education Committee</h3>
                        <span class="bg-white text-green-600 text-xs font-semibold px-2 py-1 rounded">Standing</span>
                    </div>
                </div>
                <div class="p-6">
                    <div class="mb-4">
                        <p class="text-sm text-gray-600 mb-2"><strong>Chairperson:</strong></p>
                        <p class="text-gray-900">Hon. Ana Reyes</p>
                    </div>
                    <div class="mb-4">
                        <p class="text-sm text-gray-600 mb-2"><strong>Members:</strong></p>
                        <p class="text-gray-900">8 members</p>
                    </div>
                    <div class="mb-4">
                        <p class="text-sm text-gray-600 mb-2"><strong>Jurisdiction:</strong></p>
                        <p class="text-gray-700 text-sm">Education, schools, libraries, and educational programs</p>
                    </div>
                    <div class="flex gap-2 text-sm text-gray-500 mb-4">
                        <span><i class="bi bi-calendar-event mr-1"></i>11 meetings</span>
                        <span><i class="bi bi-file-text mr-1"></i>7 reports</span>
                    </div>
                    <a href="#"
                        class="block text-center bg-green-600 hover:bg-green-700 text-white py-2 rounded-lg font-medium transition">
                        View Details
                    </a>
                </div>
            </div>

            <!-- Committee Card 4 -->
            <div class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-lg transition">
                <div class="bg-purple-600 text-white p-4">
                    <div class="flex items-center justify-between">
                        <h3 class="text-xl font-bold">Public Safety Committee</h3>
                        <span class="bg-white text-purple-600 text-xs font-semibold px-2 py-1 rounded">Standing</span>
                    </div>
                </div>
                <div class="p-6">
                    <div class="mb-4">
                        <p class="text-sm text-gray-600 mb-2"><strong>Chairperson:</strong></p>
                        <p class="text-gray-900">Hon. Rosa Martinez</p>
                    </div>
                    <div class="mb-4">
                        <p class="text-sm text-gray-600 mb-2"><strong>Members:</strong></p>
                        <p class="text-gray-900">6 members</p>
                    </div>
                    <div class="mb-4">
                        <p class="text-sm text-gray-600 mb-2"><strong>Jurisdiction:</strong></p>
                        <p class="text-gray-700 text-sm">Police, fire protection, disaster preparedness, and emergency
                            services</p>
                    </div>
                    <div class="flex gap-2 text-sm text-gray-500 mb-4">
                        <span><i class="bi bi-calendar-event mr-1"></i>9 meetings</span>
                        <span><i class="bi bi-file-text mr-1"></i>5 reports</span>
                    </div>
                    <a href="#"
                        class="block text-center bg-purple-600 hover:bg-purple-700 text-white py-2 rounded-lg font-medium transition">
                        View Details
                    </a>
                </div>
            </div>

            <!-- Committee Card 5 -->
            <div class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-lg transition">
                <div class="bg-orange-600 text-white p-4">
                    <div class="flex items-center justify-between">
                        <h3 class="text-xl font-bold">Infrastructure Committee</h3>
                        <span class="bg-white text-orange-600 text-xs font-semibold px-2 py-1 rounded">Standing</span>
                    </div>
                </div>
                <div class="p-6">
                    <div class="mb-4">
                        <p class="text-sm text-gray-600 mb-2"><strong>Chairperson:</strong></p>
                        <p class="text-gray-900">Hon. Pedro Garcia</p>
                    </div>
                    <div class="mb-4">
                        <p class="text-sm text-gray-600 mb-2"><strong>Members:</strong></p>
                        <p class="text-gray-900">8 members</p>
                    </div>
                    <div class="mb-4">
                        <p class="text-sm text-gray-600 mb-2"><strong>Jurisdiction:</strong></p>
                        <p class="text-gray-700 text-sm">Public works, roads, bridges, and infrastructure development
                        </p>
                    </div>
                    <div class="flex gap-2 text-sm text-gray-500 mb-4">
                        <span><i class="bi bi-calendar-event mr-1"></i>15 meetings</span>
                        <span><i class="bi bi-file-text mr-1"></i>10 reports</span>
                    </div>
                    <a href="#"
                        class="block text-center bg-orange-600 hover:bg-orange-700 text-white py-2 rounded-lg font-medium transition">
                        View Details
                    </a>
                </div>
            </div>

            <!-- Committee Card 6 -->
            <div class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-lg transition">
                <div class="bg-teal-600 text-white p-4">
                    <div class="flex items-center justify-between">
                        <h3 class="text-xl font-bold">Environment Committee</h3>
                        <span class="bg-white text-teal-600 text-xs font-semibold px-2 py-1 rounded">Standing</span>
                    </div>
                </div>
                <div class="p-6">
                    <div class="mb-4">
                        <p class="text-sm text-gray-600 mb-2"><strong>Chairperson:</strong></p>
                        <p class="text-gray-900">Hon. Luis Fernandez</p>
                    </div>
                    <div class="mb-4">
                        <p class="text-sm text-gray-600 mb-2"><strong>Members:</strong></p>
                        <p class="text-gray-900">7 members</p>
                    </div>
                    <div class="mb-4">
                        <p class="text-sm text-gray-600 mb-2"><strong>Jurisdiction:</strong></p>
                        <p class="text-gray-700 text-sm">Environmental protection, waste management, and sustainability
                        </p>
                    </div>
                    <div class="flex gap-2 text-sm text-gray-500 mb-4">
                        <span><i class="bi bi-calendar-event mr-1"></i>8 meetings</span>
                        <span><i class="bi bi-file-text mr-1"></i>6 reports</span>
                    </div>
                    <a href="#"
                        class="block text-center bg-teal-600 hover:bg-teal-700 text-white py-2 rounded-lg font-medium transition">
                        View Details
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

<?php include 'footer.php'; ?>
