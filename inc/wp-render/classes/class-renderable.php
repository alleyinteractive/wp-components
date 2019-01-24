<?php
/**
 * Renderable class.
 *
 * @package WP_Component
 */

namespace Render_WP;

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
			$this->require();
		}
	}

	/**
	 * Return the partial, instead of outputting it.
	 *
	 * @return string
	 */
	public function get_contents() {
		ob_start();
		$this->require();
		return ob_get_clean();
	}

	/**
	 * Require the partial
	 *
	 * @return void
	 */
	public function require() {
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
		$theme_components_path  = apply_filters(
			'wp_components_php_component_path',
			get_stylesheet_directory() . '/inc'
		);
		$component_partial_path = '/components/' . $this->component_instance->name . '/template-parts/' . $this->template_slug . '.php';

		if ( defined( 'WP_COMPONENTS_PATH' ) && file_exists( WP_COMPONENTS_PATH . $component_partial_path ) ) {
			return WP_COMPONENTS_PATH . $component_partial_path;
		} elseif ( file_exists( $theme_components_path . $component_partial_path ) ) {
			return $theme_components_path . $component_partial_path;
		}
	}

	/**
	 * Resolve and render a component's CSS.
	 */
	public function render_css() {
		$name         = $this->component_instance->name;
		$default_path = WP_COMPONENTS_PHP_ASSET_PATH . '/' . $name . '.css';
		$css          = apply_filters( 'wp_components_php_resolve_asset', $default_path, $name, 'css' );

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
