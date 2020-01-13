<?php
/**
 * Class ImageTest
 *
 * @package Wp_Components
 */

/**
 * Test the Image component.
 */
class Image_Tests extends WP_UnitTestCase {

	/**
	 * Attachment ID.
	 *
	 * @var number
	 */
	public static $attachment_id = 0;

	/**
	 * Test suite setup.
	 */
	public static function setUpBeforeClass() {
		// insert an attachment.
		self::$attachment_id = self::factory()->attachment->create_upload_object( dirname( __FILE__ ) . '/test-image.jpg' );
	}

	/**
	 * Test suite setup.
	 */
	public static function tearDownAfterClass() {
		// delete attachment.
		wp_delete_post( self::$attachment_id );
	}

	/**
	 * Unit test setup.
	 */
	public function setUp() {
		$this->image = new \WP_Components\Image();
		$this->image::register_breakpoints(
			[
				'xxl' => '90rem',
				'xl'  => '80rem',
				'lg'  => '64rem',
				'md'  => '48rem',
				'sm'  => '32rem',
			]
		);
		$this->image::register_sizes(
			[
				'test' => [
					'sources'            => [
						[
							'transforms' => [
								'resize' => [ 640, 480 ],
							],
							'descriptor' => 640,
							'media' => [ 'max' => 'xxl' ],
						],
						[
							'transforms' => [
								'resize' => [ 480, 360 ],
							],
							'descriptor' => 480,
							'media' => [ 'max' => 'md' ],
						],
					],
					'aspect_ratio'       => 2 / 3,
					'retina'             => true,
					'fallback_image_url' => wp_get_attachment_url( self::factory()->attachment->create_object( [ 'file' => 'fallback.jpg' ] ) ),
				],
			]
		);

		// insert a post.
		$this->post = $this->factory->post->create_and_get(
			array(
				'post_title' => rand_str(),
				'post_date'  => '2020-01-01 00:00:00',
			)
		);
	}

	/**
	 * Test for setting attachment ID.
	 */
	public function test_set_attachment_id() {
		$this->image->set_attachment_id( self::$attachment_id );

		$this->assertArraySubset(
			[
				'id'    => self::$attachment_id,
				'url'   => $this->image->attachment->guid,
				'crops' => [],
			],
			$this->image->config
		);
	}

	/**
	 * Test for setting post id.
	 */
	public function test_set_post_id() {
		update_post_meta(
			$this->post->ID,
			'_thumbnail_id',
			self::$attachment_id
		);

		$this->image->set_post_id( $this->post->ID );

		$this->assertArraySubset(
			[
				'id'    => self::$attachment_id,
				'url'   => $this->image->attachment->guid,
				'crops' => [],
			],
			$this->image->config
		);
	}

	/**
	 * Test simplest possible call to configure().
	 */
	public function test_simple_configure() {
		$this->image->configure( self::$attachment_id );
		$this->assertArraySubset(
			[
				'id'         => self::$attachment_id,
				'url'        => $this->image->attachment->guid,
				'src'        => $this->image->attachment->guid,
				'crops'      => [],
				'image_size' => 'full',
			],
			$this->image->config
		);
	}

	/**
	 * Test setting config for size.
	 */
	public function test_set_config_for_size() {
		$this->image->set_attachment_id( self::$attachment_id );
		$this->image->set_config_for_size( 'thumbnail' );
		$this->assertArraySubset(
			[
				'id'         => self::$attachment_id,
				'url'        => $this->image->attachment->guid,
				'src'        => $this->image->attachment->guid,
				'lqip_src'   => $this->image->attachment->guid . '?quality=60&resize=60,60',
				'srcset'     => $this->image->attachment->guid . '?resize=300,300 300w,' . $this->image->attachment->guid . '?resize=150,150 150w',
				'crops'      => [],
				'image_size' => 'thumbnail',
			],
			$this->image->config
		);
	}

	/**
	 * Test <picture> markup
	 */
	public function test_picture() {
		$this->image->configure( self::$attachment_id, 'test', true );
		$this->assertArraySubset(
			[
				'id'          => self::$attachment_id,
				'url'         => $this->image->attachment->guid,
				'src'         => $this->image->attachment->guid,
				'lqip_src'    => $this->image->attachment->guid . '?quality=60&resize=60,40',
				'source_tags' => [
					[
						'srcset' => $this->image->attachment->guid . '?resize=640,480 1x,' . $this->image->attachment->guid . '?resize=1280,960 2x',
						'media'  => '(max-width: 90rem)'
					],
					[
						'srcset' => $this->image->attachment->guid . '?resize=480,360 1x,' . $this->image->attachment->guid . '?resize=960,720 2x',
						'media'  => '(max-width: 48rem)'
					],
				],
				'sizes'       => '(max-width: 90rem) 640px,(max-width: 48rem) 480px,100vw',
				'crops'       => [],
				'image_size'  => 'test',
				'picture'     => 1,
			],
			$this->image->config
		);
	}

	/**
	 * Test transforms.
	 */
	public function test_transforms() {
		$this->image::register_sizes(
			[
				'transforms' => [
					'sources'            => [
						[
							'transforms' => [
								'w'       => [ 640 ],
								'h'       => [ 480 ],
								'quality' => [ 40 ],
							],
							'descriptor' => 640,
						],
					],
				],
			]
		);
		$this->image->configure( self::$attachment_id, 'transforms' );

		$this->assertArraySubset(
			[
				'id'         => self::$attachment_id,
				'srcset'     => $this->image->attachment->guid . '?w=1280&h=960&quality=40 1280w,' . $this->image->attachment->guid . '?w=640&h=480&quality=40 640w',
			],
			$this->image->config
		);
	}

	/**
	 * Test default fallback image configuration.
	 */
	public function test_default_fallback() {
		$this->image->configure( 0, 'this-size-should-not-exist' );

		$this->assertEquals(
			$this->image->get_config( 'src' ),
			$this->image::$fallback_image_url
		);
	}

	/**
	 * Test custom global fallback image configuration.
	 */
	public function test_custom_fallback() {
		// Create new image and register as a fallback.
		$this->image::register_fallback_image(
			self::factory()->attachment->create_object(
				[
					'file'           => 'image.jpg',
					'post_mime_type' => 'foo',
					'post_type'      => 'attachment',
					'post_status'    => 'inherit',
				]
			)
		);

		$this->image->configure( 0, 'this-size-should-not-exist' );
		$this->assertEquals(
			'http://example.org/wp-content/uploads/image.jpg',
			$this->image->get_config( 'src' )
		);
	}

	/**
	 * Test custom size-specific fallback image configuration.
	 */
	public function test_size_fallback() {
		$this->image->configure( 0, 'test' );
		$this->assertEquals(
			'http://example.org/wp-content/uploads/fallback.jpg',
			$this->image->get_config( 'src' )
		);
	}

	/**
	 * Test that image component will not rely on global $post and use fallbacks appropriately,
	 * instead of falling back to image attached to global $post.
	 */
	public function test_missing_image() {
		global $post;

		// insert a second post.
		$post_two = $this->factory->post->create_and_get(
			array(
				'post_title' => rand_str(),
				'post_date'  => '2020-01-01 00:00:00',
			)
		);

		// Add thumbnail to first post, but not second
		update_post_meta(
			$this->post->ID,
			'_thumbnail_id',
			self::$attachment_id
		);

		// Set global post
		$post = $this->post;

		// Create image components
		$image_one = ( new \WP_Components\Image() )->configure( self::$attachment_id, 'test' );
		$image_two = ( new \WP_Components\Image() )->configure( $post_two->ID, 'test' );

		$this->assertEquals(
			'http://example.org/wp-content/uploads/2019/12/test-image.jpg',
			$image_one->get_config( 'src' )
		);
		$this->assertEquals(
			'http://example.org/wp-content/uploads/fallback.jpg',
			$image_two->get_config( 'src' )
		);
	}

	/**
	 * Test thumbnail editor config.
	 */
	public function test_crops() {
		$this->image::register_crop_sizes(
			[
				'Test crops' => [
					'test' => [
						'height' => 640,
						'width'  => 480,
					],
				],
			]
		);
		update_post_meta(
			$this::$attachment_id,
			'wpcom_thumbnail_edit',
			[
				'test' => [ 682, 0, 640, 480 ],
			]
		);
		$this->image->configure( self::$attachment_id, 'test' );
		$this->assertArraySubset(
			[
				'id'     => self::$attachment_id,
				'crops'  => [
					'test' => [ 682, 0, 640, 480 ],
				],
				'srcset' => $this->image->attachment->guid . '?crop=682px%2C0px%2C-42px%2C480px&resize=1280,960 1280w,' .
					$this->image->attachment->guid . '?crop=682px%2C0px%2C-42px%2C480px&resize=640,480 640w,' .
					$this->image->attachment->guid . '?crop=682px%2C0px%2C-42px%2C480px&resize=960,720 960w,' .
					$this->image->attachment->guid . '?crop=682px%2C0px%2C-42px%2C480px&resize=480,360 480w',
			],
			$this->image->config
		);
	}
}
