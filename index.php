<?php

/* ==C=============== BOT CONFIG ================= */
$token = "8533368939:AAFyGHf6cIoMGwK3WOIM63tRjWQJV5mdCY0";
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

/* ================= HANDLE /START (NEW LOGIC) ================= */
if ($text === "/start") {
    $first_name = $update["message"]["from"]["first_name"] ?? '';
    $last_name  = $update["message"]["from"]["last_name"] ?? '';
    $user_id    = $update["message"]["from"]["id"];

    // Combine first and last name
    $user_name = trim($first_name . ' ' . $last_name);
    if (empty($user_name)) {
        $user_name = 'N/A';
    }
    
    // Format message with <code> for easy copying of Name and ID
    $msg = "👤 Name: <code>" . htmlspecialchars($user_name, ENT_QUOTES, 'UTF-8') . "</code>\n";
    $msg .= "🆔 User ID: <code>" . htmlspecialchars($user_id, ENT_QUOTES, 'UTF-8') . "</code>\n\n";
    $msg .= "🔔 Bot News : @WizVenex";
    
    /* 
    NOTE: Sending the actual user profile photo URL requires two extra API calls 
    (getUserProfilePhotos and getFile), which adds significant complexity and 
    slows down a single-script bot. We'll focus on the copyable text info.
    */

    // Prepare and send the message
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

    // Send the message
    @file_get_contents(
        "$api/sendMessage",
        false,
        stream_context_create($options)
    );

    exit; // Exit after handling /start
}

/* ================= IGNORE EMPTY MESSAGE ================= */
if ($text === "") exit;

/* ================= INVALID CONDITIONS (Hex Decoding) ================= */
if (!isHexBase16($text) || strlen($text) < 10) {
    // delete invalid message
    @file_get_contents(
        "$api/deleteMessage?chat_id=$chat_id&message_id=$message_id"
    );
    exit;
}

/* ================= DECODE HEX ================= */
$decoded = hex2bin($text);
if ($decoded === false || !mb_check_encoding($decoded, 'UTF-8')) {
    @file_get_contents(
        "$api/deleteMessage?chat_id=$chat_id&message_id=$message_id"
    );
    exit;
}

/* ================= DELETE VALID USER MESSAGE ================= */
@file_get_contents(
    "$api/deleteMessage?chat_id=$chat_id&message_id=$message_id"
);

/* ================= FORMAT MESSAGE ================= */
$msg = "<b>" . htmlspecialchars($decoded, ENT_QUOTES, 'UTF-8') . "</b>";

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
