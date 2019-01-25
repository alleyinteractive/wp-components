<?php
/**
 * Head component.
 *
 * @package WP_Components
 */

namespace WP_Components;

/**
 * Head.
 */
class Head extends Component {

	/**
	 * Unique component slug.
	 *
	 * @var string
	 */
	public $name = 'head';

	/**
	 * Define the default config of a head.
	 *
	 * @return array A default config.
	 */
	public function default_config() {
		return [
			'name' => '',
		];
	}
}
