<?php

// Get the file path from the query string
$file = isset($_GET['file']) ? $_GET['file'] : '';

if (empty($file)) {
    die('No file specified.');
}

// Path to the file you want to allow users to download
$fullPath = 'uploads/' . basename($file);

// Check if the file exists
if (file_exists($fullPath)) {
    // Set headers to force download
    header('Content-Description: File Transfer');
    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename="'.basename($fullPath).'"');
    header('Expires: 0');
    header('Cache-Control: must-revalidate');
    header('Pragma: public');
    header('Content-Length: ' . filesize($fullPath));
    flush(); // Flush system output buffer
    readfile($fullPath);
    exit;
} else {
    echo 'The file does not exist.';
}

?>
