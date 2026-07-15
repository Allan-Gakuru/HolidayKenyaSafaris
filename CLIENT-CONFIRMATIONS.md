# Client Confirmations Register

Use this file for project-level decisions such as contact details, legal wording, privacy, analytics, and commercial policy. It is not a per-Tour, per-image, FAQ, Destination, price, or source approval system. In WordPress, an authorized editor's decision to publish public content and assigned media is the approval signal.

## Confirmed

| Item | Status | Decision |
|---|---|---|
| Exact name | Confirmed | Holiday Kenya Safaris |
| Market | Confirmed | Local Kenyan market |
| Operator relationship | Confirmed | Disclose operation by Ashford Tours & Travel |
| Identity direction | Confirmed | The Wayfinder |
| Main conversion | Confirmed | WhatsApp quote inquiry |
| Product scope | Confirmed | Domestic safaris, excursions, coast, staycations, groups, and relevant local special-interest products |
| Default exclusions | Confirmed | International holidays, visas, transfers, and inbound-only products unless later approved |
| Pricing presentation | Confirmed | One optional `From price per person (KSh)` field per Tour; otherwise `Request current KSh rate` |
| Editorial approval | Confirmed | Draft means private; publishing by an authorized editor approves public copy and assigned media without additional confirmation fields |
| Content dates | Confirmed | Start and end dates exist only on Campaigns; Tour prices are updated manually and never auto-expire |
| Required intake fields | Confirmed | Name, phone, package, preferred date/month, travelers |
| Inquiry recovery storage | Confirmed | Save a private WordPress inquiry after explicit disclosure and contact consent, before showing the WhatsApp review step |
| Site scope | Confirmed | Home, catalogue, destinations, Tour pages, trust/about, contact, campaign template |
| CMS direction | Confirmed | WordPress with templates and structured content management |

## Required Before Production Build or Integration

| Item | Current status | Needed action | Blocking point |
|---|---|---|---|
| Production Wayfinder logo | Approved concept only | Redraw and approve SVG/PNG/favicons | Final header, favicon, brand launch |
| Domain | Confirmed | `holidaykenyasafaris.ke` | Production configuration |
| Hosting | Confirmed | cPanel account `holidayk`; document root `/home/holidayk/public_html` | Deployment |
| WordPress admin ownership | CLIENT CONFIRMATION REQUIRED | Name account owners and editorial roles | Production access |
| Meta Pixel ID | CLIENT CONFIRMATION REQUIRED | Supply ID | Meta tracking launch |
| GA4 Measurement ID | CLIENT CONFIRMATION REQUIRED | Supply ID | GA4 tracking launch |
| GTM container | CLIENT CONFIRMATION REQUIRED | Decide whether GTM is used and supply ID | Tag configuration |
| Consent/privacy approach | CLIENT CONFIRMATION REQUIRED | Approve privacy and cookie behavior | Production tracking |
| Inquiry retention and deletion | CLIENT CONFIRMATION REQUIRED | Approve retention period, deletion workflow, access roles, and final public privacy wording | Production inquiry storage |

## Contact and Company Information

| Item | Current status | Rule |
|---|---|---|
| WhatsApp | Temporary: `+254 722 742 799` | Retain until client replaces it |
| Main phone | CLIENT CONFIRMATION REQUIRED | Use verified Ashford contact only if approved for this brand |
| Email | CLIENT CONFIRMATION REQUIRED | Prefer dedicated Holiday Kenya Safaris address |
| Address | CLIENT CONFIRMATION REQUIRED | Verify against current Ashford source and client approval |
| Business hours | CLIENT CONFIRMATION REQUIRED | Do not infer |
| Quote response expectation | CLIENT CONFIRMATION REQUIRED | Do not promise a response time without operations approval |
| Legal company/operator wording | CLIENT CONFIRMATION REQUIRED | Approve footer, terms, invoice, and operator language |
| Registration and tax details | CLIENT CONFIRMATION REQUIRED | Publish only when verified and appropriate |
| Memberships and licenses | CLIENT CONFIRMATION REQUIRED | Record exact source and expiry/current status |

## Commercial Policies

All are `CLIENT CONFIRMATION REQUIRED`:

- Deposit amount or percentage.
- Payment methods and official payment destination.
- Balance deadline.
- Cancellation and amendment policy.
- Refund policy.
- No-show policy.
- Child age bands and rates.
- Single supplement rules.
- Group minimum and maximum sizes.
- Resident/citizen documentation requirements.
- Travel insurance language.
- Liability and force-majeure terms.
- Price-validity period.
- Quote-validity period.

Do not fill these with generic tour-industry terms.

These are global commercial and legal decisions, not Tour-editor confirmation, assumption, or validity-date fields.

## Rates

Rates use the simplified editorial model:

- The Tour editor has one optional positive whole-number field: `From price per person (KSh)`.
- Entering a value and publishing the Tour approves `From KSh X per person` for cards, Tour pages, Campaigns, and quote context.
- Leaving it blank shows `Request current KSh rate`.
- There are no price status, source, checked-date, valid-until, season, residency, group-size, sharing, vehicle, accommodation, inclusion, supplement, adult, or child price fields.
- Campaign start and end dates do not alter Tour prices.
- The client updates or removes the Tour price manually.
- Leave the price blank for per-vehicle, per-group, child-specific, single-supplement-dependent, or highly variable products that cannot truthfully use one per-person starting figure.
- Never auto-convert or import a USD amount into the public KSh field.

## Media Rights

WordPress does not request owner, source, permission status, usage scope, license, evidence, checked date, or expiry fields. Media uploaded or deliberately assigned to published content by an authorized editor is treated as approved for website use. Use native alt text, and add a public caption only when a visible credit is required.

Imported or scraped media remains unassigned until the editor selects it. The generated Mara and Mercy images in this repository remain internal presentation references and are not approved for the live site.

## Operational Proof

Request and verify where the website needs it:

- Fleet/vehicle information and whether vehicles are owned or contracted.
- Guide profiles and credentials.
- Emergency and safety process.
- Corporate proposal, invoice, and procurement capabilities.
- School/youth supervision and first-aid practices.
- Group payment support.
- Accommodation relationships.
- Real Kenyan customer reviews.

## Resolution Format

When a remaining project-level confirmation arrives, record:

- Item.
- Approved value or wording.
- Approver.
- Date.
- Source file, email, or URL.
- Expiry or review date if applicable.
