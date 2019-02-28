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

	use WP_Query;
	use WP_Post;

	/**
	 * Unique component slug.
	 *
	 * @var string
	 */
	public $name = 'head';

	/**
	 * Hook into post being set.
	 *
	 * @return self
	 */
	public function query_has_set() : self {
		return $this;
	}

	/**
	 * Hook into post being set.
	 *
	 * @return self
	 */
	public function post_has_set() : self {
		return $this;
	}
}
