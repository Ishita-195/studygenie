<?php
// Set the response header to application/json
header('Content-Type: application/json');

// Simulate a short processing delay of about 1 second
sleep(1);

// Create the JSON response object
$response = [
    "status" => "processing_started",
    "message" => "PDF processing started successfully"
];

// Output the JSON response
echo json_encode($response);

// Ensure no further output or whitespace is sent
exit;