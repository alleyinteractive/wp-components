<?php
/**
 * Image component.
 *
 * @package WP_Components
 */

namespace WP_Components;

/**
 * Image
 */
class Image extends Component {

	use Attachment;

	/**
	 * Unique component slug.
	 *
	 * @var string
	 */
	public $name = 'image';

	/**
	 * Image sizes.
	 *
	 * @var array
	 */
	public static $sizes = [];

	/**
	 * Media queries for sizes attribute or source tags.
	 *
	 * @var array
	 */
	public static $breakpoints = [];

	/**
	 * WPCOM Thumbnail Editor Sizes
	 *
	 * @var array
	 */
	public static $crop_sizes = [];

	/**
	 * Global fallback image URL, will be used if a size-specific fallback is not configured.
	 *
	 * @var string
	 */
	public static $fallback_image_url = 'data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7';

	/**
	 * Define the default config of an image.
	 *
	 * @return array Default config.
	 */
	public function default_config() : array {
		$this->register_default_sizes();

		return [
			'aspect_ratio'        => 9 / 16,
			'id'                  => 0,
			'alt'                 => '',
			'caption'             => '',
			'crops'               => '',
			'height'              => 0,
			'image_size'          => 'full',
			'lazyload'            => true,
			'lqip_src'            => 'data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7',
			'post_id'             => 0,
			'retina'              => true,
			'show_caption'        => false,
			'sources'             => [],
			'source_tags'         => [],
			'src'                 => '',
			'srcset'              => '',
			'url'                 => '',
			'use_basic_img'       => false,
			'using_data_fallback' => false,
			'width'               => 0,
		];
	}

	/**
	 * Register default image size.
	 *
	 * @return void
	 */
	protected function register_default_sizes() {
		$options              = wp_load_alloptions();
		$default_size_configs = [];

		foreach ( [ 'thumbnail', 'medium', 'large' ] as $default_size ) {
			$default_size_configs[ $default_size ] = [
				'sources' => [
					[
						'transforms' => [
							'resize' => [
								$options[ $default_size . '_size_w' ],
								$options[ $default_size . '_size_h' ],
							],
						],
						'descriptor' => $options[ $default_size . '_size_w' ],
					],
				],
				'aspect_ratio' => $options[ $default_size . '_size_h' ] / $options[ $default_size . '_size_w' ],
			];
		}

		$this->register_sizes( $default_size_configs );
	}

	/**
	 * Register sizes at once.
	 *
	 * @param array $sizes Array of arguments for register_size.
	 */
	public static function register_sizes( array $sizes ) {
		self::$sizes = array_merge( self::$sizes, $sizes );
	}

	/**
	 * Register breakpoints.
	 *
	 * @param array $breakpoints Array of breakpoints.
	 */
	public static function register_breakpoints( array $breakpoints ) {
		self::$breakpoints = array_merge( self::$breakpoints, $breakpoints );
	}

	/**
	 * Register global fallback image URL.
	 *
	 * @param int $fallback_image_id Attachment ID of fallback image.
	 */
	public static function register_fallback_image( int $fallback_image_id ) {
		// Treat a number as an attachment ID.
		$url = wp_get_attachment_url( $fallback_image_id );

		if ( ! empty( $url ) ) {
			self::$fallback_image_url = $url;
		}
	}

	/**
	 * Register crops for WPCOM Thumbnail Editor.
	 *
	 * @param array $crop_sizes Array of crop sizes.
	 */
	public static function register_crop_sizes( array $crop_sizes ) {
		self::$crop_sizes = array_merge( self::$crop_sizes, $crop_sizes );

		// Register image sizes.
		add_action(
			'after_setup_theme',
			function() {
				foreach ( Image::$crop_sizes as $crop_size ) {
					foreach ( $crop_size as $key => $params ) {
						$params = wp_parse_args(
							$params,
							[
								'crop'   => false,
								'height' => 0,
								'width'  => 0,
							]
						);
						add_image_size( $key, $params['width'], $params['height'], $params['crop'] );
					}
				}
			}
		);

		// Setup WPCOM Thumbnail Editor image ratio map.
		add_filter(
			'wpcom_thumbnail_editor_args',
			function( $args ) {
				$mapping = [];
				foreach ( Image::$crop_sizes as $key => $crop_size ) {
					$mapping[ $key ] = $mapping[ $key ] ?? [];
					$mapping[ $key ] = array_merge( $mapping[ $key ], array_keys( $crop_size ) );
				}
				$args['image_ratio_map'] = $mapping;
				return $args;
			}
		);
	}

	/**
	 * Run after attachment ID is set.
	 *
	 * @return void
	 */
	public function attachment_has_set() {
		// Get crops from post meta.
		$crops = (array) get_post_meta( $this->get_attachment_id(), 'wpcom_thumbnail_edit', true );

		$this->merge_config(
			[
				'crops' => array_filter( $crops ),
				'id'    => $this->get_attachment_id(),
				'url'   => strtok( $this->get_attachment_src( 'full' ), '?' ),
			]
		);
	}

	/**
	 * Loads a predefined array of settings from the static sizes array.
	 *
	 * @param string $image_size Key of the size.
	 * @param bool   $picture Whether or not to use a <picture> element.
	 * @return \Wp_Components\Image Current instance of this class.
	 */
	public function set_config_for_size( string $image_size, $picture = false ): self {
		// Call configure method.
		return $this->configure( $this->get_config( 'id' ), $image_size, $picture );
	}

	/**
	 * Prepare config for use with an <img> or <picture> tag.
	 *
	 * @param int    $id         Attachment ID or Post ID.
	 * @param string $image_size Image size configuration to use for this component.
	 * @param bool   $picture    Whether or not to use a <picture> element.
	 * @return Component Current instance of this class.
	 */
	public function configure( $id, $image_size = 'full', $picture = false ): self {
		$sizes = self::$sizes;
		// Set config size and fallback.
		$size_config = $sizes[ $image_size ] ?? [];

		// Attempt to set attachment by ID, if not set already.
		if ( empty( $this->attachment ) ) {
			$this->set_id( $id );
		}

		// Set fallback image if no url configured.
		if ( empty( $this->get_config( 'url' ) ) ) {
			$this->set_config( 'url', $this->get_fallback_image( $size_config ) );
		}

		// Return early with just a src if no size config exists for provided size.
		if ( empty( $sizes[ $image_size ] ) || empty( $this->attachment ) ) {
			return $this->set_config( 'src', $this->get_config( 'url' ) );
		}

		// Set aspect ratio.
		$this->set_config( 'aspect_ratio', $this->get_aspect_ratio( $size_config ) );

		// Set flags for using a basic <img> tag or using the fallback URL.
		$this->merge_config(
			[
				'use_basic_img'       => ( 1 === count( $this->get_config( 'sources' ) ) && ! $this->get_config( 'retina' ) ),
				'using_data_fallback' => strstr( $this->get_config( 'url' ), 'data:' ),
				'image_size'          => $image_size,
				'sources'             => $size_config['sources'],
			]
		);

		// Set image alt text (via trait).
		$this->set_alt_text();

		// Set image dimensions (via trait).
		$this->set_attachment_dimensions();

		// Set crop transforms.
		$this->configure_crops( $image_size );

		// Set image config.
		return $this->merge_config(
			[
				'caption'     => $this->get_attachment_caption(),
				'lqip_src'    => $this->get_lqip_src(),
				'picture'     => $picture,
				'sizes'       => $this->get_sizes(),
				'source_tags' => $this->get_source_tags( $picture ),
				'src'         => $this->get_src(),
				'srcset'      => $this->get_srcset( $size_config ),
				'retina'      => $this->get_retina( $size_config ),
				'lazyload'    => $this->get_lazyload( $size_config ),
			]
		);
	}

	/**
	 * Configure crop setttings for a particular image size.
	 *
	 * @param string $image_size Image size for which crops should be configured.
	 * @return Component
	 */
	public function configure_crops( $image_size ): self {
		$crops = $this->get_config( 'crops' );

		// If the size key matches a crop option, apply that transform.
		if ( ! empty( $crops[ $image_size ] ) ) {
			$sources = $this->get_config( 'sources' );

			foreach ( $sources as &$source ) {
				// Convert stored coordinates into crop friendly parameters.
				$source['transforms'] = array_merge(
					[
						'crop' => [
							$crops[ $image_size ][0] . 'px',
							$crops[ $image_size ][1] . 'px',
							( $crops[ $image_size ][2] - $crops[ $image_size ][0] ) . 'px',
							( $crops[ $image_size ][3] - $crops[ $image_size ][1] ) . 'px',
						],
					],
					$source['transforms']
				);
			}

			$this->set_config( 'sources', $sources );
		}

		return $this;
	}

	/**
	 * Get configured fallback image.
	 *
	 * @param array $size_config Config for current image size.
	 * @return string
	 */
	public function get_fallback_image( $size_config ): string {
		return $size_config['fallback_image_url'] ?? self::$fallback_image_url;
	}

	/**
	 * Get lazyload setting.
	 *
	 * @param array $size_config Config for current image size.
	 * @return string
	 */
	public function get_lazyload( $size_config ): string {
		return $size_config['lazyload'] ?? $this->get_config( 'lazyload' );
	}

	/**
	 * Get retina image setting.
	 *
	 * @param array $size_config Config for current image size.
	 * @return string
	 */
	public function get_retina( $size_config ): string {
		return $size_config['retina'] ?? $this->get_config( 'retina' );
	}

	/**
	 * Retrieve aspect ratio value from either image size config, image component config, or original width and height values
	 *
	 * @param array $size_config Configuration for current image size.
	 * @return string
	 */
	public function get_aspect_ratio( $size_config ) {
		$aspect_ratio = ( $size_config['aspect_ratio'] ?? $this->config['aspect_ratio'] ) ?? false;

		// Useful if you're ouptutting an image with only one constrained dimension (like a max height or max width, but no specific aspect ratio). Usually involves `fit`, `w`, or `h` transforms.
		if ( 'auto' === $aspect_ratio ) {
			if ( ! empty( $this->get_config( 'width' ) ) && ! empty( $this->get_config( 'height' ) ) ) {
				return intval( $this->get_config( 'height' ) ) / intval( $this->get_config( 'width' ) );
			}

			return false;
		}

		return $aspect_ratio;
	}

	/**
	 * Prepare config for use with an <picture> tag.
	 *
	 * @param bool $picture Should this image use <picture> markup.
	 * @return array
	 */
	public function get_source_tags( $picture = false ) {
		$source_tags = [];
		$sources     = (array) $this->config['sources'];

		// Don't set this if we're using a basic <img> tag or using the fallback image.
		if (
			! $picture
			|| $this->get_config( 'use_basic_img' )
			|| $this->get_config( 'using_data_fallback' )
		) {
			return [];
		}

		foreach ( $sources as $params ) {
			// Get source URL.
			$transforms = $params['transforms'];
			$src_url    = $this->apply_transforms( $transforms );

			// Add retina source to srcset, if applicable.
			if ( $this->config['retina'] ) {
				$retina_url    = $this->apply_transforms( $transforms, 2 );
				$srcset_string = "{$src_url} 1x,{$retina_url} 2x";
			} else {
				$srcset_string = $src_url;
			}

			// Construct source tag.
			$source_tags[] = [
				'srcset' => $srcset_string,
				'media'  => esc_attr( $this->get_media( $params['media'] ?? '' ) ),
			];
		}

		return $source_tags;
	}

	/**
	 * Get source attribute for this image.
	 *
	 * @return string LQIP source URL
	 */
	public function get_src() : string {
		if ( $this->get_config( 'using_data_fallback' ) || ! $this->get_config( 'use_basic_img' ) ) {
			return $this->get_config( 'url' );
		}

		return $this->apply_transforms( $this->get_config( 'sources' )[0]['transforms'] );
	}

	/**
	 * Get source URL for lqip functionality
	 *
	 * @return string LQIP source URL
	 */
	public function get_lqip_src() : string {
		$aspect_ratio = $this->config['aspect_ratio'];

		// Return default if no aspect ratio is set.
		if ( empty( $aspect_ratio ) || strstr( $this->get_config( 'url' ), 'data:' ) ) {
			return $this->get_config( 'lqip_src' );
		}

		return $this->apply_transforms(
			[
				'quality' => [ 60 ],
				'resize'  => [ 60, 60 * $aspect_ratio ],
			]
		);
	}

	/**
	 * Get the srcset for <img> element.
	 *
	 * @return array Sources.
	 */
	public function get_srcset( $size_config ) : string {
		$srcset  = [];
		$sources = (array) $this->config['sources'];

		// Don't set this if we're using a basic <img> tag or using the fallback image.
		if ( $this->get_config( 'use_basic_img' ) || $this->get_config( 'using_data_fallback' ) ) {
			return '';
		}

		foreach ( $sources as $params ) {
			// Get source URL.
			$src_url = $this->apply_transforms( $params['transforms'] );

			// Get descriptor.
			$descriptor = $params['descriptor'] ?? 0;

			// Add retina source to srcset, if applicable.
			if ( is_numeric( $descriptor ) ) {
				if ( $this->get_retina ) {
					$retina_url        = $this->apply_transforms( $params['transforms'], 2 );
					$retina_descriptor = absint( $descriptor ) * 2;
					$srcset[]          = "{$retina_url} {$retina_descriptor}w";
				}

				$srcset[] = "{$src_url} {$descriptor}w";
			} else {
				$srcset[] = "{$src_url} {$descriptor}";
			}
		}

		return html_entity_decode( esc_attr( implode( ',', $srcset ) ) );
	}

	/**
	 * Prepare config for use with an <picture> tag.
	 *
	 * @return string
	 */
	public function get_sizes() : string {
		$sizes   = [];
		$sources = (array) $this->config['sources'];
		$default = false;

		// Don't set this if we're using a basic <img> tag or using the fallback image.
		if ( $this->get_config( 'use_basic_img' ) || $this->get_config( 'using_data_fallback' ) ) {
			return '';
		}

		foreach ( $sources as $params ) {
			// Ensure descriptor is set.
			if ( ! isset( $params['descriptor'] ) ) {
				continue;
			}

			if ( is_numeric( $params['descriptor'] ) ) {
				if ( ! empty( $params['default'] ) ) {
					$default = "{$params['descriptor']}px";
					continue;
				}

				if ( ! empty( $params['media'] ) ) {
					$sizes[] = "{$this->get_media( $params['media'] )} {$params['descriptor']}px";
				}
			}
		}

		$sizes[] = ! empty( $default ) ? $default : '100vw';

		return esc_attr( implode( ',', $sizes ) );
	}

	/**
	 * Get media attribute for a specific source
	 *
	 * @param array $media_params Media query parameters.
	 * @return string Media attribute content.
	 */
	public function get_media( $media_params ) : string {
		$breakpoints = self::$breakpoints;

		if ( ! is_array( $media_params ) ) {
			return 'all';
		}

		// Use custom media if it's set.
		if ( ! empty( $media_params['custom'] ) ) {
			return $media_params['custom'];
		}

		// Compile min and max width settings.
		$min_width = ! empty( $media_params['min'] ) ?
			"(min-width: {$breakpoints[ $media_params['min'] ]})" :
			false;
		$max_width = ! empty( $media_params['max'] ) ?
			"(max-width: {$breakpoints[ $media_params['max'] ]})" :
			false;

		if ( $min_width && $max_width ) {
			return "{$min_width} and {$max_width}";
		} elseif ( $min_width ) {
			return $min_width;
		} elseif ( $max_width ) {
			return $max_width;
		}

		// Default to 'all'.
		return 'all';
	}

	/**
	 * Shortcut for attempting to set attachment or post ID.
	 *
	 * @param int $id Attachment or Post ID.
	 * @return Component Current instance of this class.
	 */
	public function set_id( $id ): self {
		// Attempt to set attachment ID first.
		$this->set_attachment_id( $id );

		// Attempt to set post ID if it doesn't work.
		if ( empty( $this->attachment ) ) {
			$this->set_post_id( $id );
		}

		return $this;
	}

	/**
	 * Setup this component using a post.
	 *
	 * @param int $post_id Post ID.
	 * @return Component Current instance of this class.
	 */
	public function set_post_id( $post_id ): self {

		// Validate $post_id.
		if (
			0 === $post_id
			|| ! get_post( $post_id ) instanceof \WP_Post
		) {
			// trigger fallback image or other settings.
			return $this;
		}

		// Get the URL.
		$attachment_id = get_post_thumbnail_id( $post_id );

		return $this->set_attachment( $attachment_id );
	}

	/**
	 * Setup this component using an attachment.
	 * (preserved for backwards compatibility, use `$this->configure()` instead).
	 *
	 * @param int $attachment_id Attachemnt ID.
	 * @return Component Current instance of this class.
	 */
	public function set_attachment_id( $attachment_id ): self {
		return $this->set_attachment( $attachment_id );
	}

	/**
	 * Retrieve alt text for current image.
	 *
	 * @return Component
	 */
	public function set_alt_text(): self {
		return $this->set_config( 'alt', $this->get_attachment_alt() );
	}

	/**
	 * Set the URL to the original image.
	 *
	 * @param string $url Image URL.
	 * @return Component Current instance of this class.
	 */
	public function set_url( string $url ) {
		return $this->set_config( 'url', $url );
	}

	/**
	 * Set alt text for image.
	 *
	 * @param string $alt Alt text for image.
	 * @return Component Current instance of this class.
	 */
	public function alt( string $alt ) {
		return $this->set_config( 'alt', $alt );
	}

	/**
	 * Shortcut for setting 'lazyload' config value.
	 *
	 * @param bool $value Value to which `lazyload` config property should be changed.
	 */
	public function lazyload( $value = true ) {
		return $this->set_config( 'lazyload', $value );
	}

	/**
	 * Set aspect ratio of image for use with CSS intrinsic ratio sizing.
	 * Set to false to turn off intrinsic sizing.
	 *
	 * @param float|bool $ratio Aspect ratio of image expressed as a decimal.
	 */
	public function aspect_ratio( $ratio ) {
		return $this->set_config( 'aspect_ratio', $ratio );
	}

	/**
	 * Set the width of an image. Defaults to pixels, supports percentages.
	 *
	 * @see  https://developer.wordpress.com/docs/photon/api/#w
	 *
	 * @param int $width  Resized width.
	 * @param int $density_multiplier screen density multiplier.
	 * @return array Transform values prepared to be added as query args.
	 */
	public function w( int $width, $density_multiplier = 1 ) {
		$value = absint( $width ) * $density_multiplier;
		return [ 'w' => $value ];
	}

	/**
	 * Set the height of an image. Defaults to pixels, supports percentages.
	 *
	 * @see  https://developer.wordpress.com/docs/photon/api/#h
	 *
	 * @param int $height  Resized height.
	 * @param int $density_multiplier screen density multiplier.
	 * @return array Transform values prepared to be added as query args.
	 */
	public function h( int $height, $density_multiplier = 1 ) {
		$value = absint( $height ) * $density_multiplier;
		return [ 'h' => $value ];
	}

	/**
	 * Crop an image by percentages x-offset,y-offset,width,height (x,y,w,h).
	 * Percentages are used so that you don’t need to recalculate the cropping
	 * when transforming the image in other ways such as resizing it.Original
	 * image: 4-MCM_0830-1600×1064.jpgcrop=12,25,60,60 takes a 60% by 60%
	 * rectangle from the source image starting at 12% offset from the left and
	 * 25% offset from the top.
	 *
	 * @see  https://developer.wordpress.com/docs/photon/api/#crop
	 *
	 * @param int|string $x      X-offset value.
	 * @param int|string $y      Y-offset value.
	 * @param int|string $width  Width.
	 * @param int|string $height Height.
	 * @return array Transform values prepared to be added as query args.
	 */
	public function crop( $x, $y, $width, $height ) {
		$value = sprintf(
			'%1$s,%2$s,%3$s,%4$s',
			$x,
			$y,
			$width,
			$height
		);
		return [ 'crop' => $value ];
	}

	/**
	 * Resize and crop an image to exact width,height pixel dimensions. Set the
	 * first number as close to the target size as possible and then crop the
	 * rest. Which direction it’s resized and cropped depends on the aspect
	 * ratios of the original image and the target size.
	 *
	 * @see  https://developer.wordpress.com/docs/photon/api/#resize
	 *
	 * @param int $width  Resized width.
	 * @param int $height Resized height.
	 * @param int $density_multiplier screen density multiplier.
	 * @return array Transform values prepared to be added as query args.
	 */
	public function resize( int $width, int $height, $density_multiplier = 1 ) {
		$value = sprintf(
			'%1$d,%2$d',
			absint( $width ) * $density_multiplier,
			absint( $height ) * $density_multiplier
		);
		return [ 'resize' => $value ];
	}

	/**
	 * Fit an image to a containing box of width,height dimensions. Image
	 * aspect ratio is maintained.
	 *
	 * @see  https://developer.wordpress.com/docs/photon/api/#fit
	 *
	 * @param int $width  Resized width.
	 * @param int $height Resized height.
	 * @param int $density_multiplier screen density multiplier.
	 * @return array Transform values prepared to be added as query args.
	 */
	public function fit( int $width, int $height, $density_multiplier = 1 ) {
		$value = sprintf(
			'%1$d,%2$d',
			absint( $width ) * $density_multiplier,
			absint( $height ) * $density_multiplier
		);
		return [ 'fit' => $value ];
	}

	/**
	 * Modify compression quality of source image
	 *
	 * @see  https://developer.wordpress.com/docs/photon/api/#quality
	 *
	 * @param int $percentage Quality percentage.
	 * @return array Transform values prepared to be added as query args.
	 */
	public function quality( int $percentage ) {
		$value = absint( $percentage );
		return [ 'quality' => $value ];
	}

	/**
	 * Apply a single transform to the current source URL
	 *
	 * @see  https://developer.wordpress.com/docs/photon/api/#fit
	 *
	 * @param array  $transform Transform to apply.
	 * @param array  $args      Array of transform arguments.
	 * @param string $url       Url to which to apply transforms. Defaults to the original attachment URL.
	 * @return string New URL with transform applied
	 */
	public function apply_transform( $transform, $args, $url = '' ) {
		$source_url = ! empty( $url ) ? $url : $this->get_config( 'url' );

		// If transform method doesn't exist, return original URL.
		if ( ! method_exists( $this, $transform ) ) {
			return $source_url;
		}

		// Get prepared transform args.
		$transform_args = call_user_func_array(
			array( $this, $transform ),
			$args
		);

		// Apply the transform to URL.
		return add_query_arg( $transform_args, $source_url );
	}

	/**
	 * Apply an array of transforms to an image src URL
	 *
	 * @param array  $transforms         parameters for all transforms to apply.
	 * @param int    $density_multiplier screen density multiplier.
	 * @param string $url                Url to which to apply transforms. Defaults to the original attachment URL.
	 * @return string Url with all transforms applied
	 */
	public function apply_transforms( array $transforms, $density_multiplier = 1, $url = '' ) {
		// Loop through transforms.
		foreach ( $transforms as $transform => $values ) {
			// Add multiplier.
			$values[] = $density_multiplier;
			$url      = $this->apply_transform( $transform, $values, $url );
		}

		return $url;
	}
}
