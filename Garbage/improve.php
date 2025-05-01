<?php
require 'vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Configuration - Customize these settings
$batchSize = 50;            // Emails per batch
$pauseBetweenEmails = 2;    // Seconds between individual emails
$maxExecutionTime = 600;    // Script max execution time (seconds)
$csvFile = 'clients.csv';
$trackingFile = 'email_tracking.json';
$emailTemplate = 'emailbody.html';
$logFile = 'email_log.txt';

// SMTP Configuration - You can add multiple SMTP accounts here
$smtpAccounts = [
    [
        'host' => 'smtp.gmail.com',
        'username' => 'ahmnanzil33@gmail.com',
        'password' => 'hpitjdlzhhmnhurc',
        'port' => 587,
        'encryption' => PHPMailer::ENCRYPTION_STARTTLS,
        'from_email' => 'ahmnanzil@web.service',
        'from_name' => 'Web Development',
        'daily_limit' => 100,    // Gmail typical daily limit
        'hourly_limit' => 20,    // Conservative hourly limit
        'emails_sent_today' => 0,
        'last_sent_time' => null
    ]
    // Add more SMTP accounts here if needed
    // Example:
    // [
    //     'host' => 'smtp.someotherservice.com',
    //     'username' => 'your_username',
    //     'password' => 'your_password',
    //     'port' => 587,
    //     'encryption' => PHPMailer::ENCRYPTION_STARTTLS,
    //     'from_email' => 'your_email@domain.com',
    //     'from_name' => 'Your Name',
    //     'daily_limit' => 500,
    //     'hourly_limit' => 100,
    //     'emails_sent_today' => 0,
    //     'last_sent_time' => null
    // ]
];

// Increase PHP limits for larger processing
ini_set('max_execution_time', $maxExecutionTime);
ini_set('memory_limit', '256M');

// Initialize logging function
function logMessage($message) {
    $timestamp = date('Y-m-d H:i:s');
    $logEntry = "[$timestamp] $message" . PHP_EOL;
    file_put_contents($GLOBALS['logFile'], $logEntry, FILE_APPEND);
    
    // Also write to error log for server logs
    error_log($message);
}

// Initialize tracking data
function initializeTrackingData() {
    if (file_exists($GLOBALS['trackingFile'])) {
        $data = json_decode(file_get_contents($GLOBALS['trackingFile']), true);
        if (is_array($data)) {
            // Update SMTP account tracking if exists
            if (isset($data['smtp_accounts'])) {
                foreach ($data['smtp_accounts'] as $index => $account) {
                    // Reset email counts if it's a new day
                    if (isset($account['last_reset_date']) && $account['last_reset_date'] != date('Y-m-d')) {
                        $data['smtp_accounts'][$index]['emails_sent_today'] = 0;
                        $data['smtp_accounts'][$index]['last_reset_date'] = date('Y-m-d');
                    }
                }
            } else {
                // Initialize SMTP account tracking if not present
                $data['smtp_accounts'] = [];
                foreach ($GLOBALS['smtpAccounts'] as $index => $account) {
                    $data['smtp_accounts'][$index] = [
                        'emails_sent_today' => 0,
                        'last_sent_time' => null,
                        'last_reset_date' => date('Y-m-d')
                    ];
                }
            }
            
            return $data;
        }
    }
    
    // Create tracking file if it doesn't exist with default values
    $defaultData = [
        'current_index' => 0,
        'total_processed' => 0,
        'last_batch_time' => null,
        'all_sent' => false,
        'smtp_accounts' => []
    ];
    
    // Initialize SMTP account tracking
    foreach ($GLOBALS['smtpAccounts'] as $index => $account) {
        $defaultData['smtp_accounts'][$index] = [
            'emails_sent_today' => 0,
            'last_sent_time' => null,
            'last_reset_date' => date('Y-m-d')
        ];
    }
    
    // Make sure the directory exists
    $dir = dirname($GLOBALS['trackingFile']);
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }
    
    // Save default data
    file_put_contents($GLOBALS['trackingFile'], json_encode($defaultData, JSON_PRETTY_PRINT));
    
    return $defaultData;
}

// Save tracking data
function saveTrackingData($data) {
    file_put_contents($GLOBALS['trackingFile'], json_encode($data, JSON_PRETTY_PRINT));
}

// Process CSV file
function getClientsFromCSV($startIndex, $batchSize) {
    $clients = [];
    $count = 0;
    $currentIndex = 0;
    
    if (($handle = fopen($GLOBALS['csvFile'], "r")) !== FALSE) {
        // Skip header row
        fgetcsv($handle);
        
        while (($data = fgetcsv($handle)) !== FALSE) {
            // Skip rows until we reach the starting index
            if ($currentIndex < $startIndex) {
                $currentIndex++;
                continue;
            }
            
            // Get client data
            if (count($data) >= 4) {
                $email = trim($data[0]);
                $name = trim($data[1]);
                $address = trim($data[2]);
                $customerNumber = trim($data[3]);
                $sentStatus = isset($data[4]) ? trim($data[4]) : "No";
                
                // Validate email
                if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    // Only include clients that haven't been marked as sent
                    if ($sentStatus !== "Yes") {
                        $clients[] = [
                            'email' => $email,
                            'name' => $name,
                            'address' => $address,
                            'customer_number' => $customerNumber,
                            'row_index' => $currentIndex
                        ];
                        
                        $count++;
                        if ($count >= $batchSize) {
                            break;
                        }
                    }
                } else {
                    logMessage("Invalid email skipped: $email at row " . ($currentIndex + 2));
                }
            } else {
                logMessage("Malformed CSV row skipped at index " . ($currentIndex + 2));
            }
            
            $currentIndex++;
        }
        fclose($handle);
    }
    
    return [
        'clients' => $clients,
        'last_index' => $currentIndex,
        'total_rows' => countTotalRows() - 1  // Subtract 1 for header
    ];
}

// Count total rows in CSV file
function countTotalRows() {
    $rowCount = 0;
    try {
        if (($handle = fopen($GLOBALS['csvFile'], "r")) !== FALSE) {
            while (fgetcsv($handle) !== FALSE) {
                $rowCount++;
            }
            fclose($handle);
        }
    } catch (Exception $e) {
        logMessage("Error counting CSV rows: " . $e->getMessage());
    }
    return $rowCount;
}

// Mark clients as sent in CSV
function markClientsAsSent($clients) {
    // Read entire CSV file
    $rows = [];
    if (($handle = fopen($GLOBALS['csvFile'], "r")) !== FALSE) {
        while (($data = fgetcsv($handle)) !== FALSE) {
            $rows[] = $data;
        }
        fclose($handle);
    }
    
    // Update sent status for processed clients
    foreach ($clients as $client) {
        $rowIndex = $client['row_index'] + 1; // +1 because we count header as row 0
        if (isset($rows[$rowIndex])) {
            // Make sure we have enough columns
            while (count($rows[$rowIndex]) < 5) {
                $rows[$rowIndex][] = "";
            }
            $rows[$rowIndex][4] = "Yes"; // Mark as sent
        }
    }
    
    // Write updated data back to CSV
    if (($handle = fopen($GLOBALS['csvFile'], "w")) !== FALSE) {
        foreach ($rows as $row) {
            fputcsv($handle, $row);
        }
        fclose($handle);
    }
}

// Reset all clients as unsent
function resetAllClients() {
    // Read entire CSV file
    $rows = [];
    if (($handle = fopen($GLOBALS['csvFile'], "r")) !== FALSE) {
        while (($data = fgetcsv($handle)) !== FALSE) {
            $rows[] = $data;
        }
        fclose($handle);
    }
    
    // Reset sent status for all rows except header
    for ($i = 1; $i < count($rows); $i++) {
        // Make sure we have enough columns
        while (count($rows[$i]) < 5) {
            $rows[$i][] = "";
        }
        $rows[$i][4] = "No"; // Reset sent status
    }
    
    // Write updated data back to CSV
    if (($handle = fopen($GLOBALS['csvFile'], "w")) !== FALSE) {
        foreach ($rows as $row) {
            fputcsv($handle, $row);
        }
        fclose($handle);
    }
    
    logMessage("Campaign reset: All clients marked as unsent");
}

// Get next available SMTP account
function getAvailableSMTPAccount($tracking) {
    global $smtpAccounts;
    
    foreach ($tracking['smtp_accounts'] as $index => $accountStats) {
        // Skip if account doesn't exist anymore in configuration
        if (!isset($smtpAccounts[$index])) {
            continue;
        }
        
        $account = $smtpAccounts[$index];
        $stats = $accountStats;
        
        // Reset counter if it's a new day
        if (isset($stats['last_reset_date']) && $stats['last_reset_date'] != date('Y-m-d')) {
            $stats['emails_sent_today'] = 0;
            $stats['last_reset_date'] = date('Y-m-d');
            $tracking['smtp_accounts'][$index] = $stats;
            saveTrackingData($tracking);
        }
        
        // Check if we're under the daily limit
        if ($stats['emails_sent_today'] < $account['daily_limit']) {
            // Check hourly limit and throttling
            $canSend = true;
            if (!empty($stats['last_sent_time'])) {
                // Calculate how many emails were sent in the last hour
                $lastHourEmails = 0;
                
                // Complex logic for hourly rate limiting could be added here
                // For simplicity, we'll just use a basic time-based throttle
                $timeSinceLastEmail = time() - strtotime($stats['last_sent_time']);
                if ($timeSinceLastEmail < 60) { // Less than 1 minute since last email
                    $canSend = false;
                }
            }
            
            if ($canSend) {
                return ['index' => $index, 'account' => $account];
            }
        }
    }
    
    // No available accounts found
    return null;
}

// Update SMTP account usage
function updateSMTPAccountUsage($tracking, $accountIndex, $emailsSent) {
    if (isset($tracking['smtp_accounts'][$accountIndex])) {
        $tracking['smtp_accounts'][$accountIndex]['emails_sent_today'] += $emailsSent;
        $tracking['smtp_accounts'][$accountIndex]['last_sent_time'] = date('Y-m-d H:i:s');
        $tracking['smtp_accounts'][$accountIndex]['last_reset_date'] = date('Y-m-d');
    }
    
    return $tracking;
}

// Send emails to clients using PHPMailer with improved error handling and rate limiting
function sendEmails($clients, $tracking) {
    
    try {
        $emailTemplatePath = __DIR__ . '/' . $GLOBALS['emailTemplate'];
        $emailBody = file_get_contents($emailTemplatePath);
    } catch (Exception $e) {
        logMessage("Error loading email template: " . $e->getMessage());
        return ['sent' => 0, 'failed' => count($clients), 'tracking' => $tracking];
    }
    
    $sentCount = 0;
    $failedCount = 0;
    $successfulClients = [];
    
    foreach ($clients as $client) {
        // Get available SMTP account
        $smtpData = getAvailableSMTPAccount($tracking);
        
        if (!$smtpData) {
            logMessage("No SMTP accounts available for sending. Daily/hourly limits reached.");
            break;
        }
        
        $accountIndex = $smtpData['index'];
        $account = $smtpData['account'];
        
        // Create a new PHPMailer instance for each email
        $mail = new PHPMailer(true);
        
        try {
            // Server settings
            $mail->isSMTP();
            $mail->Host = $account['host'];
            $mail->SMTPAuth = true;
            $mail->Username = $account['username'];
            $mail->Password = $account['password'];
            $mail->SMTPSecure = $account['encryption'];
            $mail->Port = $account['port'];
            
            // Add debug options for troubleshooting
            $mail->SMTPDebug = SMTP::DEBUG_OFF; // Set to DEBUG_SERVER or DEBUG_CONNECTION for troubleshooting
            
            // Sender
            $mail->setFrom($account['from_email'], $account['from_name']);
            
            // Recipient
            $mail->addAddress($client['email'], $client['name']);
            
            // Content
            $mail->isHTML(true);
            $mail->CharSet = 'UTF-8';
            $mail->Subject = "Boost Your Online Presence with a Professional Website 🌐";
            
            // Optional: Personalize email content
            // $personalizedContent = str_replace(
            //     ['{{name}}', '{{email}}', '{{address}}', '{{customer_number}}'],
            //     [$client['name'], $client['email'], $client['address'], $client['customer_number']],
            //     $emailBody
            // );
            // $mail->Body = $personalizedContent;
            
            // Simple approach for now
            $mail->Body = $emailBody;
            
            // Send email
            $mail->send();
            $sentCount++;
            $successfulClients[] = $client;
            
            // Log successful send
            logMessage("Email sent successfully to: " . $client['email']);
            
            // Update SMTP account usage
            $tracking = updateSMTPAccountUsage($tracking, $accountIndex, 1);
            
            // Save tracking data periodically (after every 10 emails)
            if ($sentCount % 10 == 0) {
                saveTrackingData($tracking);
            }
            
            // Add pause between emails to avoid triggering spam filters
            sleep($GLOBALS['pauseBetweenEmails']);
            
        } catch (Exception $e) {
            $failedCount++;
            // Log error but continue with next email
            logMessage("Failed to send email to {$client['email']}: {$mail->ErrorInfo}");
        }
    }
    
    // Mark successful clients as sent
    if (!empty($successfulClients)) {
        markClientsAsSent($successfulClients);
    }
    
    return [
        'sent' => $sentCount,
        'failed' => $failedCount,
        'tracking' => $tracking
    ];
}

// Main function to process and send emails
function processEmails() {
    // Initialize tracking data
    $tracking = initializeTrackingData();
    
    // Get clients for current batch
    $result = getClientsFromCSV($tracking['current_index'], $GLOBALS['batchSize']);
    $clients = $result['clients'];
    $totalRows = $result['total_rows'];
    
    // If no clients to process, check if we've processed everything
    if (empty($clients)) {
        if ($tracking['total_processed'] >= $totalRows || $tracking['all_sent']) {
            // Reset everything and start over
            resetAllClients();
            $tracking['current_index'] = 0;
            $tracking['total_processed'] = 0;
            $tracking['all_sent'] = false;
            saveTrackingData($tracking);
            return [
                'status' => 'reset',
                'message' => 'All clients have been processed. Starting over from the beginning.'
            ];
        } else {
            // Move to next batch position
            $tracking['current_index'] = $result['last_index'];
            saveTrackingData($tracking);
            return [
                'status' => 'skip',
                'message' => 'No new clients to process at this position. Moving to next batch.'
            ];
        }
    }
    
    logMessage("Starting to process batch of " . count($clients) . " emails");
    
    // Send emails with improved handling
    $sendResult = sendEmails($clients, $tracking);
    $sentCount = $sendResult['sent'];
    $failedCount = $sendResult['failed'];
    $tracking = $sendResult['tracking'];
    
    // Update tracking data
    $tracking['current_index'] = $result['last_index'];
    $tracking['total_processed'] += $sentCount;
    $tracking['last_batch_time'] = date('Y-m-d H:i:s');
    
    // Check if we've processed all clients
    if ($tracking['total_processed'] >= $totalRows) {
        $tracking['all_sent'] = true;
    }
    
    saveTrackingData($tracking);
    
    logMessage("Batch processing complete. Sent: $sentCount, Failed: $failedCount");
    
    return [
        'status' => 'success',
        'sent_count' => $sentCount,
        'failed_count' => $failedCount,
        'total_processed' => $tracking['total_processed'],
        'total_clients' => $totalRows,
        'next_batch_starts_at' => $tracking['current_index']
    ];
}

// Execute if this script is accessed directly
if (basename($_SERVER['SCRIPT_FILENAME']) == basename(__FILE__)) {
    // Check if this is an AJAX request or a direct POST request to process emails
    if ((isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') ||
        (isset($_POST['action']) && $_POST['action'] == 'sendEmails')) {
        $result = processEmails();
        
        // Return JSON response for AJAX requests, otherwise show a message
        if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
            header('Content-Type: application/json');
            echo json_encode($result);
            exit;
        } else {
            header('Location: ' . $_SERVER['PHP_SELF'] . '?status=' . $result['status'] . '&sent=' . ($result['sent_count'] ?? 0) . '&failed=' . ($result['failed_count'] ?? 0));
            exit;
        }
    }
    
    // Otherwise, show the admin interface
    $tracking = initializeTrackingData();
    $totalRows = countTotalRows() - 1; // Subtract 1 for header
    
    // Get SMTP account status
    $smtpStatus = [];
    foreach ($tracking['smtp_accounts'] as $index => $stats) {
        if (isset($GLOBALS['smtpAccounts'][$index])) {
            $account = $GLOBALS['smtpAccounts'][$index];
            $smtpStatus[] = [
                'email' => $account['username'],
                'sent_today' => $stats['emails_sent_today'],
                'daily_limit' => $account['daily_limit'],
                'last_sent' => $stats['last_sent_time']
            ];
        }
    }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Advanced Email Campaign Manager</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            background-color: #f5f5f5;
        }
        .container {
            max-width: 900px;
            margin: 50px auto;
            padding: 20px;
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        h1, h2, h3 {
            color: #333;
        }
        .stats {
            margin: 20px 0;
            padding: 15px;
            background-color: #f9f9f9;
            border-radius: 4px;
        }
        .progress-bar {
            height: 20px;
            background-color: #e0e0e0;
            border-radius: 4px;
            margin: 10px 0;
            overflow: hidden;
        }
        .progress-bar-fill {
            height: 100%;
            background-color: #4CAF50;
            width: <?php echo ($tracking['total_processed'] / max(1, $totalRows)) * 100; ?>%;
        }
        button {
            background-color: #4CAF50;
            color: white;
            border: none;
            padding: 10px 15px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            margin-right: 10px;
            margin-bottom: 10px;
        }
        button:hover {
            background-color: #45a049;
        }
        button.reset {
            background-color: #f44336;
        }
        button.reset:hover {
            background-color: #d32f2f;
        }
        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 4px;
        }
        .alert-success {
            background-color: #dff0d8;
            color: #3c763d;
            border: 1px solid #d6e9c6;
        }
        .alert-warning {
            background-color: #fcf8e3;
            color: #8a6d3b;
            border: 1px solid #faebcc;
        }
        .alert-info {
            background-color: #d9edf7;
            color: #31708f;
            border: 1px solid #bce8f1;
        }
        .alert-danger {
            background-color: #f2dede;
            color: #a94442;
            border: 1px solid #ebccd1;
        }
        #result {
            margin-top: 20px;
            padding: 15px;
            background-color: #f0f0f0;
            border-radius: 4px;
            display: none;
        }
        .csv-info {
            margin-top: 30px;
            padding: 15px;
            background-color: #f0f0f0;
            border-radius: 4px;
        }
        .smtp-accounts {
            margin-top: 30px;
            padding: 15px;
            background-color: #f0f0f0;
            border-radius: 4px;
        }
        .smtp-account {
            margin-bottom: 15px;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        .config-form {
            margin-top: 20px;
            padding: 15px;
            background-color: #f0f0f0;
            border-radius: 4px;
        }
        .form-group {
            margin-bottom: 15px;
        }
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        .form-group input {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        .back-link {
            display: block;
            margin-top: 20px;
            text-align: center;
        }
        .log-viewer {
            margin-top: 20px;
            padding: 15px;
            background-color: #f0f0f0;
            border-radius: 4px;
            max-height: 200px;
            overflow-y: auto;
        }
        .log-entry {
            font-family: monospace;
            margin-bottom: 5px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Advanced Email Campaign Manager</h1>
        
        <?php if (isset($_GET['status'])): ?>
            <?php if ($_GET['status'] == 'success'): ?>
                <div class="alert alert-success">
                    <strong>Success!</strong> Sent <?php echo $_GET['sent']; ?> emails successfully.
                    <?php if (isset($_GET['failed']) && $_GET['failed'] > 0): ?>
                        <br><strong>Warning:</strong> Failed to send <?php echo $_GET['failed']; ?> emails.
                    <?php endif; ?>
                </div>
            <?php elseif ($_GET['status'] == 'reset'): ?>
                <div class="alert alert-info">
                    <strong>Campaign Reset!</strong> All clients have been marked as unsent.
                </div>
            <?php elseif ($_GET['status'] == 'skip'): ?>
                <div class="alert alert-warning">
                    <strong>No New Clients!</strong> Moved to next batch position.
                </div>
            <?php endif; ?>
        <?php endif; ?>
        
        <div class="stats">
            <h2>Campaign Status</h2>
            <p><strong>Total Clients:</strong> <?php echo $totalRows; ?></p>
            <p><strong>Batch Size:</strong> <?php echo $batchSize; ?></p>
            <p><strong>Emails Sent:</strong> <?php echo $tracking['total_processed']; ?></p>
            <p><strong>Next Batch Starting At:</strong> <?php echo $tracking['current_index']; ?></p>
            <p><strong>Last Batch Sent:</strong> <?php echo $tracking['last_batch_time'] ? $tracking['last_batch_time'] : 'Never'; ?></p>
            
            <div class="progress-bar">
                <div class="progress-bar-fill"></div>
            </div>
            <p><?php echo round(($tracking['total_processed'] / max(1, $totalRows)) * 100, 1); ?>% Complete</p>
        </div>
        
        <div class="smtp-accounts">
            <h3>SMTP Account Status</h3>
            <?php foreach ($smtpStatus as $account): ?>
            <div class="smtp-account">
                <p><strong>Email:</strong> <?php echo $account['email']; ?></p>
                <p><strong>Sent Today:</strong> <?php echo $account['sent_today']; ?> / <?php echo $account['daily_limit']; ?></p>
                <p><strong>Last Email Sent:</strong> <?php echo $account['last_sent'] ? $account['last_sent'] : 'Never'; ?></p>
                <div class="progress-bar">
                    <div class="progress-bar-fill" style="width: <?php echo ($account['sent_today'] / max(1, $account['daily_limit'])) * 100; ?>%;"></div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        
        <!-- Use form for non-JS environments -->
        <form method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>" id="emailForm">
            <input type="hidden" name="action" value="sendEmails">
            <button type="submit" id="sendBatch">Send Next Batch</button>
            
            <a href="resetCampaign.php" class="reset" id="resetCampaign" onclick="return confirm('Are you sure you want to reset the entire campaign? This will mark all emails as unsent.');">
                <button type="button" class="reset">Reset Campaign</button>
            </a>
            
            <!-- Configuration form -->
            <div class="config-form">
                <h3>Configure Batch Settings</h3>
                <div class="form-group">
                    <label for="batchSize">Batch Size:</label>
                    <input type="number" id="batchSize" name="batchSize" min="1" max="100" value="<?php echo $batchSize; ?>">
                </div>
                <div class="form-group">
                    <label for="pauseBetweenEmails">Pause Between Emails (seconds):</label>
                    <input type="number" id="pauseBetweenEmails" name="pauseBetweenEmails" min="1" max="30" value="<?php echo $pauseBetweenEmails; ?>">
                </div>
                <button type="button" id="updateConfig">Update Configuration</button>
            </div>
        </form>
        
        <div id="result"></div>
        
        <!-- Log Viewer -->
        <div class="log-viewer">
            <h3>Recent Log Entries</h3>
            <div id="logEntries">
            <?php 
            if (file_exists($logFile)) {
                $logs = array_slice(array_filter(explode("\n", file_get_contents($logFile))), -10);
                foreach ($logs as $log) {
                echo '<div class="log-entry">' . htmlspecialchars($log) . '</div>';
                }
            } else {
                echo '<div class="log-entry">No log entries found.</div>';
            }
            ?>
            </div>
        </div>
        <div class="back-link">
            <p style="text-align: center; margin-top: 20px; color: #666;">
            Developed by Ahm Nanzil &copy; <?php echo date('Y'); ?>
            </p>
        </div>
    </div>
</body>
</html>
<?php
}
?>