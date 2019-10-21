<?php
/**
 * Autoloaders.
 *
 * @package WP_Components
 */

namespace WP_Components;

/**
 * Autoloader for components in the WP_Components plugin.
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
			$object_path  = WP_COMPONENTS_PATH . rtrim( '/inc/wp-components/classes/' . implode( '/', $dirs ), '/' ) . "/class-{$object}.php";
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

		/**
		 * Filter to define the namespace(s).
		 *
		 * @param string|array $namespaces The theme namespace(s) to use for autoloading components.
		 */
		$theme_component_namespaces = apply_filters( 'wp_components_theme_components_namespace', '' );

		if ( empty( $theme_component_namespaces ) ) {
			return;
		}

		// Convert to an array if needed.
		if ( is_string( $theme_component_namespaces ) ) {
			$theme_component_namespaces = [ $theme_component_namespaces ];
		}

		// Trim leading dashes.
		$class = ltrim( $class, '\\' );

		foreach ( $theme_component_namespaces as $theme_component_namespace ) {
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

				// Attempt to guess the path.
				$dirs     = explode( '\\', ltrim( $class, '\\' ) );
				$filename = end( $dirs );
				$path     = get_stylesheet_directory() . '/components/' . implode( '/', $dirs ) . "/class-{$filename}.php";

				/**
				 * Filter the path(s) in which to look for components.
				 *
				 * @param string|array $path   Path, or array of paths, where theme components are located.
				 * @param string       $class  The class.
				 * @param array        $dirs   Array of directories derived from the class.
				 * @param $filename    $string The expected filename.
				 */
				$paths = apply_filters( 'wp_components_theme_components_path', $path, $class, $dirs, $filename );

				// Convert to an array if needed.
				if ( is_string( $paths ) ) {
					$paths = [ $paths ];
				}

				foreach ( $paths as $path ) {
					if ( file_exists( $path ) ) {
						require_once $path;
					}
				}
			}
		}
	}
);

/**
 * Autoloader for WP_Render template files.
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
