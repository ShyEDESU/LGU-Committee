<?php
require_once __DIR__ . '/../../../config/session_config.php';
require_once __DIR__ . '/../../../app/helpers/CommitteeHelper.php';
require_once __DIR__ . '/../../../app/helpers/ReportsHelper.php';
require_once __DIR__ . '/../../../app/helpers/NotificationHelper.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../../../auth/login.php');
    exit();
}

$userId   = $_SESSION['user_id'];
$userRole = $_SESSION['user_role'] ?? 'User';

// Get committee ID from query string
$committeeId = (int)($_GET['committee'] ?? 0);
$committee   = $committeeId ? getCommitteeById($committeeId) : null;

if (!$committee) {
    $_SESSION['error_message'] = 'Committee not found.';
    header('Location: ../committee-profiles/index.php');
    exit();
}

// Only leadership or admin can draft reports
$canDraft = in_array($userRole, ['Admin', 'Super Admin'])
    || $userId == ($committee['chairperson_id'] ?? 0)
    || $userId == ($committee['vice_chair_id']  ?? 0)
    || $userId == ($committee['secretary_id']   ?? 0);

if (!$canDraft) {
    $_SESSION['error_message'] = 'You do not have permission to draft reports for this committee.';
    header('Location: ../committee-profiles/view.php?id=' . $committeeId);
    exit();
}

$errors = [];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title          = trim($_POST['title']          ?? '');
    $reportType     = trim($_POST['report_type']    ?? '');
    $recommendation = trim($_POST['recommendation'] ?? '');
    $content        = trim($_POST['content']        ?? '');

    if (empty($title))          $errors[] = 'Report title is required.';
    if (empty($reportType))     $errors[] = 'Report type is required.';
    if (empty($recommendation)) $errors[] = 'Recommendation is required.';
    if (empty($content))        $errors[] = 'Report content/summary is required.';

    if (empty($errors)) {
        $reportId = createReport($committeeId, $title, $reportType, $recommendation, $content, $userId);

        if ($reportId) {
            // Notify all committee members about the new draft
            $members = getCommitteeMembersForReport($committeeId);
            foreach ($members as $member) {
                if ($member['user_id'] != $userId) {
                    createNotification(
                        $member['user_id'],
                        '📄 New Committee Report Draft',
                        "A new report \"{$title}\" has been drafted for the {$committee['name']} committee. Your signature may be needed.",
                        'info',
                        'medium',
                        "pages/committee-reports/view.php?id={$reportId}"
                    );
                }
            }
            $_SESSION['success_message'] = 'Report draft created successfully!';
            header('Location: view.php?id=' . $reportId);
            exit();
        } else {
            $errors[] = 'Failed to create the report. Please try again.';
        }
    }
}

$pageTitle = 'Draft Committee Report — ' . htmlspecialchars($committee['name']);
include '../../includes/header.php';
?>

<div class="container-fluid">
    <!-- Breadcrumb -->
    <nav class="mb-4" aria-label="breadcrumb">
        <ol class="breadcrumb bg-transparent p-0">
            <li class="breadcrumb-item"><a href="../../dashboard.php" class="text-red-600">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="../committee-profiles/index.php" class="text-red-600">Committee Profiles</a></li>
            <li class="breadcrumb-item"><a href="../committee-profiles/view.php?id=<?php echo $committeeId; ?>" class="text-red-600"><?php echo htmlspecialchars($committee['name']); ?></a></li>
            <li class="breadcrumb-item active">Draft Report</li>
        </ol>
    </nav>

    <!-- Header -->
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Draft New Committee Report</h1>
            <p class="text-gray-500 dark:text-gray-400 mt-1"><?php echo htmlspecialchars($committee['name']); ?></p>
        </div>
        <a href="../committee-profiles/view.php?id=<?php echo $committeeId; ?>"
           class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg transition">
            <i class="bi bi-arrow-left mr-2"></i>Back to Committee
        </a>
    </div>

    <?php if (!empty($errors)): ?>
        <div class="bg-red-50 dark:bg-red-900/20 border-l-4 border-red-500 p-4 mb-6 rounded-r-lg">
            <div class="flex items-start gap-3">
                <i class="bi bi-exclamation-triangle text-red-500 text-xl mt-0.5"></i>
                <div>
                    <h3 class="font-semibold text-red-800 dark:text-red-300">Please correct the following errors:</h3>
                    <ul class="list-disc list-inside text-red-700 dark:text-red-400 mt-1 space-y-1">
                        <?php foreach ($errors as $error): ?>
                            <li><?php echo htmlspecialchars($error); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- Form Card -->
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 p-8">
        <form method="POST" class="space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                <!-- Title -->
                <div class="md:col-span-2">
                    <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                        Report Title <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="title" required
                           value="<?php echo htmlspecialchars($_POST['title'] ?? ''); ?>"
                           placeholder="e.g., Report on Proposed City Ordinance No. 2026-010"
                           class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-xl focus:ring-2 focus:ring-red-500 dark:bg-gray-700 dark:text-white">
                </div>

                <!-- Report Type -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                        Report Type <span class="text-red-500">*</span>
                    </label>
                    <select name="report_type" required
                            class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-xl focus:ring-2 focus:ring-red-500 dark:bg-gray-700 dark:text-white">
                        <option value="">Select Report Type</option>
                        <?php foreach (['Preliminary Report','Interim Report','Final Report','Special Report'] as $rt): ?>
                            <option value="<?php echo $rt; ?>" <?php echo (($_POST['report_type'] ?? '') === $rt) ? 'selected' : ''; ?>>
                                <?php echo $rt; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Recommendation -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                        Committee Recommendation <span class="text-red-500">*</span>
                    </label>
                    <select name="recommendation" required
                            class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-xl focus:ring-2 focus:ring-red-500 dark:bg-gray-700 dark:text-white">
                        <option value="">Select Recommendation</option>
                        <?php foreach (['Approve','Disapprove','Amend','Defer','Return to Sponsor'] as $rec): ?>
                            <option value="<?php echo $rec; ?>" <?php echo (($_POST['recommendation'] ?? '') === $rec) ? 'selected' : ''; ?>>
                                <?php echo $rec; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Content / Summary -->
                <div class="md:col-span-2">
                    <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                        Report Content / Summary <span class="text-red-500">*</span>
                    </label>
                    <textarea name="content" rows="10" required
                              placeholder="Write the committee's findings, deliberation summary, and basis for recommendation..."
                              class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-xl focus:ring-2 focus:ring-red-500 dark:bg-gray-700 dark:text-white resize-none"><?php echo htmlspecialchars($_POST['content'] ?? ''); ?></textarea>
                </div>
            </div>

            <!-- Info Banner -->
            <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-700 rounded-xl p-4 flex items-start gap-3">
                <i class="bi bi-info-circle text-blue-500 text-xl mt-0.5 flex-shrink-0"></i>
                <p class="text-sm text-blue-800 dark:text-blue-300">
                    After creating this draft, the report will be set to <strong>Draft</strong> status. You can then open it for voting
                    by publishing it, which will notify all committee members to cast their signature
                    (<strong>Approve / Dissent / Abstain</strong>).
                </p>
            </div>

            <!-- Actions -->
            <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-200 dark:border-gray-700">
                <a href="../committee-profiles/view.php?id=<?php echo $committeeId; ?>"
                   class="px-6 py-2 border border-gray-300 dark:border-gray-600 rounded-xl text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                    Cancel
                </a>
                <button type="submit"
                        class="bg-red-600 hover:bg-red-700 text-white px-8 py-2 rounded-xl transition font-semibold">
                    <i class="bi bi-file-earmark-plus mr-2"></i>Save Draft
                </button>
            </div><!-- /.actions row -->
        </form>
    </div><!-- /.form card -->
</div><!-- /.container-fluid -->
</div><!-- /#module-content-wrapper -->

<?php
include '../../includes/footer.php';
include '../../includes/layout-end.php';
?>
