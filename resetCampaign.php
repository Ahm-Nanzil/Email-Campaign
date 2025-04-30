<?php
/**
 * Reset Campaign Script
 * Resets all clients as unsent and restarts tracking
 */

// Include the main script to access functions
require_once 'index.php';

// Reset all clients in CSV
resetAllClients();

// Reset tracking data
$trackingData = [
    'current_index' => 0,
    'total_processed' => 0,
    'last_batch_time' => null,
    'all_sent' => false
];
saveTrackingData($trackingData);

// Redirect back to main page
header('Location: index.php');
exit;
?>