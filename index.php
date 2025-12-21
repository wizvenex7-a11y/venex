<?php

/* ================= BOT CONFIG ================= */
$token = "8579701610:AAGEVkPUMduT1GQwy408vZKrBwrMfnEWhpM";
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

/* ================= KEEP /START ================= */
if ($text === "/start") exit;
if ($text === "") exit;

/* ================= DELETE USER MESSAGE ================= */
@file_get_contents(
    "$api/deleteMessage?chat_id=$chat_id&message_id=$message_id"
);

/* ================= GET USER COUNT FROM TELEGRAM ================= */
$response = file_get_contents("$api/getChatMemberCount?chat_id=$chat_id");
$data = json_decode($response, true);

if (!isset($data['result'])) exit;
$userCount = $data['result'];

/* ================= ONLY ACCEPT HEX ================= */
if (!isHexBase16($text)) exit;

/* ================= DECODE HEX ================= */
$decoded = hex2bin($text);
if ($decoded === false || !mb_check_encoding($decoded, 'UTF-8')) exit;

/* ================= CHECK AFTER DECODE ================= */
if (strpos($decoded, '@Venex444') === false) exit;

/* ================= FORMAT MESSAGE ================= */
$msg  = "<b>" . htmlspecialchars($decoded, ENT_QUOTES, 'UTF-8') . "</b>";
$msg .= "\n\n<b>👥 Users : $userCount</b>";

/* ================= SEND BOT MESSAGE ================= */
$data = [
    'chat_id'    => $chat_id,
    'text'       => $msg,
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
