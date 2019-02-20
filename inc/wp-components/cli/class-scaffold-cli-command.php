<?php
/**
 * WP Components commands for scaffolding components.
 *
 * @package WP_Components
 */

namespace WP_Components;

use WP_CLI;
use WP_CLI\Utils;
use WP_CLI\Process;
use WP_CLI\Inflector;

/**
 * Class to add additional scaffolding options to the `wp scaffold` command.
 */
class Scaffold_CLI_Command extends \Scaffold_Command {

	/**
	 * Scaffold out a new component.
	 *
	 * ## OPTIONS
	 *
	 * <class>
	 * : Component class name.
	 *
	 * [--name]
	 * : Unique slug of component.
	 *
	 * [--namespace]
	 * : Namespace of component.
	 *
	 * [--folder_path]
	 * : Path for component.
	 *
	 * [--force]
	 * : Override existing code.
	 *
	 * ## EXAMPLES
	 *
	 *     # Scaffold a new WP Component component.
	 *     $ wp component jumbotron --force=yes
	 *     Success: Created '/var/www/thing.com/wp-content/themes/theme/'.
	 *
	 * @param array $args       CLI args.
	 * @param array $assoc_args CLI associate args.
	 */
	public function component( $args, $assoc_args ) {

		$data = [
			'class_name' => $args[0] ?? '';
			'file_name'  => str_replace( '_', '-', strtolower( $class_name ) );
			'name'       => $assoc_args['name'] ?? 'component';
			'namespace'  => $assoc_args['namespace'] ?? 'WP_Components';
			'path'       => $assoc_args['folder_path'] ?? '';
		];

		print_r( $data );

		// Validate class name.
		if ( empty( $data['class_name'] ) ) {
			WP_CLI::error( __( 'Component class name missing or invalid', 'wp-components' ) );
		}

		// Determine where to put these files.
		$wp_dir  = get_template_directory() . "/components/{$data['path']}";

		$this->create_files(
			[
				"$wp_dir/class-{$file_name}.php" => $this->mustache_render( 'wp-components-component.mustache', $data ),
			],
			(bool) ( $assoc_args['force'] ?? false )
		);
	}

	/**
	 * Override mustache render so we can access it here.
	 *
	 * @param  string $template Template.
	 * @param  array  $data     Data to use in the template.
	 * @return string
	 */
	private static function mustache_render( $template, $data ) {
		$template_path = dirname( dirname( __FILE__ ) ) . "/cli/templates/{$template}";
		echo $template_path; die();
		return Utils\mustache_render( $template_path, $data );
	}
}
WP_CLI::add_command( 'scaffold', __NAMESPACE__ . '\Scaffold_CLI_Command' );
