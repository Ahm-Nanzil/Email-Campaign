<?php
/**
 * Automatic Email Sender
 * Sends emails to 400 clients at a time from clients.csv
 * Tracks progress and resumes from where it left off
 */
// Include PHPMailer classes
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Configuration 
$batchSize = 400;  // Number of emails to send per batch
$csvFile = 'clients.csv';
$trackingFile = 'email_tracking.json';
$emailTemplate = 'emailbody.html'; // Using your existing email template

// Initialize tracking data
function initializeTrackingData() {
    if (file_exists($GLOBALS['trackingFile'])) {
        $data = json_decode(file_get_contents($GLOBALS['trackingFile']), true);
        if (is_array($data)) {
            return $data;
        }
    }
    
    // Create tracking file if it doesn't exist with default values
    $defaultData = [
        'current_index' => 0,
        'total_processed' => 0,
        'last_batch_time' => null,
        'all_sent' => false
    ];
    
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
            $email = $data[0];
            $name = $data[1];
            $address = $data[2];
            $customerNumber = $data[3];
            $sentStatus = isset($data[4]) ? $data[4] : "No";
            
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
    if (($handle = fopen($GLOBALS['csvFile'], "r")) !== FALSE) {
        while (fgetcsv($handle) !== FALSE) {
            $rowCount++;
        }
        fclose($handle);
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
        $rows[$i][4] = "No"; // Reset sent status
    }
    
    // Write updated data back to CSV
    if (($handle = fopen($GLOBALS['csvFile'], "w")) !== FALSE) {
        foreach ($rows as $row) {
            fputcsv($handle, $row);
        }
        fclose($handle);
    }
}

// Send emails to clients using PHPMailer
function sendEmails($clients) {
    
    
    require 'vendor/autoload.php';
    
    $emailContent = file_get_contents($GLOBALS['emailTemplate']);
    $sentCount = 0;
    
    foreach ($clients as $client) {
        // Create a new PHPMailer instance for each email
        $mail = new PHPMailer(true);
        
        try {
            // Server settings
            $mail->isSMTP();                           
            $mail->Host       = 'smtp.gmail.com';      
            $mail->SMTPAuth   = true;                  
            $mail->Username   = 'ahmnanzil33dfsdf@gmail.com'; 
            $mail->Password   = 'hpitjdlzhhmnhurfdsfc'; 
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS; 
            $mail->Port       = 587;
            
            // Sender
            $mail->setFrom('ahmnanzil@web.service', 'Web Development');
            
            // Recipient
            $mail->addAddress($client['email'], $client['name']);
            
            // Replace placeholders in template
            $personalizedContent = str_replace(
                ['{{name}}', '{{email}}', '{{address}}', '{{customer_number}}'],
                [$client['name'], $client['email'], $client['address'], $client['customer_number']],
                $emailContent
            );
            
            // Content
            $mail->isHTML(true);
            $mail->CharSet = 'UTF-8';
            $mail->Subject = "Boost Your Online Presence with a Professional Website 🌐";
            $mail->Body = $personalizedContent;
            $mail->AltBody = strip_tags($personalizedContent);
            
            // Send email
            $mail->send();
            $sentCount++;
            
            // Log successful send
            error_log("Email sent successfully to: " . $client['email']);
            
            // Optional: Add a small delay to avoid overwhelming the server
            usleep(100000); // 100ms delay
            
        } catch (Exception $e) {
            // Log error but continue with next email
            error_log("Failed to send email to {$client['email']}: {$mail->ErrorInfo}");
        }
    }
    
    return $sentCount;
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
    
    // Send emails
    $sentCount = sendEmails($clients);
    
    // Mark clients as sent
    markClientsAsSent($clients);
    
    // Update tracking data
    $tracking['current_index'] = $result['last_index'];
    $tracking['total_processed'] += $sentCount;
    $tracking['last_batch_time'] = date('Y-m-d H:i:s');
    
    // Check if we've processed all clients
    if ($tracking['total_processed'] >= $totalRows) {
        $tracking['all_sent'] = true;
    }
    
    saveTrackingData($tracking);
    
    return [
        'status' => 'success',
        'sent_count' => $sentCount,
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
            header('Location: ' . $_SERVER['PHP_SELF'] . '?status=' . $result['status'] . '&sent=' . ($result['sent_count'] ?? 0));
            exit;
        }
    }
    
    // Otherwise, show the admin interface
    $tracking = initializeTrackingData();
    $totalRows = countTotalRows() - 1; // Subtract 1 for header
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Email Campaign Manager</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            background-color: #f5f5f5;
        }
        .container {
            max-width: 800px;
            margin: 50px auto;
            padding: 20px;
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        h1, h2 {
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
        .back-link {
            display: block;
            margin-top: 20px;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Email Campaign Manager</h1>
        
        <?php if (isset($_GET['status'])): ?>
            <?php if ($_GET['status'] == 'success'): ?>
                <div class="alert alert-success">
                    <strong>Success!</strong> Sent <?php echo $_GET['sent']; ?> emails successfully.
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
            <p><strong>Emails Sent:</strong> <?php echo $tracking['total_processed']; ?></p>
            <p><strong>Next Batch Starting At:</strong> <?php echo $tracking['current_index']; ?></p>
            <p><strong>Last Batch Sent:</strong> <?php echo $tracking['last_batch_time'] ? $tracking['last_batch_time'] : 'Never'; ?></p>
            
            <div class="progress-bar">
                <div class="progress-bar-fill"></div>
            </div>
            <p><?php echo round(($tracking['total_processed'] / max(1, $totalRows)) * 100, 1); ?>% Complete</p>
        </div>
        
        <!-- Use form for non-JS environments -->
        <form method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>" id="emailForm">
            <input type="hidden" name="action" value="sendEmails">
            <button type="submit" id="sendBatch">Send Next Batch (400 Emails)</button>
            <a href="resetCampaign.php" class="reset" id="resetCampaign" onclick="return confirm('Are you sure you want to reset the entire campaign? This will mark all emails as unsent.');">
                <button type="button" class="reset">Reset Campaign</button>
            </a>
        </form>
        
        <div id="result"></div>
        
        <div class="csv-info">
            <h3>CSV File Information</h3>
            <p>Make sure your clients.csv file has the following columns:</p>
            <ul>
                <li>Email - Client email address</li>
                <li>Customer Name - Client's name</li>
                <li>Address - Client's address</li>
                <li>Customer Number - Unique identifier</li>
                <li>Sent - Tracking column (Yes/No)</li>
            </ul>
            <p><strong>Note:</strong> The system will automatically update the "Sent" column as emails are processed.</p>
        </div>
        
        <a href="index.html" class="back-link">Back to Home</a>
    </div>
    
    <script>
        // Only use AJAX if JavaScript is enabled
        document.getElementById('emailForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const sendButton = document.getElementById('sendBatch');
            sendButton.disabled = true;
            sendButton.textContent = 'Sending...';

            
            fetch('autoEmailSender.php', {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'action=sendEmails'
            })
            .then(response => response.json())
            .then(data => {
                console.log(data);

                const resultDiv = document.getElementById('result');
                resultDiv.style.display = 'block';
                
                if (data.status === 'success') {
                    resultDiv.innerHTML = `
                        <div class="alert alert-success">
                            <h3>Batch Sent Successfully</h3>
                            <p>Sent ${data.sent_count} emails</p>
                            <p>Total processed: ${data.total_processed} out of ${data.total_clients}</p>
                            <p>Next batch will start at index: ${data.next_batch_starts_at}</p>
                        </div>
                    `;
                } else if (data.status === 'reset') {
                    resultDiv.innerHTML = `
                        <div class="alert alert-info">
                            <h3>Campaign Reset</h3>
                            <p>${data.message}</p>
                        </div>
                    `;
                } else {
                    resultDiv.innerHTML = `
                        <div class="alert alert-warning">
                            <h3>Batch Skipped</h3>
                            <p>${data.message}</p>
                        </div>
                    `;
                }
                
                // Reload the page to update stats
                setTimeout(() => {
                    window.location.reload();
                }, 3000);
            })
            .catch(error => {
                document.getElementById('result').innerHTML = `
                    <div class="alert alert-danger">
                        <h3>Error</h3>
                        <p>${error.message}</p>
                    </div>
                `;
                sendButton.disabled = false;
                sendButton.textContent = 'Send Next Batch (400 Emails)';
            });
        });
    </script>
</body>
</html>
<?php
}
?>