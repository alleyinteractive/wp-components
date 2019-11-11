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
		// insert a post.
		$this->post = $this->factory->post->create_and_get(
			array(
				'post_title' => rand_str(),
				'post_date'  => '2009-07-01 00:00:00',
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
		$this->assertArraySubset(
			[
				'id'          => self::$attachment_id,
				'url'         => $this->image->attachment->guid,
				'src'         => $this->image->attachment->guid,
				'lqip_src'   => $this->image->attachment->guid . '?quality=60&resize=60,60',
				'srcset'      => $this->image->attachment->guid . '?resize=600,600 600w,' . $this->image->attachment->guid . '?resize=300,300 300w',
				'source_tags' => [
					[
						'srcset' => $this->image->attachment->guid . '?resize=300,300 1x,' . $this->image->attachment->guid . '?resize=600,600 2x',
						'media'  => 'all'
					],
				],
				'crops'       => [],
				'image_size'  => 'medium',
			],
			$this->image->config
		);
	}
}
