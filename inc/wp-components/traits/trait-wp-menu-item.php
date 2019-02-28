<?php
/**
 * WP_Menu_Item trait.
 *
 * @package WP_Components
 */

namespace WP_Components;

/**
 * WP_Menu_Item trait.
 */
trait WP_Menu_Item {

	/**
	 * Menu Item object.
	 *
	 * @var null|Object
	 */
	public $menu_item = null;

	/**
	 * Set the menu item object.
	 *
	 * @param mixed $menu_item Post object or menu item ID.
	 * @return object Instance of the class this trait is implemented on.
	 */
	public function set_menu_item( $menu_item = null ) : self {
		// Post was passed.
		if ( $menu_item instanceof \WP_Post && 'nav_menu_item' === $menu_item->post_type ) {
			$this->menu_item = $menu_item;
			$this->menu_item_has_set();

			return $this;
		}

		// ID passed in.
		if ( 0 !== absint( $menu_item ) ) {
			$this->set_menu_item( get_post( $menu_item ) );
			return $this;
		}

		// Something else went wrong.
		// @todo determine how to handle error messages.
		return $this;
	}

	/**
	 * Callback function for classes to override.
	 *
	 * @return object Instance of the class this trait is implemented on.
	 */
	public function menu_item_has_set() : self {
		return $this;
	}

	/**
	 * Parse a menu post.
	 *
	 * @return object Instance of the class this trait is implemented on.
	 */
	public function set_config_from_menu_item() : self {
		if ( empty( $this->menu_item ) ) {
			return;
		}

		// Determine label based on type.
		$label = ( 'custom' === $this->menu_item->type ) ? $this->menu_item->post_title : $this->menu_item->title;

		// Default fields.
		return $this->merge_config(
			[
				'id'    => $this->menu_item->ID,
				'label' => $label,
				'url'   => $this->menu_item->url,
			]
		);
	}
}
