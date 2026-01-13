<?php

/* ================= BOT CONFIG ================= */
$token = "8579701610:AAGEVkPUMduT1GQwy408vZKrBwrMfnEWhpM";
$api   = "https://api.telegram.org/bot$token";

/* ================= SETTINGS ================= */
$limitCount = 2;
$limitTime  = 300; // 5 minutes
$rateFile   = __DIR__ . "/rate.json";
$logFile    = __DIR__ . "/decode_log.txt";

/* ================= HELPERS ================= */
function isBase16($s) {
    return $s !== "" && ctype_xdigit($s) && strlen($s) % 2 === 0;
}

function isBase64Enc($s) {
    return preg_match('/^[A-Za-z0-9+\/=]+$/', $s)
        && base64_encode(base64_decode($s, true)) === $s;
}

function isBase32Enc($s) {
    return preg_match('/^[A-Z2-7=]+$/i', $s);
}

function base32_decode_custom($input) {
    $alphabet = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
    $input = strtoupper(rtrim($input, '='));
    $binary = '';

    foreach (str_split($input) as $char) {
        $pos = strpos($alphabet, $char);
        if ($pos === false) return false;
        $binary .= str_pad(decbin($pos), 5, '0', STR_PAD_LEFT);
    }

    $output = '';
    foreach (str_split($binary, 8) as $byte) {
        if (strlen($byte) === 8) {
            $output .= chr(bindec($byte));
        }
    }
    return $output;
}

function rateLimit($uid, $file, $limit, $seconds) {
    $now = time();
    $data = file_exists($file) ? json_decode(file_get_contents($file), true) : [];

    $data[$uid] = $data[$uid] ?? [];
    $data[$uid] = array_filter($data[$uid], fn($t) => ($now - $t) < $seconds);

    if (count($data[$uid]) >= $limit) return false;

    $data[$uid][] = $now;
    file_put_contents($file, json_encode($data));
    return true;
}

/* ================= READ UPDATE ================= */
$update = json_decode(file_get_contents("php://input"), true);
if (!isset($update["message"]["text"])) exit;

$chat_id    = $update["message"]["chat"]["id"];
$user_id    = $update["message"]["from"]["id"];
$message_id = $update["message"]["message_id"];
$text       = trim($update["message"]["text"]);

if ($text === "/start" || strlen($text) < 10) exit;

/* ================= RATE LIMIT ================= */
if (!rateLimit($user_id, $rateFile, $limitCount, $limitTime)) exit;

/* ================= DETECT & DECODE ================= */
$decoded = false;
$type = null;

if (isBase16($text)) {
    $decoded = hex2bin($text);
    $type = "BASE16";
} elseif (isBase64Enc($text)) {
    $decoded = base64_decode($text, true);
    $type = "BASE64";
} elseif (isBase32Enc($text)) {
    $decoded = base32_decode_custom($text);
    $type = "BASE32";
}

if ($decoded === false || !mb_check_encoding($decoded, 'UTF-8')) {
    @file_get_contents("$api/deleteMessage?chat_id=$chat_id&message_id=$message_id");
    exit;
}

/* ================= DELETE USER MESSAGE ================= */
@file_get_contents("$api/deleteMessage?chat_id=$chat_id&message_id=$message_id");

/* ================= LOG ================= */
$prefix = substr($text, 0, 15);
$time   = date("Y-m-d H:i:s");

file_put_contents(
    $logFile,
    "$prefix - $user_id - $time - $type\n",
    FILE_APPEND
);

/* ================= SEND RESULT ================= */
$header = "<b>$prefix - $user_id - $time</b>\n\n";
$body   = "<code>" . htmlspecialchars($decoded, ENT_QUOTES, 'UTF-8') . "</code>";

$data = [
    'chat_id' => $chat_id,
    'text' => $header . $body,
    'parse_mode' => 'HTML'
];

file_get_contents(
    "$api/sendMessage",
    false,
    stream_context_create([
        'http' => [
            'method' => 'POST',
            'header' => "Content-Type: application/x-www-form-urlencoded",
            'content' => http_build_query($data)
        ]
    ])
);

?>
