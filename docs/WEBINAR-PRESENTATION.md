# Diani Christmas presentation

This is the client-approved exception to the usual Tour quote flow. Its conversion is **register for the presentation → add the presentation to a calendar**. It does not book a holiday or collect money.

## Deploy and configure

1. Deploy the custom theme and HKS Core through the existing GitHub-to-cPanel workflow. No new production plugin or library is required.
2. Open WordPress admin as an administrator. HKS Core creates two normal **draft Pages** once: `/diani-christmas-presentation/` and `/diani-christmas-presentation/thank-you/`. Existing independently authored pages on those routes are preserved.
3. Under **HKS Settings → Webinar presentation**, enter the confirmed October webinar date, start time in **Africa/Nairobi**, duration (55 minutes by default), and actual HTTPS joining URL. The December holiday dates are separate and never become calendar-event dates.
4. Assign an approved real Diani Sea Resort image. Any room photograph must show the quoted Comfort category. Optionally supply the confirmed presenter name and approved portrait. The bundled resort photograph is available only in an administrator's draft preview; it does not substitute for assigned public media.
5. Confirm the existing published privacy-policy Page is selected in WordPress or HKS Settings. Preview both Pages, then publish both when the operational details are confirmed. Until then, public registration stays closed.
6. Verify the existing FluentSMTP connection with the site's normal mail test. Complete one controlled registration after deployment and confirm its email in FluentSMTP's logs and the recipient inbox. This last live transport check was not performed in the local build.

Date, time, joining URL, title and duration are shared by the landing page, thank-you, email, Google Calendar and ICS. Changing the event settings updates future invitations and sends; it does not automatically update events already imported into a personal calendar or resend earlier mail.

## Registrations and confirmation emails

Administrators can open **Tours → Webinar registrations** to search by name/email, view the four answers and mail status, or download the attendee CSV. Registrations are private WordPress records (`hks_webinar_signup` and `_hks_webinar_*` metadata), excluded from public search and REST collections. Follow the site's agreed retention/access practices; this feature does not invent a new retention policy.

Required answers: name, email and booking-payment band. Family priority is optional, limited to 500 characters. Bands are KES 60,000–79,999, KES 80,000–99,999 and KES 100,000 or more. Selecting a larger amount does not change the holiday price. The KES 60,000 payment is credited to the holiday total, and answering the form makes no payment commitment.

The server validates, stores and verifies the answers before returning success. Email is queued through WordPress Cron (`hks_send_webinar_confirmation`) and sent through `wp_mail`, allowing the existing FluentSMTP transport to handle it. SMTP is not awaited by the form response. Ensure WP Cron runs; if hosting disables traffic-triggered Cron, retain/configure the site's normal server Cron job. A quiet site without working Cron will leave mail queued.

One event/email identity is stored once, case-insensitively. Duplicate submissions keep the original answers and do not resend an already accepted confirmation. Failed sends receive up to three worker attempts; an administrator can retry from the registration record. “Accepted by mailer” records transport acceptance, not proof of inbox delivery. The thank-you says whether mail is queued, accepted or failed, while retaining the calendar/joining details.

## Cache, privacy and calendar behavior

- Exclude the thank-you path and `/wp-json/hks/v1/webinar/*` from any full-page/CDN cache. They send `Cache-Control: no-store, private`; the thank-you also sets `DONOTCACHEPAGE`. Configure caches that run before WordPress to respect these exclusions.
- A signed, expiring HttpOnly receipt cookie permits the genuine success view. Direct or forged visits return a registration prompt. No personal details or receipt tokens are placed in URLs.
- The form obtains a fresh short-lived signed token at submit time, so a cached landing page cannot serve an expired form token. Answers stay in the form after an error. Honeypot, minimum fill time, an IP-hashed rate limit, and expiring per-email database locks limit automated/repeated writes.
- Google Calendar and `.ics` links contain the event details and actual joining URL, with no attendee answers. Both use unambiguous UTC timestamps and identify Africa/Nairobi. ICS uses a stable UID and UTF-8-safe RFC 5545 folding. Calendar downloads remain available for the published event after registration closes at its start time.
- Both pages are `noindex`. Analytics events are `webinar_registration_cta_click`, `webinar_registration_complete` (only after a new saved registration), and `webinar_calendar_cta_click` with `menu`, `google` or `ics` provider. Payloads contain event/CTA identifiers only. A calendar click is never described as proof that an event was saved. Existing Meta receives custom `WebinarRegistration` when available; confirm production consent/tracking setup separately.

## Performance and verification

The two templates use their own approximately 8 KB CSS and 6 KB deferred JavaScript, about **4.4 KB combined gzip**, with no new library. They skip catalogue navigation/style assets and the emoji loader. Montserrat is self-hosted (about 37 KB); the draft photograph is WebP (about 99 KB). Public assigned images use WordPress responsive image markup with explicit dimensions. Hosting, the final image, cache and consent/tracking configuration still determine live Core Web Vitals.

Local validation uses an isolated WordPress 7.0 + SCF 6.9.1 + SQLite fixture with all mail intercepted locally. Run the integration test only against a disposable local installation defining `HKS_WEBINAR_TESTING=true` and `WP_ENVIRONMENT_TYPE=local`, with HKS Core and Wayfinder active:

```text
php tools/test_webinar_wp.php /path/to/test-wordpress/wp-load.php
python -B tools/test_webinar_http.py http://127.0.0.1:8096
```

The PHP test deliberately changes event options, creates local fixture Pages/images/registrations, and simulates failures. It must never target production. The HTTP test is restricted to localhost and also creates test registrations; its fixture must intercept outgoing mail.

The 71 WordPress integration checks and 17 HTTP checks cover strict dates, publication gates, validation, storage failure, duplicate handling, private access, signed receipts, email failure/retry/deduplication, event timing, ICS encoding, CSV formula handling and lock recovery. Browser checks cover required fields, focus, save/redirect, calendar menu keyboard behavior, safe disabled-JavaScript behavior, and desktop/mobile layouts. The tested landing HTML transferred at about 8.2 KB with gzip. These local measurements are not a production Core Web Vitals score.

The required scaffold check found a missing direct-access guard in the existing testimonial pattern; the standard ABSPATH guard was added without changing its rendered content. The scaffold, content-model, MVP, conversion, public-template and cPanel validators pass, as do the existing 61 server and 58 browser inquiry boundary checks and the 49-file PHP syntax inventory.
