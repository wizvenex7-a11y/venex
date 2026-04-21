<?php

/* == BOT CONFIG == */
$token = "8533368939:AAFyGHf6cIoMGwK3WOIM63tRjWQJV5mdCY0";
$api   = "https://api.telegram.org/bot$token";
$bot_user = "Venex444_bot"; 

/* == HELPER: HEX CHECK == */
function isHexBase16($s) {
    return $s !== "" && ctype_xdigit($s) && strlen($s) % 2 === 0;
}

/* == READ UPDATE == */
$update = json_decode(file_get_contents("php://input"), true);
if (!isset($update["message"]["text"])) exit;

$chat_id    = $update["message"]["chat"]["id"];
$message_id = $update["message"]["message_id"];
$text       = trim($update["message"]["text"]);

/* ================= HANDLE /START ================= */
if ($text === "/start") {
    $first_name = $update["message"]["from"]["first_name"] ?? '';
    $last_name  = $update["message"]["from"]["last_name"] ?? '';
    $user_id    = $update["message"]["from"]["id"];
    $user_name  = trim($first_name . ' ' . $last_name) ?: 'N/A';
    
    $msg = "👤 Name: <code>" . htmlspecialchars($user_name, ENT_QUOTES, 'UTF-8') . "</code>\n";
    $msg .= "🆔 User ID: <code>" . htmlspecialchars($user_id, ENT_QUOTES, 'UTF-8') . "</code>\n\n";
    $msg .= "🔔 Bot News : @WizVenex";

    file_get_contents("$api/sendMessage?" . http_build_query(['chat_id' => $chat_id, 'text' => $msg, 'parse_mode' => 'HTML']));
    exit;
}

/* ================= HANDLE / TEXT (ENCODE TO LINK) ================= */
if (strpos($text, '/') === 0) {
    // Get text after the /
    $plainText = trim(substr($text, 1));

    if (!empty($plainText)) {
        // Delete the user's command message
        @file_get_contents("$api/deleteMessage?chat_id=$chat_id&message_id=$message_id");

        // Convert Text to Hex (Base16)
        $hex = strtoupper(bin2hex($plainText));
        
        // Generate the Mono Link
        $link = "https://t.me/$bot_user?text=$hex";
        
        $msg = "🔗 <b>Generated Base16 Link:</b>\n\n";
        $msg .= "<code>$link</code>"; // Mono copy format

        $data = [
            'chat_id'    => $chat_id,
            'text'       => $msg,
            'parse_mode' => 'HTML'
        ];
        
        file_get_contents("$api/sendMessage?" . http_build_query($data));
        exit;
    }
}

/* ================= IGNORE EMPTY ================= */
if ($text === "") exit;

/* ================= HANDLE HEX DECODING ================= */
if (!isHexBase16($text) || strlen($text) < 10) {
    @file_get_contents("$api/deleteMessage?chat_id=$chat_id&message_id=$message_id");
    exit;
}

$decoded = hex2bin($text);
if ($decoded === false || !mb_check_encoding($decoded, 'UTF-8')) {
    @file_get_contents("$api/deleteMessage?chat_id=$chat_id&message_id=$message_id");
    exit;
}

// Success: Delete hex and send plain text
@file_get_contents("$api/deleteMessage?chat_id=$chat_id&message_id=$message_id");

$data = [
    'chat_id'    => $chat_id,
    'text'       => "<b>" . htmlspecialchars($decoded, ENT_QUOTES, 'UTF-8') . "</b>",
    'parse_mode' => 'HTML'
];

file_get_contents("$api/sendMessage?" . http_build_query($data));

?>
