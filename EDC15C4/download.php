<?php

// Get the file path and SW version from the query string
$file = isset($_GET['file']) ? $_GET['file'] : '';
$sw_version = isset($_GET['sw_version']) ? $_GET['sw_version'] : '';

if (empty($file) || empty($sw_version)) {
    die('No file specified or SW version missing.');
}

// Path to the file you want to allow users to download
$fullPath = 'uploads/' . basename($file);

// Check if the file exists
if (file_exists($fullPath)) {
    // Extract the base filename without the directory
    $baseFilename = basename($file);

    // Modify the filename to include the SW version
    $filename = 'file_' . $sw_version . '_' . $baseFilename;

    // Set headers to force download
    header('Content-Description: File Transfer');
    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename="'.basename($filename).'"');
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
