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
    <style>
        body {
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
            font-family: Arial, sans-serif;
            background-color: #f0f0f0;
        }
        .unknown-version {
            color: #721c24;
            background-color: #f8d7da;
            border: 1px solid #f5c6cb;
            border-radius: 5px;
            padding: 20px;
            font-weight: bold;
            font-size: 1.2em;
            text-align: center;
            box-shadow: 0px 4px 6px rgba(0, 0, 0, 0.1);
            margin-top: 20px;
        }
        .container {
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            padding: 20px;
            width: 400px;
            max-width: 100%;
            box-sizing: border-box;
        }
        h1 {
            font-size: 24px;
            color: #333;
            margin-bottom: 20px;
            text-align: center;
        }
        h2, h3 {
            font-size: 20px;
            color: #333;
            margin-bottom: 20px;
            text-align: center;
        }
        label {
            display: block;
            margin-bottom: 10px;
            font-size: 16px;
            color: #333;
        }
        input[type="file"] {
            display: block;
            margin: 10px 0 20px;
            width: 100%;
            padding: 10px;
            font-size: 16px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }
        .checkbox-group {
            margin-bottom: 20px;
        }
        .checkbox-group input[type="checkbox"] {
            margin-right: 10px;
        }
        .checkbox-group label {
            display: inline-block;
            font-size: 16px;
            color: #333;
        }
        input[type="submit"] {
            padding: 10px 20px;
            font-size: 16px;
            color: #fff;
            background-color: #007BFF;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            transition: background-color 0.3s;
            width: 100%;
        }
        input[type="submit"]:hover {
            background-color: #0056b3;
        }
        .supported {
            color: green;
            font-weight: bold;
        }
        .spoiler {
            margin-top: 20px;
            width: 300px;
            text-align: center;
        }
        .spoiler-content {
            display: none;
            background-color: #f8f9fa;
            border: 1px solid #ccc;
            border-radius: 5px;
            padding: 10px;
            margin-top: 10px;
        }
        .spoiler-button {
            background-color: #007bff;
            color: white;
            padding: 10px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        .spoiler-button:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>

<?php if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($showForm) && $showForm): ?>
    <div class="container">
        <h1>BMW EDC15C4</h1>
        <h2>SW Version: <?php echo htmlspecialchars($sw_version_result); ?></h2>
        <h3>File selected is: <?php echo htmlspecialchars(basename($filePath)); ?></h3>
        <form action="./<?php echo htmlspecialchars($sw_version_result); ?>.php" method="post">
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
            <ul>
                <li class="supported">351210</li>
                <li class="supported">351761</li>
                <!-- Add more supported versions here with class="supported" -->
            </ul>
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
