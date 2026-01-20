<?php
require_once 'config/database.php';

echo "Deep Cleaning and Seeding sample data for Reports & Analytics...\n";

// Disable foreign key checks for clean up
$conn->query("SET FOREIGN_KEY_CHECKS = 0");
$conn->query("TRUNCATE TABLE attendance_records");
$conn->query("TRUNCATE TABLE referrals");
$conn->query("TRUNCATE TABLE legislative_documents");
$conn->query("TRUNCATE TABLE meetings"); // Truncate meetings too for clean trend
$conn->query("SET FOREIGN_KEY_CHECKS = 1");

// Get a valid user_id
$user_res = $conn->query("SELECT user_id FROM users LIMIT 1");
$user = $user_res->fetch_assoc();
$valid_user_id = $user ? (int) $user['user_id'] : 1;

// Get a valid committee_id
$comm_res = $conn->query("SELECT committee_id FROM committees LIMIT 1");
$comm = $comm_res->fetch_assoc();
$valid_comm_id = $comm ? (int) $comm['committee_id'] : 1;

echo "Using User ID: $valid_user_id, Committee ID: $valid_comm_id\n";

// 1. Seed Legislative Documents & Referrals
$doc_data = [
    ['REF-2025-001', 'ordinance', 'Annual Budget 2025', 'Approved', 15],
    ['REF-2025-002', 'resolution', 'Health Reform Act', 'Approved', 10],
    ['REF-2025-003', 'committee_report', 'Safety Audit', 'Approved', 20],
    ['REF-2024-099', 'ordinance', 'Zoning Revision', 'Approved', 45],
    ['REF-2025-004', 'ordinance', 'Plastic Ban V2', 'Under Review', 0],
    ['REF-2025-005', 'resolution', 'Volunteer Honor', 'Approved', 5],
    ['REF-2025-006', 'ordinance', 'Traffic Code', 'Pending', 0]
];

foreach ($doc_data as $d) {
    $offset = (int) $d[4];
    $created_at = date('Y-m-d H:i:s', strtotime('-' . ($offset + rand(5, 15)) . ' days'));
    $updated_at = ($d[3] === 'Approved') ? date('Y-m-d H:i:s', strtotime($created_at . ' +' . $offset . ' days')) : $created_at;

    $stmt = $conn->prepare("INSERT INTO legislative_documents (document_number, document_type, title, description, assigned_committee_id, status, created_by, created_at, updated_at) VALUES (?, ?, ?, 'Sample system-generated description.', ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssisiss", $d[0], $d[1], $d[2], $valid_comm_id, $d[3], $valid_user_id, $created_at, $updated_at);
    $stmt->execute();
    $doc_id = $conn->insert_id;

    if ($doc_id > 0) {
        $stmtRef = $conn->prepare("INSERT INTO referrals (document_id, referral_type, to_committee_id, status, assigned_date, created_by, created_at, updated_at) VALUES (?, 'incoming', ?, ?, ?, ?, ?, ?)");
        // Placeholders (?): 7 (excluding 'incoming')
        // Variables: doc_id(i), valid_comm_id(i), d[3](s), created_at(s), valid_user_id(i), created_at(s), updated_at(s)
        // Total variables: 7
        // Type string: iisssis (Wait, let's count: i, i, s, s, i, s, s) -> iisssis? No, 7 is: iississ? Let's count again:
        // 1:i, 2:i, 3:s, 4:s, 5:i, 6:s, 7:s -> iisssis is 7 chars.
        $stmtRef->bind_param("iisssis", $doc_id, $valid_comm_id, $d[3], $created_at, $valid_user_id, $created_at, $updated_at);
        $stmtRef->execute();
        echo "Successfully seeded referral for: {$d[0]}\n";
    }
    echo "Seeded: {$d[0]}\n";
}

// 2. Seed Meetings & Attendance (Past 4 Months)
$user_ids_res = $conn->query("SELECT user_id FROM users");
$user_ids = [];
while ($row = $user_ids_res->fetch_assoc())
    $user_ids[] = (int) $row['user_id'];

for ($i = 0; $i < 5; $i++) {
    $date = date('Y-m-d', strtotime("-$i months"));
    // Fix: Using meeting_title as per database schema
    $stmtM = $conn->prepare("INSERT INTO meetings (meeting_title, meeting_date, committee_id, status, created_by) VALUES (?, ?, ?, 'Completed', ?)");
    $m_title = "Strategic Session - " . date('F Y', strtotime($date));
    $m_date = $date . " 09:00:00";
    $stmtM->bind_param("ssii", $m_title, $m_date, $valid_comm_id, $valid_user_id);
    $stmtM->execute();
    $m_id = $conn->insert_id;

    foreach ($user_ids as $u_id) {
        $s = (rand(1, 10) > 3) ? 'present' : 'absent';
        $stmtAt = $conn->prepare("INSERT INTO attendance_records (meeting_id, user_id, status) VALUES (?, ?, ?)");
        $stmtAt->bind_param("iis", $m_id, $u_id, $s);
        $stmtAt->execute();
    }
    echo "Seeded Meeting for: $date\n";
}

echo "Seeding complete.\n";
