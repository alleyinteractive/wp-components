<?php
/**
 * Parsely component.
 *
 * @package WP_Components
 */

namespace WP_Components\Integrations;

/**
 * Parsely.
 */
class Parsely extends Component {

	use \WP_Components\WP_Post;

	/**
	 * Unique component slug.
	 *
	 * @var string
	 */
	public $name = 'parsely';

	/**
	 * Define a default config.
	 *
	 * @return array Default config.
	 */
	public function default_config() {
		return [
			'site' => '',
		];
	}
}
