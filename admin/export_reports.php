<?php
// admin/export_reports.php

session_start();

// Check if the user is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    die("Access Denied.");
}

// --- File Paths ---
$registrations_file = __DIR__ . '/data/registrations.csv';
$events_file = __DIR__ . '/data/events.csv';

// --- Helper function to read a CSV file ---
function read_csv($filePath) {
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

// --- Process Data for Reports ---
$all_registrations = read_csv($registrations_file);
$all_events = read_csv($events_file);

// Membership Stats
$member_stats = [
    'Total Members' => count($all_registrations),
    'Approved Members' => 0,
    'Pending Members' => 0,
    'Declined Members' => 0,
];
foreach ($all_registrations as $reg) {
    $status = strtolower($reg['Status'] ?? 'pending');
    if ($status === 'approved') $member_stats['Approved Members']++;
    elseif ($status === 'pending') $member_stats['Pending Members']++;
    elseif ($status === 'declined') $member_stats['Declined Members']++;
}

// Event Stats
$event_stats = [
    'Total Events' => count($all_events),
    'Upcoming Events' => 0,
    'Past Events' => 0,
];
foreach ($all_events as $event) {
    if (strtotime($event['Date']) >= strtotime('today')) {
        $event_stats['Upcoming Events']++;
    } else {
        $event_stats['Past Events']++;
    }
}

// --- Generate CSV output ---
$filename = "club_stats_export_" . date('Y-m-d') . ".csv";

header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="' . $filename . '"');

$output = fopen('php://output', 'w');

// Write header
fputcsv($output, ['Statistic', 'Value']);

// Write Membership Data
fputcsv($output, ['--- Membership Stats ---', '']);
foreach ($member_stats as $key => $value) {
    fputcsv($output, [$key, $value]);
}

// Write Event Data
fputcsv($output, ['--- Event Stats ---', '']);
foreach ($event_stats as $key => $value) {
    fputcsv($output, [$key, $value]);
}

fclose($output);
exit;
