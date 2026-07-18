<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../../config/session_config.php';
require_once __DIR__ . '/../../config/database.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['response' => "Session expired. Please log in to chat with the legislative assistant."]);
    exit();
}

$input = json_decode(file_get_contents('php://input'), true);
$message = trim($input['message'] ?? '');

if (empty($message)) {
    echo json_encode(['response' => "I didn't catch that. Could you please type something?"]);
    exit();
}

$lower = strtolower($message);
$reply = "";

// 1. KNOWLEDGE DATABASE (Valenzuela Council & CMS Help)
if (preg_match('/(hi|hello|hey|greetings)/i', $lower)) {
    $reply = "Hello! I am your **Valenzuela Legislative Assistant**. 🏛️\n\nI can help you navigate the Committee Management System, find information about standing committees, or explain legislative rules. What would you like to know today?";
}
elseif (preg_match('/(committee|committees)/i', $lower) && (preg_match('/(list|show|how many|what are)/i', $lower) || preg_match('/(valenzuela)/i', $lower))) {
    $reply = "Valenzuela City currently operates with **24 Standing Committees** in the Sanggunian Panlungsod. Key committees include:\n\n" .
             "- ⚖️ **Laws, Rules & Reorganization** (Chair: Hon. Ramon Encarnacion)\n" .
             "- 💰 **Appropriations, Ways & Means** (Chair: Hon. Kate Galang-Coseteng)\n" .
             "- 🏥 **Health & Sanitation** (Chair: Hon. Jenny Pingree-Esplana)\n" .
             "- 🎓 **Education** (Chair: Hon. Mar Nolasco)\n" .
             "- 🏗️ **Public Works & Infrastructure** (Chair: Hon. Gerry Esplana)\n\n" .
             "You can view all active rosters and schedules by navigating to the **Committee Profiles** tab in the sidebar!";
}
elseif (preg_match('/(report|reports)/i', $lower) && preg_match('/(how to|draft|create|vote|sign)/i', $lower)) {
    $reply = "### 📄 Working with Committee Reports\n\n" .
             "1. **Drafting:** Committee Chairs, Vice-Chairs, and Secretaries can draft reports. Go to **Committee Profiles**, choose your committee, scroll to the **Committee Reports** section, and click **Draft New**.\n" .
             "2. **Voting:** Once a draft is saved and set to *Voting* status, members will receive a notification. They can view the report and cast their signature vote (**Approve / Dissent / Abstain**).\n" .
             "3. **Enactment:** A report must receive a majority of *Approved* votes to be finalized and sent to the plenary.";
}
elseif (preg_match('/(meeting|meetings)/i', $lower) && preg_match('/(emergency|request)/i', $lower)) {
    $reply = "### 🚨 Requesting Emergency Meetings\n\n" .
             "If a critical legislative item requires immediate attention:\n" .
             "1. Go to your **Committee Profile**.\n" .
             "2. Click the **Meetings** tab.\n" .
             "3. Fill out the **Request Emergency Meeting** form (specify the title, reason, and date).\n" .
             "4. All system Admins will be notified instantly to review and approve your request.";
}
elseif (preg_match('/(document|documents|upload)/i', $lower) && preg_match('/(how to|where|add)/i', $lower)) {
    $reply = "### 📁 Uploading Documents\n\n" .
             "You can add reference files, charter rules, or draft ordinances:\n" .
             "1. Navigate to **Committee Profiles** and open your committee.\n" .
             "2. Click the **Documents** tab.\n" .
             "3. Click **Upload Document**.\n" .
             "4. Fill in the title, select the file (PDF, DOC, DOCX up to 10MB), and submit.";
}
elseif (preg_match('/(action item|action items|task|tasks)/i', $lower)) {
    $reply = "### 📋 Action Items & Task Reminders\n\n" .
             "- **Kanban Board:** Go to **Action Items** in the sidebar to drag-and-drop tasks between *To Do*, *In Progress*, and *Done*.\n" .
             "- **Notifications:** Assigned users are automatically notified when a task is created.\n" .
             "- **Deadline Reminders:** The system scans upcoming deadlines daily and will alert you **3 days before**, **1 day before**, and **on the due date**.";
}
elseif (preg_match('/(councilor|councilors|member|members)/i', $lower)) {
    $reply = "Valenzuela City Councilors (Sanggunian Panlungsod) include:\n\n" .
             "- **Hon. Katherine 'Kate' Galang-Coseteng**\n" .
             "- **Hon. Niña Shiela 'Ninang' Lopez**\n" .
             "- **Hon. Gerald 'Gerry' Esplana**\n" .
             "- **Hon. Jennifer 'Jenny' Pingree-Esplana**\n" .
             "- **Hon. Marlon Paulo 'Mar' Nolasco**\n" .
             "- **Hon. Ramon L. Encarnacion**\n" .
             "- **Hon. Ricardo 'Riki' Ricart**\n" .
             "- **Hon. Aloysius Arthur 'Art' Herrera**\n" .
             "- **Hon. Joseph Albert 'Jobo' Templonuevo**\n" .
             "- **Hon. Christian 'Ian' Feliciano**\n" .
             "- **Hon. Carlito 'Lito' De Guzman**\n" .
             "- **Hon. Mickey S. Pineda**\n\n" .
             "You can manage user roles and profiles via **User Management** (Admins only).";
}
elseif (preg_match('/(ordinance|ordinances|referral|referrals)/i', $lower)) {
    $reply = "### ⚖️ Legislative Items & Referrals\n\n" .
             "A referral is an ordinance or resolution sent by the city secretary to a committee for review.\n\n" .
             "- **Committee Action:** The committee deliberates, assigns action items (tasks), and drafts a **Committee Report** recommending its approval or disapproval.\n" .
             "- **Ordinance Integration:** Once the committee report is approved, it goes back to the main session for final passage into the permanent Ordinance database.";
}
else {
    // Default fallback assistant response
    $reply = "I understand you're asking about something related to LGU operations. I can assist you with:\n\n" .
             "1. **Committees** (e.g., 'What committees are in Valenzuela?')\n" .
             "2. **Reports** (e.g., 'How do I draft a committee report?')\n" .
             "3. **Emergency Meetings** (e.g., 'How to request emergency meeting?')\n" .
             "4. **Tasks** (e.g., 'How do action items and reminders work?')\n" .
             "5. **Documents** (e.g., 'How to upload documents?')\n\n" .
             "Please rephrase your question using keywords like **committees**, **reports**, **tasks**, **meetings**, or **councilors** so I can find the exact guidelines for you!";
}

echo json_encode(['response' => $reply]);
exit();
