<?php
function compare_files($file1_path, $file2_path) {
    $file1_contents = file_get_contents($file1_path);
    $file2_contents = file_get_contents($file2_path);

    if ($file1_contents === false || $file2_contents === false) {
        return "Error reading one of the files.";
    }

    if (strlen($file1_contents) != strlen($file2_contents)) {
        return "Files are not the same size. Comparison aborted.";
    }

    $differences = [];
    $length = strlen($file1_contents);

    for ($i = 0; $i < $length; $i++) {
        if ($file1_contents[$i] !== $file2_contents[$i]) {
            $differences[] = [
                'offset' => sprintf("%08X", $i),
                'file1' => sprintf("%02X", ord($file1_contents[$i])),
                'file2' => sprintf("%02X", ord($file2_contents[$i]))
            ];
        }
    }

    return $differences;
}

function read_ecu_type($file_path, $offset, $length) {
    $file_contents = file_get_contents($file_path, false, null, $offset, $length);
    return $file_contents === false ? "Error reading ECU type." : trim($file_contents);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $file1 = $_FILES['file1'];
    $file2 = $_FILES['file2'];
    $ECU_type = $_POST['ECU_type'];

    if ($file1['error'] !== UPLOAD_ERR_OK || $file2['error'] !== UPLOAD_ERR_OK) {
        echo "Error uploading files.";
        exit;
    }

    $file1_path = $file1['tmp_name'];
    $file2_path = $file2['tmp_name'];

    // Set offset and length based on selected ECU type
    $ecu_specs = [
        'EDC17' => [0x1A, 18],
        'EDC15C4' => [0x7BFB4, 6],
        'BMW_EDC16' => [0x170038, 6]

    ];

    if (!isset($ecu_specs[$ECU_type])) {
        echo "Unknown ECU type.";
        exit;
    }

    list($offset, $length) = $ecu_specs[$ECU_type];

    $ecu_sw1 = read_ecu_type($file1_path, $offset, $length);
    $ecu_sw2 = read_ecu_type($file2_path, $offset, $length);

    $differences = compare_files($file1_path, $file2_path);

    echo "<link rel='stylesheet' href='combined_styles.css'>";
    echo "<div class='result-container'>";
    echo "<h2>Comparison Results:</h2>";
    echo "<p><strong>ECU Type File 1:</strong> $ecu_sw1</p>";
    echo "<p><strong>ECU Type File 2:</strong> $ecu_sw2</p>";

    if (empty($differences)) {
        echo "<p>Files are identical.</p>";
    } else {
        $json_data = [];
        foreach ($differences as $diff) {
            $json_data[sprintf("%d", hexdec($diff['offset']))] = $diff['file2'];
        }

        $json_string = json_encode($json_data, JSON_PRETTY_PRINT);

        echo "<form method='POST' action='save_results.php'>";
        echo "<div class='form-group'>";
        echo "<label for='solution_type'>Select Solution Type:</label>";
        echo "<select name='solution_type' id='solution_type' required>";
        echo "<option value='EGR'>EGR</option>";
        echo "<option value='DPF'>DPF</option>";
        echo "<option value='IMMO'>IMMO</option>";
        echo "<option value='REMAP'>REMAP</option>";
        echo "</select>";
        echo "</div>";
        echo "<input type='hidden' name='ECU_type' value='$ECU_type'>";
        echo "<input type='hidden' name='ecu_sw1' value='" . htmlspecialchars($ecu_sw1, ENT_QUOTES, 'UTF-8') . "'>";
        echo "<input type='hidden' name='json_data' value='" . htmlspecialchars($json_string, ENT_QUOTES, 'UTF-8') . "'>";
        echo "<table class='comparison-table'>
                <thead>
                    <tr>
                        <th>Offset</th>
                        <th>Old Value</th>
                        <th>New Value</th>
                    </tr>
                </thead>
                <tbody>";

        foreach ($differences as $diff) {
            echo "<tr>
                    <td>0x{$diff['offset']}</td>
                    <td>{$diff['file1']}</td>
                    <td>{$diff['file2']}</td>
                  </tr>";
        }

        echo "  </tbody>
              </table>";
        echo "<button type='submit' name='save' class='btn-save'>Make into solution</button>";
        echo "</form>";
    }
    echo "</div>";
}
?>
