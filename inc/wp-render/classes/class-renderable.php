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
	 * Return the partial, instead of outputting it.
	 *
	 * @return string
	 */
	public function locate_component_partial() {

		// Get the namespace to build the path.
		$namespace = get_class( $this->component_instance );

		// Explode to modify individual parts.
		$directory_parts = explode( '\\', $namespace );

		// Remove project namespace.
		array_shift( $directory_parts );

		// Lowercase all parts.
		$directory_parts = array_map( 'strtolower', $directory_parts );

		// Replace underscores with dashes
		$directory_parts = array_map(
			function( $directory_part ) {
				return str_replace( '_', '-', $directory_part);
			},
			$directory_parts
		);

		$file = array_pop( $directory_parts );

		$path = get_template_directory() . '/' . implode( '/', $directory_parts ) . "/template-parts/index.php";
		if ( file_exists( $path ) ) {
			return $path;
		}
	}

	/**
	 * Resolve and render a component's CSS.
	 */
	public function render_css() {
		$name         = $this->component_instance->name;
		$default_path = WP_COMPONENTS_PHP_ASSET_PATH . '/' . $name . '.css';
		$css          = apply_filters( 'wp_render_asset_path', $default_path, $name, 'css' );

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

		if ( $name !== $javascript ) {
			printf(
				'<script src="%1$s" class="%2$s" type="text/javascript" async></script>', // phpcs:ignore WordPress.WP.EnqueuedResources.NonEnqueuedScript
				esc_url( $javascript ),
				esc_attr( $name . '-component-js' )
			);
		}
	}
}
