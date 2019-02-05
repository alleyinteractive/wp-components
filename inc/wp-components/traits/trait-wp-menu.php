<?php
/**
 * WP_Menu trait.
 *
 * @package WP_Components
 */

namespace WP_Components;

/**
 * WP_Menu trait.
 */
trait WP_Menu {

	/**
	 * Menu object.
	 *
	 * @var null|Object
	 */
	public $menu = null;

	/**
	 * Set the menu object.
	 *
	 * @param mixed $menu Term object or menu location.
	 */
	public function set_menu( $menu = null ) {
		// Menu location was passed.
		if ( is_string( $menu ) ) {
			// Get menu locations.
			$locations = get_nav_menu_locations();

			// Get object id by location.
			$menu_term = wp_get_nav_menu_object( $locations[ $menu ] ?? null );

			$this->set_menu( $menu_term );

			return $this;
		}

		// Use global $post.
		if ( $menu instanceof \WP_Term ) {
			// Get the menu title.
			$this->menu = $menu;
			$this->menu_has_set();
			return $this;
		}

		// Something else went wrong.
		// @todo determine how to handle error messages.
		return $this;
	}

	/**
	 * Callback function for classes to override.
	 */
	public function menu_has_set() {
		// Silence is golden.
	}

	/**
	 * Build a menu component by parsing a menu.
	 *
	 * @return Menu An instance of the Menu class.
	 */
	public function parse_wp_menu() {
		if ( empty( $this->menu ) || ! $this->menu instanceof \WP_Term ) {
			return;
		}

		$menu_items = wp_get_nav_menu_items( $this->menu );

		$this->build_menu( $this, $menu_items );

		return $this;
	}

	/**
	 * Recursive function to build a complete menu with children menu items.
	 *
	 * @param  Menu    $menu Instance of menu class.
	 * @param  array   $menu_items Menu items.
	 * @param  integer $parent_id  Parent menu ID.
	 * @return array All menu items.
	 */
	public function build_menu( $menu, $menu_items, $parent_id = 0 ) {
		// Loop through all menu items.
		foreach ( (array) $menu_items as $key => $menu_item ) {

			// Current menu's id.
			$menu_item_id = $menu_item->ID;

			// Current menu's parent id.
			$menu_item_parent_id = absint( $menu_item->menu_item_parent );

			// Is the current menu item a child of the parent item.
			if ( $menu_item_parent_id === $parent_id ) {
				// Get menu_item_class config and fall back to WP Components menu item.
				$menu_item_class = $this->get_config( 'menu_item_class' ) ?? '\WP_Components\Component\Menu_Item';

				if ( ! class_exists( $menu_item_class ) ) {
					return;
				}

				// Get parsed menu item.
				$clean_menu_item = ( new $menu_item_class() )->set_menu_item( $menu_item );

				// Remove from loop.
				unset( $menu_items[ $key ] );

				// Normalize parent IDs for comparison.
				$parent_ids = array_map( 'absint', wp_list_pluck( $menu_items, 'menu_item_parent' ) );

				if ( in_array( $menu_item_id, $parent_ids, true ) ) {
					// Get menu class.
					$menu_class = get_class( $this );

					// Recursively build children menu items.
					$clean_menu_item->children[] = $this->build_menu(
						( new $menu_class() )->merge_config(
							[
								'type'     => 'submenu',
								'title'    => $menu_item->title,
							]
						),
						$menu_items,
						$menu_item_id
					);
				}

				$menu->children[] = $clean_menu_item;
			}
		}

		return (array) $menu;
	}
}
