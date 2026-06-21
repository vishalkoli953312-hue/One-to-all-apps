<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

$file = 'database.json';

// फाइल चेक करें और बेसिक स्ट्रक्चर बनाएं
if (!file_exists($file)) {
    file_put_contents($file, json_encode(["settings" => ["iconSize" => 100, "textSize" => 16], "apps" => []]));
}

$data = json_decode(file_get_contents($file), true);
$method = $_SERVER['REQUEST_METHOD'];

// 1. डेटा भेजना (GET)
if ($method === 'GET') {
    echo json_encode($data);
}

// 2. डेटा सेव, एडिट और डिलीट करना (POST)
if ($method === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    
    // अगर डिलीट का सिग्नल मिले
    if (isset($input['action']) && $input['action'] === 'delete') {
        $data['apps'] = array_filter($data['apps'], function($app) use ($input) {
            return $app['id'] != $input['id'];
        });
        $data['apps'] = array_values($data['apps']);
    } 
    // अगर एडमिन सेटिंग्स (Size) अपडेट हो
    else if (isset($input['iconSize'])) {
        $data['settings'] = $input;
    }
    // अगर नया ऐप जोड़ा या एडिट किया जाए
    else if (isset($input['name'])) {
        if (isset($input['id']) && $input['id'] != "") {
            // Edit
            foreach ($data['apps'] as $key => $app) {
                if ($app['id'] == $input['id']) {
                    $data['apps'][$key] = $input;
                }
            }
        } else {
            // Add New
            $input['id'] = strval(time());
            $data['apps'][] = $input;
        }
    }
    
    // फाइल में सेव करें
    if(file_put_contents($file, json_encode($data, JSON_PRETTY_PRINT))) {
        echo json_encode(["success" => true]);
    } else {
        echo json_encode(["success" => false, "error" => "Cannot write to file"]);
    }
}
?>