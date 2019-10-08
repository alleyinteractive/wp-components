<?php
/**
 * Yoast component.
 *
 * @package WP_Components
 */

namespace WP_Components\Integrations;

/**
 * Yoast.
 */
class Yoast extends \WP_Components\Component {
	use \WP_Components\WP_Post;

	/**
	 * Unique component slug.
	 *
	 * @var string
	 */
	public $name = 'yoast';

	/**
	 * Define a default config.
	 *
	 * @return array Default config.
	 */
	public function default_config() : array {
		return [
			'title'              => '',
			'social_title'       => '',
			'description'        => '',
			'social_description' => '',
			'social_image'       => '',
			'deindex_google'     => false,
		];
	}

	/**
	 * Attach hooks to modify Head content.
	 */
	protected function add_hooks() {
		add_filter(
			'wp_components_head_meta_title',
			function( string $title ) {
				return $this->get_config( 'title' ) ?: $title;
			}
		);

		add_filter(
			'wp_components_head_social_title',
			function( string $social_title ) {
				return $this->get_config( 'social_title' ) ?: $social_title;
			}
		);

		add_filter(
			'wp_components_head_meta_description',
			function( string $description ) {
				return $this->get_config( 'description' ) ?: $description;
			}
		);

		add_filter(
			'wp_components_head_social_description',
			function( string $social_description ) {
				return $this->get_config( 'social_description' ) ?: $social_description;
			}
		);

		add_filter(
			'wp_components_head_image_id',
			function( int $social_image ) {
				return $this->get_config( 'social_image' ) ?: $social_image;
			}
		);

		add_filter(
			'wp_components_head_deindex_url',
			function( bool $deindex_url ) {
				$deindex = $this->get_config( 'deindex_google' );
				return $deindex ? ( strpos( $deindex, 'noindex' ) !== false ) : $deindex_url;
			}
		);
	}

	/**
	 * Hook into post being set.
	 *
	 * @return self
	 */
	public function post_has_set() : parent {
		// Return if Yoast is not active.
		if ( ! class_exists( '\WPSEO_Frontend' ) ) {
			return $this;
		}

		// Workaround for is_singular() not being set.
		global $wp_query;
		$wp_query->is_singular = true;

		// Get the Yoast front-end instance.
		$wp_seo_front_end = \WPSEO_Frontend::get_instance();

		// And get or create the Yoast OG instance.
		global $wpseo_og;

		// Use the global if available.
		if ( empty( $wpseo_og ) ) {
			// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
			$wpseo_og = new \WPSEO_OpenGraph();
		}

		$this->add_hooks();

		// Update the config with actual values.
		return $this->merge_config(
			[
				'title'              => $wp_seo_front_end->title( $this->wp_post_get_title() ),
				'social_title'       => $wpseo_og->og_title( false ),
				'description'        => $wp_seo_front_end->metadesc( false ),
				'social_description' => $wpseo_og->description( false ),
				'social_image'       => \WPSEO_Meta::get_value( 'opengraph-image-id', $this->get_post_id() ),
				'deindex_google'     => $wp_seo_front_end->get_robots(),
			]
		);
	}
}
