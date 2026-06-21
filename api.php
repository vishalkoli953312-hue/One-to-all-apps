<?php
header('Content-Type: application/json');
$file = 'database.json';

// अगर फाइल नहीं है तो खाली स्ट्रक्चर बनाओ
if (!file_exists($file)) {
    file_put_contents($file, json_encode(["settings" => ["iconSize" => 100, "textSize" => 16], "apps" => []]));
}

$data = json_decode(file_get_contents($file), true);
$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    echo json_encode($data);
}

if ($method === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (isset($input['action']) && $input['action'] === 'delete') {
        $data['apps'] = array_filter($data['apps'], function($app) use ($input) {
            return $app['id'] != $input['id'];
        });
        $data['apps'] = array_values($data['apps']);
    } else if (isset($input['iconSize'])) {
        $data['settings'] = $input;
    } else if (isset($input['name'])) {
        if (isset($input['id']) && $input['id'] != "") {
            foreach ($data['apps'] as $key => $app) {
                if ($app['id'] == $input['id']) { $data['apps'][$key] = $input; }
            }
        } else {
            $input['id'] = strval(time());
            $data['apps'][] = $input;
        }
    }
    
    // यहाँ चेक करें कि फाइल सेव हुई या नहीं
    if(file_put_contents($file, json_encode($data, JSON_PRETTY_PRINT))) {
        echo json_encode(["success" => true]);
    } else {
        http_response_code(500);
        echo json_encode(["success" => false, "error" => "Permission Denied: Cannot write to database.json"]);
    }
}
?>