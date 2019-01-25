<?php
/**
 * Menu component.
 *
 * @package WP_Components
 */

namespace WP_Components;

/**
 * Menu.
 */
class Menu extends Component {

	use \WP_Components\WP_Menu;

	/**
	 * Unique component slug.
	 *
	 * @var string
	 */
	public $name = 'menu';


	/**
	 * Define a default config.
	 *
	 * @return array Default config.
	 */
	public function default_config() {
		return [
			'location'        => '',
			'title'           => '',
			'menu_item_class' => '',
		];
	}

	/**
	 * Callback function for classes to override.
	 */
	public function menu_has_set() {
		// Silence is golden.
	}
}
