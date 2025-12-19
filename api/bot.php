<?php

/* ================= BOT CONFIG ================= */
$token = "8579701610:AAFev92qYjsEDHVbLOgZMhze3_ebeGrg4QM";
$api   = "https://api.telegram.org/bot$token";

/* ================= HEX CHECK ================= */
function isHexBase16($s) {
    return $s !== "" && ctype_xdigit($s) && strlen($s) % 2 === 0;
}

/* ================= READ UPDATE ================= */
$update = json_decode(file_get_contents("php://input"), true);
if (!isset($update["message"]["text"])) exit;

$chat_id    = $update["message"]["chat"]["id"];
$message_id = $update["message"]["message_id"];
$text       = trim($update["message"]["text"]);

/* ================= DELETE USER MESSAGE ================= */
@file_get_contents(
    "$api/deleteMessage?chat_id=$chat_id&message_id=$message_id"
);

if ($text === "") exit;

/* ================= USER COUNTER ================= */
$usersFile = "users.txt";
if (!file_exists($usersFile)) file_put_contents($usersFile, "");

$users = file($usersFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
if (!in_array($chat_id, $users)) {
    file_put_contents($usersFile, $chat_id . PHP_EOL, FILE_APPEND | LOCK_EX);
}
$userCount = count(file($usersFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES));

/* ================= ONLY ACCEPT HEX ================= */
if (!isHexBase16($text)) {
    exit; // ❌ plain text → ignore completely
}

/* ================= DECODE HEX ================= */
$decoded = hex2bin($text);
if ($decoded === false || !mb_check_encoding($decoded, 'UTF-8')) {
    exit;
}

/* ================= CHECK AFTER DECODE ================= */
if (strpos($decoded, '@Venex444') === false) {
    exit; // ❌ decoded text does not contain keyword
}

/* ================= FORMAT MESSAGE ================= */
$msg  = "<b>" . htmlspecialchars($decoded, ENT_QUOTES, 'UTF-8') . "</b>";
$msg .= "\n\n<b>👥 Users Using Bot: $userCount</b>";

/* ================= SEND BOT MESSAGE ================= */
$data = [
    'chat_id' => $chat_id,
    'text' => $msg,
    'parse_mode' => 'HTML'
];

$options = [
    'http' => [
        'method'  => 'POST',
        'header'  => "Content-Type: application/x-www-form-urlencoded",
        'content' => http_build_query($data)
    ]
];

file_get_contents(
    "$api/sendMessage",
    false,
    stream_context_create($options)
);

?>

