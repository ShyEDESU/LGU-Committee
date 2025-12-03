<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Assign to Committee - CMS</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gray-50">
    <div class="min-h-screen flex">
        <!-- Sidebar -->
        <aside class="w-64 bg-gradient-to-b from-red-600 to-red-700 text-white p-6">
            <div class="mb-8">
                <h1 class="text-2xl font-bold">CMS</h1>
                <p class="text-xs text-red-200">Committee System</p>
            </div>
            <nav class="space-y-2">
                <a href="../../dashboard.php" class="block px-4 py-2 rounded hover:bg-red-700 transition"><i class="fas fa-home mr-2"></i>Dashboard</a>
            </nav>
        </aside>
        
        <!-- Main Content -->
        <div class="flex-1 p-8">
            <div class="mb-6">
                <h2 class="text-3xl font-bold text-gray-800">Assign Members to Committee</h2>
                <p class="text-gray-600 mt-1">Add or update committee member assignments</p>
            </div>
            
            <div class="bg-white rounded-lg shadow p-6 max-w-2xl">
                <form class="space-y-4">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Committee</label>
                        <select class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-red-600">
                            <option>Select a committee</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Member</label>
                        <select class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-red-600">
                            <option>Select a member</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Role</label>
                        <select class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-red-600">
                            <option>Member</option>
                            <option>Chair</option>
                            <option>Vice-Chair</option>
                        </select>
                    </div>
                    <div class="flex gap-4 pt-4">
                        <button type="submit" class="bg-red-600 text-white px-6 py-2 rounded-lg hover:bg-red-700 transition">Assign Member</button>
                        <a href="directory.php" class="bg-gray-300 text-gray-800 px-6 py-2 rounded-lg hover:bg-gray-400 transition">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>