<?php

// Check if file was uploaded
if (isset($_FILES["photo"])) {
  // Check file size (optional)
  if ($_FILES["photo"]["size"] > 2000000) { // 2MB limit (modify as needed)
    exit; // Exit if the file is larger than 2MB
    
  } else {
    exec('C:\xampp\cgi-bin\feature_extraction.py '.$_FILES['photo']['tmp_name'], $image_hash);
    echo implode($image_hash); // Return the result from the Python script
  }
} else {
  exit; // Exit upon no file received
}
?>