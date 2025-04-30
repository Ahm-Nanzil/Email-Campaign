<?php

$inputFile = 'leads.csv';
$outputFile = 'clients.csv';

$existingEmails = [];
if (file_exists($outputFile) && ($existingHandle = fopen($outputFile, 'r')) !== FALSE) {
    fgetcsv($existingHandle); // skip header
    while (($row = fgetcsv($existingHandle)) !== FALSE) {
        $existingEmails[] = strtolower(trim($row[0]));
    }
    fclose($existingHandle);
}

if (($handle = fopen($inputFile, 'r')) !== FALSE) {
    $outputHandle = fopen($outputFile, 'a');
    
    $header = fgetcsv($handle); // skip header
    
    while (($row = fgetcsv($handle)) !== FALSE) {
        $email = strtolower(trim($row[0]));
        $customerName = $row[1];
        $address = $row[3];
        $customerNumber = $row[7];

        if (!in_array($email, $existingEmails)) {
            $existingEmails[] = $email;
            fputcsv($outputHandle, [$email, $customerName, $address, $customerNumber]);
        }
    }
    
    fclose($handle);
    fclose($outputHandle);
    
    echo "New unique leads have been added to $outputFile.\n";
    header("Location: index.php");
    exit;
} else {
    echo "Unable to open $inputFile.\n";
}

?>
