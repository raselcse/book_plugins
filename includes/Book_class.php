<?php
class Book_class
{
  var $db_version = "1.0.0";

  public function __construct()
  {
    global $wpdb;
  }

  public function activate()
  {

    require_once ABSPATH . 'wp-admin/includes/upgrade.php';

    add_option('lem_hive_book_version', $this->db_version);

    if (!current_user_can('activate_plugins')) return;

    global $wpdb;

    if (null === $wpdb->get_row("SELECT post_name FROM {$wpdb->prefix}posts WHERE post_name = 'book-list'", 'ARRAY_A')) {

      $current_user = wp_get_current_user();

      // create post object
      $page = array(
        'post_title'  => __('Book list'),
        'post_status' => 'publish',
        'post_content' =>  '[book per_page="4"]',
        'post_author' => $current_user->ID,
        'post_type'   => 'page',
      );

      // insert the post into the database
      wp_insert_post($page);
    }
  }

  public function deactivate()
  {
    require_once ABSPATH . 'wp-admin/includes/upgrade.php';
    delete_option('lem_hive_book_version');

    // $table_candidate = $this->tables['candidate'];
    // $table_job = $this->tables['job'];

  }

  public function lemon_hive_book_cpt()
  {

    $labels = array(
      'name'               => _x('Books', 'post type general name'),
      'singular_name'      => _x('Book', 'post type singular name'),
      'add_new'            => _x('Add New', 'book'),
      'add_new_item'       => __('Add New Book'),
      'edit_item'          => __('Edit Book'),
      'new_item'           => __('New Book'),
      'all_items'          => __('All Books'),
      'view_item'          => __('View Book'),
      'search_items'       => __('Search Books'),
      'not_found'          => __('No Books found'),
      'not_found_in_trash' => __('No Books found in the Trash'),
      'menu_name'          => 'Books'
    );
    $args = array(
      'labels'        => $labels,
      'description'   => 'Book custom post type',
      'public'        => true,
      'menu_position' => 5,
      'supports'      => array('title', 'editor', 'thumbnail', 'excerpts', 'custom-fields'),
      'has_archive'   => true,
    );
    register_post_type('book', $args);
  }

  public function lemon_hive_book_taxonomies()
  {
    register_taxonomy(
      'book_category',
      'book',
      array(
        'labels' => array(
          'name' => 'Book category',
          'add_new_item' => 'Add New book category',
          'new_item_name' => "New book category"
        ),
        'show_ui' => true,
        'show_tagcloud' => false,
        'hierarchical' => true
      )
    );
  }

  function lemon_hive_book_shortcode($atts)
  {

    extract(shortcode_atts(array(
      'per_page' => '4',
    ), $atts));

    $paged = (get_query_var('paged')) ? absint(get_query_var('paged')) : 1;

    $settings = array(
      'showposts' => $per_page,
      'post_type' => 'book',
      'orderby' => 'menu_order',
      'order' => 'ASC',
      'paged' => $paged
    );
    $category_value = (esc_attr($_GET['cat_book'])) ? esc_attr($_GET['cat_book']) : false;

    if ($category_value) {
      $settings['tax_query'] = array(
        array(
          'taxonomy' => 'book_category',
          'field' => 'slug',
          'terms' => $category_value
        )
      );
    }

    $post_query = new WP_Query($settings);

    $list = '<div id="book-section">';

    $args = array(
      'child_of'                 => 0,
      'parent'                   => '',
      'orderby'                  => 'name',
      'order'                    => 'ASC',
      'hide_empty'               => 1,
      'hierarchical'             => 1,
      'taxonomy'                 => 'book_category',
      'pad_counts'               => false
    );
    $categories = get_categories($args);

    $list .= '<ul class="category_list">';
    $active_class_all_category = empty($category_value) ? 'active' : '';
    $list .= '<li class="' . $active_class_all_category . '"><a href="' . site_url() . '/book-page/"> All </a></li>';
    foreach ($categories as $category) {
      $active_class = ($category_value == $category->slug) ? 'active' : '';
      $url = get_term_link($category);
      $list .= '<li class="' . $active_class . '"><a href="' . site_url() . '/book-page/?cat_book=' . $category->slug . '"> ' . $category->name . '</a></li>';
    }
    $list .= '</ul>';

    while ($post_query->have_posts()) : $post_query->the_post();
      $featured_img_url = get_the_post_thumbnail_url(get_the_ID(), 'full');
      $list .= '<article class="book-item" style="background-image: url(' . esc_url($featured_img_url) . ');
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
    wp_reset_postdata();

    $list .= '</div>';
    if ($post_query->max_num_pages > 1) {
      $list .= '<div id="more_books" data-maxpage="' . $post_query->max_num_pages . '" data-ppp="' . $per_page . '" data-category="' . $category_value . '">Load More</div>';
    }

    return $list;
  }
}
