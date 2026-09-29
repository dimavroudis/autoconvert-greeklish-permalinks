<?php

require_once __DIR__ . '/../../includes/agp-converter.php';

class Agp_Query_Test_WPDB
{
	public $posts = 'wp_posts';
	public $terms = 'wp_terms';
	public $term_taxonomy = 'wp_term_taxonomy';
	public $prepared_args = array();
	public $batch_sizes = array();
	public $post_rows = array();
	public $term_rows = array();

	public function prepare(string $query, ...$args)
	{
		$this->prepared_args[] = $args[0] ?? array();
		return $query;
	}

	public function get_results($query = null, $output = OBJECT)
	{
		$is_post_query = strpos($query, 'FROM wp_posts') !== false;
		$rows = $is_post_query ? $this->post_rows : $this->term_rows;
		$batch_number = count($this->batch_sizes);
		$batch = array_slice($rows, $batch_number * 500, 500);
		$this->batch_sizes[] = count($batch);

		return $batch;
	}
}

class Agp_Query_Test extends \WP_Mock\Tools\TestCase
{
	/**
	 * @var \wpdb|null
	 */
	protected $previous_wpdb;

	public function setUp(): void
	{
		parent::setUp();
		$this->previous_wpdb = isset($GLOBALS['wpdb']) ? $GLOBALS['wpdb'] : null;
	}

	public function tearDown(): void
	{
		if ($this->previous_wpdb === null) {
			unset($GLOBALS['wpdb']);
		} else {
			$GLOBALS['wpdb'] = $this->previous_wpdb;
		}
		parent::tearDown();
	}

	public function testPostQueryReadsRowsInBatches()
	{
		$wpdb = new Agp_Query_Test_WPDB();
		for ($i = 0; $i < 500; $i++) {
			$wpdb->post_rows[] = (object) array('ID' => $i + 1, 'post_name' => 'post-' . $i);
		}
		$wpdb->post_rows[] = (object) array('ID' => 501, 'post_name' => 'α');
		$GLOBALS['wpdb'] = $wpdb;

		$count = (new Agp_Converter())->postQuery(array('post'));

		$this->assertSame(1, $count);
		$this->assertSame(array(500, 1), $wpdb->batch_sizes);
		$this->assertSame(array('post', 500, 0), $wpdb->prepared_args[0]);
		$this->assertSame(array('post', 500, 500), $wpdb->prepared_args[1]);
	}

	public function testTermQueryReadsRowsInBatches()
	{
		$wpdb = new Agp_Query_Test_WPDB();
		for ($i = 0; $i < 500; $i++) {
			$wpdb->term_rows[] = (object) array('term_id' => $i + 1, 'slug' => 'term-' . $i, 'taxonomy' => 'category');
		}
		$wpdb->term_rows[] = (object) array('term_id' => 501, 'slug' => 'α', 'taxonomy' => 'category');
		$GLOBALS['wpdb'] = $wpdb;

		$count = (new Agp_Converter())->termQuery(array('category'));

		$this->assertSame(1, $count);
		$this->assertSame(array(500, 1), $wpdb->batch_sizes);
		$this->assertSame(array('category', 500, 0), $wpdb->prepared_args[0]);
		$this->assertSame(array('category', 500, 500), $wpdb->prepared_args[1]);
	}

	public function testPostQueryReturnsObjectsAndHonorsLimit()
	{
		$wpdb = new Agp_Query_Test_WPDB();
		$wpdb->post_rows = array(
			(object) array('ID' => 1, 'post_name' => 'valid-slug'),
			(object) array('ID' => 2, 'post_name' => 'Αθήνα'),
			(object) array('ID' => 3, 'post_name' => 'καλημέρα'),
			(object) array('ID' => 4, 'post_name' => 'σπίτι'),
		);
		$GLOBALS['wpdb'] = $wpdb;

		$updated = (new Agp_Converter())->postQuery(array('post'), 'object', 2);

		$this->assertCount(2, $updated);
		$this->assertSame(array(2, 3), array_map(function ($post) {
			return $post['ID'];
		}, $updated));
		$this->assertSame('athina', $updated[0]['post_name']);
		$this->assertSame('kalimera', $updated[1]['post_name']);
	}

	public function testTermQueryIgnoresAlreadyGreeklishTerms()
	{
		$wpdb = new Agp_Query_Test_WPDB();
		$wpdb->term_rows = array(
			(object) array('term_id' => 1, 'slug' => 'category-1', 'taxonomy' => 'category'),
			(object) array('term_id' => 2, 'slug' => 'category-2', 'taxonomy' => 'category'),
			(object) array('term_id' => 3, 'slug' => 'Μαρία', 'taxonomy' => 'category'),
		);
		$GLOBALS['wpdb'] = $wpdb;

		$count = (new Agp_Converter())->termQuery(array('category'));

		$this->assertSame(1, $count);
	}
}
