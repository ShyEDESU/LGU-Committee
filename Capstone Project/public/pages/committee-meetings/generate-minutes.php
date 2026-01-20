<?php
require_once __DIR__ . '/../../../config/session_config.php';
require_once __DIR__ . '/../../../app/helpers/MeetingHelper.php';
require_once __DIR__ . '/../../../app/helpers/CommitteeHelper.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../../../auth/login.php');
    exit();
}

$id = $_GET['id'] ?? 0;
$meeting = getMeetingById($id);

if (!$meeting) {
    die("Meeting not found.");
}

$committee = getCommitteeById($meeting['committee_id']);
$agendaItems = getAgendaItems($id) ?? [];
$attendance = getAttendanceRecords($id) ?? [];
$attendanceStats = getAttendanceStats($id) ?? [];
$documents = getMeetingDocuments($id) ?? [];

// Fetch voting results for each agenda item
foreach ($agendaItems as &$item) {
    if (isset($item['id'])) {
        $item['votes'] = getVoteResults($item['id']) ?? null;
        $item['deliberations'] = getDeliberationsByAgenda($item['id']) ?? [];
    }
}
unset($item);

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Minutes -
        <?php echo htmlspecialchars($meeting['title']); ?>
    </title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        @media print {
            .no-print {
                display: none !important;
            }

            body {
                background: white !important;
            }

            .print-container {
                box-shadow: none !important;
                border: none !important;
                width: 100% !important;
                max-width: 100% !important;
                margin: 0 !important;
                padding: 0 !important;
            }
        }

        body {
            background-color: #f3f4f6;
            font-family: 'Inter', sans-serif;
        }
    </style>
</head>

<body class="py-12 px-4">
    <div class="max-w-4xl mx-auto space-y-8 no-print mb-8">
        <div class="flex justify-between items-center bg-white p-4 rounded-xl shadow-sm border border-gray-200">
            <div class="flex items-center space-x-4">
                <a href="view.php?id=<?php echo $id; ?>" class="text-gray-500 hover:text-gray-700 transition">
                    <i class="bi bi-arrow-left text-xl"></i>
                </a>
                <h1 class="font-bold text-gray-900">Minutes Preview</h1>
            </div>
            <button onclick="window.print()"
                class="bg-red-600 hover:bg-red-700 text-white px-6 py-2 rounded-lg font-bold transition flex items-center">
                <i class="bi bi-printer mr-2"></i> Print to PDF
            </button>
        </div>
    </div>

    <div class="print-container max-w-4xl mx-auto bg-white shadow-2xl border border-gray-200 p-12 md:p-16">
        <!-- Header -->
        <div class="text-center border-b-2 border-gray-900 pb-8 mb-8">
            <p class="uppercase tracking-widest text-sm font-semibold text-gray-600 mb-2">Republic of the Philippines
            </p>
            <p class="uppercase text-lg font-bold text-gray-900 mb-1">City Government of Valenzuela</p>
            <p class="uppercase text-xl font-black text-gray-900 mb-6">Legislative Services Department</p>

            <h2 class="text-2xl font-black uppercase underline decoration-4 underline-offset-8">
                <?php echo htmlspecialchars($meeting['committee_name']); ?>
            </h2>
            <p class="mt-4 text-lg font-bold">Minutes of the Meeting</p>
        </div>

        <!-- Meeting Details -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-10 text-sm">
            <div class="space-y-2">
                <p><span class="font-bold w-24 inline-block italic">SUBJECT:</span> <span class="uppercase font-bold">
                        <?php echo htmlspecialchars($meeting['title']); ?>
                    </span></p>
                <p><span class="font-bold w-24 inline-block italic">DATE:</span>
                    <?php echo date('F j, Y', strtotime($meeting['date'])); ?>
                </p>
                <p><span class="font-bold w-24 inline-block italic">TIME:</span>
                    <?php echo date('g:i A', strtotime($meeting['time_start'])); ?>
                    <?php echo !empty($meeting['time_end']) ? ' - ' . date('g:i A', strtotime($meeting['time_end'])) : ''; ?>
                </p>
            </div>
            <div class="space-y-2">
                <p><span class="font-bold w-24 inline-block italic">VENUE:</span>
                    <?php echo htmlspecialchars($meeting['venue']); ?>
                </p>
                <p><span class="font-bold w-24 inline-block italic">PRESIDED BY:</span>
                    <?php echo htmlspecialchars($committee['chair']); ?>
                </p>
                <p><span class="font-bold w-24 inline-block italic">QUORUM:</span>
                    <?php echo ($attendanceStats['has_quorum'] ?? false) ? 'QUORUM PRESENT' : 'NO QUORUM'; ?>
                </p>
            </div>
        </div>

        <!-- Attendance -->
        <div class="mb-10">
            <h3 class="font-black border-b border-gray-300 pb-2 mb-4 uppercase italic">I. ATTENDANCE</h3>
            <div class="grid grid-cols-2 gap-x-12 gap-y-2 text-sm">
                <?php if (empty($attendance)): ?>
                    <p class="text-gray-400 italic">No attendance records found.</p>
                <?php else: ?>
                    <?php foreach ($attendance as $record): ?>
                        <div class="flex justify-between items-center py-1 border-b border-gray-50">
                            <span>
                                <?php echo htmlspecialchars($record['name'] ?? 'Unknown Member'); ?>
                            </span>
                            <span
                                class="font-bold <?php echo $record['status'] === 'Present' ? 'text-green-700' : 'text-red-700'; ?>">
                                [
                                <?php echo strtoupper($record['status']); ?>]
                            </span>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

        <!-- Proceedings -->
        <div class="mb-10">
            <h3 class="font-black border-b border-gray-300 pb-2 mb-4 uppercase italic">II. PROCEEDINGS & DISCUSSIONS
            </h3>
            <div class="space-y-8">
                <?php if (empty($agendaItems)): ?>
                    <p class="text-gray-400 italic">No agenda items recorded.</p>
                <?php else: ?>
                    <?php foreach ($agendaItems as $index => $item): ?>
                        <div class="space-y-3">
                            <p class="font-bold text-gray-900">
                                <?php echo ($index + 1); ?>.
                                <?php echo htmlspecialchars($item['title']); ?>
                            </p>

                            <?php if (!empty($item['deliberations'])): ?>
                                <div class="pl-6 space-y-4">
                                    <?php foreach ($item['deliberations'] as $delib): ?>
                                        <div class="text-sm leading-relaxed text-gray-800 italic border-l-2 border-gray-200 pl-4">
                                            "
                                            <?php echo nl2br(htmlspecialchars($delib['content'])); ?>"
                                            <p class="text-xs font-bold mt-1 not-italic">— Recorded at
                                                <?php echo date('g:i A', strtotime($delib['created_at'])); ?>
                                            </p>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php else: ?>
                                <p class="pl-6 text-sm text-gray-500 italic">No deliberations recorded for this item.</p>
                            <?php endif; ?>

                            <?php if (!empty($item['votes'])): ?>
                                <div class="pl-6 pt-2">
                                    <div class="bg-gray-50 p-3 rounded border border-gray-200 text-xs">
                                        <p class="font-bold uppercase text-red-600 mb-1">Voting Result:
                                            <?php
                                            $total = ($item['votes']['yes'] ?? 0) + ($item['votes']['no'] ?? 0);
                                            if ($total > 0) {
                                                echo ($item['votes']['yes'] ?? 0) > ($item['votes']['no'] ?? 0) ? 'PASSED' : 'FAILED';
                                            } else {
                                                echo 'PENDING / NO VOTES';
                                            }
                                            ?>
                                        </p>
                                        <p>Yes: <?php echo $item['votes']['yes'] ?? 0; ?> | No:
                                            <?php echo $item['votes']['no'] ?? 0; ?> | Abstain:
                                            <?php echo $item['votes']['abstain'] ?? 0; ?></p>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

        <!-- Adjournment -->
        <div class="mb-16">
            <h3 class="font-black border-b border-gray-300 pb-2 mb-4 uppercase italic">III. ADJOURNMENT</h3>
            <p class="text-sm">Meeting was officially adjourned at
                <?php echo !empty($meeting['time_end']) ? date('g:i A', strtotime($meeting['time_end'])) : '____________________'; ?>.
            </p>
        </div>

        <!-- Signatures -->
        <div class="grid grid-cols-2 gap-20 pt-10">
            <div class="text-center">
                <p class="text-sm font-bold border-b-2 border-gray-900 pb-2 mb-1">
                    <?php echo strtoupper($_SESSION['user_name'] ?? 'Secretary'); ?>
                </p>
                <p class="text-xs italic uppercase">Prepared by</p>
                <p class="text-xs font-bold">Committee Secretary</p>
            </div>
            <div class="text-center">
                <p class="text-sm font-bold border-b-2 border-gray-900 pb-2 mb-1">
                    <?php echo strtoupper($committee['chair'] ?? 'Committee Chair'); ?>
                </p>
                <p class="text-xs italic uppercase">Attested by</p>
                <p class="text-xs font-bold">Committee Chairman</p>
            </div>
        </div>
    </div>

    <!-- Page Footer (Print only) -->
    <div class="hidden print:block fixed bottom-8 left-0 right-0 text-center text-[10px] text-gray-400">
        Generated by Legislative CMS on
        <?php echo date('F j, Y g:i A'); ?> | Official Draft Minutes
    </div>
</body>

</html>