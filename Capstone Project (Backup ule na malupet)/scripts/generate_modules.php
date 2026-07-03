#!/usr/bin/env php
<?php
/**
 * Module Tab Page Generator
 * 
 * This script generates tab-based module pages using a template.
 * Used to quickly create remaining 9 module pages with consistent structure.
 * 
 * Usage: php generate_modules.php [module_name] [num_tabs]
 * 
 * Example:
 *   php generate_modules.php "Member Assignment" 6
 *   php generate_modules.php "Referral Management" 7
 */

// Module configuration
$modules = [
    [
        'name' => 'Member Assignment',
        'path' => 'pages/member-assignment/directory.php',
        'icon' => 'bi bi-people',
        'color' => 'blue',
        'tabs' => [
            ['id' => 'assign-members', 'label' => 'Assign Members', 'icon' => 'bi-person-plus', 'color' => 'blue'],
            ['id' => 'define-roles', 'label' => 'Define Roles', 'icon' => 'bi-person-badge', 'color' => 'blue'],
            ['id' => 'expertise', 'label' => 'Expertise Tagging', 'icon' => 'bi-tag', 'color' => 'blue'],
            ['id' => 'substitutes', 'label' => 'Substitute Members', 'icon' => 'bi-person-fill', 'color' => 'blue'],
            ['id' => 'directory', 'label' => 'Member Directory', 'icon' => 'bi-phone', 'color' => 'blue'],
            ['id' => 'history', 'label' => 'Membership History', 'icon' => 'bi-clock-history', 'color' => 'blue'],
        ]
    ],
    [
        'name' => 'Referral Management',
        'path' => 'pages/referral-management/inbox.php',
        'icon' => 'bi bi-inbox',
        'color' => 'green',
        'tabs' => [
            ['id' => 'receive', 'label' => 'Receive Referrals', 'icon' => 'bi-arrow-down-circle', 'color' => 'green'],
            ['id' => 'inbox', 'label' => 'Referral Inbox', 'icon' => 'bi-inbox', 'color' => 'green'],
            ['id' => 'assignment', 'label' => 'Assignment', 'icon' => 'bi-distribution', 'color' => 'green'],
            ['id' => 'multi-committee', 'label' => 'Multi-Committee', 'icon' => 'bi-diagram-3', 'color' => 'green'],
            ['id' => 'acknowledgment', 'label' => 'Acknowledgment', 'icon' => 'bi-check-circle', 'color' => 'green'],
            ['id' => 'deadlines', 'label' => 'Deadlines', 'icon' => 'bi-calendar-event', 'color' => 'green'],
            ['id' => 'alerts', 'label' => 'Overdue Alerts', 'icon' => 'bi-bell', 'color' => 'green'],
        ]
    ],
    [
        'name' => 'Meeting Scheduler',
        'path' => 'pages/meeting-scheduler/view.php',
        'icon' => 'bi bi-calendar-event',
        'color' => 'purple',
        'tabs' => [
            ['id' => 'schedule', 'label' => 'Schedule Meetings', 'icon' => 'bi-calendar-plus', 'color' => 'purple'],
            ['id' => 'integration', 'label' => 'Calendar Integration', 'icon' => 'bi-calendar', 'color' => 'purple'],
            ['id' => 'recurring', 'label' => 'Recurring Meetings', 'icon' => 'bi-arrow-repeat', 'color' => 'purple'],
            ['id' => 'room-booking', 'label' => 'Room Booking', 'icon' => 'bi-door-closed', 'color' => 'purple'],
            ['id' => 'conflict', 'label' => 'Conflict Detection', 'icon' => 'bi-exclamation-circle', 'color' => 'purple'],
            ['id' => 'quorum', 'label' => 'Quorum Setting', 'icon' => 'bi-people-fill', 'color' => 'purple'],
            ['id' => 'cancellation', 'label' => 'Cancellation', 'icon' => 'bi-x-circle', 'color' => 'purple'],
        ]
    ],
    [
        'name' => 'Agenda Builder',
        'path' => 'pages/agenda-builder/create.php',
        'icon' => 'bi bi-list-check',
        'color' => 'yellow',
        'tabs' => [
            ['id' => 'create', 'label' => 'Create Agendas', 'icon' => 'bi-plus-circle', 'color' => 'yellow'],
            ['id' => 'ordinances', 'label' => 'Add Ordinances', 'icon' => 'bi-file-text', 'color' => 'yellow'],
            ['id' => 'prioritize', 'label' => 'Prioritization', 'icon' => 'bi-arrow-up-circle', 'color' => 'yellow'],
            ['id' => 'attachments', 'label' => 'Attachments', 'icon' => 'bi-paperclip', 'color' => 'yellow'],
            ['id' => 'time', 'label' => 'Time Allocation', 'icon' => 'bi-clock', 'color' => 'yellow'],
            ['id' => 'templates', 'label' => 'Templates', 'icon' => 'bi-file-earmark', 'color' => 'yellow'],
            ['id' => 'distribution', 'label' => 'Distribution', 'icon' => 'bi-share', 'color' => 'yellow'],
        ]
    ],
    [
        'name' => 'Deliberation Tools',
        'path' => 'pages/deliberation-tools/discussions.php',
        'icon' => 'bi bi-chat-dots',
        'color' => 'indigo',
        'tabs' => [
            ['id' => 'discussions', 'label' => 'Discussion Threads', 'icon' => 'bi-chat-left', 'color' => 'indigo'],
            ['id' => 'comments', 'label' => 'Comments & Notes', 'icon' => 'bi-chat-dots', 'color' => 'indigo'],
            ['id' => 'amendments', 'label' => 'Amendment Proposals', 'icon' => 'bi-pencil-square', 'color' => 'indigo'],
            ['id' => 'positions', 'label' => 'Position Tracking', 'icon' => 'bi-hand-thumbs-up', 'color' => 'indigo'],
            ['id' => 'voting', 'label' => 'Voting', 'icon' => 'bi-check-circle', 'color' => 'indigo'],
            ['id' => 'decisions', 'label' => 'Decisions', 'icon' => 'bi-gavel', 'color' => 'indigo'],
            ['id' => 'history', 'label' => 'Deliberation History', 'icon' => 'bi-clock-history', 'color' => 'indigo'],
        ]
    ],
    [
        'name' => 'Action Item Tracking',
        'path' => 'pages/action-items/all.php',
        'icon' => 'bi bi-lightning',
        'color' => 'pink',
        'tabs' => [
            ['id' => 'create', 'label' => 'Create Items', 'icon' => 'bi-plus-circle', 'color' => 'pink'],
            ['id' => 'assign', 'label' => 'Assign Tasks', 'icon' => 'bi-person-check', 'color' => 'pink'],
            ['id' => 'deadlines', 'label' => 'Deadlines', 'icon' => 'bi-calendar-event', 'color' => 'pink'],
            ['id' => 'progress', 'label' => 'Progress Tracking', 'icon' => 'bi-graph-up', 'color' => 'pink'],
            ['id' => 'verification', 'label' => 'Verification', 'icon' => 'bi-check2-circle', 'color' => 'pink'],
            ['id' => 'alerts', 'label' => 'Overdue Alerts', 'icon' => 'bi-exclamation-circle', 'color' => 'pink'],
            ['id' => 'reports', 'label' => 'Action Reports', 'icon' => 'bi-file-pdf', 'color' => 'pink'],
        ]
    ],
    [
        'name' => 'Report Generation',
        'path' => 'pages/report-generation/generate.php',
        'icon' => 'bi bi-file-pdf',
        'color' => 'orange',
        'tabs' => [
            ['id' => 'templates', 'label' => 'Report Templates', 'icon' => 'bi-file-earmark', 'color' => 'orange'],
            ['id' => 'automated', 'label' => 'Automated Drafting', 'icon' => 'bi-lightning', 'color' => 'orange'],
            ['id' => 'recommendations', 'label' => 'Recommendations', 'icon' => 'bi-chat-dots', 'color' => 'orange'],
            ['id' => 'minority', 'label' => 'Minority Reports', 'icon' => 'bi-file-text', 'color' => 'orange'],
            ['id' => 'approval', 'label' => 'Approval Workflow', 'icon' => 'bi-check-circle', 'color' => 'orange'],
            ['id' => 'trigger', 'label' => 'Second Reading Trigger', 'icon' => 'bi-arrow-right-circle', 'color' => 'orange'],
            ['id' => 'archiving', 'label' => 'Report Archiving', 'icon' => 'bi-archive', 'color' => 'orange'],
            ['id' => 'export', 'label' => 'Export/Share', 'icon' => 'bi-share', 'color' => 'orange'],
        ]
    ],
    [
        'name' => 'Inter-Committee Coordination',
        'path' => 'pages/inter-committee/joint.php',
        'icon' => 'bi bi-share',
        'color' => 'teal',
        'tabs' => [
            ['id' => 'coordination', 'label' => 'Joint Coordination', 'icon' => 'bi-diagram-3', 'color' => 'teal'],
            ['id' => 'messaging', 'label' => 'Message Boards', 'icon' => 'bi-chat-left-text', 'color' => 'teal'],
            ['id' => 'documents', 'label' => 'Document Sharing', 'icon' => 'bi-file-earmark-share', 'color' => 'teal'],
            ['id' => 'hearings', 'label' => 'Joint Hearings', 'icon' => 'bi-megaphone', 'color' => 'teal'],
            ['id' => 'reports', 'label' => 'Joint Reports', 'icon' => 'bi-file-pdf', 'color' => 'teal'],
            ['id' => 'referrals', 'label' => 'Inter-Referrals', 'icon' => 'bi-arrow-left-right', 'color' => 'teal'],
        ]
    ],
    [
        'name' => 'Research Support',
        'path' => 'pages/research-support/request.php',
        'icon' => 'bi bi-book',
        'color' => 'cyan',
        'tabs' => [
            ['id' => 'request', 'label' => 'Request Support', 'icon' => 'bi-hand-thumbs-up', 'color' => 'cyan'],
            ['id' => 'briefs', 'label' => 'Policy Briefs', 'icon' => 'bi-file-text', 'color' => 'cyan'],
            ['id' => 'analysis', 'label' => 'Legal Analysis', 'icon' => 'bi-scale', 'color' => 'cyan'],
            ['id' => 'comparative', 'label' => 'Comparative Legislation', 'icon' => 'bi-book-fill', 'color' => 'cyan'],
        ]
    ],
];

echo "Module Tab Page Generator\n";
echo "==========================\n\n";

echo "Modules ready to generate:\n";
foreach ($modules as $index => $module) {
    echo ($index + 1) . ". {$module['name']} ({$module['path']})\n";
}

echo "\nTo use this generator:\n";
echo "1. Run individual module generators\n";
echo "2. Or manually create using the template in MODULES_IMPLEMENTATION_COMPLETE.md\n";
echo "\nAll modules follow the same structure as Committee Structure module.\n";

?>
