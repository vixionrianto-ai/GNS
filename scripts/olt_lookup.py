#!/usr/bin/env python3
import json
import re
import sys
import urllib.parse
import urllib.request
import http.cookiejar
from html import unescape

def clean(value):
    value = re.sub(r"<[^>]+>", " ", value)
    value = unescape(value)
    value = re.sub(r"\s+", " ", value)
    return value.strip()

def session_key(html):
    patterns = [
        r"name=['\"]SessionKey['\"][^>]*value=['\"]([^'\"]*)['\"]",
        r"value=['\"]([^'\"]*)['\"][^>]*name=['\"]SessionKey['\"]",
        r"SessionKey\s*\.\s*name\s*=\s*['\"]SessionKey['\"][\s\S]*?SessionKey\s*\.\s*value\s*=\s*['\"]([^'\"]*)['\"]",
        r"SessionKey\s*\.\s*value\s*=\s*['\"]([^'\"]*)['\"]",
    ]
    for pattern in patterns:
        match = re.search(pattern, html, re.I)
        if match:
            return match.group(1).strip()
    return ""

def parse_rows(html):
    result = []
    for row in re.findall(r"<tr[^>]*>(.*?)</tr>", html, re.I | re.S):
        cells = re.findall(r"<td[^>]*>(.*?)</td>", row, re.I | re.S)
        if len(cells) < 5:
            continue
        cells = [clean(x) for x in cells]
        if not re.match(r"^(EPON|GPON)", cells[0], re.I):
            continue
        result.append(cells)
    return result

def mac(value):
    return re.sub(r"[^0-9A-Fa-f]", "", value or "").upper()

def match_row(rows, username, caller_id):
    target = (username or "").strip().lower()
    target_mac = mac(caller_id)
    for cells in rows:
        desc = cells[3].strip()
        if target and desc.lower() == target:
            return {
                "onu": cells[0],
                "status": cells[1],
                "mac": cells[2],
                "description": cells[3],
                "distance": cells[4],
                "last_deregister_reason": cells[8] if len(cells) > 8 else "-",
            }
    for cells in rows:
        if target and target in cells[3].lower():
            return {
                "onu": cells[0],
                "status": cells[1],
                "mac": cells[2],
                "description": cells[3],
                "distance": cells[4],
                "last_deregister_reason": cells[8] if len(cells) > 8 else "-",
            }
    for cells in rows:
        if target_mac and mac(cells[2]) == target_mac:
            return {
                "onu": cells[0],
                "status": cells[1],
                "mac": cells[2],
                "description": cells[3],
                "distance": cells[4],
                "last_deregister_reason": cells[8] if len(cells) > 8 else "-",
            }
    return None

def parse_opm(rows):
    result = []
    for cells in rows:
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

def main():
    payload = json.loads(sys.stdin.read())
    base = payload["base_url"].rstrip("/")
    username = payload.get("username", "")
    password = payload.get("password", "")
    target = payload.get("target", "")
    caller_id = payload.get("caller_id", "")

    login_url = base + "/action/main.html"
    status_url = base + "/action/onustatusinfo.html"
    opm_url = base + "/action/onuopmdiag.html"

    cookies = http.cookiejar.CookieJar()
    opener = urllib.request.build_opener(urllib.request.HTTPCookieProcessor(cookies))
    headers = {
        "User-Agent": "Mozilla/5.0 (Windows NT 10.0; Win64; x64) Chrome/152"
    }

    def request(url, data=None, referer=None):
        h = dict(headers)
        h["Referer"] = referer or login_url
        if data is not None:
            h["Content-Type"] = "application/x-www-form-urlencoded"
        req = urllib.request.Request(
            url,
            data=data,
            headers=h,
            method="POST" if data is not None else "GET",
        )
        response = opener.open(req, timeout=10)
        return response.read().decode("gb2312", errors="replace")

    request(base + "/action/login.html")
    login_data = urllib.parse.urlencode({
        "user": username,
        "pass": password,
        "button": "login",
        "who": "100",
    }).encode()
    request(login_url, login_data, base + "/action/login.html")

    for pon in range(1, 5):
        status_html = request(status_url)
        key = session_key(status_html)
        data = {
            "select": str(pon),
            "searchMac": "",
            "searchDescription": "",
            "who": "100",
        }
        if key:
            data["SessionKey"] = key
        status_html = request(
            status_url,
            urllib.parse.urlencode(data).encode(),
            status_url,
        )
        status_rows = parse_rows(status_html)
        matched = match_row(status_rows, target, caller_id)
        if not matched:
            continue

        opm_html = request(opm_url)
        key = session_key(opm_html)
        data = {
            "select": str(pon),
            "searchMac": "",
            "searchDescription": "",
            "who": "100",
        }
        if key:
            data["SessionKey"] = key
        opm_html = request(
            opm_url,
            urllib.parse.urlencode(data).encode(),
            opm_url,
        )
        opm_rows = parse_opm(parse_rows(opm_html))

        opm = None
        for row in opm_rows:
            if row["onu"].strip().upper() == matched["onu"].strip().upper():
                opm = row
                break
        if opm is None and caller_id:
            target_mac = mac(caller_id)
            for row in opm_rows:
                if mac(row.get("mac")) == target_mac:
                    opm = row
                    break
        if opm is None:
            for row in opm_rows:
                if row.get("description", "").strip().lower() == matched["description"].strip().lower():
                    opm = row
                    break

        result = dict(matched)
        if opm:
            result.update({
                "temperature": opm.get("temperature"),
                "voltage": opm.get("voltage"),
                "tx_bias": opm.get("tx_bias"),
                "tx_power": opm.get("tx_power"),
                "rx_power": opm.get("rx_power"),
            })
        else:
            result.update({
                "temperature": None,
                "voltage": None,
                "tx_bias": None,
                "tx_power": None,
                "rx_power": None,
            })
        print(json.dumps({"ok": True, "data": result}, ensure_ascii=False))
        return

    print(json.dumps({"ok": True, "data": None}, ensure_ascii=False))

if __name__ == "__main__":
    try:
        main()
    except Exception as exc:
        print(json.dumps({"ok": False, "error": str(exc)}, ensure_ascii=False))
        sys.exit(1)
