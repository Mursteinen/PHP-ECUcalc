<?php
session_start();

function read_sw_version($file_path, $offset, $length) {
    $file_contents = file_get_contents($file_path, false, null, $offset, $length);
    if ($file_contents === false) {
        return "Error reading SW version.";
    }
    return trim($file_contents);
}

$sw_version_result = '';
$selectedSolutions = []; // Initialize the variable to avoid undefined variable error

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_FILES['file']) && $_FILES['file']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['file'];
        $uploadDirectory = 'uploads/';

        if (!is_dir($uploadDirectory)) {
            mkdir($uploadDirectory, 0777, true);
        }

        $filePath = $uploadDirectory . basename($file['name']);
        
        if (move_uploaded_file($file['tmp_name'], $filePath)) {
            $_SESSION['file_path'] = $filePath;

            // Adjust the offset and length as needed for your specific SW version
            $offset = 0x7BFB4; // Example offset
            $length = 6;   // Example length

            $sw_version_result = read_sw_version($filePath, $offset, $length);

            if (strpos($sw_version_result, 'Error') !== false) {
                $sw_version_result = "Error reading SW version.";
            } else {
                // Store the SW version result in the session
                $_SESSION['sw_version_result'] = $sw_version_result;

                switch ($sw_version_result) {
                    case '351210':
                    case '351761':
                        $showForm = true;
                        break;
                    default:
                        $showForm = false;
                        echo "<div class='unknown-version'>Unknown SW version: $sw_version_result</div>";
                        break;
                }
            }
        } else {
            $sw_version_result = "Error moving the uploaded file.";
        }
    } else {
        $sw_version_result = "No file uploaded or file upload error.";
    }

    // Process the selected solutions if the form is submitted
    $selectedSolutions = isset($_POST['solutions']) ? $_POST['solutions'] : [];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SW Version Reader</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<?php if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($showForm) && $showForm): ?>
    <div class="container">
        <h1>BMW EDC15C4</h1>
        <h2>SW Version: <?php echo htmlspecialchars($sw_version_result); ?></h2>
        <h3>File selected is: <?php echo htmlspecialchars(basename($filePath)); ?></h3>
        <form action="./solutions.php" method="post">
            <div class="checkbox-group">
                <label>Select Solutions:</label><br>
                <input type="checkbox" id="immo" name="solutions[]" value="IMMO" <?php echo in_array('IMMO', $selectedSolutions) ? 'checked' : ''; ?>>
                <label for="immo">IMMO Solution</label><br>
                <input type="checkbox" id="egr" name="solutions[]" value="EGR" <?php echo in_array('EGR', $selectedSolutions) ? 'checked' : ''; ?>>
                <label for="egr">EGR Solution</label><br>
                <input type="checkbox" id="dpf" name="solutions[]" value="DPF" <?php echo in_array('DPF', $selectedSolutions) ? 'checked' : ''; ?>>
                <label for="dpf">DPF Solution</label>
            </div>

            <input type="submit" value="Update File">
        </form>
    </div>
<?php endif; ?>

<?php if (!isset($showForm) || !$showForm): ?>
    <div class="spoiler">
        <button class="spoiler-button" onclick="toggleSpoiler()">Show Supported SW Versions</button>
        <div class="spoiler-content">
            <table>
                <thead>
                    <tr>
                        <th>Supported SW Versions</th>
						<th>EGR</th>
						<th>DPF</th>
						<th>IMMO</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td class="supported">351210</td>
						<td class="supported"></td>
						<td class="supported"></td>
						<td class="supported">X</td>
                    </tr>
                    <tr>
                        <td class="supported">351761</td>
						<td class="supported"></td>
						<td class="supported"></td>
						<td class="supported">X</td>
                    </tr>
                    <!-- Add more supported versions here with class="supported" -->
                </tbody>
            </table>
        </div>
    </div>
<?php endif; ?>

<script>
    function toggleSpoiler() {
        var content = document.querySelector('.spoiler-content');
        var button = document.querySelector('.spoiler-button');
        if (content.style.display === "none") {
            content.style.display = "block";
            button.textContent = "Hide Supported SW Versions";
        } else {
            content.style.display = "none";
            button.textContent = "Show Supported SW Versions";
        }
    }

    // Ensure the spoiler content is hidden by default
    document.addEventListener("DOMContentLoaded", function() {
        document.querySelector('.spoiler-content').style.display = "none";
    });
</script>

</body>
</html>
