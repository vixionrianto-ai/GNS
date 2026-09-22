#!/usr/bin/env python3
import http.cookiejar
import json
import re
import sys
import urllib.parse
import urllib.request
from html import unescape

TIMEOUT = 12

def clean(value):
    value = re.sub(r"<[^>]+>", " ", value or "")
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
    rows = []
    for row in re.findall(r"<tr[^>]*>(.*?)</tr>", html, re.I | re.S):
        cells = re.findall(r"<td[^>]*>(.*?)</td>", row, re.I | re.S)
        if len(cells) < 5:
            continue
        cells = [clean(x) for x in cells]
        if re.match(r"^(EPON|GPON)", cells[0], re.I):
            rows.append(cells)
    return rows

def norm_mac(value):
    return re.sub(r"[^0-9A-Fa-f]", "", value or "").upper()

def make_status(cells):
    return {
        "onu": cells[0],
        "status": cells[1] if len(cells) > 1 else "-",
        "mac": cells[2] if len(cells) > 2 else "",
        "description": cells[3] if len(cells) > 3 else "",
        "distance": cells[4] if len(cells) > 4 else "-",
        "last_deregister_reason": cells[8] if len(cells) > 8 else "-",
    }

def match_status(rows, target, caller_id):
    target_l = (target or "").strip().lower()
    target_mac = norm_mac(caller_id)

    for cells in rows:
        row = make_status(cells)
        if target_l and row["description"].lower() == target_l:
            return row

    for cells in rows:
        row = make_status(cells)
        if target_l and target_l in row["description"].lower():
            return row

    for cells in rows:
        row = make_status(cells)
        if target_mac and norm_mac(row["mac"]) == target_mac:
            return row

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

def find_opm(rows, matched, caller_id):
    target_onu = matched.get("onu", "").strip().upper()
    target_mac = norm_mac(caller_id or matched.get("mac", ""))
    target_desc = matched.get("description", "").strip().lower()

    for row in rows:
        if row["onu"].strip().upper() == target_onu:
            return row

    for row in rows:
        if target_mac and norm_mac(row.get("mac")) == target_mac:
            return row

    for row in rows:
        if target_desc and row.get("description", "").strip().lower() == target_desc:
            return row

    return None

def main():
    payload = json.loads(sys.stdin.read())
    base = str(payload["base_url"]).rstrip("/")
    username = str(payload.get("username", ""))
    password = str(payload.get("password", ""))
    target = str(payload.get("target", ""))
    caller_id = str(payload.get("caller_id", ""))

    login_page = base + "/action/login.html"
    login_url = base + "/action/main.html"
    status_url = base + "/action/onustatusinfo.html"
    opm_url = base + "/action/onuopmdiag.html"

    cookies = http.cookiejar.CookieJar()
    opener = urllib.request.build_opener(
        urllib.request.HTTPCookieProcessor(cookies)
    )

    headers = {
        "User-Agent": "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 Chrome/152 Safari/537.36",
        "Accept": "text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8",
    }

    def request(url, data=None, referer=None):
        h = dict(headers)
        if referer:
            h["Referer"] = referer
        if data is not None:
            h["Content-Type"] = "application/x-www-form-urlencoded"
        req = urllib.request.Request(
            url,
            data=data,
            headers=h,
            method="POST" if data is not None else "GET",
        )
        with opener.open(req, timeout=TIMEOUT) as response:
            return response.read().decode("gb2312", errors="replace")

    # The same CookieJar is used for the complete login -> status -> OPM flow.
    request(login_page)
    login_data = urllib.parse.urlencode({
        "user": username,
        "pass": password,
        "button": "Login",
        "who": "100",
    }).encode()
    login_html = request(login_url, login_data, login_page)

    # Some firmware revisions use lowercase button value. Retry login only if
    # the first login response still looks like the login page.
    if re.search(r'name=["\']user["\']', login_html, re.I) and re.search(
        r'name=["\']pass["\']', login_html, re.I
    ):
        cookies.clear()
        request(login_page)
        login_data = urllib.parse.urlencode({
            "user": username,
            "pass": password,
            "button": "login",
            "who": "100",
        }).encode()
        request(login_url, login_data, login_page)

    for pon in range(1, 5):
        status_page = request(status_url, status_url)
        key = session_key(status_page)

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
        matched = match_status(status_rows, target, caller_id)

        if not matched:
            continue

        opm_page = request(opm_url, opm_url)
        key = session_key(opm_page)

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
        opm = find_opm(parse_opm(parse_rows(opm_html)), matched, caller_id)

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
