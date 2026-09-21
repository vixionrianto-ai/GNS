import json
import re
import sys
import urllib.parse
import urllib.request
import http.cookiejar
from html import unescape

def clean(v):
    v=re.sub(r"<[^>]+>"," ",v); v=unescape(v); return re.sub(r"\s+"," ",v).strip()

def key(h):
    for p in [r"name=['\"]SessionKey['\"][^>]*value=['\"]([^'\"]*)['\"]",r"SessionKey\s*\.\s*value\s*=\s*['\"]([^'\"]*)['\"]"]:
        m=re.search(p,h,re.I|re.S)
        if m:return m.group(1).strip()
    return ""

def rows(h):
    out=[]
    for rh in re.findall(r"<tr[^>]*>(.*?)</tr>",h,re.I|re.S):
        cs=[clean(x) for x in re.findall(r"<td[^>]*>(.*?)</td>",rh,re.I|re.S)]
        if len(cs)>=5 and re.match(r"^(EPON|GPON)",cs[0],re.I): out.append(cs)
    return out

def mac(v): return re.sub(r"[^0-9A-Fa-f]","",v or "").upper()

def match(rs,target,cid):
    t=target.strip().lower(); m=mac(cid)
    for c in rs:
        if t and c[3].lower()==t:return c
    for c in rs:
        if t and t in c[3].lower():return c
    for c in rs:
        if m and mac(c[2])==m:return c
    return None

def main():
    p=json.loads(sys.stdin.read()); base=p["base_url"].rstrip("/")
    user=p.get("username",""); pw=p.get("password",""); target=p.get("target",""); cid=p.get("caller_id","")
    jar=http.cookiejar.CookieJar(); op=urllib.request.build_opener(urllib.request.HTTPCookieProcessor(jar))
    hdr={"User-Agent":"Mozilla/5.0 (Windows NT 10.0; Win64; x64) Chrome/152"}
    def req(url,data=None,ref=None):
        h=dict(hdr); h["Referer"]=ref or base+"/action/main.html"
        if data is not None:h["Content-Type"]="application/x-www-form-urlencoded"
        q=urllib.request.Request(url,data=data,headers=h,method="POST" if data is not None else "GET")
        return op.open(q,timeout=10).read().decode("gb2312","replace")
    req(base+"/action/login.html")
    data=urllib.parse.urlencode({"user":user,"pass":pw,"button":"login","who":"100"}).encode()
    req(base+"/action/main.html",data,base+"/action/login.html")
    for pon in range(1,5):
        h=req(base+"/action/onustatusinfo.html"); k=key(h)
        d={"select":str(pon),"searchMac":"","searchDescription":"","who":"100"}
        if k:d["SessionKey"]=k
        h=req(base+"/action/onustatusinfo.html",urllib.parse.urlencode(d).encode(),base+"/action/onustatusinfo.html")
        m=match(rows(h),target,cid)
        if not m:continue
        oh=req(base+"/action/onuopmdiag.html"); k=key(oh)
        d={"select":str(pon),"searchMac":"","searchDescription":"","who":"100"}
        if k:d["SessionKey"]=k
        oh=req(base+"/action/onuopmdiag.html",urllib.parse.urlencode(d).encode(),base+"/action/onuopmdiag.html")
        opm=None
        for c in rows(oh):
            if len(c)>=9 and c[0].strip().upper()==m[0].strip().upper(): opm=c; break
        if opm is None:
            for c in rows(oh):
                if len(c)>=9 and cid and mac(c[1])==mac(cid): opm=c; break
        result={"onu":m[0],"status":m[1],"mac":m[2],"description":m[3],"distance":m[4],"last_deregister_reason":m[8] if len(m)>8 else "-","temperature":opm[4] if opm else None,"voltage":opm[5] if opm else None,"tx_bias":opm[6] if opm else None,"tx_power":opm[7] if opm else None,"rx_power":opm[8] if opm else None}
        print(json.dumps({"ok":True,"data":result},ensure_ascii=False)); return
    print(json.dumps({"ok":True,"data":None},ensure_ascii=False))
if __name__=="__main__":
    try:main()
    except Exception as e: print(json.dumps({"ok":False,"error":str(e)},ensure_ascii=False)); sys.exit(1)
