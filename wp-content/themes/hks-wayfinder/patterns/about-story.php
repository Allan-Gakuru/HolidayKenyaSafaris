<?php
/**
 * Title: About Holiday Kenya Safaris
 * Slug: hks-wayfinder/about-story
 * Categories: text
 * Inserter: no
 *
 * Brand Script: client-supplied Google Doc, tab t.ryz9ncvyyxv7 (4 September 2026).
 * Ownership and 20+ years of experience confirmed by the client on the same date.
 *
 * @package HKS_Wayfinder
 */

defined( 'ABSPATH' ) || exit;
$tours_url = get_post_type_archive_link( 'hks_tour' ) ?: home_url( '/tours/' );
?>
<!-- wp:group {"tagName":"section","className":"hks-about-story","layout":{"type":"default"}} -->
<section class="wp-block-group hks-about-story" aria-labelledby="hks-about-story-title"><!-- wp:group {"className":"hks-shell hks-about-story__inner","layout":{"type":"default"}} -->
<div class="wp-block-group hks-shell hks-about-story__inner"><!-- wp:group {"className":"hks-about-story__opening","layout":{"type":"default"}} -->
<div class="wp-block-group hks-about-story__opening"><!-- wp:heading {"anchor":"hks-about-story-title"} -->
<h2 class="wp-block-heading" id="hks-about-story-title"><?php esc_html_e( 'A holiday worth looking forward to.', 'hks-wayfinder' ); ?></h2>
<!-- /wp:heading -->

<!-- wp:paragraph {"className":"hks-about-story__lead"} -->
<p class="hks-about-story__lead"><?php esc_html_e( 'You deserve a proper break. We help you get from “we should go somewhere” to a trip you feel ready to take.', 'hks-wayfinder' ); ?></p>
<!-- /wp:paragraph --></div>
<!-- /wp:group -->

<!-- wp:group {"className":"hks-about-story__body","layout":{"type":"default"}} -->
<div class="wp-block-group hks-about-story__body"><!-- wp:paragraph -->
<p><?php esc_html_e( 'Perhaps it’s a safari, a few days at the coast, or your first holiday abroad. You know how you want it to feel. But comparing packages, working out what the price covers and getting everyone’s dates to line up can turn the excitement into a long to-do list.', 'hks-wayfinder' ); ?></p>
<!-- /wp:paragraph -->

<!-- wp:paragraph -->
<p><?php esc_html_e( 'Holiday Kenya Safaris helps Kenyan travellers make sense of the options, with trips across Kenya and beyond. We bring the itinerary, accommodation details, inclusions and exclusions together so you can understand the trip before you commit. Where a detail still needs confirming, we’ll say so.', 'hks-wayfinder' ); ?></p>
<!-- /wp:paragraph -->

<!-- wp:paragraph -->
<p><?php esc_html_e( 'We’re owned and operated by Ashford Tours & Travel, bringing over 20 years of travel experience to your plans. You have a real travel consultant to talk to, whether you’re heading away as a couple, with family or with a whole group.', 'hks-wayfinder' ); ?></p>
<!-- /wp:paragraph -->

<!-- wp:paragraph -->
<p><?php esc_html_e( 'Start with a trip you like and share your dates, traveller count and preferences. Review your quote request, then choose WhatsApp or email to send it. Our team will help confirm the details and prepare your quote, so you can spend less time chasing answers and more time looking forward to going.', 'hks-wayfinder' ); ?></p>
<!-- /wp:paragraph -->

<!-- wp:buttons -->
<div class="wp-block-buttons"><!-- wp:button -->
<div class="wp-block-button"><a class="wp-block-button__link wp-element-button" href="<?php echo esc_url( $tours_url ); ?>"><?php esc_html_e( 'Explore our trips', 'hks-wayfinder' ); ?></a></div>
<!-- /wp:button --></div>
<!-- /wp:buttons --></div>
<!-- /wp:group --></div>
<!-- /wp:group --></section>
<!-- /wp:group -->
