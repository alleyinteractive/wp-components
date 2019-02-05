<?php
/**
 * Autoloaders.
 *
 * @package WP_Components
 */

namespace WP_Components;

/**
 * Autoloader for WP_Components.
 */
spl_autoload_register(
	function( $object ) {

		// Trim leading dashes.
		$object = ltrim( $object, '\\' );

		// Is this under the WP_Components namespace?
		if ( false !== strpos( $object, 'WP_Components' ) ) {
			/**
			 * Strip the namespace, replace underscores with dashes, and lowercase.
			 *
			 * `\WP_Components\Body`
			 * becomes
			 * `body`
			 */
			$object = strtolower(
				str_replace(
					[ 'WP_Components\\', '_' ],
					[ '', '-' ],
					$object
				)
			);

			$dirs   = explode( '\\', $object );
			$object = array_pop( $dirs );

			// Check if this is a class.
			$object_path  = WP_COMPONENTS_PATH . rtrim( '/inc/' . implode( '/', $dirs ), '/' ) . "/wp-components/classes/class-{$object}.php";
			if ( file_exists( $object_path ) ) {
				require_once $object_path;
			}

			// Check if this is a trait.
			$trait_path  = WP_COMPONENTS_PATH . rtrim( '/inc/' . implode( '/', $dirs ), '/' ) . "/wp-components/traits/trait-{$object}.php";
			if ( file_exists( $trait_path ) ) {
				require_once $trait_path;
			}
		}
	}
);
/**
 * Autoloader for components in a theme.
 */
spl_autoload_register(
	function( $class ) {

		// Filter to define the namespace.
		$theme_component_namespace = apply_filters( 'wp_components_theme_components_namespace', '' );
		if ( empty( $theme_component_namespace ) ) {
			return;
		}

		// Trim leading dashes.
		$class = ltrim( $class, '\\' );

		// Is this under the WP_Components namespace?
		if ( false !== strpos( $class, $theme_component_namespace ) ) {
			/**
			 * Strip the namespace, replace underscores with dashes, and lowercase.
			 *
			 * `\WP_Components\Body`
			 * becomes
			 * `body`
			 */
			$class = strtolower(
				str_replace(
					[ $theme_component_namespace, '_' ],
					[ '', '-' ],
					$class
				)
			);

			$dirs  = explode( '\\', $class );
			$class = array_pop( $dirs );
			$path  = apply_filters( 'wp_components_theme_components_path', $class, $dirs );
			if ( file_exists( $path ) ) {
				require_once $path;
			}
		}
	}
);

/**
 * Autoloader for WP_Render.
 */
spl_autoload_register(
	function( $object ) {

		// Trim leading dashes.
		$object = ltrim( $object, '\\' );

		// Is this under the WP_Render namespace?
		if ( false !== strpos( $object, 'WP_Render' ) ) {
			/**
			 * Strip the namespace, replace underscores with dashes, and lowercase.
			 *
			 * `\WP_Render\Render_Controller`
			 * becomes
			 * `render-controller`
			 */
			$object = strtolower(
				str_replace(
					[ 'WP_Render\\', '_' ],
					[ '', '-' ],
					$object
				)
			);

			$dirs   = explode( '\\', $object );
			$object = array_pop( $dirs );

			// Check if this is a class.
			$object_path  = WP_COMPONENTS_PATH . rtrim( '/inc/' . implode( '/', $dirs ), '/' ) . "/wp-render/classes/class-{$object}.php";
			if ( file_exists( $object_path ) ) {
				require_once $object_path;
			}

			// Check if this is a trait.
			$trait_path  = WP_COMPONENTS_PATH . rtrim( '/inc/' . implode( '/', $dirs ), '/' ) . "/wp-render/traits/trait-{$object}.php";
			if ( file_exists( $trait_path ) ) {
				require_once $trait_path;
			}
		}
	}
);
