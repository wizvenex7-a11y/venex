import json
import requests
import html

# ================= BOT CONFIG =================
TOKEN = "8579701610:AAGbS1MDmsDHGPMVeAgrEnaRF-wx_GiIxYI"
API = f"https://api.telegram.org/bot{TOKEN}"

# ================= HEX CHECK =================
def is_hex_base16(s: str) -> bool:
    return (
        s != "" and
        all(c in "0123456789abcdefABCDEF" for c in s) and
        len(s) % 2 == 0
    )

# ================= READ UPDATE =================
def main():
    update = json.loads(input())

    if "message" not in update or "text" not in update["message"]:
        return

    chat_id = update["message"]["chat"]["id"]
    message_id = update["message"]["message_id"]
    text = update["message"]["text"].strip()

    # ================= DELETE USER MESSAGE =================
    requests.get(
        f"{API}/deleteMessage",
        params={"chat_id": chat_id, "message_id": message_id}
    )

    if not text:
        return

    # ================= ONLY ACCEPT HEX =================
    if not is_hex_base16(text):
        return  # ❌ ignore non-hex input

    # ================= DECODE HEX =================
    try:
        decoded = bytes.fromhex(text).decode("utf-8")
    except Exception:
        return

    # ================= CHECK AFTER DECODE =================
    if "@Venex444" not in decoded:
        return

    # ================= FORMAT MESSAGE =================
    msg = f"<b>{html.escape(decoded)}</b>"

    # ================= SEND BOT MESSAGE =================
    requests.post(
        f"{API}/sendMessage",
        data={
            "chat_id": chat_id,
            "text": msg,
            "parse_mode": "HTML"
        }
    )

if __name__ == "__main__":
    main()
