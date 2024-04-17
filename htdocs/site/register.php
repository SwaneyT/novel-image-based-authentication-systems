<?php

// Define database credentials (replace with your actual values)
$DATABASE_HOST = "localhost";
$DATABASE_USERNAME = "root";
$DATABASE_PASSWORD = "SeCuRe123$";
$DATABASE_NAME = "master-database";

// Define error messages (optional, customize as needed)
$errorMessages = [
    "missing_fields" => "Please fill in both fields.",
    "invalid_username" => "Please enter a valid username.",
    "existing_username" => "This username is already taken.",
    "database_error" => "An error occurred while registering. Please try again later.",
];

// Initialize error flag and user data
$hasError = false;
$userData = [];

@$method = $_POST['test2'];

if (isset($_FILES['photo']['tmp_name']) && $_FILES['photo']['tmp_name'] !== '') {

    $fileInfo = finfo_open(FILEINFO_MIME_TYPE);
    $mimeType = finfo_file($fileInfo, $_FILES['photo']['tmp_name']);
    finfo_close($fileInfo);

    // Check if the MIME type corresponds to an image
    $imageMimeTypes = array(
        'image/jpeg',
        'image/png',
    );

    if (!in_array($mimeType, $imageMimeTypes)) {
        $data = array('status' => 'fail', 'error' => 'Invalid file type');
        echo json_encode($data);
        exit;
    }
}

if ($method === "image-method"){
    $username = $_POST['test'];
    $cFile = curl_file_create($_FILES['photo']['tmp_name']);
    $request = curl_init();

    $post = array('photo'=> $cFile);

    curl_setopt($request, CURLOPT_URL,"http://localhost:80/masters-site/image_extract.php");
    curl_setopt($request, CURLOPT_POST, 1);
    curl_setopt($request, CURLOPT_POSTFIELDS, $post);

    // catch the response
    curl_setopt($request, CURLOPT_RETURNTRANSFER, true);

    $response = curl_exec($request);

    $_POST['username'] = $username;
    $_POST['password'] = $response;

    if (curl_errno($request)) {
        $error_msg = curl_error($request);
        echo "cURL Error: $error_msg";
        exit; // Or handle the error appropriately
    }
}



// Check if required fields are present
if (empty($_POST['username']) || empty($_POST['password'])) {
    $hasError = true;
    $errorMessage = $errorMessages["missing_fields"];
} else {
    $userData = [
        "username" => trim($_POST['username']),
        "password" => password_hash(trim($_POST['password']), PASSWORD_BCRYPT, ['cost' => 12]),
    ];
}




// Connect to the database
$conn = mysqli_connect($DATABASE_HOST, $DATABASE_USERNAME, $DATABASE_PASSWORD, $DATABASE_NAME);

// Check connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Validate username format (optional, consider using a library for advanced validation)
if (!$hasError && !preg_match("/^[a-zA-Z0-9_-]+$/", $userData["username"])) {
    $hasError = true;
    $errorMessage = $errorMessages["invalid_username"];
}

// Check if username already exists in the database (optional, adjust query based on your table structure)
if (!$hasError) {
    $sql = "SELECT username FROM login_table WHERE username = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "s", $userData["username"]);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if (mysqli_num_rows($result) > 0) {
        $hasError = true;
        $errorMessage = $errorMessages["existing_username"];
    }
    mysqli_stmt_close($stmt);
    mysqli_free_result($result);
}

// Register user if no errors
if (!$hasError) {
    $sql = "INSERT INTO login_table (username, password) VALUES (?, ?)";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "ss", $userData["username"], $userData["password"]);
    if (mysqli_stmt_execute($stmt)) {
        // User registered successfully (handle success message or redirect)
        $data = array('status' => 'success', 'username' => $userData["username"], 'hash' => $userData["password"]);
        echo json_encode($data);
    } else {
        $hasError = true;
        $errorMessage = $errorMessages["database_error"];
    }
    mysqli_stmt_close($stmt);
}

// Close database connection
mysqli_close($conn);

// Display error message (optional)
if ($hasError) {
    $data = array('status' => 'fail', 'error' => $errorMessage);
    echo json_encode($data);
}

?>