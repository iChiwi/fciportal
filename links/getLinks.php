<?php
header('Content-Type: application/json');
require '../config.php'; // use your existing DB connection

// Get the category parameter
$category = $_GET['category'] ?? '';
$params = [];

if ($category === 'official') {
    $sql = "SELECT * FROM links WHERE category = 'official' ORDER BY sort_order";
} elseif ($category === 'section') {
    $group_code = $_GET['group_code'] ?? '';
    $sql = "SELECT * FROM links WHERE category = 'section' AND group_code = :group_code ORDER BY sort_order";
    $params['group_code'] = trim($group_code);
} elseif ($category === 'subject') {
    $subject_key = $_GET['subject_key'] ?? '';
    $sql = "SELECT * FROM links WHERE category = 'subject' AND subject_key = :subject_key ORDER BY sort_order";
    $params['subject_key'] = trim($subject_key);
} else {
    echo json_encode([]);
    exit;
}

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$links = $stmt->fetchAll();
echo json_encode($links);
?>
