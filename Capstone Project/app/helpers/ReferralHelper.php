<?php
/**
 * Referral Helper - Database Version
 * Manages referrals using MySQL database
 */

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/AuditHelper.php';

/**
 * Get all referrals with linked document and committee info
 */
function getAllReferrals()
{
    global $conn;

    $sql = "SELECT 
                r.*,
                ld.title,
                ld.document_number,
                ld.document_type as type,
                ld.description,
                ld.priority,
                ld.is_public,
                c.committee_name,
                CONCAT(u.first_name, ' ', u.last_name) as assigned_to_name
            FROM referrals r
            JOIN legislative_documents ld ON r.document_id = ld.document_id
            LEFT JOIN committees c ON r.to_committee_id = c.committee_id
            LEFT JOIN users u ON r.assigned_to_user_id = u.user_id
            ORDER BY r.created_at DESC";

    $result = $conn->query($sql);

    if (!$result) {
        error_log("Error fetching referrals: " . $conn->error);
        return [];
    }

    $referrals = [];
    while ($row = $result->fetch_assoc()) {
        $referrals[] = [
            'id' => $row['referral_id'],
            'document_id' => $row['document_id'],
            'title' => $row['title'],
            'document_number' => $row['document_number'],
            'type' => ucfirst($row['type']),
            'description' => $row['description'],
            'priority' => ucfirst($row['priority']),
            'status' => $row['status'],
            'committee_id' => $row['to_committee_id'],
            'committee_name' => $row['committee_name'] ?? 'Not Assigned',
            'assigned_to' => $row['assigned_to_name'] ?? 'Not Assigned',
            'assigned_member_id' => $row['assigned_to_user_id'],
            'deadline' => $row['deadline_date'],
            'created_at' => $row['created_at'],
            'notes' => $row['notes'],
            'is_public' => (bool) $row['is_public']
        ];
    }

    return $referrals;
}

/**
 * Get referral by ID
 */
function getReferralById($id)
{
    global $conn;

    $sql = "SELECT 
                r.*,
                ld.title,
                ld.document_number,
                ld.document_type as type,
                ld.description,
                ld.priority,
                ld.is_public,
                c.committee_name,
                CONCAT(u.first_name, ' ', u.last_name) as assigned_to_name
            FROM referrals r
            JOIN legislative_documents ld ON r.document_id = ld.document_id
            LEFT JOIN committees c ON r.to_committee_id = c.committee_id
            LEFT JOIN users u ON r.assigned_to_user_id = u.user_id
            WHERE r.referral_id = ?";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        return null;
    }

    $row = $result->fetch_assoc();
    return [
        'id' => $row['referral_id'],
        'document_id' => $row['document_id'],
        'title' => $row['title'],
        'document_number' => $row['document_number'],
        'type' => ucfirst($row['type']),
        'description' => $row['description'],
        'priority' => ucfirst($row['priority']),
        'status' => $row['status'],
        'committee_id' => $row['to_committee_id'],
        'committee_name' => $row['committee_name'] ?? 'Not Assigned',
        'assigned_to' => $row['assigned_to_name'] ?? 'Not Assigned',
        'assigned_member_id' => $row['assigned_to_user_id'],
        'deadline' => $row['deadline_date'],
        'created_at' => $row['created_at'],
        'notes' => $row['notes'],
        'is_public' => (bool) $row['is_public']
    ];
}

/**
 * Create new referral
 */
function createReferral($data)
{
    global $conn;

    $conn->begin_transaction();

    try {
        // 1. Create Legislative Document first
        $docType = strtolower($data['type']);
        $docPriority = strtolower($data['priority'] ?? 'normal');
        $isPublic = isset($data['is_public']) ? (int) $data['is_public'] : 1;
        $createdBy = $_SESSION['user_id'] ?? 1;

        // Generate document number if not provided
        $docNumber = $data['document_number'] ?? 'REF-' . date('YmdHis');

        $stmtDoc = $conn->prepare("INSERT INTO legislative_documents (document_number, document_type, title, description, assigned_committee_id, priority, is_public, created_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmtDoc->bind_param("ssssisii", $docNumber, $docType, $data['title'], $data['description'], $data['committee_id'], $docPriority, $isPublic, $createdBy);

        if (!$stmtDoc->execute()) {
            throw new Exception("Failed to create legislative document: " . $stmtDoc->error);
        }

        $documentId = $conn->insert_id;

        // 2. Create Referral
        $referralType = 'incoming';
        $assignedTo = $data['assigned_member_id'] ?? null;
        $assignedDate = date('Y-m-d H:i:s');
        $deadline = $data['deadline'] ?? null;

        $stmtRef = $conn->prepare("INSERT INTO referrals (document_id, referral_type, to_committee_id, assigned_to_user_id, assigned_date, deadline_date, notes, created_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmtRef->bind_param("isiisssi", $documentId, $referralType, $data['committee_id'], $assignedTo, $assignedDate, $deadline, $data['notes'], $createdBy);

        if (!$stmtRef->execute()) {
            throw new Exception("Failed to create referral: " . $stmtRef->error);
        }

        $referralId = $conn->insert_id;

        // Log the action
        logAuditAction(
            $_SESSION['user_id'] ?? null,
            'CREATE',
            'referrals',
            "Created new referral for document ID: $documentId ('{$data['title']}')"
        );

        $conn->commit();
        return $referralId;

    } catch (Exception $e) {
        $conn->rollback();
        error_log("Error creating referral: " . $e->getMessage());
        return false;
    }
}

/**
 * Update referral
 */
function updateReferral($id, $data)
{
    global $conn;

    $referral = getReferralById($id);
    if (!$referral)
        return false;

    $conn->begin_transaction();

    try {
        // 1. Update Legislative Document
        $docType = strtolower($data['type'] ?? $referral['type']);
        $docPriority = strtolower($data['priority'] ?? $referral['priority']);
        $isPublic = isset($data['is_public']) ? (int) $data['is_public'] : $referral['is_public'];

        $stmtDoc = $conn->prepare("UPDATE legislative_documents SET document_type = ?, title = ?, description = ?, assigned_committee_id = ?, priority = ?, is_public = ? WHERE document_id = ?");
        $stmtDoc->bind_param("sssisii", $docType, $data['title'], $data['description'], $data['committee_id'], $docPriority, $isPublic, $referral['document_id']);

        if (!$stmtDoc->execute()) {
            throw new Exception("Failed to update legislative document: " . $stmtDoc->error);
        }

        // 2. Update Referral
        $assignedTo = $data['assigned_member_id'] ?? $referral['assigned_member_id'];
        $deadline = $data['deadline'] ?? $referral['deadline'];
        $status = $data['status'] ?? $referral['status'];

        $stmtRef = $conn->prepare("UPDATE referrals SET to_committee_id = ?, assigned_to_user_id = ?, deadline_date = ?, status = ?, notes = ? WHERE referral_id = ?");
        $stmtRef->bind_param("iisssi", $data['committee_id'], $assignedTo, $deadline, $status, $data['notes'], $id);

        if (!$stmtRef->execute()) {
            throw new Exception("Failed to update referral: " . $stmtRef->error);
        }

        // Log the action
        logAuditAction(
            $_SESSION['user_id'] ?? null,
            'UPDATE',
            'referrals',
            "Updated referral ID: $id"
        );

        $conn->commit();
        return true;

    } catch (Exception $e) {
        $conn->rollback();
        error_log("Error updating referral: " . $e->getMessage());
        return false;
    }
}

/**
 * Delete referral and linked document
 */
function deleteReferral($id)
{
    global $conn;

    $referral = getReferralById($id);
    if (!$referral)
        return false;

    $conn->begin_transaction();

    try {
        // Referral will be deleted automatically if ON DELETE CASCADE is set on document_id, 
        // but let's be explicit if needed. The schema shows FOREIGN KEY (document_id) REFERENCES legislative_documents(document_id) ON DELETE CASCADE.

        $stmtDoc = $conn->prepare("DELETE FROM legislative_documents WHERE document_id = ?");
        $stmtDoc->bind_param("i", $referral['document_id']);

        if (!$stmtDoc->execute()) {
            throw new Exception("Failed to delete legislative document: " . $stmtDoc->error);
        }

        // Log the action
        logAuditAction(
            $_SESSION['user_id'] ?? null,
            'DELETE',
            'referrals',
            "Deleted referral ID: $id and its linked document"
        );

        $conn->commit();
        return true;

    } catch (Exception $e) {
        $conn->rollback();
        error_log("Error deleting referral: " . $e->getMessage());
        return false;
    }
}

/**
 * Get referrals by status
 */
function getReferralsByStatus($status)
{
    $referrals = getAllReferrals();
    return array_filter($referrals, function ($ref) use ($status) {
        return $ref['status'] === $status;
    });
}

/**
 * Get referrals by committee
 */
function getReferralsByCommittee($committeeId)
{
    $referrals = getAllReferrals();
    return array_filter($referrals, function ($ref) use ($committeeId) {
        return (int) $ref['committee_id'] === (int) $committeeId;
    });
}

/**
 * Update referral status
 */
function updateReferralStatus($id, $status)
{
    global $conn;
    $stmt = $conn->prepare("UPDATE referrals SET status = ? WHERE referral_id = ?");
    $stmt->bind_param("si", $status, $id);
    if ($stmt->execute()) {
        logAuditAction(
            $_SESSION['user_id'] ?? null,
            'UPDATE',
            'referrals',
            "Updated referral status to '{$status}' for referral ID: $id"
        );
        return true;
    }
    return false;
}
/**
 * Get overdue referrals
 */
function getOverdueReferrals()
{
    $referrals = getAllReferrals();
    $today = date('Y-m-d');
    return array_filter($referrals, function ($ref) use ($today) {
        return !empty($ref['deadline']) && $ref['deadline'] < $today && $ref['status'] !== 'Approved' && $ref['status'] !== 'Rejected' && $ref['status'] !== 'Implemented';
    });
}

/**
 * Get referrals within a deadline range
 */
function getReferralsByDeadline($start, $end)
{
    $referrals = getAllReferrals();
    return array_filter($referrals, function ($ref) use ($start, $end) {
        if (empty($ref['deadline']))
            return false;
        return $ref['deadline'] >= $start && $ref['deadline'] <= $end;
    });
}
