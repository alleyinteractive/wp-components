<?php
/**
 * Renderable class.
 *
 * @package WP_Component
 */

namespace WP_Render;

/**
 * Renderable.
 */
class Renderable {

	/**
	 * Component instance
	 *
	 * @var array
	 */
	public $component_instance;

	/**
	 * Should the partial be output (false) or returned (true).
	 *
	 * @var bool Default false.
	 */
	public $return = false;

	/**
	 * Slug of template to load
	 *
	 * @var bool Default false.
	 */
	public $template_slug = '';

	/**
	 * Class constructor.
	 *
	 * @param \Wp_Component\Component $component_instance Instance of a component (or template) to render.
	 * @param bool                    $return             Whether or not this component's markup be returned instead of printed.
	 */
	public function __construct( $component_instance, $return = false ) {
		$this->component_instance = $component_instance;
		$this->return             = $return;
	}

	/**
	 * Load the partial.
	 *
	 * @param string $template_slug Slug of template to load.
	 */
	public function render( $template_slug = 'index' ) {
		$this->template_slug = $template_slug;

		if ( $this->return ) {
			return $this->get_contents();
		} else {
			$this->require_partial();
		}
	}

	/**
	 * Return the partial, instead of outputting it.
	 *
	 * @return string
	 */
	public function get_contents() {
		ob_start();
		$this->require_partial();
		return ob_get_clean();
	}

	/**
	 * Require the partial
	 *
	 * @return void
	 */
	public function require_partial() {
		$partial = $this->locate_component_partial();
		if ( ! empty( $partial ) ) {
			require $partial;
		}
	}

	/**
	 * Get the path for a component's template part. This will assume the same
	 * folder structure as WP Components, but can be filtered to modify the
	 * logic.
	 *
	 * @return string
	 */
	public function locate_component_partial() {

		// Get the namespace to build the path.
		$namespace_parts = self::explode_namespace( get_class( $this->component_instance ) );

		// Duplicate to directory parts so we can modify those values and still
		// have access to the full path for the filter.
		$directory_parts = $namespace_parts;

		// Remove the first namespace value.
		array_shift( $directory_parts );

		// This is a WP Component, so modify the path a bit.
		if ( 'wp-components' === $namespace_parts[0] ) {
			array_unshift( $directory_parts, 'components' );
		}

		// Use default structure.
		$path = get_template_directory() . '/' . implode( '/', $directory_parts ) . '/template-parts/index.php';

		/**
		 * Modify the path to the component template part.
		 *
		 * @param string $path               Default path.
		 * @param array  $namespace_parts    The exploded namespace ready for
		 *                                   conversion to a filepath.
		 * @param object $component_instance The component object.
		 */
		$path = apply_filters( 'wp_render_component_template_part_path', $path, $namespace_parts, $this->component_instance );
		if ( file_exists( $path ) ) {
			return $path;
		}
	}

	/**
	 * Return the namespace as an array of parts that can be used to build a
	 * filepath.
	 *
	 * @param string $namespace Class namespace.
	 * @return array
	 */
	public static function explode_namespace( string $namespace ) {

		// Explode to modify individual parts.
		$namespace_parts = explode( '\\', $namespace );

		// Lowercase all parts.
		$namespace_parts = array_map( 'strtolower', $namespace_parts );

		// Replace underscores with dashes.
		$namespace_parts = array_map(
			function( $namespace_part ) {
				return str_replace( '_', '-', $namespace_part );
			},
			$namespace_parts
		);

		return $namespace_parts;
	}

	/**
	 * Resolve and render a component's CSS.
	 */
	public function render_css() {
		$name         = $this->component_instance->name;
		$default_path = WP_COMPONENTS_PHP_ASSET_PATH . '/' . $name . '.css';
		$css          = apply_filters( 'wp_render_asset_path', $default_path, $name, 'css' );

		// Only enqueue the CSS if the file exists.
		// @todo ensure this file only loads once for all components rendered.
		if ( ! file_exists( $css ) ) {
			return;
		}

		if ( $name !== $css ) {
			printf(
				'<link rel="stylesheet" href="%1$s" class="%2$s" />', // phpcs:ignore WordPress.WP.EnqueuedResources.NonEnqueuedStylesheet
				esc_url( $css ),
				esc_attr( $name . '-component-css' )
			);
		}
	}

	/**
	 * Resolve and render a component's JS.
	 */
	public function render_js() {
		$name         = $this->component_instance->name;
		$default_path = WP_COMPONENTS_PHP_ASSET_PATH . '/' . $name . '.js';
		$javascript   = apply_filters( 'wp_components_php_resolve_asset', $default_path, $name, 'js' );

		// Only enqueue the JS if the file exists.
		// @todo ensure this file only loads once for all components rendered.
		if ( ! file_exists( $javascript ) ) {
			return;
		}

		if ( $name !== $javascript ) {
			printf(
				'<script src="%1$s" class="%2$s" type="text/javascript" async></script>', // phpcs:ignore WordPress.WP.EnqueuedResources.NonEnqueuedScript
				esc_url( $javascript ),
				esc_attr( $name . '-component-js' )
			);
		}
	}
}
