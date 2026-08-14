<?php
header('Content-Type: application/json; charset=utf-8');
require __DIR__ . '/config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok' => false, 'error' => 'method'], JSON_UNESCAPED_UNICODE);
    exit;
}

$raw = file_get_contents('php://input');
$d = json_decode($raw, true);
if (!is_array($d) || trim((string)($d['name'] ?? '')) === '') {
    http_response_code(422);
    echo json_encode(['ok' => false, 'error' => 'name'], JSON_UNESCAPED_UNICODE);
    exit;
}

$d['name'] = trim((string)$d['name']);
$d['created_at'] = date('c');
$d['drinks'] = is_array($d['drinks'] ?? null) ? $d['drinks'] : [];
$d['plus_one_fee'] = (($d['plus_one'] ?? '') === 'Да') ? 1000 : 0;

$dataDir = __DIR__ . '/data';
if (!is_dir($dataDir)) {
    mkdir($dataDir, 0755, true);
}
$file = $dataDir . '/responses.json';
$arr = file_exists($file) ? json_decode(file_get_contents($file), true) : [];
if (!is_array($arr)) $arr = [];
$arr[] = $d;
if (file_put_contents($file, json_encode($arr, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT), LOCK_EX) === false) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => 'save'], JSON_UNESCAPED_UNICODE);
    exit;
}

$msg = "🎉 НОВАЯ АНКЕТА\n\n";
$msg .= "👤 ФИО: " . ($d['name'] ?? '—') . "\n";
$msg .= "📅 Участие: " . ($d['attendance'] ?? '—') . "\n";
$msg .= "👥 +1: " . ($d['plus_one'] ?? '—') . "\n";
if (($d['plus_one'] ?? '') === 'Да') {
    $msg .= "Имя +1: " . ($d['plus_one_name'] ?? '—') . "\n";
    $msg .= "💰 Доплата: 1 000 ₽\n";
}
$msg .= "🛏 Ночёвка: " . ($d['sleep'] ?? '—') . "\n";
$msg .= "🥂 Напитки: " . (count($d['drinks']) ? implode(', ', $d['drinks']) : 'Не указаны') . "\n";
$msg .= "💬 Комментарий: " . ($d['comment'] ?? '—');

$telegramConfigured = TELEGRAM_BOT_TOKEN !== 'PASTE_NEW_BOT_TOKEN_HERE'
    && TELEGRAM_CHAT_ID !== 'PASTE_ADMIN_CHAT_ID_HERE';
$telegramSent = false;

if ($telegramConfigured && function_exists('curl_init')) {
    $url = 'https://api.telegram.org/bot' . TELEGRAM_BOT_TOKEN . '/sendMessage';
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => http_build_query([
            'chat_id' => TELEGRAM_CHAT_ID,
            'text' => $msg
        ]),
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 8,
    ]);
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    $telegramSent = ($httpCode >= 200 && $httpCode < 300 && $response !== false);
}

echo json_encode([
    'ok' => true,
    'saved' => true,
    'telegram_sent' => $telegramSent,
    'telegram_configured' => $telegramConfigured
], JSON_UNESCAPED_UNICODE);
