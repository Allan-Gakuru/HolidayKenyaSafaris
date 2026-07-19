# Holiday Kenya Safaris site operations guide

This is the practical operating manual for the Holiday Kenya Safaris WordPress
website. It covers the current MVP, the content approval workflow, inquiry
handling, deployment, maintenance, and the remaining launch gates.

The exact brand name is **Holiday Kenya Safaris**. The selected identity is
**The Wayfinder**. Holiday Kenya Safaris serves the Kenyan domestic market and
is operated by Ashford Tours & Travel.

## 1. What the website is designed to do

The website turns a visitor into a qualified quote conversation:

```text
Facebook ad or other visit
-> relevant Tour or Campaign page
-> package details and trust information
-> short quote form
-> private WordPress inquiry record
-> visitor-reviewed WhatsApp message
-> visitor sends the message in WhatsApp
-> consultant confirms a quote and next steps
```

The website does not take online payments or confirm bookings. A booking exists
only after the operator completes its normal quotation and booking process.

## 2. Non-negotiable publishing rules

Never publish or imply any of the following without an approved source:

- a Campaign price;
- lodge, room, vehicle, guide, or departure availability;
- a policy or legal promise;
- a review, membership, licence, award, or business credential;
- a photograph whose website usage rights have not been verified;
- a response-time promise;
- a claim that a visitor sent a WhatsApp message merely because WhatsApp opened.

Use only Kenyan shillings for optional Campaign prices. Do not convert an Ashford
USD rate and present the conversion as an approved KSh price.

Any source value marked `CLIENT CONFIRMATION REQUIRED` is internal only. Remove
the marker from public fields only after resolving the fact; never rewrite the
marker into a confident public claim.

## 3. System requirements and components

The current code requires:

- WordPress 6.6 or later;
- PHP 8.3 or later;
- Secure Custom Fields 6.9.1 or later;
- the `HKS Core` plugin;
- the `HKS Wayfinder` block theme.

Activate Secure Custom Fields before HKS Core. HKS Core owns Tours, Campaigns,
FAQs, Destinations, settings, inquiry capture, validation, and publication
safeguards. HKS Wayfinder owns the public design and templates.

Do not replace the theme with Elementor, Divi, or another page builder. Do not
deactivate HKS Core while the site is serving Tours or collecting inquiries.

## 4. WordPress administration map

| WordPress area | Purpose |
|---|---|
| **Tours -> All Tours** | Canonical package records and public Tour pages |
| **Tours -> Campaigns** | Focused landing pages linked to one Tour |
| **Tours -> FAQs** | Reusable, source-approved questions and answers |
| **Tours -> Destinations** | Public destination terms and approved guidance |
| **Tours -> Tour Types** | Package classification |
| **Tours -> Occasions** | Audience or occasion classification |
| **Tours -> Travel Styles** | Travel-style classification |
| **Tours -> Quote inquiries** | Private recovery records; Administrators only |
| **Tours -> Import MVP drafts** | One-time/controlled seed importer |
| **HKS Settings** | Global contact, legal, analytics, social, and brand settings |
| **Media** | Images plus rights, provenance, alt text, and credit records |
| **Appearance -> Editor** | Block-theme templates and styles; edit cautiously |

Only trusted Administrators should access **Quote inquiries** and **HKS
Settings**. Inquiry access requires the WordPress `manage_options` capability.

## 5. Immediate steps after importing the MVP drafts

The importer creates three Tour drafts and three linked Campaign drafts. It does
not publish content, import photographs, invent prices, or create policies.

Important: do not run the importer again after manually editing a draft unless
you intentionally want that draft refreshed from the seed file. The importer
updates records that remain WordPress drafts. It protects published, pending,
private, and other non-draft records.

Use this order:

1. Review and publish each canonical Tour.
2. Verify each public Tour page and quote form.
3. Review its linked Campaign.
4. Publish the Campaign when its focused message is ready.
5. Campaigns remain `noindex` by default until an intentional SEO decision is implemented.

## 6. Tour workflow

A Tour is the single canonical record for a package. Do not create duplicate
Tours merely to change a Facebook-ad headline or audience angle. Use a Campaign
for that.

### 6.1 Review and approve an imported Tour

1. Open **Tours -> All Tours**.
2. Select **Edit** on the Tour; do not use Quick Edit for factual approval.
3. Review the WordPress title, excerpt, and overview.
4. Review every visible package field: route, duration, logistics, itinerary,
   inclusions, exclusions, suitability, gallery, FAQs, and classifications.
5. Open the source URL and compare the public facts against the source and current
   operator knowledge.
6. Leave unclear information blank and keep the record in Draft until the visible
   facts and assigned media are ready.
7. Preview the Tour, then select **Publish**.

There is no separate approval button or source-status field. The WordPress
**Publish** action is the authorized editor's approval for the visible copy and
assigned media.

### 6.2 Minimum Tour publication gate

The publication guard requires a public Tour title and rejects the internal
`CLIENT CONFIRMATION REQUIRED` marker anywhere in public candidate content.
Other optional values may remain blank and are omitted or handled by the
template's deliberate empty state.

If a public save is unsafe, WordPress blocks it or returns the item to Draft and
shows the exact reasons. Correct the listed fields; do not bypass the guard by
changing plugin code or inserting data directly into the database.

### 6.3 Creating a new Tour

1. Select **Tours -> Add Tour**.
2. Add the public title, short excerpt, and concise overview.
3. Enter structured package facts rather than placing everything in the overview.
4. Add Destinations, Tour Types, Occasions, and Travel Styles.
5. Add the itinerary, inclusions, exclusions, public notes, gallery, and selected FAQs where available.
6. Save as Draft, preview, review the visible result, and publish.

Use revisions to inspect or restore editorial changes. Preserve the source
snapshot even when marketing copy is rewritten for local buyers.

### 6.4 Unpublishing or deleting a Tour

Changing a published Tour to Draft, Private, Trash, or deleting it automatically
returns its public linked Campaigns to Draft. This prevents a Campaign from
remaining public without its canonical facts.

Prefer Draft or Private while investigating a problem. Move content to Trash only
when the record is genuinely unwanted. Do not permanently delete source records
or inquiries casually.

## 7. Pricing workflow

### 7.1 Tours are price-free

Do not enter prices on Tours. The Tour editor has no active price field, and Tour
cards, archives, Destination pages, related Tours, canonical Tour pages, and Tour
quote panels show neither a value nor a request-rate fallback. Old Tour price
metadata may remain stored after deployment, but the site ignores it.

### 7.2 Publishing an optional Campaign price

Open the focused Campaign and use **From price per person (KSh)** only when price
is a useful selling point for that Campaign. Enter one positive whole KSh amount
that truthfully represents a per-person starting price, then preview and publish
the Campaign. Leaving the field blank produces no price output.

Do not copy a legacy Tour amount or convert an Ashford USD amount automatically.
Campaign dates do not change or remove the price. When the offer changes, update
or clear the Campaign field manually.

## 8. Campaign workflow

A Campaign is a focused landing-page message for one audience, occasion, desire,
problem, or objection. It inherits the Tour's itinerary, logistics, inclusions,
policies, and other canonical facts, and may own one optional selling price.

### 8.1 Review and publish a Campaign

1. Publish the linked Tour first.
2. Open **Tours -> Campaigns** and edit the Campaign.
3. Confirm **Linked Tour** points to exactly one correct published Tour.
4. Review the hero headline, supporting copy, optional Campaign price, and navigation mode.
5. Review the optional planning start and end dates. They do not schedule publication or change price.
6. Preview the Campaign, then select **Publish** and verify the landing page.

A Campaign cannot publish without exactly one published linked Tour. It is
`noindex` by default through the template; there is no client-facing indexing or
lifecycle-status field.

### 8.2 Campaign content rules

- Keep factual itinerary changes on the linked Tour. Keep an optional selling price on the Campaign itself.
- Do not imply guaranteed wildlife, weather, lodge availability, or savings.
- Match the landing-page promise to the ad that sends traffic to it.
- Keep one clear angle per Campaign.
- Use Campaign-specific proof only when it is real, deliberately entered or assigned, and ready for publication.

## 9. Destinations and classifications

Assign at least one relevant Destination to each Tour. Use Tour Type, Occasion,
and Travel Style to organize content; expose a public filter only when enough
Tours exist to make it useful.

Destination archive pages always show the term name and published Tours.
Additional overview, travel-time, best-time, weather, or access guidance appears
only when the Destination has:

- a source URL or reference;
- a valid checked date;
- Source status **Operator reviewed** or **Client confirmed**;
- public text without unresolved confirmation markers.

Avoid duplicate terms such as `Mara`, `Maasai Mara`, and `Masai Mara` for the same
destination. Choose one canonical spelling and merge mistakes before the
catalogue grows.

## 10. FAQs and policies

### 10.1 FAQs

1. Open **Tours -> FAQs** and add a question as the WordPress title.
2. Add the answer in the structured Answer field.
3. Record its source URL or reference, confirmation status, checked date, and
   expiry/review date when applicable.
4. Publish the FAQ.
5. Select it in a Tour or Campaign's **Featured FAQs** field.

The public template renders only published, sourced, checked, unexpired FAQs with
**Operator reviewed** or **Client confirmed** status.

### 10.2 Policies

Package policies live on the canonical Tour. Every displayed policy needs:

- a clear public summary;
- a traceable source;
- a checked date;
- **Operator reviewed** or **Client confirmed** status;
- a valid expiry/review date when one applies.

Do not fill policy gaps with generic travel-industry language. Deposit,
cancellation, refund, no-show, child, insurance, liability, documents, and quote
validity wording must come from the client/operator.

## 11. Media and image-rights workflow

The site intentionally works without photographs until approved media exists.

For every image:

1. Upload it through **Media -> Add New**.
2. Open the attachment details.
3. Add descriptive native **Alt Text** for the image's purpose.
4. Complete **Media rights and provenance (internal)**:
   - asset owner;
   - photographer/creator where known;
   - source URL or reference;
   - permission status;
   - approved usage scopes;
   - licence or permission basis;
   - permission evidence where available;
   - permission and rights-checked dates;
   - expiry and restrictions;
   - required credit line.
5. Select **Website** in Approved usage scopes.
6. Use **Operator reviewed** or **Client confirmed** only when permission is
   genuinely verified.
7. Assign the image as a Tour/Campaign featured image or structured gallery image.
8. Verify the public page and any required credit.

An image is hidden by the public Tour templates unless it has an acceptable
permission status, Website scope, rights checked date, alt text, live permission,
and a credit line when credit is required.

Do not assume that an image on an Ashford, lodge, park, photographer, or social
media page can be reused. Do not use identifiable customer photographs without
the required marketing consent.

## 12. HKS global settings

Open **HKS Settings** as an Administrator. Each public value is stored beside its
confirmation status, source/reference, checked date, and internal notes.

The only safe defaults are:

- company name: `Holiday Kenya Safaris`;
- operator disclosure: `Holiday Kenya Safaris is operated by Ashford Tours & Travel.`;
- official Holiday Kenya Safaris mobile and WhatsApp destination: `254712965131`;
- public email: `info@holidaykenyasafaris.ke`;
- Instagram: `https://www.instagram.com/holidaykenyasafaris/`;
- Facebook: `https://www.facebook.com/people/Holiday-Kenya-Safaris/61591508593846/`.

Other settings remain blank until confirmed, including address, map, hours,
response expectation, other social profiles, legal pages, analytics IDs,
and default sharing imagery.

Do not add a public value without its confirmation envelope. Never put passwords,
API secrets, private keys, or customer details in HKS Settings.

## 13. Quote inquiry and WhatsApp workflow

### 13.1 What the visitor experiences

1. The visitor selects a quote CTA.
2. An accessible form asks for name, phone, package, preferred date/month, and
   traveler count plus Tour-specific optional questions.
3. The visitor consents to private recovery storage.
4. **Save & review WhatsApp message** creates or refreshes a private inquiry.
5. The visitor reviews the exact message and request reference.
6. **Open WhatsApp to send** launches WhatsApp.
7. The visitor must still select Send inside WhatsApp.

On `/group-travel/`, the same form is shown inline. The visitor first selects a
Destination and then one of its published Tours. The standard name, phone,
date/month, traveler-count, consent, recovery, review, and WhatsApp steps remain
unchanged. Destination and Tour choices update automatically when catalogue
records are published or their Destination assignments change.

### 13.2 Handling inquiries in WordPress

1. Sign in as an Administrator.
2. Open **Tours -> Quote inquiries**.
3. Review the reference, name, phone, package, Destination where available,
   travel plan, capture time, and WhatsApp state.
4. Open the record for the inquiry route, optional answers, and campaign
   attribution. **Group Travel page** identifies requests made through the
   dedicated planner.
5. Follow up through the operator's approved phone/WhatsApp process when a valid
   inquiry has not appeared in the consultant's WhatsApp conversation.

**Opened** means only that the website launched WhatsApp. It does not prove that
the visitor sent the message. **Not recorded** means the recovery record exists
but no WhatsApp launch was recorded.

The MVP inquiry screen is a read-only recovery view, not a sales CRM. Track quote,
follow-up, booking, and revenue status through the operator's approved sales
process until a CRM workflow is deliberately added.

### 13.3 Inquiry privacy

- Only Administrators can access inquiries.
- Inquiries are excluded from public pages, search, REST responses, feeds, and
  WordPress export.
- Names, phones, dates, and budgets must never be placed in analytics events.
- Do not export or copy inquiry data into insecure spreadsheets or personal
  accounts.
- Approve a privacy notice, retention period, deletion process, and access policy
  before production marketing begins.
- Delete records only according to the approved retention/deletion policy.

## 14. Analytics and attribution

The site currently emits a privacy-safe event contract to `window.dataLayer` and
the `hks:analytics` browser event. It does not invent or load GA4, GTM, or Meta
Pixel IDs.

Current events include:

- `view_tour` and `view_campaign`;
- `quote_cta_click`;
- `quote_form_start`;
- `quote_form_error` without entered values;
- `quote_inquiry_saved`;
- `quote_form_complete` using a traveler-count bucket;
- `whatsapp_launch`.

`whatsapp_launch` is not a sent inquiry, qualified conversation, quote, or
booking. Reconcile website activity with real consultant conversations and sales.

Before enabling production trackers, approve the privacy/cookie behavior and add
the real IDs through a reviewed integration. Never place names, phone numbers,
travel dates, group details, or budgets in analytics parameters.

## 15. Theme and page editing

HKS Wayfinder is a custom block theme. The templates already control the
homepage, Tour catalogue, Tour pages, Campaign pages, and Destination archives.

- Edit Tour facts in Tours, not directly in templates.
- Edit Campaign messaging in Campaigns, not by duplicating a page.
- Use **Appearance -> Editor** only for deliberate global template or style work.
- Do not unlock or remove the quote CTA, source-safe dynamic blocks, skip link, or
  main-content landmark.
- Do not turn every section into a card or use generic tourism-poster layouts.
- Reserve WhatsApp green for WhatsApp actions.
- Test keyboard focus, mobile layouts, and reduced motion after template changes.

The MVP header intentionally contains only Home and Tours until additional public
pages and their content are approved.

## 16. Deployment from GitHub to cPanel

The canonical repository is:

```text
https://github.com/Allan-Gakuru/HolidayKenyaSafaris.git
```

The cPanel-managed clone is intended to live at:

```text
/home/holidayk/repositories/HolidayKenyaSafaris
```

The WordPress document root is:

```text
/home/holidayk/public_html
```

The checked-in `.cpanel.yml` copies only:

```text
wp-content/themes/hks-wayfinder
wp-content/plugins/hks-core
```

It does not deploy WordPress core, uploads, `wp-config.php`, the database, secrets,
or repository documentation.

### 16.1 Normal release

1. Complete and verify changes locally.
2. Run the repository validators and PHP syntax checks.
3. Commit and push the reviewed change to GitHub `main`.
4. In cPanel, open **Files -> Git Version Control**.
5. Manage `HolidayKenyaSafaris` and select **Update from Remote**.
6. Confirm the expected Git commit SHA.
7. Select **Deploy HEAD Commit**.
8. Review deployment output for failures.
9. Clear relevant WordPress/page/CDN caches.
10. Smoke-test the homepage, Tours, one Campaign, an inquiry, and WhatsApp launch.

Git deployment changes code only. Tours, Campaigns, settings, users, inquiries,
media, and other WordPress content remain in the production database.

### 16.2 Before every production deployment

- confirm a current filesystem and database backup or hosting snapshot exists;
- record the current live commit SHA;
- ensure cPanel's repository worktree is clean;
- confirm the deployment contains only the intended theme/plugin change;
- avoid deploying while another person is changing deployment files;
- plan cache clearing and immediate smoke testing.

### 16.3 Rollback

For a bad code release, create a reviewed Git revert of the bad commit, push the
revert to `main`, then Update from Remote and Deploy HEAD Commit again. Restore a
filesystem backup only when Git redeployment cannot recover the code.

A code rollback does not reverse database/content changes. Restore a database
only from a known-good backup and only when the incident actually involves data.

## 17. Updates and backups

Before updating WordPress, Secure Custom Fields, PHP, HKS Core, or HKS Wayfinder:

1. Take or verify a current backup.
2. Record current versions and the deployed Git SHA.
3. Update staging first when staging is available.
4. Verify the editor fields, Tour publishing, Campaign linking, quote form,
   inquiry records, and WhatsApp launch.
5. Update production and repeat the smoke test.

Do not edit HKS theme or plugin files through the WordPress Plugin File Editor or
Theme File Editor. Those changes are not tracked and will be overwritten by the
next Git deployment.

Back up both the database and `wp-content/uploads`. GitHub is a backup of custom
code, not a backup of live WordPress content or media.

## 18. Routine operating rhythm

### Daily when campaigns are active

- Check **Quote inquiries** for recoverable requests.
- Reconcile inquiry references with actual WhatsApp conversations.
- Respond through the approved consultant workflow.
- Watch for form, page, or WhatsApp reports.

### Weekly

- Review qualified conversations by Tour and Campaign.
- Record repeated questions, objections, and quote-loss reasons.
- Check published pages for stale availability language.
- Review active Campaign prices and remove any that are no longer current.
- Confirm backups are completing.

### Monthly

- Test the full quote journey on mobile and desktop.
- Review Administrator accounts and remove unnecessary access.
- Apply tested security and compatibility updates.
- Check 404s, search visibility, performance, and accessibility basics.
- Review content sources and checked dates.

## 19. Launch checklist

### Content and commercial

- Every public Tour has a reviewed traceable source.
- Every displayed price belongs to a Campaign and is a deliberate positive whole KSh per-person starting amount.
- No unapproved policy, review, membership, or availability claim is public.
- Campaigns match their ads and link to the correct Tour.
- The remaining local catalogue is added only in reviewed batches.

### Media and brand

- Every displayed image has website permission, alt text, and required credit.
- No placeholder or generated presentation image is presented as trip evidence.
- Wayfinder logo assets render clearly at header and favicon sizes.
- The site consistently uses the exact Holiday Kenya Safaris name.

### Conversion and privacy

- Required and optional quote fields work on mobile and desktop.
- A valid form creates one recoverable inquiry.
- Retrying the same browser request does not create duplicates.
- The WhatsApp message contains the correct package and reference.
- WordPress records Opened without claiming Sent.
- Privacy notice, retention, deletion, and consent behavior are approved.
- Administrator inquiry access is restricted.

### Technical

- HTTPS works without mixed-content warnings.
- Homepage, `/tours/`, Tour pages, Campaign pages, and Destinations work.
- Permalinks, 404 behavior, metadata, robots/noindex, and sharing previews work.
- Keyboard navigation, focus, dialog focus, and reduced motion work.
- Mobile layouts do not hide content behind CTAs.
- Core Web Vitals and major browsers have been checked.
- Backups and rollback have been proven.
- GA4/Meta/GTM are either correctly consented and verified or intentionally absent.

## 20. Troubleshooting

### HKS fields or menus are missing

1. Confirm Secure Custom Fields is active and at least version 6.9.1.
2. Confirm HKS Core is active.
3. Confirm WordPress is at least 6.6 and PHP is at least 8.3.
4. Check the top of the dashboard for an HKS Core requirements notice.
5. Open the full Edit screen rather than Quick Edit and scroll below the editor.

### A Tour will not publish

Read the red publication notice. Correct the listed public-content issue and save
again. A legacy Tour price never blocks publication because Tours are price-free.

### A Campaign will not publish

Publish its linked Tour first. Then confirm exactly one linked Tour, lifecycle
Testing/Active, the complete audience brief, headline/supporting copy, and
analytics label.

### A Campaign price does not appear

Confirm the amount is entered on the Campaign—not the linked Tour—and is a
positive whole number. A blank Campaign field is intentionally omitted. Clear
WordPress and host caches after deploying the updated code.

### An image does not appear

Open the Media attachment and check permission status, Website usage scope,
rights checked date, expiry, alt text, and required credit line. Then confirm the
correct image is assigned to the Tour, Campaign, or Destination.

### Quote inquiries are not visible

Only Administrators with `manage_options` can see them. Confirm HKS Core is active
and open **Tours -> Quote inquiries**. Do not weaken the capability merely to make
the menu visible to an untrusted role.

### Changes are deployed but not visible

Confirm cPanel deployed the expected SHA, then clear WordPress, host, object,
browser, and CDN caches as applicable. Check whether the change is code (Git) or
content (WordPress database); a Git deployment does not copy database content.

### A deployment fails

Stop and read the cPanel deployment output. Do not repeatedly deploy blindly.
Confirm the repository is clean, `.cpanel.yml` exists on the deployed branch, the
source directories exist, and `/home/holidayk/public_html` is writable by the
cPanel account. Preserve the last working code and database backup.

## 21. Secure support access

When browser-assisted support is needed:

1. Create a named temporary WordPress Administrator account rather than sharing a
   permanent personal account.
2. Use a unique temporary password.
3. Enter credentials directly into the shared browser session; do not commit or
   paste them into repository files.
4. Remove or downgrade the temporary account after the work is complete.
5. Review WordPress users and active sessions after support.

Never provide cPanel, GitHub, database, SSH, or WordPress secrets in a public
issue, commit, screenshot, or documentation file.

## 22. Current MVP limitations and remaining launch work

The MVP currently provides the architecture, three seed packages, linked Campaign
drafts, public templates, inquiry recovery, WhatsApp handoff, and cPanel pull
deployment. The following still require deliberate completion or verification:

- browser/dashboard QA on the deployed host;
- approved media and image-rights records;
- deliberate Campaign-specific KSh starting prices, if selected Campaigns will use price as a selling point;
- approved legal/privacy/retention wording;
- real analytics IDs and consent configuration, if tracking is enabled;
- the remaining approved local catalogue;
- staging, performance, accessibility, SEO, and cross-browser launch testing;
- final contact, social, operational proof, and policy confirmations.

Until those items are resolved, keep unsupported claims and Campaign prices absent
and use direct human quoting.

## 23. Source of truth

When information conflicts, use this order:

1. client-confirmed Holiday Kenya Safaris information;
2. current Ashford product pages and confirmed Ashford operational details;
3. the controlled workspace catalogue;
4. internal research or hypotheses, clearly marked and never treated as sales data.

The detailed execution contract remains in `AGENTS.md` and its required documents.
This guide explains day-to-day operation; it does not weaken any source,
publication, privacy, design, or deployment safeguard in that contract.
