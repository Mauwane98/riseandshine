<?php
// admin/export_members.php

session_start();

// Check if the user is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    // Or redirect to login, or just stop execution
    die("Access Denied.");
}

// --- File and data reading ---
$registrations_file = __DIR__ . '/data/registrations.csv';

function get_all_registrations($filePath) {
    if (!file_exists($filePath)) return [];
    $data = [];
    if (($handle = fopen($filePath, 'r')) !== false) {
        $header = fgetcsv($handle);
        if ($header === false) { fclose($handle); return []; }
        while (($row = fgetcsv($handle)) !== false) {
            if (is_array($row) && count($row) === count($header)) {
                $data[] = array_combine($header, $row);
            }
        }
        fclose($handle);
    }
    return $data;
}

// --- Get filters from URL ---
$all_registrations = get_all_registrations($registrations_file);
$filter = $_GET['filter'] ?? 'all';
$search_query = trim($_GET['search'] ?? '');

// --- Apply filters ---
$filtered_registrations = $all_registrations;

if ($filter !== 'all') {
    $filtered_registrations = array_filter($filtered_registrations, function($reg) use ($filter) {
        return isset($reg['Status']) && strtolower($reg['Status']) === $filter;
    });
}

if (!empty($search_query)) {
    $filtered_registrations = array_filter($filtered_registrations, function($reg) use ($search_query) {
        $name = $reg['Full Name'] ?? '';
        $email = $reg['Email'] ?? '';
        return stripos($name, $search_query) !== false || stripos($email, $search_query) !== false;
    });
}

// --- Generate CSV output ---
$filename = "members_export_" . date('Y-m-d') . ".csv";

header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="' . $filename . '"');

$output = fopen('php://output', 'w');

// Write header
if (!empty($filtered_registrations)) {
    fputcsv($output, array_keys($filtered_registrations[0]));
} else {
    // Write a default header if there are no results
    fputcsv($output, ['Full Name', 'Age', 'Email', 'Phone', 'Experience', 'Joining Fee', 'Proof File', 'Timestamp', 'Status']);
}


// Write data
foreach ($filtered_registrations as $row) {
    fputcsv($output, $row);
}

fclose($output);
exit;

?>
