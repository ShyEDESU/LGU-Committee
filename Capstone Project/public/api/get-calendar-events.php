<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../../config/session_config.php';
require_once __DIR__ . '/../../app/helpers/MeetingHelper.php';
require_once __DIR__ . '/../../app/helpers/DataHelper.php';

// Get all meetings
$meetings = getAllMeetings();

// Convert to FullCalendar format
$events = [];
foreach ($meetings as $meeting) {
    $events[] = [
        'id' => $meeting['id'],
        'title' => $meeting['title'],
        'start' => $meeting['date'] . 'T' . $meeting['time_start'],
        'end' => $meeting['date'] . 'T' . $meeting['time_end'],
        'description' => $meeting['description'],
        'committee_id' => $meeting['committee_id'],
        'committee_name' => $meeting['committee_name'],
        'venue' => $meeting['venue'],
        'status' => $meeting['status'],
        'url' => '../committee-meetings/view.php?id=' . $meeting['id']
    ];
}

echo json_encode($events);
