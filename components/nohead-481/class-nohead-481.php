<?php
/**
 * Nohead_481 component.
 *
 * @package WP_Component
 */

namespace WP_Component;

/**
 * Nohead_481.
 */
class Nohead_481 extends Component {

	/**
	 * Unique component slug.
	 *
	 * @var string
	 */
	public $name = 'nohead-481';

	public function default_config() {
		return [
			'test' => 'Ornare interdum imperdiet est urna',
		];
	}
}
