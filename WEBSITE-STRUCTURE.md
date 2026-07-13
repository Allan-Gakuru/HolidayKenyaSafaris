# Website Structure and Templates

## Information Architecture

### Primary Navigation

- Home.
- Tours.
- Destinations.
- Group Travel.
- About.
- Contact.
- WhatsApp quote action.

Do not overload the main navigation with every audience or destination. Use catalogue filters, destination pages, occasion modules, and campaign links.

## Sitemap

### Home

Purpose:

- Establish local-market relevance and Wayfinder trust.
- Help visitors choose by destination, trip type, occasion, or time available.
- Feature verified priority tours.
- Explain the operator relationship.
- Move interested visitors to a relevant Tour, Campaign, or WhatsApp quote.

Suggested order:

1. Brand and literal offer.
2. Primary destination or seasonal visual.
3. Find-your-trip routes: weekend, family, couple, group, adventure.
4. Featured verified tours.
5. Why Holiday Kenya Safaris.
6. How quote-to-booking works.
7. Destination discovery.
8. Real proof when available.
9. Final WhatsApp prompt.

### Tour Catalogue

Purpose:

- Let visitors scan and compare approved products.
- Support useful filtering without creating an ecommerce feel.

Initial filters may include:

- Destination.
- Duration.
- Tour type.
- Travel style.
- Occasion.
- Price band only after enough confirmed prices exist.

Tour cards should show:

- Actual destination image.
- Tour title.
- Duration.
- Route or departure point.
- `From KSh...` plus status-aware qualifier, or `Request current rate`.
- One or two travel-style labels.
- Clear detail action.

### Destination Pages

Purpose:

- Answer destination-level questions.
- Provide SEO entry points.
- Group relevant Tours.
- Explain best times, travel style, duration, and local considerations.

Never invent weather, park-fee, or access information. Date and source any changeable facts.

### Tour Detail Page

Use the existing Maasai Mara prototype as the structural reference.

Default sequence:

1. Full-width destination hero with one strong headline.
2. Duration, nights, route/departure, price basis, and WhatsApp CTA.
3. Concise overview explaining why the trip matters.
4. Stable price panel with assumptions and current-status language.
5. Day-by-day itinerary.
6. Destination or experience story supported by an approved image.
7. Included and excluded lists.
8. Vehicle, accommodation, guide, meals, and logistics proof.
9. Suitability and important notes.
10. FAQ matched to the product.
11. How the quote process works.
12. Related Tours.
13. Final intake-to-WhatsApp CTA.

Important prototype reference:

`outputs/holiday-kenya-safari-maasai-mara.html`

Preserve from it:

- Emotional hero leading into practical detail.
- Clear facts above the fold.
- Day-by-day itinerary.
- Visible inclusions and exclusions.
- Intake form before WhatsApp.
- Mobile CTA clarity.

Replace from it:

- Singular `Holiday Kenya Safari` naming.
- Temporary red/gold/green palette.
- Temporary text-only logo.
- Unconfirmed KSh values presented without CMS status.

### Campaign Landing Page

Purpose:

- Maintain message match with a Facebook ad.
- Convert one audience and angle efficiently.
- Reuse canonical Tour facts.

Features:

- Linked Tour required.
- Full, reduced, or minimal navigation.
- Campaign-specific hero, proof order, FAQs, and CTA.
- Canonical itinerary, inclusions, exclusions, and price status inherited.
- Attribution retained through WhatsApp launch.
- Optional noindex for temporary or highly duplicative test variants.

Campaign pages are not constrained to Mercy. Add variants as evidence supports them.

### Group Travel

Provide routes for:

- Corporate and MICE.
- Chamas, churches, and SACCOs.
- Schools and youth.
- Private friend or family groups.

These pages should emphasize organizer outcomes, quoting requirements, transport, documentation, safety, payment coordination, and one accountable contact.

Do not claim capabilities or documentation that Ashford has not confirmed.

### About and Trust

Purpose:

- Explain Holiday Kenya Safaris and its relationship to Ashford Tours & Travel.
- Show operational experience and people.
- Provide verifiable contacts and trust evidence.

Potential modules, only when verified:

- Operator story.
- Team and guide profiles.
- Physical address and contacts.
- Memberships and licenses.
- Vehicle or fleet information.
- Payment and booking process.
- Real testimonials.

### Contact

- WhatsApp.
- Phone.
- Email.
- Address and map when confirmed.
- Business hours and response expectation when confirmed.
- General inquiry form only if there is a real handling process.

## Reusable Components

- Wayfinder header and footer.
- Tour card.
- Destination card.
- Price and assumptions panel.
- Itinerary timeline.
- Inclusion/exclusion lists.
- Vehicle and accommodation proof strip.
- Trust module.
- Testimonial module with verification status.
- FAQ accordion with keyboard support.
- Intake-to-WhatsApp dialog or sheet.
- Sticky mobile WhatsApp action that does not obscure content.
- Campaign attribution handler.
- Related-tour query.

## Visual Requirements

- Wayfinder palette and approved production logo.
- Real visual assets are required.
- Hero content must reveal the destination rather than show an abstract texture.
- Keep primary experiences full-width or unframed where appropriate.
- Cards use restrained radii and only for repeated objects.
- No cards nested inside cards.
- Do not scale text with viewport width.
- Do not let long tour names, KSh prices, or CTA labels overflow.
- Maintain stable dimensions for cards, price rows, icons, and form controls.

## Mobile Requirements

- Design and test for mobile first because Facebook traffic and WhatsApp conversion are mobile-heavy.
- Hero headline, facts, price, and CTA must fit without overlap.
- Intake form must support the on-screen keyboard and retain entered values.
- WhatsApp launch must not occur before validation.
- Sticky actions must respect safe areas and not cover the final page content.
- Filters should use an accessible drawer or modal rather than a compressed desktop sidebar.

## SEO Requirements

- Human-readable URLs.
- Unique titles, descriptions, H1s, and canonical behavior.
- Tour and destination internal linking.
- Breadcrumbs where useful.
- Image alt text that describes the content rather than stuffing keywords.
- Structured data only where it accurately represents the offer and organization.
- No fake review ratings.
- Campaign indexing controlled according to duplication and campaign longevity.

## Performance Requirements

- Responsive images and modern formats.
- Set image dimensions to prevent layout shift.
- Lazy-load below-the-fold media.
- Avoid autoplay video in the initial viewport unless strongly justified and tested.
- Keep JavaScript limited to interactions, form behavior, analytics, and necessary blocks.
- Use caching, compression, CDN, backups, and staging at the hosting layer.

## Accessibility Requirements

- WCAG AA color contrast.
- Keyboard-operable navigation, filters, accordions, forms, and dialogs.
- Clear focus indicators.
- Labels and errors tied to fields.
- Reduced-motion support.
- No information communicated only through color.
- Respect heading order and landmark structure.

