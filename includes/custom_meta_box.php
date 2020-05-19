<?php
//making the meta box (Note: meta box != custom meta field)
function book_gallery_add_custom_meta_box()
{
    add_meta_box(
        'book_custom_meta_box_id',       // $id
        'Gallery Id',                  // $title
        'book_custom_meta_field',  // $callback
        'book',                 // $page
        'normal',                  // $context
        'high'                     // $priority
    );
}
add_action('add_meta_boxes', 'book_gallery_add_custom_meta_box');

//showing custom form fields
function book_custom_meta_field()
{
    global $post;

    // Use nonce for verification to secure data sending
    wp_nonce_field(basename(__FILE__), 'book_gallery_our_nonce');
    $galleryValue = get_post_meta($post->ID, '_book_gallery_value', true);
?>

    <!-- my custom value input -->
    <input type="text" name="book_gallery_value" value="<?php echo esc_attr($galleryValue) ?> ">

<?php
}

//now we are saving the data
function book_gallery_save_meta_fields($post_id)
{

    // verify nonce
    if (!isset($_POST['book_gallery_our_nonce']) || !wp_verify_nonce($_POST['book_gallery_our_nonce'], basename(__FILE__)))
        return 'nonce not verified';

    // check autosave
    if (wp_is_post_autosave($post_id))
        return 'autosave';

    //check post revision
    if (wp_is_post_revision($post_id))
        return 'revision';

    // check permissions
    if ('book' == $_POST['post_type']) {
        if (!current_user_can('edit_page', $post_id))
            return 'cannot edit page';
    } elseif (!current_user_can('edit_post', $post_id)) {
        return 'cannot edit post';
    }

    //so our basic checking is done, now we can grab what we've passed from our newly created form
    $book_gallery_value = sanitize_text_field($_POST['book_gallery_value']);

    update_post_meta($post_id, '_book_gallery_value', $book_gallery_value);
}
add_action('save_post', 'book_gallery_save_meta_fields');
