<?php
ini_set('session.gc_maxlifetime', 86400);
session_start();
// Change this to your connection info.
$DATABASE_HOST = 'localhost';
$DATABASE_USER = 'root';
$DATABASE_PASS = 'SeCuRe123$';
$DATABASE_NAME = 'master-database';


// Try and connect using the info above.
$con = mysqli_connect($DATABASE_HOST, $DATABASE_USER, $DATABASE_PASS, $DATABASE_NAME);
if ( mysqli_connect_errno() ) {
	// If there is an error with the connection, stop the script and display the error.
	exit('Failed to connect to MySQL: ' . mysqli_connect_error());
}


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

@$method = $_POST['test2'];

if ($method === "image-method"){
    $username = $_POST['test'];
    $cFile = curl_file_create($_FILES['photo']['tmp_name']);
    $request = curl_init();

    $post = array('photo'=> $cFile);

    curl_setopt($request, CURLOPT_URL,"http://localhost:80/masters-site/image_extract.php");
    curl_setopt($request, CURLOPT_POST, 1);
    curl_setopt($request, CURLOPT_POSTFIELDS,
            $post);

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

if (!isset($_POST['username']) || !isset($_POST['password'])) {
    exit('Please fill both the username and password fields!');
}

// Prepare our SQL, preparing the SQL statement will prevent SQL injection.
if ($stmt = $con->prepare('SELECT id, password FROM login_table WHERE username = ?')) {
	// Bind parameters (s = string, i = int, b = blob, etc), in our case the username is a string so we use "s"
	$stmt->bind_param('s', $_POST['username']);
	$stmt->execute();
	// Store the result so we can check if the account exists in the database.
	$stmt->store_result();

    if ($stmt->num_rows > 0) {
        $stmt->bind_result($id, $password);
        $password_to_hash = $_POST['password'];
        // $sanitized_password_to_hash = htmlspecialchars($password_to_hash, ENT_QUOTES, 'UTF-8');
        $stmt->fetch();
        // Account exists, now we verify the password.
        // Note: remember to use password_hash in your registration file to store the hashed passwords.#

        if (password_verify(strval(trim($password_to_hash)), trim($password))) {
            // Verification success! User has logged-in!
            // Create sessions, so we know the user is logged in, they basically act like cookies but remember the data on the server.
            session_regenerate_id();
            $_SESSION['logged_in'] = TRUE;
            $_SESSION['name'] = $_POST['username'];
            $_SESSION['id'] = $id;
            setcookie('username', $_SESSION['name'], time() + 604800, '/');
            $username = htmlspecialchars($_SESSION['name'], ENT_QUOTES);
            $data = array('status' => 'success', 'username' => $username);
            echo json_encode($data);
        } else{
            // Incorrect login
            // $data = array('status' => 'fail');
            // echo json_encode($data);
            // $data = array('original' => $_POST['password'], 'to_match_in_db' => $password);
            $data = array('status' => 'fail', 'error' => 'invalid');
            echo json_encode($data);
        }
    } else {
        // Incorrect username
        $data = array('status' => 'fail');
        echo json_encode($data);
    }
	$stmt->close();
}
?>