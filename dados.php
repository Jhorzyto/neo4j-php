<?php

if (file_exists('./dados.json')) {
    $data = file_get_contents('./dados.json');
} else {
    $data = json_encode([
        'meta' => [
            'code' => 400
        ]
    ]);
    http_response_code(400);
}

header('Content-Type: application/json');
echo $data;
