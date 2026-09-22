import base64
import getpass
import http.cookiejar
import json
import re
import sys
import urllib.parse
import urllib.request
from html import unescape


def clean(cell):
    cell = re.sub(r"<[^>]+>", " ", cell)
    cell = unescape(cell)
    cell = re.sub(r"\\s+", " ", cell)
    return cell.strip()


def session_key(html):
    patterns = [
        r"name=['\\\"]SessionKey['\\\"][^>]*value=['\\\"]([^'\\\"]*)['\\\"]",
        r"SessionKey\\.value\\s*=\\s*['\\\"]([^'\\\"]+)['\\\"]",
    ]
    for pattern in patterns:
        match = re.search(pattern, html, re.I)
        if match:
            return match.group(1).strip()
    return ""


def rows(html):
    result = []
    for row in re.findall(r"<tr[^>]*>(.*?)</tr>", html, re.I | re.S):
        cells = [clean(x) for x in re.findall(r"<td[^>]*>(.*?)</td>", row, re.I | re.S)]
        if len(cells) < 5 or not re.match(r"^EPON", cells[0], re.I):
            continue
        result.append(cells)
    return result


def status_rows(html):
    result = []
    for cells in rows(html):
        if len(cells) < 5:
            continue
        result.append({
            "onu": cells[0],
            "status": cells[1],
            "mac": cells[2],
            "description": cells[3],
            "distance": cells[4],
            "last_deregister_reason": cells[8] if len(cells) > 8 else "-",
        })
    return result


def opm_rows(html):
    result = []
    for cells in rows(html):
        if len(cells) < 9:
            continue
        result.append({
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
    return result


def normalize_mac(value):
    return re.sub(r"[^A-Fa-f0-9]", "", value or "").upper()


def match(rows_data, target, caller):
    target = (target or "").strip()
    caller = normalize_mac(caller)
    for row in rows_data:
        desc = row.get("description", "").strip()
        mac = normalize_mac(row.get("mac", ""))
        if target and desc.lower() == target.lower():
            return row
        if target and target.lower() in desc.lower():
            return row
        if caller and mac and caller == mac:
            return row
    return None


def main():
    payload = json.loads(base64.b64decode(sys.argv[1]).decode("utf-8"))
    base = payload["base_url"].rstrip("/")
    target = payload.get("target_username", "")
    caller = payload.get("caller_id", "")
    username = payload["username"]
    password = payload["password"]

    login_page = base + "/action/login.html"
    login_url = base + "/action/main.html"
    status_url = base + "/action/onustatusinfo.html"
    opm_url = base + "/action/onuopmdiag.html"

    cookies = http.cookiejar.CookieJar()
    opener = urllib.request.build_opener(urllib.request.HTTPCookieProcessor(cookies))
    headers = {"User-Agent": "Mozilla/5.0 (Windows NT 10.0; Win64; x64) Chrome/152"}

    def request(url, data=None, referer=None):
        h = {**headers, "Referer": referer or login_url}
        if data is not None:
            h["Content-Type"] = "application/x-www-form-urlencoded"
        req = urllib.request.Request(url, data=data, headers=h, method="POST" if data is not None else "GET")
        return opener.open(req, timeout=10).read().decode("gb2312", errors="replace")

    request(login_page)
    login_data = urllib.parse.urlencode({"user": username, "pass": password, "button": "login", "who": "100"}).encode()
    request(login_url, login_data, login_page)

    for pon in range(1, 5):
        html = request(status_url)
        key = session_key(html)
        data = {"select": str(pon), "searchMac": "", "searchDescription": "", "who": "100"}
        if key:
            data["SessionKey"] = key
        status = status_rows(request(status_url, urllib.parse.urlencode(data).encode(), status_url))
        matched = match(status, target, caller)
        if not matched:
            continue

        html = request(opm_url)
        key = session_key(html)
        data = {"select": str(pon), "searchMac": "", "searchDescription": "", "who": "100"}
        if key:
            data["SessionKey"] = key
        opm = opm_rows(request(opm_url, urllib.parse.urlencode(data).encode(), opm_url))
        opm_index = {row["onu"]: row for row in opm}
        optical = opm_index.get(matched["onu"], {})
        result = {
            **matched,
            "temperature": optical.get("temperature"),
            "voltage": optical.get("voltage"),
            "tx_bias": optical.get("tx_bias"),
            "tx_power": optical.get("tx_power"),
            "rx_power": optical.get("rx_power"),
        }
        print(json.dumps(result, ensure_ascii=False))
        return

    print(json.dumps(None))


if __name__ == "__main__":
    try:
        main()
    except Exception as exc:
        print(json.dumps({"error": str(exc)}))
        sys.exit(1)
