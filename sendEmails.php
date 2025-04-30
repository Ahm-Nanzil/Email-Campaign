<?php

$csvFile = 'clients.csv';
$tempFile = 'temp_clients.csv';
$rowsToProcess = 4;

if (!file_exists($csvFile)) {
    die("CSV file not found.");
}

$input = fopen($csvFile, 'r');
$output = fopen($tempFile, 'w');

$header = fgetcsv($input);
$header[] = 'Sent';
$rows = [];
$processed = 0;

// Function to send the email via POST to emailConfiguration.php
function sendEmailToConfiguration($email) {
    $url = 'emailConfiguration.php'; // Path to your email configuration page

    // Prepare POST data
    $data = [
        'email' => $email,
    ];

    // Use cURL to send the POST request
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    // Execute the request and get the response
    $response = curl_exec($ch);

    if (curl_errno($ch)) {
        echo 'cURL Error: ' . curl_error($ch);
    }

    curl_close($ch);
    
    return $response; // Optionally return response if needed for logging or debugging
}

// Load all data and mark those to be sent
while (($row = fgetcsv($input)) !== false) {
    $email = $row[0];
    $sentFlag = isset($row[4]) ? $row[4] : 'no';

    if ($processed < $rowsToProcess && strtolower($sentFlag) !== 'yes') {
        // Send the email via the POST request to emailConfiguration.php
        $response = sendEmailToConfiguration($email);

        // Check if the email was sent successfully and mark it
        if ($response) {
            $row[4] = 'yes'; // Mark as sent
            $processed++;
        }
    } else if (!isset($row[4])) {
        $row[] = $sentFlag;
    }
    
    $rows[] = $row;
}

// If all are marked, reset for next round
if ($processed === 0) {
    foreach ($rows as &$row) {
        $row[4] = 'no';
    }
    unset($row);
    $processed = 'reset';
}

// Write new data back to the CSV
fputcsv($output, $header);
foreach ($rows as $row) {
    fputcsv($output, $row);
}

fclose($input);
fclose($output);

rename($tempFile, $csvFile);

echo ($processed === 'reset') 
    ? "All leads were already sent. Resetting status for next round."
    : "Emails sent to $processed leads.";
?>
