<?php

/**
 * Plugin name: Image Gallery
 * Author: Erik
 * Version: 1.0
 */

/**
 * Register Image Gallery Custom Post Type
 */
add_action('init', 'register_image_gallery');
function register_image_gallery() {
    register_post_type('image_gallery', [
        'label' => 'Image Gallery',
        'public' => true,
        'menu_icon' => 'dashicons-format-gallery',
        'supports' => ['title']
    ]);
}

/**
 * Enqueue CSS and Javascript in wp-admin
 * wp_enqueue_script => frontend
 * admin_enqueue_script => backend (wp-admim)
 * login_enqueue_script => login page
 */
add_action('admin_enqueue_scripts', 'load_gallery_script');
function load_gallery_script() {
    wp_register_style( 'gallery_custom_style', plugins_url('/image_gallery/css/admin.css') );
    wp_enqueue_style('gallery_custom_style');

    wp_enqueue_script('custom_script', plugins_url('/image_gallery/js/script.js'), [], '1.0', true);
}

/**
 * Enqueue CSS and Javascript in frontend
 */
add_action('wp_enqueue_scripts', 'load_front_end_script');
function load_front_end_script() {
    wp_register_style('gallery_frontend_custom_style', plugins_url('/image_gallery/css/gallery.css'));
    wp_enqueue_style('gallery_frontend_custom_style');
}

/**
 * Create meta box
 */
add_action('add_meta_boxes', 'create_image_gallery_meta_box');
function create_image_gallery_meta_box() {
    add_meta_box(
        'image_id',
        'Image Gallery',
        'show_image_gallery',
        'image_gallery',
        'normal',
        'high'
    );
}

/**
 * Show meta box in backend (wp-admin)
 */
function show_image_gallery() {
    global $post;
    $meta = get_post_meta($post->ID, 'image_gallery', true); ?>

    <input type="hidden" name="nonce_field" value="<?= wp_create_nonce(basename(__FILE__)) ?>">
    <p><input type="button" value="Choose Image" class="button image-upload"></p>
    <div class="image-preview">
        <?php if(isset($meta) && $meta !== '') : ?>
            <h3 class="preview-header">Image Preview</h3>
            <div class="preview-area">
                <?php foreach($meta as $link) : ?>
                    <div class="preview-group">
                        <div class="preview-link">
                            <label>Link</label>
                            <input name="image_gallery[]" type="text" value="<?= $link ?>" readonly>
                        </div>
                        <div class="image-show">
                            <img src="<?= $link ?>">
                            <span onclick="hardDelete(event)" class="dashicons dashicons-no-alt"></span>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

<?php }

/**
 * Save meta box data to DB
 */
add_action('save_post', 'save_image_gallery_meta');
function save_image_gallery_meta($post_id) {
    // verify nonce
    if(!wp_verify_nonce($_POST['nonce_field'], basename(__FILE__))) {
        return $post_id;
    }

    // check auto save
    if(defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return $post_id;
    }

    // check permission
    if($_POST['post_type'] === 'page') {
        if(!current_user_can( 'edit_page', $post_id )) {
            return $post_id;
        }else if(!current_user_can('edit_post', $post_id)) {
            return $post_id;
        }
    }

    if(!isset($_POST['image_gallery'])) {
        return $post_id;
    }

    $old = get_post_meta($post_id, 'image_gallery', true);
    $new = $_POST['image_gallery'];

    update_post_meta($post_id, 'image_gallery', $new);

    // if($new && $new !== $old) {
    //     update_post_meta($post_id, 'image_gallery', $new);
    // }else if($old && $new === '') {
    //     delete_post_meta($post_id, 'image_gallery', $old);
    // }
}

/**
 * Show Gallery with shortcode
 */
function generate_gallery($atts) {
    $post = get_posts([
        'post_type' => 'image_gallery'
    ]);

    $post_id = $post[0]->ID;
    if(!empty($atts)) $post_id = $atts['id'];

    $gallery_meta = get_post_meta($post_id, 'image_gallery', true);
    ob_start()
    ?>
    <div class="gallery-group">
        <?php foreach($gallery_meta as $gallery) : ?>
            <div class="gallery-group-wrapper">
                <img src="<?= $gallery ?>">
            </div>
        <?php endforeach ?>
    </div>
    <?php
    $content = ob_get_clean();

    return $content;
}
add_shortcode('image_gallery', 'generate_gallery');