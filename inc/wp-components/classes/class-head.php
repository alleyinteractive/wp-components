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
	public function add_meta( string $property, string $content ) : self {
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

	/**
	 * Hook into post being set.
	 *
	 * @return self
	 */
	public function query_has_set() : self {
		$this->set_title( $this->get_the_head_title() . $this->get_trailing_title() );
		$this->set_additional_meta_tags();
		return $this;
	}

	/**
	 * Hook into post being set.
	 *
	 * @return self
	 */
	public function post_has_set() : self {

		$this->set_title( $this->get_meta_title() . $this->get_trailing_title() );
		$this->set_additional_meta_tags();
		$this->set_standard_meta();
		$this->set_open_graph_meta();

		return $this;
	}

	/**
	 * Get the head title based on the query set.
	 *
	 * @return string
	 */
	public function get_the_head_title() : string {
		switch ( true ) {
			// Search results.
			case $this->query->is_search():
				return sprintf(
					/* translators: search term */
					__( 'Search results: %s', 'wp-components' ),
					$this->query->get( 's' )
				);

			// Author archive.
			case $this->query->is_author():
				$this->set_author( $this->query->get( 'author_name' ) );
				return sprintf(
					/* translators: author display name */
					__( 'Articles by %s', 'wp-components' ),
					$this->get_author_display_name()
				);

			// Term archives.
			case $this->query->is_category():
			case $this->query->is_tag():
			case $this->query->is_tax():
				$this->set_term( $this->query->get_queried_object() );
				return $this->wp_term_get_name();

			// Generic 404.
			case $this->query->is_404():
				return __( '404 - Page not found', 'wp-components' );

			// Post type archives.
			case $this->query->is_post_type_archive():
				$post_type   = $this->query->get( 'post_type' );
				$post_object = get_post_type_object( $post_type );
				return $post_object->label;
		}

		return get_bloginfo( 'name' );
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
	 * Apply default, additional, meta tags.
	 */
	public function set_additional_meta_tags() {

		/**
		 * Use this filter to add additional meta tags.
		 *
		 * @param array $codes Array of name and content codes.
		 */
		$tags = apply_filters( 'wp_components_head_additional_meta_tags', [] );

		if ( ! empty( $tags ) && is_array( $tags ) ) {
			foreach ( $tags as $name => $content ) {
				if ( ! empty( $content ) ) {
					$this->add_tag(
						'meta',
						[
							'name'    => $name,
							'content' => $content,
						]
					);
				}
			}
		}
	}

	/**
	 * Apply basic meta tags.
	 */
	public function set_standard_meta() {

		// Meta description.
		$meta_description = $this->get_meta_description();
		if ( ! empty( $meta_description ) ) {
			$this->add_tag(
				'meta',
				[
					'name'    => 'description',
					'content' => esc_attr( $meta_description ),
				]
			);
		}

		// Filter the meta key where this is stored.
		$meta_key      = apply_filters( 'wp_components_head_meta_keywords_key', '_meta_keywords' );
		$meta_keywords = apply_filters(
			'wp_components_head_meta_keywords',
			explode( ',', get_post_meta( $this->post->ID, $meta_key, true ) ),
			$this->post
		);

		if ( ! empty( $meta_keywords ) ) {
			$this->add_tag(
				'meta',
				[
					'name'    => 'keywords',
					'content' => esc_attr( implode( ',', array_filter( $meta_keywords ) ) ),
				]
			);
		}

		// Canoncial url.
		$meta_key = apply_filters( 'wp_components_head_canonical_url_key', '_canonical_url' );

		$canonical_url = (string) get_post_meta( $this->post->ID, $meta_key, true );
		if ( ! empty( $canonical_url ) ) {
			$this->set_canonical_url( $canonical_url );
		}

		// Deindex URL.
		$meta_key = apply_filters( 'wp_components_head_deindex_url_key', '_deindex_google' );

		if ( absint( get_post_meta( $this->post->ID, $meta_key, true ) ) ) {
			$this->add_tag(
				'meta',
				[
					'name'    => 'robots',
					'content' => 'noindex',
				]
			);
		}
	}

	/**
	 * Add basic open graph tags.
	 */
	public function set_open_graph_meta() {

		// Define values that are used multiple times.
		$description  = $this->get_social_description();
		$image_source = $this->get_image_source();
		$image_url    = '';
		$permalink    = $this->wp_post_get_permalink();
		$title        = $this->get_social_title();

		// Open graph meta.
		$this->add_meta( 'og:url', $permalink );
		$this->add_meta( 'og:type', 'article' );
		$this->add_meta( 'og:title', $title );
		$this->add_meta( 'og:description', $description );
		$this->add_meta( 'og:site_name', get_bloginfo( 'name' ) );

		// Images.
		if ( ! empty( $image_source ) ) {
			$image_url = $image_source[0];
			$this->add_meta( 'og:image', $image_source[0] );
			$this->add_meta( 'og:width', $image_source[1] );
			$this->add_meta( 'og:height', $image_source[2] );
		}

		// Property specific meta.
		$twitter_meta = [
			'twitter:card'          => 'summary_large_image',
			'twitter:title'         => $title,
			'twitter:description'   => $description,
			'twitter:image'         => $image_url,
			'twitter:url'           => $permalink,
		];

		// Twitter account.
		$twitter_account = apply_filters( 'wp_components_head_twitter_account', '' );
		if ( ! empty( $twitter_account ) ) {
			$twitter_meta['twitter:site'] = '@' . str_replace( '@', '', $twitter_account );
		}

		// Add Twitter tags.
		foreach ( $twitter_meta as $name => $content ) {
			if ( empty( $content ) ) {
				return;
			}

			$this->add_tag(
				'meta',
				[
					'name'    => $name,
					'content' => $content,
				]
			);
		}
	}

	/**
	 * Get the title used by the head tag.
	 *
	 * Priorities are,
	 *  1. Meta key `_meta_title` (key filterable).
	 *  3. Post title.
	 *
	 * @return string
	 */
	public function get_meta_title() : string {

		// Filter the meta key where this is stored.
		$meta_key = apply_filters( 'wp_components_head_meta_title_key', '_meta_title' );

		$meta_title = (string) get_post_meta( $this->post->ID, $meta_key, true );
		if ( ! empty( $meta_title ) ) {
			return $meta_title;
		}

		return $this->wp_post_get_title();
	}

	/**
	 * Get the title used by open graph tags/social.
	 *
	 * Priorities are,
	 *  1. Meta key `_social_title` (key filterable).
	 *  2. Meta key `_meta_title` (key filterable).
	 *  3. Post title.
	 *
	 * @return string
	 */
	public function get_social_title() : string {

		// Filter the meta key where this is stored.
		$meta_key = apply_filters( 'wp_components_head_social_title_key', '_social_title' );

		$social_title = get_post_meta( $this->post->ID, $meta_key, true );
		if ( ! empty( $social_title ) ) {
			return $social_title;
		}

		return $this->get_meta_title();
	}


	/**
	 * Get the meta description used by the head tag.
	 *
	 * Priorities are,
	 *  1. Meta key `_meta_description` (key filterable).
	 *  2. Post excerpt.
	 *
	 * @return string
	 */
	public function get_meta_description() : string {

		// Filter the meta key where this is stored.
		$meta_key = apply_filters( 'wp_components_head_meta_description_key', '_meta_description' );

		$meta_description = (string) get_post_meta( $this->post->ID, $meta_key, true );
		if ( ! empty( $meta_description ) ) {
			return $meta_description;
		}

		return $this->wp_post_get_excerpt();
	}

	/**
	 * Get the meta description used by open graph tags/social.
	 *
	 * Priorities are,
	 *  1. Meta key `_social_description` (key filterable).
	 *  2. Meta key `_meta_description` (key filterable).
	 *  3. Post excerpt.
	 *
	 * @return string
	 */
	public function get_social_description() : string {

		// Filter the meta key where this is stored.
		$meta_key = apply_filters( 'wp_components_head_social_description_key', '_social_description' );

		$social_description = (string) get_post_meta( $this->post->ID, $meta_key, true );
		if ( ! empty( $social_description ) ) {
			return $social_description;
		}

		return $this->get_meta_description();
	}

	/**
	 * Get image source with its info.
	 *
	 * @return array
	 */
	protected function get_image_source() : array {

		// Get image url.
		$image_id = absint( get_post_meta( $this->post->ID, '_social_image_id', true ) );
		$image_source    = wp_get_attachment_image_src( $image_id, 'full' );

		// Fallback to featured image.
		if ( empty( $image ) ) {
			$image_source = wp_get_attachment_image_src( get_post_thumbnail_id( $this->post->ID ), 'full' );
		}

		// Fallback.
		if ( empty( $image_source ) ) {
			return [];
		}

		// Remove query string from url.
		$image_source[0] = strtok( $image_source[0], '?' ) . '?resize=1200,600';
		return $image_source;
	}
}
