<?php
/**
 * Social Sharing component.
 *
 * @package WP_Components
 */

namespace WP_Components;

/**
 * Social Sharing.
 */
class Social_Sharing extends Component {

	use WP_Post;

	/**
	 * Unique component slug.
	 *
	 * @var string
	 */
	public $name = 'social-sharing';

	/**
	 * Hook into post being set.
	 */
	public function post_has_set() {
	}
}
