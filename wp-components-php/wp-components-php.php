<?php
/**
 * Main file for PHP renderer
 *
 * @package WP_Component
 */

namespace WP_Component\PHP;

define( 'WP_COMPONENTS_PHP_ASSET_PATH', get_stylesheet_directory() . '/client/build' );

// Load classes.
require_once 'inc/classes/class-renderable.php';
require_once 'inc/classes/class-render-controller.php';
require_once 'template-tags.php';
