<?php
require_once 'config/database.php';

// Check if templates already exist
$check = $conn->query("SELECT COUNT(*) FROM agenda_templates");
if ($check->fetch_row()[0] == 0) {
    // Insert Regular Session Template
    $conn->query("INSERT INTO agenda_templates (name, description) VALUES ('Regular Session', 'Standard template for weekly/monthly committee sessions')");
    $templateId = $conn->insert_id;

    $items = [
        ['Call to Order', 'Official start of the meeting', 5, 'Procedural', 1],
        ['Roll Call', 'Verification of quorum', 5, 'Procedural', 2],
        ['Approval of Minutes', 'Review and approval of previous session minutes', 10, 'Report', 3],
        ['Business for the Day', 'Discussion of current agenda items', 60, 'Discussion', 4],
        ['Other Matters', 'Any miscellaneous topics', 15, 'Discussion', 5],
        ['Adjournment', 'Official end of the meeting', 5, 'Procedural', 6]
    ];

    $stmt = $conn->prepare("INSERT INTO agenda_template_items (template_id, title, description, duration, item_type, item_order) VALUES (?, ?, ?, ?, ?, ?)");
    foreach ($items as $item) {
        $stmt->bind_param("issisi", $templateId, $item[0], $item[1], $item[2], $item[3], $item[4]);
        $stmt->execute();
    }

    // Insert Public Hearing Template
    $conn->query("INSERT INTO agenda_templates (name, description) VALUES ('Public Hearing', 'Standard template for public consultations and hearings')");
    $templateId = $conn->insert_id;

    $items = [
        ['Opening Remarks', 'Introduction and purpose of the hearing', 10, 'Procedural', 1],
        ['Presentation of Proposal', 'Detailed review of the ordinance or resolution', 30, 'Presentation', 2],
        ['Public Testimony', 'Comments from stakeholders and community members', 60, 'Public Input', 3],
        ['Committee Deliberation', 'Initial responses from committee members', 20, 'Discussion', 4],
        ['Closing Statement', 'Next steps and wrap-up', 5, 'Procedural', 5]
    ];

    foreach ($items as $item) {
        $stmt->bind_param("issisi", $templateId, $item[0], $item[1], $item[2], $item[3], $item[4]);
        $stmt->execute();
    }

    echo "Successfully seeded agenda templates.\n";
} else {
    echo "Templates already seeded.\n";
}
?>