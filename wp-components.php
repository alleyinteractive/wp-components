<?php
/**
 * Plugin Name:     WP Components
 * Plugin URI:      alley.co
 * Description:     Build WordPress themes using components.
 * Author:          jameswalterburke
 * Text Domain:     wp-components
 * Domain Path:     /languages
 * Version:         0.1.0
 *
 * @package         WP_Components
 */

namespace WP_Components;

/**
 * Define the path of this plugin.
 */
define( 'WP_COMPONENTS_PATH', dirname( __FILE__ ) );

/**
 * Define the path to the assets used in the PHP rendering system.
 */
define( 'WP_COMPONENTS_PHP_ASSET_PATH', get_stylesheet_directory() . '/client/build' );

// Load template tag helpers.
require_once 'inc/wp-render/template-tags.php';

/**
 * Autoloader.
 */
spl_autoload_register(
	function( $class ) {
		$class = ltrim( $class, '\\' );
		if ( false !== strpos( $class, 'WP_Components' ) ) {
			// echo $class;
			// echo "</br>";

			/**
			 * Strip the namespace, replace underscores with dashes, and lowercase.
			 *
			 * `\WP_Component\Component\Slim_Navigation\Menu`
			 * becomes
			 * `slim-navigation\class-menu.php`
			 */
			$class = strtolower(
				str_replace(
					[ 'WP_Components\\Component\\', '_' ],
					[ '', '-' ],
					$class
				)
			);

			$dirs  = explode( '\\', $class );
			$class = array_pop( $dirs );

			// Check if this is a class.
			$class_path  = WP_COMPONENTS_PATH . rtrim( '/inc/' . implode( '/', $dirs ), '/' ) . "/classes/class-{$class}.php";
			if ( file_exists( $class_path ) ) {
				require_once $class_path;
			}

			// Check if this is a trait.
			$trait_path  = WP_COMPONENTS_PATH . rtrim( '/inc/' . implode( '/', $dirs ), '/' ) . "/traits/trait-{$class}.php";
			if ( file_exists( $trait_path ) ) {
				require_once $trait_path;
			}
		}
	}
);
