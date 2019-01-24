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

// Include autoloaders.
require_once 'autoload.php';

// Load WP_Render template tags.
require_once 'inc/wp-render/template-tags.php';
