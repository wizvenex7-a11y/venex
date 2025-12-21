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

/* ================= ONLY ACCEPT HEX ================= */
if (!isHexBase16($text)) exit;

/* ================= DECODE HEX ================= */
$decoded = hex2bin($text);
if ($decoded === false || !mb_check_encoding($decoded, 'UTF-8')) exit;

/* ================= CHECK AFTER DECODE ================= */
if (strpos($decoded, '@Venex444') === false) {

    // ⏱ sleep 2 minutes
    sleep(120);

    // ⚠️ send warning message after sleep
    $warn = "❌ Invalid decode content.\n\n"
          . "⏳ Time delay applied: 2 minutes.\n"
          . "✅ Please include @Venex444 in your decoded message.";

    $data = [
        'chat_id' => $chat_id,
        'text'    => $warn
    ];

    file_get_contents(
        "$api/sendMessage",
        false,
        stream_context_create([
            'http' => [
                'method'  => 'POST',
                'header'  => "Content-Type: application/x-www-form-urlencoded",
                'content' => http_build_query($data)
            ]
        ])
    );

    exit;
}

/* ================= FORMAT SUCCESS MESSAGE ================= */
$msg  = "<b>" . htmlspecialchars($decoded, ENT_QUOTES, 'UTF-8') . "</b>";
$msg .= "\n\n<b>✅ Decode accepted</b>";

/* ================= SEND BOT MESSAGE ================= */
$data = [
    'chat_id'    => $chat_id,
    'text'       => $msg,
    'parse_mode' => 'HTML'
];

file_get_contents(
    "$api/sendMessage",
    false,
    stream_context_create([
        'http' => [
            'method'  => 'POST',
            'header'  => "Content-Type: application/x-www-form-urlencoded",
            'content' => http_build_query($data)
        ]
    ])
);

?>