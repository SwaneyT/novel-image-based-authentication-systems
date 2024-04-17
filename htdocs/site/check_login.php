<?php
session_start(); // Add this at the beginning of the page


$isLoggedIn = isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true;
$userID = isset($_SESSION['id']) ? $_SESSION['id'] : '';
$username = isset($_SESSION['name']) ? $_SESSION['name'] : '';

$response = array(
  'isLoggedIn' => $isLoggedIn,
  'userID' => $userID,
  'username' => $username,
);
  
echo json_encode($response);
?>