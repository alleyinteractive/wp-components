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

	use Author;
	use Guest_Author;
	use WP_Post;
	use WP_Query;
	use WP_Term;
	use WP_User;

	/**
	 * Unique component slug.
	 *
	 * @var string
	 */
	public $name = 'head';

	/**
	 * Define default children.
	 *
	 * @return array Default children.
	 */
	public function default_children() : array {
		return [
			( new Component() )
				->set_name( 'title' )
				->set_children( [ get_bloginfo( 'name' ) ] ),
		];
	}

	/**
	 * Hook into post being set.
	 *
	 * @return self
	 */
	public function query_has_set() : self {

		switch ( true ) {
			case $this->query->is_search():
				$this->set_title(
					sprintf(
						/* translators: search term */
						__( 'Search results: %s', 'wp-components' ),
						$this->query->get( 's' )
					) . $this->get_trailing_title()
				);
				break;

			case $this->query->is_author():
				$this->set_author( $this->query->get( 'author_name' ) );
				$this->set_title(
					sprintf(
						/* translators: author display name */
						__( 'Articles by %s', 'wp-components' ),
						$this->get_author_display_name()
					) . $this->get_trailing_title()
				);
				break;

			case $this->query->is_category():
			case $this->query->is_tag():
			case $this->query->is_tax():
				$this->set_term( $this->query->get_queried_object() );
				$this->set_title( $this->wp_term_get_name() . $this->get_trailing_title() );
				break;

			case $this->query->is_404():
				$this->set_title( __( '404 - Page not found', 'wp-components' ) . $this->get_trailing_title() );
				break;

			case $this->query->is_post_type_archive():
				$post_type   = $this->query->get( 'post_type' );
				$post_object = get_post_type_object( $post_type );
				$this->set_title( $post_object->label . $this->get_trailing_title() );
				break;
		}

		return $this;
	}

	/**
	 * Get the trailing title.
	 *
	 * @return string
	 */
	public function get_trailing_title() {
		return ' | ' . get_bloginfo( 'name' );
	}

	/**
	 * Hook into post being set.
	 *
	 * @return self
	 */
	public function post_has_set() : self {
		$this->set_title( $this->wp_post_get_title() . $this->get_trailing_title() );
		return $this;
	}

	/**
	 * Set the title tag.
	 *
	 * @param string $value The title value.
	 * @return self
	 */
	public function set_title( $value ) : self {

		// Loop through children and update the title component (which should
		// exist since it's a default child).
		foreach ( $this->children as &$child ) {
			if ( 'title' === $child->name ) {
				$child->children[0] = html_entity_decode( $value );
			}
		}

		return $this;
	}

	/**
	 * Helper function for setting a canonical url.
	 *
	 * @param  string $url Canonical URL.
	 * @return self
	 */
	public function set_canonical_url( $url ) : self {
		return $this->add_link( 'canonical', $url );
	}

	/**
	 * Helper function for adding a new meta tag.
	 *
	 * @param string $property Property value.
	 * @param string $content  Content value.
	 * @return self
	 */
	public function add_meta( $property, $content ) : self {
		return $this->add_tag(
			'meta',
			[
				'property' => $property,
				'content'  => html_entity_decode( $content ),
			]
		);
	}

	/**
	 * Helper function for adding a new link tag.
	 *
	 * @param string $rel  Rel value.
	 * @param string $href Href value.
	 * @return self
	 */
	public function add_link( $rel, $href ) {
		return $this->add_tag(
			'link',
			[
				'rel'  => $rel,
				'href' => $href,
			]
		);
	}

	/**
	 * Helper function for add a new script tag.
	 *
	 * @param string $src Script tag src url.
	 * @param bool   $defer If script should defer loading until DOMContentLoaded.
	 * @param bool   $async If script should load asynchronous.
	 * @return self
	 */
	public function add_script( $src, $defer = true, $async = true ) : self {
		return $this->add_tag(
			'script',
			[
				'src'   => $src,
				'defer' => $defer,
				'async' => $async,
			]
		);
	}

	/**
	 * Helper function to quickly add a new tag.
	 *
	 * @param string $tag        Tag value.
	 * @param array  $attributes Tag attributes.
	 * @return self
	 */
	public function add_tag( $tag, $attributes = [] ) : self {

		$component = new Component();
		$component->set_name( $tag );
		$component->merge_config( $attributes );

		// Append this tag as a child component.
		return $this->append_child( $component );
	}

}
