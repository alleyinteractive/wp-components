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
	public function default_config() : array {
		return [
			'location'        => '',
			'title'           => '',
			'title_link'      => '',
			'display_title'   => false,
			'menu_item_class' => '',
			'theme_name'      => '',
		];
	}
}
