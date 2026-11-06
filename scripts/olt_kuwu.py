import base64
import getpass
import http.cookiejar
import json
import re
import sys
import urllib.parse
import urllib.request
from html import unescape

def bersihkan(cell):
    cell = re.sub(r"<[^>]+>", " ", cell)
    cell = unescape(cell)
    cell = re.sub(r"\s+", " ", cell)
    return cell.strip()

def ambil_session_key(html):
    patterns = [
        r"name=['\"]SessionKey['\"][^>]*value=['\"]([^'\"]*)['\"]",
        r"SessionKey\.value\s*=\s*['\"]([^'\"]+)['\"]",
    ]
    for pattern in patterns:
        match = re.search(pattern, html, re.I)
        if match:
            return match.group(1)
    return ""

def parse_status(html):
    hasil = []
    rows = re.findall(r"<tr[^>]*>(.*?)</tr>", html, re.I | re.S)
    for row in rows:
        cells = re.findall(r"<td[^>]*>(.*?)</td>", row, re.I | re.S)
        if len(cells) < 5:
            continue
        cells = [bersihkan(x) for x in cells]
        if not re.match(r"^(EPON|GPON)", cells[0], re.I):
            continue
        hasil.append({
            "onu": cells[0],
            "status": cells[1],
            "mac": cells[2],
            "description": cells[3],
            "distance": cells[4],
            "last_deregister_reason": cells[8] if len(cells) > 8 else "-",
        })
    return hasil

def parse_opm(html):
    hasil = []
    rows = re.findall(r"<tr[^>]*>(.*?)</tr>", html, re.I | re.S)
    for row in rows:
        cells = re.findall(r"<td[^>]*>(.*?)</td>", row, re.I | re.S)
        if len(cells) < 9:
            continue
        cells = [bersihkan(x) for x in cells]
        if not re.match(r"^(EPON|GPON)", cells[0], re.I):
            continue
        hasil.append({
            "onu": cells[0],
            "mac": cells[1],
            "description": cells[2],
            "distance": cells[3],
            "temperature": cells[4],
            "voltage": cells[5],
            "tx_bias": cells[6],
            "tx_power": cells[7],
            "rx_power": cells[8],
        })
    return hasil

def normalize_mac(value):
    return re.sub(r"[^A-Fa-f0-9]", "", value or "").upper()

def main():
    bridge_mode = len(sys.argv) > 1

    if bridge_mode:
        payload = json.loads(base64.b64decode(sys.argv[1]).decode("utf-8"))
        BASE = payload["base_url"].rstrip("/")
        username = payload["username"]
        password = payload["password"]
        target_username = payload.get("target_username", "")
        caller_id = payload.get("caller_id", "")
    else:
        BASE = "http://lamongan.gopal.my.id:725"
        username = input("Username OLT: ")
        password = getpass.getpass("Password OLT: ")
        target_username = ""
        caller_id = ""

    LOGIN_URL = BASE + "/action/main.html"
    STATUS_URL = BASE + "/action/onustatusinfo.html"
    OPM_URL = BASE + "/action/onuopmdiag.html"

    cookies = http.cookiejar.CookieJar()
    opener = urllib.request.build_opener(
        urllib.request.HTTPCookieProcessor(cookies)
    )

    headers = {
        "User-Agent": "Mozilla/5.0 (Windows NT 10.0; Win64; x64) Chrome/152"
    }

    def ambil_html(url, data=None, referer=None):
        h = {**headers, "Referer": referer or LOGIN_URL}
        if data is not None:
            h["Content-Type"] = "application/x-www-form-urlencoded"
        req = urllib.request.Request(
            url,
            data=data,
            headers=h,
            method="POST" if data is not None else "GET"
        )
        response = opener.open(req, timeout=10)
        return response.read().decode("gb2312", errors="replace")

    if not bridge_mode:
        print()
        print("Login OLT...")

    ambil_html(BASE + "/action/login.html")

    login_data = urllib.parse.urlencode({
        "user": username,
        "pass": password,
        "button": "login",
        "who": "100",
    }).encode()

    ambil_html(
        LOGIN_URL,
        data=login_data,
        referer=BASE + "/action/login.html"
    )

    if not bridge_mode:
        print("Login OK")

    semua = []

    for pon in range(1, 5):
        if not bridge_mode:
            print()
            print(f"Ambil PON{pon}...")

        html_status = ambil_html(STATUS_URL)
        session_key = ambil_session_key(html_status)

        data_status = {
            "select": str(pon),
            "searchMac": "",
            "searchDescription": "",
            "who": "100",
        }
        if session_key:
            data_status["SessionKey"] = session_key

        html_status = ambil_html(
            STATUS_URL,
            data=urllib.parse.urlencode(data_status).encode(),
            referer=STATUS_URL
        )
        status_data = parse_status(html_status)

        html_opm = ambil_html(OPM_URL)
        session_key = ambil_session_key(html_opm)

        data_opm = {
            "select": str(pon),
            "searchMac": "",
            "searchDescription": "",
            "who": "100",
        }
        if session_key:
            data_opm["SessionKey"] = session_key

        html_opm = ambil_html(
            OPM_URL,
            data=urllib.parse.urlencode(data_opm).encode(),
            referer=OPM_URL
        )
        opm_data = parse_opm(html_opm)
        opm_index = {x["onu"]: x for x in opm_data}

        for s in status_data:
            o = opm_index.get(s["onu"])
            semua.append({
                "pon": pon,
                "onu": s["onu"],
                "status": s["status"],
                "mac": s["mac"],
                "description": s["description"],
                "distance": s["distance"],
                "temperature": o["temperature"] if o else "-",
                "voltage": o["voltage"] if o else "-",
                "tx_bias": o["tx_bias"] if o else "-",
                "tx_power": o["tx_power"] if o else "-",
                "rx_power": o["rx_power"] if o else "-",
                "last_deregister_reason": s.get("last_deregister_reason", "-"),
            })

        if not bridge_mode:
            print(
                f"Status: {len(status_data)} | "
                f"OPM: {len(opm_data)} | "
                f"Gabungan: {len(status_data)}"
            )

    if bridge_mode:
        target = (target_username or "").strip().lower()
        wanted_mac = normalize_mac(caller_id)
        matched = None

        for row in semua:
            desc = (row.get("description") or "").strip()
            mac = normalize_mac(row.get("mac"))
            if target and desc.lower() == target:
                matched = row
                break
            if target and target in desc.lower():
                matched = row
                break
            if wanted_mac and mac and wanted_mac == mac:
                matched = row
                break

        if matched:
            print(json.dumps(matched, ensure_ascii=False))
        else:
            print(json.dumps({
                "not_found": True,
                "total_rows": len(semua),
                "target_username": target_username,
                "caller_id": caller_id,
            }, ensure_ascii=False))
        return

    print()
    print("=" * 145)
    print("DATA ONU + OPM")
    print("=" * 145)

    for x in semua:
        print(
            f"{x['onu']:12} | "
            f"{x['status']:7} | "
            f"{x['description']:25} | "
            f"RX {x['rx_power']:>7} | "
            f"TX {x['tx_power']:>6} | "
            f"T {x['temperature']:>6} | "
            f"V {x['voltage']:>5} | "
            f"Dist {x['distance']:>5} m"
        )

    print("=" * 145)
    print("TOTAL ONU :", len(semua))

if __name__ == "__main__":
    try:
        main()
    except Exception as exc:
        if len(sys.argv) > 1:
            print(json.dumps({"error": str(exc)}, ensure_ascii=False))
        else:
            raise
