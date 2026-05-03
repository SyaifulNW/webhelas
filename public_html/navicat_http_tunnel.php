<?php
error_reporting(0); // Tambahkan ini
$conn = new mysqli('localhost', 'helascor', 'syaiful_12', 'helascor_helas');

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Navicat will send a request with 'request_data'
if (isset($_POST['request_data'])) {
    $request = base64_decode($_POST['request_data']);
    $data = unserialize($request);

    if (isset($data['query'])) {
        $result = $conn->query($data['query']);
        if ($result === TRUE) {
            echo base64_encode(serialize(['result' => 'OK']));
        } else {
            // Respons ini penting jika ada error SQL.
            echo base64_encode(serialize(['error' => $conn->error]));
        }
    } else {
        echo base64_encode(serialize(['error' => 'Invalid request']));
    }
} else {
    // Jika diakses dari browser, akan menampilkan ini.
    echo "This is Navicat HTTP Tunnel.";
}
$conn->close();

