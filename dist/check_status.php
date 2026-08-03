<?php
require_once 'config.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');

$open = isRegistrationOpen($pdo);
$settings = getRegistrationSettings($pdo);

echo json_encode([
    'open' => $open,
    'status' => $settings['registration_status'],
    'close_date' => $settings['close_date']
]);
?>
