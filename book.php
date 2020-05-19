<?php
/*
Plugin Name: Book
Plugin URI: #
Description: Book plugins for Lemon Hive.
Author: SM Golam Zilani
Author URI: #
Text Domain: lemon-hive-book
Domain Path: /languages/
Version: 1.0.0
*/

defined('ABSPATH') or die('No script kiddies please!');

define('LHB_VERSION', '1.0.0');

register_activation_hook(__FILE__, 'lemon_hive_book_activator');
register_deactivation_hook(__FILE__, 'lemon_hive_book_deactivator');

include_once dirname(__FILE__) . '/includes/Book_class.php';
include_once dirname(__FILE__) . '/includes/custom_meta_box.php';
function lemon_hive_book_activator()
{
	global $wp_rewrite;
	$book = new Book_class();
	$book->activate();
	$wp_rewrite->flush_rules(true);
}

function lemon_hive_book_deactivator()
{
	global $wp_rewrite;
	$book = new Book_class();
	$book->deactivate();
	$wp_rewrite->flush_rules(true);
}
$book = new Book_class();
add_action('init', array($book, 'lemon_hive_book_cpt'));
add_action('init', array($book, 'lemon_hive_book_taxonomies'), 0);
add_shortcode('book',  array($book, 'lemon_hive_book_shortcode'));
/**
 * Register a custom menu page.
 */
//add_action( 'init', array('Advertisement_type','add_new'));

function lemon_hive_book_css_and_js()
{

	wp_enqueue_style('book_style_css', plugins_url('public/css/style_book.css', __FILE__));
	wp_enqueue_style('book_style_css');

	wp_register_script('book_custom_js', plugins_url('public/js/custom_book.js', __FILE__), array('jquery'));
	wp_enqueue_script('book_custom_js');


	wp_localize_script('book_custom_js', 'ajax_posts', array(
		'ajaxurl' => admin_url('admin-ajax.php'),
		'noposts' => __('No older books found'),
	));
}

add_action('init', 'lemon_hive_book_css_and_js');


function lemon_hive_book_query_vars_filter($vars)
{
	$vars[] = "cat_book";
	return $vars;
}
add_filter('query_vars', 'lemon_hive_book_query_vars_filter');


function lemon_hive_book_more_book_ajax()
{

	$ppp = (isset($_POST["ppp"])) ? $_POST["ppp"] : 4;
	$paged = (isset($_POST['pageNumber'])) ? $_POST['pageNumber'] : 0;

	header("Content-Type: text/html");
	$cat = (isset($_POST['cat'])) ? $_POST['cat'] : '';
	$args = array(
		'showposts' => $ppp,
		'post_type' => 'book',
		'orderby' => 'menu_order',
		'order' => 'ASC',
		'paged' => $paged,

	);

	if ($cat != '') {
		$args['tax_query'] = array(
			array(
				'taxonomy' => 'book_category',
				'field' => 'slug',
				'terms' => $cat
			)
		);
	}

	$loop = new WP_Query($args);

	$out = '';

	if ($loop->have_posts()) :  while ($loop->have_posts()) : $loop->the_post();
			$featured_img_url = get_the_post_thumbnail_url(get_the_ID(), 'full');
			$out .= '<article class="book-item" style="background-image: url(' . esc_url($featured_img_url) . ');
							background-color: rgba(255, 255, 255, 0);">

					<div class="book-content">
						<div class="book-top-content">
							<h3 class="book-title">' . get_the_title() . '</h3>
							<p>' . get_the_content() . '</p>
							<a class="book-link" href="' . get_the_permalink() . '">Read More</a>
						</div>

					</div>
				</article>';

		endwhile;
	endif;
	wp_reset_postdata();
	die($out);
}

add_action('wp_ajax_nopriv_more_book_ajax', 'lemon_hive_book_more_book_ajax');
add_action('wp_ajax_more_book_ajax', 'lemon_hive_book_more_book_ajax');
