<?php
/**
 * Menu_Item component.
 *
 * @package WP_Components
 */

namespace WP_Components;

/**
 * Menu_Item.
 */
class Menu_Item extends Component {

	use \WP_Components\WP_Menu_Item;

	/**
	 * Unique component slug.
	 *
	 * @var string
	 */
	public $name = 'menu-item';

	/**
	 * Define a default config.
	 *
	 * @return array Default config.
	 */
	public function default_config() : array {
		return [
			'id'         => '',
			'label'      => '',
			'url'        => '',
			'theme_name' => '',
		];
	}

	/**
	 * Callback function for classes to override.
	 *
	 * @return self
	 */
	public function menu_item_has_set() : self {
		$this->set_config_from_menu_item();
		return $this;
	}
}
