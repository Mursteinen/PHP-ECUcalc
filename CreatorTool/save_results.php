<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $solution_type = $_POST['solution_type'];
    $ECU_type = $_POST['ECU_type'];
    $json_data = $_POST['json_data'];
    $ecu_sw1 = $_POST['ecu_sw1'];

    // Create directory path
    $directory_path = "../{$ECU_type}/{$ecu_sw1}_solutions";

    // Ensure the directory exists
    if (!is_dir($directory_path)) {
        mkdir($directory_path, 0777, true);
    }

    // Set the filename
    $filename = "{$directory_path}/{$solution_type}.json";

    // Decode the JSON data and reformat the keys as hexadecimal
    $data = json_decode($json_data, true);
    $formatted_data = [];
    foreach ($data as $offset => $value) {
        $formatted_data[sprintf("0x%08X", $offset)] = $value;
    }

    // Save the formatted data to the file
    file_put_contents($filename, json_encode($formatted_data, JSON_PRETTY_PRINT));

    echo "<div class='result-message'>Results saved to $filename</div>";
} else {
    echo "<div class='error-message'>Invalid request method.</div>";
}
?>
