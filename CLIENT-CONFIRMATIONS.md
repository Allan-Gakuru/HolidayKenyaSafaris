# Client Confirmations Register

Use this file for project-level decisions such as contact details, legal wording, privacy, analytics, and commercial policy. It is not a per-Tour, per-image, FAQ, Destination, price, or source approval system. In WordPress, an authorized editor's decision to publish public content and assigned media is the approval signal.

## Confirmed

| Item | Status | Decision |
|---|---|---|
| Exact name | Confirmed | Holiday Kenya Safaris |
| Market | Confirmed | Local Kenyan market |
| Operator relationship | Confirmed | Disclose operation by Ashford Tours & Travel |
| Identity direction | Confirmed | The Wayfinder |
| Main conversion | Confirmed | Saved quote inquiry and automatic email to the private configurable team-recipient list, followed by a visitor-chosen WhatsApp or email handoff |
| Product scope | Confirmed | Domestic safaris, excursions, coast, staycations, groups, relevant local special-interest products, and international holidays sourced from Ashford |
| Default exclusions | Confirmed | Visa services, standalone transfers, and products that cannot be represented accurately as Tours |
| Catalogue separation | Confirmed | Public Tour Scope taxonomy with `Kenya Tours` and `International Tours`; Destination remains the geographic taxonomy |
| Pricing presentation | Confirmed | Tours have one editable `From price per person (KSh)` field. Campaigns retain a separate optional override and may inherit the Tour amount when blank. |
| Ashford conversion rule | Confirmed | Use the source low-season per-person amount, or a clearly credible published starting amount where no seasonal table exists, the live USD/KSh rate on the import date, and always round upward to the next KSh 500; reject placeholder/deposit-like values and record conversion evidence in the repository manifest. |
| Ashford migration publication | Confirmed | The current remaining-Ashford-catalogue import may reuse Ashford images and itinerary copy and publish directly; unresolved or contradictory products remain draft. |
| Editorial approval | Confirmed | Draft means private; publishing by an authorized editor approves public copy and assigned media without additional confirmation fields |
| Content dates | Confirmed | Start and end dates exist only on Campaigns and do not automatically alter the optional Campaign price |
| Required intake fields | Confirmed | Name, phone, email, package, preferred date/month, travelers |
| Inquiry recovery storage | Confirmed | Save a private WordPress inquiry after validation, before showing the channel-neutral review step |
| Site scope | Confirmed | Home, catalogue, destinations, Tour pages, trust/about, contact, campaign template |
| CMS direction | Confirmed | WordPress with templates and structured content management |

## Required Before Production Build or Integration

| Item | Current status | Needed action | Blocking point |
|---|---|---|---|
| Production Wayfinder logo | Confirmed for website identity | Use `holiday-kenya-safaris-logo.svg` in the header/mobile drawer and export browser icons and the sharing image from that artwork. The client requested removal of the older logo and all active references on 2026-09-03. Retain the supplied approved PNG as a reference. | Header, mobile navigation, browser icons and link previews |
| Domain | Confirmed | `holidaykenyasafaris.ke` | Production configuration |
| Hosting | Confirmed | cPanel account `holidayk`; document root `/home/holidayk/public_html` | Deployment |
| WordPress admin ownership | CLIENT CONFIRMATION REQUIRED | Name account owners and editorial roles | Production access |
| Meta Pixel ID | Confirmed | `1741203640340218` (`Holiday Kenya Safaris Pixel`), verified in Meta Events Manager and the official Meta Pixel for WordPress connection on 2026-08-26 | Meta tracking launch |
| GA4 Measurement ID | CLIENT CONFIRMATION REQUIRED | Supply ID | GA4 tracking launch |
| GTM container | CLIENT CONFIRMATION REQUIRED | Decide whether GTM is used and supply ID | Tag configuration |
| Consent/privacy approach | CLIENT CONFIRMATION REQUIRED | Approve privacy and cookie behavior | Production tracking |
| Inquiry retention and deletion | CLIENT CONFIRMATION REQUIRED | Approve retention period, deletion workflow, access roles, and final public privacy wording | Production inquiry storage |

## Contact and Company Information

| Item | Current status | Rule |
|---|---|---|
| WhatsApp | Confirmed: `+254 712 965 131` | Official Holiday Kenya Safaris WhatsApp destination |
| Main phone | Confirmed: `+254 712 965 131` | Official Holiday Kenya Safaris mobile number |
| Email | Confirmed: `info@holidaykenyasafaris.ke` | Official Holiday Kenya Safaris public email |
| Instagram | Confirmed | `https://www.instagram.com/holidaykenyasafaris/` |
| Facebook | Confirmed | `https://www.facebook.com/people/Holiday-Kenya-Safaris/61591508593846/` |
| Address | Confirmed: Suite 101, Twiga Towers, Nairobi | Approved by the client in the About page rebuild task on 2026-09-04. Display through the public address setting. |
| Map | Confirmed: Twiga Towers directions and embedded Google Map | The client requested an embedded map on 2026-09-04. Retain the saved Map URL for directions and use the verified Google Maps **Share → Embed a map** URL as the default for the separate optional embed URL setting. Editors may replace or clear the embed URL in HKS Settings. |
| Business hours | Confirmed: Monday–Saturday, 8am–5pm | Approved by the client in the About page rebuild task on 2026-09-04. Display the Business hours value saved under HKS Settings. |
| About ownership and experience wording | Confirmed for the About narrative | Holiday Kenya Safaris is owned and operated by Ashford Tours & Travel, bringing 20+ years of travel experience. Approved by the client on 2026-09-04; incorporate discreetly in the narrative, without a separate ownership or experience section. This does not approve legal, invoice, or policy wording. |
| Quote response expectation | Confirmed for public reassurance wording | Tour quote panels may state `Fast Responses to all queries`. This approval does not create a fixed response-time SLA. Approved by the client in a Codex task on 2026-08-16. |
| Legal company/operator wording | CLIENT CONFIRMATION REQUIRED | Approve footer, terms, invoice, and operator language |
| Registration and tax details | CLIENT CONFIRMATION REQUIRED | Publish only when verified and appropriate |
| Memberships and licenses | CLIENT CONFIRMATION REQUIRED | Record exact source and expiry/current status |

## About Page Copy and Structure

The client approved the following direction in the About page rebuild task on 2026-09-04:

- Form the introductory narrative from the **Brand Script** section of the [client-supplied Google Doc](https://docs.google.com/document/d/1S2VXcHRJrdqRUWnoEQutxg2EzbWV6Q1OHwEW9vGpi8Y/edit?tab=t.ryz9ncvyyxv7), specifically tab `t.ryz9ncvyyxv7`.
- Include the Ashford ownership and 20+ years of travel experience within that narrative.
- The only additional page sections are **Visit Us** and **Contact Us**.
- Read public location, embedded map, directions, hours, and contact details from HKS Settings; omit optional blank values cleanly.

## Commercial Policies

All are `CLIENT CONFIRMATION REQUIRED`:

- Deposit amount or percentage.
- Payment methods and official payment destination.
- Balance deadline.
- Cancellation and amendment policy. The previous Tour quote-panel reassurance was removed when the client replaced the bullet set on 2026-08-16; detailed public terms still require confirmation before the policy route is published.
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

Rates use one editable starting-price field per Tour and an optional Campaign override:

- The Tour editor exposes one positive whole-number field: `From price per person (KSh)`.
- A populated Tour price renders on cards, archives, Destination pages, related Tours, canonical Tour pages, and Tour quote panels as `From KSh X per person`; blank or zero is omitted.
- The Campaign editor has one optional positive whole-number field: `From price per person (KSh)`.
- Entering a Campaign value overrides the linked Tour price on that Campaign.
- Leaving the Campaign field blank may display the linked Tour starting price.
- There are no price status, source, checked-date, valid-until, season, residency, group-size, sharing, vehicle, accommodation, inclusion, supplement, adult, or child price fields.
- Campaign start and end dates do not alter the price. The client updates or removes it manually.
- Leave the Campaign price blank when it is not a useful selling point or the offer cannot truthfully use one per-person starting figure.
- For the current authorized Ashford expansion, convert the source low-season per-person value, or a clearly credible published starting amount where no seasonal table exists, using the documented live rate and upward KSh 500 rounding rule, then import it into the Tour field.
- Preserve the separate Campaign field; do not copy a Tour amount into Campaign metadata merely to achieve inheritance.

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
