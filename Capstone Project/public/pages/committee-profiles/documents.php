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
    header('Location: index.php');
    exit();
}

// Active sub-tab: documents (default) or ordinances
$subTab = $_GET['subtab'] ?? 'documents';

// Handle document deletion
if (isset($_POST['delete_document'])) {
    $documentId = intval($_POST['delete_document']);
    $isMeetingDoc = isset($_POST['is_meeting_doc']) && $_POST['is_meeting_doc'] == '1';

    if ($isMeetingDoc) {
        require_once __DIR__ . '/../../../app/helpers/MeetingHelper.php';
        $success = deleteMeetingDocument($documentId);
    } else {
        $success = deleteCommitteeDocument($documentId);
    }

    if ($success) {
        $_SESSION['success_message'] = 'Document deleted successfully';
    } else {
        $_SESSION['error_message'] = 'Failed to delete document';
    }
    header('Location: documents.php?id=' . $id);
    exit();
}

$documents = getCommitteeDocuments($id);
$userName = $_SESSION['user_name'] ?? 'User';
$pageTitle = 'Committee Documents';
include '../../includes/header.php';
?>

<div class="container-fluid">
    <nav class="mb-4" aria-label="breadcrumb">
        <ol class="breadcrumb bg-transparent p-0">
            <li class="breadcrumb-item"><a href="../../dashboard.php" class="text-red-600">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="index.php" class="text-red-600">Committees</a></li>
            <li class="breadcrumb-item"><a href="view.php?id=<?php echo $id; ?>" class="text-red-600"><?php echo htmlspecialchars($committee['name']); ?></a></li>
            <li class="breadcrumb-item active">Documents &amp; Ordinances</li>
        </ol>
    </nav>

    <!-- Page Header -->
    <div class="flex justify-between items-start mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white"><?php echo htmlspecialchars($committee['name']); ?></h1>
            <p class="text-gray-500 dark:text-gray-400 mt-1">Documents &amp; Ordinances Management</p>
        </div>
        <div class="flex gap-2">
            <?php if ($subTab === 'documents'): ?>
            <a href="upload-document.php?committee_id=<?php echo $id; ?>"
                class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg inline-flex items-center gap-2 transition text-sm font-medium shadow-sm">
                <i class="bi bi-upload"></i> Upload Document
            </a>
            <?php endif; ?>
            <a href="view.php?id=<?php echo $id; ?>"
                class="bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-200 px-4 py-2 rounded-lg inline-flex items-center gap-2 transition text-sm font-medium">
                <i class="bi bi-arrow-left"></i> Back to Overview
            </a>
        </div>
    </div>

    <!-- Main Navigation Tabs (matching view.php) -->
    <div class="mb-0">
        <div class="border-b border-gray-200 dark:border-gray-700">
            <nav class="-mb-px flex space-x-6 overflow-x-auto">
                <a href="view.php?id=<?php echo $id; ?>"
                    class="border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300 whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition">
                    <i class="bi bi-info-circle mr-1"></i>Overview
                </a>
                <a href="members.php?id=<?php echo $id; ?>"
                    class="border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300 whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition">
                    <i class="bi bi-people mr-1"></i>Members
                </a>
                <a href="view.php?id=<?php echo $id; ?>&tab=meetings"
                    class="border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300 whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition">
                    <i class="bi bi-calendar-event mr-1"></i>Meetings
                </a>
                <a href="view.php?id=<?php echo $id; ?>&tab=agenda"
                    class="border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300 whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition">
                    <i class="bi bi-card-checklist mr-1"></i>Agenda
                </a>
                <a href="documents.php?id=<?php echo $id; ?>"
                    class="border-red-500 text-red-600 whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition">
                    <i class="bi bi-folder mr-1"></i>Documents
                </a>
                <a href="view.php?id=<?php echo $id; ?>&tab=feedback"
                    class="border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300 whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition">
                    <i class="bi bi-chat-right-text mr-1"></i>Feedback
                </a>
                <a href="history.php?id=<?php echo $id; ?>"
                    class="border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300 whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition">
                    <i class="bi bi-clock-history mr-1"></i>History
                </a>
            </nav>
        </div>
    </div>

    <!-- Sub-tabs: Documents | Ordinances -->
    <div class="mt-6 mb-4 flex gap-2">
        <a href="documents.php?id=<?php echo $id; ?>&subtab=documents"
            class="<?php echo $subTab === 'documents' ? 'bg-red-600 text-white shadow-md' : 'bg-white dark:bg-gray-800 text-gray-600 dark:text-gray-300 border border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700'; ?> px-5 py-2 rounded-lg text-sm font-semibold inline-flex items-center gap-2 transition">
            <i class="bi bi-file-earmark-text"></i> Internal Documents
        </a>
        <a href="documents.php?id=<?php echo $id; ?>&subtab=ordinances"
            class="<?php echo $subTab === 'ordinances' ? 'bg-red-600 text-white shadow-md' : 'bg-white dark:bg-gray-800 text-gray-600 dark:text-gray-300 border border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700'; ?> px-5 py-2 rounded-lg text-sm font-semibold inline-flex items-center gap-2 transition">
            <i class="bi bi-bank2"></i> Ordinances
            <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-bold bg-amber-100 text-amber-700 dark:bg-amber-900/50 dark:text-amber-300">API</span>
        </a>
    </div>

    <?php if ($subTab === 'documents'): ?>
    <!-- ============================= DOCUMENTS TAB ============================= -->
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700">
        <div class="p-5 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between">
            <div>
                <h2 class="text-lg font-bold text-gray-900 dark:text-white">Internal Documents</h2>
                <p class="text-sm text-gray-500 dark:text-gray-400">Files uploaded by the committee</p>
            </div>
            <span class="text-sm font-semibold text-gray-500 dark:text-gray-400 bg-gray-100 dark:bg-gray-700 px-3 py-1 rounded-full">
                <?php echo count($documents); ?> file<?php echo count($documents) !== 1 ? 's' : ''; ?>
            </span>
        </div>

        <?php if (empty($documents)): ?>
        <div class="text-center py-16 px-6">
            <div class="w-20 h-20 bg-gray-100 dark:bg-gray-700 rounded-full flex items-center justify-center mx-auto mb-4">
                <i class="bi bi-folder2-open text-4xl text-gray-400"></i>
            </div>
            <h3 class="text-lg font-semibold text-gray-700 dark:text-gray-300 mb-1">No Documents Yet</h3>
            <p class="text-gray-500 dark:text-gray-400 text-sm mb-5">Upload the first document for this committee.</p>
            <a href="upload-document.php?committee_id=<?php echo $id; ?>"
                class="bg-red-600 hover:bg-red-700 text-white px-6 py-2.5 rounded-lg inline-flex items-center gap-2 text-sm font-medium transition shadow-sm">
                <i class="bi bi-upload"></i> Upload Document
            </a>
        </div>
        <?php else: ?>
        <div class="p-5 grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
            <?php foreach ($documents as $doc): ?>
            <?php
            $extension = strtolower(pathinfo($doc['file_name'] ?? '', PATHINFO_EXTENSION));
            $iconClass = 'bi-file-earmark-text text-gray-500';
            $iconBg = 'bg-gray-100 dark:bg-gray-700';
            switch ($extension) {
                case 'pdf':
                    $iconClass = 'bi-file-earmark-pdf text-red-600';
                    $iconBg = 'bg-red-50 dark:bg-red-900/20';
                    break;
                case 'xlsx': case 'xls':
                    $iconClass = 'bi-file-earmark-excel text-green-600';
                    $iconBg = 'bg-green-50 dark:bg-green-900/20';
                    break;
                case 'docx': case 'doc':
                    $iconClass = 'bi-file-earmark-word text-blue-600';
                    $iconBg = 'bg-blue-50 dark:bg-blue-900/20';
                    break;
                case 'pptx': case 'ppt':
                    $iconClass = 'bi-file-earmark-slides text-orange-600';
                    $iconBg = 'bg-orange-50 dark:bg-orange-900/20';
                    break;
                case 'jpg': case 'jpeg': case 'png':
                    $iconClass = 'bi-file-earmark-image text-purple-600';
                    $iconBg = 'bg-purple-50 dark:bg-purple-900/20';
                    break;
            }
            ?>
            <div class="border border-gray-200 dark:border-gray-700 rounded-xl p-4 hover:shadow-md transition bg-white dark:bg-gray-800 flex flex-col gap-3">
                <!-- Header -->
                <div class="flex items-start gap-3">
                    <div class="<?php echo $iconBg; ?> rounded-lg p-3 flex-shrink-0">
                        <i class="bi <?php echo $iconClass; ?> text-2xl"></i>
                    </div>
                    <div class="flex-1 min-w-0">
                        <h3 class="font-semibold text-gray-900 dark:text-white text-sm leading-tight line-clamp-2">
                            <?php echo htmlspecialchars($doc['title']); ?>
                        </h3>
                        <p class="text-xs text-gray-400 dark:text-gray-500 mt-0.5 truncate">
                            <?php echo htmlspecialchars($doc['file_name'] ?? 'No file'); ?>
                        </p>
                    </div>
                </div>

                <!-- Tags -->
                <div class="flex flex-wrap gap-1.5">
                    <span class="px-2 py-0.5 bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-300 rounded text-xs font-medium">
                        <?php echo htmlspecialchars($doc['type'] ?? 'General'); ?>
                    </span>
                    <?php if (!empty($doc['source'])): ?>
                    <span class="px-2 py-0.5 bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 rounded text-xs">
                        <i class="bi bi-info-circle"></i> <?php echo htmlspecialchars($doc['source']); ?>
                    </span>
                    <?php endif; ?>
                </div>

                <!-- Description -->
                <?php if (!empty($doc['description'])): ?>
                <p class="text-xs text-gray-500 dark:text-gray-400 line-clamp-2">
                    <?php echo htmlspecialchars($doc['description']); ?>
                </p>
                <?php endif; ?>

                <!-- Footer meta + actions -->
                <div class="mt-auto pt-3 border-t border-gray-100 dark:border-gray-700 flex items-center justify-between gap-2">
                    <div class="text-xs text-gray-400 dark:text-gray-500">
                        <span><?php echo date('M d, Y', strtotime($doc['uploaded_date'])); ?></span>
                        <span class="mx-1">·</span>
                        <span><?php echo htmlspecialchars($doc['uploaded_by'] ?? 'Unknown'); ?></span>
                    </div>
                    <div class="flex gap-1.5 flex-shrink-0">
                        <?php if (!empty($doc['file_path'])): ?>
                        <a href="download-document.php?id=<?php echo $doc['id']; ?>"
                            class="px-3 py-1.5 bg-red-600 hover:bg-red-700 text-white rounded-lg text-xs font-medium transition inline-flex items-center gap-1">
                            <i class="bi bi-download"></i> Download
                        </a>
                        <?php else: ?>
                        <button disabled class="px-3 py-1.5 bg-gray-300 dark:bg-gray-600 text-gray-500 dark:text-gray-400 rounded-lg text-xs cursor-not-allowed">
                            No File
                        </button>
                        <?php endif; ?>
                        <form method="POST" style="display:inline;" onsubmit="return confirm('Delete this document?')">
                            <input type="hidden" name="delete_document" value="<?php echo $doc['id']; ?>">
                            <?php if (!empty($doc['is_meeting_doc'])): ?>
                            <input type="hidden" name="is_meeting_doc" value="1">
                            <?php endif; ?>
                            <button type="submit"
                                class="px-2.5 py-1.5 border border-red-300 dark:border-red-700 text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg text-xs transition">
                                <i class="bi bi-trash"></i>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>

    <?php else: ?>
    <!-- ============================= ORDINANCES TAB ============================= -->
    <div class="space-y-5">

        <!-- API Integration Banner -->
        <div class="bg-gradient-to-r from-amber-50 to-orange-50 dark:from-amber-900/20 dark:to-orange-900/20 border border-amber-200 dark:border-amber-800 rounded-xl p-5">
            <div class="flex items-start gap-4">
                <div class="w-10 h-10 bg-amber-100 dark:bg-amber-900/50 rounded-lg flex items-center justify-center flex-shrink-0 mt-0.5">
                    <i class="bi bi-plug text-amber-600 dark:text-amber-400 text-xl"></i>
                </div>
                <div class="flex-1">
                    <div class="flex items-center gap-2 mb-1">
                        <h3 class="font-bold text-amber-900 dark:text-amber-200">City Ordinances System Integration</h3>
                        <span class="px-2 py-0.5 bg-amber-200 dark:bg-amber-800 text-amber-800 dark:text-amber-200 rounded text-xs font-bold uppercase tracking-wide">Pending API</span>
                    </div>
                    <p class="text-sm text-amber-800 dark:text-amber-300 leading-relaxed">
                        This section will be connected to the <strong>City Hall Ordinances Management System</strong> via API. 
                        Once linked, all ordinances assigned to <strong><?php echo htmlspecialchars($committee['name']); ?></strong> will appear here automatically — including status, sponsors, and legislative history.
                    </p>
                    <div class="mt-3 flex flex-wrap gap-2 text-xs">
                        <span class="px-2 py-1 bg-white dark:bg-gray-800 border border-amber-200 dark:border-amber-700 text-amber-700 dark:text-amber-300 rounded-lg font-medium inline-flex items-center gap-1">
                            <i class="bi bi-key"></i> API Key Required
                        </span>
                        <span class="px-2 py-1 bg-white dark:bg-gray-800 border border-amber-200 dark:border-amber-700 text-amber-700 dark:text-amber-300 rounded-lg font-medium inline-flex items-center gap-1">
                            <i class="bi bi-arrow-left-right"></i> Two-Way Sync
                        </span>
                        <span class="px-2 py-1 bg-white dark:bg-gray-800 border border-amber-200 dark:border-amber-700 text-amber-700 dark:text-amber-300 rounded-lg font-medium inline-flex items-center gap-1">
                            <i class="bi bi-shield-check"></i> Secure Endpoint
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Dummy Ordinance Data -->
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm overflow-hidden">
            <div class="p-5 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between">
                <div>
                    <h2 class="text-base font-bold text-gray-900 dark:text-white">Ordinances Under This Committee</h2>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Sample data — live data will be fetched from the Ordinances API</p>
                </div>
                <div class="flex items-center gap-2">
                    <span class="relative flex h-2.5 w-2.5">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-amber-400 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-amber-500"></span>
                    </span>
                    <span class="text-xs text-amber-600 dark:text-amber-400 font-semibold">Demo Data</span>
                </div>
            </div>

            <!-- Filter Bar -->
            <div class="px-5 py-3 bg-gray-50 dark:bg-gray-700/50 border-b border-gray-100 dark:border-gray-700 flex flex-wrap gap-2">
                <button class="px-3 py-1 bg-red-600 text-white rounded-lg text-xs font-semibold">All</button>
                <button class="px-3 py-1 bg-white dark:bg-gray-700 border border-gray-200 dark:border-gray-600 text-gray-600 dark:text-gray-300 rounded-lg text-xs hover:bg-gray-50 dark:hover:bg-gray-600 transition">Enacted</button>
                <button class="px-3 py-1 bg-white dark:bg-gray-700 border border-gray-200 dark:border-gray-600 text-gray-600 dark:text-gray-300 rounded-lg text-xs hover:bg-gray-50 dark:hover:bg-gray-600 transition">For Second Reading</button>
                <button class="px-3 py-1 bg-white dark:bg-gray-700 border border-gray-200 dark:border-gray-600 text-gray-600 dark:text-gray-300 rounded-lg text-xs hover:bg-gray-50 dark:hover:bg-gray-600 transition">Filed</button>
                <button class="px-3 py-1 bg-white dark:bg-gray-700 border border-gray-200 dark:border-gray-600 text-gray-600 dark:text-gray-300 rounded-lg text-xs hover:bg-gray-50 dark:hover:bg-gray-600 transition">Approved</button>
            </div>

            <!-- Ordinance List -->
            <?php
            // Dummy ordinance data
            $ordinances = [
                [
                    'number' => 'ORD-2026-041',
                    'title' => 'An Ordinance Regulating Traffic Flow and Vehicle Routes within the City',
                    'sponsor' => 'Hon. Maria Santos',
                    'status' => 'Enacted',
                    'status_color' => 'green',
                    'date_filed' => '2026-01-15',
                    'date_enacted' => '2026-03-10',
                    'reading' => '3rd Reading Passed',
                ],
                [
                    'number' => 'ORD-2026-038',
                    'title' => 'An Ordinance Establishing the City Public Transportation Modernization Program',
                    'sponsor' => 'Hon. Jose Reyes',
                    'status' => 'For Second Reading',
                    'status_color' => 'blue',
                    'date_filed' => '2026-02-20',
                    'date_enacted' => null,
                    'reading' => '1st Reading Passed',
                ],
                [
                    'number' => 'ORD-2026-035',
                    'title' => 'An Ordinance Requiring Installation of CCTV Cameras in Public Terminals',
                    'sponsor' => 'Hon. Ana Cruz',
                    'status' => 'Filed',
                    'status_color' => 'gray',
                    'date_filed' => '2026-03-05',
                    'date_enacted' => null,
                    'reading' => 'Pending 1st Reading',
                ],
                [
                    'number' => 'ORD-2025-112',
                    'title' => 'An Ordinance Implementing Anti-Colorum Vehicle Penalties in the City',
                    'sponsor' => 'Hon. Roberto Lim',
                    'status' => 'Approved',
                    'status_color' => 'purple',
                    'date_filed' => '2025-10-08',
                    'date_enacted' => '2025-12-22',
                    'reading' => '3rd Reading Passed',
                ],
            ];
            $statusColors = [
                'green'  => 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300',
                'blue'   => 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-300',
                'gray'   => 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300',
                'amber'  => 'bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-300',
                'purple' => 'bg-purple-100 text-purple-800 dark:bg-purple-900/30 dark:text-purple-300',
                'red'    => 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-300',
            ];
            ?>
            <div class="divide-y divide-gray-100 dark:divide-gray-700">
                <?php foreach ($ordinances as $ord): ?>
                <div class="p-5 hover:bg-gray-50 dark:hover:bg-gray-700/30 transition group">
                    <div class="flex items-start gap-4">
                        <!-- Icon -->
                        <div class="w-10 h-10 bg-red-50 dark:bg-red-900/20 rounded-lg flex items-center justify-center flex-shrink-0">
                            <i class="bi bi-file-text text-red-600 dark:text-red-400 text-lg"></i>
                        </div>
                        <!-- Content -->
                        <div class="flex-1 min-w-0">
                            <div class="flex flex-wrap items-center gap-2 mb-1">
                                <span class="text-xs font-bold text-gray-500 dark:text-gray-400 font-mono"><?php echo htmlspecialchars($ord['number']); ?></span>
                                <span class="px-2 py-0.5 rounded-full text-xs font-semibold <?php echo $statusColors[$ord['status_color']]; ?>">
                                    <?php echo htmlspecialchars($ord['status']); ?>
                                </span>
                            </div>
                            <p class="text-sm font-semibold text-gray-900 dark:text-white leading-snug mb-1">
                                <?php echo htmlspecialchars($ord['title']); ?>
                            </p>
                            <div class="flex flex-wrap gap-3 text-xs text-gray-500 dark:text-gray-400 mt-2">
                                <span class="inline-flex items-center gap-1">
                                    <i class="bi bi-person"></i> <?php echo htmlspecialchars($ord['sponsor']); ?>
                                </span>
                                <span class="inline-flex items-center gap-1">
                                    <i class="bi bi-calendar3"></i> Filed: <?php echo date('M d, Y', strtotime($ord['date_filed'])); ?>
                                </span>
                                <?php if ($ord['date_enacted']): ?>
                                <span class="inline-flex items-center gap-1 text-green-600 dark:text-green-400">
                                    <i class="bi bi-check-circle"></i> Enacted: <?php echo date('M d, Y', strtotime($ord['date_enacted'])); ?>
                                </span>
                                <?php endif; ?>
                                <span class="inline-flex items-center gap-1">
                                    <i class="bi bi-book"></i> <?php echo htmlspecialchars($ord['reading']); ?>
                                </span>
                            </div>
                        </div>
                        <!-- Actions (visible on hover) -->
                        <div class="flex gap-1.5 opacity-0 group-hover:opacity-100 transition flex-shrink-0">
                            <button title="View Details"
                                class="p-2 text-gray-400 hover:text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg transition text-sm">
                                <i class="bi bi-eye"></i>
                            </button>
                            <button title="Download PDF"
                                class="p-2 text-gray-400 hover:text-blue-600 hover:bg-blue-50 dark:hover:bg-blue-900/20 rounded-lg transition text-sm">
                                <i class="bi bi-download"></i>
                            </button>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>

            <!-- Footer: Connect API prompt -->
            <div class="p-5 bg-gray-50 dark:bg-gray-700/30 border-t border-gray-100 dark:border-gray-700 flex items-center justify-between">
                <p class="text-xs text-gray-500 dark:text-gray-400">Showing <strong>4</strong> sample ordinances &mdash; connect the Ordinances API to load live data.</p>
                <a href="../../system-settings/index.php" class="text-xs text-red-600 hover:text-red-700 font-semibold inline-flex items-center gap-1 transition">
                    <i class="bi bi-gear"></i> Configure API
                </a>
            </div>
        </div>

        <!-- API Endpoint Info card -->
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-5 shadow-sm">
            <h3 class="text-sm font-bold text-gray-800 dark:text-gray-200 mb-3 flex items-center gap-2">
                <i class="bi bi-code-slash text-red-500"></i> API Integration Reference
            </h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-xs">
                <div>
                    <p class="text-gray-500 dark:text-gray-400 font-medium mb-1">Expected Endpoint (GET)</p>
                    <code class="block bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded px-3 py-2 text-gray-700 dark:text-gray-300 font-mono break-all">
                        /api/ordinances?committee_id=<?php echo $id; ?>
                    </code>
                </div>
                <div>
                    <p class="text-gray-500 dark:text-gray-400 font-medium mb-1">This System's Webhook (POST)</p>
                    <code class="block bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded px-3 py-2 text-gray-700 dark:text-gray-300 font-mono break-all">
                        /api/webhook/ordinance-update
                    </code>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

</div><!-- /.container-fluid -->
</div><!-- /#module-content-wrapper -->

<?php
include '../../includes/footer.php';
include '../../includes/layout-end.php';
?>