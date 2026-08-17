<?php
/**
 * Native WordPress menu locations rendered in the Wayfinder navigation shell.
 *
 * @package HKS_Wayfinder
 */

namespace HKS_Wayfinder;

defined( 'ABSPATH' ) || exit;

/**
 * Shared escaping and attribute helpers for the public menu walkers.
 */
abstract class Menu_Walker extends \Walker_Nav_Menu {

	/**
	 * Return filtered menu-item classes.
	 *
	 * @param object $item  Menu item.
	 * @param object $args  Menu arguments.
	 * @param int    $depth Current depth.
	 * @return string[]
	 */
	protected function item_classes( object $item, object $args, int $depth ): array {
		$classes   = array_filter( (array) ( $item->classes ?? array() ) );
		$classes[] = 'menu-item-' . (int) $item->ID;
		$classes   = apply_filters( 'nav_menu_css_class', $classes, $item, $args, $depth );

		return array_values(
			array_filter(
				array_map( 'sanitize_html_class', is_array( $classes ) ? $classes : array() )
			)
		);
	}

	/**
	 * Return a filtered, plain-text item title.
	 *
	 * @param object $item  Menu item.
	 * @param object $args  Menu arguments.
	 * @param int    $depth Current depth.
	 * @return string
	 */
	protected function item_title( object $item, object $args, int $depth ): string {
		$title = apply_filters( 'the_title', (string) $item->title, (int) $item->ID );
		$title = apply_filters( 'nav_menu_item_title', $title, $item, $args, $depth );

		return wp_strip_all_tags( (string) $title );
	}

	/**
	 * Build safe link attributes using WordPress's standard filter contract.
	 *
	 * @param object $item       Menu item.
	 * @param object $args       Menu arguments.
	 * @param int    $depth      Current depth.
	 * @param string $class_name Optional link class.
	 * @return string
	 */
	protected function link_attributes( object $item, object $args, int $depth, string $class_name = '' ): string {
		$rel = trim( (string) ( $item->xfn ?? '' ) );

		if ( '_blank' === ( $item->target ?? '' ) ) {
			$rel = trim( $rel . ' noopener noreferrer' );
		}

		$attributes = array(
			'class'        => $class_name,
			'href'         => (string) ( $item->url ?? '' ),
			'target'       => (string) ( $item->target ?? '' ),
			'rel'          => implode( ' ', array_unique( array_filter( preg_split( '/\s+/', $rel ) ?: array() ) ) ),
			'aria-current' => ! empty( $item->current ) ? 'page' : '',
		);
		$attributes = apply_filters( 'nav_menu_link_attributes', $attributes, $item, $args, $depth );

		$output = '';
		foreach ( is_array( $attributes ) ? $attributes : array() as $attribute => $value ) {
			if ( '' === (string) $value ) {
				continue;
			}

			$output .= ' ' . esc_attr( (string) $attribute ) . '="' . esc_attr( (string) $value ) . '"';
		}

		return $output;
	}

	/**
	 * Check whether a parent item has a useful destination of its own.
	 *
	 * @param object $item Menu item.
	 * @return bool
	 */
	protected function has_parent_link( object $item ): bool {
		$url = trim( (string) ( $item->url ?? '' ) );

		return '' !== $url && '#' !== $url;
	}

	/**
	 * Check whether an item owns a submenu.
	 *
	 * @param object $item Menu item.
	 * @return bool
	 */
	protected function has_children( object $item ): bool {
		return in_array( 'menu-item-has-children', (array) ( $item->classes ?? array() ), true );
	}
}

/**
 * Render a two-level desktop menu using the existing details-based dropdowns.
 */
final class Desktop_Menu_Walker extends Menu_Walker {

	/**
	 * Start a submenu list.
	 *
	 * @param string $output Menu output.
	 * @param int    $depth  Current depth.
	 * @param object $args   Menu arguments.
	 * @return void
	 */
	public function start_lvl( &$output, $depth = 0, $args = null ): void {
		$class = 0 === (int) $depth ? 'hks-nav-menu__list' : 'sub-menu';
		$output .= '<ul class="' . esc_attr( $class ) . '">';
	}

	/**
	 * End a submenu list.
	 *
	 * @param string $output Menu output.
	 * @param int    $depth  Current depth.
	 * @param object $args   Menu arguments.
	 * @return void
	 */
	public function end_lvl( &$output, $depth = 0, $args = null ): void {
		$output .= '</ul>';
	}

	/**
	 * Start one menu item.
	 *
	 * @param string $output            Menu output.
	 * @param object $data_object       Menu item.
	 * @param int    $depth             Current depth.
	 * @param object $args              Menu arguments.
	 * @param int    $current_object_id Current object ID.
	 * @return void
	 */
	public function start_el( &$output, $data_object, $depth = 0, $args = null, $current_object_id = 0 ): void {
		$item     = $data_object;
		$classes  = $this->item_classes( $item, $args, (int) $depth );
		$title    = $this->item_title( $item, $args, (int) $depth );
		$class    = implode( ' ', $classes );
		$children = $this->has_children( $item );

		if ( 0 === (int) $depth ) {
			if ( $children ) {
				$output .= '<details class="hks-nav-menu ' . esc_attr( $class ) . '" data-hks-nav-menu><summary>' . esc_html( $title ) . '</summary><div class="hks-nav-menu__panel">';

				$description = trim( wp_strip_all_tags( (string) ( $item->description ?? '' ) ) );
				if ( '' !== $description ) {
					$output .= '<p>' . esc_html( $description ) . '</p>';
				}
			} else {
				$output .= '<a' . $this->link_attributes( $item, $args, (int) $depth, $class ) . '>' . esc_html( $title ) . '</a>';
			}

			return;
		}

		$output .= '<li class="' . esc_attr( $class ) . '"><a' . $this->link_attributes( $item, $args, (int) $depth ) . '>' . esc_html( $title ) . '</a>';
	}

	/**
	 * End one menu item.
	 *
	 * @param string $output      Menu output.
	 * @param object $data_object Menu item.
	 * @param int    $depth       Current depth.
	 * @param object $args        Menu arguments.
	 * @return void
	 */
	public function end_el( &$output, $data_object, $depth = 0, $args = null ): void {
		$item = $data_object;

		if ( 0 === (int) $depth ) {
			if ( $this->has_children( $item ) ) {
				if ( $this->has_parent_link( $item ) ) {
					$title   = $this->item_title( $item, $args, (int) $depth );
					$output .= '<a' . $this->link_attributes( $item, $args, (int) $depth, 'hks-nav-menu__all' ) . '>' . esc_html( sprintf( __( 'View %s', 'hks-wayfinder' ), $title ) ) . '<span aria-hidden="true">&rarr;</span></a>';
				}

				$output .= '</div></details>';
			}

			return;
		}

		$output .= '</li>';
	}
}

/**
 * Render the same managed hierarchy in the mobile drawer.
 */
final class Mobile_Menu_Walker extends Menu_Walker {

	/**
	 * Start a submenu list.
	 *
	 * @param string $output Menu output.
	 * @param int    $depth  Current depth.
	 * @param object $args   Menu arguments.
	 * @return void
	 */
	public function start_lvl( &$output, $depth = 0, $args = null ): void {
		$output .= 0 === (int) $depth ? '<ul>' : '<ul class="sub-menu">';
	}

	/**
	 * End a submenu list.
	 *
	 * @param string $output Menu output.
	 * @param int    $depth  Current depth.
	 * @param object $args   Menu arguments.
	 * @return void
	 */
	public function end_lvl( &$output, $depth = 0, $args = null ): void {
		$output .= '</ul>';
	}

	/**
	 * Start one mobile menu item.
	 *
	 * @param string $output            Menu output.
	 * @param object $data_object       Menu item.
	 * @param int    $depth             Current depth.
	 * @param object $args              Menu arguments.
	 * @param int    $current_object_id Current object ID.
	 * @return void
	 */
	public function start_el( &$output, $data_object, $depth = 0, $args = null, $current_object_id = 0 ): void {
		$item     = $data_object;
		$classes  = $this->item_classes( $item, $args, (int) $depth );
		$title    = $this->item_title( $item, $args, (int) $depth );
		$class    = implode( ' ', $classes );
		$children = $this->has_children( $item );

		if ( 0 === (int) $depth ) {
			if ( $children ) {
				$output .= '<details class="' . esc_attr( $class ) . '"><summary>' . esc_html( $title ) . '</summary>';

				if ( $this->has_parent_link( $item ) ) {
					$output .= '<a' . $this->link_attributes( $item, $args, (int) $depth, 'hks-mobile-menu__parent-link' ) . '>' . esc_html( sprintf( __( 'View %s', 'hks-wayfinder' ), $title ) ) . '<span aria-hidden="true">&rarr;</span></a>';
				}
			} else {
				$output .= '<a' . $this->link_attributes( $item, $args, (int) $depth, $class ) . '>' . esc_html( $title ) . '<span aria-hidden="true">&rarr;</span></a>';
			}

			return;
		}

		$output .= '<li class="' . esc_attr( $class ) . '"><a' . $this->link_attributes( $item, $args, (int) $depth ) . '>' . esc_html( $title ) . '<span aria-hidden="true">&rarr;</span></a>';
	}

	/**
	 * End one mobile menu item.
	 *
	 * @param string $output      Menu output.
	 * @param object $data_object Menu item.
	 * @param int    $depth       Current depth.
	 * @param object $args        Menu arguments.
	 * @return void
	 */
	public function end_el( &$output, $data_object, $depth = 0, $args = null ): void {
		if ( 0 === (int) $depth ) {
			if ( $this->has_children( $data_object ) ) {
				$output .= '</details>';
			}

			return;
		}

		$output .= '</li>';
	}
}

/**
 * Register and render editable WordPress menu locations.
 */
final class NavMenus {

	public const PRIMARY_LOCATION = 'hks_primary';
	public const FOOTER_LOCATION  = 'hks_footer';

	/**
	 * Register dashboard-managed menu locations.
	 *
	 * @return void
	 */
	public static function register(): void {
		register_nav_menus(
			array(
				self::PRIMARY_LOCATION => __( 'Primary header and mobile menu', 'hks-wayfinder' ),
				self::FOOTER_LOCATION  => __( 'Footer menu', 'hks-wayfinder' ),
			)
		);
	}

	/**
	 * Keep the native Menus screen easy to find in a block theme dashboard.
	 *
	 * @return void
	 */
	public static function register_admin_page(): void {
		if ( ! current_user_can( 'edit_theme_options' ) ) {
			return;
		}

		global $submenu;
		foreach ( $submenu['themes.php'] ?? array() as $item ) {
			if ( isset( $item[2] ) && 'nav-menus.php' === $item[2] ) {
				return;
			}
		}

		add_submenu_page(
			'themes.php',
			__( 'Site Menus', 'hks-wayfinder' ),
			__( 'Site Menus', 'hks-wayfinder' ),
			'edit_theme_options',
			'nav-menus.php'
		);
	}

	/**
	 * Check whether the editor has assigned a primary menu.
	 *
	 * @return bool
	 */
	public static function has_primary_menu(): bool {
		return self::location_has_items( self::PRIMARY_LOCATION );
	}

	/**
	 * Render the managed desktop navigation items.
	 *
	 * @return string
	 */
	public static function render_desktop(): string {
		return (string) wp_nav_menu(
			array(
				'theme_location' => self::PRIMARY_LOCATION,
				'container'      => false,
				'fallback_cb'    => false,
				'items_wrap'     => '%3$s',
				'depth'          => 2,
				'echo'           => false,
				'item_spacing'   => 'discard',
				'walker'         => new Desktop_Menu_Walker(),
			)
		);
	}

	/**
	 * Render the managed mobile navigation items.
	 *
	 * @return string
	 */
	public static function render_mobile(): string {
		return (string) wp_nav_menu(
			array(
				'theme_location' => self::PRIMARY_LOCATION,
				'container'      => false,
				'fallback_cb'    => false,
				'items_wrap'     => '%3$s',
				'depth'          => 2,
				'echo'           => false,
				'item_spacing'   => 'discard',
				'walker'         => new Mobile_Menu_Walker(),
			)
		);
	}

	/**
	 * Render the managed footer menu or its safe existing fallback.
	 *
	 * @return string
	 */
	public static function render_footer(): string {
		if ( self::location_has_items( self::FOOTER_LOCATION ) ) {
			$menu = wp_nav_menu(
				array(
					'theme_location' => self::FOOTER_LOCATION,
					'container'      => false,
					'fallback_cb'    => false,
					'menu_class'     => 'hks-footer-menu',
					'menu_id'        => 'hks-footer-menu',
					'depth'          => 1,
					'echo'           => false,
					'item_spacing'   => 'discard',
				)
			);

			return '<nav aria-label="' . esc_attr__( 'Footer navigation', 'hks-wayfinder' ) . '">' . $menu . '</nav>';
		}

		return sprintf(
			'<nav aria-label="%1$s"><a href="%2$s">%3$s</a><a href="%4$s">%5$s</a><a href="%6$s">%7$s</a><a href="%8$s">%9$s</a><a href="%10$s">%11$s</a></nav>',
			esc_attr__( 'Footer navigation', 'hks-wayfinder' ),
			esc_url( home_url( '/' ) ),
			esc_html__( 'Home', 'hks-wayfinder' ),
			esc_url( get_post_type_archive_link( 'hks_tour' ) ?: home_url( '/tours/' ) ),
			esc_html__( 'All tours', 'hks-wayfinder' ),
			esc_url( home_url( '/#destinations' ) ),
			esc_html__( 'Destinations', 'hks-wayfinder' ),
			esc_url( home_url( '/travel-guides/' ) ),
			esc_html__( 'Travel Guides', 'hks-wayfinder' ),
			esc_url( home_url( '/group-travel/' ) ),
			esc_html__( 'Group travel', 'hks-wayfinder' )
		);
	}

	/**
	 * Ensure an assigned location contains at least one public menu item.
	 *
	 * @param string $location Registered location slug.
	 * @return bool
	 */
	private static function location_has_items( string $location ): bool {
		if ( ! has_nav_menu( $location ) ) {
			return false;
		}

		$locations = get_nav_menu_locations();
		$menu_id   = isset( $locations[ $location ] ) ? (int) $locations[ $location ] : 0;
		$items     = $menu_id > 0 ? wp_get_nav_menu_items( $menu_id ) : false;

		return is_array( $items ) && array() !== $items;
	}
}
