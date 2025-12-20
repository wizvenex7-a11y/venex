import json
import requests
import html

TOKEN = "8579701610:AAGEVkPUMduT1GQwy408vZKrBwrMfnEWhpM"
API = f"https://api.telegram.org/bot{TOKEN}"

def is_hex_base16(s):
    return s and all(c in "0123456789abcdefABCDEF" for c in s) and len(s) % 2 == 0

def handler(event, context):
    try:
        update = json.loads(event["body"])
    except Exception:
        return {"statusCode": 200}

    if "message" not in update or "text" not in update["message"]:
        return {"statusCode": 200}

    chat_id = update["message"]["chat"]["id"]
    message_id = update["message"]["message_id"]
    text = update["message"]["text"].strip()

    # Delete user message
    requests.get(
        f"{API}/deleteMessage",
        params={"chat_id": chat_id, "message_id": message_id}
    )

    if not is_hex_base16(text):
        return {"statusCode": 200}

    try:
        decoded = bytes.fromhex(text).decode("utf-8")
    except Exception:
        return {"statusCode": 200}

    if "@Venex444" not in decoded:
        return {"statusCode": 200}

    msg = f"<b>{html.escape(decoded)}</b>"

    requests.post(
        f"{API}/sendMessage",
        data={
            "chat_id": chat_id,
            "text": msg,
            "parse_mode": "HTML"
        }
    )

    return {"statusCode": 200}
