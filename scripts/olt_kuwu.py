import urllib.parse
import urllib.request
import http.cookiejar
import getpass
import re
from html import unescape

BASE = "http://lamongan.gopal.my.id:725"
LOGIN_URL = BASE + "/action/main.html"
STATUS_URL = BASE + "/action/onustatusinfo.html"
OPM_URL = BASE + "/action/onuopmdiag.html"

username = input("Username OLT: ")
password = getpass.getpass("Password OLT: ")

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
        url, data=data, headers=h,
        method="POST" if data is not None else "GET"
    )
    response = opener.open(req, timeout=10)
    return response.read().decode("gb2312", errors="replace")

def ambil_session_key(html):
    match = re.search(
        r"name=['\"]SessionKey['\"][^>]*value=['\"]([^'\"]*)['\"]",
        html, re.I
    )
    return match.group(1) if match else ""

def bersihkan(cell):
    cell = re.sub(r"<[^>]+>", " ", cell)
    cell = unescape(cell)
    cell = re.sub(r"\s+", " ", cell)
    return cell.strip()

def parse_status(html):
    hasil = []
    rows = re.findall(r"<tr[^>]*>(.*?)</tr>", html, re.I | re.S)
    for row in rows:
        cells = re.findall(r"<td[^>]*>(.*?)</td>", row, re.I | re.S)
        if len(cells) < 5:
            continue
        cells = [bersihkan(x) for x in cells]
        if not cells[0].startswith("EPON"):
            continue
        hasil.append({
            "onu": cells[0], "status": cells[1], "mac": cells[2],
            "description": cells[3], "distance": cells[4],
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
        if not cells[0].startswith("EPON"):
            continue
        hasil.append({
            "onu": cells[0], "mac": cells[1], "description": cells[2],
            "distance": cells[3], "temperature": cells[4],
            "voltage": cells[5], "tx_bias": cells[6],
            "tx_power": cells[7], "rx_power": cells[8],
        })
    return hasil

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

semua = []

for pon in range(1, 5):
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

    if pon == 1:
        print("DEBUG PON1 STATUS HTML")
        print("HTML length:", len(html_status))
        print("SessionKey:", "ADA" if session_key else "KOSONG")
        print("EPON ditemukan:", "EPON" in html_status.upper())
        print("login ditemukan:", bool(re.search(r"login|password|name=['\\\"]user['\\\"]", html_status, re.I)))
        print("TR:", len(re.findall(r"<tr[^>]*>.*?</tr>", html_status, re.I | re.S)))
        print("TD:", len(re.findall(r"<td[^>]*>.*?</td>", html_status, re.I | re.S)))

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
        })

for row in semua:
    print(row)

print(f"\nTOTAL ONU: {len(semua)}")