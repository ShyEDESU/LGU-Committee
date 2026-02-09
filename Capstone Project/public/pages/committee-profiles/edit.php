<?php
require_once __DIR__ . '/../../../config/session_config.php';
require_once __DIR__ . '/../../../app/helpers/CommitteeHelper.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../../../auth/login.php');
    exit();
}

$id = $_GET['id'] ?? 0;
$committee = getCommitteeById($id);

if (!$committee) {
    $_SESSION['error_message'] = 'Committee not found';
    header('Location: index.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'name' => trim($_POST['name'] ?? ''),
        'type' => $_POST['type'] ?? '',
        'chair' => trim($_POST['chair'] ?? ''),
        'vice_chair' => trim($_POST['vice_chair'] ?? ''),
        'jurisdiction' => trim($_POST['jurisdiction'] ?? ''),
        'description' => trim($_POST['description'] ?? ''),
        'status' => $_POST['status'] ?? 'Active'
    ];

    $errors = [];
    if (empty($data['name']))
        $errors[] = 'Committee name is required';
    if (empty($data['type']))
        $errors[] = 'Committee type is required';
    if (empty($data['chair']))
        $errors[] = 'Chairperson is required';

    if (empty($errors)) {
        updateCommittee($id, $data);
        $_SESSION['success_message'] = 'Committee updated successfully!';
        header('Location: view.php?id=' . $id);
        exit();
    }
}

$userName = $_SESSION['user_name'] ?? 'User';
$pageTitle = 'Edit Committee';
include '../../includes/header.php';
?>

<div class="container-fluid">
    <nav class="mb-4" aria-label="breadcrumb">
        <ol class="breadcrumb bg-transparent p-0">
            <li class="breadcrumb-item"><a href="../../dashboard.php" class="text-red-600">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="index.php" class="text-red-600">Committees</a></li>
            <li class="breadcrumb-item"><a href="view.php?id=<?php echo $id; ?>"
                    class="text-red-600"><?php echo htmlspecialchars($committee['name']); ?></a></li>
            <li class="breadcrumb-item active">Edit</li>
        </ol>
    </nav>

    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Edit Committee</h1>
        <a href="view.php?id=<?php echo $id; ?>" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg">
            <i class="bi bi-x-circle mr-2"></i>Cancel
        </a>
    </div>

    <?php if (!empty($errors)): ?>
        <div class="bg-red-50 border-l-4 border-red-500 p-4 mb-6">
            <ul class="list-disc list-inside text-red-700">
                <?php foreach ($errors as $error): ?>
                    <li><?php echo htmlspecialchars($error); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6">
        <form method="POST">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium mb-2">Committee Name *</label>
                    <input type="text" name="name" required value="<?php echo htmlspecialchars($committee['name']); ?>"
                        class="w-full px-4 py-2 border rounded-lg dark:bg-gray-700 dark:text-white">
                </div>

                <div>
                    <label class="block text-sm font-medium mb-2">Type *</label>
                    <select name="type" required class="w-full px-4 py-2 border rounded-lg dark:bg-gray-700">
                        <option value="Standing" <?php echo $committee['type'] === 'Standing' ? 'selected' : ''; ?>>
                            Standing</option>
                        <option value="Special" <?php echo $committee['type'] === 'Special' ? 'selected' : ''; ?>>Special
                        </option>
                        <option value="Ad Hoc" <?php echo $committee['type'] === 'Ad Hoc' ? 'selected' : ''; ?>>Ad Hoc
                        </option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium mb-2">Status *</label>
                    <select name="status" required class="w-full px-4 py-2 border rounded-lg dark:bg-gray-700">
                        <option value="Active" <?php echo $committee['status'] === 'Active' ? 'selected' : ''; ?>>Active
                        </option>
                        <option value="Inactive" <?php echo $committee['status'] === 'Inactive' ? 'selected' : ''; ?>>
                            Inactive</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium mb-2">Chairperson *</label>
                    <input type="text" name="chair" required
                        value="<?php echo htmlspecialchars($committee['chair']); ?>"
                        class="w-full px-4 py-2 border rounded-lg dark:bg-gray-700">
                </div>

                <div>
                    <label class="block text-sm font-medium mb-2">Vice-Chairperson</label>
                    <input type="text" name="vice_chair"
                        value="<?php echo htmlspecialchars($committee['vice_chair'] ?? ''); ?>"
                        class="w-full px-4 py-2 border rounded-lg dark:bg-gray-700">
                </div>

                <div class="md:col-span-2">
                    <label class="block text-sm font-medium mb-2">Jurisdiction *</label>
                    <textarea name="jurisdiction" required rows="2"
                        class="w-full px-4 py-2 border rounded-lg dark:bg-gray-700"><?php echo htmlspecialchars($committee['jurisdiction']); ?></textarea>
                </div>

                <div class="md:col-span-2">
                    <label class="block text-sm font-medium mb-2">Description</label>
                    <textarea name="description" rows="4"
                        class="w-full px-4 py-2 border rounded-lg dark:bg-gray-700"><?php echo htmlspecialchars($committee['description'] ?? ''); ?></textarea>
                </div>
            </div>

            <div class="flex justify-end gap-3 mt-6 pt-6 border-t">
                <a href="view.php?id=<?php echo $id; ?>" class="px-6 py-2 border rounded-lg hover:bg-gray-50">Cancel</a>
                <button type="submit" class="bg-red-600 hover:bg-red-700 text-white px-6 py-2 rounded-lg">
                    <i class="bi bi-check-circle mr-2"></i>Update Committee
                </button>
            </div>
        </form>
    </div>
</div>

</div> <!-- Closing container-fluid and module-content-wrapper -->
<?php
include '../../includes/footer.php';
include '../../includes/layout-end.php';
?>