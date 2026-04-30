<?php

$id = $_GET['id'];

$data = json_encode([
    "id" => $id
]);

$options = [
    "http" => [
        "header"  => "Content-Type: application/json\r\n",
        "method"  => "POST",
        "content" => $data
    ]
];

$context = stream_context_create($options);

$result = file_get_contents(
    "http://127.0.0.1:5000/generate_quiz",
    false,
    $context
);

echo $result;
?>