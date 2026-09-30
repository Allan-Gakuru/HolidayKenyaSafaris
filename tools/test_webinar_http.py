"""HTTP checks against the disposable local WordPress webinar fixture only."""
import gzip
import http.cookiejar
import json
import sys
import time
import urllib.error
import urllib.parse
import urllib.request
import uuid

base = sys.argv[1] if len(sys.argv) > 1 else "http://127.0.0.1:8096"
assert urllib.parse.urlsplit(base).hostname in ("127.0.0.1", "localhost"), "Local test fixture only"
opener = urllib.request.build_opener(urllib.request.HTTPCookieProcessor(http.cookiejar.CookieJar()))
checks = 0

def request(path, payload=None, headers=None):
    data = json.dumps(payload).encode() if payload is not None else None
    req = urllib.request.Request(base + path, data=data, headers=headers or ({"Content-Type": "application/json"} if data else {}))
    try:
        response = opener.open(req, timeout=30)
    except urllib.error.HTTPError as exc:
        response = exc
    return response.status, response.headers, response.read()

def verify(value, message):
    global checks
    assert value, message
    checks += 1

status, headers, body = request("/diani-christmas-presentation/", headers={"Accept-Encoding": "gzip"})
page_encoding = headers.get("Content-Encoding", "identity")
verify(status == 200, "Registration page loads")
html_bytes = gzip.decompress(body) if headers.get("Content-Encoding") == "gzip" else body
html = html_bytes.decode()
verify("data-webinar-form" in html, "Complete configured event renders form")
verify('<form method="post"' in html and 'data-webinar-cta="form" disabled' in html, "Unavailable JavaScript cannot submit personal data through a GET URL")
verify("navigation.js" not in html and "/style.css" not in html and "wp-emoji-release" not in html, "Catalogue and emoji assets are excluded")
status, headers, direct = request("/diani-christmas-presentation/thank-you/")
verify("You’re registered." not in direct.decode(), "Direct thank-you does not claim success")
verify("no-store" in headers.get("Cache-Control", ""), "Thank-you is not cached")
status, headers, context = request("/wp-json/hks/v1/webinar/form")
verify(status == 200 and "no-store" in headers.get("Cache-Control", ""), "Fresh token context is not cached")
payload = {"name": "HTTP Fixture Parent", "email": f"http-{uuid.uuid4()}@example.test", "payment_band": "80000_99999", "priority": "Quiet family time.", "website": "", "started_at": int(time.time() * 1000) - 3000, "form_token": json.loads(context)["form_token"]}
status, headers, result = request("/wp-json/hks/v1/webinar/registrations", payload)
verify(status == 201 and json.loads(result)["stored"], "HTTP registration is persisted")
verify("HttpOnly" in headers.get("Set-Cookie", "") and "SameSite=Lax" in headers.get("Set-Cookie", ""), "Receipt is HttpOnly and SameSite")
verify("no-store" in headers.get("Cache-Control", ""), "Submission response is private")
status, _, result = request("/wp-json/hks/v1/webinar/registrations", payload)
verify(status == 200 and not json.loads(result)["created"], "Duplicate HTTP registration is idempotent")
status, _, thanks = request("/diani-christmas-presentation/thank-you/")
verify("You’re registered." in thanks.decode() and "Add to calendar" in thanks.decode(), "Receipt opens genuine calendar thank-you")
status, headers, calendar = request("/wp-admin/admin-post.php?action=hks_webinar_calendar")
verify(status == 200 and headers.get("Content-Type", "").startswith("text/calendar"), "ICS download has correct content type")
verify("attachment;" in headers.get("Content-Disposition", "") and b"BEGIN:VEVENT" in calendar, "Calendar is a downloadable event")
verify(payload["email"].encode() not in calendar and b"Africa/Nairobi" in calendar, "ICS has timezone and no registrant details")
status, _, _ = request("/wp-admin/admin-post.php?action=hks_webinar_export")
verify(status in (400, 403), "Anonymous CSV export is denied")
bad = {**payload, "email": "invalid"}
status, headers, result = request("/wp-json/hks/v1/webinar/registrations", bad)
verify(status == 422 and "no-store" in headers.get("Cache-Control", ""), "Validation errors cannot be cached")
print(f"Passed {checks} local HTTP webinar checks.")
print(f"Registration HTML: {len(html_bytes):,} bytes; transferred: {len(body):,} bytes; encoding: {page_encoding}.")
