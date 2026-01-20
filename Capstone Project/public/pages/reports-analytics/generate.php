<?php
require_once __DIR__ . '/../../../config/session_config.php';
require_once __DIR__ . '/../../../config/database.php';
require_once __DIR__ . '/../../../app/helpers/ReportsHelper.php';
require_once __DIR__ . '/../../../app/helpers/CommitteeHelper.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../../../auth/login.php');
    exit();
}

$pageTitle = "Generate Report";
include '../../includes/header.php';

$committees = getAllCommittees(); // Existing helper function
?>

<div class="px-6 py-8">
    <div class="mb-8">
        <a href="index.php" class="text-blue-600 hover:text-blue-700 flex items-center gap-2 mb-4 font-semibold">
            <i class="bi bi-arrow-left"></i> Back to Dashboard
        </a>
        <h1 class="text-3xl font-extrabold text-gray-900 flex items-center gap-3">
            <i class="bi bi-file-earmark-plus-fill text-red-600"></i>
            Generate Professional Report
        </h1>
        <p class="text-gray-500 mt-2 text-lg">Create a detailed summary report for a specific committee or time period.
        </p>
    </div>

    <div class="max-w-4xl">
        <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
            <div class="p-8">
                <form id="reportForm" action="generate-process.php" method="POST">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mb-8">
                        <!-- Committee Selection -->
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-3 uppercase tracking-wider">Target
                                Committee</label>
                            <select name="committee_id"
                                class="w-full px-4 py-3 rounded-xl border-2 border-gray-100 focus:border-red-500 focus:ring-0 transition-all outline-none"
                                required>
                                <option value="all">All Committees (System-wide Summary)</option>
                                <?php foreach ($committees as $committee): ?>
                                    <option value="<?php echo $committee['committee_id']; ?>">
                                        <?php echo $committee['committee_name']; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- Report Type -->
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-3 uppercase tracking-wider">Report
                                Template</label>
                            <select name="report_type"
                                class="w-full px-4 py-3 rounded-xl border-2 border-gray-100 focus:border-red-500 focus:ring-0 transition-all outline-none">
                                <option value="full">Full Operational Summary</option>
                                <option value="attendance">Detailed Attendance Report</option>
                                <option value="tasks">Action Item Tracking Report</option>
                                <option value="referrals">Referral Log & Status Report</option>
                            </select>
                        </div>

                        <!-- Date Range -->
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-3 uppercase tracking-wider">From
                                Date</label>
                            <input type="date" name="date_from"
                                value="<?php echo date('Y-m-01', strtotime('-1 month')); ?>"
                                class="w-full px-4 py-3 rounded-xl border-2 border-gray-100 focus:border-red-500 focus:ring-0 transition-all outline-none">
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-3 uppercase tracking-wider">To
                                Date</label>
                            <input type="date" name="date_to" value="<?php echo date('Y-m-d'); ?>"
                                class="w-full px-4 py-3 rounded-xl border-2 border-gray-100 focus:border-red-500 focus:ring-0 transition-all outline-none">
                        </div>
                    </div>

                    <div class="border-t border-gray-50 pt-8 mt-8 flex items-center justify-between">
                        <div class="flex items-center gap-2 text-gray-500">
                            <i class="bi bi-info-circle"></i>
                            <span class="text-sm">Reports are generated as PDF by default.</span>
                        </div>
                        <button type="submit"
                            class="px-8 py-3.5 bg-red-600 text-white rounded-xl hover:bg-red-700 transition-all shadow-lg hover:shadow-red-200 flex items-center gap-3 font-bold">
                            <i class="bi bi-gear-fill animate-spin-slow"></i> Generate Report
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Quick Tips card -->
        <div class="mt-8 bg-blue-50 rounded-2xl p-6 border border-blue-100 border-dashed">
            <h4 class="text-blue-800 font-bold mb-2 flex items-center gap-2">
                <i class="bi bi-lightbulb"></i> Pro Tip
            </h4>
            <p class="text-blue-700 text-sm leading-relaxed">
                Generating a "Full Operational Summary" for a specific committee will include meeting minutes summaries,
                action item completion rates, and all legislative referrals handled during the selected period.
            </p>
        </div>
    </div>
</div>

<style>
    @keyframes spin-slow {
        from {
            transform: rotate(0deg);
        }

        to {
            transform: rotate(360deg);
        }
    }

    .animate-spin-slow {
        animation: spin-slow 8s linear infinite;
    }
</style>

<?php include '../../includes/footer.php'; ?>