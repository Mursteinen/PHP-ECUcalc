<?php
session_start();

// Check if the file path is stored in the session
if (!isset($_SESSION['file_path'])) {
    echo "<h2 style='text-align: center;'>Error: No file uploaded.</h2>";
    exit;
}

$file_path = $_SESSION['file_path'];
$file_name = basename($file_path);

// Get the software version result from the session
if (!isset($_SESSION['sw_version_result'])) {
    echo "<h2 style='text-align: center;'>Error: SW version not available.</h2>";
    exit;
}

$sw_version_result = $_SESSION['sw_version_result'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get the uploaded file's path from the session
    $uploadedFile = $file_path;

    // Generate the base file name with the current date
    $date = date('Ymd');  // Format: YYYYMMDD
    $baseFileName = $date;
    
    // Initialize suffix variable
    $suffix = '';

    // Initialize an array to keep track of successfully applied solutions
    $appliedSolutions = [];

    // Function to load solution data from JSON file
    function loadSolutionData($solution, $sw_version_result) {
        $filePath = "./{$sw_version_result}_solutions/" . $solution . '.json';
        if (file_exists($filePath)) {
            $jsonContent = file_get_contents($filePath);
            return json_decode($jsonContent, true);
        } else {
            return null;
        }
    }

    // Open the binary file for reading and writing
    $file = fopen($uploadedFile, 'r+b');

    if (!$file) {
        die('Unable to open file!');
    }

    // Get selected solutions from the form
    $selectedSolutions = isset($_POST['solutions']) ? $_POST['solutions'] : [];

    // Debugging: Output the selected solutions
    echo "<p>Selected solutions: " . implode(', ', $selectedSolutions) . "</p>";

    // Apply updates based on selected solutions
    foreach ($selectedSolutions as $solution) {
        $solutionData = loadSolutionData($solution, $sw_version_result);
        if ($solutionData) {
            // Append the correct suffix based on selected solutions
            switch ($solution) {
                case 'IMMO':
                    $suffix .= '_IMMOoff';
                    break;
                case 'EGR':
                    $suffix .= '_EGRoff';
                    break;
                case 'DPF':
                    $suffix .= '_DPFoff';
                    break;
            }
            
            // Add to applied solutions list
            $appliedSolutions[] = $solution;
            
            // Apply updates for this solution
            foreach ($solutionData as $offsetHex => $dataHex) {
                // Convert the hexadecimal offset to a decimal integer
                $offset = hexdec($offsetHex);

                // Seek to the offset
                fseek($file, $offset);

                // Pack the data as binary
                $binaryData = pack('H*', $dataHex);

                // Write the binary data
                fwrite($file, $binaryData);
            }
        } else {
            echo "<p style='color: red;'>No solution data found for $solution.</p>";
        }
    }

    // Close the file
    fclose($file);

    // Define the final file name with the appropriate suffix
    $destinationFile = 'uploads/' . $baseFileName . $suffix . '_NoCS_' . '.bin';

    // Rename the file to include the suffix
    if (!rename($uploadedFile, $destinationFile)) {
        die('Failed to rename the file.');
    }

    // Include CSS for styling
    echo '<style>
            body {
                text-align: center;
                font-family: Arial, sans-serif;
            }
            .download-button {
                display: inline-block;
                padding: 10px 20px;
                font-size: 16px;
                color: #fff;
                background-color: #007bff;
                border: none;
                border-radius: 5px;
                text-decoration: none;
                text-align: center;
                cursor: pointer;
                margin-top: 20px;
            }
            .download-button:hover {
                background-color: #0056b3;
            }
          </style>';

    // Display the message with applied solutions and the styled download button
    echo '<h2>Binary file updated successfully</h2>';
    if (!empty($appliedSolutions)) {
        echo '<p>Applied solution(s): ' . implode(', ', $appliedSolutions) . '</p>';
    }
    echo '<a href="download.php?file=' . urlencode($destinationFile) . '&sw_version=' . urlencode($sw_version_result) . '" class="download-button">Download the updated file</a>';
} else {
    echo 'Invalid request method.';
}
?>
