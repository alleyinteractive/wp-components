<?php
/**
 * Post component.
 *
 * @package WP_Components
 */

namespace WP_Components;

/**
 * Post.
 */
class Post extends Component {

	use WP_Post;

	/**
	 * Unique component slug.
	 *
	 * @var string
	 */
	public $name = 'post';
}
