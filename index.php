<?php

/* == BOT CONFIG == */
$token = "8533368939:AAFyGHf6cIoMGwK3WOIM63tRjWQJV5mdCY0";
$api   = "https://api.telegram.org/bot$token";
$bot_user = "Venex444_bot"; // Your bot username from the HTML

/* == HEX CHECK == */
function isHexBase16($s) {
    return $s !== "" && ctype_xdigit($s) && strlen($s) % 2 === 0;
}

/* == READ UPDATE == */
$update = json_decode(file_get_contents("php://input"), true);
if (!isset($update["message"]["text"])) exit;

$chat_id    = $update["message"]["chat"]["id"];
$message_id = $update["message"]["message_id"];
$text       = trim($update["message"]["text"]);

/* == HANDLE / (NEW FEATURE: DIRECT LINK COPY) == */
if ($text === "/") {
    // Delete the user's / message to keep the chat clean
    @file_get_contents("$api/deleteMessage?chat_id=$chat_id&message_id=$message_id");

    $link = "https://t.me/$bot_user";
    $msg = "🔗 <b>Direct Bot Link</b> (Tap to copy):\n\n";
    $msg .= "<code>$link</code>"; // This makes it Monospace and Tap-to-copy

    $data = [
        'chat_id'    => $chat_id,
        'text'       => $msg,
        'parse_mode' => 'HTML'
    ];
    
    file_get_contents("$api/sendMessage?" . http_build_query($data));
    exit;
}

/* == HANDLE /START == */
if ($text === "/start") {
    $first_name = $update["message"]["from"]["first_name"] ?? '';
    $last_name  = $update["message"]["from"]["last_name"] ?? '';
    $user_id    = $update["message"]["from"]["id"];
    $user_name  = trim($first_name . ' ' . $last_name) ?: 'N/A';
    
    $msg = "👤 Name: <code>" . htmlspecialchars($user_name, ENT_QUOTES, 'UTF-8') . "</code>\n";
    $msg .= "🆔 User ID: <code>" . htmlspecialchars($user_id, ENT_QUOTES, 'UTF-8') . "</code>\n\n";
    $msg .= "🔔 Bot News : @WizVenex";

    $data = ['chat_id' => $chat_id, 'text' => $msg, 'parse_mode' => 'HTML'];
    file_get_contents("$api/sendMessage?" . http_build_query($data));
    exit;
}

/* == IGNORE EMPTY == */
if ($text === "") exit;

/* == INVALID HEX CONDITIONS == */
if (!isHexBase16($text) || strlen($text) < 10) {
    @file_get_contents("$api/deleteMessage?chat_id=$chat_id&message_id=$message_id");
    exit;
}

/* == DECODE HEX == */
$decoded = hex2bin($text);
if ($decoded === false || !mb_check_encoding($decoded, 'UTF-8')) {
    @file_get_contents("$api/deleteMessage?chat_id=$chat_id&message_id=$message_id");
    exit;
}

/* == SUCCESS: DELETE & SEND DECODED == */
@file_get_contents("$api/deleteMessage?chat_id=$chat_id&message_id=$message_id");

$data = [
    'chat_id'    => $chat_id,
    'text'       => "<b>" . htmlspecialchars($decoded, ENT_QUOTES, 'UTF-8') . "</b>",
    'parse_mode' => 'HTML'
];

file_get_contents("$api/sendMessage?" . http_build_query($data));

?>
