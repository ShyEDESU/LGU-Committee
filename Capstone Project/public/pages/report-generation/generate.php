<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Generate Report - CMS</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gray-50">
    <div class="min-h-screen flex">
        <aside class="w-64 bg-gradient-to-b from-red-600 to-red-700 text-white p-6">
            <div class="mb-8"><h1 class="text-2xl font-bold">CMS</h1></div>
            <nav class="space-y-2">
                <a href="../../dashboard.php" class="block px-4 py-2 rounded hover:bg-red-700"><i class="fas fa-home mr-2"></i>Dashboard</a>
            </nav>
        </aside>
        <div class="flex-1 p-8">
            <h2 class="text-3xl font-bold text-gray-800 mb-2">Generate Report</h2>
            <p class="text-gray-600 mb-6">Create a new committee report</p>
            <div class="bg-white rounded-lg shadow p-6 max-w-2xl">
                <form class="space-y-4">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Report Type</label>
                        <select class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-red-600">
                            <option>Select report type</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Date Range</label>
                        <input type="text" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-red-600" placeholder="Select date range">
                    </div>
                    <div class="flex gap-4 pt-4">
                        <button type="submit" class="bg-red-600 text-white px-6 py-2 rounded-lg hover:bg-red-700">Generate Report</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>